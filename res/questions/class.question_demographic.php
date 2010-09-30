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
 * Demographic Questions Class
 *
 * Class for the  'ke_questionnaire' extension.
 *
 * @author	Nadine Schwingler <schwingler@kennziffer.com>
 * @package	TYPO3
 * @subpackage	tx_kequestionnaire
 * */

class question_demographic extends question{
	var $templateName           = "question_demographic.html";              //Name of default Templatefile

    /**
	 * Defines all fields in Template
	 *
	 *
	 */
    function buildFieldArray(){
		switch($this->type){
			case "both":
				$tables=array("fe_users","tt_address");
			break;
			case "address":
				$tables=array("tt_address");
			break;
			case "demo":
				$tables=array("fe_users");
			break;
		}
		if (is_array($tables)){
			foreach($tables as $table){
				$this->demographicOptions[$table]=$this->getOptionsForDemographic($this->question,$table);
				$options=$this->demographicOptions[$table];
				if(!isset($options["fields"])) return;
	
	
				foreach($options["fields"] as $fieldName=>$type){
					$answer=isset($this->answer[$table][$fieldName])?$this->answer[$table][$fieldName]:"";
					$marker="###DEFAULT###";
	
					$html=$this->cObj->getSubpart($this->tmplFields,"###".strtoupper("demo_".$fieldName)."###");
	
					$marker=$html==""?"###DEMO_DEFAULT###":"###".strtoupper($fieldName)."###";
	
					$label=$this->obj->pi_getLL($fieldName,$fieldName);
	
					$options=array();
					switch($type){
						case "selectbox":
						case "demographic_radio":
							if(isset($options["options"][$fieldName])){
								foreach($options["options"][$fieldName] as $key=>$val){
									$options[]=array("uid"=>$key,"title"=>$val);
								}
							}
						break;
						default:
	
							$options=array();
	
					}
					$fieldName=$table."__".$fieldName;
					$this->fields[$fieldName]=new kequestionnaire_input($fieldName,$type,$answer,$marker,$this->obj,$options,array(),array(),array(),$label);
	
				}
			}
		}
    }

	/**
	 * Set options for demographic question type from flexform date
	 *
	 * @param       string     	$question
	 * @return      array()
	 *
	 */
	function getOptionsForDemographic($question,$table){
		t3lib_div::loadTCA($table);
		$TCA = $GLOBALS["TCA"][$table];


		$flex=$table=="fe_users"?$question["demographic_fields"]:$question["demographic_addressfields"];
		$flex = t3lib_div::xml2array($flex);

		// Parse Flexform Data
		$ffdata=array();
		if (is_array($flex['data'])) {
			foreach ( $flex['data'] as $sheet => $data ) {
				foreach ( $data as $lang => $value ) {
					foreach ( $value as $key => $val ) {
						$ffdata[$sheet][$key] = $this->obj->pi_getFFvalue($flex, $key, $sheet);
					}
				}
			}
		}


		// Fields
		$fields=t3lib_div::trimExplode(",",$ffdata["sDEF"]["FeUser_Fields"]);
		//t3lib_div::debug($fields,"fields");

		foreach($fields as  $field){
			//t3lib_div::debug($TCA["columns"][$field],"getOptionsForDemographic");

			if($field=="") continue;
			if(!isset($TCA["columns"][$field]["config"]["type"])) continue;

			switch($TCA["columns"][$field]["config"]["type"]){
				case "input":
				case "text":
					$out["fields"][$field]="input";
				break;
			}
		}

		// Validation
		$fields=t3lib_div::trimExplode(",",$ffdata["mDEF"]["FeUser_Fields"]);
		foreach($fields as  $field){
			if($field=="") continue;
			if(!isset($out["fields"][$field])) continue;
			$out["validation"][$field]="required";
		}

		// Options
		$out["options"]=array();

		return $out;

	}


    /**
	 * Selects Subpartname depending on Qustiontype
	 *
	 * @return      the whole question ready rendered
	 *
	 */
	function getTemplateName(){

		$this->type=$this->question["demographic_type"];

		switch($this->type){
			case "demo":
				$out= "QUESTION_DEMOGRAPHIC";
			break;
			case "address":
				$out= "QUESTION_ADDRESS";
			break;
			case "both":
				$out= "QUESTION_BOTH";
			break;
			default:
				$out= "Templatetype ".$this->question["matrix_type"]." not defined!";
				//t3lib_div::debug($out,"getTemplateName");

			break;
		}

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
	return;
		foreach($this->fields as $key=>&$field){
			$validationTypes=array();
			if(!isset($this->demographicOptions["validation"][$key])) continue;

			switch($field->type){
				case "input":
					$validationTypes[]=$this->demographicOptions["validation"][$key];
				break;
				case "selectbox":

				break;
				case "demographic_radio":
					// if(!$this->question['mandatory']) break;
					//
					// $value=$this->answer["options"];
					// $validationTypes[]="matrix_required_option";
				break;

				default:
					$out= "Templatetype ".$this->type." not defined!";

				break;
			}


			$errors=$field->validate($validationTypes);
			if (!$this->checkDependancies()){
				$this->error=0;
			} elseif(count($errors) > 0) {
				$this->error=1;
				$this->errorFields[] = $key;
			};
		}

	}

}

?>
