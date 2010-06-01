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
 * Class/Function which manipulates the item-array for table/field tx_kequestionnaire_dependancies_activating_value.
 *
 * @author	Nadine Schwingler <schwingler@kennziffer.com>
 * @package	TYPO3
 * @subpackage	tx_kequestionnaire
 */
class tx_kequestionnaire_tx_kequestionnaire_dependancies_activating_value {
        function main(&$params,&$pObj)	{
/*
 debug('Hello World!',1);
 debug('$params:',1);
 debug($params);
 debug('$pObj:',1);
 debug($pObj);
*/
//echo t3lib_div::debug($pObj);

         // Adding an item!
	 //$params['items'][] = array($pObj->sL("Added label by PHP function|Tilføjet Dansk tekst med PHP funktion"), 999);
	 //$params['items'][] = array($pObj->sL("Added label by PHP function|Tilføjet Dansk tekst med PHP funktion"), 999);
	 //t3lib_div::devLog('params', 'activating_value', 0, $params);
	 
	 // No return - the $params and $pObj variables are passed by reference, so just change content in then and it is passed back automatically...
}
}



if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/ke_questionnaire/res/class.tx_kequestionnaire_tx_kequestionnaire_dependancies_activating_value.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/ke_questionnaire/res/class.tx_kequestionnaire_tx_kequestionnaire_dependancies_activating_value.php']);
}

?>