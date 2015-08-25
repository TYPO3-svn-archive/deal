<?php

if ( !defined( 'TYPO3_MODE' ) )
{
  die( 'Access denied.' );
}

$TCA[ "tx_my_apartmentsforrent" ] = array(
  "interface" => array(
    "showRecordFieldList" => "...,immo24id,immo24idSandbox,immo24log,immo24tstamp,immo24tstampSandbox,immo24url,immo24urlSandbox"
  ),
  "columns" => array(
    "immo24id" => Array(
      "exclude" => 1,
      "label" => "LLL:EXT:deal/Resources/Private/Marketplaces/Immo24/samples/my_apartmentsforrent/locallang_db.xml:tx_my_apartmentsforrent.immo24id",
      "config" => Array(
        "type" => "input",
        "size" => "11",
//        "readOnly" => "1",
      )
    ),
    "immo24idSandbox" => Array(
      "exclude" => 1,
      "label" => "LLL:EXT:deal/Resources/Private/Marketplaces/Immo24/samples/my_apartmentsforrent/locallang_db.xml:tx_my_apartmentsforrent.immo24idSandbox",
      "config" => Array(
        "type" => "input",
        "size" => "11",
//        "readOnly" => "1",
      )
    ),
    "immo24log" => Array(
      "exclude" => 1,
      "label" => "LLL:EXT:deal/Resources/Private/Marketplaces/Immo24/samples/my_apartmentsforrent/locallang_db.xml:tx_my_apartmentsforrent.immo24log",
      "config" => Array(
        "type" => "text",
        "cols" => "50",
        "rows" => "10",
      )
    ),
    'immo24tstamp' => array(
      'exclude' => 1,
      "label" => "LLL:EXT:deal/Resources/Private/Marketplaces/Immo24/samples/my_apartmentsforrent/locallang_db.xml:tx_my_apartmentsforrent.immo24tstamp",
      'config' => array(
        'type' => 'input',
        'size' => '20',
        'max' => '20',
        'eval' => 'datetime',
        'default' => mktime( date( 'H' ), date( 'i' ), 0, date( 'm' ), date( 'd' ), date( 'Y' ) ),
      ),
    ),
    'immo24tstampSandbox' => array(
      'exclude' => 1,
      "label" => "LLL:EXT:deal/Resources/Private/Marketplaces/Immo24/samples/my_apartmentsforrent/locallang_db.xml:tx_my_apartmentsforrent.immo24tstampSandbox",
      'config' => array(
        'type' => 'input',
        'size' => '20',
        'max' => '20',
        'eval' => 'datetime',
        'default' => mktime( date( 'H' ), date( 'i' ), 0, date( 'm' ), date( 'd' ), date( 'Y' ) ),
      ),
    ),
    "immo24url" => Array(
      "exclude" => 1,
      "label" => "LLL:EXT:deal/Resources/Private/Marketplaces/Immo24/samples/my_apartmentsforrent/locallang_db.xml:tx_my_apartmentsforrent.immo24url",
      "config" => Array(
        "type" => "input",
        "size" => "60",
      )
    ),
    "immo24urlSandbox" => Array(
      "exclude" => 1,
      "label" => "LLL:EXT:deal/Resources/Private/Marketplaces/Immo24/samples/my_apartmentsforrent/locallang_db.xml:tx_my_apartmentsforrent.immo24urlSandbox",
      "config" => Array(
        "type" => "input",
        "size" => "60",
      )
    ),
  ),
  "types" => array(
    "0" => array( "showitem" => "..., --div--;LLL:EXT:deal/Resources/Private/Marketplaces/Immo24/samples/my_apartmentsforrent/locallang_db.xml:tx_my_apartmentsforrent.div.immo24,immo24id,immo24idSandbox,immo24log,immo24tstamp,immo24tstampSandbox,immo24url,immo24urlSandbox" )
  ),
);