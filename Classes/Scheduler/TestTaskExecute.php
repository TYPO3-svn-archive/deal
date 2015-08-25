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
class TestTaskExecute extends \TYPO3\CMS\Scheduler\Task\AbstractTask
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
   * execute( ) : Function executed from the Scheduler.
   *              Sends an email
   *
   * @access public
   * @return boolean
   */
  public function execute( )
  {
    $testtask = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance( 'Netzmacher\\Deal\\Scheduler\\TestTask' );
    $success = $testtask->run( $this->taskUid, $this->deal_adminemail );
    return $success;
  }

}
