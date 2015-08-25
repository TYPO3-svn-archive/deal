<?php

namespace Netzmacher\Deal\Scheduler;

use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

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
 * Task for Scheduler task
 *
 * @package deal
 * @license http://www.gnu.org/licenses/lgpl.html
 * 			GNU Lesser General Public License, version 3 or later
 * @version 7.0.0
 * @since 7.0.0
 */
class Immo24Task extends \Netzmacher\Deal\Scheduler\Immo24TaskExecute
{

  /**
   * An email address to be used during the process
   *
   * @var string $aAdminEmailPrompts
   */
  private $_aAdminEmailPrompts;

  /**
   * Logging array. Structure: id, channel
   *
   * @access private
   * @var array $_aLog
   */
  private $_aLog = array();

  /**
   * An email address to be used during the process
   *
   * @var string $aStatistic
   */
  private $_aStatistic;

  /**
   * Extension label
   *
   * @access private
   * @var string $_extLabel
   */
  private $_extLabel = 'Deal!';

  /**
   * The is24-sdk object Path to locallang file (with : as postfix)
   *
   * @var object
   */
  private $_oImmocaster;

  /**
   * @var string
   */
  private $_sImmo24KeyPublic = NULL;

  /**
   * @var string
   */
  private $_sImmo24KeyPrivate = NULL;

  /**
   * @var string
   */
  private $_sImmo24TableCertificate = NULL;

  /**
   * @var string
   */
  private $_sImmo24User = NULL;

  /**
   * An email address to be used during the process
   *
   * @var string $deal_adminemail
   */
  public $deal_adminemail;

  /**
   * Mode for clean up the is24 database
   *
   * @var string $deal_cleanupimmo24
   */
  public $deal_cleanupimmo24;

  /**
   * An email address to be used during the process
   *
   * @var string $deal_adminemail
   */
  public $deal_adminemailmode;

  /**
   * Private live key
   *
   * @var string $deal_keyliveprivate
   */
  public $deal_keyliveprivate;

  /**
   * Public live key
   *
   * @var string $deal_keylivepublic
   */
  public $deal_keylivepublic;

  /**
   * Private sandbox key
   *
   * @var string $deal_keysandboxprivate
   */
  public $deal_keysandboxprivate;

  /**
   * Public sandbox key
   *
   * @var string $deal_keysandboxpublic
   */
  public $deal_keysandboxpublic;

  /**
   * Mode: live or sandbox
   *
   * @var string $deal_liveorsandbox
   */
  public $deal_liveorsandbox;

  /**
   * Label of the table, which contains the offers for appartments for rent
   *
   * @var string $deal_tableappartmentrent
   */
  public $deal_tableappartmentrent;

  /**
   * Label of the table, which contains the offers for appartments for rent
   *
   * @var string $deal_tableappartmentrent
   */
  public $deal_tablecontact;

  /**
   * Current task uid
   *
   * @var string $taskUid
   */
  public $taskUid;

  /**
   * _email() :
   *
   * @return boolean
   * @access private
   * @version 7.0.0
   * @since 7.0.0
   */
  private function _email()
  {
    $sTaskLabel = 'IS24-TASK';

    if ( $this->_emailErrorEmpty( $sTaskLabel ) )
    {
      return FALSE;
    }

    $this->_emailPromptMode( $sTaskLabel );

    switch ( $this->deal_adminemailmode )
    {
      case( 'nothing' ):
        return TRUE;
      case( 'all' ):
      case( 'update' ):
      case( 'warn' ):
        // follow the workflow
        break;
      default:
        return FALSE;
    }

    $prompt = $sTaskLabel . ': email will try to send to "' . $this->deal_adminemail . '"';
    $GLOBALS[ 'BE_USER' ]->simplelog( $prompt, 'deal', 0 );

    // Get execution information
    $exec = $this->getExecution();

    // Get call method
    if ( basename( PATH_thisScript ) == 'cli_dispatch.phpsh' )
    {
      $calledBy = 'CLI module dispatcher';
      $site = '-';
    }
    else
    {
      $calledBy = 'TYPO3 backend';
      $site = GeneralUtility::getIndpEnv( 'TYPO3_SITE_URL' );
    }

    $sSubjectPrefix = $this->_emailSubjectPrefix();

    $start = $exec->getStart();
    $end = $exec->getEnd();
    $interval = $exec->getInterval();
    $multiple = $exec->getMultiple();
    $cronCmd = $exec->getCronCmd();
    $mailBody = 'DEAL! ' . $sTaskLabel . LF
            . '' . LF
            . 'o REPORT' . LF
            . 'o STATISTIC' . LF
            . '  o TYPO3' . LF
            . '  o IMMOBILIENSCOUT 24' . LF
            . 'o TECHNICAL DETAILS' . LF
            . '' . LF
            . '- - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -' . LF
            . 'REPORT' . LF
            . $this->_emailReport() . LF
            . '' . LF
            . '- - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -' . LF
            . 'STATISTIC' . LF
            . $this->_emailStatistic() . LF
            . '' . LF
            . '- - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -' . LF
            . '+++ TECHNICAL DETAILS' . LF
            . 'UID: ' . $this->taskUid . LF
            . 'Sitename: ' . $GLOBALS[ 'TYPO3_CONF_VARS' ][ 'SYS' ][ 'sitename' ] . LF
            . 'Site: ' . $site . LF
            . 'Called by: ' . $calledBy . LF
            . 'tstamp: ' . date( 'Y-m-d H:i:s' ) . ' [' . time() . ']' . LF
            . 'maxLifetime: ' . $this->scheduler->extConf[ 'maxLifetime' ] . LF
            . 'start: ' . date( 'Y-m-d H:i:s', $start ) . ' [' . $start . ']' . LF
            . 'end: ' . (empty( $end ) ? '-' : date( 'Y-m-d H:i:s', $end ) . ' [' . $end . ']') . LF
            . 'interval: ' . $interval . LF
            . 'multiple: ' . ($multiple ? 'yes' : 'no') . LF
            . 'cronCmd: ' . ($cronCmd ? $cronCmd : 'not used')
    ;

    // Prepare mailer and send the mail
    try
    {
      /** @var $mailer \TYPO3\CMS\Core\Mail\MailMessage */
      $mailer = GeneralUtility::makeInstance( 'TYPO3\\CMS\\Core\\Mail\\MailMessage' );
      $mailer->setFrom( array( $this->deal_adminemail => 'DEAL! ' . $sTaskLabel ) );
      $mailer->setReplyTo( array( $this->deal_adminemail => 'DEAL! ' . $sTaskLabel ) );
      $mailer->setSubject( $sSubjectPrefix . 'DEAL! ' . $sTaskLabel );
      $mailer->setBody( $mailBody );
      $mailer->setTo( $this->deal_adminemail );
      $iMailsSend = $mailer->send();
    }
    catch ( \Exception $e )
    {
      throw new \TYPO3\CMS\Core\Exception( $e->getMessage() );
    }

    return $this->_emailPromptResult( $iMailsSend );
  }

  /**
   * _emailErrorEmpty() :
   *
   * @return boolean
   * @access private
   * @version 7.0.0
   * @since 7.0.0
   */
  private function _emailErrorEmpty( $sTaskLabel )
  {
    if ( !empty( $this->deal_adminemail ) )
    {
      return FALSE;
    }

    $prompt = 'FATAL ERROR: e-mail address is missing.';
    $this->_zzFlashMessage( 'Deal! ' . $prompt, 'ERROR' );
    $GLOBALS[ 'BE_USER' ]->simplelog( $sTaskLabel . ': ' . $prompt, 'deal', 2 );
    return TRUE;
  }

  /**
   * _emailPromptMode() :
   *
   * @param integer $iMailsSend number of sended e-mails
   * @return boolean
   * @access private
   * @version 7.0.0
   * @since 7.0.0
   */
  private function _emailPromptMode( $sTaskLabel )
  {
    switch ( $this->deal_adminemailmode )
    {
      case( 'all' ):
        $prompt = $sTaskLabel . ': E-mail mode is "send all". E-mail will send to "' . $this->deal_adminemail . '"';
        $GLOBALS[ 'BE_USER' ]->simplelog( $prompt, 'deal', 0 );
        break;
      case( 'nothing' ):
        $prompt = $sTaskLabel . ': E-mail mode is disabled. Any e-mail won\'t send to "' . $this->deal_adminemail . '"';
        $GLOBALS[ 'BE_USER' ]->simplelog( $prompt, 'deal', 0 );
        break;
      case( 'update' ):
        $prompt = $sTaskLabel . ': E-mail mode is "send in case of update". E-mail will send to "' . $this->deal_adminemail . '"';
        $GLOBALS[ 'BE_USER' ]->simplelog( $prompt, 'deal', 0 );
        break;
      case( 'warn' ):
        $prompt = $sTaskLabel . ': E-mail mode is "send in case of warnings and erros". E-mail will send to "' . $this->deal_adminemail . '"';
        $GLOBALS[ 'BE_USER' ]->simplelog( $prompt, 'deal', 0 );
        break;
      default:
        $prompt = $sTaskLabel . ': FATAL ERROR: E-mail mode is undefined. Any e-mail won\'t send to "' . $this->deal_adminemail . '"';
        $GLOBALS[ 'BE_USER' ]->simplelog( $prompt, 'deal', 2 );
        $this->_zzFlashMessage( $prompt, 'ERROR' );
        break;
    }
  }

  /**
   * _emailPromptResult() :
   *
   * @param integer $iMailsSend number of sended e-mails
   * @return boolean
   * @access private
   * @version 7.0.0
   * @since 7.0.0
   */
  private function _emailPromptResult( $iMailsSend )
  {
    $this->_emailPromptResultError( $iMailsSend );
    $this->_emailPromptResultSuccess( $iMailsSend );

    return $iMailsSend > 0;
  }

  /**
   * _emailPromptResultError() :
   *
   * @param integer $iMailsSend number of sended e-mails
   * @return boolean
   * @access private
   * @version 7.0.0
   * @since 7.0.0
   */
  private function _emailPromptResultError( $iMailsSend )
  {
    if ( $iMailsSend > 0 )
    {
      return;
    }

    $prompt = 'Deal! Everything seem\'s to be proper but any e-mail isn\'t sent to: ' . $this->deal_adminemail . '<br />' . LF
            . 'Maybe you can\'t send e-mails from the current server.<br />' . LF
            . 'In this case please set<br />' . LF
            . '$TYPO3_CONF_VARS[MAIL][transport] = mbox <br />' . LF
            . '$TYPO3_CONF_VARS[MAIL][transport_mbox_file] = /var/mail/yourname <- path is an example'
    ;
    $this->_zzFlashMessage( $prompt, 'ERROR' );
  }

  /**
   * _emailPromptResultSuccess() :
   *
   * @param
   * @return boolean
   * @access private
   * @version 7.0.0
   * @since 7.0.0
   */
  private function _emailPromptResultSuccess( $iMailsSend )
  {
    global $TYPO3_CONF_VARS;

    if ( $iMailsSend <= 0 )
    {
      return;
    }

    $prompt = 'Deal! An e-mail is sent to: ' . $this->deal_adminemail;
    $this->_zzFlashMessage( $prompt, 'OK' );

    if ( $TYPO3_CONF_VARS[ 'MAIL' ][ 'transport' ] != 'mbox' )
    {
      return;
    }

    $prompt = 'Deal! The e-mail hasn\'t left your local server. It is stored in your mbox at ' . $TYPO3_CONF_VARS[ 'MAIL' ][ 'transport_mbox_file' ];
    $GLOBALS[ 'BE_USER' ]->simplelog( $prompt, 'deal', 0 );
    $this->_zzFlashMessage( $prompt, 'INFO' );
  }

  /**
   * _emailReport() :
   *
   * @return boolean
   * @access private
   * @version 7.0.0
   * @since 7.0.0
   */
  private function _emailReport()
  {
    $sReport = null;
    foreach ( ( array ) $this->_aAdminEmailPrompts as $sSeverity => $aPrompts )
    {
      $sReport = $sReport . LF
              . '+++ ' . strtoupper( $sSeverity ) . LF
              . implode( LF, $aPrompts )
      ;
    }
    return $sReport;
  }

  /**
   * _emailStatistic() :
   *
   * @return boolean
   * @access private
   * @version 7.0.0
   * @since 7.0.0
   */
  private function _emailStatistic()
  {
    // see $this->_initStatistic();
    // added || updated
    $sChannelNew = NULL;
//$this->_aStatistic[ 'channel' ][ $mode ][ $iChannel ][ 'disabled' ];
    foreach ( ( array ) $this->_aStatistic[ 'channel' ][ 'added' ] as $iChannel => $aStatus )
    {
//      var_dump( __METHOD__, __LINE__, 'new: ' . $iChannel . ' disabled: ' . $aStatus[ 'disabled' ] );
//      var_dump( __METHOD__, __LINE__, 'new: ' . $iChannel . ' enabled: ' . $aStatus[ 'enabled' ] );
      $sChannelNew = $sChannelNew . '    disabled (' . $iChannel . ')                    ' . sprintf( "%'.5d", $aStatus[ 'disabled' ] ) . LF;
      $sChannelNew = $sChannelNew . '    enabled  (' . $iChannel . ')                    ' . sprintf( "%'.5d", $aStatus[ 'enabled' ] ) . LF;
    }
    foreach ( ( array ) $this->_aStatistic[ 'channel' ][ 'updated' ] as $iChannel => $aStatus )
    {
//      var_dump( __METHOD__, __LINE__, 'updated: ' . $iChannel . ' disabled: ' . $aStatus[ 'disabled' ] );
//      var_dump( __METHOD__, __LINE__, 'updated: ' . $iChannel . ' enabled: ' . $aStatus[ 'enabled' ] );
      $sChannelUpdated = $sChannelUpdated . '    disabled (' . $iChannel . ')                    ' . sprintf( "%'.5d", $aStatus[ 'disabled' ] ) . LF;
      $sChannelUpdated = $sChannelUpdated . '    enabled  (' . $iChannel . ')                    ' . sprintf( "%'.5d", $aStatus[ 'enabled' ] ) . LF;
    }
    $sStatistic = ''
            . LF
            . '=============================================' . LF
            . 'TYPO3                                        ' . LF
            . '=============================================' . LF
            . 'All appartments for rent ...............' . sprintf( "%'.5d", $this->_aStatistic[ 'TYPO3' ][ $this->deal_tableappartmentrent ][ 'records' ][ 'all' ] ) . LF
            . '  no need for an update                 ' . sprintf( "%'.5d", $this->_aStatistic[ 'TYPO3' ][ $this->deal_tableappartmentrent ][ 'records' ][ 'noneedforupdate' ] ) . LF
            . 'All contacts ...........................' . sprintf( "%'.5d", $this->_aStatistic[ 'TYPO3' ][ $this->deal_tablecontact ][ 'records' ][ 'all' ] ) . LF
            . '  no need for an update                 ' . sprintf( "%'.5d", $this->_aStatistic[ 'TYPO3' ][ $this->deal_tablecontact ][ 'records' ][ 'noneedforupdate' ] ) . LF
            . LF
            . '=============================================' . LF
            . 'IMMOBILIENSCOUT 24                           ' . LF
            . '=============================================' . LF
            . 'All appartments for rent ...............' . sprintf( "%'.5d", $this->_aStatistic[ 'immo24' ][ $this->deal_tableappartmentrent ][ 'records' ][ 'all' ][ 'all' ] ) . LF
            . '  error                                 ' . sprintf( "%'.5d", $this->_aStatistic[ 'immo24' ][ $this->deal_tableappartmentrent ][ 'records' ][ 'all' ][ 'error' ] ) . LF
            . '  no need for an update                 ' . sprintf( "%'.5d", $this->_aStatistic[ 'immo24' ][ $this->deal_tableappartmentrent ][ 'records' ][ 'all' ][ 'noneedforupdate' ] ) . LF
            . '  success                               ' . sprintf( "%'.5d", $this->_aStatistic[ 'immo24' ][ $this->deal_tableappartmentrent ][ 'records' ][ 'all' ][ 'success' ] ) . LF
            . '  New . . . . . . . . . . . . . . . . . ' . sprintf( "%'.5d", $this->_aStatistic[ 'immo24' ][ $this->deal_tableappartmentrent ][ 'records' ][ 'added' ][ 'all' ] ) . LF
            . $sChannelNew
            . '    error                               ' . sprintf( "%'.5d", $this->_aStatistic[ 'immo24' ][ $this->deal_tableappartmentrent ][ 'records' ][ 'added' ][ 'error' ] ) . LF
            . '  Removed . . . . . . . . . . . . . . . ' . sprintf( "%'.5d", $this->_aStatistic[ 'immo24' ][ $this->deal_tableappartmentrent ][ 'records' ][ 'removed' ][ 'all' ] ) . LF
            . '    error                               ' . sprintf( "%'.5d", $this->_aStatistic[ 'immo24' ][ $this->deal_tableappartmentrent ][ 'records' ][ 'removed' ][ 'error' ] ) . LF
            . '    success                             ' . sprintf( "%'.5d", $this->_aStatistic[ 'immo24' ][ $this->deal_tableappartmentrent ][ 'records' ][ 'removed' ][ 'success' ] ) . LF
            . '  Updated . . . . . . . . . . . . . . . ' . sprintf( "%'.5d", $this->_aStatistic[ 'immo24' ][ $this->deal_tableappartmentrent ][ 'records' ][ 'updated' ][ 'all' ] ) . LF
            . $sChannelUpdated
            . '    error                               ' . sprintf( "%'.5d", $this->_aStatistic[ 'immo24' ][ $this->deal_tableappartmentrent ][ 'records' ][ 'updated' ][ 'error' ] ) . LF
            . 'All contacts ...........................' . sprintf( "%'.5d", $this->_aStatistic[ 'immo24' ][ $this->deal_tablecontact ][ 'records' ][ 'all' ][ 'all' ] ) . LF
            . '  error                                 ' . sprintf( "%'.5d", $this->_aStatistic[ 'immo24' ][ $this->deal_tablecontact ][ 'records' ][ 'all' ][ 'error' ] ) . LF
            . '  new                                   ' . sprintf( "%'.5d", $this->_aStatistic[ 'immo24' ][ $this->deal_tablecontact ][ 'records' ][ 'all' ][ 'added' ] ) . LF
            . '  no need for an update                 ' . sprintf( "%'.5d", $this->_aStatistic[ 'immo24' ][ $this->deal_tablecontact ][ 'records' ][ 'all' ][ 'noneedforupdate' ] ) . LF
            . '  removed                               ' . sprintf( "%'.5d", $this->_aStatistic[ 'immo24' ][ $this->deal_tablecontact ][ 'records' ][ 'all' ][ 'removed' ] ) . LF
            . '  updated                               ' . sprintf( "%'.5d", $this->_aStatistic[ 'immo24' ][ $this->deal_tablecontact ][ 'records' ][ 'all' ][ 'updated' ] ) . LF
    ;
    return $sStatistic;
  }

  /**
   * _emailSubjectPrefix() :
   *
   * @return boolean
   * @access private
   * @version 7.0.0
   * @since 7.0.0
   */
  private function _emailSubjectPrefix()
  {
    switch ( TRUE )
    {
      case( isset( $this->_aAdminEmailPrompts[ 'error' ] ) ):
        return '[ERROR] ';
      case( isset( $this->_aAdminEmailPrompts[ 'warning' ] ) ):
        return '[WARNING] ';
      case( isset( $this->_aAdminEmailPrompts[ 'info' ] ) ):
      case( isset( $this->_aAdminEmailPrompts[ 'notice' ] ) ):
      case( isset( $this->_aAdminEmailPrompts[ 'ok' ] ) ):
        return '[SUCCESS] ';
      default:
        return '[ERROR: undefined subject prefix!] ';
    }
  }

  /**
   * _immo24Export( ) :
   *
   * @return boolean
   * @access private
   * @version 7.0.0
   * @since 7.0.0
   */
  private function _immo24Export()
  {
    $_bSuccess = TRUE;

    if ( !$this->_immo24ExportContact() )
    {
      $_bSuccess = FALSE;
    }
//    var_dump( __METHOD__, __LINE__ );
//    return FALSE;

    if ( !$this->_immo24ExportAppartmentrent() )
    {
      $_bSuccess = FALSE;
    }

    return $_bSuccess;
  }

  /**
   * _immo24ExportAppartmentrent( ) :
   *
   * @return boolean
   * @access private
   * @version 7.0.0
   * @since 7.0.0
   */
  private function _immo24ExportAppartmentrent()
  {
    if ( !$this->_immo24ExportAppartmentrentRequirements() )
    {
      return FALSE;
    }

    $rows = $this->_immo24ExportAppartmentrentItems();
    if ( isset( $rows[ 'error' ] ) )
    {
      return FALSE;
    }

    if ( $this->_immo24RemoveAppartmentrent( $rows, 'beforeExport' ) )
    {
      return TRUE;
    }

    if ( !$this->_immo24ExportAppartmentrentLoop( $rows ) )
    {
      return FALSE;
    }
//    $telefon = '+49 30 123456';
//    $teile = preg_split( "/[\s]+/", $telefon );
//    var_dump( __METHOD__, __LINE__, $telefon, $teile, $this->taskUid, $this->deal_adminemail );

    if ( $this->_immo24RemoveAppartmentrent( $rows, 'afterExport' ) )
    {
      return TRUE;
    }

    return TRUE;
  }

  /**
   * _immo24ExportAppartmentrentAttachments( ) :
   *
   * @return boolean
   * @access private
   * @version 7.0.0
   * @since 7.0.0
   */
  private function _immo24ExportAppartmentrentAttachments( $row, $immo24id )
  {
    $this->_immo24ExportAttachmentsRemove( $row, $immo24id );
    $this->_immo24ExportAttachmentsAdd( $row, $immo24id );

    return TRUE;
  }

  /**
   * _immo24ExportAppartmentrentItems( ) :
   *
   * @return array $rows
   * @access private
   * @version 7.0.0
   * @since 7.0.0
   */
  private function _immo24ExportAppartmentrentItems()
  {
    $rows = $this->_immo24ExportAppartmentrentItemsRows();

    return $rows;
  }

  /**
   * _immo24ExportAppartmentrentItemsRows( ) :
   *
   * @return array $rows
   * @access private
   * @version 7.0.0
   * @since 7.0.0
   */
  private function _immo24ExportAppartmentrentItemsRows()
  {
    $sFields = '*';
    $sTable = $this->deal_tableappartmentrent;
    $andWhere = $this->_typo3GetImmo24CtrlSql( $sTable, 'andWhere' );
    $sWhere = '1 ' . BackendUtility::deleteClause( $sTable ) . $andWhere;
    $query = $GLOBALS[ 'TYPO3_DB' ]->SELECTquery(
            $sFields, $sTable, $sWhere
    );
    $res = $GLOBALS[ 'TYPO3_DB' ]->exec_SELECTquery(
            $sFields
            , $sTable
            , $sWhere
    );
    $rows = array();

    while ( ($row = $GLOBALS[ 'TYPO3_DB' ]->sql_fetch_assoc( $res ) ) )
    {
      $rows[ $row[ 'uid' ] ] = $row;
    }

    if ( $this->_zzPromptErrorSql( $query ) )
    {
      $rows = array(
        'error' => true
      );
      return $rows;
    }

    $GLOBALS[ 'TYPO3_DB' ]->sql_free_result( $res );

    return $rows;
  }

  /**
   * _immo24ExportAppartmentrentLoop( ) :
   *
   * @param array $rows
   * @return boolean
   * @access private
   * @version 7.0.0
   * @since 7.0.0
   */
  private function _immo24ExportAppartmentrentLoop( $rows )
  {
    $table = $this->deal_tableappartmentrent;

    $sImmo24Id = $this->_typo3GetImmo24CtrlField( $table, 'immo24id' );
    if ( $sImmo24Id == 'error' )
    {
      return FALSE;
    }

    $immo24Fields = $this->_typo3GetImmo24Fields( $table );
    if ( $immo24Fields == 'error' )
    {
      return FALSE;
    }

    foreach ( ( array ) $rows as $row )
    {
      $this->_aStatistic[ 'immo24' ][ $table ][ 'records' ][ 'all' ][ 'all' ] ++;
      $this->_aStatistic[ 'TYPO3' ][ $table ][ 'records' ][ 'all' ] ++;
      if ( $this->_zzIsUptodate( $table, $row ) )
      {
        continue;
      }
      switch ( TRUE )
      {
        case(empty( $row[ $sImmo24Id ] )):
          $this->_immo24ExportAppartmentrentLoopAdd( $row, $immo24Fields );
          break;
        case(!empty( $row[ $sImmo24Id ] )):
        default:
          $this->_immo24ExportAppartmentrentLoopUpdate( $row, $immo24Fields );
          break;
      }
    }
    return TRUE;
  }

  /**
   * _immo24ExportAppartmentrentLoopAdd( ) :
   *
   * @param array $row
   * @param array $immo24Fields
   * @return boolean
   * @access private
   * @version 7.0.0
   * @since 7.0.0
   */
  private function _immo24ExportAppartmentrentLoopAdd( $row, $immo24Fields )
  {
    $level = 0;
    $xml = null;
    $table = $this->deal_tableappartmentrent;


    $this->_aStatistic[ 'immo24' ][ $table ][ 'records' ][ 'added' ][ 'all' ] ++;

    foreach ( ( array ) $immo24Fields as $sImmo24Field => $aImmo24Field )
    {
      $level++;
      $xml = $xml . $this->_zzImmo24Xml( $table, $row, $aImmo24Field, $sImmo24Field, $level );
      $level--;
    }

    $xml = '<?xml version="1.0" encoding="utf-8"?>
<realestates:apartmentRent xmlns:xlink="http://www.w3.org/1999/xlink" xmlns:realestates="http://rest.immobilienscout24.de/schema/offer/realestates/1.0">
' . $xml . '</realestates:apartmentRent>';

    $aParams = array(
      'username' => $this->_sImmo24User,
      'service' => 'immobilienscout',
      'estate' => array(
        'xml' => $xml,
      ),
    );

    $sResponse = $this->_oImmocaster->exportObject( $aParams );
    $method = __METHOD__ . ' (#' . __LINE__ . ')';
    $immo24id = $this->_immo24ValidationResponse( $row, $sResponse, $method, $xml );
    //var_dump( __METHOD__, __LINE__, $immo24id );
    if ( $immo24id === TRUE )
    {
      $immo24id = NULL;
    }
    switch ( TRUE )
    {
      case ( ( string ) $immo24id !== ( string ) ( int ) $immo24id ):
      case ( empty( $immo24id ) ):
        $this->_typo3UpdateRow( $table, $row, $immo24id );
        $this->_aStatistic[ 'immo24' ][ $table ][ 'records' ][ 'all' ][ 'error' ] ++;
        $this->_aStatistic[ 'immo24' ][ $table ][ 'records' ][ 'added' ][ 'error' ] ++;
        //var_dump( __METHOD__, __LINE__, $immo24id );
        return FALSE;
      default:
        // follow the workflow
        break;
    }
//    if ( $immo24id != ( int ) $immo24id || $immo24id <= 0 )
//    {
//      var_dump( __METHOD__, __LINE__, $immo24id, ( int ) $immo24id, ( string ) $immo24id !== ( string ) ( int ) $immo24id );
//      var_dump( __METHOD__, __LINE__, $immo24id, $xml, $aParams );
//      die();
//    }
    if ( !$this->_typo3UpdateRow( $table, $row, $immo24id ) )
    {
      //var_dump( __METHOD__, __LINE__, $immo24id );
      $this->_aStatistic[ 'immo24' ][ $table ][ 'records' ][ 'all' ][ 'error' ] ++;
      $this->_aStatistic[ 'immo24' ][ $table ][ 'records' ][ 'added' ][ 'error' ] ++;
      return FALSE;
    }

    $this->_aStatistic[ 'immo24' ][ $table ][ 'records' ][ 'all' ][ 'success' ] ++;

    //var_dump( __METHOD__, __LINE__, $immo24id );
    $this->_immo24ExportAppartmentrentAttachments( $row, $immo24id );
    $this->_immo24ExportAppartmentrentPublish( $row, $immo24id, 'added' );

    return TRUE;
  }

  /**
   * _immo24ExportAppartmentrentLoopUpdate( ) :
   *
   * @param array $row
   * @param array $immo24Fields
   * @return boolean
   * @access private
   * @version 7.0.0
   * @since 7.0.0
   */
  private function _immo24ExportAppartmentrentLoopUpdate( $row, $immo24Fields )
  {
    $level = 0;
    $xml = null;
    $table = $this->deal_tableappartmentrent;

//    if ( $this->_zzIsUptodate( $table, $row ) )
//    {
//      return TRUE;
//    }
//
    foreach ( ( array ) $immo24Fields as $sImmo24Field => $aImmo24Field )
    {
      $level++;
      $xml = $xml . $this->_zzImmo24Xml( $table, $row, $aImmo24Field, $sImmo24Field, $level );
      $level--;
    }

    $xml = '<?xml version="1.0" encoding="utf-8"?>
<realestates:apartmentRent xmlns:xlink="http://www.w3.org/1999/xlink" xmlns:realestates="http://rest.immobilienscout24.de/schema/offer/realestates/1.0">
' . $xml . '</realestates:apartmentRent>';

    $sImmo24Id = $this->_typo3GetImmo24CtrlField( $table, 'immo24id' );
    $iImmo24Id = $row[ $sImmo24Id ];

    $aParams = array(
      'username' => $this->_sImmo24User,
      'exposeid' => $iImmo24Id,
      'service' => 'immobilienscout',
      'estate' => array(
        'xml' => $xml,
      ),
    );

    $sResponse = $this->_oImmocaster->changeObject( $aParams );
    $method = __METHOD__ . ' (#' . __LINE__ . ')';
    $immo24id = $this->_immo24ValidationResponse( $row, $sResponse, $method );
    if ( empty( $immo24id ) )
    {
      $this->_aStatistic[ 'immo24' ][ $table ][ 'records' ][ 'all' ][ 'error' ] ++;
      $this->_aStatistic[ 'immo24' ][ $table ][ 'records' ][ 'updated' ][ 'error' ] ++;
      return FALSE;
    }

    if ( !$this->_typo3UpdateRow( $table, $row, $immo24id ) )
    {
      $this->_aStatistic[ 'immo24' ][ $table ][ 'records' ][ 'all' ][ 'error' ] ++;
      $this->_aStatistic[ 'immo24' ][ $table ][ 'records' ][ 'updated' ][ 'error' ] ++;
      return FALSE;
    }

    $this->_aStatistic[ 'immo24' ][ $table ][ 'records' ][ 'all' ][ 'success' ] ++;
    $this->_aStatistic[ 'immo24' ][ $table ][ 'records' ][ 'updated' ][ 'all' ] ++;

    $this->_immo24ExportAppartmentrentAttachments( $row, $immo24id );
    $this->_immo24ExportAppartmentrentPublish( $row, $immo24id, 'updated' );

    return TRUE;
  }

  /**
   * _immo24ExportAppartmentrentPublish( ) :
   *
   * @param array $row
   * @param array $immo24Fields
   * @param string $mode        : added || updated
   * @return boolean
   * @access private
   * @version 7.0.0
   * @since 7.0.0
   */
  private function _immo24ExportAppartmentrentPublish( $row, $immo24id, $mode )
  {
    $aChannels = $this->_typo3GetImmo24CtrlChannels( $this->deal_tableappartmentrent );

    foreach ( $aChannels as $iChannel => $bPublish )
    {
      if ( !$bPublish )
      {
        continue;
      }
      $this->_immo24ExportAppartmentrentPublishChannel( $row, $immo24id, $mode, $iChannel );
    }

    return TRUE;
  }

  /**
   * _immo24ExportAppartmentrentPublishChannel( ) :
   *
   * @param array $row
   * @param array $immo24Fields
   * @param string $mode        : added || updated
   * @param integer $iChannel   : number of the channel
   * @return boolean
   * @access private
   * @version 7.0.0
   * @since 7.0.0
   */
  private function _immo24ExportAppartmentrentPublishChannel( $row, $immo24id, $mode, $iChannel )
  {
    $sKeyDisabled = $GLOBALS[ 'TCA' ][ $this->deal_tableappartmentrent ][ 'ctrl' ][ 'enablecolumns' ][ 'disabled' ];
    $bDisabled = $row[ $sKeyDisabled ];

    //var_dump( __METHOD__, __LINE__, $immo24id );
    $aParams = array(
      'exposeid' => $immo24id,
      'channelid' => $iChannel, // 10000 = IS24, 10001 = Homepage
      'username' => $this->_sImmo24User,
    );

    switch ( $bDisabled )
    {
      case true:
//var_dump( __METHOD__, __LINE__, 'disabled');
        $sResponse = $this->_oImmocaster->disableObject( $aParams );
        break;
      case false:
      default:
//var_dump( __METHOD__, __LINE__, 'enabled');
        $sResponse = $this->_oImmocaster->enableObject( $aParams );
        break;
    }

    $method = __METHOD__ . ' (#' . __LINE__ . ')';
    $immo24idChannel = $this->_immo24ValidationResponse( $row, $sResponse, $method );

    if ( empty( $immo24idChannel ) )
    {
      $this->_aStatistic[ 'channel' ][ $mode ][ $iChannel ][ 'error' ] ++;
      $this->_aLog[ 'channels' ][ $immo24id ][ $iChannel ] = 'error';
      return FALSE;
    }

    switch ( $bDisabled )
    {
      case true:
        $this->_aStatistic[ 'channel' ][ $mode ][ $iChannel ][ 'disabled' ] ++;
        $this->_aLog[ 'channels' ][ $immo24id ][ $iChannel ] = 'disabled';
        break;
      case false:
      default:
        $this->_aStatistic[ 'channel' ][ $mode ][ $iChannel ][ 'enabled' ] ++;
        $this->_aLog[ 'channels' ][ $immo24id ][ $iChannel ] = 'enabled';
        break;
    }

    return TRUE;
  }

  /**
   * _immo24ExportAppartmentrentRequirements( ) :
   *
   * @return boolean
   * @access private
   * @version 7.0.0
   * @since 7.0.0
   */
  private function _immo24ExportAppartmentrentRequirements()
  {
    if ( !$this->_immo24ExportAppartmentrentRequirementsTcaCtrl() )
    {
      return FALSE;
    }
    return TRUE;
  }

  /**
   * _immo24ExportAppartmentrentRequirementsTcaCtrl( ) :
   *
   * @return boolean
   * @access private
   * @version 7.0.0
   * @since 7.0.0
   */
  private function _immo24ExportAppartmentrentRequirementsTcaCtrl()
  {
    return $this->_zzValidateTcaCtrl( $this->deal_tableappartmentrent );
  }

  /**
   * _immo24ExportAttachmentsAdd( ) :
   *
   * @return boolean
   * @access private
   * @version 7.0.0
   * @since 7.0.0
   */
  private function _immo24ExportAttachmentsAdd( $row, $immo24id )
  {
    //return TRUE;
    $bFloorplan = FALSE;
    $ctrlField = 'images';
    $this->_immo24ExportAttachmentsAddFiles( $row, $immo24id, $ctrlField, $bFloorplan );

    $bFloorplan = TRUE;
    $ctrlField = 'floorplan';
    $this->_immo24ExportAttachmentsAddFiles( $row, $immo24id, $ctrlField, $bFloorplan );

    return TRUE;
  }

  /**
   * _immo24ExportAttachmentsAddFiles( ) :
   *
   * @param array $row
   * @param integer $immo24id
   * @param string $ctrlField  : images || floorplan
   * @param boolean $bFloorplan
   * @return boolean
   * @access private
   * @version 7.0.0
   * @since 7.0.0
   */
  private function _immo24ExportAttachmentsAddFiles( $row, $immo24id, $ctrlField, $bFloorplan )
  {
    $table = $this->deal_tableappartmentrent;
    $aAttachments = $this->_typo3GetImmo24CtrlField( $table, 'attachments' );
    $sFilesKey = $aAttachments[ $ctrlField ][ 'files' ][ 'field' ];
    $sFiles = $row[ $sFilesKey ];
    $uploadFolder = $GLOBALS[ 'TCA' ][ $table ][ 'columns' ][ $sFilesKey ][ 'config' ][ 'uploadfolder' ];
    $sType = $aAttachments[ $ctrlField ][ 'files' ][ 'type' ];

    $aFiles = explode( ',', $sFiles );
    foreach ( $aFiles as $sFile )
    {
      $sRelativePath = $uploadFolder . '/' . $sFile;
      $sAbsolutePath = \TYPO3\CMS\Core\Utility\GeneralUtility::getFileAbsFileName( $sRelativePath );
      $aParams = array(
        'username' => $this->_sImmo24User,
        'estateid' => $immo24id, //ID des Objektes
        'title' => NULL,
        'type' => $sType, // Picture, PDFDocument or Link
        'file' => $sAbsolutePath, // file path OR URL
        'floorplan' => $bFloorplan,
      );
      $sResponse = $this->_oImmocaster->exportObjectAttachment( $aParams );
      $method = __METHOD__ . ' (#' . __LINE__ . ')';
      $sImmo24id = $this->_immo24ValidationResponse( $row, $sResponse, $method );
      unset( $sImmo24id );
    }
  }

  /**
   * _immo24ExportAttachmentsRemove( ) :
   *
   * @return boolean
   * @access private
   * @version 7.0.0
   * @since 7.0.0
   */
  private function _immo24ExportAttachmentsRemove( $row, $iImmo24id )
  {
    $sImmo24idKey = $this->_typo3GetImmo24CtrlField( $this->deal_tableappartmentrent, 'immo24id' );

    // RETURN: current record isn't exported never to immobilienscout 24
    if ( empty( $row[ $sImmo24idKey ] ) )
    {
      return TRUE;
    }

    $aParams = array(
      'estateid' => $iImmo24id,
      'username' => $this->_sImmo24User,
    );
    $sResponse = $this->_oImmocaster->getObjectAttachments( $aParams );

    $aJsonResponse = ( array ) json_decode( $sResponse );
    //var_dump( __METHOD__, __LINE__, $aJsonResponse );
    $oCommonAttachments = $aJsonResponse[ 'common.attachments' ][ 0 ];
    //var_dump( __METHOD__, __LINE__, $oCommonAttachments );
    $aAttachments = ( array ) $oCommonAttachments->attachment;
    if ( isset( $aAttachments[ '@id' ] ) )
    {
      $aParams = array(
        'attachmentid' => $aAttachments[ '@id' ],
        'estateid' => $iImmo24id,
        'username' => $this->_sImmo24User,
      );
      $sResponse = $this->_oImmocaster->deleteObjectAttachment( $aParams );
      $method = __METHOD__ . ' (#' . __LINE__ . ')';
      $immo24id = $this->_immo24ValidationResponse( $row, $sResponse, $method );
      unset( $immo24id );
      return TRUE;
    }

    //var_dump( __METHOD__, __LINE__, $aAttachments );
    foreach ( $aAttachments as $oAttachment )
    {
      $aAttachment = ( array ) $oAttachment;
      $aParams = array(
        'attachmentid' => $aAttachment[ '@id' ],
        'estateid' => $iImmo24id,
        'username' => $this->_sImmo24User,
      );
      $sResponse = $this->_oImmocaster->deleteObjectAttachment( $aParams );
//$aParameter = array('attachmentid' => 'ATTACHMENTID' /*ID des Bildes*/, 'estateid' => 'ESTATEID' /*ID des Objekts*/ );
//$res = $oImmocaster->deleteObjectAttachment($aParameter);
    }


    return TRUE;
  }

  /**
   * _immo24ExportContact( ) :
   *
   * @return boolean
   * @access private
   * @version 7.0.0
   * @since 7.0.0
   */
  private function _immo24ExportContact()
  {
    if ( !$this->_immo24ExportContactRequirements() )
    {
      return FALSE;
    }

    $rows = $this->_immo24ExportContactItems();
    if ( isset( $rows[ 'error' ] ) )
    {
      return FALSE;
    }

//    if ( $this->_immo24RemoveContact( $rows, 'beforeExport' ) )
//    {
//      return TRUE;
//    }

    if ( !$this->_immo24ExportContactLoop( $rows ) )
    {
      return FALSE;
    }

//    $telefon = '+49 30 123456';
//    $teile = preg_split( "/[\s]+/", $telefon );
//    var_dump( __METHOD__, __LINE__, $telefon, $teile, $this->taskUid, $this->deal_adminemail );
//
//    if ( $this->_immo24RemoveContact( $rows, 'afterExport' ) )
//    {
//      return TRUE;
//    }

    return TRUE;
  }

  /**
   * _immo24ExportContactItems( ) :
   *
   * @return array $rows
   * @access private
   * @version 7.0.0
   * @since 7.0.0
   */
  private function _immo24ExportContactItems()
  {
    $rows = $this->_immo24ExportContactItemsRows();

    return $rows;
  }

  /**
   * _immo24ExportContactItemsRows( ) :
   *
   * @return array $rows
   * @access private
   * @version 7.0.0
   * @since 7.0.0
   */
  private function _immo24ExportContactItemsRows()
  {
    $aUids = $this->_typo3GetImmo24ContactUidsFromApartments();
    if ( empty( $aUids ) )
    {
      return FALSE;
    }

    $andWhere = implode( ',', $aUids );
    if ( !empty( $andWhere ) )
    {
      $andWhere = ' AND uid IN (' . $andWhere . ')';
    }

    $sFields = '*';
    $sTable = $this->deal_tablecontact;
    $sWhere = '1 ' . BackendUtility::deleteClause( $sTable ) . $andWhere;
    $query = $GLOBALS[ 'TYPO3_DB' ]->SELECTquery(
            $sFields, $sTable, $sWhere
    );
    $res = $GLOBALS[ 'TYPO3_DB' ]->exec_SELECTquery(
            $sFields
            , $sTable
            , $sWhere
    );
    $rows = array();

    while ( ($row = $GLOBALS[ 'TYPO3_DB' ]->sql_fetch_assoc( $res ) ) )
    {
      $rows[ $row[ 'uid' ] ] = $row;
    }

    if ( $this->_zzPromptErrorSql( $query ) )
    {
      $rows = array(
        'error' => true
      );
      return $rows;
    }

    $GLOBALS[ 'TYPO3_DB' ]->sql_free_result( $res );

    return $rows;
  }

  /**
   * _immo24ExportContactLoop( ) :
   *
   * @param array $rows
   * @return boolean
   * @access private
   * @version 7.0.0
   * @since 7.0.0
   */
  private function _immo24ExportContactLoop( $rows )
  {
    $table = $this->deal_tablecontact;

    $sImmo24Id = $this->_typo3GetImmo24CtrlField( $table, 'immo24id' );
    if ( $sImmo24Id == 'error' )
    {
      return FALSE;
    }

    $immo24Fields = $this->_typo3GetImmo24Fields( $table );
    if ( $immo24Fields == 'error' )
    {
      return FALSE;
    }
    //var_dump( __METHOD__, __LINE__, $rows );

    foreach ( ( array ) $rows as $row )
    {
      $this->_aStatistic[ 'immo24' ][ $table ][ 'records' ][ 'all' ][ 'all' ] ++;
      $this->_aStatistic[ 'TYPO3' ][ $table ][ 'records' ][ 'all' ] ++;
      if ( $this->_zzIsUptodate( $table, $row ) )
      {
        continue;
      }
      switch ( TRUE )
      {
        case(empty( $row[ $sImmo24Id ] )):
          $this->_immo24ExportContactLoopAdd( $row, $immo24Fields );
          break;
        case(!empty( $row[ $sImmo24Id ] )):
        default:
          $this->_immo24ExportContactLoopUpdate( $row, $immo24Fields );
          break;
      }
    }
//    $telefon = '+49 30 123456';
//    $teile = preg_split( "/[\s]+/", $telefon );
//    var_dump( __METHOD__, __LINE__, $telefon, $teile, $this->taskUid, $this->deal_adminemail );
    return TRUE;
  }

  /**
   * _immo24ExportContactLoopAdd( ) :
   *
   * @param array $row
   * @param array $immo24Fields
   * @return boolean
   * @access private
   * @version 7.0.0
   * @since 7.0.0
   */
  private function _immo24ExportContactLoopAdd( $row, $immo24Fields )
  {
    $level = 0;
    $xml = null;
    $table = $this->deal_tablecontact;

    $this->_aStatistic[ 'immo24' ][ $table ][ 'records' ][ 'added' ][ 'all' ] ++;

    foreach ( ( array ) $immo24Fields as $sImmo24Field => $aImmo24Field )
    {
      $level++;
      $xml = $xml . $this->_zzImmo24Xml( $table, $row, $aImmo24Field, $sImmo24Field, $level );
      $level--;
    }

    $xml = '<?xml version="1.0" encoding="UTF-8"?>
<common:realtorContactDetail xmlns:common="http://rest.immobilienscout24.de/schema/common/1.0" xmlns:xlink="http://www.w3.org/1999/xlink" xmlns:ns4="http://rest.immobilienscout24.de/schema/customer/1.0" xmlns:ns5="http://rest.immobilienscout24.de/schema/user/1.0" >
' . $xml . '</common:realtorContactDetail>';

    $aParams = array(
      'username' => $this->_sImmo24User,
      'contact' => array(
        'xml' => $xml,
      ),
    );

    $sResponse = $this->_oImmocaster->exportContact( $aParams );

    //var_dump( __METHOD__, __LINE__, $sResponse );
    $method = __METHOD__ . ' (#' . __LINE__ . ')';
    $immo24id = $this->_immo24ValidationResponse( $row, $sResponse, $method, $xml );
    //var_dump( __METHOD__, __LINE__, $immo24id, $row, json_decode( $sResponse ) );
    if ( $immo24id === TRUE )
    {
      $immo24id = NULL;
    }
    if ( empty( $immo24id ) )
    {
      $this->_typo3UpdateRow( $table, $row, $immo24id );
      $this->_aStatistic[ 'immo24' ][ $table ][ 'records' ][ 'all' ][ 'error' ] ++;
      return FALSE;
    }

    if ( !$this->_typo3UpdateRow( $table, $row, $immo24id ) )
    {
      $this->_aStatistic[ 'immo24' ][ $table ][ 'records' ][ 'all' ][ 'error' ] ++;
      return FALSE;
    }

    $this->_aStatistic[ 'immo24' ][ $table ][ 'records' ][ 'all' ][ 'added' ] ++;
    return TRUE;
  }

  /**
   * _immo24ExportContactLoopUpdate( ) :
   *
   * @param array $row
   * @param array $immo24Fields
   * @return boolean
   * @access private
   * @version 7.0.0
   * @since 7.0.0
   */
  private function _immo24ExportContactLoopUpdate( $row, $immo24Fields )
  {
    $level = 0;
    $xml = null;
    $table = $this->deal_tablecontact;

//    if ( $this->_zzIsUptodate( $table, $row ) )
//    {
//      return TRUE;
//    }
//
    foreach ( ( array ) $immo24Fields as $sImmo24Field => $aImmo24Field )
    {
      $level++;
      $xml = $xml . $this->_zzImmo24Xml( $table, $row, $aImmo24Field, $sImmo24Field, $level );
      $level--;
    }

    $xml = '<?xml version="1.0" encoding="UTF-8"?>
<common:realtorContactDetail xmlns:common="http://rest.immobilienscout24.de/schema/common/1.0" xmlns:xlink="http://www.w3.org/1999/xlink" xmlns:ns4="http://rest.immobilienscout24.de/schema/customer/1.0" xmlns:ns5="http://rest.immobilienscout24.de/schema/user/1.0" >
' . $xml . '</common:realtorContactDetail>';

    $sImmo24Id = $this->_typo3GetImmo24CtrlField( $table, 'immo24id' );
    $iImmo24Id = $row[ $sImmo24Id ];

    $aParams = array(
      'username' => $this->_sImmo24User,
      'contactid' => $iImmo24Id,
      'contact' => array(
        'xml' => $xml,
      ),
    );

    $sResponse = $this->_oImmocaster->changeContact( $aParams );
    var_dump( __METHOD__, __LINE__, $sResponse );
    $method = __METHOD__ . ' (#' . __LINE__ . ')';
    $sMessageCode = $this->_immo24ValidationResponse( $row, $sResponse, $method, $xml );

//    if ( $sMessageCode != 'MESSAGE_RESOURCE_UPDATED' )
//    {
//      $this->_aStatistic[ 'immo24' ][ $table ][ 'records' ][ 'all' ][ 'error' ] ++;
//      return FALSE;
//    }
//
    if ( !$this->_typo3UpdateRow( $table, $row, $sMessageCode ) )
    {
      $this->_aStatistic[ 'immo24' ][ $table ][ 'records' ][ 'all' ][ 'error' ] ++;
      return FALSE;
    }

    $this->_aStatistic[ 'immo24' ][ $table ][ 'records' ][ 'all' ][ 'updated' ] ++;
    return TRUE;
  }

  /**
   * _immo24ExportContactRequirements( ) :
   *
   * @return boolean
   * @access private
   * @version 7.0.0
   * @since 7.0.0
   */
  private function _immo24ExportContactRequirements()
  {
    if ( !$this->_immo24ExportContactRequirementsTcaCtrl() )
    {
      return FALSE;
    }
    return TRUE;
  }

  /**
   * _immo24ExportContactRequirementsTcaCtrl( ) :
   *
   * @return boolean
   * @access private
   * @version 7.0.0
   * @since 7.0.0
   */
  private function _immo24ExportContactRequirementsTcaCtrl()
  {
    return $this->_zzValidateTcaCtrl( $this->deal_tablecontact );
  }

  /**
   * _immo24GetUser() :
   *
   * @return boolean
   * @access private
   * @version 7.0.0
   * @since 7.0.0
   */
  private function _immo24GetUser()
  {
    $sUsers = $this->_oImmocaster->getAllApplicationUsers( array( 'string' => true ) );
    list( $sUser) = explode( ', ', $sUsers );

    if ( !empty( $sUser ) )
    {
      $this->_sImmo24User = $sUser;

      $prompt = 'Deal! User: ' . $sUser;
      $this->_zzFlashMessage( $prompt, 'INFO' );

      return TRUE;
    }

    $prompt = $this->_extLabel . ': Can\'t get any immo24 user at ' . __METHOD__ . ' (#' . __LINE__ . ')';
    $this->_zzFlashMessage( $prompt, 'ERROR' );

    return FALSE;
  }

  /**
   * _immo24RemoveAppartmentrent( ) :
   *
   * @param array $rows
   * @param string $position  : beforeExport || afterExport
   * @return boolean
   * @access private
   * @version 7.0.0
   * @since 7.0.0
   */
  private function _immo24RemoveAppartmentrent( $rows, $position )
  {
    if ( !$this->_immo24RemoveAppartmentrentRequirements( $position ) )
    {
      return FALSE;
    }

    switch ( $this->deal_cleanupimmo24 )
    {
      case 'allAll' :
        $bUnknownOnly = FALSE;
        $this->_immo24RemoveAppartmentrentAll( $rows, $bUnknownOnly );
        return TRUE;
      case 'dealAll' :
        $this->_immo24RemoveAppartmentrentKnown( $rows );
        return TRUE;
      case 'nothing' :
        return FALSE;
      case 'unknownAll' :
        $bUnknownOnly = TRUE;
        $this->_immo24RemoveAppartmentrentAll( $rows, $bUnknownOnly );
        return TRUE;
      default:
      case 'allApartmentsRent' :
      case 'allContacts' :
      case 'dealApartmentsRent' :
      case 'dealContacts' :
      case 'unknownApartmentsRent' :
      case 'unknownContacts' :
        return FALSE;
    }
  }

  /**
   * _immo24RemoveAppartmentrentAll( ) :
   *
   * @param array $rows
   * @param boolean $bUnknownOnly : If is true, only items will deleted, which aren't part of the given rows.
   * @return boolean
   * @access private
   * @version 7.0.0
   * @since 7.0.0
   */
  private function _immo24RemoveAppartmentrentAll( $rows, $bUnknownOnly )
  {
    //var_dump( __METHOD__, __LINE__, $bUnknownOnly, array_keys( $rows ) );
    static $iHandledEstates = 0;
    $iRemovedRealEstates = 0;

    $aParams = array(
      'username' => $this->_sImmo24User,
    );
    $sResponse = $this->_oImmocaster->fullUserSearch( $aParams );

    $aRealestates = ( array ) json_decode( $sResponse );
    $oRealestatesRealEstates = $aRealestates[ 'realestates.realEstates' ];
    //var_dump( __METHOD__, __LINE__, $oRealestatesRealEstates );
    $iNumberOfHits = $oRealestatesRealEstates->Paging->numberOfHits;
    $aRealEstateElement = ( array ) $oRealestatesRealEstates->realEstateList->realEstateElement;
    if ( $aRealEstateElement[ '@id' ] )
    {
      $uid = $aRealEstateElement[ 'externalId' ];
      $row = $rows[ $uid ];
      //var_dump( __METHOD__, __LINE__, $uid, isset( $rows[ $uid ]) );
      if ( $bUnknownOnly && isset( $rows[ $uid ] ) )
      {
        //var_dump( __METHOD__, __LINE__, $uid );
        $iHandledEstates++;
        return TRUE;
      }
      $iImmo24Id = $aRealEstateElement[ '@id' ];
      if ( $this->_immocasterDeleteObject( $row, $iImmo24Id ) )
      {
        $iRemovedRealEstates++;
      }
      $iHandledEstates++;
      return TRUE;
    }
    foreach ( $aRealEstateElement as $oRealEstateElement )
    {
      $aRealEstate = ( array ) $oRealEstateElement;
      $uid = $aRealEstate[ 'externalId' ];
      $row = $rows[ $uid ];
      //var_dump( __METHOD__, __LINE__, $uid, isset( $rows[ $uid ]) );
      if ( $bUnknownOnly && isset( $rows[ $uid ] ) )
      {
        //var_dump( __METHOD__, __LINE__, $uid );
        $iHandledEstates++;
        continue;
      }
      $iImmo24Id = $aRealEstate[ '@id' ];
      if ( $this->_immocasterDeleteObject( $row, $iImmo24Id ) )
      {
        $iRemovedRealEstates++;
      }
      $iHandledEstates++;
    }
    //var_dump( __METHOD__, __LINE__, $iNumberOfHits, count( $aRealEstateElement ), $iRemovedRealEstates, $iHandledEstates );

    if ( $iNumberOfHits <= $iHandledEstates )
    {
      return TRUE;
    }

    if ( $iRemovedRealEstates < 1 )
    {
      return TRUE;
    }

    if ( $iHandledEstates > 1000 )
    {
      $prompt = $this->_extLabel . ': ' . __METHOD__ . ' (' . __LINE__ . ') exits after 1.000 loops.';
      $this->_zzFlashMessage( $prompt, 'ERROR' );
      return TRUE;
    }

    return $this->_immo24RemoveAppartmentrentAll( $rows, $bUnknownOnly );
  }

  /**
   * _immo24RemoveAppartmentrentKnown( ) :
   *
   * @param array $rows
   * @return boolean
   * @access private
   * @version 7.0.0
   * @since 7.0.0
   */
  private function _immo24RemoveAppartmentrentKnown( $rows )
  {
    $table = $this->deal_tableappartmentrent;

    $sImmo24Id = $this->_typo3GetImmo24CtrlField( $table, 'immo24id' );
    if ( $sImmo24Id == 'error' )
    {
      return FALSE;
    }

    foreach ( ( array ) $rows as $row )
    {
      $this->_aStatistic[ 'TYPO3' ][ $table ][ 'records' ][ 'all' ] ++;
      $iImmo24Id = $row[ $sImmo24Id ];
      if ( empty( $iImmo24Id ) )
      {
        continue;
      }
      $this->_immocasterDeleteObject( $row, $iImmo24Id );
    }
    return TRUE;
  }

  /**
   * _immo24RemoveAppartmentrentRequirements( ) :
   *
   * @param string $position  : beforeExport || afterExport
   * @return boolean
   * @access private
   * @version 7.0.0
   * @since 7.0.0
   */
  private function _immo24RemoveAppartmentrentRequirements( $position )
  {
    switch ( $position )
    {
      case 'afterExport' :
        return $this->_immo24RemoveAppartmentrentRequirementsAfterExport();
      case 'beforeExport' :
        return $this->_immo24RemoveAppartmentrentRequirementsBeforeExport();
      default:
        $prompt = $this->_extLabel . ': $position is undefined: "' . $position . '"';
        $this->_zzFlashMessage( $prompt, 'ERROR' );
        return FALSE;
    }
  }

  /**
   * _immo24RemoveAppartmentrentRequirementsAfterExport( ) :
   *
   * @return boolean
   * @access private
   * @version 7.0.0
   * @since 7.0.0
   */
  private function _immo24RemoveAppartmentrentRequirementsAfterExport()
  {
    switch ( $this->deal_cleanupimmo24 )
    {
      case 'unknownAll' :
        $prompt = $this->_extLabel . ' Immobilienscout24 database will cleaned up. All data will removed, which isn\'t known by Deal!';
        $this->_zzFlashMessage( $prompt, 'NOTICE' );
        return TRUE;
      case 'allAll' :
      case 'dealAll' :
      case 'nothing' :
        return FALSE;
      default:
      case 'allApartmentsRent' :
      case 'allContacts' :
      case 'dealApartmentsRent' :
      case 'dealContacts' :
      case 'unknownApartmentsRent' :
      case 'unknownContacts' :
        return FALSE;
    }
  }

  /**
   * _immo24RemoveAppartmentrentRequirementsBeforeExport( ) :
   *
   * @return boolean
   * @access private
   * @version 7.0.0
   * @since 7.0.0
   */
  private function _immo24RemoveAppartmentrentRequirementsBeforeExport()
  {
    switch ( $this->deal_cleanupimmo24 )
    {
      case 'allAll' :
        $prompt = $this->_extLabel . ' Immobilienscout24 database will cleaned up. All data will removed. Any data won\'t exported.';
        $this->_zzFlashMessage( $prompt, 'WARNING' );
        $prompt = $this->_extLabel . ' If you like to export data, please disable the cleaning up of the immobilienscout24 database.';
        $this->_zzFlashMessage( $prompt, 'NOTICE' );
        return TRUE;
//        $prompt = $this->_extLabel . ': Sorry, but action from above isn\'t implemented now.';
//        $this->_zzFlashMessage( $prompt, 'ERROR' );
//        return FALSE;
      case 'dealAll' :
        $prompt = $this->_extLabel . ' Immobilienscout24 database will cleaned up. All data will removed, which is known by Deal! Any data won\'t exported.';
        $this->_zzFlashMessage( $prompt, 'WARNING' );
        $prompt = $this->_extLabel . ' If you like to export data, please disable the cleaning up of the immobilienscout24 database.';
        $this->_zzFlashMessage( $prompt, 'NOTICE' );
        return TRUE;
      case 'nothing' :
      case 'unknownAll' :
        return FALSE;
      default:
      case 'allApartmentsRent' :
      case 'allContacts' :
      case 'dealApartmentsRent' :
      case 'dealContacts' :
      case 'unknownApartmentsRent' :
      case 'unknownContacts' :
        return FALSE;
    }
  }

  /**
   * _immo24ValidationResponseJson( ) :
   *
   * @param array $row
   * @param array $jsonResponse
   * @param string $method
   * @param string $xml
   * @return boolean
   * @access private
   * @version 7.0.0
   * @since 7.0.0
   */
  private function _immo24ValidationResponseJson( $row, $jsonResponse, $method, $xml = null )
  {

    $aResponse = json_decode( $jsonResponse, true );
//    var_dump( __METHOD__, __LINE__, $aResponse );
//die();
    if ( isset( $aResponse[ 'common.messages' ][ '0' ][ 'message' ][ 'id' ] ) )
    {
      $id = $aResponse[ 'common.messages' ][ '0' ][ 'message' ][ 'id' ];
      return $id;
    }

    return $this->_zzPromptErrorImmo24ResponseArray( $row, $aResponse, $method, $xml );
  }

  /**
   * _immo24ValidationResponse( ) :
   *
   * @param array $row
   * @param string $sResponse
   * @param string $method
   * @param string $xml
   * @return boolean
   * @access private
   * @version 7.0.0
   * @since 7.0.0
   */
  private function _immo24ValidationResponse( $row, $sResponse, $method, $xml = null )
  {
//    $meinString = 'abc';
//$findMich   = 'a';
//$pos = strpos($meinString, $findMich);

    switch ( TRUE )
    {
      case (strpos( $sResponse, '{"' ) === 0 ):
        $aResponse = json_decode( $sResponse, true );
        break;
      case (strpos( $sResponse, '<?xml' ) === 0 ):
        $aResponse = array(
          'common.messages' => array(
            0 => GeneralUtility::xml2array( $sResponse ),
          ),
        );
        break;
      case(!empty( $sResponse )):
        $sResponse = var_export( $sResponse, TRUE );
        $aResponse = array(
          'common.messages' => array(
            0 => array(
              'message' => array(
                'message' => 'Response isn\'t defined: "' . $sResponse . '"',
              ),
            ),
          ),
        );
        break;
      default:
        $aResponse = array(
          'common.messages' => array(
            0 => array(
              'message' => array(
                'message' => 'Response isn\'t defined: "' . $sResponse . '"',
              ),
            ),
          ),
        );
    }

//    var_dump( __METHOD__, __LINE__, $aResponse );
//die();
    if ( isset( $aResponse[ 'common.messages' ][ '0' ][ 'message' ][ 'id' ] ) )
    {
      $id = $aResponse[ 'common.messages' ][ '0' ][ 'message' ][ 'id' ];
      return $id;
    }

    return $this->_zzPromptErrorImmo24ResponseArray( $row, $aResponse, $method, $xml );
  }

  /**
   * _immocasterDeleteObject( ) :
   *
   * @param array $row
   * @param integer $iImmo24Id
   * @return boolean
   * @access private
   * @version 7.0.0
   * @since 7.0.0
   */
  private function _immocasterDeleteObject( $row, $iImmo24Id )
  {
    $table = $this->deal_tableappartmentrent;

    if ( empty( $iImmo24Id ) )
    {
      return FALSE;
    }
    $aParams = array(
      'username' => $this->_sImmo24User,
      'estateid' => $iImmo24Id,
    );

    $sResponse = $this->_oImmocaster->deleteObject( $aParams );
    //var_dump( __METHOD__, __LINE__, json_decode( $sResponse ) );
    //$method = __METHOD__ . ' (#' . __LINE__ . ')';
    //$immo24id = $this->_immo24ValidationResponse( $row, $sResponse, $method );
    $this->_aStatistic[ 'immo24' ][ $table ][ 'records' ][ 'all' ][ 'all' ] ++;
    $this->_aStatistic[ 'immo24' ][ $table ][ 'records' ][ 'removed' ][ 'all' ] ++;
    $messageCode = $this->_zzImmo24MessageCode( $sResponse );
    $dealUid = NULL;
    if ( isset( $row[ 'uid' ] ) )
    {
      $dealUid = ' TYPO3-uid: ' . $row[ 'uid' ];
    }
    switch ( $messageCode )
    {
      case 'ERROR_RESOURCE_NOT_FOUND':
        $this->_aStatistic[ 'immo24' ][ $table ][ 'records' ][ 'removed' ][ 'error' ] ++;
        $prompt = $this->_extLabel . ' Error while deleting: "' . $messageCode . '". Scout-ID: "' . $iImmo24Id . '".' . $dealUid;
        $this->_zzFlashMessage( $prompt, 'ERROR' );
        $this->_typo3UpdateRow( $table, $row, 'ERROR_RESOURCE_NOT_FOUND' );
        return FALSE;
      case 'MESSAGE_RESOURCE_DELETED':
        //$this->_aStatistic[ 'TYPO3' ]['appartmentrent'][ 'records' ][ 'removed' ] ++;
        $this->_aStatistic[ 'immo24' ][ $table ][ 'records' ][ 'removed' ][ 'success' ] ++;
        $this->_typo3UpdateRow( $table, $row, 'MESSAGE_RESOURCE_DELETED' );
        return TRUE;
      default:
        $prompt = $this->_extLabel . ' Error while deleting: "' . $messageCode . '". Scout-ID: "' . $iImmo24Id . '".' . $dealUid;
        $this->_zzFlashMessage( $prompt, 'ERROR' );
        $this->_typo3UpdateRow( $table, $row, 'error' );
        return FALSE;
    }
  }

  /**
   * _init( ) :
   *
   * @param array $aParams  Array of task variables
   * @return boolean
   * @access private
   * @version 7.0.0
   * @since 7.0.0
   */
  private function _init( $aParams )
  {
    $this->_initVars( $aParams );
    $this->_initImmocaster();

    if ( !$this->_initImmo24User() )
    {
      return FALSE;
    }

    $this->_initStatistic();

    $this->_initValueCleanup();

    return TRUE;
  }

  /**
   * _initImmocaster() :
   *
   * @return void
   * @access private
   * @version 7.0.0
   * @since 7.0.0
   */
  private function _initImmocaster()
  {
    $this->_initImmocasterKeys();
    $this->_initImmocasterInstance();
    $this->_initImmocasterDatabase();
    $this->_initImmocasterProperties();
  }

  /**
   * _initImmocasterDatabase() : Database contains certification records.
   *                        If there isn't any record, immo24 actions aren't possible, which need a certificate.
   *
   * @return void
   * @access private
   * @version 7.0.0
   * @since 7.0.0
   */
  private function _initImmocasterDatabase()
  {
    $this->_initImmocasterDatabaseLiveOrSandbox();

    $aDatabase = array(
      'mysql',
      TYPO3_db_host,
      TYPO3_db_username,
      TYPO3_db_password,
      TYPO3_db
    );

    $this->_oImmocaster->setDataStorage(
            $aDatabase, 'Immocaster', $this->_sImmo24TableCertificate
    );
  }

  /**
   * _initImmocasterDatabaseLiveOrSandbox() : Database contains certification records.
   *                        If there isn't any record, immo24 actions aren't possible, which need a certificate.
   *
   * @return void
   * @access private
   * @version 7.0.0
   * @since 7.0.0
   */
  private function _initImmocasterDatabaseLiveOrSandbox()
  {
    switch ( $this->deal_liveorsandbox )
    {
      case( 'live' ):
        $this->_sImmo24TableCertificate = 'tx_deal_immo24certificate';
        $prompt = 'Deal! Environment: live.';
        $this->_zzFlashMessage( $prompt, 'INFO' );
        break;
      case( 'sandbox' ):
      default:
        $this->_sImmo24TableCertificate = 'tx_deal_immo24certificateSandbox';
        $prompt = 'Deal! Environment: sandbox.';
        $this->_zzFlashMessage( $prompt, 'INFO' );
        break;
    }
  }

  /**
   * _initImmocasterInstance() :
   *
   * @return void
   * @access private
   * @version 7.0.0
   * @since 7.0.0
   */
  private function _initImmocasterInstance()
  {
    require_once(ExtensionManagementUtility::extPath( 'deal' ) . 'Resources/Private/Marketplaces/Immo24/restapi-php-sdk_1.1.78/Immocaster/Sdk.php');
    $this->_oImmocaster = \Immocaster_Sdk::getInstance( 'immo24', $this->_sImmo24KeyPublic, $this->_sImmo24KeyPrivate );
  }

  /**
   * _initImmocasterKeys() :
   *
   * @return void
   * @access private
   * @version 7.0.0
   * @since 7.0.0
   */
  private function _initImmocasterKeys()
  {
    switch ( $this->deal_liveorsandbox )
    {
      case( 'live' ):
        $this->_sImmo24KeyPublic = $this->deal_keylivepublic;
        $this->_sImmo24KeyPrivate = $this->deal_keyliveprivate;
        break;
      case( 'sandbox' ):
      default:
        $this->_sImmo24KeyPublic = $this->deal_keysandboxpublic;
        $this->_sImmo24KeyPrivate = $this->deal_keysandboxprivate;
        break;
    }
  }

  /**
   * _initImmocasterProperties() :
   *
   * @return void
   * @access private
   * @version 7.0.0
   * @since 7.0.0
   */
  private function _initImmocasterProperties()
  {
    // json or xml
    $this->_oImmocaster->setContentResultType( 'json' );

    // curl or none
    $this->_oImmocaster->setReadingType( 'curl' );

//    // request debug mode
//    if ( $aImmo24TsProperties[ 'requestDebug' ] )
//    {
//      // Disable it
//      //$this->_oImmocaster->disableRequestDebug()
//      $this->_oImmocaster->enableRequestDebug();
//    }
//    // strict mode
//    if ( $aImmo24TsProperties[ 'strictMode' ] )
//    {
//      $this->_oImmocaster->setStrictMode( true );
//    }
    // live or sandbox
    $this->_oImmocaster->setRequestUrl( $this->deal_liveorsandbox );
  }

  /**
   * _initImmo24User() :
   *
   * @return boolean
   * @access private
   * @version 7.0.0
   * @since 7.0.0
   */
  private function _initImmo24User()
  {
    return $this->_immo24GetUser();
  }

  /**
   * _initStatistic( ) :
   *
   * @return void
   * @access private
   * @version 7.0.0
   * @since 7.0.0
   */
  private function _initStatistic()
  {
    $this->_aStatistic = array(
      'immo24' => array(
        $this->deal_tableappartmentrent => array(
          'records' => array(
            'all' => array(
              'all' => 0,
              'error' => 0,
              'noneedforupdate' => 0,
              'success' => 0,
            ),
            'added' => array(
              'all' => 0,
              'disabled' => 0,
              'enabled' => 0,
              'error' => 0,
            ),
            'removed' => array(
              'all' => 0,
              'error' => 0,
              'success' => 0,
            ),
            'updated' => array(
              'all' => 0,
              'disabled' => 0,
              'enabled' => 0,
              'error' => 0,
            ),
          ),
        ),
        $this->deal_tablecontact => array(
          'records' => array(
            'all' => array(
              'all' => 0,
              'error' => 0,
              'new' => 0,
              'noneedforupdate' => 0,
              'removed' => 0,
              'updated' => 0,
            ),
          ),
        ),
      ),
      'TYPO3' => array(
        $this->deal_tableappartmentrent => array(
          'records' => array(
            'all' => 0,
            'noneedforupdate' => 0,
          ),
        ),
        $this->deal_tablecontact => array(
          'records' => array(
            'all' => 0,
            'noneedforupdate' => 0,
          ),
        ),
      ),
    );
  }

  /**
   * _initValueCleanup( ) :
   *
   * @return void
   * @access private
   * @version 7.0.0
   * @since 7.0.0
   */
  private function _initValueCleanup()
  {
    switch ( $this->deal_cleanupimmo24 )
    {
      case 'allAll' :
      case 'dealAll' :
      case 'nothing' :
      case 'unknownAll' :
        return TRUE;
      case 'allApartmentsRent' :
      case 'allContacts' :
      case 'dealApartmentsRent' :
      case 'dealContacts' :
      case 'unknownApartmentsRent' :
      case 'unknownContacts' :
      default:
        // follow the workflow
        break;
    }

    $prompt = $this->_extLabel . ': ' . 'FATAL ERROR<br />' . LF
            . '$this->deal_cleanupimmo24 is undefined: "' . $this->deal_cleanupimmo24 . '"'
    ;

    $this->_zzFlashMessage( $prompt, 'ERROR' );
    $this->deal_cleanupimmo24 = 'nothing';
  }

  /**
   * _initVars( ) :
   *
   * @param array $aParams  Array of task variables
   * @return void
   * @access private
   * @version 7.0.0
   * @since 7.0.0
   */
  private function _initVars( $aParams )
  {
    $this->deal_adminemail = $aParams[ 'deal_adminemail' ];
    $this->deal_adminemailmode = $aParams[ 'deal_adminemailmode' ];
    $this->deal_cleanupimmo24 = $aParams[ 'deal_cleanupimmo24' ];
    $this->deal_keyliveprivate = $aParams[ 'deal_keyliveprivate' ];
    $this->deal_keylivepublic = $aParams[ 'deal_keylivepublic' ];
    $this->deal_keysandboxprivate = $aParams[ 'deal_keysandboxprivate' ];
    $this->deal_keysandboxpublic = $aParams[ 'deal_keysandboxpublic' ];
    $this->deal_liveorsandbox = $aParams[ 'deal_liveorsandbox' ];
    $this->deal_tableappartmentrent = $aParams[ 'deal_tableappartmentrent' ];
    $this->deal_tablecontact = $aParams[ 'deal_tablecontact' ];
    $this->taskUid = $aParams[ 'taskUid' ];
  }

  /**
   * _typo3GetCtrlField( ) :
   *
   * @param string $table
   * @param string $key
   * @return boolean
   * @access private
   * @version 7.0.0
   * @since 7.0.0
   */
  private function _typo3GetCtrlField( $table, $key )
  {
    $ctrlField = $GLOBALS[ 'TCA' ][ $table ][ 'ctrl' ][ $key ];
    if ( !empty( $ctrlField ) )
    {
      return $ctrlField;
    }

    $prompt = $this->_extLabel . ': ' . 'FATAL ERROR<br />' . LF
            . "\$GLOBALS[ 'TCA' ][ '" . $table . "' ][ 'ctrl' ][ '" . $key . "' ] is empty!"
    ;

    $this->_zzFlashMessage( $prompt, 'ERROR' );
    return 'error';
  }

  /**
   * _typo3GetColumnsArray( ) :
   *
   * @param string $table
   * @param string $key
   * @return boolean
   * @access private
   * @version 7.0.0
   * @since 7.0.0
   */
  private function _typo3GetColumnsArray( $table, $key )
  {
    $ctrlField = $GLOBALS[ 'TCA' ][ $table ][ 'columns' ][ $key ];
    if ( !empty( $ctrlField ) )
    {
      return $ctrlField;
    }

    $prompt = $this->_extLabel . ': ' . 'FATAL ERROR<br />' . LF
            . "\$GLOBALS[ 'TCA' ][ '" . $table . "' ][ 'ctrl' ][ '" . $key . "' ] is empty!"
    ;

    $this->_zzFlashMessage( $prompt, 'ERROR' );
    return 'error';
  }

  /**
   * _typo3GetImmo24ContactUidsFromApartments( ) :
   *
   * @return array $uids
   * @access private
   * @version 7.0.0
   * @since 7.0.0
   */
  private function _typo3GetImmo24ContactUidsFromApartments()
  {
    $aContactUids = array();
    $rows = $this->_immo24ExportAppartmentrentItemsRows();

    $sContactUid = $this->_typo3GetImmo24CtrlField( $this->deal_tableappartmentrent, 'contact' );
    $this->_typo3GetImmo24ContactUidsFromApartmentsErrorMm( $sContactUid );

    foreach ( ( array ) $rows as $row )
    {
      $aContactUids[] = ( int ) $row[ $sContactUid ];
      $this->_typo3GetImmo24ContactUidsFromApartmentsErrorInteger( $row, $sContactUid );
    }

    $aContactUids = array_unique( $aContactUids );

    return $aContactUids;
  }

  /**
   * _typo3GetImmo24ContactUidsFromApartmentsErrorInteger( ) :
   *
   * @return array $uids
   * @access private
   * @version 7.0.0
   * @since 7.0.0
   */
  private function _typo3GetImmo24ContactUidsFromApartmentsErrorInteger( $row, $sContactUid )
  {
    if ( ( string ) (( int ) $row[ $sContactUid ]) === ( string ) $row[ $sContactUid ] )
    {
      return;
    }

    $prompt = 'Deal! Contact value isn\'t any integer: "'
            . $row[ $sContactUid ]
            . '" at ' . $this->deal_tableappartmentrent . '.uid '
            . $row[ 'uid' ]
    ;
    $this->_zzFlashMessage( $prompt, 'ERROR' );
  }

  /**
   * _typo3GetImmo24ContactUidsFromApartmentsErrorIntegerMm( ) :
   *
   * @return
   * @access private
   * @internal Support for MM-relation at typo3/sysext/core/Classes/Database/DatabaseConnection::exec_SELECT_mm_query
   * @version 7.0.0
   * @since 7.0.0
   */
  private function _typo3GetImmo24ContactUidsFromApartmentsErrorMm( $key )
  {
    $aField = $this->_typo3GetColumnsArray( $this->deal_tableappartmentrent, $key );
    if ( !isset( $aField[ 'config' ][ 'MM' ] ) )
    {
      return;
    }

    $prompt = 'Deal! ' . $this->deal_tableappartmentrent . '.' . $key
            . ' is a MM-relation.'
            . ' But MM-relation isn\'t supported in the current version.'
            . ' Sorry for the trouble.'
    ;
    $this->_zzFlashMessage( $prompt, 'ERROR' );
  }

  /**
   * _typo3GetImmo24CtrlChannels( ) :
   *
   * @param string $table
   * @return boolean
   * @access private
   * @version 7.0.0
   * @since 7.0.0
   */
  private function _typo3GetImmo24CtrlChannels( $table )
  {
    $aChannels = $GLOBALS[ 'TCA' ][ $table ][ 'ctrl' ][ 'tx_deal' ][ 'marketplaces' ][ 'immo24' ][ 'ctrl' ][ 'channels' ];
    if ( !empty( $aChannels ) )
    {
      return $aChannels;
    }

    $prompt = $this->_extLabel . ': ' . 'FATAL ERROR<br />' . LF
            . "\$GLOBALS[ 'TCA' ][ '" . $table . "' ][ 'ctrl' ][ 'tx_deal' ][ 'marketplaces' ][ 'immo24' ][ 'ctrl' ][ 'channels' ] is empty!"
    ;

    $this->_zzFlashMessage( $prompt, 'ERROR' );
    return 'error';
  }

  /**
   * _typo3GetImmo24CtrlField( ) :
   *
   * @param string $table
   * @param string $key
   * @return boolean
   * @access private
   * @version 7.0.0
   * @since 7.0.0
   */
  private function _typo3GetImmo24CtrlField( $table, $key )
  {
    $key = $this->_typo3GetImmo24CtrlFieldSandbox( $key );

    $ctrlField = $GLOBALS[ 'TCA' ][ $table ][ 'ctrl' ][ 'tx_deal' ][ 'marketplaces' ][ 'immo24' ][ 'ctrl' ][ 'fields' ][ $key ];
    if ( !empty( $ctrlField ) )
    {
      return $ctrlField;
    }

    $prompt = $this->_extLabel . ': ' . 'FATAL ERROR<br />' . LF
            . "\$GLOBALS[ 'TCA' ][ '" . $table . "' ][ 'ctrl' ][ 'tx_deal' ][ 'marketplaces' ][ 'immo24' ][ 'ctrl' ]['fields'][ '" . $key . "' ] is empty!"
    ;

    $this->_zzFlashMessage( $prompt, 'ERROR' );
    return 'error';
  }

  /**
   * _typo3GetImmo24CtrlFieldSandbox( ) :
   *
   * @param string $table
   * @param string $key
   * @return boolean
   * @access private
   * @version 7.0.0
   * @since 7.0.0
   */
  private function _typo3GetImmo24CtrlFieldSandbox( $key )
  {
    switch ( $this->deal_liveorsandbox )
    {
      case( 'sandbox' ):
        $key = $key . 'sandbox';
        break;
      case( 'live' ):
      default:
        // follow the workflow
        break;
    }

    return $key;
  }

  /**
   * _typo3GetImmo24CtrlSql( ) :
   *
   * @param string $table
   * @param string $key
   * @return boolean
   * @access private
   * @version 7.0.0
   * @since 7.0.0
   */
  private function _typo3GetImmo24CtrlSql( $table, $key )
  {
    $aSqlKey = $GLOBALS[ 'TCA' ][ $table ][ 'ctrl' ][ 'tx_deal' ][ 'marketplaces' ][ 'immo24' ][ 'ctrl' ][ 'sql' ][ $key ];
    if ( !empty( $aSqlKey ) )
    {
      return $aSqlKey;
    }

    $prompt = $this->_extLabel . ': ' . 'FATAL ERROR<br />' . LF
            . "\$GLOBALS[ 'TCA' ][ '" . $table . "' ][ 'ctrl' ][ 'tx_deal' ][ 'marketplaces' ][ 'immo24' ][ 'ctrl' ][ '" . $key . "' ] is empty!"
    ;

    $this->_zzFlashMessage( $prompt, 'ERROR' );
    return 'error';
  }

  /**
   * _typo3GetImmo24CtrlValue( ) :
   *
   * @param string $table
   * @param string $key
   * @return boolean
   * @access private
   * @version 7.0.0
   * @since 7.0.0
   */
  private function _typo3GetImmo24CtrlValue( $table, $key )
  {
    $key = $this->_typo3GetImmo24CtrlValueSandbox( $key );

    $ctrlValue = $GLOBALS[ 'TCA' ][ $table ][ 'ctrl' ][ 'tx_deal' ][ 'marketplaces' ][ 'immo24' ][ 'ctrl' ][ 'values' ][ $key ];
    if ( !empty( $ctrlValue ) )
    {
      return $ctrlValue;
    }

    $prompt = $this->_extLabel . ': ' . 'FATAL ERROR<br />' . LF
            . "\$GLOBALS[ 'TCA' ][ '" . $table . "' ][ 'ctrl' ][ 'tx_deal' ][ 'marketplaces' ][ 'immo24' ][ 'ctrl' ]['values'][ '" . $key . "' ] is empty!"
    ;

    $this->_zzFlashMessage( $prompt, 'ERROR' );
    return 'error';
  }

  /**
   * _typo3GetImmo24CtrlValueSandbox( ) :
   *
   * @param string $table
   * @param string $key
   * @return boolean
   * @access private
   * @version 7.0.0
   * @since 7.0.0
   */
  private function _typo3GetImmo24CtrlValueSandbox( $key )
  {
    switch ( $this->deal_liveorsandbox )
    {
      case( 'sandbox' ):
        $key = $key . 'sandbox';
        break;
      case( 'live' ):
      default:
        // follow the workflow
        break;
    }

    return $key;
  }

  /**
   * _typo3GetImmo24Fields( ) :
   *
   * @param string $table
   * @return boolean
   * @access private
   * @version 7.0.0
   * @since 7.0.0
   */
  private function _typo3GetImmo24Fields( $table )
  {
    $fields = $GLOBALS[ 'TCA' ][ $table ][ 'ctrl' ][ 'tx_deal' ][ 'marketplaces' ][ 'immo24' ][ 'fields' ];
    if ( is_array( $fields ) )
    {
      return $fields;
    }

    $prompt = $this->_extLabel . ': ' . 'FATAL ERROR<br />' . LF
            . "\$GLOBALS[ 'TCA' ][ '" . $table . "' ][ 'ctrl' ][ 'tx_deal' ][ 'marketplaces' ][ 'immo24' ][ 'fields' ] isn't an array!"
    ;

    $this->_zzFlashMessage( $prompt, 'ERROR' );
    return 'error';
  }

  /**
   * _typo3GetTableContact( ) :
   *
   * @param array $row
   * @param array $immo24Fields
   * @return boolean
   * @access private
   * @version 7.0.0
   * @since 7.0.0
   */
  private function _typo3GetTableRows( $table, $andWhere )
  {
    $sFields = '*';
    $sWhere = '1 ' . BackendUtility::deleteClause( $table ) . $andWhere;
    $query = $GLOBALS[ 'TYPO3_DB' ]->SELECTquery(
            $sFields, $table, $sWhere
    );
    $res = $GLOBALS[ 'TYPO3_DB' ]->exec_SELECTquery(
            $sFields
            , $table
            , $sWhere
    );

    $rows = array();

    while ( ($row = $GLOBALS[ 'TYPO3_DB' ]->sql_fetch_assoc( $res ) ) )
    {
      $rows[ $row[ 'uid' ] ] = $row;
    }

    if ( $this->_zzPromptErrorSql( $query ) )
    {
      $rows = array(
        'error' => true
      );
      return $rows;
    }

    $GLOBALS[ 'TYPO3_DB' ]->sql_free_result( $res );

    return $rows;
  }

  /**
   * _typo3UpdateRow() :
   *
   * @return boolean
   * @access private
   * @version 7.0.0
   * @since 7.0.0
   */
  private function _typo3UpdateRow( $table, $row, $sImmo24id )
  {
    //var_dump( __METHOD__, __LINE__, $sImmo24id );
    $uid = $row[ 'uid' ];
    $fields_values = array();
    $uCurrentTime = time();

    $keyImmo24id = $this->_typo3GetImmo24CtrlField( $table, 'immo24id' );
    $keyImmo24log = $this->_typo3GetImmo24CtrlField( $table, 'immo24log' );
    $keyImmo24tstamp = $this->_typo3GetImmo24CtrlField( $table, 'immo24timestamp' );

    $valueImmo24log = $this->_typo3UpdateRowLog( $table, $row[ $keyImmo24log ], $sImmo24id );

    switch ( TRUE )
    {
      case( $sImmo24id == 'MESSAGE_RESOURCE_UPDATED' ):
        $fields_values[ $keyImmo24tstamp ] = $uCurrentTime;
        $fields_values = $this->_typo3UpdateRowUrl( $table, $sImmo24id, $fields_values );
        break;
      case( $sImmo24id == 'ERROR_RESOURCE_NOT_FOUND' ):
      case( $sImmo24id == 'ERROR_RESOURCE_VALIDATION' ):
      case( $sImmo24id == 'MESSAGE_RESOURCE_DELETED' ):
        $fields_values[ $keyImmo24id ] = NULL;
        $fields_values[ $keyImmo24tstamp ] = NULL;
        $fields_values = $this->_typo3UpdateRowUrl( $table, $sImmo24id, $fields_values );
        break;
      case(( string ) ( int ) $sImmo24id === ( string ) $sImmo24id ):
        $fields_values[ $keyImmo24id ] = $sImmo24id;
        $fields_values[ $keyImmo24tstamp ] = $uCurrentTime;
        $fields_values = $this->_typo3UpdateRowUrl( $table, $sImmo24id, $fields_values );
        break;
      case(empty( $sImmo24id ) ):
      default:
        $fields_values[ $keyImmo24id ] = NULL;
        $fields_values[ $keyImmo24tstamp ] = $uCurrentTime;
        break;
    }

    $fields_values[ $keyImmo24log ] = $valueImmo24log;
    $fields_values[ 'tstamp' ] = $uCurrentTime;

    $query = $GLOBALS[ 'TYPO3_DB' ]->UPDATEquery(
            $table, 'uid=' . intval( $uid ), $fields_values
    );
    $GLOBALS[ 'TYPO3_DB' ]->exec_UPDATEquery(
            $table, 'uid=' . intval( $uid ), $fields_values
    );

    if ( $this->_zzPromptErrorSql( $query ) )
    {
      return FALSE;
    }

//    $prompt = $this->_extLabel . ': ' . __METHOD__ . ' (#' . __LINE__ . ')';
//    $this->_zzFlashMessage( $prompt, 'INFO' );
//    return FALSE;

    return TRUE;
  }

  /**
   * _typo3UpdateRowLog() :
   *
   * @return boolean
   * @access private
   * @version 7.0.0
   * @since 7.0.0
   */
  private function _typo3UpdateRowLog( $table, $sCurrentLog, $sImmo24id )
  {
    $sChannels = $this->_typo3UpdateRowLogChannel( $table, $sImmo24id );
    $sPrefix = $this->_typo3UpdateRowLogPrefix();

    switch ( TRUE )
    {
      case( $sImmo24id == 'ERROR_RESOURCE_NOT_FOUND' ):
        $sHeader = $sPrefix . 'ERROR at ' . strftime( '%Y/%m/%d %T' ) . '. There wasn\'t any item with the current id.' . PHP_EOL;
        break;
      case( $sImmo24id == 'MESSAGE_RESOURCE_DELETED' ):
        $sHeader = $sPrefix . 'Removed at ' . strftime( '%Y/%m/%d %T' ) . PHP_EOL;
        break;
      case(!empty( $sImmo24id ) ):
        $sHeader = $sPrefix . 'Update at ' . strftime( '%Y/%m/%d %T' ) . '. immo24 ID: ' . $sImmo24id . PHP_EOL;
        break;
      case(empty( $sImmo24id ) ):
      default:
        $sHeader = $sPrefix . 'ERROR at ' . strftime( '%Y/%m/%d %T' ) . '. Without any further information.' . PHP_EOL;
        break;
    }

    $sLog = $sHeader
            . $sChannels
            . $sCurrentLog
    ;

    return $sLog;
  }

  /**
   * _typo3UpdateRowLogChannel() :
   *
   * @return boolean
   * @access private
   * @version 7.0.0
   * @since 7.0.0
   */
  private function _typo3UpdateRowLogChannel( $table, $sImmo24id )
  {
    // RETURN: table uisn't the table for apartment rent
    if ( $table != $this->deal_tableappartmentrent )
    {
      return NULL;
    }

    // RETURN: there isn't any channel prompt
    if ( !isset( $this->_aLog[ 'channels' ][ $sImmo24id ] ) )
    {
      return NULL;
    }

    $aChannelPrompt = array();
    $aLog = $this->_aLog[ 'channels' ][ $sImmo24id ];
    foreach ( ( array ) $aLog as $iChannel => $sPrompt )
    {
      $aChannelPrompt[] = 'Channel: ' . $iChannel . ': ' . $sPrompt;
    }

    $sChannelPrompt = implode( ', ', $aChannelPrompt );
    $sChannelPrompt = '          (' . $sChannelPrompt . ')' . PHP_EOL;

    return $sChannelPrompt;
  }

  /**
   * _typo3UpdateRowUrl() :
   *
   * @return
   * @access private
   * @version 7.0.0
   * @since 7.0.0
   */
  private function _typo3UpdateRowUrl( $table, $sImmo24id, $fields_values )
  {
    // RETURN: table uisn't the table for apartment rent
    if ( $table != $this->deal_tableappartmentrent )
    {
      return $fields_values;
    }

    $keyImmo24url = $this->_typo3GetImmo24CtrlField( $table, 'immo24url' );

    switch ( TRUE )
    {
      case(is_int( $sImmo24id ) ):
        $valueImmo24urlexpose = $this->_typo3GetImmo24CtrlValue( $table, 'urlexpose' );
        $fields_values [ $keyImmo24url ] = $valueImmo24urlexpose . '/' . $sImmo24id;
        break;
      case(empty( $sImmo24id ) ):
      case( $sImmo24id == 'ERROR_RESOURCE_NOT_FOUND' ):
      case( $sImmo24id == 'MESSAGE_RESOURCE_DELETED' ):
      default:
        // no url
        break;
    }

    return $fields_values;
  }

  /**
   * _typo3UpdateRowLogPrefix() :
   *
   * @return boolean
   * @access private
   * @version 7.0.0
   * @since 7.0.0
   */
  private function _typo3UpdateRowLogPrefix()
  {
    switch ( $this->deal_liveorsandbox )
    {
      case( 'sandbox' ):
        $prefix = '[Sandbox] ';
        break;
      case( 'live' ):
      default:
        $prefix = '[Live] ';
        break;
    }

    return $prefix;
  }

  /**
   * _zzFlashMessage()    :
   *
   * @return boolean
   * @access private
   * @version 7.0.0
   * @since 7.0.0
   */
  private function _zzFlashMessage( $prompt, $severity )
  {
    switch ( $severity )
    {
      case 'ERROR':
        $this->_aAdminEmailPrompts[ 'error' ][] = $prompt;
        $message = GeneralUtility::makeInstance(
                        'TYPO3\\CMS\\Core\\Messaging\\FlashMessage', $prompt, null, // the header is optional
                        \TYPO3\CMS\Core\Messaging\FlashMessage::ERROR, // the severity is optional as well and defaults to \TYPO3\CMS\Core\Messaging\FlashMessage::OK
                        FALSE // optional, whether the message should be stored in the session or only in the \TYPO3\CMS\Core\Messaging\FlashMessageQueue object (default is FALSE)
        );
        break;
      case 'INFO':
        $this->_aAdminEmailPrompts[ 'info' ][] = $prompt;
        $message = GeneralUtility::makeInstance(
                        'TYPO3\\CMS\\Core\\Messaging\\FlashMessage', $prompt, null, // the header is optional
                        \TYPO3\CMS\Core\Messaging\FlashMessage::INFO, // the severity is optional as well and defaults to \TYPO3\CMS\Core\Messaging\FlashMessage::OK
                        FALSE // optional, whether the message should be stored in the session or only in the \TYPO3\CMS\Core\Messaging\FlashMessageQueue object (default is FALSE)
        );
        break;
      case 'OK':
        $this->_aAdminEmailPrompts[ 'ok' ][] = $prompt;
        $message = GeneralUtility::makeInstance(
                        'TYPO3\\CMS\\Core\\Messaging\\FlashMessage', $prompt, null, // the header is optional
                        \TYPO3\CMS\Core\Messaging\FlashMessage::OK, // the severity is optional as well and defaults to \TYPO3\CMS\Core\Messaging\FlashMessage::OK
                        FALSE // optional, whether the message should be stored in the session or only in the \TYPO3\CMS\Core\Messaging\FlashMessageQueue object (default is FALSE)
        );
        break;
      case 'WARNING':
        $this->_aAdminEmailPrompts[ 'warning' ][] = $prompt;
        $message = GeneralUtility::makeInstance(
                        'TYPO3\\CMS\\Core\\Messaging\\FlashMessage', $prompt, null, // the header is optional
                        \TYPO3\CMS\Core\Messaging\FlashMessage::WARNING, // the severity is optional as well and defaults to \TYPO3\CMS\Core\Messaging\FlashMessage::OK
                        FALSE // optional, whether the message should be stored in the session or only in the \TYPO3\CMS\Core\Messaging\FlashMessageQueue object (default is FALSE)
        );
        break;
      case 'NOTICE':
      default:
        $this->_aAdminEmailPrompts[ 'notice' ][] = $prompt;
        $message = GeneralUtility::makeInstance(
                        'TYPO3\\CMS\\Core\\Messaging\\FlashMessage', $prompt, null, // the header is optional
                        \TYPO3\CMS\Core\Messaging\FlashMessage::NOTICE, // the severity is optional as well and defaults to \TYPO3\CMS\Core\Messaging\FlashMessage::OK
                        FALSE // optional, whether the message should be stored in the session or only in the \TYPO3\CMS\Core\Messaging\FlashMessageQueue object (default is FALSE)
        );
        break;
    }
    \TYPO3\CMS\Core\Messaging\FlashMessageQueue::addMessage( $message );
  }

  /**
   * _zzImmo24MessageCode( ) :
   *
   * @param string $sResponse
   * @param string $method
   * @return string
   * @access private
   * @version 7.0.0
   * @since 7.0.0
   */
  private function _zzImmo24MessageCode( $sResponse )
  {

    $aResponse = json_decode( $sResponse, true );
//    var_dump( __METHOD__, __LINE__, $aResponse );
//die();
    if ( !isset( $aResponse[ 'common.messages' ][ '0' ][ 'message' ][ 'messageCode' ] ) )
    {
      return NULL;
    }

    return $aResponse[ 'common.messages' ][ '0' ][ 'message' ][ 'messageCode' ];
  }

  /**
   * _zzImmo24Xml( ) :
   *
   * @param array $row
   * @param array $immo24Fields
   * @return boolean
   * @access private
   * @version 7.0.0
   * @since 7.0.0
   */
  private function _zzImmo24Xml( $table, $row, $aImmo24Field, $sImmo24Field, $level )
  {
    $xml = NULL;

    $xml = $this->_zzImmo24XmlGetAttribute( $table, $row, $aImmo24Field, $sImmo24Field, $level );
    switch ( TRUE )
    {
      case( $xml === TRUE ):
        return NULL;
      case( $xml === FALSE ):
        // follow the workflow
        break;
      default:
        return $xml;
    }

    $key = $aImmo24Field[ 'field' ];
    if ( is_array( $key ) )
    {
      $xml = $xml . $this->_zzImmo24XmlGetArray( $table, $row, $aImmo24Field, $sImmo24Field, $level );
      return $xml;
    }

    $value = $this->_zzImmo24XmlGetValue( $table, $row, $aImmo24Field, $sImmo24Field );
    if ( $value === NULL )
    {
      return;
    }

    $sSpace = str_repeat( ' ', $level * 4 );
    $xml = $sSpace . '<' . $sImmo24Field . '>' . $value . '</' . $sImmo24Field . '>' . PHP_EOL;

    return $xml;
  }

  /**
   * _zzImmo24XmlGetArray( ) :
   *
   * @param array $row
   * @param array $immo24Fields
   * @return boolean
   * @access private
   * @version 7.0.0
   * @since 7.0.0
   */
  private function _zzImmo24XmlGetArray( $table, $row, $aImmo24Field, $sImmo24Field, $level )
  {
    $key = $aImmo24Field[ 'field' ];

    $sSpace = str_repeat( ' ', $level * 4 );
    $xml = $xml . $sSpace . '<' . $sImmo24Field . '>' . PHP_EOL;
    foreach ( ( array ) $key as $sField => $aField )
    {
      $level++;
      $xml = $xml . $this->_zzImmo24Xml( $table, $row, $aField, $sField, $level );
      $level--;
    }
    $xml = $xml . $sSpace . '</' . $sImmo24Field . '>' . PHP_EOL;
    return $xml;
  }

  /**
   * _zzImmo24XmlGetAttribute( ) :
   *
   * @param array $row
   * @param array $immo24Fields
   * @return boolean
   * @access private
   * @version 7.0.0
   * @since 7.0.0
   */
  private function _zzImmo24XmlGetAttribute( $table, $row, $aImmo24Field, $sImmo24Field, $level )
  {
    if ( !isset( $aImmo24Field[ 'attribute' ] ) )
    {
      return FALSE;
    }

    if ( $table != $this->deal_tableappartmentrent )
    {
      return FALSE;
    }

    switch ( $sImmo24Field )
    {
      case 'contact':
        return $this->_zzImmo24XmlGetAttributeAppartmentrentContact( $table, $row, $aImmo24Field, $sImmo24Field, $level );
      default:
        $prompt = 'Deal! WARNING: There isn\'t any case for the attribute ' . $this->deal_tablecontact . '.' . $sImmo24Field . '.' . LF
                . 'Method: ' . __METHOD__ . ' (' . __LINE__ . ')';
        ;
        $this->_zzFlashMessage( $prompt, 'WARNING' );
        return TRUE;
    }
  }

  /**
   * _zzImmo24XmlGetAttributeAppartmentrentContact( ) :
   *
   * @param array $row
   * @param array $immo24Fields
   * @return boolean
   * @access private
   * @version 7.0.0
   * @since 7.0.0
   */
  private function _zzImmo24XmlGetAttributeAppartmentrentContact( $table, $row, $aImmo24Field, $sImmo24Field, $level )
  {
    $value = $this->_zzImmo24XmlGetValue( $table, $row, $aImmo24Field, $sImmo24Field );
    if ( empty( $value ) )
    {
      $uid = $row[ 'uid' ];
      $field = $aImmo24Field[ 'field' ];
      $prompt = 'Deal! WARNING: ' . $table . '.' . $uid . '.' . $field . ' (immobilienscout24 field "' . $sImmo24Field . '") is empty!';
      $this->_zzFlashMessage( $prompt, 'WARNING' );
      return TRUE;
    }

    $andWhere = ' AND uid = ' . $value;
    $aContactRows = $this->_typo3GetTableRows( $this->deal_tablecontact, $andWhere );
    if ( empty( $aContactRows ) )
    {
      $uid = $row[ 'uid' ];
      $prompt = 'Deal! WARNING: ' . $this->deal_tablecontact . ' is empty ( SQL statement WHERE ... ' . $andWhere . ')';
      $this->_zzFlashMessage( $prompt, 'WARNING' );
      return TRUE;
    }

    $firstKey = key( $aContactRows );
    $keyImmo24contact = $this->_typo3GetImmo24CtrlField( $this->deal_tablecontact, 'immo24id' );
    $value = $aContactRows[ $firstKey ][ $keyImmo24contact ];
    $uid = $aContactRows[ $firstKey ][ 'uid' ];

    if ( empty( $value ) )
    {
      $uid = $row[ 'uid' ];
      $prompt = 'Deal! WARNING: ' . $this->deal_tablecontact . '.' . $uid . '.' . $keyImmo24contact . ' is empty.';
      $this->_zzFlashMessage( $prompt, 'WARNING' );
      return TRUE;
    }

    $sSpace = str_repeat( ' ', $level * 4 );
    $attribute = $aImmo24Field[ 'attribute' ];
    $xml = $sSpace . '<' . $sImmo24Field . ' ' . $attribute . '="' . $value . '" />' . PHP_EOL;

    return $xml;
  }

  /**
   * _zzImmo24XmlGetValue( ) :
   *
   * @param array $row
   * @param array $immo24Fields
   * @return string $value
   * @access private
   * @version 7.0.0
   * @since 7.0.0
   */
  private function _zzImmo24XmlGetValue( $table, $row, $aImmo24Field, $sImmo24Field )
  {
    $value = $this->_zzImmo24XmlGetValueDefault( $row, $aImmo24Field );
    $value = $this->_zzImmo24XmlGetValueMapping( $value, $aImmo24Field );
    $value = $this->_zzImmo24XmlGetValuePattern( $row, $value, $aImmo24Field, $table, $sImmo24Field );

    if ( $value != htmlspecialchars( $value ) )
    {
      $value = '<![CDATA[' . $value . ']]>';
    }

    return $value;
  }

  /**
   * _zzImmo24XmlGetValue( ) :
   *
   * @param array $row
   * @param array $immo24Fields
   * @return string $value
   * @access private
   * @version 7.0.0
   * @since 7.0.0
   */
  private function _zzImmo24XmlGetValueDefault( $row, $aImmo24Field )
  {
    $key = $aImmo24Field[ 'field' ];
    $value = NULL;

    if ( !empty( $key ) )
    {
      $value = $row[ $key ];
    }

    if ( $value === NULL )
    {
      $value = $aImmo24Field[ 'default' ];
    }

    return $value;
  }

  /**
   * _zzImmo24XmlGetValueMapping( ) :
   *
   * @param string $value
   * @param array $immo24Fields
   * @return string $value
   * @access private
   * @version 7.0.0
   * @since 7.0.0
   */
  private function _zzImmo24XmlGetValueMapping( $value, $aImmo24Field )
  {
    if ( !isset( $aImmo24Field[ 'mapping' ] ) )
    {
      return $value;
    }

    $aMapping = $aImmo24Field[ 'mapping' ];

    if ( isset( $aMapping[ $value ] ) )
    {
      $value = $aMapping[ $value ];
    }

    return $value;
  }

  /**
   * _zzImmo24XmlGetValuePattern( ) :
   *
   * @param string $value
   * @param array $immo24Fields
   * @return string $value
   * @access private
   * @version 7.0.0
   * @since 7.0.0
   */
  private function _zzImmo24XmlGetValuePattern( $row, $value, $aImmo24Field, $table, $sImmo24Field )
  {
    if ( !isset( $aImmo24Field[ 'field' ] ) )
    {
      return $value;
    }

    if ( !isset( $aImmo24Field[ 'pattern' ] ) )
    {
      return $value;
    }

    $key = $aImmo24Field[ 'field' ];
    $sPattern = '/' . $aImmo24Field[ 'pattern' ] . '/';

    $aResult = array();
    //var_dump( __METHOD__, __LINE__, $sPattern );
    $bMatchesPartOrWhole = preg_match( $sPattern, $value, $aResult );
    $bMatches = FALSE;

    //var_dump( __METHOD__, __LINE__, $table, $key, $sImmo24Field, $value, $sPattern, $bMatchesPartOrWhole, $aResult );

    switch ( TRUE )
    {
      case(!$bMatchesPartOrWhole ):
        $bMatches = FALSE;
        break;
      case( $aResult[ 0 ] == $value ):
        $bMatches = TRUE;
        break;
      default :
        $bMatches = FALSE;
        break;
    }

    if ( $bMatches )
    {
      return $value;
    }

    $sSample = $aImmo24Field[ 'patternsample' ];
    if ( !empty( $sSample ) )
    {
      $sSample = 'This should match ' . $sSample . '. ';
    }
    $uid = $row[ 'uid' ];
    $prompt = 'Deal! WARNING: Pattern ins\'t matched at ' . $table . '.' . $uid . '.' . $key . ' (immobilienscout24 field "' . $sImmo24Field . '").' . LF
            . 'Pattern is "' . $sPattern . '". ' . $sSample . 'The given value "' . $value . '" won\'t exported!'
    ;
    $this->_zzFlashMessage( $prompt, 'WARNING' );
    return NULL;
  }

  /**
   * _zzIsUptodate( ) :
   *
   * @param array $rows
   * @return boolean
   * @access private
   * @version 7.0.0
   * @since 7.0.0
   */
  private function _zzIsUptodate( $table, $row )
  {
    $sTypo3Tstamp = $this->_typo3GetCtrlField( $table, 'tstamp' );
    $sImmo24Tstamp = $this->_typo3GetImmo24CtrlField( $table, 'immo24timestamp' );

    $uTypo3Tstamp = $row[ $sTypo3Tstamp ];
    $uImmo24Tstamp = $row[ $sImmo24Tstamp ];

    // RETURN FALSE: record isn't never saved
    if ( $uTypo3Tstamp <= 0 )
    {
      return FALSE;
    }

    // RETURN TRUE: timestamp of TYPO3 record is smaller or equal to timestamp of immo24 record
    if ( $uTypo3Tstamp <= $uImmo24Tstamp )
    {
      $this->_aStatistic[ 'immo24' ][ $table ][ 'records' ][ 'all' ][ 'noneedforupdate' ] ++;
      $this->_aStatistic[ 'TYPO3' ][ $table ][ 'records' ][ 'noneedforupdate' ] ++;
      return TRUE;
    }

    return FALSE;
  }

  /**
   * _zzPromptErrorImmo24ResponseArray( ) :
   *
   * @param array $row
   * @param string $sResponse
   * @param string $method
   * @param string $xml
   * @return mixed
   * @access private
   * @version 7.0.0
   * @since 7.0.0
   */
  private function _zzPromptErrorImmo24ResponseArray( $row, $aResponse, $method, $xml )
  {
    if ( !isset( $aResponse[ 'common.messages' ][ '0' ][ 'message' ] ) )
    {
      return FALSE;
    }

    $sMessageCode = $aResponse[ 'common.messages' ][ '0' ][ 'message' ][ 'messageCode' ];
    $sMessagePrompt = $aResponse[ 'common.messages' ][ '0' ][ 'message' ][ 'message' ];

    switch ( TRUE )
    {
      case(isset( $aResponse[ 'common.messages' ][ '0' ][ 'message' ][ 'id' ] )):
        return $aResponse[ 'common.messages' ][ '0' ][ 'message' ][ 'id' ];
      case($sMessageCode == 'MESSAGE_RESOURCE_UPDATED'):
        return $sMessageCode;
      case($sMessageCode == 'ERROR_RESOURCE_NOT_FOUND'):
        $xml = NULL;
        break;
      default:
        // follow the workflow
        break;
    }
//    var_dump( __METHOD__, __LINE__ );

    $sTitleKey = $this->_typo3GetCtrlField( $this->deal_tableappartmentrent, 'label' );
    $sTitleValue = $row[ $sTitleKey ];

    $prompt = $this->_extLabel . ': immo24 message code: ' . $sMessageCode . '<br />' . LF
            . 'immo24 message prompt: ' . $sMessagePrompt . '<br />' . LF
            . 'Method: ' . $method . '<br />' . LF
            . 'Table: ' . $this->deal_tableappartmentrent . '.' . $row[ 'uid' ] . '.' . $sTitleKey . ': "' . $sTitleValue . '"'
    ;

    $this->_zzFlashMessage( $prompt, 'ERROR' );

    if ( empty( $xml ) )
    {
      return $sMessageCode;
    }

    $prompt = $this->_extLabel . ': XML code: <br />' . LF
            . htmlentities( $xml );
    $prompt = nl2br( $prompt );

    $this->_zzFlashMessage( $prompt, 'WARNING' );

    return $sMessageCode;
  }

  /**
   * _zzPromptErrorSql( ) :
   *
   * @return void
   * @access private
   * @version 7.0.0
   * @since 7.0.0
   */
  private function _zzPromptErrorSql( $query )
  {
    $error = $GLOBALS[ 'TYPO3_DB' ]->sql_error();

    if ( !$error )
    {
      return FALSE;
    }

    $prompt = $this->_extLabel . ': ' . 'SQL ERROR<br />' . LF
            . 'query: ' . $query . '<br />' . LF
            . 'error: ' . $error . '<br />' . LF
    ;

    $this->_zzFlashMessage( $prompt, 'ERROR' );

    return TRUE;
  }

  /**
   * _zzPromptErrorTcaCtrl()    :
   *
   * @return void
   * @access private
   * @version 7.0.0
   * @since 7.0.0
   */
  private function _zzPromptErrorTcaCtrl( $table, $tca )
  {
    $tca = "\$TCA[ '" . $table . "' ][ 'ctrl' ][ 'tx_deal' ][ 'marketplaces' ][ 'immo24' ]";

    $prompt = $this->_extLabel . ': ' . $GLOBALS[ 'LANG' ]->sL( 'LLL:EXT:deal/Classes/Scheduler/locallang.xlf:immo24.prompt.errortcactrl' );
    $prompt = str_replace( '%table%', $table, $prompt );
    $prompt = str_replace( '%tca%', $tca, $prompt );

    $this->_zzFlashMessage( $prompt, 'ERROR' );
  }

  /**
   * _zzValidateTcaCtrl()    :
   *
   * @return boolean
   * @access private
   * @version 7.0.0
   * @since 7.0.0
   */
  private function _zzValidateTcaCtrl( $table )
  {
    if ( isset( $GLOBALS[ 'TCA' ][ $table ][ 'ctrl' ][ 'tx_deal' ][ 'marketplaces' ][ 'immo24' ] ) )
    {
      return TRUE;
    }

    $tca = "\$TCA[ '" . $table . "' ][ 'ctrl' ][ 'tx_deal' ][ 'marketplaces' ][ 'immo24' ]";
    $this->_zzPromptErrorTcaCtrl( $table, $tca );
    return FALSE;
  }

  /**
   * run( ) : Function executed from the Scheduler.
   *              Exports items to immobilienscout24
   *
   * @param array $aParams  Array of task variables
   * @return boolean
   * @access public
   * @version 7.0.0
   * @since 7.0.0
   */
  public function run( $aParams )
  {
    $_bSuccess = TRUE;

    if ( !$this->_init( $aParams ) )
    {
      $this->_email();
      return FALSE;
    }

    if ( !$this->_immo24Export() )
    {
      $_bSuccess = FALSE;
    }

    if ( !$this->_email() )
    {
      $_bSuccess = FALSE;
    }

    return $_bSuccess;
  }

}
