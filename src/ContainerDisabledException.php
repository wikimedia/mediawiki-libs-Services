<?php
/**
 * Exception thrown when trying to access a service on a disabled container or factory.
 *
 * @license GPL-2.0-or-later
 * @file
 */

namespace Wikimedia\Services;

use Exception;
use Psr\Container\ContainerExceptionInterface;
use RuntimeException;

/**
 * Exception thrown when trying to access a service on a disabled container or factory.
 */
class ContainerDisabledException extends RuntimeException
	implements ContainerExceptionInterface {

	/**
	 * @param Exception|null $previous
	 */
	public function __construct( ?Exception $previous = null ) {
		parent::__construct( 'Container disabled!', 0, $previous );
	}

}
