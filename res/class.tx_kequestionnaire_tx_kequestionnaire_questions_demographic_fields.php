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
 * Class/Function which manipulates the item-array for table/field tx_kequestionnaire_demographic_fields.
 *
 * @author	Nadine Schwingler <schwingler@kennziffer.com>
 * @package	TYPO3
 * @subpackage	tx_kequestionnaire
 */
class tx_kequestionnaire_tx_kequestionnaire_questions_demographic_fields {
        function get_feuser_fields(&$params,&$pObj)	{
		global $LANG;
		$extConf = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['ke_questionnaire']);
		$excludes = explode(',',$extConf['demographic_fields_exclude']);
		//t3lib_div::devLog('extConf', 'demographic_fields', 0, $extConf);
		//t3lib_div::devLog('params', 'demographic_fields', 0, $params);
		
		$allowed_types = array('input','text','select','check');
		
		if (version_compare(TYPO3_branch, '6.1', '<')) {
			t3lib_div::loadTCA("fe_users");
		}
		
		$TCA = &$GLOBALS["TCA"]["fe_users"];
		//t3lib_div::devLog('tca', 'demographic_fields', 0, $TCA);
		$content = '';
		foreach ($TCA['columns'] as $name => $conf){
			if (!in_array($name,$excludes) AND in_array($conf['config']['type'],$allowed_types)){
				$label = rtrim($LANG->sL($conf['label']),':');
				$params['items'][] = array($label,$name);
				//$params['items'][] = array($name, $name,'');
				//$params['items'][] = array('Pflichtfeld');
			}
		}
		
		//return $params;
                // No return - the $params and $pObj variables are passed by reference, so just change content in then and it is passed back automatically...
	}
	
	function get_feuser_mandatory(&$params,&$pObj)	{
		global $LANG;
		$extConf = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['ke_questionnaire']);
		$excludes = explode(',',$extConf['demographic_fields_exclude']);
		//t3lib_div::devLog('extConf', 'demographic_fields', 0, $extConf);
		//t3lib_div::devLog('params', 'demographic_fields', 0, $params);
		
		$allowed_types = array('input','text','select','check');
		
		if (version_compare(TYPO3_branch, '6.1', '<')) {
			t3lib_div::loadTCA("fe_users");
		}
		$TCA = &$GLOBALS["TCA"]["fe_users"];
		//t3lib_div::devLog('tca', 'demographic_fields', 0, $TCA);
		$content = '';
		foreach ($TCA['columns'] as $name => $conf){
			if (!in_array($name,$excludes) AND in_array($conf['config']['type'],$allowed_types)){
				//t3lib_div::devLog('conf '.$name, 'demographic_fields', 0, $conf);
				$label = rtrim($LANG->sL($conf['label']),':');
				$params['items'][] = array($label.' *',$name);
				//$params['items'][] = array($name, $name,'');
				//$params['items'][] = array('Pflichtfeld');
			}
		}
		
		//return $params;
                // No return - the $params and $pObj variables are passed by reference, so just change content in then and it is passed back automatically...
	}
	function get_ttaddress_fields(&$params,&$pObj)	{
		//Remove
	}
	
	function get_ttaddress_mandatory(&$params,&$pObj)	{
		//Remove
	}	
}



if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/ke_questionnaire/res/class.tx_kequestionnaire_tx_kequestionnaire_questions_demographic_fields.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/ke_questionnaire/res/class.tx_kequestionnaire_tx_kequestionnaire_questions_demographic_fields.php']);
}

?>