<?php

$EM_CONF[$_EXTKEY] = array(
  'title' => 'Deal! TYPO3 for ebay and immoscout24',
  'description' => 'TYPO3 interface for the marketplaces ebay and immobilienscout24. '
  . 'Manage all items with TYPO3 and publish it on ebay and immobilienscout24 (is24). '
  . 'Deal! is ready for use with Quick Shop. '
  . 'See: http://typo3-deal.de/typo3conf/ext/deal/doc/manual.pdf',
  'category' => 'be',
  'shy' => 0,
  'version' => '7.2.2',
  'dependencies' => 'typo3',
  'conflicts' => '',
  'priority' => '',
  'loadOrder' => '',
  'module' => '',
  'state' => 'beta',
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
      'typo3' => '4.5.0-6.2.99',
    ),
    'conflicts' => array(
    ),
    'suggests' => array(
      'cps_devlib' => '0.9.1-',
      'cps_tcatree' => '0.4.2-',
      'deal_ebay_000_us' => '',
      'deal_ebay_077_germany' => '',
    ),
  ),
);