<?php

if ( !defined( 'TYPO3_MODE' ) )
  die( 'Access denied.' );

// #i0036, 150825, dwildt, +
$TCA[ "fe_users" ][ "ctrl" ][ "tx_deal" ] = array(
  'marketplaces' => array(
    'immo24' => array(
      'ctrl' => array(
        'fields' => array(
          'immo24id' => 'immo24id', // obligate
          'immo24idsandbox' => 'immo24idSandbox', // obligate
          'immo24log' => 'immo24log', // obligate
          'immo24logsandbox' => 'immo24log', // obligate
          'immo24timestamp' => 'immo24tstamp', // obligate
          'immo24timestampsandbox' => 'immo24tstampSandbox', // obligate
        ),
      ),
      'fields' => array( // order of fields is obligate! See http://api.immobilienscout24.de/our-apis/import-export/contact/post.html
        'email' => array(
          'field' => 'email', // obligate
        ),
        'salutation' => array(
          'field' => null,
          'mapping' => array(
            '0' => 'MALE',
            '1' => 'FEMALE',
          ),
          'pattern' => '^MALE|FEMALE$', // True, if it's matching MALE or FEMALE
          'patternsample' => 'MALE, FEMALE',
        ),
        'firstname' => array(
          'field' => null,
        ),
        'lastname' => array(
          'field' => 'name', // obligate
        ),
        'faxNumberCountryCode' => array(
          'field' => null,
          'pattern' => '^\+[0-9]{2,3}', // True: +49, +001
          'patternsample' => '+49, +001',
        ),
        'faxNumberAreaCode' => array(
          'field' => null,
          'pattern' => '^[^0].[0-9]*$', // True all numbers, which don't start with a 0
          'patternsample' => '30, 475896 but not 030 and not 0475896',
        ),
        'faxNumberSubscriber' => array(
          'field' => 'fax',
          'pattern' => '^[\d][\d \-]{0,24}[\d]$', // True all numbers, which don't start with a 0
          'patternsample' => '030 475-896 but not (030) 475-896',
        ),
        'phoneNumberCountryCode' => array(
          'field' => null,
          'pattern' => '^\+[0-9]{2,3}', // True: +49, +001
          'patternsample' => '+49, +001',
        ),
        'phoneNumberAreaCode' => array(
          'field' => null,
          'pattern' => '^[^0].[0-9]*$', // True all numbers, which don't start with a 0
          'patternsample' => '30, 475896 but not 030 and not 0475896',
        ),
        'phoneNumberSubscriber' => array(
          'field' => 'telephone',
          'pattern' => '^[\d][\d \-]{0,24}[\d]$', // True all numbers, which don't start with a 0
          'patternsample' => '030 475-896 but not (030) 475-896',
        ),
        'cellNumberCountryCode' => array(
          'field' => null,
          'pattern' => '^\+[0-9]{2,3}', // True: +49, +001
          'patternsample' => '+49, +001',
        ),
        'cellNumberAreaCode' => array(
          'field' => null,
          'pattern' => '^[^0].[0-9]*$', // True all numbers, which don't start with a 0
          'patternsample' => '30, 475896 but not 030 and not 0475896',
        ),
        'cellNumberSubscriber' => array(
          'field' => null,
          'pattern' => '^[^0].[0-9]*$', // True all numbers, which don't start with a 0
          'patternsample' => '30, 475896 but not 030 and not 0475896',
        ),
        'address' => array(
          'field' => array(
            'street' => array(
              'field' => 'address',
            ),
            'houseNumber' => array(
              'field' => NULL,
            ),
            'postcode' => array(
              'field' => 'zip',
            ),
            'city' => array(
              'field' => 'city',
            ),
          ),
        ),
        'countryCode' => array(
          'field' => 'country',
          'default' => 'DEU',
          'mapping' => array(
            '276' => 'DEU',
            'Deutschland' => 'DEU',
            'Germany' => 'DEU',
          ),
          'pattern' => '^DEU|XXX|YYY$', // True, if it's matching DEU, XXX or YYY (the two last are dummies!)
          'patternsample' => 'DEU, XXX, YYY (the two last are dummies!)',
        ),
        'title' => array(
          'field' => null,
        ),
        'additionName' => array(
          'field' => null,
        ),
        'company' => array(
          'field' => 'company',
        ),
        'homepageUrl' => array(
          'field' => 'www',
          'pattern' => '^http(s?)\:\/\/[a-zA-Z0-9\-\.]+\.[a-zA-Z]{2,3}(\/\S*)?$',
          'patternsample' => 'http://google.de, https://google.de but not ftp://google.de, www.google.de',
        ),
        'position' => array(
          'field' => null,
        ),
        'officehours' => array(
          'field' => null,
        ),
        'defaultContact' => array(
          'field' => null,
          'default' => 'false',
        ),
        'localPartnerContact' => array(
          'field' => null,
        ),
        'businessCardContact' => array(
          'field' => null,
          'default' => 'false',
        ),
        'realEstateReferenceCount' => array(
          'field' => null,
        ),
        'externalId' => array(
          'field' => 'uid',
        ),
        'showOnProfilePage' => array(
          'field' => null,
          'default' => 'false',
        ),
      ),
    ),
  )
);

// #i0006, 150722, dwildt, +
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns(
        'fe_users', array(
  'immo24id' => array(
    'label' => 'LLL:EXT:deal/Resources/Private/Marketplaces/Immo24/samples/fe_users/locallang_db.xml:fe_users.immo24id',
    'config' => array(
      'type' => 'input',
      'size' => '11',
    ),
  ),
  "immo24idSandbox" => Array(
    "exclude" => 1,
    "label" => "LLL:EXT:deal/Resources/Private/Marketplaces/Immo24/samples/fe_users/locallang_db.xml:fe_users.immo24idSandbox",
    'config' => array(
      'type' => 'input',
      'size' => '11',
    ),
  ),
  'immo24log' => array(
    'label' => 'LLL:EXT:deal/Resources/Private/Marketplaces/Immo24/samples/fe_users/locallang_db.xml:fe_users.immo24log',
    'config' => array(
      'type' => 'text',
      'cols' => '50',
      'rows' => '10',
    )
  ),
  'immo24tstamp' => array(
    'exclude' => 1,
    'label' => 'LLL:EXT:deal/Resources/Private/Marketplaces/Immo24/samples/fe_users/locallang_db.xml:fe_users.immo24tstamp',
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
    "label" => "LLL:EXT:deal/Resources/Private/Marketplaces/Immo24/samples/fe_users/locallang_db.xml:fe_users.immo24tstampSandbox",
    'config' => array(
      'type' => 'input',
      'size' => '20',
      'max' => '20',
      'eval' => 'datetime',
      'default' => mktime( date( 'H' ), date( 'i' ), 0, date( 'm' ), date( 'd' ), date( 'Y' ) ),
    ),
  ),
        )
);
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes(
        'fe_users', '--div--;LLL:EXT:deal/Resources/Private/Marketplaces/Immo24/samples/fe_users/locallang_db.xml:fe_users.div.immo24,immo24id,immo24idSandbox,immo24log,immo24tstamp,immo24tstampSandbox,immo24url,immo24urlSandbox'
);
