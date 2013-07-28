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
 * Class/Function which manipulates the item-array for table/field tx_kequestionnaire_dependancies_activating_question.
 *
 * @author	Nadine Schwingler <schwingler@kennziffer.com>
 * @package	TYPO3
 * @subpackage	tx_kequestionnaire
 */
class tx_kequestionnaire_tx_kequestionnaire_dependancies_activating_question {
        function main(&$params,&$pObj)	{
/*
 debug('Hello World!',1);
 debug('$params:',1);
 debug($params);
 debug('$pObj:',1);
 debug($pObj);
*/
		$where = 'hidden=0 and deleted=0 and pid='.$params['row']['pid'];
		$where .= ' AND (type="closed"';
		$where .= ' OR (type="open" AND open_validation="numeric")';
		$where .= ' OR (type="open" AND open_validation="integer"))';
		//t3lib_div::devLog('params', 'activating_question', 0, $params);
		//t3lib_div::devLog('where', 'activating_question', 0, array($where));
		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*','tx_kequestionnaire_questions',$where,'sorting');
		if ($res){
			while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)){
				// Adding an item!
				$params['items'][] = array('['.$row['uid'].'] '.$row['title'], $row['uid']);	
			}
		}
		if (version_compare(TYPO3_branch, '6.1', '<')) {
			t3lib_div::loadTCA("tx_kequestionnaire_dependancies");
		}
		$TCA = &$GLOBALS["TCA"]["tx_kequestionnaire_dependancies"];
		$TCA['columns']['activating_value']['config']['items'] = array();
		if ($params['row']['activating_question']){
			$where = 'hidden=0 and deleted=0 and question_uid='.$params['row']['activating_question'];
			$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*','tx_kequestionnaire_answers',$where);
			if ($res){
				while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)){
					$TCA['columns']['activating_value']['config']['items'][] = array('['.$row['uid'].'] '.$row['title'],$row['uid']);
				}
			}
		}
		
		
		//t3lib_div::devLog('params', 'activating_question', 0, $params);
		
		// No return - the $params and $pObj variables are passed by reference, so just change content in then and it is passed back automatically...
	}
}



if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/ke_questionnaire/res/class.tx_kequestionnaire_tx_kequestionnaire_dependancies_activating_question.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/ke_questionnaire/res/class.tx_kequestionnaire_tx_kequestionnaire_dependancies_activating_question.php']);
}

?>