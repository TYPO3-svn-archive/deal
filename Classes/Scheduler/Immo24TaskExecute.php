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
class Immo24TaskExecute extends \TYPO3\CMS\Scheduler\Task\AbstractTask
{

  /**
   * An email address to be used during the process
   *
   * @var string $deal_adminemail
   */
  public $deal_adminemail;

  /**
   * An email address to be used during the process
   *
   * @var string $deal_adminemail
   */
  public $deal_adminemailmode;

  /**
   * Mode for clean up the is24 database
   *
   * @var string $deal_cleanupimmo24
   */
  public $deal_cleanupimmo24;

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
   * execute( ) : Function executed from the Scheduler.
   *              Exports items to immobilienscout24
   *
   * @access public
   * @return boolean
   * @version 7.0.0
   * @since 7.0.0
   */
  public function execute()
  {
    $immo24Task = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance( 'Netzmacher\\Deal\\Scheduler\\Immo24Task' );

    $aParams = $this->init();

    $success = $immo24Task->run( $aParams );
    return $success;
  }

  /**
   * This method returns the destination mail address as additional information
   *
   * @access public
   * @return	string		Information to display
   * @internal #i0037
   * @version 7.0.1
   * @since 7.0.1
   */
  public function getAdditionalInformation()
  {
    return $GLOBALS[ 'LANG' ]->sL( 'LLL:EXT:deal/Classes/Scheduler/locallang.xlf:immo24.field.cleanupimmo24' ) . ': ' . $this->deal_cleanupimmo24;
  }

  /**
   * init( ) :
   *
   * @access private
   * @return boolean
   * @version 7.0.0
   * @since 7.0.0
   */
  private function init()
  {
    $aParams = $this->initVars();
    return $aParams;
  }

  /**
   * initVars( ) :
   *
   * @access private
   * @return boolean
   * @version 7.0.0
   * @since 7.0.0
   */
  private function initVars()
  {
    $aParams = array(
      'deal_adminemail' => $this->deal_adminemail,
      'deal_adminemailmode' => $this->deal_adminemailmode,
      'deal_cleanupimmo24' => $this->deal_cleanupimmo24,
      'deal_keyliveprivate' => $this->deal_keyliveprivate,
      'deal_keylivepublic' => $this->deal_keylivepublic,
      'deal_keysandboxprivate' => $this->deal_keysandboxprivate,
      'deal_keysandboxpublic' => $this->deal_keysandboxpublic,
      'deal_liveorsandbox' => $this->deal_liveorsandbox,
      'deal_tableappartmentrent' => $this->deal_tableappartmentrent,
      'deal_tablecontact' => $this->deal_tablecontact,
      'taskUid' => $this->taskUid,
    );
    return $aParams;
  }

}
