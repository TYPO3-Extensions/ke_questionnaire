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
 * Class/Function which manipulates the item-array for table/field tx_kequestionnaire_questions_type.
 *
 * @author	Nadine Schwingler <schwingler@kennziffer.com>
 * @package	TYPO3
 * @subpackage	tx_kequestionnaire
 */
class tx_kequestionnaire_tx_kequestionnaire_questions_type {
	function main(&$params,&$pObj)	{
		global $GLOBALS;
		//t3lib_div::devLog('TSFE', 'type', 0, $GLOBALS['LANG']);
/*								
		debug('Hello World!',1);
		debug('$params:',1);
		debug($params);
		debug('$pObj:',1);
		debug($pObj);
*/
		//$temp = $_SERVER['SCRIPT_FILENAME'];
		//t3lib_div::devLog('temp', 'type', 0, array($temp));
		//$pos = strpos($temp,'/typo3/alt_doc');
		//t3lib_extMgm::extPath($_BASE_EXTKEY)
		//$path = substr($temp,0,$pos).'/'.t3lib_extMgm::siteRelPath('ke_questionnaire').'res/questions/';
		//t3lib_div::devLog('path', 'type', 0, array($path));
		//$path = '.';
		$path = t3lib_extMgm::extPath('ke_questionnaire').'res/questions/';
		if ($dir = opendir($path)){
			while($file=readdir($dir)){
				if (!$this->check_dir($file) && $file != "." && $file != ".."){
					$file = stristr($file,'question_');
					//t3lib_div::devLog('file', 'type', 0, array($file));
					$f_pos = strpos($file,'_');
					$file = substr($file,($f_pos+1));
					if ($file != ''){
						$file = explode('.',$file);
						$files[] = $file[0];
					}
				}
			}
		} else {
			//t3lib_div::devLog('file', 'type', 0, array($file));
		}		
		closedir($dir);
		//$path = substr($temp,0,$pos).'/'.t3lib_extMgm::siteRelPath('ke_questionnaire_premium').'res/questions/';
		if (t3lib_extMgm::isLoaded('ke_questionnaire_premium')){
			$path = t3lib_extMgm::extPath('ke_questionnaire_premium').'res/questions/';
			//t3lib_div::devLog('path', 'type', 0, array($path));
			//$path = '.';
			if ($dir = opendir($path)){
				while($file=readdir($dir)){
					if (!$this->check_dir($file) && $file != "." && $file != ".."){
						$file = stristr($file,'question_');
						//t3lib_div::devLog('file', 'type', 0, array($file));
						$f_pos = strpos($file,'_');
						$file = substr($file,($f_pos+1));
						if ($file != ''){
							$file = explode('.',$file);
							$files[] = $file[0];
						}
					}
				}
			} else {
				//t3lib_div::devLog('file', 'type', 0, array($file));
			}		
			closedir($dir);
		}
		
		asort($files);
		$params['items'] = array();
		foreach ($files as $nr => $type){
			$params['items'][] = array($GLOBALS['LANG']->sL("LLL:EXT:ke_questionnaire/locallang_db.xml:tx_kequestionnaire_questions.type.I.".$type),$type);
		}
		//t3lib_div::devLog('files '.$path, 'type', 0, $files);
		//t3lib_div::devLog('params', 'type', 0, $params);
		
		// No return - the $params and $pObj variables are passed by reference, so just change content in then and it is passed back automatically...
	}
	
	function check_dir($dir){
		// bypasses open_basedir restrictions of is_dir and fileperms
		$tmp_cmd = `ls -dl $dir`;
		$dir_flag = $tmp_cmd[0];
		if($dir_flag!="d")
		{
		    // not d; use next char (first char might be 's' and is still directory)
		    $dir_flag = $tmp_cmd[1];
		}
		return ($dir_flag=="d");
	}
}



if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/ke_questionnaire/res/class.tx_kequestionnaire_tx_kequestionnaire_questions_type.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/ke_questionnaire/res/class.tx_kequestionnaire_tx_kequestionnaire_questions_type.php']);
}

?>