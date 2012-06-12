<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2012 www.kennziffer.com GmbH <info@kennziffer.com>
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
 * Swiftmail class for ke_questionnaire
 *
 * @package 	TYPO3
 * @subpackage 	tx_kequequestionnaire_swiftmailer
 */

class tx_kequestionnaire_swiftmailer {	
	protected $swiftMailObj = null;
	
	public function __construct() {
		//create new swiftmailer object
		$this->swiftMailObj = t3lib_div::makeInstance('t3lib_mail_Message');
	}
	
	public function send($swiftParameters = array()) {
		$checkRequired = array('setTo','setFrom','setSubject','setBody');
		
		//parameters not set properly
		if(!is_array($swiftParameters) || !count($swiftParameters)) {
			return false;
		}
		
		//all basically required parameters set?
		foreach($checkRequired as $requiredParameter) {
			if(!array_key_exists($requiredParameter,$swiftParameters) || empty($requiredParameter)) {
				return false;
			}
		}
		
		//set vars for swiftmailer dynamically
		foreach($swiftParameters as $swKey => $swValue) {
			if(method_exists($this->swiftMailObj, $swKey)) {
				if($swKey == 'setBody' || $swKey == 'addPart') {
					$bodyText = $swValue[0];
					$bodyContentType = $swValue[1];
					
					$this->swiftMailObj->$swKey($bodyText,$bodyContentType);
				} else {
					$this->swiftMailObj->$swKey($swValue);
				}
			}
		}
		
		//send mail
		$numSent = $this->swiftMailObj->send();
		return $numSent;
	}
	
	public function __destruct() {
		//destroy swiftmailer object
		$this->swiftMailObj = null;
	}
	
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/ke_questionnaire/res/other/class.swiftmailer.php']){
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/ke_questionnaire/res/other/class.swiftmailer.php']);
}
?>