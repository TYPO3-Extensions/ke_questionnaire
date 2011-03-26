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
 * Open Questions Class
 *
 * Class for the  'ke_questionnaire' extension.
 *
 * @author	Nadine Schwingler <schwingler@kennziffer.com>
 * @package	TYPO3
 * @subpackage	tx_kequestionnaire
 * */

class question_open extends question{
    var $templateName           = "question_open.html";              //Name of default Templatefile


    /**
	 * Defines all fields in Template
	 *
	 *
	 */
    function buildFieldArray(){
		$this->fields["text"]=new kequestionnaire_input("text","input",$this->answer["text"],"###INPUT###",$this->obj,array(),array(),array(),array(),"",$this->dependants);
    }

    /**
	 * Selects Subpartname depending on Qustiontype
	 *
	 * @return      the whole question ready rendered
	 *
	 */
	function getTemplateName(){
		switch($this->question["open_type"]){
			case 0:
				switch($this->question["open_validation"]){
					case "numeric":
						$out= "QUESTION_NUMBERIC";
					break;
					case "integer":
						$out= "QUESTION_INTEGER";
					break;
					case "date":
						$out= "QUESTION_DATE";
					break;
					case "email":
						$out= "QUESTION_EMAIL";
					break;
					default:
						$out= "QUESTION_SINGLE";
					break;
				}
			break;
			case 1:
				$out= "QUESTION_MULTI";
			break;
			default:
				$out= "Templatetype ".$this->question["open_type"]." not defined!";
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
		//t3lib_div::devLog('validate', 'question_open', 0, array('question'=>$this->question,'answer'=>$this->answer['text']));
		//t3lib_div::devLog('extConf', 'question_open', 0, $this->obj->extConf);
		//t3lib_div::devLog('this->question', 'question_open', 0, $this->question);
		$value=$this->answer['text'];

		// Collect all types of required validation
		$validationTypes=array();
		if($this->question['open_validation']) $validationTypes[]=$this->question['open_validation']; // validation for special type?
		if($this->question['mandatory']) $validationTypes[]="required"; // required?
		
		$validationOptions["dateFormat"]=$this->dateFormat;
		$validationOptions["numberDivider"]=$this->numberDivider;
		if ($this->question['open_validation'] == 'text') $validationOptions["textOptions"]=explode($this->obj->extConf['oq_validation_parter'],$this->question['open_validation_text']);
		if ($this->question['open_validation'] == 'keys') {
		    $validationOptions["textOptions"]=explode($this->obj->extConf['oq_validation_parter'],$this->question['open_validation_keywords']);
		    $validationOptions["matchAll"] = $this->question['open_validation_keywords_all'];
		    if ($this->question['open_validation_keywords_all']){
			foreach ($validationTypes as $key => $vvalue){
			    //t3lib_div::debug('test', $key);
			    if ($vvalue == 'keys') $validationTypes[$key] = $vvalue.'_all';
			}
		    }
		}
		// Get all validation errors
		$errors=$this->fields["text"]->validate($validationTypes,$value,$validationOptions);
		if (!$this->checkDependancies()){
			$this->error=0;
		} elseif(count($errors) > 0) {
		    $this->error=1;
		    $this->errorFields[] = $key;
		}

	}


}

?>
