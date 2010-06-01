<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2009 Nadine Schwingler <schwingler@kennziffer.com>
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
 * Class/Function which manipulates the item-array for table/field tx_kequestionnaire_redirect.
 *
 * @author	Nadine Schwingler <schwingler@kennziffer.com>
 * @package	TYPO3
 * @subpackage	tx_kequestionnaire
 */
class tx_kequestionnaire_tx_kequestionnaire_redirect {
        function main($params)	{		
		GLOBAL $LANG,$BE_USER;
		
		//get translations from file cache in language of currently logged in be-user
		$langLabel = $LANG->LL_files_cache['EXT:ke_questionnaire/locallang.xml'][$BE_USER->uc['lang']];
		
		//in case of completely new plugin on page, data has to be saved first
		if(substr($params['row']['uid'],0,3) == 'NEW') {
			$params['items'][] = array($langLabel['tt_content.pi_flexform.redirect_save'], 0);
			return $params;
		}
		
		//default item
		$params['items'][] = array($langLabel['tt_content.pi_flexform.redirect_no'], 0);
		
		if(!isset($params['row']['uid'])) {
			return $params;
		}
		
		//get settings from flexforms, as soon as settings were saved
		$piData = t3lib_BEfunc::getRecord('tt_content',$params['row']['uid']);
		$ffData = t3lib_div::xml2array($piData['pi_flexform']);
		if (is_array($ffData)) $storagePid = $ffData['data']['sDEF']['lDEF']['storage_pid']['vDEF'];
		
		if(!is_array($piData) || !count($piData) || !is_array($ffData) || empty($storagePid)) {
			return $params;
		}
		
		//get open questions
		$where = 'hidden=0 and deleted=0 and pid='.$storagePid;
		$where .= ' AND (type="closed") AND mandatory = 1';

		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*','tx_kequestionnaire_questions',$where,'sorting');
		if ($res){
			while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)){
				//add item
				$params['items'][] = array($row['title'], $row['uid']);	
			}
		}

		return $params;
	}
}



if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/ke_questionnaire/res/class.tx_kequestionnaire_tx_kequestionnaire_redirect.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/ke_questionnaire/res/class.tx_kequestionnaire_tx_kequestionnaire_redirect.php']);
}

?>