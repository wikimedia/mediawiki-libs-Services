<?php
/**
 * Test file for testing ServiceContainer::loadWiringFiles
 */

return [
	'Foo' => static function () {
		return 'Foo!';
	},
];
