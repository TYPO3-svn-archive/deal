<?php

namespace Netzmacher\Deal\Scheduler;

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
class TestTask extends \Netzmacher\Deal\Scheduler\TestTaskExecute
{

  /**
   * An email address to be used during the process
   *
   * @var string $deal_adminemail
   */
  public $deal_adminemail;

  /**
   * Current task uid
   *
   * @var string $taskUid
   */
  public $taskUid;

  /**
   * run( ) : Function executed from the Scheduler.
   *          Sends an email
   *
   * @access public
   * @return boolean
   */
  public function run( $taskUid, $deal_adminemail )
  {
    $success = FALSE;

    $this->deal_adminemail = $deal_adminemail;
    $this->taskUid = $taskUid;

    if ( empty( $this->deal_adminemail ) )
    {
      // No email defined, just log the task
      $this->promptErrorNoEmail();
      \TYPO3\CMS\Core\Utility\GeneralUtility::devLog( '[TYPO3\\CMS\\Scheduler\\Example\\TestTask]: No email address given', 'deal', 2 );
      return $success;
    }

    // If an email address is defined, send a message to it
    // NOTE: the TYPO3_DLOG constant is not used in this case, as this is a test task
    // and debugging is its main purpose anyway
    \TYPO3\CMS\Core\Utility\GeneralUtility::devLog( '[TYPO3\\CMS\\Scheduler\\Example\\TestTask]: Test email sent to "' . $this->deal_adminemail . '"', 'deal', 0 );

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
      $site = \TYPO3\CMS\Core\Utility\GeneralUtility::getIndpEnv( 'TYPO3_SITE_URL' );
    }

    $start = $exec->getStart();
    $end = $exec->getEnd();
    $interval = $exec->getInterval();
    $multiple = $exec->getMultiple();
    $cronCmd = $exec->getCronCmd();
    $mailBody = 'DEAL! TEST-TASK' . LF
            . '- - - - - - - - - - - - - - - -' . LF
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
            . 'cronCmd: ' . ($cronCmd ? $cronCmd : 'not used');

    // Prepare mailer and send the mail
    try
    {
      /** @var $mailer \TYPO3\CMS\Core\Mail\MailMessage */
      $mailer = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance( 'TYPO3\\CMS\\Core\\Mail\\MailMessage' );
      $mailer->setFrom( array( $this->deal_adminemail => 'DEAL! TEST-TASK' ) );
      $mailer->setReplyTo( array( $this->deal_adminemail => 'DEAL! TEST-TASK' ) );
      $mailer->setSubject( 'DEAL! TEST-TASK' );
      $mailer->setBody( $mailBody );
      $mailer->setTo( $this->deal_adminemail );
      $mailsSend = $mailer->send();
      $this->promptHelpInCaseOfNoEmail( $mailsSend );
      $this->promptSuccess( $mailsSend );
      $success = $mailsSend > 0;
    }
    catch ( \Exception $e )
    {
      throw new \TYPO3\CMS\Core\Exception( $e->getMessage() );
    }
    return $success;
  }

  /**
   * This method returns the destination mail address as additional information
   *
   * @return string Information to display
   */
  public function getAdditionalInformation()
  {
    return $GLOBALS[ 'LANG' ]->sL( 'LLL:EXT:scheduler/mod1/locallang.xlf:label.email' ) . ': ' . $this->deal_adminemail;
  }

  /**
   * promptErrorNoEmail( ) :
   *
   * @access private
   * @return void
   */
  private function promptErrorNoEmail()
  {
    $prompt = 'Deal! Fatal error: e-mail address is missing.';
    $message = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(
                    'TYPO3\\CMS\\Core\\Messaging\\FlashMessage', $prompt, null, \TYPO3\CMS\Core\Messaging\FlashMessage::ERROR, TRUE
    );
    \TYPO3\CMS\Core\Messaging\FlashMessageQueue::addMessage( $message );
  }

  /**
   * promptHelpInCaseOfNoEmail( ) :
   *
   * @param integer $mailsSend    : number of e-mails, which are sent
   * @access private
   * @return void
   */
  private function promptHelpInCaseOfNoEmail( $mailsSend )
  {
    if ( $mailsSend > 0 )
    {
      return;
    }

    $prompt = 'Deal! Everything seem\'s to be proper but any e-mail isn\'t sent to: ' . $this->deal_adminemail . '<br />' . LF
            . 'Maybe you can\'t send e-mails from the current server.<br />' . LF
            . 'In this case please set<br />' . LF
            . '$TYPO3_CONF_VARS[MAIL][transport] = mbox <br />' . LF
            . '$TYPO3_CONF_VARS[MAIL][transport_mbox_file] = /var/mail/yourname <- path is an example'
    ;
    $message = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(
                    'TYPO3\\CMS\\Core\\Messaging\\FlashMessage', $prompt, null, \TYPO3\CMS\Core\Messaging\FlashMessage::NOTICE, TRUE
    );
    \TYPO3\CMS\Core\Messaging\FlashMessageQueue::addMessage( $message );
  }

  /**
   * promptSuccess( ) :
   *
   * @param integer $mailsSend    : number of e-mails, which are sent
   * @access private
   * @return void
   */
  private function promptSuccess( $mailsSend )
  {
    global $TYPO3_CONF_VARS;

    if ( $mailsSend <= 0 )
    {
      return;
    }

    $prompt = 'Deal! An e-mail is sent to: ' . $this->deal_adminemail;
    $message = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(
                    'TYPO3\\CMS\\Core\\Messaging\\FlashMessage', $prompt, null, \TYPO3\CMS\Core\Messaging\FlashMessage::OK, TRUE
    );
    \TYPO3\CMS\Core\Messaging\FlashMessageQueue::addMessage( $message );

    if ( $TYPO3_CONF_VARS[ 'MAIL' ][ 'transport' ] != 'mbox' )
    {
      return;
    }

    $prompt = 'Deal! The e-mail hasn\'t left your local server. It is stored in your mbox at ' . $TYPO3_CONF_VARS[ 'MAIL' ][ 'transport_mbox_file' ] . '.'
    ;
    $message = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(
                    'TYPO3\\CMS\\Core\\Messaging\\FlashMessage', $prompt, null, \TYPO3\CMS\Core\Messaging\FlashMessage::INFO, TRUE
    );
    \TYPO3\CMS\Core\Messaging\FlashMessageQueue::addMessage( $message );
  }

}