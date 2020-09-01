<?php
/**
 * Test file for testing ServiceContainer::loadWiringFiles
 */

return (object)[
	'Foo' => function () {
		return 'Foo!';
	},
];
