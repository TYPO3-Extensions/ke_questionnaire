<?php
#,6,blind
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
 * Blindtext Questions Class
 * 
 * Class for the  'ke_questionnaire' extension.
 *
 * @author	Nadine Schwingler <schwingler@kennziffer.com>
 * @package	TYPO3
 * @subpackage	tx_kequestionnaire
 * */

class question_blind  extends question{
    var $templateName           = "question_blind.html";              //Name of default Templatefile


    /**
	 * Defines all fields in Template
	 *
	 *
	 */
    function buildFieldArray(){
	
		$this->fields["text"]=new kequestionnaire_input("text","blind",array("text"=>$this->question["helptext"]),"###BLIND###",$this->obj);
    }

    /**
	 * Selects Subpartname depending on Qustiontype
	 *
	 * @return      the whole question ready rendered
	 *
	 */   
	function getTemplateName(){
		
		return "QUESTION_BLIND";
		
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
	}


}
?>
