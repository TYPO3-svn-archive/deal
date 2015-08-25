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
class TestFieldProvider implements \TYPO3\CMS\Scheduler\AdditionalFieldProviderInterface
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
   * @return void
   * @access public
   * @version 7.0.0
   * @since 7.0.0
   */
  public function getAdditionalFields( array &$taskInfo, $task, \TYPO3\CMS\Scheduler\Controller\SchedulerModuleController $parentObject )
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
    $additionalFields = array();
    $additionalFields[ $fieldID ] = array(
      'code' => $fieldCode,
      'label' => 'LLL:EXT:deal/Classes/Scheduler/locallang.xlf:test.field.adminemail',
      'cshKey' => '_MOD_tools_txschedulerM1',
      'cshLabel' => $fieldID
    );

    return $additionalFields;
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
   * @return void
   * @access public
   * @version 7.0.0
   * @since 7.0.0
   */
  public function validateAdditionalFields( array &$submittedData, \TYPO3\CMS\Scheduler\Controller\SchedulerModuleController $parentObject )
  {
    $submittedData[ 'deal_adminemail' ] = trim( $submittedData[ 'deal_adminemail' ] );

    if ( empty( $submittedData[ 'deal_adminemail' ] ) )
    {
      $prompt = $this->extLabel . ': ' . $GLOBALS[ 'LANG' ]->sL( 'LLL:EXT:deal/Classes/Scheduler/locallang.xlf:test.prompt.enteradminemail' );
      $message = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(
                      'TYPO3\\CMS\\Core\\Messaging\\FlashMessage', $prompt, null, \TYPO3\CMS\Core\Messaging\FlashMessage::ERROR, TRUE
      );
      \TYPO3\CMS\Core\Messaging\FlashMessageQueue::addMessage( $message );
      $result = FALSE;
    }
    else
    {
      $result = TRUE;
    }

    return $result;
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
    $task->deal_adminemail = $submittedData[ 'deal_adminemail' ];
  }

}
