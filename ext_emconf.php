<?php

$EM_CONF[$_EXTKEY] = array(
	'title' => 'Deal! Quick Shop interface for amazon and ebay.',
	'description' => 'TYPO3 Quick Shop interface for the marketplaces amazon and ebay. Manage all items with TYPO3 and publish it at amazon and at ebay. Use the interface ready-to-use. See: https://typo3-deal.de/typo3conf/ext/deal/doc/manual.pdf',
	'category' => 'be',
	'shy' => 0,
	'version' => '0.0.2',
	'dependencies' => 'cps_tcatree',
	'conflicts' => '',
	'priority' => '',
	'loadOrder' => '',
	'module' => '',
	'state' => 'alpha',
	'uploadfolder' => 0,
	'createDirs' => '',
	'modify_tables' => '',
	'clearcacheonload' => 1,
	'lockType' => '',
	'author' => 'Dirk Wildt (Die Netzmacher)',
	'author_email' => 'http://wildt.at.die-netzmacher.de',
	'author_company' => '',
	'CGLcompliance' => '',
	'CGLcompliance_note' => '',
	'constraints' => array(
		'depends' => array(
			'cps_tcatree' => '',
			'typo3' => '4.5.0-6.1.99',
		),
		'conflicts' => array(
		),
		'suggests' => array(
		),
	),
	'suggests' => array(
	),
);

?>