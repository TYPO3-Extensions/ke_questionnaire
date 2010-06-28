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
require_once(t3lib_extMgm::extPath('ke_questionnaire').'res/questions/class.kequestionnaire_input_matrix.php');


/**
 * Matrix Questions Class
 *
 * Class for the  'ke_questionnaire' extension.
 *
 * @author	Nadine Schwingler <schwingler@kennziffer.com>
 * @package	TYPO3
 * @subpackage	tx_kequestionnaire
 * */

class question_matrix  extends question{
	var $templateName           = "question_matrix.html";              //Name of default Templatefile

	/**
	 * The initiation method of the PlugIn
	 *
	 * @param	array		$conf: The plugin configuration
	 * @param	object		$parent: parent-Object
	 * @param	array		$answer: prefill Values for answers id/text
	 *
	 */
	function base_init($uid){
		// Subquestions
		$where = "question_uid=".$uid;
		$where .= " AND sys_language_uid=".$GLOBALS['TSFE']->sys_language_uid;
		$where .= $this->cObj->enableFields('tx_kequestionnaire_subquestions');
		$res=$GLOBALS["TYPO3_DB"]->exec_SELECTgetRows("*", "tx_kequestionnaire_subquestions", $where,'','sorting');

		foreach($res as $row){
			$row=$this->processRTEFields($row,"tx_kequestionnaire_subquestions");

			$this->subquestions[$row["uid"]]=$row;
			$this->subquestions[$row["uid"]]["error"]=0;
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
		switch($this->type){
			case "radio":
				$type= "matrix_radio";
				$this->buildFieldArrayForElement($type);
			break;
			case "check":
				$type= "matrix_checkbox";
				$this->buildFieldArrayForElement($type);
			break;
			case "input":
				switch($this->question["matrix_validation"]){
					case "":
						$type= "matrix_input";
					break;
					case "numeric":
						$type= "matrix_input_numeric";
					break;
					case "date":
						$type= "matrix_input_date";
					break;
					case "percent":
						$type= "matrix_input_percent";
					break;
				}
				$this->buildFieldArrayForElement($type);
			break;
			default:
				$out= "Templatetype ".$this->type." not defined!";
				//t3lib_div::debug($out,"buildFieldArray");
			break;
		}
	}

	function buildFieldArrayForElement($type){
		$typeHead= "matrix_head";
		$marker="###HEAD###";
		$this->fields["head"]=new kequestionnaire_input_matrix("head",$typeHead,$this->answer,$marker,$this->obj,$this->subquestions,$this->columns, $this->question['matrix_maxanswers']);
		$marker="###SUBQUESTION###";
		$this->countInput=$this->question["matrix_inputfield"]>0?$this->question["matrix_inputfield"]:0;
		$i=0;$this->lastOptionKeys=array();

		foreach($this->subquestions as $key=>$val){
			$typeField=$type;
			if($i>=count($this->subquestions)-$this->countInput) {
				$typeField.="_with_input";
				$this->lastOptionKeys[]=$key;
			}
			//#############################################
			// KENNZIFFER Nadine Schwingler 03.11.2009
			// Anpassung Title-Line
			if ($val['title_line'] == 1) $typeField = 'matrix_title_line';
			//#############################################
			$this->fields[$key]=new kequestionnaire_input_matrix($key,$typeField,$this->answer,$marker,$this->obj,$this->subquestions,$this->columns, $this->question['matrix_maxanswers']);
			$i++;
		}
		if($type=="matrix_input_percent") $this->fields["sum"]=new kequestionnaire_input_matrix($key,"matrix_input_percent_sum",$this->answer["options"],"###SUM###",$this->obj,$this->options,$this->subquestions,$this->columns, $this->question['matrix_maxanswers']);
	}


	/**
	 * Selects Subpartname depending on Qustiontype
	 *
	 * @return      the whole question ready rendered
	 *
	 */
	function getTemplateName(){
		$this->type=$this->question["matrix_type"];

		$out = 'QUESTION_MATRIX_GENERAL';
		return $out;
	}

	/**
	 * The validation method of the Question-Class
	 *
	 * @return	boolean true if validation is correct
	 * 		Error-String if validation failed
	 *
	 */
	function validate(){
		//t3lib_div::devLog('validate', 'question_matrix', 0,array($this->question["matrix_validation"]));
		foreach($this->fields as $key=>$field){
			$validationTypes=array();
			if(in_array($key,$this->lastOptionKeys)) continue; // no validation for martix inputfield rows
			if($key=="head") continue;
			switch($this->type){
				case "radio":
				case "check":
					if(!$this->question['mandatory']) break;
					$value=$this->answer["options"];
					$validationTypes[]="matrix_required_option";
				break;
				case "input":
					$value=$this->answer["options"];
					if($this->question['mandatory']) $validationTypes[]="matrix_required_input";
					switch($this->question["matrix_validation"]){
						case "numeric":
							$validationTypes[]="matrix_numeric";
						break;
						case "date":
							$validationTypes[]="matrix_date";
						break;
						case "percent":
							$validationTypes[]=($field->type=="matrix_input_percent_sum")?"matrix_sum":"matrix_numeric";
						break;
					}
				break;
				default:
					$out= "Templatetype ".$this->type." not defined!";
				break;
			}

			$validationOptions["dateFormat"]=$this->dateFormat;
			$validationOptions["numberDivider"]=$this->numberDivider;

			$errors=$field->validate($validationTypes,$value,$validationOptions);
			//t3lib_div::devLog('validate '.$this->question['uid'], 'question_matrix', 0, array('errors'=>$errors));
			if(count($errors) > 0) {
			    $this->error=1;
			    $this->errorFields[] = $key;
		        }
		}
	}
}
?>
