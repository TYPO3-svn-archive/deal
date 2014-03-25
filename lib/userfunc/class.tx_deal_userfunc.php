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
*  the Free Software Deal; either version 2 of the License, or
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
* Class provides methods for the extension manager.
*
* @author    Dirk Wildt <http://wildt.at.die-netzmacher.de>
* @package    TYPO3
* @subpackage    deal
* @version  0.1.0
* @since    0.0.1
*/


  /**
 * [CLASS/FUNCTION INDEX of SCRIPT]
 *
 *
 *
 *   49: class tx_deal_userfunc
 *   67:     function promptCheckUpdate()
 *  102:     function promptCurrIP()
 *
 * TOTAL FUNCTIONS: 2
 * (This index is automatically created/updated by the extension "extdeveval")
 *
 */
class tx_deal_userfunc
{

 /**
  * Extension key
  *
  * @var string
  */
  public $extKey = 'deal';

 /**
  * Extension configuration
  *
  * @var array
  */
  private $arr_extConf = null;

 /**
  * Max width of div tags
  *
  * @var string
  */
  private $maxWidth = "600px";

 /**
  * Version of TYPO3 (sample: 4.7.7 -> 4007007)
  *
  * @var string
  */
  public $typo3Version = null;




  /***********************************************
   *
   * Extension Management
   *
   **********************************************/

 /**
  * extMgmVersion( ): Returns the version of an extension as an interger and a string.
  *                   I.e
  *                   * int: 4007007
  *                   * str: 4.7.7
  *
  * @param    string        $_EXTKEY    : extension key
  * @return    array        $arrReturn  : version as int (integer) and str (string)
  * @access public
  * @version 0.0.1
  * @since 0.0.1
  */
  public function extMgmVersion( $_EXTKEY )
  {
    $arrReturn = null;

    if( ! t3lib_extMgm::isLoaded( $_EXTKEY ) )
    {
      $arrReturn['int'] = 0;
      $arrReturn['str'] = 0;
      return $arrReturn;
    }

      // Do not use require_once!
    require( t3lib_extMgm::extPath( $_EXTKEY ) . 'ext_emconf.php');
    $strVersion = $EM_CONF[$_EXTKEY]['version'];

      // Set version as integer (sample: 4.7.7 -> 4007007)
    list( $main, $sub, $bugfix ) = explode( '.', $strVersion );
    $intVersion = ( ( int ) $main ) * 1000000;
    $intVersion = $intVersion + ( ( int ) $sub ) * 1000;
    $intVersion = $intVersion + ( ( int ) $bugfix ) * 1;
      // Set version as integer (sample: 4.7.7 -> 4007007)

    $arrReturn['int'] = $intVersion;
    $arrReturn['str'] = $strVersion;
    return $arrReturn;
  }



  /***********************************************
   *
   * Prompts
   *
   **********************************************/

  /**
   * promptAmazonContract(): Displays the quick start message.
   *
   * @return  string    message wrapped in HTML
   * @access  public
   * @version 0.1.0
   * @since   0.1.0
   */
  public function promptAmazonContract()
  {
//.message-notice
//.message-information
//.message-ok
//.message-warning
//.message-error

      $prompt = null;

      $prompt = $prompt . '
<div class="typo3-message message-information">
  <div class="message-body" style="max-width:600px;">
    ' . $GLOBALS['LANG']->sL('LLL:EXT:deal/lib/userfunc/locallang.xml:promptAmazonContract') . '
  </div>
</div>';

    return $prompt;
  }

  /**
   * promptCurrIP( ): Displays the IP of the current backend user
   *
   * @return    string        message wrapped in HTML
   * @access  public
   * @version 0.0.1
   * @since   0.0.1
   */
  public function promptCurrIP( )
  {
      $prompt = null;

      $prompt = $prompt.'
<div class="typo3-message message-information">
  <div class="message-body">
    ' . $GLOBALS['LANG']->sL('LLL:EXT:deal/lib/userfunc/locallang.xml:promptCurrIPBody') . ': ' . t3lib_div :: getIndpEnv('REMOTE_ADDR') . '
  </div>
</div>';

    return $prompt;
  }

  /**
   * promptEvaluatorTYPO3version(): Displays the quick start message.
   *
   * @return  string    message wrapped in HTML
   * @access  public
   * @version 0.0.1
   * @since   0.0.1
   */
  public function promptEvaluatorTYPO3version()
  {
//.message-notice
//.message-information
//.message-ok
//.message-warning
//.message-error

    $prompt = null;

    $this->set_TYPO3Version( );

    switch( true )
    {
      case( $this->typo3Version < 4005000 ):
          // Smaller than 4.5
        $prompt = $prompt . '
          <div class="typo3-message message-warning" style="max-width:' . $this->maxWidth . ';">
            <div class="message-body">
              ' . $GLOBALS['LANG']->sL('LLL:EXT:deal/lib/userfunc/locallang.xml:promptEvaluatorTYPO3version45smaller'). '
            </div>
          </div>
          ';
//        $prompt = $prompt . '
//          <div class="typo3-message message-information" style="max-width:' . $this->maxWidth . ';">
//            <div class="message-body">
//              ' . $GLOBALS['LANG']->sL('LLL:EXT:deal/lib/userfunc/locallang.xml:promptEvaluatorIncludeCss4-6'). '
//            </div>
//          </div>
//          ';
        break;
      case( $this->typo3Version < 4006000 ):
          // Greater than 4.7
        $prompt = $prompt . '
          <div class="typo3-message message-ok" style="max-width:' . $this->maxWidth . ';">
            <div class="message-body">
              ' . $GLOBALS['LANG']->sL('LLL:EXT:deal/lib/userfunc/locallang.xml:promptEvaluatorTYPO3version46smaller'). '
            </div>
          </div>
          ';
//        $prompt = $prompt . '
//          <div class="typo3-message message-information" style="max-width:' . $this->maxWidth . ';">
//            <div class="message-body">
//              ' . $GLOBALS['LANG']->sL('LLL:EXT:deal/lib/userfunc/locallang.xml:promptEvaluatorIncludeCss4-6'). '
//            </div>
//          </div>
//          ';
        break;
      case( $this->typo3Version < 4007000 ):
          // Greater than 4.7
        $prompt = $prompt . '
          <div class="typo3-message message-ok" style="max-width:' . $this->maxWidth . ';">
            <div class="message-body">
              ' . $GLOBALS['LANG']->sL('LLL:EXT:deal/lib/userfunc/locallang.xml:promptEvaluatorTYPO3version47smaller'). '
            </div>
          </div>
          ';
//        $prompt = $prompt . '
//          <div class="typo3-message message-information" style="max-width:' . $this->maxWidth . ';">
//            <div class="message-body">
//              ' . $GLOBALS['LANG']->sL('LLL:EXT:deal/lib/userfunc/locallang.xml:promptEvaluatorIncludeCss4-6'). '
//            </div>
//          </div>
//          ';
        break;
      case( $this->typo3Version < 4008000 ):
          // Greater than 4.7
        $prompt = $prompt . '
          <div class="typo3-message message-ok" style="max-width:' . $this->maxWidth . ';">
            <div class="message-body">
              ' . $GLOBALS['LANG']->sL('LLL:EXT:deal/lib/userfunc/locallang.xml:promptEvaluatorTYPO3version48smaller'). '
            </div>
          </div>
          ';
        break;
//      case( ( $this->typo3Version >= 4006000 ) && ( $this->typo3Version < 4007000 ) ):
      default:
          // Equal to 4.6
        $prompt = $prompt . '
          <div class="typo3-message message-ok" style="max-width:' . $this->maxWidth . ';">
            <div class="message-body">
              ' . $GLOBALS['LANG']->sL('LLL:EXT:deal/lib/userfunc/locallang.xml:promptEvaluatorTYPO3version48orGreater'). '
            </div>
          ';
        break;
    }

    return $prompt;
  }

  /**
   * promptExternalLinks(): Displays the quick start message.
   *
   * @return  string    message wrapped in HTML
   * @access  public
   * @version 0.0.1
   * @since   0.0.1
   */
  public function promptExternalLinks()
  {
//.message-notice
//.message-information
//.message-ok
//.message-warning
//.message-error

      $prompt = null;

      $prompt = $prompt . '
<div class="message-body" style="max-width:600px;">
  ' . $GLOBALS['LANG']->sL('LLL:EXT:deal/lib/userfunc/locallang.xml:promptExternalLinksBody') . '
</div>';

    return $prompt;
  }

  /**
   * promptSponsors( ): Displays the quick start message.
   *
   * @return  string    message wrapped in HTML
   * @access  public
   * @version 0.0.1
   * @since   0.0.1
   */
  public function promptSponsors()
  {
//.message-notice
//.message-information
//.message-ok
//.message-warning
//.message-error

      $prompt = null;

      $prompt = $prompt . '
<div class="message-body" style="max-width:600px;">
  ' . $GLOBALS['LANG']->sL('LLL:EXT:deal/lib/userfunc/locallang.xml:promptSponsors') . '
</div>';

    return $prompt;
  }



  /***********************************************
   *
   * TYPO3
   *
   **********************************************/

 /**
   * set_TYPO3Version( ):
   *
   * @return  void
   * @access  private
   * @version 0.0.1
   * @since 0.0.1
   */
  private function set_TYPO3Version( )
  {
      // RETURN : typo3Version is set
    if( $this->typo3Version !== null )
    {
      return;
    }
      // RETURN : typo3Version is set

      // Set TYPO3 version as integer (sample: 4.7.7 -> 4007007)
    list( $main, $sub, $bugfix ) = explode( '.', TYPO3_version );
    $version = ( ( int ) $main ) * 1000000;
    $version = $version + ( ( int ) $sub ) * 1000;
    $version = $version + ( ( int ) $bugfix ) * 1;
    $this->typo3Version = $version;
      // Set TYPO3 version as integer (sample: 4.7.7 -> 4007007)

    if( $this->typo3Version < 3000000 )
    {
      $prompt = '<h1>ERROR</h1>
        <h2>Unproper TYPO3 version</h2>
        <ul>
          <li>
            TYPO3 version is smaller than 3.0.0
          </li>
          <li>
            constant TYPO3_version: ' . TYPO3_version . '
          </li>
          <li>
            integer $this->typo3Version: ' . ( int ) $this->typo3Version . '
          </li>
        </ul>
          ';
      die ( $prompt );
    }
  }
}


if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/deal/lib/class.tx_deal_userfunc.php'])
{
  include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/deal/lib/class.tx_deal_userfunc.php']);
}

?>