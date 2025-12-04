<?php
/**
 * Exception thrown when trying to access a disabled service.
 *
 * @license GPL-2.0-or-later
 * @file
 */

namespace Wikimedia\Services;

use Exception;
use Psr\Container\ContainerExceptionInterface;
use RuntimeException;

/**
 * Exception thrown when trying to access a disabled service.
 */
class ServiceDisabledException extends RuntimeException
	implements ContainerExceptionInterface {

	/**
	 * @param string $serviceName
	 * @param Exception|null $previous
	 */
	public function __construct( string $serviceName, ?Exception $previous = null ) {
		parent::__construct( "Service disabled: $serviceName", 0, $previous );
	}

}
