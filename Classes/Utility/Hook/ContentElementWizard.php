<?php
namespace Netzmacher\Deal\Utility\Hook;

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

/***************************************************************
*  Copyright notice
*
*  (c) 2015 Dirk Wildt <http://wildt.at.die-netzmacher.de/>
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
 * Class ContentElementWizard enables a new icon/link for deal for immobilienscout24
 * on adding ...
 *
 * @package Netzmacher\Deal\Utility\Hook
 */
class ContentElementWizard {

	/**
	 * Path to locallang file (with : as postfix)
	 *
	 * @var string
	 */
	protected $locallangPath = 'LLL:EXT:deal/Resources/Private/Language/Marketplaces/Immo24/locallang_mod.xlf:';

	/**
	 * @var \TYPO3\CMS\Lang\LanguageService
	 */
	protected $languageService = NULL;

	/**
	 * Adding a new content element wizard item for deal
	 *
	 * @param array $contentElementWizardItems
	 * @return array
	 */
	public function proc($contentElementWizardItems = array()) {
		$this->initialize();
		$contentElementWizardItems['plugins_tx_deal_pi1'] = array(
			'icon' => ExtensionManagementUtility::extRelPath('deal') . 'Resources/Public/Icons/Marketplaces/Immo24/pi1Wizard.gif',
			'title' => $this->languageService->sL($this->locallangPath . 'pluginPi1WizardTitle', TRUE),
			'description' => $this->languageService->sL($this->locallangPath . 'pluginPi1WizardDescription', TRUE),
			'params' => '&defVals[tt_content][CType]=list&defVals[tt_content][list_type]=deal_pi1',
			'tt_content_defValues' => array(
				'CType' => 'list',
			),
		);

		return $contentElementWizardItems;
	}

	/**
	 * Initialize
	 *
	 * @return void
	 */
	protected function initialize() {
		$this->languageService = $GLOBALS['LANG'];
	}
}