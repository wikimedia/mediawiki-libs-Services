<?php
/**
 * Interface for salvageable services.
 *
 * @license GPL-2.0-or-later
 * @file
 */

namespace Wikimedia\Services;

/**
 * SalvageableService defines an interface for services that are able to salvage state from a
 * previous instance of the same class. The intent is to allow new service instances to re-use
 * resources that would be expensive to re-create, such as cached data or network connections.
 *
 * @note There is no expectation that services will be destroyed when the process (or web request)
 * terminates.
 */
interface SalvageableService {

	/**
	 * Re-uses state from $other. $other must not be used after being passed to salvage(),
	 * and should be considered to be destroyed.
	 *
	 * @note Implementations are responsible for determining what parts of $other can be re-used
	 * safely. In particular, implementations should check that the relevant configuration of
	 * $other is the same as in $this before re-using resources from $other.
	 *
	 * @note Implementations must take care to detach any re-used resources from the original
	 * service instance. If $other is destroyed later, resources that are now used by the
	 * new service instance must not be affected.
	 *
	 * @note If $other is a DestructibleService, implementations should make sure that $other
	 * is in destroyed state after salvage finished. This may be done by calling $other->destroy()
	 * after carefully detaching all relevant resources.
	 *
	 * @param SalvageableService $other The object to salvage state from. $other must have the
	 * exact same type as $this.
	 */
	public function salvage( SalvageableService $other );

}
