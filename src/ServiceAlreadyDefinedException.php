<?php
/**
 * Exception thrown when a service was already defined, but the
 * caller expected it to not exist.
 *
 * @license GPL-2.0-or-later
 * @file
 */

namespace Wikimedia\Services;

use Exception;
use Psr\Container\ContainerExceptionInterface;
use RuntimeException;

/**
 * Exception thrown when a service was already defined, but the
 * caller expected it to not exist.
 */
class ServiceAlreadyDefinedException extends RuntimeException
	implements ContainerExceptionInterface {

	/**
	 * @param string $serviceName
	 * @param Exception|null $previous
	 */
	public function __construct( string $serviceName, ?Exception $previous = null ) {
		parent::__construct( "Service already defined: $serviceName", 0, $previous );
	}

}
