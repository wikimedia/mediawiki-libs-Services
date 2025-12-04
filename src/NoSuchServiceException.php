<?php
/**
 * Exception thrown when the requested service is not known.
 *
 * @license GPL-2.0-or-later
 * @file
 */

namespace Wikimedia\Services;

use Exception;
use Psr\Container\NotFoundExceptionInterface;
use RuntimeException;

/**
 * Exception thrown when the requested service is not known.
 */
class NoSuchServiceException extends RuntimeException
	implements NotFoundExceptionInterface {

	/**
	 * @param string $serviceName
	 * @param Exception|null $previous
	 */
	public function __construct( string $serviceName, ?Exception $previous = null ) {
		parent::__construct( "No such service: $serviceName", 0, $previous );
	}

}
