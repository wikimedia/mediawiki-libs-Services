<?php

namespace Wikimedia\Services\Tests;

use PHPUnit\Framework\TestCase;
use Wikimedia\Services\CannotReplaceActiveServiceException;
use Wikimedia\Services\ContainerDisabledException;
use Wikimedia\Services\DestructibleService;
use Wikimedia\Services\NoSuchServiceException;
use Wikimedia\Services\RecursiveServiceDependencyException;
use Wikimedia\Services\ServiceAlreadyDefinedException;
use Wikimedia\Services\ServiceContainer;
use Wikimedia\Services\ServiceDisabledException;

/**
 * @covers \Wikimedia\Services\ServiceContainer
 * @covers \Wikimedia\Services\CannotReplaceActiveServiceException
 * @covers \Wikimedia\Services\ContainerDisabledException
 * @covers \Wikimedia\Services\NoSuchServiceException
 * @covers \Wikimedia\Services\RecursiveServiceDependencyException
 * @covers \Wikimedia\Services\ServiceAlreadyDefinedException
 * @covers \Wikimedia\Services\ServiceDisabledException
 */
class ServiceContainerTest extends TestCase {

	private function newServiceContainer( array $extraArgs = [] ): ServiceContainer {
		return new ServiceContainer( $extraArgs );
	}

	public function testGetServiceNames() {
		$services = $this->newServiceContainer();
		$names = $services->getServiceNames();

		$this->assertSame( [], $names );

		$name = 'TestService92834576';
		$services->defineService( $name, static function () {
			return null;
		} );

		$names = $services->getServiceNames();
		$this->assertContains( $name, $names );
	}

	public function testHasService() {
		$services = $this->newServiceContainer();

		$name = 'TestService92834576';
		$this->assertFalse( $services->hasService( $name ) );
		$this->assertFalse( $services->has( $name ) );

		$services->defineService( $name, static function () {
			return null;
		} );

		$this->assertTrue( $services->hasService( $name ) );
		$this->assertTrue( $services->has( $name ) );
	}

	public function testGetService() {
		$services = $this->newServiceContainer( [ 'Foo' ] );

		$theService = (object)[];
		$name = 'TestService92834576';
		$count = 0;

		$services->defineService(
			$name,
			function ( $actualLocator, $extra ) use ( $services, $theService, &$count ) {
				$count++;
				$this->assertSame( $services, $actualLocator );
				$this->assertSame( 'Foo', $extra );
				return $theService;
			}
		);

		$this->assertSame( $theService, $services->getService( $name ) );
		$this->assertSame( $theService, $services->get( $name ) );

		$services->getService( $name );
		$this->assertSame( 1, $count, 'instantiator should be called exactly once!' );
	}

	public function testGetServiceRecursionCheck() {
		$services = $this->newServiceContainer();

		$services->defineService( 'service1', static function ( ServiceContainer $services ) {
			$services->getService( 'service2' );
		} );

		$services->defineService( 'service2', static function ( ServiceContainer $services ) {
			$services->getService( 'service3' );
		} );

		$services->defineService( 'service3', static function ( ServiceContainer $services ) {
			$services->getService( 'service1' );
		} );

		$exceptionThrown = false;
		try {
			$services->getService( 'service1' );
		} catch ( RecursiveServiceDependencyException $e ) {
			$exceptionThrown = true;
			$this->assertSame(
				'Recursive service instantiation: ' .
				'Circular dependency when creating service! ' .
				'service1 -> service2 -> service3 -> service1', $e->getMessage() );
		}
		$this->assertTrue( $exceptionThrown, 'RecursiveServiceDependencyException must be thrown' );
	}

	public function testGetService_fail_unknown() {
		$services = $this->newServiceContainer();

		$name = 'TestService92834576';

		$this->expectException( NoSuchServiceException::class );

		$services->getService( $name );
	}

	public function testPeekService() {
		$services = $this->newServiceContainer();

		$services->defineService(
			'Foo',
			static function () {
				return (object)[];
			}
		);

		$services->defineService(
			'Bar',
			static function () {
				return (object)[];
			}
		);

		// trigger instantiation of Foo
		$services->getService( 'Foo' );

		$this->assertIsObject(
			$services->peekService( 'Foo' ),
			'Peek should return the service object if it had been accessed before.'
		);

		$this->assertNull(
			$services->peekService( 'Bar' ),
			'Peek should return null if the service was never accessed.'
		);
	}

	public function testPeekService_fail_unknown() {
		$services = $this->newServiceContainer();

		$name = 'TestService92834576';

		$this->expectException( NoSuchServiceException::class );

		$services->peekService( $name );
	}

	public function testDefineService() {
		$services = $this->newServiceContainer();

		$theService = (object)[];
		$name = 'TestService92834576';

		$services->defineService( $name, function ( $actualLocator ) use ( $services, $theService ) {
			$this->assertSame( $services, $actualLocator );
			return $theService;
		} );

		$this->assertTrue( $services->hasService( $name ) );
		$this->assertSame( $theService, $services->getService( $name ) );
	}

	public function testDefineService_fail_duplicate() {
		$services = $this->newServiceContainer();

		$theService = (object)[];
		$name = 'TestService92834576';

		$services->defineService( $name, static function () use ( $theService ) {
			return $theService;
		} );

		$this->expectException( ServiceAlreadyDefinedException::class );

		$services->defineService( $name, static function () use ( $theService ) {
			return $theService;
		} );
	}

	public function testApplyWiring() {
		$services = $this->newServiceContainer();

		$wiring = [
			'Foo' => static function () {
				return 'Foo!';
			},
			'Bar' => static function () {
				return 'Bar!';
			},
		];

		$services->applyWiring( $wiring );

		$this->assertSame( 'Foo!', $services->getService( 'Foo' ) );
		$this->assertSame( 'Bar!', $services->getService( 'Bar' ) );
	}

	public function testApplyWiring_fail_nonfunction() {
		$services = $this->newServiceContainer();

		$wiring = [
			'Foo' => static function () {
				return 'Foo!';
			},
			'Bar' => 'not a function',
		];

		$this->expectException( \TypeError::class );
		$services->applyWiring( $wiring );
	}

	public function testImportWiring() {
		$services = $this->newServiceContainer();

		$wiring = [
			'Foo' => static function () {
				return 'Foo!';
			},
			'Bar' => static function () {
				return 'Bar!';
			},
			'Car' => static function () {
				return 'FUBAR!';
			},
		];

		$services->applyWiring( $wiring );

		$services->addServiceManipulator( 'Foo', static function ( $service ) {
			return $service . '+X';
		} );

		$services->addServiceManipulator( 'Car', static function ( $service ) {
			return $service . '+X';
		} );

		$newServices = $this->newServiceContainer();

		// create a service with manipulator
		$newServices->defineService( 'Foo', static function () {
			return 'Foo!';
		} );

		$newServices->addServiceManipulator( 'Foo', static function ( $service ) {
			return $service . '+Y';
		} );

		// create a service before importing, so we can later check that
		// existing service instances survive importWiring()
		$newServices->defineService( 'Car', static function () {
			return 'Car!';
		} );

		// force instantiation
		$newServices->getService( 'Car' );

		// Define another service, so we can later check that extra wiring
		// is not lost.
		$newServices->defineService( 'Xar', static function () {
			return 'Xar!';
		} );

		// import wiring, but skip `Bar`
		$newServices->importWiring( $services, [ 'Bar' ] );

		$this->assertNotContains( 'Bar', $newServices->getServiceNames(), 'Skip `Bar` service' );
		$this->assertSame( 'Foo!+Y+X', $newServices->getService( 'Foo' ) );

		// import all wiring, but preserve existing service instance
		$newServices->importWiring( $services );

		$this->assertContains( 'Bar', $newServices->getServiceNames(), 'Import all services' );
		$this->assertSame( 'Bar!', $newServices->getService( 'Bar' ) );
		$this->assertSame( 'Car!', $newServices->getService( 'Car' ), 'Use existing service instance' );
		$this->assertSame( 'Xar!', $newServices->getService( 'Xar' ), 'Predefined services are kept' );
	}

	public function testLoadWiringFiles() {
		$services = $this->newServiceContainer();

		$wiringFiles = [
			__DIR__ . '/TestWiring1.php',
			__DIR__ . '/TestWiring2.php',
		];

		$services->loadWiringFiles( $wiringFiles );

		$this->assertSame( 'Foo!', $services->getService( 'Foo' ) );
		$this->assertSame( 'Bar!', $services->getService( 'Bar' ) );
	}

	public function testLoadWiringFiles_fail_duplicate() {
		$services = $this->newServiceContainer();

		$wiringFiles = [
			__DIR__ . '/TestWiring1.php',
			__DIR__ . '/./TestWiring1.php',
		];

		// loading the same file twice should fail, because
		$this->expectException( ServiceAlreadyDefinedException::class );

		$services->loadWiringFiles( $wiringFiles );
	}

	public function testLoadWiringFiles_fail_nonarray() {
		$services = $this->newServiceContainer();

		$wiringFiles = [
			__DIR__ . '/TestWiring3.php',
		];

		$this->expectException( \LogicException::class );
		$this->expectExceptionMessage( 'must return an array' );
		$services->loadWiringFiles( $wiringFiles );
	}

	public function testRedefineService() {
		$services = $this->newServiceContainer( [ 'Foo' ] );

		$theService1 = (object)[];
		$name = 'TestService92834576';

		$services->defineService( $name, function () {
			$this->fail(
				'The original instantiator function should not get called'
			);
		} );

		// redefine before instantiation
		$services->redefineService(
			$name,
			function ( $actualLocator, $extra ) use ( $services, $theService1 ) {
				$this->assertSame( $services, $actualLocator );
				$this->assertSame( 'Foo', $extra );
				return $theService1;
			}
		);

		// force instantiation, check result
		$this->assertSame( $theService1, $services->getService( $name ) );
	}

	public function testRedefineService_disabled() {
		$services = $this->newServiceContainer( [ 'Foo' ] );

		$theService1 = (object)[];
		$name = 'TestService92834576';

		$services->defineService( $name, static function () {
			return 'Foo';
		} );

		// disable the service. we should be able to redefine it anyway.
		$services->disableService( $name );

		$services->redefineService( $name, static function () use ( $theService1 ) {
			return $theService1;
		} );

		// force instantiation, check result
		$this->assertSame( $theService1, $services->getService( $name ) );
	}

	public function testRedefineService_fail_undefined() {
		$services = $this->newServiceContainer();

		$theService = (object)[];
		$name = 'TestService92834576';

		$this->expectException( NoSuchServiceException::class );

		$services->redefineService( $name, static function () use ( $theService ) {
			return $theService;
		} );
	}

	public function testRedefineService_fail_in_use() {
		$services = $this->newServiceContainer( [ 'Foo' ] );

		$theService = (object)[];
		$name = 'TestService92834576';

		$services->defineService( $name, static function () {
			return 'Foo';
		} );

		// create the service, so it can no longer be redefined
		$services->getService( $name );

		$this->expectException( CannotReplaceActiveServiceException::class );

		$services->redefineService( $name, static function () use ( $theService ) {
			return $theService;
		} );
	}

	public function testAddServiceManipulator() {
		$services = $this->newServiceContainer( [ 'Foo' ] );

		$theService1 = (object)[];
		$theService2 = (object)[];
		$name = 'TestService92834576';

		$services->defineService(
			$name,
			function ( $actualLocator, $extra ) use ( $services, $theService1 ) {
				$this->assertSame( $services, $actualLocator );
				$this->assertSame( 'Foo', $extra );
				return $theService1;
			}
		);

		$services->addServiceManipulator(
			$name,
			function (
				$theService, $actualLocator, $extra
			) use (
				$services, $theService1, $theService2
			) {
				$this->assertSame( $theService1, $theService );
				$this->assertSame( $services, $actualLocator );
				$this->assertSame( 'Foo', $extra );
				return $theService2;
			}
		);

		// force instantiation, check result
		$this->assertSame( $theService2, $services->getService( $name ) );
	}

	public function testAddServiceManipulator_fail_undefined() {
		$services = $this->newServiceContainer();

		$theService = (object)[];
		$name = 'TestService92834576';

		$this->expectException( NoSuchServiceException::class );

		$services->addServiceManipulator( $name, static function () use ( $theService ) {
			return $theService;
		} );
	}

	public function testAddServiceManipulator_fail_in_use() {
		$services = $this->newServiceContainer( [ 'Foo' ] );

		$theService = (object)[];
		$name = 'TestService92834576';

		$services->defineService( $name, static function () use ( $theService ) {
			return $theService;
		} );

		// create the service, so it can no longer be redefined
		$services->getService( $name );

		$this->expectException( CannotReplaceActiveServiceException::class );

		$services->addServiceManipulator( $name, static function () {
			return 'Foo';
		} );
	}

	public function testDisableService() {
		$services = $this->newServiceContainer( [ 'Foo' ] );

		$destructible = $this->createMock( DestructibleService::class );
		$destructible->expects( $this->once() )
			->method( 'destroy' );

		$services->defineService( 'Foo', static function () use ( $destructible ) {
			return $destructible;
		} );
		$services->defineService( 'Bar', static function () {
			return (object)[];
		} );
		$services->defineService( 'Qux', static function () {
			return (object)[];
		} );

		// instantiate Foo and Bar services
		$services->getService( 'Foo' );
		$services->getService( 'Bar' );

		// disable service, should call destroy() once.
		$services->disableService( 'Foo' );
		$this->assertTrue( $services->isServiceDisabled( 'Foo' ) );

		// disabled service should still be listed
		$this->assertContains( 'Foo', $services->getServiceNames() );

		// getting other services should still work
		$services->getService( 'Bar' );

		// disable non-destructible service, and not-yet-instantiated service
		$services->disableService( 'Bar' );
		$this->assertTrue( $services->isServiceDisabled( 'Bar' ) );
		$services->disableService( 'Qux' );
		$this->assertTrue( $services->isServiceDisabled( 'Qux' ) );

		$this->assertNull( $services->peekService( 'Bar' ) );
		$this->assertNull( $services->peekService( 'Qux' ) );

		// disabled service should still be listed
		$this->assertContains( 'Bar', $services->getServiceNames() );
		$this->assertContains( 'Qux', $services->getServiceNames() );

		$this->expectException( ServiceDisabledException::class );
		$services->getService( 'Qux' );
	}

	public function testDisableService_fail_undefined() {
		$services = $this->newServiceContainer();

		$theService = (object)[];
		$name = 'TestService92834576';

		$this->expectException( NoSuchServiceException::class );

		$services->redefineService( $name, static function () use ( $theService ) {
			return $theService;
		} );
	}

	public function testDestroy() {
		$services = $this->newServiceContainer();

		$destructible = $this->createMock( DestructibleService::class );
		$destructible->expects( $this->once() )
			->method( 'destroy' );

		$services->defineService( 'Foo', static function () use ( $destructible ) {
			return $destructible;
		} );

		$services->defineService( 'Bar', static function () {
			return (object)[];
		} );

		// create the service
		$services->getService( 'Foo' );

		// destroy the container
		$services->destroy();

		$this->expectException( ContainerDisabledException::class );
		$services->getService( 'Bar' );
	}

}
