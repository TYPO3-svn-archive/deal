<?php

$EM_CONF[$_EXTKEY] = array(
  'title' => 'Deal! Quick Shop interface for ebay',
  'description' => 'TYPO3 Quick Shop interface for the marketplace ebay. Manage all items with TYPO3 and publish it on ebay. Amazon is under construction. See: http://typo3-deal.de/typo3conf/ext/deal/doc/manual.pdf',
  'category' => 'be',
  'shy' => 0,
  'version' => '0.1.0',
  'dependencies' => 'cps_tcatree,deal_ebay_000_us',
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
      'deal_ebay_000_us' => '',
      'typo3' => '4.5.0-6.1.99',
    ),
    'conflicts' => array(
    ),
    'suggests' => array(
      'deal_ebay_077_germany' => '',
    ),
  ),
  'suggests' => array(
    'deal_ebay_077_germany' => '',
  ),
);
?>