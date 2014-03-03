<?php

/* * *************************************************************
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
 * ************************************************************* */

require_once( PATH_tslib . 'class.tslib_pibase.php' );

/**
 * plugin 'ebay sample start' for the 'deal' extension.
 *
 * @author	Dirk Wildt <http://wildt.at.die-netzmacher.de>
 * @package	TYPO3
 * @subpackage	tx_deal
 * @version	0.0.1
 * @since       0.0.1
 */
class tx_deal_piMarketplacesEbaySamplesPhpGettingstarted extends tslib_pibase {

  public $prefixId      = 'tx_deal_piMarketplacesEbaySamplesPhpGettingstarted';
  public $scriptRelPath = 'plugins/marketplaces/ebay/samples/php/GettingStarted_PHP_NV_XML/class.tx_deal_piMarketplacesEbaySamplesPhpGettingstarted.php';
  public $extKey        = 'deal';
  public $local_cObj    = null;
  public $conf          = null;
  public $arr_extConf   = null;

  /**
   * main( ) : the main method of the PlugIn
   *
   * @param	string		$content  : plugin content. Usually empty.
   * @param	array		$conf     : plugin configuration.
   * @return	string		$content  : The content that is displayed on the website
   * @version  0.0.1
   * @since    0.0.1
   */
  public function main($content, $conf) {

    $this->conf = $conf;
    $this->pi_loadLL();

    $content = $this->ebaySampleStart();

    return $this->pi_wrapInBaseClass($content);
  }

  /*   * *********************************************
   *
   * ebay
   *
   * ******************************************** */

  /**
   * ebaySampleStart( ) :
   *
   * @return	string		$content  : 
   * internal   See deal/lib/marketplaces/ebay/samples/php/GettingStarted_PHP_NV_XML/Sample_GettingStarted_PHP_NV_XML.php
   * @access private
   * @version  0.0.1
   * @since    0.0.1
   */
  private function ebaySampleStart() {

    // 140217, dwildt, 1-
    //error_reporting(E_ALL);  // Turn on all errors, warnings and notices for easier debugging
    // API request variables
    $endpoint = 'http://svcs.ebay.com/services/search/FindingService/v1';  // URL to call
    $version = '1.0.0';            // API version supported by your application
    $appid = 'MyAppID';          // Replace with your own AppID
    $globalid = 'EBAY-US';          // Global ID of the eBay site you want to search (e.g., EBAY-DE)
    $query = 'harry potter';     // You may want to supply your own query
    $safequery = urlencode($query);  // Make the query URL-friendly
    $i = '0';                // Initialize the item filter index to 0
    // Create a PHP array of the item filters you want to use in your request
    $filterarray =
            array(
                array(
                    'name' => 'MaxPrice',
                    'value' => '25',
                    'paramName' => 'Currency',
                    'paramValue' => 'USD'),
                array(
                    'name' => 'FreeShippingOnly',
                    'value' => 'true',
                    'paramName' => '',
                    'paramValue' => ''),
                array(
                    'name' => 'ListingType',
                    'value' => array('AuctionWithBIN', 'FixedPrice', 'StoreInventory'),
                    'paramName' => '',
                    'paramValue' => ''),
    );

    // 140217, dwildt, 6+
    $globalid = $this->conf['constanteditor.']['globalid'];
    $appid = $this->conf['constanteditor.']['appid'];
    $query = $this->conf['constanteditor.']['query'];
    $safequery = urlencode($query);
    $filterarray[0]['value'] = $this->conf['constanteditor.']['currency.']['value'];
    $filterarray[0]['paramValue'] = $this->conf['constanteditor.']['currency.']['paramValue'];

    // Generates an indexed URL snippet from the array of item filters
    function buildURLArray($filterarray) {
      global $urlfilter;
      global $i;
      // Iterate through each filter in the array
      foreach ($filterarray as $itemfilter) {
        // Iterate through each key in the filter
        foreach ($itemfilter as $key => $value) {
          if (is_array($value)) {
            foreach ($value as $j => $content) { // Index the key for each value
              $urlfilter .= "&itemFilter($i).$key($j)=$content";
            }
          } else {
            if ($value != "") {
              $urlfilter .= "&itemFilter($i).$key=$value";
            }
          }
        }
        $i++;
      }
      return "$urlfilter";
    }

    // End of buildURLArray function
    // Build the indexed item filter URL snippet
    buildURLArray($filterarray);

    // Construct the findItemsByKeywords HTTP GET call 
    $apicall = "$endpoint?";
    $apicall .= "OPERATION-NAME=findItemsByKeywords";
    $apicall .= "&SERVICE-VERSION=$version";
    $apicall .= "&SECURITY-APPNAME=$appid";
    $apicall .= "&GLOBAL-ID=$globalid";
    $apicall .= "&keywords=$safequery";
    $apicall .= "&paginationInput.entriesPerPage=3";
    $apicall .= "$urlfilter";

    // Load the call and capture the document returned by eBay API
    $resp = simplexml_load_file($apicall);

    // Check to see if the request was successful, else print an error
    if ($resp->ack == "Success") {
      $results = '';
      // If the response was loaded, parse it and build links  
      foreach ($resp->searchResult->item as $item) {
        $pic = $item->galleryURL;
        $link = $item->viewItemURL;
        $title = $item->title;

        // For each SearchResultItem node, build a link and append it to $results
        $results .= "<tr><td><img src=\"$pic\"></td><td><a href=\"$link\">$title</a></td></tr>";
      }
    }
    // If the response does not indicate 'Success,' print an error
    else {
      $results = '<h3>
        ' . $this->pi_getLL('promptErrorAppID') . '
      </h3>';
    }

    $content = '
      <h1>
        ' . $this->pi_getLL('promptSuccessHeader') . ' ' . $query . '
      </h1>
      <table>
        <tr>
          <td>
            ' . $results . '
          </td>
        </tr>
      </table>';

    return $content;
  }

}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/deal/plugins/marketplaces/ebay/samples/php/GettingStarted_PHP_NV_XML/class.tx_deal_piMarketplacesEbaySamplesPhpGettingstarted.php']) {
  include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/deal/plugins/marketplaces/ebay/samples/php/GettingStarted_PHP_NV_XML/class.tx_deal_piMarketplacesEbaySamplesPhpGettingstarted.php']);
}
