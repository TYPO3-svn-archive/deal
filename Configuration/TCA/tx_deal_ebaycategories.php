<?php

$dealTca = array(
  'ctrl' => array(
    'title' => 'LLL:EXT:deal/locallang_db.xml:tx_deal_ebaycategories',
    'label' => 'title',
    'tstamp' => 'tstamp',
    'crdate' => 'crdate',
    'cruser_id' => 'cruser_id',
    'default_sortby' => 'ORDER BY title',
    'delete' => 'deleted',
    'enablecolumns' => array(
      'disabled' => 'hidden',
    ),
    'iconfile' => \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extRelPath( 'deal' ) . 'Resources/Public/Images/Marketplaces/ebay/ext_icon.gif',
    'searchFields' => 'title',
    'treeParentField' => 'uid_parent',
  ),
  'interface' => array(
    'showRecordFieldList' => 'hidden,title,uid_parent'
  ),
	'types' => array(
		'1' => array('showitem' => 'hidden,title,uid_parent'),
	),
	'palettes' => array(
		'1' => array(),
	),
  'columns' => array(
    'hidden' => array(
      'exclude' => 1,
      'label' => 'LLL:EXT:lang/locallang_general.xml:LGL.hidden',
      'config' => array(
        'type' => 'check',
        'default' => '0'
      )
    ),
    'title' => array(
      'exclude' => 0,
      'label' => 'LLL:EXT:deal/locallang_db.xml:tx_deal_ebaycategories.title',
      'config' => array(
        'type' => 'input',
        'size' => '30',
        'eval' => 'required',
      )
    ),
    'uid_parent' => array(
      'exclude' => 0,
      'label' => 'LLL:EXT:deal/locallang_db.xml:tx_deal_ebaycategories.uid_parent',
      'config' => array(
        'type' => 'select',
        'size' => 1,
        'minitems' => 0,
        'maxitems' => 2,
        'trueMaxItems' => 1,
        'foreign_table' => 'tx_deal_ebaycategories',
        'foreign_table_where' => 'AND tx_deal_ebaycategories.pid=###CURRENT_PID### ORDER BY tx_deal_ebaycategories.title',
        'form_type' => 'user',
        'userFunc' => 'tx_cpstcatree->getTree',
        'treeView' => 1,
        'expandable' => 1,
        'expandFirst' => 0,
        'expandAll' => 0,
      ),
    ),
  ),
  'types' => array(
    '0' => array( 'showitem' => 'hidden;;1;;1-1-1, title;;;;2-2-2, uid_parent' )
  ),
  'palettes' => array(
    '1' => array( 'showitem' => '' ),
  )
);

return $dealTca;