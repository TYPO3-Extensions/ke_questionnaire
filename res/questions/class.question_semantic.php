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

require_once(PATH_tslib.'class.tslib_pibase.php');


/**
 * Semantic Differential Questions Class
 * 
 * Class for the  'ke_questionnaire' extension.
 *
 * @author	Nadine Schwingler <schwingler@kennziffer.com>
 * @package	TYPO3
 * @subpackage	tx_kequestionnaire
 * */

class question_semantic extends question{
	var $templateName           = "question_semantic.html";              //Name of default Templatefile

	/**
	 * The initiation method of the PlugIn
	 *
	 * @param	array		$conf: The plugin configuration
	 * @param	object		$parent: parent-Object
	 * @param	array		$answer: prefill Values for answers id/text
	 *
	 */
	function base_init($uid){
		// Sublines
		$where = "question_uid=".$uid;
		$where .= " AND sys_language_uid=".$GLOBALS['TSFE']->sys_language_uid;
		$where .= $this->cObj->enableFields('tx_kequestionnaire_sublines');
		$res=$GLOBALS["TYPO3_DB"]->exec_SELECTgetRows("*", "tx_kequestionnaire_sublines", $where,'','sorting');

		foreach($res as $row){
			$row=$this->processRTEFields($row,"tx_kequestionnaire_sublines");

			$this->sublines[$row["uid"]]=$row;
			$this->sublines[$row["uid"]]["error"]=0;
		}
		
		// Columns
		$where = "question_uid=".$uid;
		$where .= " AND sys_language_uid=".$GLOBALS['TSFE']->sys_language_uid;
		$where .= $this->cObj->enableFields('tx_kequestionnaire_columns');
		$res=$GLOBALS["TYPO3_DB"]->exec_SELECTgetRows("*", "tx_kequestionnaire_columns", $where,'','sorting');

		foreach($res as $row){
			$row=$this->processRTEFields($row,"tx_kequestionnaire_columns");

			$this->columns[$row["uid"]]=$row;
			$this->answers[$row["uid"]]=$row;
		}
	}

	/**
	 * Defines all fields in Template
	 *
	 *
	 */
	function buildFieldArray(){
		$typeHead= "matrix_head";
		$marker="###HEAD###";
		$this->fields["head"]=new kequestionnaire_input("head",$typeHead,$this->answer["options"],$marker,$this->obj,$this->options,$this->subquestions,$this->columns,$this->sublines);				
		
		$marker="###SUBQUESTION###";
		$type="semantic";
		
		foreach($this->sublines as $key=>$val){				
			$this->fields[$key]=new kequestionnaire_input($key,$type,$this->answer,$marker,$this->obj,$this->options,$this->subquestions,$this->columns,$this->sublines);				
			
		}
	}

	/**
	 * Selects Subpartname depending on Qustiontype
	 *
	 * @return      the whole question ready rendered
	 *
	 */   
	function getTemplateName(){			
		return "QUESTION_SEMANTIC";
	}



	/**
	 * The validation method of the Question-Class
	 *
	 * @return	boolean true if validation is correct
	 * 		Error-String if validation failed
	 *
	 */
	function validate(){
		if(!$this->question['mandatory']) return;
		$validationTypes[]="semantic_required";
		$value=$this->answer["options"];

		foreach($this->fields as $key=>$field){						
			if($key=="head") continue;
			$errors=$field->validate($validationTypes,$value);
			if(count($errors)) $this->error=1;
		}
		if (!$this->checkDependancies()){
			$this->error=0;
		}
	}
	
	/**
	 * get simple Answer-String
	 *
	 */
	function getSimpleAnswer(){
		$saveA = $this->getSaveArray();
		$saveA = $saveA[$this->uid];
		
		$answer =  '';
		
		//t3lib_div::debug($saveA);
		if (is_array($saveA['answer'])){
			$answer .= '<table>';
			$answer .= '<tr><th></th>';
			foreach ($saveA['possible_answers'] as $ckey => $cols){
				if ($ckey != 'lines'){
					$answer .= '<th>'.$cols.'</th>';
				}
			}
			$answer .= '<th></th></tr>';
			foreach ($saveA['possible_answers']['lines'] as $rkey => $rows){
				$answer .= '<tr>';
				$answer .= '<td>'.$rows['start'];
				//t3lib_div::debug($rows,'rows');
				if (is_array($saveA['answer']['text']) && $saveA['answer']['text'][$rkey]){
					$answer .= ' ('.$saveA['answer']['text'][$rkey][0].')';
				}
				$answer .= '</td>';
				foreach ($saveA['possible_answers'] as $ckey => $cols){
					if ($ckey != 'lines'){
						if ($saveA['answer']['options'][$rkey] == $ckey){
							$answer .= '<td style="text-align: center">X</td>';
						} else {
							$answer .= '<td></td>';
						}
					}
				}
				$answer .= '<td>'.$rows['end'].'</td>';
				$answer .= '</tr>';
			}
			$answer .= '</table>';
		}
	   
		return $answer;
        }
}


?>
