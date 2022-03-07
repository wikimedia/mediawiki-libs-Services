<?php
$cfg = require __DIR__ . '/../vendor/mediawiki/mediawiki-phan-config/src/config.php';

$cfg['directory_list'] = [
	'src',
	'vendor/psr/container',
	'vendor/wikimedia/scoped-callback',
];
$cfg['exclude_analysis_directory_list'][] = 'vendor/';

return $cfg;
