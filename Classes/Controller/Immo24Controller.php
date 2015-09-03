<?php

namespace Netzmacher\Deal\Controller;

use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

//use TYPO3\CMS\Backend\Utility\BackendUtility;
//use TYPO3\CMS\Core\Utility\GeneralUtility;
//use TYPO3\CMS\Extbase\Utility\LocalizationUtility;
//use TYPO3\CMS\Extbase\Persistence\Generic\QueryResult;
//use TYPO3\CMS\Core\Messaging\AbstractMessage;

/* * *************************************************************
 *  Copyright notice
 *
 *  (c) 2015 Dirk Wildt <http://wildt.at.die-netzmacher.de/>
 *
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 * ************************************************************* */

/**
 * Controller for deal immobilienscout24 interface frontend output
 *
 * @package deal
 * @license http://www.gnu.org/licenses/lgpl.html
 * 			GNU Lesser General Public License, version 3 or later
 * @version 7.0.2
 * @since 7.0.0
 */
class Immo24Controller extends ActionController
{

  /**
   * @var boolean
   */
  private $bImmo24Sandbox = FALSE;

  /**
   * The is24-sdk object Path to locallang file (with : as postfix)
   *
   * @var object
   */
  private $oImmocaster;

  /**
   * Path to locallang file (with : as postfix)
   *
   * @var string
   */
  private $locallangPath = 'LLL:EXT:deal/Resources/Private/Language/Marketplaces/Immo24/locallang_mod.xlf:';

  /**
   * @var \TYPO3\CMS\Lang\LanguageService
   */
  private $languageService = NULL;

  /**
   * @var string
   */
  private $sDatabaseTable = 'tx_deal_immo24certificate';

  /**
   * @var string
   */
  private $sDatabaseTableCurrent = NULL;

  /**
   * @var string
   */
  private $sDatabaseTableSandbox = 'tx_deal_immo24certificateSandbox';

  /**
   * @var string
   */
  private $sImmo24User = NULL;

  /**
   * init() :
   *
   * @return void
   * @access private
   * @version 7.0.0
   * @since 7.0.0
   */
  private function init()
  {
    $this->initImmocaster();
    $this->initProperties();
    $this->initDatabase();
  }

  /**
   * initDatabse()  : Database contains certification records.
   *                      If there isn't any record, immo24 actions aren't possible, which need a certificate.
   *
   * @return void
   * @access private
   * @version 7.0.0
   * @since 7.0.0
   */
  private function initDatabase()
  {
    $this->initDatabaseCurrent();
    $sDatabase = $this->settings[ 'marketplaces' ][ 'immo24' ][ 'api' ][ 'database' ][ 'type' ];

    $aDatabase = array(
      $sDatabase,
      TYPO3_db_host,
      TYPO3_db_username,
      TYPO3_db_password,
      TYPO3_db
    );

    $this->oImmocaster->setDataStorage(
            $aDatabase, 'Immocaster', $this->sDatabaseTableCurrent
    );
    $this->initDatabaseProperUid();
  }

  /**
   * initDatabseCurrent() : Database contains certification records.
   *                        If there isn't any record, immo24 actions aren't possible, which need a certificate.
   *
   * @return void
   * @access private
   * @version 7.0.0
   * @since 7.0.0
   */
  private function initDatabaseCurrent()
  {
    switch ( true )
    {
      case( $this->bImmo24Sandbox === FALSE):
        $this->sDatabaseTableCurrent = $this->sDatabaseTable;
        break;
      case( $this->bImmo24Sandbox === TRUE):
        $this->sDatabaseTableCurrent = $this->sDatabaseTableSandbox;
        break;
      case( $this->bImmo24Sandbox === NULL):
        $this->zzPromptErrorAndDie( 'errorRequestUrlUndefined' );
        break;
    }
  }

  /**
   * initDatabaseProperUid()  :
   *
   * @return void
   * @access private
   * @version 7.0.0
   * @since 7.0.0
   */
  private function initDatabaseProperUid()
  {
    $rows = $this->initDatabaseProperUidRows();

    if ( empty( $rows ) )
    {
      return;
    }

    if ( count( $rows ) > 1 )
    {
      $this->zzPromptErrorAndDie( 'errorDatabaseMoreThanOneRecord' );
    }

    $this->initDatabaseProperUidSetNewuid();
  }

  /**
   * initDatabaseProperUid()  :
   *
   * @return void
   * @access private
   * @version 7.0.0
   * @since 7.0.0
   */
  private function initDatabaseProperUidNewuid()
  {
    $sFields = 'MAX(uid) + 1 AS newuid';
    $sWhere = 1;
    $res = $GLOBALS[ 'TYPO3_DB' ]->exec_SELECTquery(
            $sFields, $this->sDatabaseTableCurrent, $sWhere
    );
    $row = $GLOBALS[ 'TYPO3_DB' ]->sql_fetch_assoc( $res );
    $GLOBALS[ 'TYPO3_DB' ]->sql_free_result( $res );

    return ( int ) $row[ 'newuid' ];
  }

  /**
   * initDatabaseProperUidRows()  :
   *
   * @return void
   * @access private
   * @version 7.0.0
   * @since 7.0.0
   */
  private function initDatabaseProperUidRows()
  {
    $sFields = '*';
    //$sWhere = 'uid = 0 ' . BackendUtility::deleteClause( $this->sDatabaseTableCurrent );
    $sWhere = 'uid = 0';
    $res = $GLOBALS[ 'TYPO3_DB' ]->exec_SELECTquery(
            $sFields, $this->sDatabaseTableCurrent, $sWhere
    );
    $rows = array();
    while ( ($row = $GLOBALS[ 'TYPO3_DB' ]->sql_fetch_assoc( $res ) ) )
    {
      $rows[] = $row;
    }
    $GLOBALS[ 'TYPO3_DB' ]->sql_free_result( $res );

    return $rows;
  }

  /**
   * initDatabaseProperUidSetNewuid()  :
   *
   * @return void
   * @access private
   * @version 7.0.0
   * @since 7.0.0
   */
  private function initDatabaseProperUidSetNewuid()
  {
    $fields_values = array(
      'uid' => $this->initDatabaseProperUidNewuid()
    );
    $GLOBALS[ 'TYPO3_DB' ]->exec_UPDATEquery(
            $this->sDatabaseTableCurrent, 'uid=0', $fields_values
    );
  }

  /**
   * initImmocaster() :
   *
   * @return void
   * @access private
   * @version 7.0.0
   * @since 7.0.0
   */
  private function initImmocaster()
  {
    require_once(ExtensionManagementUtility::extPath( 'deal' ) . 'Resources/Private/Marketplaces/Immo24/restapi-php-sdk_1.1.78/Immocaster/Sdk.php');

    list ($public, $private) = $this->initImmocasterKeys();
//    var_dump( __METHOD__, __LINE__, $public, $private );
    $this->oImmocaster = \Immocaster_Sdk::getInstance( 'immo24', $public, $private );
  }

  /**
   * initImmocasterKeys() :
   *
   * @return void
   * @access private
   * @version 7.0.0
   * @since 7.0.0
   */
  private function initImmocasterKeys()
  {
    $aLiveorsandbox = $this->settings[ 'flexform' ][ 'liveorsandbox' ];
//    var_dump( __METHOD__, __LINE__, $this->settings );

    switch ( true )
    {
      case(empty( $aLiveorsandbox[ 'keys' . $aLiveorsandbox[ 'requestUrl' ] . 'Private' ] )):
      case(empty( $aLiveorsandbox[ 'keys' . $aLiveorsandbox[ 'requestUrl' ] . 'Public' ] )):
//        $prompt = 'immo24 mode is "' . $aLiveorsandbox . '", but one or both - private and public - keys are empty. '
//                . 'Please configure a proper flexform. '
//                . 'Sorry for the trouble. TYPO3 Deal!';
//        $prompt = $this->languageService->sL( $this->locallangPath . 'errorNoKeys', true );
        $this->zzPromptErrorAndDie( 'errorNoKeys', array( '%mode%' ), array( $aLiveorsandbox[ 'requestUrl' ] ) );
        break;
      default:
        // follow the workflow
        break;
    }

    $aKeys = array(
      $aLiveorsandbox[ 'keys' . $aLiveorsandbox[ 'requestUrl' ] . 'Public' ],
      $aLiveorsandbox[ 'keys' . $aLiveorsandbox[ 'requestUrl' ] . 'Private' ]
    );
//    var_dump( __METHOD__, __LINE__, $aKeys );

    return $aKeys;
  }

  /**
   * initProperties() :
   *
   * @return void
   * @access private
   * @version 7.0.0
   * @since 7.0.0
   */
  private function initProperties()
  {
//      var_dump( __METHOD__, __LINE__, $this->settings );
    // Get flexform configuration
    $aImmo24FfProperties = $this->settings[ 'flexform' ][ 'liveorsandbox' ];
    // Get TS configuration
    $aImmo24TsProperties = $this->settings[ 'marketplaces' ][ 'immo24' ][ 'api' ][ 'properties' ];

    // json or xml
    $this->oImmocaster->setContentResultType( $aImmo24TsProperties[ 'contentResultType' ] );

    // curl or none
    $this->initPropertiesReadingType();

    // request debug mode
    if ( $aImmo24TsProperties[ 'requestDebug' ] )
    {
      // Disable it
      //$this->oImmocaster->disableRequestDebug()
      $this->oImmocaster->enableRequestDebug();
    }

    // strict mode
    if ( $aImmo24TsProperties[ 'strictMode' ] )
    {
      $this->oImmocaster->setStrictMode( true );
    }

    // live or sandbox
    $sRequestUrl = strtolower( $aImmo24FfProperties[ 'requestUrl' ] );
    $this->initPropertiesSandbox( $sRequestUrl );
    $this->oImmocaster->setRequestUrl( $sRequestUrl );
  }

  /**
   * initPropertiesReadingType() :
   *
   * @return void
   * @access private
   * @version 7.1.0
   * @since 7.1.0
   */
  private function initPropertiesReadingType()
  {
//      var_dump( __METHOD__, __LINE__, $this->settings );
    $aImmo24TsProperties = $this->settings[ 'marketplaces' ][ 'immo24' ][ 'api' ][ 'properties' ];

    switch ( TRUE )
    {
      case( $aImmo24TsProperties[ 'readingType' ] == 'curl' ):
        $this->initPropertiesReadingTypeCurl();
        return;
      case( $aImmo24TsProperties[ 'readingType' ] == 'none' ):
      default:
        $this->initPropertiesReadingTypeNone();
        return;
    }
  }

  /**
   * initPropertiesReadingTypeCurl() :
   *
   * @return void
   * @access private
   * @internal #t0456
   * @version 7.1.0
   * @since 7.1.0
   */
  private function initPropertiesReadingTypeCurl()
  {
    //var_dump( __METHOD__, __LINE__ );
    switch ( $this->zzCurlCheckBasicFunctions() )
    {
      case( TRUE ):
        //var_dump( __METHOD__, __LINE__ );
        $this->oImmocaster->setReadingType( 'curl' );
        return;
      case( FALSE ):
      default:
        $this->initPropertiesReadingTypeNone();
        return;
    }
  }

  /**
   * initPropertiesReadingTypeNone() :
   *
   * @return void
   * @access private
   * @internal #t0456
   * @version 7.1.0
   * @since 7.1.0
   */
  private function initPropertiesReadingTypeNone()
  {
    //var_dump( __METHOD__, __LINE__ );
    $this->oImmocaster->setReadingType( 'none' );
  }

  /**
   * initPropertiesSandbox() :
   *
   * @return void
   * @access private
   * @version 7.0.0
   * @since 7.0.0
   */
  private function initPropertiesSandbox( $sRequestUrl )
  {
    $sRequestUrl = strtolower( $sRequestUrl );

    switch ( $sRequestUrl )
    {
      case 'live':
        $this->bImmo24Sandbox = FALSE;
        break;
      case 'sandbox':
        $this->bImmo24Sandbox = TRUE;
        break;
      default:
        $this->zzPromptErrorAndDie( 'errorRequestUrlUndefined' );
        break;
    }
  }

  /**
   * pluginAction()  : Certificate current application at immobilienscout24
   *
   * @return void
   * @access public
   * @version 7.0.0
   * @since 7.0.0
   */
  public function pluginAction()
  {
//var_dump(__METHOD__, __LINE__, $this->settings);
//die();
    $sApplication = $this->settings[ 'flexform' ][ 'application' ][ 'type' ];

    $this->init();

    switch ( $sApplication )
    {
      case('certification'):
        $this->pluginCertification();
        break;
      case('logo'):
        $this->pluginLogo();
        break;
      case('region'):
        $this->pluginRegions();
        break;
      default:
        $this->zzPromptErrorAndDie( 'errorFlexformApplicationUndefined' );
        break;
    }
  }

  /**
   * pluginCertification() :
   *
   * @return void
   * @access private
   * @version 7.0.0
   * @since 7.0.0
   */
  private function pluginCertification()
  {
    $this->pluginCertificationAuthentication();
    $this->pluginCertificationLiveOrSandbox();

    $sApplication = ''
            . $this->pluginCertificationHeader()
            . $this->pluginCertificationPrompt()
            . $this->pluginCertificationUsersFromDatabase()
    ;

    $this->pluginCertificationViewAssign( $sApplication );
  }

  /**
   * pluginCertificationAuthentication() :
   *
   * @return void
   * @access private
   * @version 7.0.0
   * @since 7.0.0
   */
  private function pluginCertificationAuthentication()
  {
    $sParamState = \TYPO3\CMS\Core\Utility\GeneralUtility::_GP( 'state' );
    if ( empty( $sParamState ) )
    {
      return;
    }

    $sAuthentication = $this->submitAuthentication();
    $this->submitDatabaseProperUid();

    $this->view->assign( 'authentication', $sAuthentication );

    return;
  }

  /**
   * pluginCertification() :
   *
   * @return void
   * @access private
   * @version 7.0.0
   * @since 7.0.0
   */
  private function pluginCertificationHeader()
  {
    return;
    $sHeader = ''
            . '<h2>'
            . '  ' . \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate( $this->locallangPath . 'frontendApplicationCertification', 'deal' )
            . '</h2>'
    ;

    return $sHeader;
  }

  /**
   * pluginCertificationLiveOrSandbox() :
   *
   * @return void
   * @access private
   * @version 7.0.0
   * @since 7.0.0
   */
  private function pluginCertificationLiveOrSandbox()
  {
    $this->viewAssignLiveOrSandbox();
  }

  /**
   * pluginCertificationPrompt() :
   *
   * @return void
   * @access private
   * @version 7.0.0
   * @since 7.0.0
   */
  private function pluginCertificationPrompt()
  {
    return;
    $prompt = null;
    $prompt = '<strong>Hinweis: Unter IE9 kann es zu Problemen mit der Zertifizierung kommen.</strong>
      <em>Der Benutzername sollte nach Möglichkeit gesetzt werden. Standardmäßig wird ansonsten "me" genommen. Somit können aber nicht mehrere User parallel in der Datenbank abgelegt werden. Der gewählte Benutzername muss der gleiche wie im Formular auf der nächsten Seite sein, damit der Token richtig zugewiesen werden kann.</em>'
    ;

    return $prompt;
  }

  /**
   * pluginCertificationUsersFromDatabase() :
   *
   * @return void
   * @access private
   * @version 7.0.0
   * @since 7.0.0
   */
  private function pluginCertificationUsersFromDatabase()
  {
    $prompt = null;

    $sUsers = $this->oImmocaster->getAllApplicationUsers( array( 'string' => true ) );

    if ( !empty( $sUsers ) )
    {
      $prompt = $this->pluginCertificationUsersFromDatabaseWiUsers( $sUsers );
      return $prompt;
    }

    $prompt = $this->pluginCertificationUsersFromDatabaseWoUsers();
    return $prompt;
  }

  /**
   * pluginCertificationUsersFromDatabaseWiUsers() :
   *
   * @return void
   * @access private
   * @version 7.0.0
   * @since 7.0.0
   */
  private function pluginCertificationUsersFromDatabaseWiUsers( $sUsers )
  {
    $aUsers = explode( ', ', $sUsers );
    asort( $aUsers );
    $sUsers = implode( '</li><li>', $aUsers );
    $sUsers = '<ul><li>' . $sUsers . '</li></ul>';
    $sRegistration = \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate( $this->locallangPath . 'frontendApplicationCertificationRegisteredYes', 'deal' );

    $prompt = '
        <div data-alert class="alert-box info">
          ' . $sRegistration . '
          <a href="#" class="close">&times;</a>
        </div>
      ';
    $prompt = str_replace( '%users%', $sUsers, $prompt );
    $prompt = str_replace( '%dbtable%', TYPO3_db . '.' . $this->sDatabaseTableCurrent, $prompt );

    if ( count( $aUsers ) <= 1 )
    {
      return $prompt;
    }

    $sMultiple = \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate( $this->locallangPath . 'frontendApplicationCertificationRegisteredMultiple', 'deal' );
    $prompt = $prompt . '
        <div data-alert class="alert-box alert">
          ' . $sMultiple . '
          <a href="#" class="close">&times;</a>
        </div>
      ';
    $prompt = str_replace( '%users%', $sUsers, $prompt );
    $prompt = str_replace( '%dbtable%', TYPO3_db . '.' . $this->sDatabaseTableCurrent, $prompt );
    return $prompt;
  }

  /**
   * pluginCertificationUsersFromDatabaseWoUsers() :
   *
   * @return void
   * @access private
   * @version 7.0.0
   * @since 7.0.0
   */
  private function pluginCertificationUsersFromDatabaseWoUsers()
  {
    $prompt = '
        <div data-alert class="alert-box info">
          Die Anwendung ist nicht registriert.
          Die Tabelle ' . TYPO3_db . '.' . $this->sDatabaseTableCurrent . ' ist leer.
          <a href="#" class="close">&times;</a>
        </div>
      ';
    $sRegistration = \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate( $this->locallangPath . 'frontendApplicationCertificationRegisteredNo', 'deal' );
    $prompt = '
        <div data-alert class="alert-box alert">
          ' . $sRegistration . '
          <a href="#" class="close">&times;</a>
        </div>
      ';
    $prompt = str_replace( '%dbtable%', TYPO3_db . '.' . $this->sDatabaseTableCurrent, $prompt );
    return $prompt;
  }

  /**
   * pluginCertificationViewAssign() :
   *
   * @return void
   * @access private
   * @version 7.0.0
   * @since 7.0.0
   */
  private function pluginCertificationViewAssign( $sApplication )
  {
    $sHeader = \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate( $this->locallangPath . 'frontendApplicationCertification', 'deal' );
    $sHeader = '<h2>' . $sHeader . '</h2>';
    $sLegend = \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate( $this->locallangPath . 'frontendApplicationFormLegend', 'deal' );
    $sPlaceholder = \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate( $this->locallangPath . 'frontendApplicationFormInputUserPlaceholder', 'deal' );
    $sSubmit = \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate( $this->locallangPath . 'frontendApplicationFormSubmit', 'deal' );
    $sUser = \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate( $this->locallangPath . 'frontendApplicationFormInputUserLabel', 'deal' );
    $this->view->assign( 'application', $sApplication );
    $this->view->assign( 'header', $sHeader );
    $this->view->assign( 'legend', $sLegend );
    $this->view->assign( 'placeholder', $sPlaceholder );
    $this->view->assign( 'pluginIsCertification', true );
    $this->view->assign( 'submit', $sSubmit );
    $this->view->assign( 'user', $sUser );
  }

  /**
   * pluginLogo() :
   *
   * @return void
   * @access private
   * @version 7.0.0
   * @since 7.0.0
   */
  private function pluginLogo()
  {
    if ( !$this->pluginLogoRequirements() )
    {
      return;
    }

    $this->pluginLogoLiveOrSandbox();

    // Get logo
    $aParameter = array( 'username' => $this->sImmo24User ); // Username hinterlegen (standardmäßig ihr Nutzername, der beim Login verwendet wird)
    $oLogo = $this->oImmocaster->getLogo( $aParameter );
    //var_dump( __METHOD__, __LINE__, $oLogo );
    $aImmo24TsProperties = $this->settings[ 'marketplaces' ][ 'immo24' ][ 'api' ][ 'properties' ];
    switch ( true )
    {
      case( $aImmo24TsProperties[ 'contentResultType' ] == 'none' ):
      case( $aImmo24TsProperties[ 'contentResultType' ] == 'xml' ):
        $this->pluginLogoXml( $oLogo );
        break;
      case( $aImmo24TsProperties[ 'contentResultType' ] == 'json' ):
      default:
        $this->pluginLogoJson( $oLogo );
        break;
    }
  }

  /**
   * pluginLogoJson() :
   *
   * @return void
   * @access private
   * @version 7.0.0
   * @since 7.0.0
   */
  private function pluginLogoJson( $oLogo )
  {
    $aLogo = json_decode( $oLogo, true );
    $sLogo = $this->pluginLogoJsonLogo( $aLogo );

    $sApplication = '
      <h2>
        ' . \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate( $this->locallangPath . 'frontendApplicationLogoHeader', 'deal' ) . '
      </h2>
      <div data-alert class="alert-box info">
        ' . \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate( $this->locallangPath . 'frontendApplicationLogoPrompt', 'deal' ) . '
        <a href="#" class="close">&times;</a>
      </div>
      <h3>
        ' . \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate( $this->locallangPath . 'frontendApplicationLogoImage', 'deal' ) . '
      </h3>
      <image src="' . $sLogo . '" title="My company logo" />
      <h3>
        ' . \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate( $this->locallangPath . 'frontendApplicationLogoUrl', 'deal' ) . '
      </h3>
      <a href="' . $sLogo . '" target="_blank" title="My company logo">' . $sLogo . '</a>
      <h3>
        ' . \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate( $this->locallangPath . 'frontendApplicationLogoCertificateHeader', 'deal' ) . '
      </h3>
      ' . \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate( $this->locallangPath . 'frontendApplicationLogoCertificatePrompt', 'deal' ) . ': ' . $this->sImmo24User . '
      ' . $this->pluginCertificationUsersFromDatabase() . '
      '
    ;

    $this->view->assign( 'application', $sApplication );
  }

  /**
   * pluginLogoJsonLogo() :
   *
   * @return void
   * @access private
   * @version 7.0.0
   * @since 7.0.0
   */
  private function pluginLogoJsonLogo( $aLogo )
  {
    $sLogo = null;

    $this->pluginLogoJsonLogoDie( $aLogo );

    $sLogo = $aLogo[ 'common.realtorLogo' ][ 'realtorLogoUrl' ];

    return $sLogo;
  }

  /**
   * pluginLogoJsonLogoDie() :
   *
   * @return void
   * @access private
   * @version 7.0.0
   * @since 7.0.0
   */
  private function pluginLogoJsonLogoDie( $aLogo )
  {
    //var_dump( __METHOD__, __LINE__, $aLogo );
    if ( !empty( $aLogo[ 'common.realtorLogo' ][ 'realtorLogoUrl' ] ) )
    {
      return;
    }

    $this->zzPromptErrorAndDie( 'errorNoLogo' );
  }

  /**
   * pluginLogoXml() :
   *
   * @return void
   * @access private
   * @version 7.0.0
   * @since 7.0.0
   */
  private function pluginLogoXml( $aLogo )
  {
    $this->pluginLogoXmlRegion();
  }

  /**
   * pluginLogoXmlRegion() :
   *
   * @return void
   * @access private
   * @version 7.0.0
   * @since 7.0.0
   */
  private function pluginLogoXmlRegion()
  {
    $this->pluginLogoXmlRegionDie();
  }

  /**
   * pluginLogoXmlRegionDie() :
   *
   * @return void
   * @access private
   * @version 7.0.0
   * @since 7.0.0
   */
  private function pluginLogoXmlRegionDie()
  {
//    if ( !empty( $aLogo[ 'region.regions' ][ '0' ] ) )
//    {
//      return;
//    }

    $this->zzPromptErrorAndDie( 'errorNoRegionXmlSupport' );
  }

  /**
   * pluginLogoLiveOrSandbox() :
   *
   * @return void
   * @access private
   * @version 7.0.0
   * @since 7.0.0
   */
  private function pluginLogoLiveOrSandbox()
  {
    $this->viewAssignLiveOrSandbox();
  }

  /**
   * pluginLogoRequirements() :
   *
   * @return void
   * @access private
   * @version 7.0.0
   * @since 7.0.0
   */
  private function pluginLogoRequirements()
  {
    // Get certificate
    //   return if no certificate
    if ( !$this->pluginLogoRequirementsCertificate() )
    {
      return false;
    }
    return true;
  }

  /**
   * pluginLogoRequirementsCertificate() :
   *
   * @return void
   * @access private
   * @version 7.0.0
   * @since 7.0.0
   */
  private function pluginLogoRequirementsCertificate()
  {
    $sFields = 'ic_username';
    //$sWhere = 'uid = 0 ' . BackendUtility::deleteClause( $this->sDatabaseTableCurrent );
    $sWhere = '1';
    $sLimit = '1';
    $res = $GLOBALS[ 'TYPO3_DB' ]->exec_SELECTquery(
            $sFields, $this->sDatabaseTableCurrent, $sWhere, null, null, $sLimit
    );
    $row = array();
    $row = $GLOBALS[ 'TYPO3_DB' ]->sql_fetch_assoc( $res );
    $GLOBALS[ 'TYPO3_DB' ]->sql_free_result( $res );

    if ( !empty( $row[ 'ic_username' ] ) )
    {
      $this->sImmo24User = $row[ 'ic_username' ];
      return true;
    }

    $this->pluginLogoRequirementsCertificateErrorPrompt();
    return false;
  }

  /**
   * pluginLogoRequirementsCertificate() :
   *
   * @return void
   * @access private
   * @version 7.0.0
   * @since 7.0.0
   */
  private function pluginLogoRequirementsCertificateErrorPrompt()
  {
    $sApplication = '
      <h2>
        ' . \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate( $this->locallangPath . 'frontendApplicationLogoHeader', 'deal' ) . '
      </h2>
      <div data-alert class="alert-box alert">
        ' . \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate( $this->locallangPath . 'frontendApplicationLogoPromptNoCertificate', 'deal' ) . '
      </div>
      <div data-alert class="alert-box info">
        ' . \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate( $this->locallangPath . 'frontendApplicationLogoPromptNoCertificateInfo', 'deal' ) . '
        ' . TYPO3_db . '.' . $this->sDatabaseTableCurrent . '.
        <a href="#" class="close">&times;</a>
      </div>
      '
    ;

    $this->view->assign( 'application', $sApplication );
    return false;
  }

  /**
   * pluginRegions() :
   *
   * @return void
   * @access private
   * @version 7.0.2
   * @since 7.0.0
   */
  private function pluginRegions()
  {
    if ( $this->pluginRegionsDebug() )
    {
      return;
    }

    $aImmo24TsProperties = $this->settings[ 'marketplaces' ][ 'immo24' ][ 'api' ][ 'properties' ];
    // #t0454, 150903, dwildt, 4+
    if ( !isset( $this->settings[ 'flexform' ][ 'application' ][ 'typeRegionSword' ] ) )
    {
      $this->settings[ 'flexform' ][ 'application' ][ 'typeRegionSword' ] = 'Berlin';
    }
    $sTypeRegionSword = $this->settings[ 'flexform' ][ 'application' ][ 'typeRegionSword' ];

    $this->pluginRegionsLiveOrSandbox();

    $aParameter = array( 'q' => $sTypeRegionSword );
    $oRegions = $this->oImmocaster->getRegions( $aParameter );
    //var_dump( __METHOD__, __LINE__, $oRegions );
    switch ( true )
    {
      case( $aImmo24TsProperties[ 'contentResultType' ] == 'none' ):
      case( $aImmo24TsProperties[ 'contentResultType' ] == 'xml' ):
        $this->pluginRegionsXml( $oRegions );
        break;
      case( $aImmo24TsProperties[ 'contentResultType' ] == 'json' ):
      default:
        $this->pluginRegionsJson( $oRegions );
        break;
    }
  }

  /**
   * pluginRegionsDebug() :
   *
   * @return void
   * @access private
   * @version 7.0.2
   * @since 7.0.0
   */
  private function pluginRegionsDebug()
  {
    $aImmo24TsProperties = $this->settings[ 'marketplaces' ][ 'immo24' ][ 'api' ][ 'properties' ];

    if ( !$aImmo24TsProperties[ 'requestDebug' ] )
    {
      return false;
    }

    // #t0454, 150903, dwildt, 4+
    if ( !isset( $this->settings[ 'flexform' ][ 'application' ][ 'typeRegionSword' ] ) )
    {
      $this->settings[ 'flexform' ][ 'application' ][ 'typeRegionSword' ] = 'Berlin';
    }
    $sTypeRegionSword = $this->settings[ 'flexform' ][ 'application' ][ 'typeRegionSword' ];
    $aParameter = array( 'q' => $sTypeRegionSword );
    $aDebug = $this->oImmocaster->getRegions( $aParameter );
    $sDebug = '<pre>' . var_export( $aDebug, true ) . '</pre>';
    $this->view->assign( 'application', $sDebug );

    return true;
    //exit();
  }

  /**
   * pluginRegionsJson() :
   *
   * @return void
   * @access private
   * @version 7.0.0
   * @since 7.0.0
   */
  private function pluginRegionsJson( $oRegions )
  {
    $aRegions = json_decode( $oRegions, true );

    $sTypeRegionSword = $this->settings[ 'flexform' ][ 'application' ][ 'typeRegionSword' ];
    $sApplication = ''
            . '<h2>'
            . '  ' . \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate( $this->locallangPath . 'frontendApplicationRegionWithSword', 'deal' )
            . '</h2>'
            . '<div data-alert class="alert-box info">'
            . '  ' . \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate( $this->locallangPath . 'frontendApplicationRegionPrompt', 'deal' )
            . '  <a href="#" class="close">&times;</a>'
            . '</div>'
            . '<ul>'
            . '  ' . $this->pluginRegionsJsonRegion( $aRegions )
            . '</ul>'
    ;
    $sApplication = str_replace( '%sword%', $sTypeRegionSword, $sApplication );

    $this->view->assign( 'application', $sApplication );
  }

  /**
   * pluginRegionsJsonRegion() :
   *
   * @return void
   * @access private
   * @version 7.0.0
   * @since 7.0.0
   */
  private function pluginRegionsJsonRegion( $aRegions )
  {
    $sRegions = null;

    $this->pluginRegionsJsonRegionDie( $aRegions );

    foreach ( ( array ) $aRegions[ 'region.regions' ][ '0' ][ 'region' ] as $aRegion )
    {
      $sRegions = $sRegions
              . '<li>'
              . $aRegion[ 'name' ]
              . '</li>'
      ;
    }

    return $sRegions;
  }

  /**
   * pluginRegionsJsonRegionDie() :
   *
   * @return void
   * @access private
   * @version 7.0.0
   * @since 7.0.0
   */
  private function pluginRegionsJsonRegionDie( $aRegions )
  {
    if ( !empty( $aRegions[ 'region.regions' ][ '0' ] ) )
    {
      return;
    }

    $this->zzPromptErrorAndDie( 'errorNoRegion' );
  }

  /**
   * pluginRegionsLiveOrSandbox() :
   *
   * @return void
   * @access private
   * @version 7.0.0
   * @since 7.0.0
   */
  private function pluginRegionsLiveOrSandbox()
  {
    $this->viewAssignLiveOrSandbox();
  }

  /**
   * pluginRegionsXml() :
   *
   * @return void
   * @access private
   * @version 7.0.0
   * @since 7.0.0
   */
  private function pluginRegionsXml( $aRegions )
  {
    $this->pluginRegionsXmlRegion();
  }

  /**
   * pluginRegionsXmlRegion() :
   *
   * @return void
   * @access private
   * @version 7.0.0
   * @since 7.0.0
   */
  private function pluginRegionsXmlRegion()
  {
    $this->pluginRegionsXmlRegionDie();
  }

  /**
   * pluginRegionsXmlRegionDie() :
   *
   * @return void
   * @access private
   * @version 7.0.0
   * @since 7.0.0
   */
  private function pluginRegionsXmlRegionDie()
  {
//    if ( !empty( $aRegions[ 'region.regions' ][ '0' ] ) )
//    {
//      return;
//    }

    $this->zzPromptErrorAndDie( 'errorNoRegionXmlSupport' );
  }

  /**
   * submitAction() : form for certificate a user on immobilienscout24
   *
   * @return void
   * @access public
   * @version 7.0.0
   * @since 7.0.0
   */
  public function submitAction()
  {
    $this->init();
    $this->submit();
  }

  /**
   * submit() : form for certificate a user on immobilienscout24
   *
   * @return void
   * @access private
   * @version 7.0.0
   * @since 7.0.0
   */
  private function submit()
  {
    $this->submitRequirements();
    $sAuthentication = $this->submitAuthentication();
    $this->submitDatabaseProperUid();

    $this->view->assign( 'authentication', $sAuthentication );
  }

  /**
   * submitAuthentication() : form for certificate a user on immobilienscout24
   *
   * @return void
   * @access private
   * @version 7.0.0
   * @since 7.0.0
   */
  private function submitAuthentication()
  {
    $sAuthentication = null;

    $aParameter = array(
      'callback_url' => $this->submitAuthenticationCallbackUrl(),
      'verifyApplication' => true
    );

    $returnAuthentication = $this->oImmocaster->getAccess( $aParameter );
    if ( $returnAuthentication === true )
    {
      $sAuthentication = '
        <div data-alert class="alert-box success">'
              . \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate( $this->locallangPath . 'successCertificate', 'deal' ) .
              '<a href="#" class="close">&times;</a>
        </div>
      ';
    }
    elseif ( is_array( $returnAuthentication ) && count( $returnAuthentication ) > 1 )
    {
      $sAuthentication = '
        <div data-alert class="alert-box success">'
              . \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate( $this->locallangPath . 'successCertificates', 'deal' ) .
              '<br />'
              . implode( ',', $returnAuthentication ) . '
          <a href="#" class="close">&times;</a>
        </div>
      ';
    }
    else
    {
      $sAuthentication = '
        <div data-alert class="alert-box alert">'
              . \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate( $this->locallangPath . 'errorCertificate', 'deal' ) .
              '<a href="#" class="close">&times;</a>
        </div>
      ';
    }
    return $sAuthentication;
  }

  /**
   * submit() : form for certificate a user on immobilienscout24
   *
   * @return void
   * @access private
   * @version 7.0.0
   * @since 7.0.0
   */
  private function submitAuthenticationCallbackUrl()
  {
    $aParsedQueryString = array();

    $sRequestScript = \TYPO3\CMS\Core\Utility\GeneralUtility::getIndpEnv( 'TYPO3_REQUEST_SCRIPT' );
    $sQueryString = \TYPO3\CMS\Core\Utility\GeneralUtility::getIndpEnv( 'QUERY_STRING' );

    $aQueryString = explode( '&', $sQueryString );

    // Remove non wanted params
    foreach ( $aQueryString as $aParam )
    {
      list( $key, $value) = explode( '=', $aParam );
      switch ( $key )
      {
        case 'tx_deal_pi1%5Baction%5D':
        case 'tx_deal_pi1%5Bcontroller%5D':
        case 'cHash':
          continue;
        default:
          $aParsedQueryString[] = $key . '=' . $value;
      }
    }

    $sParsedQueryString = implode( '&', ( array ) $aParsedQueryString );
    switch ( true )
    {
      case(!empty( $sParsedQueryString )):
        $sCertifyURL = $sRequestScript . '?' . $sParsedQueryString;
        break;
      default:
        $sCertifyURL = $sRequestScript;
        break;
    }

    $pos = strpos( $sCertifyURL, '?' );
    if ( $pos === false )
    {
      $sCallbackUrl = $sCertifyURL . '?';
    }
    else
    {
      $sCallbackUrl = $sCertifyURL . '&';
    }
    $aParams = $this->request->getArguments();
    $sCallbackUrl = $sCallbackUrl . 'user=' . $aParams[ 'user' ];

    return $sCallbackUrl;
  }

  /**
   * submit() : form for certificate a user on immobilienscout24
   *
   * @return void
   * @access private
   * @version 7.0.0
   * @since 7.0.0
   */
  private function submitDatabaseProperUid()
  {
    $this->initDatabaseProperUid();
  }

  /**
   * submitRequirements() : form for certificate a user on immobilienscout24
   *
   * @return void
   * @access private
   * @version 7.0.0
   * @since 7.0.0
   */
  private function submitRequirements()
  {
    $aParams = $this->request->getArguments();
    if ( empty( $aParams[ 'user' ] ) )
    {
      $this->zzPromptErrorAndDie( 'errorFormInputNoUser' );
    }
  }

  /**
   * viewAssignLiveOrSandbox() :
   *
   * @return void
   * @access private
   * @version 7.0.0
   * @since 7.0.0
   */
  private function viewAssignLiveOrSandbox()
  {
    switch ( true )
    {
      case( $this->bImmo24Sandbox === FALSE):
        $label = 'live';
        break;
      case( $this->bImmo24Sandbox === TRUE):
        $label = 'sandbox';
        break;
      case( $this->bImmo24Sandbox === NULL):
        $this->zzPromptErrorAndDie( 'errorRequestUrlUndefined' );
        break;
    }
    $liveorsandbox = '<span class="label">' . $label . '</span>';
    $this->view->assign( 'liveorsandbox', $liveorsandbox );
  }

  /**
   * zzPromptErrorAndDie() :
   *
   * @return void
   * @access private
   * @version 7.0.0
   * @since 7.0.0
   */
  private function zzPromptErrorAndDie( $sKey, $aNeedle = array(), $aReplace = array() )
  {
    $prompt = ''
            . '<div style="border:1em solid red;padding:1em;text-align:center;">'
            . '<h1 style="color:red;">'
            . '  ' . \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate( $this->locallangPath . $sKey . 'Header', 'deal' )
            . '</h1>'
            . '<p style="color:red;">'
            . '  ' . \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate( $this->locallangPath . $sKey . 'Prompt', 'deal' )
            . '</p>'
            . '<p style="color:red;">'
            . '  Method: ' . __METHOD__ . ' at line ' . __LINE__
            . '</p>'
            . '<p style="color:red;">'
            . '  Sorry for the trouble. TYPO3 Deal!'
            . '</p>'
            . '</div>'
    ;
    $prompt = str_replace( $aNeedle, $aReplace, $prompt );
    die( $prompt );
  }

  /**
   * zzCurlCheckBasicFunctions() :
   *
   * @return void
   * @access private
   * @internal #t0456
   * @version 7.1.0
   * @since 7.1.0
   */
  private function zzCurlCheckBasicFunctions()
  {

    switch ( TRUE )
    {
      case(!function_exists( "curl_exec" ) ):
      case(!function_exists( "curl_close" ) ):
      case(!function_exists( "curl_init" ) ):
      case(!function_exists( "curl_setopt" ) ):
        return false;
      default:
        // follow the workflow
        break;
    }

    return true;
//    $ch = curl_init();
//    curl_setopt( $ch, CURLOPT_URL, "example.com" );
//    curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
//    $output = curl_exec( $ch );
//    curl_close( $ch );
//    echo $output;
//    die();
  }

}
