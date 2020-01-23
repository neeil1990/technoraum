<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/menu.bundle.css',
	'js' => 'dist/menu.bundle.js',
	'rel' => [
		'main.core',
		'landing.loc',
		'landing.env',
		'landing.main',
		'landing.backend',
		'landing.ui.form.menuform',
		'landing.menu.menuitem',
	],
	'skip_core' => false,
];