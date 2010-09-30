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
 * Closed Questions Class
 *
 * Class for the  'ke_questionnaire' extension.
 *
 * @author	Nadine Schwingler <schwingler@kennziffer.com>
 * @package	TYPO3
 * @subpackage	tx_kequestionnaire
 * */

class question_closed extends question {
	var $templateName           = "question_closed.html";              //Name of default Templatefile
	
	/**
	 * The initiation method of the PlugIn
	 *
	 * @param	array		$conf: The plugin configuration
	 * @param	object		$parent: parent-Object
	 * @param	array		$answer: prefill Values for answers id/text
	 *
	 */
	function base_init($uid){
		// Options
		$this->options = array();
		$where = "question_uid=".$uid;
		$where .= " AND sys_language_uid=".$GLOBALS['TSFE']->sys_language_uid;
		$where .= $this->cObj->enableFields('tx_kequestionnaire_answers');
		$res = $GLOBALS["TYPO3_DB"]->exec_SELECTgetRows("*", "tx_kequestionnaire_answers", $where,'','sorting');
		//t3lib_div::devLog('res', 'question closed', 0, $res);

		foreach($res as $row){
			$row=$this->processRTEFields($row,"tx_kequestionnaire_answers");

			$this->options[$row["uid"]]=$row;
			$this->answers[$row["uid"]]=$row;
		}
		//t3lib_div::devLog('options', 'question closed', 0, $this->options);
	}


	/**
	 * Defines all fields in Template
	 *
	 *
	 */
	function buildFieldArray(){
		
		if($this->type == 'sbm_button' && ($this->obj->ffdata['render_type'] != 'QUESTIONS' || $this->obj->ffdata['render_count'] != 1)) {
			$this->type = 'radio_single';
		}
		
		switch($this->type){
			case "radio_single":
				$type= "radiobutton";
				$typeInput="radiobutton_with_input";
				$marker="###RADIO###";
				$markerInput="###RADIO_WITH_INPUT###";
				$this->buildFieldArrayForRadioAndCheckbox($type,$typeInput,$marker,$markerInput);
			break;
			case "check_multi":
				$type= "checkbox";
				$typeInput="checkbox_with_input";
				$marker="###CHECKBOX###";
				$markerInput="###CHECKBOX_WITH_INPUT###";
				$this->buildFieldArrayForRadioAndCheckbox($type,$typeInput,$marker,$markerInput);
			break;
			case "select_single":
				$type= "selectbox";
				$marker="###SELECT###";
				$this->fields["select"]=new kequestionnaire_input("select",$type,$this->answer["options"],$marker,$this->obj,$this->options,array(),array(),array(),'',$this->dependants);
			break;
			case "select_multi":
				$type= "selectbox_multi";
				$marker="###SELECT###";
				$this->fields["select"]=new kequestionnaire_input("select",$type,$this->answer["options"],$marker,$this->obj,$this->options,array(),array(),array(),'',$this->dependants);
				if ($this->question['closed_maxanswers'] > 0) $this->fields["select"]->maxAnswers = $this->question['closed_maxanswers'];
			break;
			case "sbm_button":				
				$type = "sbm_button";
				$marker="###SUBMIT_BUTTONS###";
				$this->fields["submit_buttons"]=new kequestionnaire_input("sbm_button",$type,$this->answers,$marker,$this->obj);
			default:
				$out= "Templatetype ".$this->question["closed_type"]." not defined!";
				//t3lib_div::debug($out,"buildFieldArray");
			break;
		}
		
		//Hook to manipulate the answers
		if (is_array($GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][$this->extKey]['closed_answers'])){
			foreach($GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][$this->extKey]['closed_answers'] as $_classRef){
				$_procObj = & t3lib_div::getUserObj($_classRef);
				$this->fields = $_procObj->closed_answers($this->question, $this->fields);
			}
		}
	}

	function buildFieldArrayForRadioAndCheckbox($type,$typeInput,$marker,$markerInput){
		$this->countInput=$this->question["closed_inputfield"]>0?$this->question["closed_inputfield"]:0;

		$i=0;	$this->lastOptionKeys=array();
		//t3lib_div::devLog('this->options', 'question closed', 0, $this->options);
		foreach($this->options as $key=>$val){
			//t3lib_div::devLog('answer '.$key, 'question closed', 0, array($val));
			if($val['show_input'] OR ($this->countInput && $i>=count($this->options)-$this->countInput)){
				$this->fields[$key]=new kequestionnaire_input($key,$typeInput,array("text"=>$this->answer["text"],"options"=>$this->answer["options"]),$markerInput,$this->obj,$this->options,array(),array(),array(),'',$this->dependants);
				$this->lastOptionKeys[]=$key;
			}else{
				$this->fields[$key]=new kequestionnaire_input($key,$type,$this->answer["options"],$marker,$this->obj,$this->options,array(),array(),array(),'',$this->dependants);
			}
			if ($this->question['closed_maxanswers'] > 0) $this->fields[$key]->maxAnswers = $this->question['closed_maxanswers'];
			$i++;
		}
		
		//t3lib_div::devLog('fields', 'question closed', 0, $this->fields);
	}
	
	/**
	 * Generate Javascript special for this question
	 *
	 * @return Javascript String
	 */
	function getSpecialJS(){
		$js = 'test';
		
		if ($this->question['closed_type'] == 'check_multi' OR $this->question['closed_type'] == 'select_multi'){
			if ($this->question['closed_maxanswers'] > 0){
				$js = 'onchange="alert("test");';
			}
		}
		
		return $js;
	}

	/**
	 * Selects Subpartname depending on Qustiontype
	 *
	 * @return      the whole question ready rendered
	 *
	 */
	function getTemplateName(){
		$this->type=$this->question["closed_type"];

		switch($this->type){
			case "radio_single":
				$out= "QUESTION_RADIO";
			break;
			case "check_multi":
				$out= "QUESTION_CHECKBOX";
			break;
			case "select_single":
				$out= "QUESTION_SELECTFIELD";
			break;
			case "select_multi":
				$out= "QUESTION_SELECTFIELD_MULTI";
			break;
			case "sbm_button":
				if($this->type == 'sbm_button' && ($this->obj->ffdata['render_type'] != 'QUESTIONS' || $this->obj->ffdata['render_count'] != 1)) {
					$out = "QUESTION_RADIO";
				} else {
					$out= "QUESTION_SUBMIT_BUTTONS";
				}
			break;
			default:
				$out= "Templatetype ".$this->question["closed_type"]." not defined!";
				//t3lib_div::debug($out,"getTemplateName");
				//t3lib_div::devLog('getTemplateName', 'closed', 0, $out);
			break;
		}
		//t3lib_div::devLog('getTemplateName', 'closed', 0, array($out));

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
		//t3lib_div::devLog('validate', 'question_closed', 0, array('type'=>$this->type));
		//t3lib_div::devLog('mist', 'test', 0, array($this->checkDependancies));

		$value=null;$doValidationForField=0;
		$errors=array();

		switch($this->type){
			case "radio_single":
			case "check_multi":
				foreach($this->fields as $key=>$field){
					if(!in_array($key,$this->lastOptionKeys)) continue;
					if($this->type=="radio_single"){
						if($key!=$this->answer["options"]) continue;
					}else{
						if(!in_array($key,$this->answer["options"])) continue;
					}
					$errors=$this->fields[$key]->validate(array("required"),$this->answer["text"][$key]);
					if(count($errors) > 0) {
						    $this->error=1;
						    $this->errorFields[] = $key;
					}
				}
			break;
			case "select_single":
			case "select_multi":
				if(!$this->question['mandatory']) continue; // required?
				$errors=$this->fields["select"]->validate(array("required_option"),$this->answer["options"][$key]);
				if(count($errors) > 0) {
					$this->error=1;
					$this->errorFields[] = $key;
				}
			break;

			default:
				$out= "Templatetype ".$this->type." not defined!";
			break;
		}
		// TODO: Check for correct answer

		// Check if any Value is selected for Checkboxes or Radio
		if($this->type!="radio_single" && $this->type!="check_multi") return;
		if(!$this->question['mandatory']) return;

		$value=$this->answer["options"];
		if(!empty($value)) return;
		if (!$this->checkDependancies()){
			$this->error=0;
		} else {
			$this->error=1;
			$this->errorMsg=$this->obj->pi_getLL("error_required");
		}
	}
}

?>
