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
 * Privacy Policy Questions Class
 * 
 * Class for the  'ke_questionnaire' extension.
 *
 * @author	Nadine Schwingler <schwingler@kennziffer.com>
 * @package	TYPO3
 * @subpackage	tx_kequestionnaire
 * */

class question_privacy extends question{
	var $templateName           = "question_privacy.html";              //Name of default Templatefile
    
    /**
	 * Defines all fields in Template
	 *
	 *
	 */
    function buildFieldArray(){
		$this->fields["privacy"]=new kequestionnaire_input("1","checkbox",$this->answer["options"],"###CHECKBOX###",$this->obj);
    }

    /**
	 * Selects Subpartname depending on Qustiontype
	 *
	 * @return      the whole question ready rendered
	 *
	 */   
	function getTemplateName(){
		return "QUESTION_PRIVACY";
		
	}
    
 

    /**
	 * The validation method of the Question-Class
	 *
	 * @return	boolean true if validation is correct
	 * 		Error-String if validation failed
	 *
	 */
    function validate(){
		$value=$this->answer['options'];
		
		$validationTypes[]='required_option';
				
		// Get all validation errors
		$errors=$this->fields["privacy"]->validate($validationTypes,$value);
		if(count($errors)>0){
			$this->error=1;
			
		}
				
	}

}
?>
