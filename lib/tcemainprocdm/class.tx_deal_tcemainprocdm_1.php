<?php
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2014 - Dirk Wildt <http://wildt.at.die-netzmacher.de>
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
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
 ***************************************************************/


/**
* The class tx_deal_tcemainprocdm bundles methods for evaluating data in backend forms
*
* @author    Dirk Wildt <http://wildt.at.die-netzmacher.de>
* @package    TYPO3
* @subpackage  deal
*
* @version 0.0.1
* @since 0.0.1
*/

 /**
 * [CLASS/FUNCTION INDEX of SCRIPT]
 *
 *
 *
 *   77: class tx_deal_tcemainprocdm
 *
 *              SECTION: Hook: processDatamap_postProcessFieldArray
 *  122:     public function processDatamap_postProcessFieldArray( $status, $table, $id, &$fieldArray, &$reference )
 *
 *              SECTION: Geo Update
 *  167:     private function geoupdate( &$fieldArray )
 *  218:     private function geoupdateGoogleAPI( &$fieldArray, $address )
 *  248:     private function geoupdateHandleData( &$fieldArray )
 *  334:     private function geoupdateHandleDataGetAddress( $fieldArray, $row )
 *  408:     private function geoupdateHandleDataGetAddressAreaLevel1( $fieldArray, $row )
 *  433:     private function geoupdateHandleDataGetAddressAreaLevel2( $fieldArray, $row )
 *  458:     private function geoupdateHandleDataGetAddressCountry( $fieldArray, $row )
 *  483:     private function geoupdateHandleDataGetAddressLocation( $fieldArray, $row )
 *  527:     private function geoupdateHandleDataGetAddressStreet( $fieldArray, $row )
 *  569:     private function geoupdateIsAddressUntouched( &$fieldArray )
 *  598:     private function geoupdateIsForbiddenByRecord( &$fieldArray )
 *  631:     private function geoupdateRequired( &$fieldArray )
 *  681:     private function geoupdateSetLabels( )
 *  728:     private function geoupdateSetPrompt( $prompt, &$fieldArray )
 *  770:     private function geoupdateSetRow( )
 *
 *              SECTION: Log
 *  864:     public function log( $prompt, $error=0, $action=2 )
 *
 *              SECTION: Route
 *  895:     private function route( &$fieldArray, &$reference )
 *  911:     private function routeGpx( &$fieldArray, &$reference )
 *  940:     private function routeGpxHandleData( &$fieldArray )
 * 1017:     private function routeGpxRequired( $fieldArray )
 *
 * TOTAL FUNCTIONS: 21
 * (This index is automatically created/updated by the extension "extdeveval")
 *
 */
class tx_deal_tcemainprocdm
{
    // [String] status of the current process: update, edit, delete, moved
  private $prefixLog = 'tx_deal ';

    // [String] status of the current process: update, edit, delete, moved
  private $processStatus  = null;
    // [String] label of the table of the current process
  private $processTable   = null;
    // [String] record uid of the current process
  private $processId      = null;

    // [Object] parent object
  private $pObj       = null;

    // [String] Geo API URL
  private $googleApiUrl  = 'http://maps.googleapis.com/maps/api/geocode/json?address=%address%&sensor=false';

    // [Array] Geoupdate lables from ext_tables.php
  private $geoupdatelabels = null;

    // [Array] Row of the current record with former data
  private $geoupdaterow  = null;
    // [Array] Row of the current record with former data
  private $row  = null;




  /***********************************************
  *
  * Hook: processDatamap_postProcessFieldArray
  *
  **********************************************/

/**
 * processDatamap_postProcessFieldArray( )
 *
 * @param	string		$status     : update, edit, delete, moved
 * @param	string		$table      : label of the current table
 * @param	integer		$id         : uid of the current record
 * @param	array		$fieldArray : modified fields - reference!
 * @param	object		$reference  : parent object - reference!
 * @return	void
 * @access public
 * @version   0.0.1
 * @since     0.0.1
 */
  public function processDatamap_postProcessFieldArray( $status, $table, $id, &$fieldArray, &$reference )
  {
    switch( true )
    {
      case( ! is_array( $GLOBALS['TCA'][$table]['ctrl']['tx_deal'] ) ):
        // RETURN : current table is without any tx_deal configuration
        return;
      case( ! is_array( $GLOBALS['TCA'][$table]['ctrl']['tx_deal']['marketplaces'] ) ):
        // RETURN : current table is without any tx_deal marketplace configuration
        return;
      default:
        // Follow the workflow
        break;
    }
      // RETURN : current table is without any tx_deal configuration

      // Initial global variables
    $this->processStatus  = $status;
    $this->processTable   = $table;
    $this->processId      = $id;
    $this->pObj           = $reference;

    if( is_array( $GLOBALS['TCA'][$table]['ctrl']['tx_deal']['marketplaces']['amazon'] ) )
    {
      $this->amazon( $fieldArray, $reference );
    }

    if( is_array( $GLOBALS['TCA'][$table]['ctrl']['tx_deal']['marketplaces']['ebay'] ) )
    {
      $this->ebay( $fieldArray, $reference );
    }

    return;
  }



  /***********************************************
  *
  * amazon
  *
  **********************************************/

/**
 * amazon( )
 *
 * @param	array		$fieldArray : Array of modified fields
 * @return	void
 * @access private
 * @version   0.0.1
 * @since     0.0.1
 */
  private function amazon( &$fieldArray )
  {
    switch( true )
    {
      case( ! $GLOBALS['TCA'][$this->processTable]['ctrl']['tx_deal']['marketplaces']['amazon']['enabled'] ):
        // RETURN : amazon isn't enabled
        $prompt = $GLOBALS['LANG']->sL('LLL:EXT:deal/lib/tcemainprocdm/locallang.xml:amazonEnabledFalse');
        $this->log( $prompt, 0, 2 );
        return;
      default:
        // Follow the workflow
        break;
    }
      // RETURN : current table is without any tx_deal configuration

    return;
  }

/**
 * geoupdateGoogleAPI( )
 *
 * @param	array		$fieldArray : Array of modified fields * @param	string		$address    : Address
 * @param	[type]		$address: ...
 * @return	array		$geodata    : lon, lat
 * @access private
 * @version   0.0.1
 * @since     0.0.1
 */
  private function geoupdateGoogleAPI( &$fieldArray, $address )
  {
      // Require map library
    require_once( PATH_typo3conf . 'ext/deal/lib/mapAPI/class.tx_deal_googleApi.php' );
      // Create object
    $objGoogleApi = new tx_deal_googleApi( );

      // Get data from API
    $result = $objGoogleApi->main( $address, $this );

      // Prompt to current record
    if( isset( $result[ 'status'] ) )
    {
      $prompt = $result[ 'status'];
      $this->geoupdateSetPrompt( $prompt, $fieldArray );
    }
      // Prompt to current record

      // RETURN geodata
    return $result[ 'geodata' ];
  }



  /***********************************************
  *
  * ebay
  *
  **********************************************/

/**
 * amazon( )
 *
 * @param	array		$fieldArray : Array of modified fields
 * @return	void
 * @access private
 * @version   0.0.1
 * @since     0.0.1
 */
  private function ebay( &$fieldArray )
  {

    switch( true )
    {
      case( ! $GLOBALS['TCA'][$this->processTable]['ctrl']['tx_deal']['marketplaces']['ebay']['enabled'] ):
        // RETURN : ebay isn't enabled
        $prompt = $GLOBALS['LANG']->sL('LLL:EXT:deal/lib/tcemainprocdm/locallang.xml:ebayEnabledFalse');
        $this->log( $prompt, 0, 2 );
        return;
      default:
        // Follow the workflow
        break;
    }
      // RETURN : current table is without any tx_deal configuration

    return;
  }

  
  
  
  /***********************************************
  *
  * Log
  *
  **********************************************/

/**
 * log( )
 *
 * @param	string		$prompt : prompt
 * @param	integer		$status : -1 = no flash message, 0 = notice, 1 = info, 3 = OK, 4 = warn, 5 = error
 * @param	string		$action : 0=No category, 1=new record, 2=update record, 3= delete record, 4= move record, 5= Check/evaluate
 * @param	string		$header : 0=No header, 1=Deal! TYPO3 for amazon and ebay, 2=Deal! TYPO3 for amazon and ebay
 * @return	void
 * @access public
 * @version   0.0.1
 * @since     0.0.1
 */
  public function log( $prompt, $status=-1, $action=2, $header=2 )
  {
    $table  = $this->processTable;
    $uid    = $this->processId;
    $pid    = null;

    $fmPrompt   = $prompt;
    $logPrompt  = '[' . $this->prefixLog . ' (' . $table . ':' . $uid . ')] ' . $prompt . PHP_EOL;

    switch( $header ) 
    {
      case( 0 ):
        $fmHeader = '';
        break;
      case( 1 ):
        $fmHeader = $GLOBALS['LANG']->sL('LLL:EXT:deal/lib/tcemainprocdm/locallang.xml:promptDealPhrase');
        break;
      case( 2 ):
      default:
        $fmHeader = $GLOBALS['LANG']->sL('LLL:EXT:deal/lib/tcemainprocdm/locallang.xml:promptDealPhrase');
        break;
    }

    switch( $status ) 
    {
      case( -1 ):
        $fmStatus   = null;
        $logStatus  = 0;
        break;
      case( 0 ):
        $fmStatus   = t3lib_FlashMessage::NOTICE;
        $logStatus  = 0;
        break;
      case( 1 ):
        $fmStatus = t3lib_FlashMessage::INFO;
        $logStatus = 0;
        break;
      case( 2 ):
        $fmStatus = t3lib_FlashMessage::OK;
        $logStatus = 0;
        break;
      case( 3 ):
        $fmPrompt   = $prompt . '<br />
                      ' . $GLOBALS['LANG']->sL('LLL:EXT:deal/lib/tcemainprocdm/locallang.xml:promptDetailsToSyslog');
        $fmStatus = t3lib_FlashMessage::WARNING;
        $logStatus = 0;
        break;
      case( 4 ):
        $fmPrompt   = $prompt . '<br />
                      ' . $GLOBALS['LANG']->sL('LLL:EXT:deal/lib/tcemainprocdm/locallang.xml:promptDetailsToSyslog');
        $fmStatus = t3lib_FlashMessage::ERROR;
        $logStatus = 0;
        break;
      default:
        $logStatus = 0;
        break;
    }
    
    $this->pObj->log( $table, $uid, $action, $pid, $logStatus, $logPrompt );
    
      // RETURN : Don't prompt to the backend
    if( $status < 0 )
    {
      return;
    }
      // RETURN : Don't prompt to the backend

    $flashMessage = t3lib_div::makeInstance( 't3lib_FlashMessage', $fmPrompt, $fmHeader, $fmStatus );
    t3lib_FlashMessageQueue::addMessage( $flashMessage );    
  }

}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/deal/lib/class.tx_deal_tcemainprocdm.php']) {
  include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/deal/lib/class.tx_deal_tcemainprocdm.php']);
}

?>