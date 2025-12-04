<?php

/**
 * Exception thrown when trying to access a disabled service.
 *
 * @license GPL-2.0-or-later
 * @file
 */

namespace Wikimedia\Services;

use Psr\Container\ContainerExceptionInterface;
use RuntimeException;

/**
 * Exception thrown when trying to instantiate a currently instantiating service.
 *
 * @since 2.0.0
 */
class RecursiveServiceDependencyException extends RuntimeException
	implements ContainerExceptionInterface {

	/**
	 * @param string $serviceName
	 */
	public function __construct( string $serviceName ) {
		parent::__construct( "Recursive service instantiation: $serviceName" );
	}

}
