<?php
declare( strict_types = 1 );

/**
 * Test file for testing ServiceContainer::loadWiringFiles
 */

return [
	'Bar' => static function () {
		return 'Bar!';
	},
];
