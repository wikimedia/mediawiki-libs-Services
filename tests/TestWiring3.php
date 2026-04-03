<?php
declare( strict_types = 1 );

/**
 * Test file for testing ServiceContainer::loadWiringFiles
 */

return (object)[
	'Foo' => static function () {
		return 'Foo!';
	},
];
