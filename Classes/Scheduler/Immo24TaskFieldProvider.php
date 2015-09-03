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
 * Field provider for Scheduler task
 */
class Immo24TaskFieldProvider implements \TYPO3\CMS\Scheduler\AdditionalFieldProviderInterface
{

  /**
   * Extension label
   *
   * @access private
   * @var string $extLabel
   */
  private $extLabel = 'Deal!';

  /**
   * getAdditionalFields()        : This method returns the list of fields to display in the editing form.
   *                                This list of fields is a 2-dimensional array. The first dimension uses
   *                                the ID of the field (as used in the "id" attribute of the field tag).
   *                                Then for each field, there must be the HTML code to render the field,
   *                                the label of the field, the key and the label for the context-
   *                                sensitive help (CSH).
   *                                All this is documented in the \TYPO3\CMS\Scheduler\AdditionalFieldProviderInterface
   *                                interface and can be seen in action in the existing task classes.
   *
   * @param object $taskInfo      : an array containing the information about the current task.
   *                                May be modified inside this method to set default values, for example.
   * @param object $task          : the current task object (when editing; when adding, "null" is passed to the method).
   * @param object $parentObject  : a back-reference to the calling BE module's object.
   * @return array
   * @access public
   * @version 7.0.0
   * @since 7.0.0
   */
  public function getAdditionalFields( array &$taskInfo, $task, \TYPO3\CMS\Scheduler\Controller\SchedulerModuleController $parentObject )
  {
    $additionalFields = array() +
            ( array ) $this->getFieldAdminEmail( $taskInfo, $task, $parentObject ) +
            ( array ) $this->getFieldAdminEmailMode( $taskInfo, $task, $parentObject ) +
            ( array ) $this->getFieldLiveorsandbox( $task ) +
            ( array ) $this->getFieldKeylivepublic( $task ) +
            ( array ) $this->getFieldKeyliveprivate( $task ) +
            ( array ) $this->getFieldKeysandboxpublic( $task ) +
            ( array ) $this->getFieldKeysandboxprivate( $task ) +
            ( array ) $this->getFieldTableappartmentrent( $task ) +
            ( array ) $this->getFieldTablecontact( $task ) +
            ( array ) $this->getFieldCleanupImmo24( $taskInfo, $task, $parentObject ) +
            array()
    ;

    return $additionalFields;
  }

  /**
   * getFieldAdminEmail()        :
   *
   * @param object $taskInfo      : see getAdditionalFields()
   * @param object $task          : see getAdditionalFields()
   * @param object $parentObject  : see getAdditionalFields()
   * @return array
   * @access private
   * @version 7.0.0
   * @since 7.0.0
   */
  private function getFieldAdminEmail( array &$taskInfo, $task, \TYPO3\CMS\Scheduler\Controller\SchedulerModuleController $parentObject )
  {
    // Initialize extra field value
    if ( empty( $taskInfo[ 'deal_adminemail' ] ) )
    {
      if ( $parentObject->CMD == 'add' )
      {
        // In case of new task and if field is empty, set default email address
        $taskInfo[ 'deal_adminemail' ] = $GLOBALS[ 'BE_USER' ]->user[ 'email' ];
      }
      elseif ( $parentObject->CMD == 'edit' )
      {
        // In case of edit, and editing a test task, set to internal value if not data was submitted already
        $taskInfo[ 'deal_adminemail' ] = $task->deal_adminemail;
      }
      else
      {
        // Otherwise set an empty value, as it will not be used anyway
        $taskInfo[ 'deal_adminemail' ] = '';
      }
    }

    // Write the code for the field
    $fieldID = 'deal_adminemail';
    $fieldValue = htmlspecialchars( $taskInfo[ 'deal_adminemail' ] );
    $fieldCode = '<input type="text" name="tx_scheduler[deal_adminemail]" id="' . $fieldID . '" value="' . $fieldValue . '" size="30" />';

    $additionalFields = array(
      $fieldID => array(
        'code' => $fieldCode,
        'label' => 'LLL:EXT:deal/Classes/Scheduler/locallang.xlf:immo24.field.adminemail',
        'cshKey' => '_MOD_tools_txschedulerM1',
        'cshLabel' => $fieldID
      )
    );

    return $additionalFields;
  }

  /**
   * getFieldAdminEmailMode()        :
   *
   * @param object $taskInfo      : see getAdditionalFields()
   * @param object $task          : see getAdditionalFields()
   * @param object $parentObject  : see getAdditionalFields()
   * @return array
   * @access private
   * @version 7.0.0
   * @since 7.0.0
   */
  private function getFieldAdminEmailMode( array &$taskInfo, $task, \TYPO3\CMS\Scheduler\Controller\SchedulerModuleController $parentObject )
  {
    $fieldID = 'deal_adminemailmode';
    $fieldValue = $task->deal_adminemailmode;

    $optionAll = $GLOBALS[ 'LANG' ]->sL( 'LLL:EXT:deal/Classes/Scheduler/locallang.xlf:immo24.field.adminemailmode.all' );
    $optionNothing = $GLOBALS[ 'LANG' ]->sL( 'LLL:EXT:deal/Classes/Scheduler/locallang.xlf:immo24.field.adminemailmode.nothing' );
    $optionUpdate = $GLOBALS[ 'LANG' ]->sL( 'LLL:EXT:deal/Classes/Scheduler/locallang.xlf:immo24.field.adminemailmode.update' );
    $optionWarn = $GLOBALS[ 'LANG' ]->sL( 'LLL:EXT:deal/Classes/Scheduler/locallang.xlf:immo24.field.adminemailmode.warn' );

    $selected = array(
      'live' => null,
      'sandbox' => null,
      $fieldValue => ' selected="selected"'
    );

    $fieldCode = '
      <select name="tx_scheduler[deal_adminemailmode]" id="' . $fieldID . '" size="1" style="width:30em;">
        <option value="warn"' . $selected[ 'warn' ] . '>' . $optionWarn . '</option>
        <option value="all"' . $selected[ 'all' ] . '>' . $optionAll . '</option>
        <option value="nothing"' . $selected[ 'nothing' ] . '>' . $optionNothing . '</option>
        <option value="update"' . $selected[ 'update' ] . '>' . $optionUpdate . '</option>
      </select>
    ';

    $additionalFields = array(
      $fieldID => array(
        'code' => $fieldCode,
        'label' => 'LLL:EXT:deal/Classes/Scheduler/locallang.xlf:immo24.field.adminemailmode',
        'cshKey' => '_MOD_tools_txschedulerM1',
        'cshLabel' => $fieldID
      )
    );

    return $additionalFields;
  }

  /**
   * getFieldCleanupImmo24()        :
   *
   * @param object $taskInfo      : see getAdditionalFields()
   * @param object $task          : see getAdditionalFields()
   * @param object $parentObject  : see getAdditionalFields()
   * @return array
   * @access private
   * @version 7.0.0
   * @since 7.0.0
   */
  private function getFieldCleanupImmo24( array &$taskInfo, $task, \TYPO3\CMS\Scheduler\Controller\SchedulerModuleController $parentObject )
  {
    $fieldID = 'deal_cleanupimmo24';
    $fieldValue = $task->deal_cleanupimmo24;

    $optionAllAll = $GLOBALS[ 'LANG' ]->sL( 'LLL:EXT:deal/Classes/Scheduler/locallang.xlf:immo24.field.cleanupimmo24.all.all' );
    $optionApartmentsrentAll = $GLOBALS[ 'LANG' ]->sL( 'LLL:EXT:deal/Classes/Scheduler/locallang.xlf:immo24.field.cleanupimmo24.apartmentsrentAll' );
    $optionAllContacts = $GLOBALS[ 'LANG' ]->sL( 'LLL:EXT:deal/Classes/Scheduler/locallang.xlf:immo24.field.cleanupimmo24.all.contacts' );
    $optionKnownAll = $GLOBALS[ 'LANG' ]->sL( 'LLL:EXT:deal/Classes/Scheduler/locallang.xlf:immo24.field.cleanupimmo24.known.all' );
    $optionApartmentsrentKnown = $GLOBALS[ 'LANG' ]->sL( 'LLL:EXT:deal/Classes/Scheduler/locallang.xlf:immo24.field.cleanupimmo24.apartmentsrentKnown' );
    $optionKnownContacts = $GLOBALS[ 'LANG' ]->sL( 'LLL:EXT:deal/Classes/Scheduler/locallang.xlf:immo24.field.cleanupimmo24.known.contacts' );
    $optionNothing = $GLOBALS[ 'LANG' ]->sL( 'LLL:EXT:deal/Classes/Scheduler/locallang.xlf:immo24.field.cleanupimmo24.nothing' );
    $optionUnknownAll = $GLOBALS[ 'LANG' ]->sL( 'LLL:EXT:deal/Classes/Scheduler/locallang.xlf:immo24.field.cleanupimmo24.unknown.all' );
    $optionApartmentsrentUnknown = $GLOBALS[ 'LANG' ]->sL( 'LLL:EXT:deal/Classes/Scheduler/locallang.xlf:immo24.field.cleanupimmo24.apartmentsrentUnknown' );
    $optionUnknownContacts = $GLOBALS[ 'LANG' ]->sL( 'LLL:EXT:deal/Classes/Scheduler/locallang.xlf:immo24.field.cleanupimmo24.unknown.contacts' );

    $selected = array(
      'allAll' => null,
      'apartmentsrentAll' => null,
      'allContacts' => null,
      'knownAll' => null,
      'apartmentsrentKnown' => null,
      'knownContacts' => null,
      'nothing' => null,
      'unknownAll' => null,
      'apartmentsrentUnknown' => null,
      'unknownContacts' => null,
      $fieldValue => ' selected="selected"'
    );

//    $fieldCode = '
//      <select name="tx_scheduler[deal_cleanupimmo24]" id="' . $fieldID . '" size="1" style="width:30em;">
//        <option value="nothing"' . $selected[ 'nothing' ] . '>' . $optionNothing . '</option>
//        <option value="allAll"' . $selected[ 'allAll' ] . '>' . $optionAllAll . '</option>
//        <option value="apartmentsrentAll"' . $selected[ 'apartmentsrentAll' ] . '>' . $optionApartmentsrentAll . '</option>
//        <option value="allContacts"' . $selected[ 'allContacts' ] . '>' . $optionAllContacts . '</option>
//        <option value="knownAll"' . $selected[ 'knownAll' ] . '>' . $optionKnownAll . '</option>
//        <option value="apartmentsrentKnown"' . $selected[ 'apartmentsrentKnown' ] . '>' . $optionApartmentsrentKnown . '</option>
//        <option value="knownContacts"' . $selected[ 'knownContacts' ] . '>' . $optionKnownContacts . '</option>
//        <option value="unknownAll"' . $selected[ 'unknownAll' ] . '>' . $optionUnknownAll . '</option>
//        <option value="apartmentsrentUnknown"' . $selected[ 'apartmentsrentUnknown' ] . '>' . $optionApartmentsrentUnknown . '</option>
//        <option value="unknownContacts"' . $selected[ 'unknownContacts' ] . '>' . $optionUnknownContacts . '</option>
//      </select>
//    ';

    $fieldCode = '
      <select name="tx_scheduler[deal_cleanupimmo24]" id="' . $fieldID . '" size="1" style="width:30em;">
        <option value="nothing"' . $selected[ 'nothing' ] . '>' . $optionNothing . '</option>
        <option value="apartmentsrentAll"' . $selected[ 'apartmentsrentAll' ] . '>' . $optionApartmentsrentAll . '</option>
        <option value="apartmentsrentKnown"' . $selected[ 'apartmentsrentKnown' ] . '>' . $optionApartmentsrentKnown . '</option>
        <option value="apartmentsrentUnknown"' . $selected[ 'apartmentsrentUnknown' ] . '>' . $optionApartmentsrentUnknown . '</option>
      </select>
    ';

    $additionalFields = array(
      $fieldID => array(
        'code' => $fieldCode,
        'label' => 'LLL:EXT:deal/Classes/Scheduler/locallang.xlf:immo24.field.cleanupimmo24',
        'cshKey' => '_MOD_tools_txschedulerM1',
        'cshLabel' => $fieldID
      )
    );

    return $additionalFields;
  }

  /**
   * getFieldKeyliveprivate()      :
   *
   * @param object $task          : see getAdditionalFields()
   * @return array
   * @access private
   * @version 7.0.0
   * @since 7.0.0
   */
  private function getFieldKeyliveprivate( $task )
  {
    $fieldID = 'deal_keyliveprivate';
    $fieldValue = htmlspecialchars( $task->deal_keyliveprivate );
    $fieldCode = '<input type="text" name="tx_scheduler[deal_keyliveprivate]" id="' . $fieldID . '" value="' . $fieldValue . '" size="30" />';

    $additionalFields = array(
      $fieldID => array(
        'code' => $fieldCode,
        'label' => 'LLL:EXT:deal/Classes/Scheduler/locallang.xlf:immo24.field.keyliveprivate',
        'cshKey' => '_MOD_tools_txschedulerM1',
        'cshLabel' => $fieldID
      )
    );

    return $additionalFields;
  }

  /**
   * getFieldKeylivepublic()      :
   *
   * @param object $task          : see getAdditionalFields()
   * @return array
   * @access private
   * @version 7.0.0
   * @since 7.0.0
   */
  private function getFieldKeylivepublic( $task )
  {
    $fieldID = 'deal_keylivepublic';
    $fieldValue = htmlspecialchars( $task->deal_keylivepublic);
    $fieldCode = '<input type="text" name="tx_scheduler[deal_keylivepublic]" id="' . $fieldID . '" value="' . $fieldValue . '" size="30" />';

    $additionalFields = array(
      $fieldID => array(
        'code' => $fieldCode,
        'label' => 'LLL:EXT:deal/Classes/Scheduler/locallang.xlf:immo24.field.keylivepublic',
        'cshKey' => '_MOD_tools_txschedulerM1',
        'cshLabel' => $fieldID
      )
    );

    return $additionalFields;
  }

  /**
   * getFieldKeysandboxprivate()      :
   *
   * @param object $task          : see getAdditionalFields()
   * @return array
   * @access private
   * @version 7.0.0
   * @since 7.0.0
   */
  private function getFieldKeysandboxprivate( $task )
  {
    $fieldID = 'deal_keysandboxprivate';
    $fieldValue = htmlspecialchars( $task->deal_keysandboxprivate);
    $fieldCode = '<input type="text" name="tx_scheduler[deal_keysandboxprivate]" id="' . $fieldID . '" value="' . $fieldValue . '" size="30" />';

    $additionalFields = array(
      $fieldID => array(
        'code' => $fieldCode,
        'label' => 'LLL:EXT:deal/Classes/Scheduler/locallang.xlf:immo24.field.keysandboxprivate',
        'cshKey' => '_MOD_tools_txschedulerM1',
        'cshLabel' => $fieldID
      )
    );

    return $additionalFields;
  }

  /**
   * getFieldKeysandboxpublic()      :
   *
   * @param object $task          : see getAdditionalFields()
   * @return array
   * @access private
   * @version 7.0.0
   * @since 7.0.0
   */
  private function getFieldKeysandboxpublic( $task )
  {
    $fieldID = 'deal_keysandboxpublic';
    $fieldValue = htmlspecialchars( $task->deal_keysandboxpublic);
    $fieldCode = '<input type="text" name="tx_scheduler[deal_keysandboxpublic]" id="' . $fieldID . '" value="' . $fieldValue . '" size="30" />';

    $additionalFields = array(
      $fieldID => array(
        'code' => $fieldCode,
        'label' => 'LLL:EXT:deal/Classes/Scheduler/locallang.xlf:immo24.field.keysandboxpublic',
        'cshKey' => '_MOD_tools_txschedulerM1',
        'cshLabel' => $fieldID
      )
    );

    return $additionalFields;
  }

  /**
   * getFieldLiveorsandbox()      :
   *
   * @param object $task          : see getAdditionalFields()
   * @return array
   * @access private
   * @version 7.0.0
   * @since 7.0.0
   */
  private function getFieldLiveorsandbox( $task )
  {
    $fieldID = 'deal_liveorsandbox';
    $fieldValue = $task->deal_liveorsandbox;

    $optionLive = $GLOBALS[ 'LANG' ]->sL( 'LLL:EXT:deal/Classes/Scheduler/locallang.xlf:immo24.field.liveorsandbox.live' );
    $optionSandbox = $GLOBALS[ 'LANG' ]->sL( 'LLL:EXT:deal/Classes/Scheduler/locallang.xlf:immo24.field.liveorsandbox.sandbox' );

    $selected = array(
      'live' => null,
      'sandbox' => null,
      $fieldValue => ' selected="selected"'
    );

    $fieldCode = '
      <select name="tx_scheduler[deal_liveorsandbox]" id="' . $fieldID . '" size="1" style="width:10em;">
        <option value="live"' . $selected[ 'live' ] . '>' . $optionLive . '</option>
        <option value="sandbox"' . $selected[ 'sandbox' ] . '>' . $optionSandbox . '</option>
      </select>
    ';

    $additionalFields = array(
      $fieldID => array(
        'code' => $fieldCode,
        'label' => 'LLL:EXT:deal/Classes/Scheduler/locallang.xlf:immo24.field.liveorsandbox',
        'cshKey' => '_MOD_tools_txschedulerM1',
        'cshLabel' => $fieldID
      )
    );

    return $additionalFields;
  }

  /**
   * getFieldTableappartmentrent()      :
   *
   * @param object $task          : see getAdditionalFields()
   * @return array
   * @access private
   * @version 7.0.0
   * @since 7.0.0
   */
  private function getFieldTableappartmentrent( $task )
  {
    $sFieldValue = $task->deal_tableappartmentrent;
    $sOptions = $this->zzTablesList( $sFieldValue );

    $fieldID = 'deal_tableappartmentrent';

    $fieldCode = '
      <select name="tx_scheduler[deal_tableappartmentrent]" id="' . $fieldID . '" size="1" style="width:30em;">
        <option></option>
' . $sOptions . '
      </select>
    ';

    $additionalFields = array(
      $fieldID => array(
        'code' => $fieldCode,
        'label' => 'LLL:EXT:deal/Classes/Scheduler/locallang.xlf:immo24.field.tableappartmentrent',
        'cshKey' => '_MOD_tools_txschedulerM1',
        'cshLabel' => $fieldID
      )
    );

    return $additionalFields;
  }

  /**
   * getFieldTablecontact()      :
   *
   * @param object $task          : see getAdditionalFields()
   * @return array
   * @access private
   * @version 7.0.0
   * @since 7.0.0
   */
  private function getFieldTablecontact( $task )
  {
    $sFieldValue = $task->deal_tablecontact;
    $sOptions = $this->zzTablesList( $sFieldValue );

    $fieldID = 'deal_tablecontact';

    $fieldCode = '
      <select name="tx_scheduler[deal_tablecontact]" id="' . $fieldID . '" size="1" style="width:30em;">
        <option></option>
' . $sOptions . '
      </select>
    ';

    $additionalFields = array(
      $fieldID => array(
        'code' => $fieldCode,
        'label' => 'LLL:EXT:deal/Classes/Scheduler/locallang.xlf:immo24.field.tablecontact',
        'cshKey' => '_MOD_tools_txschedulerM1',
        'cshLabel' => $fieldID
      )
    );

    return $additionalFields;
  }

  /**
   * saveAdditionalFields()       : This method is used to store the values contained in the additional
   *                                fields. The simplest method is to simply assign them to member
   *                                variables, so that they will be stored along the serialized task
   *                                object in the database (see :ref:`technical-background`).
   *
   * @param object $submittedData : array of values from the submitted form. May be modified inside the method.
   * @param object $task          : the current task object (when editing; when adding, "null" is passed to the method).
   * @return void
   * @access public
   * @version 7.0.0
   * @since 7.0.0
   */
  public function saveAdditionalFields( array $submittedData, \TYPO3\CMS\Scheduler\Task\AbstractTask $task )
  {
    $this->saveFieldAdminEmail( $submittedData, $task );
    $this->saveFieldAdminEmailMode( $submittedData, $task );
    $this->saveFieldCleanupImmo24( $submittedData, $task );
    $this->saveFieldKeyliveprivate( $submittedData, $task );
    $this->saveFieldKeylivepublic( $submittedData, $task );
    $this->saveFieldKeysandboxprivate( $submittedData, $task );
    $this->saveFieldKeysandboxpublic( $submittedData, $task );
    $this->saveFieldLiveorsandbox( $submittedData, $task );
    $this->saveFieldTableappartmentrent( $submittedData, $task );
    $this->saveFieldTablecontact( $submittedData, $task );
  }

  /**
   * saveFieldAdminEmail()        :
   *
   * @param object $submittedData : see saveAdditionalFields()
   * @param object $task          : see saveAdditionalFields()
   * @return void
   * @access private
   * @version 7.0.0
   * @since 7.0.0
   */
  private function saveFieldAdminEmail( array $submittedData, \TYPO3\CMS\Scheduler\Task\AbstractTask $task )
  {
    $task->deal_adminemail = $submittedData[ 'deal_adminemail' ];
  }

  /**
   * saveFieldAdminEmailMode()        :
   *
   * @param object $submittedData : see saveAdditionalFields()
   * @param object $task          : see saveAdditionalFields()
   * @return void
   * @access private
   * @version 7.0.0
   * @since 7.0.0
   */
  private function saveFieldAdminEmailMode( array $submittedData, \TYPO3\CMS\Scheduler\Task\AbstractTask $task )
  {
    $task->deal_adminemailmode = $submittedData[ 'deal_adminemailmode' ];
  }

  /**
   * saveFieldCleanupImmo24()        :
   *
   * @param object $submittedData : see saveAdditionalFields()
   * @param object $task          : see saveAdditionalFields()
   * @return void
   * @access private
   * @version 7.0.0
   * @since 7.0.0
   */
  private function saveFieldCleanupImmo24( array $submittedData, \TYPO3\CMS\Scheduler\Task\AbstractTask $task )
  {
    $task->deal_cleanupimmo24 = $submittedData[ 'deal_cleanupimmo24' ];
  }

  /**
   * saveFieldKeyliveprivate()        :
   *
   * @param object $submittedData : see saveAdditionalFields()
   * @param object $task          : see saveAdditionalFields()
   * @return void
   * @access private
   * @version 7.0.0
   * @since 7.0.0
   */
  private function saveFieldKeyliveprivate( array $submittedData, \TYPO3\CMS\Scheduler\Task\AbstractTask $task )
  {
    $task->deal_keyliveprivate = $submittedData[ 'deal_keyliveprivate' ];
  }

  /**
   * saveFieldKeylivepublic()        :
   *
   * @param object $submittedData : see saveAdditionalFields()
   * @param object $task          : see saveAdditionalFields()
   * @return void
   * @access private
   * @version 7.0.0
   * @since 7.0.0
   */
  private function saveFieldKeylivepublic( array $submittedData, \TYPO3\CMS\Scheduler\Task\AbstractTask $task )
  {
    $task->deal_keylivepublic = $submittedData[ 'deal_keylivepublic' ];
  }

  /**
   * saveFieldKeysandboxprivate()        :
   *
   * @param object $submittedData : see saveAdditionalFields()
   * @param object $task          : see saveAdditionalFields()
   * @return void
   * @access private
   * @version 7.0.0
   * @since 7.0.0
   */
  private function saveFieldKeysandboxprivate( array $submittedData, \TYPO3\CMS\Scheduler\Task\AbstractTask $task )
  {
    $task->deal_keysandboxprivate = $submittedData[ 'deal_keysandboxprivate' ];
  }

  /**
   * saveFieldKeysandboxpublic()        :
   *
   * @param object $submittedData : see saveAdditionalFields()
   * @param object $task          : see saveAdditionalFields()
   * @return void
   * @access private
   * @version 7.0.0
   * @since 7.0.0
   */
  private function saveFieldKeysandboxpublic( array $submittedData, \TYPO3\CMS\Scheduler\Task\AbstractTask $task )
  {
    $task->deal_keysandboxpublic = $submittedData[ 'deal_keysandboxpublic' ];
  }

  /**
   * saveFieldLiveorsandbox()        :
   *
   * @param object $submittedData : see saveAdditionalFields()
   * @param object $task          : see saveAdditionalFields()
   * @return void
   * @access private
   * @version 7.0.0
   * @since 7.0.0
   */
  private function saveFieldLiveorsandbox( array $submittedData, \TYPO3\CMS\Scheduler\Task\AbstractTask $task )
  {
    $task->deal_liveorsandbox = $submittedData[ 'deal_liveorsandbox' ];
  }

  /**
   * saveFieldTableappartmentrent()        :
   *
   * @param object $submittedData : see saveAdditionalFields()
   * @param object $task          : see saveAdditionalFields()
   * @return void
   * @access private
   * @version 7.0.0
   * @since 7.0.0
   */
  private function saveFieldTableappartmentrent( array $submittedData, \TYPO3\CMS\Scheduler\Task\AbstractTask $task )
  {
    $task->deal_tableappartmentrent = $submittedData[ 'deal_tableappartmentrent' ];
  }

  /**
   * saveFieldTablecontact()        :
   *
   * @param object $submittedData : see saveAdditionalFields()
   * @param object $task          : see saveAdditionalFields()
   * @return void
   * @access private
   * @version 7.0.0
   * @since 7.0.0
   */
  private function saveFieldTablecontact( array $submittedData, \TYPO3\CMS\Scheduler\Task\AbstractTask $task )
  {
    $task->deal_tablecontact = $submittedData[ 'deal_tablecontact' ];
  }

  /**
   * validateAdditionalFields()   : This method is called to validate the values that were input in the
   *                                additional fields provided by the specific task. It is expected to
   *                                return false if any of the fields contained errors, true otherwise.
   *                                The method should use the parent object's addMessage() method
   *                                to output messages about validation errors.
   *
   * @param object $submittedData : array of values from the submitted form. May be modified inside the method.
   * @param object $parentObject  : a back-reference to the calling BE module's object.
   * @return boolean
   * @access public
   * @version 7.0.0
   * @since 7.0.0
   */
  public function validateAdditionalFields( array &$submittedData, \TYPO3\CMS\Scheduler\Controller\SchedulerModuleController $parentObject )
  {
    $bool_isValidatingSuccessful = true;

    if ( !$this->validateFieldAdminEmail( $submittedData, $parentObject ) )
    {
      $bool_isValidatingSuccessful = false;
    }

    if ( !$this->validateFieldAdminEmailMode( $submittedData, $parentObject ) )
    {
      $bool_isValidatingSuccessful = false;
    }

    if ( !$this->validateFieldCleanupImmo24( $submittedData, $parentObject ) )
    {
      $bool_isValidatingSuccessful = false;
    }

    if ( !$this->validateFieldKeyliveprivate( $submittedData, $parentObject ) )
    {
      $bool_isValidatingSuccessful = false;
    }

    if ( !$this->validateFieldKeylivepublic( $submittedData, $parentObject ) )
    {
      $bool_isValidatingSuccessful = false;
    }

    if ( !$this->validateFieldKeysandboxprivate( $submittedData, $parentObject ) )
    {
      $bool_isValidatingSuccessful = false;
    }

    if ( !$this->validateFieldKeysandboxpublic( $submittedData, $parentObject ) )
    {
      $bool_isValidatingSuccessful = false;
    }

    if ( !$this->validateFieldLiveorsandbox( $submittedData, $parentObject ) )
    {
      $bool_isValidatingSuccessful = false;
    }

    if ( !$this->validateFieldTableappartmentrent( $submittedData, $parentObject ) )
    {
      $bool_isValidatingSuccessful = false;
    }

    if ( !$this->validateFieldTablecontact( $submittedData, $parentObject ) )
    {
      $bool_isValidatingSuccessful = false;
    }

    return $bool_isValidatingSuccessful;
  }

  /**
   * validateFieldAdminEmail()    :
   *
   * @param object $submittedData : see validateAdditionalFields()
   * @param object $parentObject  : see validateAdditionalFields()
   * @return boolean
   * @access private
   * @version 7.0.0
   * @since 7.0.0
   */
  private function validateFieldAdminEmail( array &$submittedData, \TYPO3\CMS\Scheduler\Controller\SchedulerModuleController $parentObject )
  {
    $submittedData[ 'deal_adminemail' ] = trim( $submittedData[ 'deal_adminemail' ] );

    if ( !empty( $submittedData[ 'deal_adminemail' ] ) )
    {
      return TRUE;
    }

    $prompt = $this->extLabel . ': ' . $GLOBALS[ 'LANG' ]->sL( 'LLL:EXT:deal/Classes/Scheduler/locallang.xlf:immo24.prompt.enteradminemail' );
    $parentObject->addMessage( $prompt, \TYPO3\CMS\Core\Messaging\FlashMessage::ERROR );

    return FALSE;
  }

  /**
   * validateFieldAdminEmailMode()    :
   *
   * @param object $submittedData : see validateAdditionalFields()
   * @param object $parentObject  : see validateAdditionalFields()
   * @return boolean
   * @access private
   * @version 7.0.0
   * @since 7.0.0
   */
  private function validateFieldAdminEmailMode( array &$submittedData, \TYPO3\CMS\Scheduler\Controller\SchedulerModuleController $parentObject )
  {
    $submittedData[ 'deal_adminemailmode' ] = trim( $submittedData[ 'deal_adminemailmode' ] );

    switch ( $submittedData[ 'deal_adminemailmode' ] )
    {
      case( 'all' ):
      case( 'nothing' ):
      case( 'update' ):
      case( 'warn' ):
        return TRUE;
      default:
        // follow the workflow
        break;
    }

    $prompt = $this->extLabel . ': ' . $GLOBALS[ 'LANG' ]->sL( 'LLL:EXT:deal/Classes/Scheduler/locallang.xlf:immo24.prompt.enteradminemailmode' );
    $parentObject->addMessage( $prompt, \TYPO3\CMS\Core\Messaging\FlashMessage::ERROR );

    return FALSE;
  }

  /**
   * validateFieldCleanupImmo24()    :
   *
   * @param object $submittedData : see validateAdditionalFields()
   * @param object $parentObject  : see validateAdditionalFields()
   * @return boolean
   * @access private
   * @version 7.0.0
   * @since 7.0.0
   */
  private function validateFieldCleanupImmo24( array &$submittedData, \TYPO3\CMS\Scheduler\Controller\SchedulerModuleController $parentObject )
  {
    $submittedData[ 'deal_cleanupimmo24' ] = trim( $submittedData[ 'deal_cleanupimmo24' ] );

    switch ( $submittedData[ 'deal_cleanupimmo24' ] )
    {
      case 'allAll' :
      case 'apartmentsrentAll' :
      case 'allContacts' :
      case 'knownAll' :
      case 'apartmentsrentKnown' :
      case 'knownContacts' :
      case 'nothing' :
      case 'unknownAll' :
      case 'apartmentsrentUnknown' :
      case 'unknownContacts' :
        return TRUE;
      default:
        // follow the workflow
        break;
    }

    $prompt = $this->extLabel . ': ' . $GLOBALS[ 'LANG' ]->sL( 'LLL:EXT:deal/Classes/Scheduler/locallang.xlf:immo24.prompt.entercleanupimmo24' );
    $parentObject->addMessage( $prompt, \TYPO3\CMS\Core\Messaging\FlashMessage::ERROR );

    return FALSE;
  }

  /**
   * validateFieldKeyliveprivate()    :
   *
   * @param object $submittedData : see validateAdditionalFields()
   * @param object $parentObject  : see validateAdditionalFields()
   * @return boolean
   * @access private
   * @version 7.0.0
   * @since 7.0.0
   */
  private function validateFieldKeyliveprivate( array &$submittedData, \TYPO3\CMS\Scheduler\Controller\SchedulerModuleController $parentObject )
  {
    $submittedData[ 'deal_keyliveprivate' ] = trim( $submittedData[ 'deal_keyliveprivate' ] );

    if ( !empty( $submittedData[ 'deal_keyliveprivate' ] ) )
    {
      return TRUE;
    }

    $prompt = $this->extLabel . ': ' . $GLOBALS[ 'LANG' ]->sL( 'LLL:EXT:deal/Classes/Scheduler/locallang.xlf:immo24.prompt.enterkeyliveprivate' );
    $parentObject->addMessage( $prompt, \TYPO3\CMS\Core\Messaging\FlashMessage::ERROR );

    return FALSE;
  }

  /**
   * validateFieldKeylivepublic()    :
   *
   * @param object $submittedData : see validateAdditionalFields()
   * @param object $parentObject  : see validateAdditionalFields()
   * @return boolean
   * @access private
   * @version 7.0.0
   * @since 7.0.0
   */
  private function validateFieldKeylivepublic( array &$submittedData, \TYPO3\CMS\Scheduler\Controller\SchedulerModuleController $parentObject )
  {
    $submittedData[ 'deal_keylivepublic' ] = trim( $submittedData[ 'deal_keylivepublic' ] );

    if ( !empty( $submittedData[ 'deal_keylivepublic' ] ) )
    {
      return TRUE;
    }

    $prompt = $this->extLabel . ': ' . $GLOBALS[ 'LANG' ]->sL( 'LLL:EXT:deal/Classes/Scheduler/locallang.xlf:immo24.prompt.enterkeylivepublic' );
    $parentObject->addMessage( $prompt, \TYPO3\CMS\Core\Messaging\FlashMessage::ERROR );

    return FALSE;
  }

  /**
   * validateFieldKeysandboxprivate()    :
   *
   * @param object $submittedData : see validateAdditionalFields()
   * @param object $parentObject  : see validateAdditionalFields()
   * @return boolean
   * @access private
   * @version 7.0.0
   * @since 7.0.0
   */
  private function validateFieldKeysandboxprivate( array &$submittedData, \TYPO3\CMS\Scheduler\Controller\SchedulerModuleController $parentObject )
  {
    $submittedData[ 'deal_keysandboxprivate' ] = trim( $submittedData[ 'deal_keysandboxprivate' ] );
    $submittedData[ 'deal_liveorsandbox' ] = trim( $submittedData[ 'deal_liveorsandbox' ] );

    switch ( TRUE )
    {
      case(!empty( $submittedData[ 'deal_keysandboxprivate' ] ) ):
      case( $submittedData[ 'deal_liveorsandbox' ] == 'live' ):
        return TRUE;
      case( $submittedData[ 'deal_liveorsandbox' ] == 'sandbox' ):
      default:
        // follow the workflow
        break;
    }

    $prompt = $this->extLabel . ': ' . $GLOBALS[ 'LANG' ]->sL( 'LLL:EXT:deal/Classes/Scheduler/locallang.xlf:immo24.prompt.enterkeysandboxprivate' );
    $parentObject->addMessage( $prompt, \TYPO3\CMS\Core\Messaging\FlashMessage::ERROR );

    return FALSE;
  }

  /**
   * validateFieldKeysandboxpublic()    :
   *
   * @param object $submittedData : see validateAdditionalFields()
   * @param object $parentObject  : see validateAdditionalFields()
   * @return boolean
   * @access private
   * @version 7.0.0
   * @since 7.0.0
   */
  private function validateFieldKeysandboxpublic( array &$submittedData, \TYPO3\CMS\Scheduler\Controller\SchedulerModuleController $parentObject )
  {
    $submittedData[ 'deal_keysandboxpublic' ] = trim( $submittedData[ 'deal_keysandboxpublic' ] );
    $submittedData[ 'deal_liveorsandbox' ] = trim( $submittedData[ 'deal_liveorsandbox' ] );

    switch ( TRUE )
    {
      case(!empty( $submittedData[ 'deal_keysandboxpublic' ] ) ):
      case( $submittedData[ 'deal_liveorsandbox' ] == 'live' ):
        return TRUE;
      case( $submittedData[ 'deal_liveorsandbox' ] == 'sandbox' ):
      default:
        // follow the workflow
        break;
    }

    $prompt = $this->extLabel . ': ' . $GLOBALS[ 'LANG' ]->sL( 'LLL:EXT:deal/Classes/Scheduler/locallang.xlf:immo24.prompt.enterkeysandboxpublic' );
    $parentObject->addMessage( $prompt, \TYPO3\CMS\Core\Messaging\FlashMessage::ERROR );

    return FALSE;
  }

  /**
   * validateFieldLiveorsandbox()    :
   *
   * @param object $submittedData : see validateAdditionalFields()
   * @param object $parentObject  : see validateAdditionalFields()
   * @return boolean
   * @access private
   * @version 7.0.0
   * @since 7.0.0
   */
  private function validateFieldLiveorsandbox( array &$submittedData, \TYPO3\CMS\Scheduler\Controller\SchedulerModuleController $parentObject )
  {
    $submittedData[ 'deal_liveorsandbox' ] = trim( $submittedData[ 'deal_liveorsandbox' ] );

    switch ( $submittedData[ 'deal_liveorsandbox' ] )
    {
      case( 'live' ):
      case( 'sandbox' ):
        return TRUE;
      default:
        // follow the workflow
        break;
    }

    $prompt = $this->extLabel . ': ' . $GLOBALS[ 'LANG' ]->sL( 'LLL:EXT:deal/Classes/Scheduler/locallang.xlf:immo24.prompt.enterliveorsandbox' );
    $parentObject->addMessage( $prompt, \TYPO3\CMS\Core\Messaging\FlashMessage::ERROR );

    return FALSE;
  }

  /**
   * validateFieldTableappartmentrent()    :
   *
   * @param object $submittedData : see validateAdditionalFields()
   * @param object $parentObject  : see validateAdditionalFields()
   * @return boolean
   * @access private
   * @version 7.0.0
   * @since 7.0.0
   */
  private function validateFieldTableappartmentrent( array &$submittedData, \TYPO3\CMS\Scheduler\Controller\SchedulerModuleController $parentObject )
  {
    $table = trim( $submittedData[ 'deal_tableappartmentrent' ] );

    if ( !empty( $table ) )
    {
      return $this->zzValidateTcaCtrl( $parentObject, $table );
    }

    $prompt = $this->extLabel . ': ' . $GLOBALS[ 'LANG' ]->sL( 'LLL:EXT:deal/Classes/Scheduler/locallang.xlf:immo24.prompt.entertableappartmentrent' );
    $parentObject->addMessage( $prompt, \TYPO3\CMS\Core\Messaging\FlashMessage::WARNING );

    return TRUE;
  }

  /**
   * validateFieldTablecontact()    :
   *
   * @param object $submittedData : see validateAdditionalFields()
   * @param object $parentObject  : see validateAdditionalFields()
   * @return boolean
   * @access private
   * @version 7.0.0
   * @since 7.0.0
   */
  private function validateFieldTablecontact( array &$submittedData, \TYPO3\CMS\Scheduler\Controller\SchedulerModuleController $parentObject )
  {
    $table = trim( $submittedData[ 'deal_tablecontact' ] );

    if ( !empty( $table ) )
    {
      return $this->zzValidateTcaCtrl( $parentObject, $table );
    }

    $prompt = $this->extLabel . ': ' . $GLOBALS[ 'LANG' ]->sL( 'LLL:EXT:deal/Classes/Scheduler/locallang.xlf:immo24.prompt.entertablecontact' );
    $parentObject->addMessage( $prompt, \TYPO3\CMS\Core\Messaging\FlashMessage::WARNING );

    return TRUE;
  }

  /**
   * zzPromptErrorTcaCtrl()    :
   *
   * @return boolean
   * @access private
   * @version 7.0.0
   * @since 7.0.0
   */
  private function zzPromptErrorTcaCtrl( \TYPO3\CMS\Scheduler\Controller\SchedulerModuleController $parentObject, $table, $tca )
  {
    $tca = "\$TCA[ '" . $table . "' ][ 'ctrl' ][ 'tx_deal' ][ 'marketplaces' ][ 'immo24' ]";

    $prompt = $this->extLabel . ': ' . $GLOBALS[ 'LANG' ]->sL( 'LLL:EXT:deal/Classes/Scheduler/locallang.xlf:immo24.prompt.errortcactrl' );
    $prompt = str_replace( '%table%', $table, $prompt );
    $prompt = str_replace( '%tca%', $tca, $prompt );
    $parentObject->addMessage( $prompt, \TYPO3\CMS\Core\Messaging\FlashMessage::ERROR );
  }

  /**
   * zzTablesList()      :
   *
   * @param object $taskInfo      : see getAdditionalFields()
   * @return string
   * @access private
   * @version 7.0.0
   * @since 7.0.0
   */
  private function zzTablesList( $sFieldValue )
  {
    $aOptions = array();
    $aSelected = array();

    foreach ( ( array ) array_keys( $GLOBALS[ 'TCA' ] ) as $sTable )
    {
      $aSelected[ $sTable ] = null;
    }

    $aSelected[ $sFieldValue ] = ' selected="selected"';

    foreach ( ( array ) array_keys( $GLOBALS[ 'TCA' ] ) as $sTable )
    {
      $sLabel = $GLOBALS[ 'LANG' ]->sL( $GLOBALS[ 'TCA' ][ $sTable ][ 'ctrl' ][ 'title' ] );
      $sValue = $sTable . ' (' . $sLabel . ')';
      $aOptions[ $sTable ] = '        <option value="' . $sTable . '"' . $aSelected[ $sTable ] . '>' . $sValue . '</option>';
    }

    ksort( $aOptions );
    $sOptions = implode( PHP_EOL, $aOptions );

    //var_dump( __METHOD__, __LINE__, $sOptions );

    return $sOptions;
  }

  /**
   * zzValidateTcaCtrl()    :
   *
   * @return boolean
   * @access private
   * @version 7.0.0
   * @since 7.0.0
   */
  private function zzValidateTcaCtrl( \TYPO3\CMS\Scheduler\Controller\SchedulerModuleController $parentObject, $table )
  {
    if ( isset( $GLOBALS[ 'TCA' ][ $table ][ 'ctrl' ][ 'tx_deal' ][ 'marketplaces' ][ 'immo24' ] ) )
    {
      return TRUE;
    }

    $tca = "\$TCA[ '" . $table . "' ][ 'ctrl' ][ 'tx_deal' ][ 'marketplaces' ][ 'immo24' ]";
    $this->zzPromptErrorTcaCtrl( $parentObject, $table, $tca );
    return FALSE;
  }

}
