<?php
/**
 * Exception thrown when trying to replace an already active service.
 *
 * @license GPL-2.0-or-later
 * @file
 */

namespace Wikimedia\Services;

use Exception;
use Psr\Container\ContainerExceptionInterface;
use RuntimeException;

/**
 * Exception thrown when trying to replace an already active service.
 */
class CannotReplaceActiveServiceException extends RuntimeException
	implements ContainerExceptionInterface {

	/**
	 * @param string $serviceName
	 * @param Exception|null $previous
	 */
	public function __construct( string $serviceName, ?Exception $previous = null ) {
		parent::__construct( "Cannot replace an active service: $serviceName", 0, $previous );
	}

}
