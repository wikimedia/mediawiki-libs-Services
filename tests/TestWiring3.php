<?php
/**
 * Test file for testing ServiceContainer::loadWiringFiles
 */

return (object)[
	'Foo' => static function () {
		return 'Foo!';
	},
];
