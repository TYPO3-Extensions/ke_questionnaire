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
 * $Id$
 */

/*
 * Aus Flexform entformt
 *<linear>
	<TCEforms>
		<label>LLL:EXT:ke_questionnaire/locallang.xml:tt_content.pi_flexform.linear</label>
		<config>
			<type>check</type>
		</config>
	</TCEforms>
 *</linear>
 *
 */

require_once(PATH_tslib.'class.tslib_pibase.php');

require_once(t3lib_extMgm::extPath('ke_questionnaire').'res/questions/class.question.php');

/**
 * Plugin 'questionnaire' for the 'ke_questionnaire' extension.
 *
 * @author	Nadine Schwingler <schwingler@kennziffer.com>
 * @package	TYPO3
 * @subpackage	tx_kequestionnaire
 */
class tx_kequestionnaire_pi1 extends tslib_pibase {
	var $prefixId      = 'tx_kequestionnaire_pi1';		// Same as class name
	var $scriptRelPath = 'pi1/class.tx_kequestionnaire_pi1.php';	// Path to this script relative to the extension dir.
	var $extKey        = 'ke_questionnaire';	// The extension key.

	var $ffdata	   = array();			//FlexForm data array
	var $extConf	   = array();			//ext_conf_template.txt
	var $template	   = '';			//template Filename
	var $tmpl	   = '';			//template
	var $pid 	   = 0;				//Pid where all the data is stored
	var $allQuestions  = array();			//Array to store all questions of this questionnaire
	var $questions	   = array();			//Array to store questions not of type blind of this questionnaire
	var $questionsByID = array();			//Array to store questions of this questionnaire By ID
	var $questionCount = array();			//count all the questions of the questionnaire
	var $user_id	   = 0;				//ID of the user filling in the questionnaire
	var $userMarker    = array(); 			//Marker for User-Values in Templates

	var $saveString    = '';			//String to store the saved Data
	var $saveArray    = '';				//Array to store the Data
	var $new	   = true;			//user made a fresh start of questionnaire
	var $finished	   = false;			//user has submitted the last questionnaire-page
	var $lastAnswered  = 0;				//when an result is loaded, get the id of the last answered question to jum to that page

	var $pageJS        = '';			//set focus for validation
	var $addHeaderData = array();			//due to the fact, that there are more than one point to add headerData, it will be stored in an array and processed at the end of the main func
	var $validated 	   = false;

	/**
	 * main(): The main method of the PlugIn
	 *
	 * @param	string		$content: The PlugIn content
	 * @param	array		$conf: The PlugIn configuration
	 * @return	The content that is displayed on the website
	 */
	function main($content,$conf)	{
		//When XAJAX->checkDependants is used, there is only a question-obj needed.
		if (t3lib_div::_GP('xajax') == 'checkDependants') {
			$dummy_obj = new question_blind();
			$dummy_obj->init(0,$this,array());
			exit;
		}
		$this->conf=$conf;
		$this->pi_setPiVarDefaults();
		$this->pi_loadLL();
		$this->pi_initPIflexform();
		$this->pi_USER_INT_obj=1;	// Configuring so caching is not expected. This value means that no cHash params are ever set. We do this, because it's a USER_INT object!
		//Initialize the Plugin
		$this->init();
		
		
		// There are main tasks we might have to do: 
		//   mainAskQuestions OR mainGetPdf OR mainSendMail
		// First thing is to set a flag indicating what's needed
		$this->mainTask = 'mainAskQuestions';
		if ($this->piVars['pdf'] == 1) {
			$this->mainTask = 'mainGetPdf';
		} elseif (intval($this->piVars['sendemail']) == 1){
			$this->mainTask = 'mainSendMail';
		}

		// now use the flag in the main task dispatcher
		$content = '';
		switch ($this->mainTask) {
			case 'mainAskQuestions':
				$content = $this->mainAskQuestions();
				break;
			case 'mainGetPdf':
				$content = $this->mainGetPdf();
				break;
			case 'mainSendMail':
				$content = $this->mainSendMail();
				break;
			default:
				$content = 'unknown task \'' . $this->mainTask . '\'';
		}
		
		return $content;
	}
	
	/**
	 * mainSendMail(): Main Fork Function to send Emails
	 *
	 * @return	The content that is displayed on the website
	 */
	function mainSendMail() {
		if(t3lib_div::validEmail($this->piVars['email'])) {
			$mailSent = $this->sendByMail($this->piVars['email']);
			if($mailSent) {
				$content = $this->pi_getLL('email_sent');
			} else {
				$content = $this->pi_getLL('email_not_sent').'<br /><a href="javascript: history.back();">'.$this->pi_getLL('email_back').'</a>';
			}
		} else {
			$content = $this->pi_getLL('email_not_sent').'<br /><a href="javascript: history.back();">'.$this->pi_getLL('email_back').'</a>';
		}
		
		return $content;
	}

	/**
	 * mainGetPdf(): Main Fork Function to get the Results for the current participation
	 *
	 * @return	PDF-Result
	 */
	function mainGetPdf() {
		$content = '';
			// allow a different method to display results
		if ($this->conf['switchToPdfGenerator'] == 'pi1/class.pdfresult.php') {
			require_once(t3lib_extMgm::extPath('ke_questionnaire').'pi1/class.pdfresult.php');
			$content = pdfresult::main($this);
		} else {
			// get the PDF-Version of the Questionnaire => the response is a pdf
			if ($this->piVars['pdf'] == 1){
				$this->getPDF($this->piVars['type']);
				exit;
			}
		}
		return $content;
	}

	/**
	 * mainAskQuestions(): Main Fork Function to render the Questionnaire
	 *
	 * @return	content to be displayed
	 */	
	function mainAskQuestions() {
		// if there are no questions made for the questionnaire
		if (count($this->questions) == 0) {
			$content = $this->pi_getLL('no_questions');
			//Hook to manipulate the Error-Message for no questions
			if (is_array($GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][$this->extKey]['pi1_noQuestions'])){
				foreach($GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][$this->extKey]['pi1_noQuestions'] as $_classRef){
					$_procObj = & t3lib_div::getUserObj($_classRef);
					$content = $_procObj->pi1_noQuestions($this);
				}
			}
			return $this->pi_wrapInBaseClass($content);
		}
		//if the validation is not processed correctly the former page will be shown
		if (!$this->checkValidation()) $this->piVars['page'] --;

		$content = '';
		$subPart = '';
		$save = true;
		$markerArray = array();
		$markerArray['###PI###'] = $this->prefixId;
		
		//check if history should be made
		$make_history = false;
		if ($this->ffdata['history'] == 1){
			$make_history = true;
		}
		
		//different handling for different access-types
		switch ($this->ffdata['access']){
			//free access, no check needed
			case 'FREE':
				//$this->ffdata['render_count_withblind'] = 1;
				$subPart = '###QUESTIONNAIRE###';
				$markerArray['###PAGES###'] = $this->getPages();
				$save = false;
				break;
			//acces only for fe_user or authcodes
			case 'FE_USERS':
			case 'AUTH_CODE':
				//if authcode and authcode is not valid
				if ($this->ffdata['access']=='AUTH_CODE' AND !$this->checkAuthCode()){
					//show the text for no authcode the input for the authcode
					$subPart = '###NO_AUTHCODE###';
					$markerArray['###FORM_ACTION###'] = htmlspecialchars($this->pi_getPageLink($GLOBALS['TSFE']->id));
					$markerArray['###TEXT###'] = $this->pi_getLL($this->no_authcodeKey);
					$markerArray['###SUBMIT_LABEL###'] = $this->pi_getLL('authcode_submit_label');
					$save = false;
				//if fe_user and no user logged in
				} elseif ($this->ffdata['access']=='FE_USERS' AND $this->user_id == 0){
					//show the text
					$subPart = '###ONLY_FEUSER###';
					$markerArray['###TEXT###'] = $this->pi_getLL('only_feuser');
					$save = false;
				//else there is a valid authcode or logged in user
				} else {
					if ($this->user_id){
						foreach ($this->userMarker as $marker => $value){
							$markerArray[$marker] = $value;
						}
					}
					//check if the user has already paricipated
					$last_result = array();
					if (!$this->piVars['result_id']){
						$check_result = $this->checkResults();
					}
					//and select the last one if there is one and not working on one
					if ($check_result['last_result'] > 0 AND $check_result['finished_count'] < $this->ffdata['max_participations'] AND !$this->piVars['result_id']){
						if ($this->ffdata['restart_possible'] != 1){
							$this->getResults($check_result['last_result'],$make_history);
							if ($this->lastAnswered > 0) $this->getPageNr();
							//t3lib_div::devLog('loaded saveArray', $this->prefixId, 0, $this->saveArray);
							$subPart = '###QUESTIONNAIRE###';
							$markerArray['###PAGES###'] = $this->getPages();
						//else show the restart page
						} else {
							$save = false;
							$subPart = '###RESUME_LAST###';
							$markerArray['###FORM_ACTION###'] = htmlspecialchars($this->pi_getPageLink($GLOBALS['TSFE']->id,'',array($this->prefixId.'[result_id]'=>($check_result['last_result']))));
							$markerArray['###TEXT###'] = $this->pi_getLL('resume_last');
							$markerArray['###AUTHCODE###'] = '';
							if ($this->piVars['auth_code']) $markerArray['###AUTHCODE###'] = '<input type="hidden" name="'.$this->prefixId.'[auth_code]" value="'.$this->piVars['auth_code'].'" />';
							$markerArray['###RESUME_LABEL###'] = $this->pi_getLL('resume_label');
							$markerArray['###RESTART_LABEL###'] = $this->pi_getLL('restart_label');
						}
					} else {
						//if the patricipation is new and the user can patricipate once more
						//then load the data and show the questionnaire
						if ($check_result['finished_count'] < $this->ffdata['max_participations']){
							//if the user wants to restart the result is cleared and the user completely restarts the answering
							if ($this->piVars['submit_type'] == 'restart') {
								$this->clearResults($this->piVars['result_id'],$make_history);
							//else if the user wants to resume his last position and all his given answers
							} elseif ($this->piVars['submit_type'] == 'resume') {
								$this->getResults($this->piVars['result_id'],$make_history);
								if ($this->lastAnswered) $this->getPageNr();
							} else {
								$this->getResults($this->piVars['result_id'],false);
							}
							$subPart = '###QUESTIONNAIRE###';
							$markerArray['###PAGES###'] = $this->getPages();
						//else Show the page informing the user that he has already participated
						} else {
							$subPart = 'NOMORE';
							$markerArray['###TEXT###'] = $this->pi_getLL('no_more');
							$save = false;
						}
					}
					//t3lib_div::devLog('markerArray for PI1', $this->prefixId, 0, $markerArray);
				}
				break;
			default:
				//Hook to include new access-types
				if (is_array($GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][$this->extKey]['pi1_accessType'])){
					foreach($GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][$this->extKey]['pi1_accessType'] as $_classRef){
						$_procObj = & t3lib_div::getUserObj($_classRef);
						$parts = array();
						$parts['markerArray'] = $markerArray;
						//contains: subPart, markerArray and save variable
						$parts = $_procObj->pi1_accessType($this,$parts);
						$subPart = $parts['subPart'];
						$markerArray = $parts['markerArray'];
						$save = $parts['save'];
					}
				}
				break;
		}
		
		//render the content
		$content = $this->renderContent($subPart,$markerArray);
		
		//if the save-Flag is set and it's not the first page with a description
		if ($save){
			//save the results
			$this->setResults($this->piVars['result_id']);
			//t3lib_div::devLog('saved saveArray '.$this->piVars['result_id'], $this->prefixId, 0, array($this->saveArray));
		}

		//if there is additional Header Data in the array
		if (is_array($this->addHeaderData)){
			//t3lib_div::devLog('fe js', $this->prefixId, 0, $this->addHeaderData);
			foreach ($this->addHeaderData as $script){
				//t3lib_div::devLog('fe js', $this->prefixId, 0, array($script));
				$GLOBALS['TSFE']->additionalHeaderData[$this->prefixId] .= $script;
			}
		}
		if(count($GLOBALS['TSFE']->register['kequestionnaire'])) {
			$GLOBALS['TSFE']->additionalHeaderData['keq-js-slider'] = '
				<script type="text/javascript">
					$(document).ready(function() {' .
					implode(CHR(10), $GLOBALS['TSFE']->register['kequestionnaire']) . '
					});
				</script>
			';			
		}
		
		return $this->pi_wrapInBaseClass($content);
	}
	
	/**
	 * getTimer(): Create Timer with the given Max-Time
	 *
	 * @param	integer		$time: length of the given time
	 * @param	string		$timer: total (for all the pages), page (for each page)
	 * @param	string		$time_type: time-value in minutes or seconds?
	 *
	 * @return	content to be displayed
	 */	
	function getTimer($time, $timer = 'total', $time_type = 'minutes'){
		$content = '';
		$markerArray = array();
		
		//check the timer-type
		if ($time_type == 'minutes') $seconds = ceil($time * 60);
		else $seconds = ceil($time);

		//if the start-timer is set and different to the actual time
		//check if a timer-tsmp is set
		$chk_time = time();
		if ($timer == 'pages'){
			//Problem with time/page: with refresh the user is able to reset the page
			$diff = $chk_time - $this->piVars['page_tstamp'][$this->piVars['page']];
			$secs = ceil($seconds - $diff);
		} else {
			$diff = $chk_time - $GLOBALS['TSFE']->fe_user->getKey('ses', 'kequestionnaire_start_tstamp');
			$secs = ceil($seconds - $diff);			
		}
		
		$markerArray['###MINS###'] = floor($secs/60);
		$markerArray['###SECS###'] = $secs%60;
		$markerArray['###TIMER_BASE###'] = $secs;
		$markerArray['###PI###'] = $this->prefixId;
			
		if ($timer == 'total') {
			$markerArray['###TEXT###'] = $this->pi_getLL('timer_text_total');
		} elseif ($timer == 'pages') {
			$markerArray['###TEXT###'] = $this->pi_getLL('timer_text_pages');
		}
			
		$content = $this->renderContent('###TIMER###',$markerArray);
		
		return $content;
	}

	/**
	 * checkAuthCode(): Check if the given Auth-Code is correct
	 *
	 * @return	false or true
	 */	
	function checkAuthCode(){
		$content = false;

		//if there is an auth_code in the piVars
		if ($this->piVars['auth_code'] != ''){
			//uses fullQuoteString for SQLInjection and X-Site Scripting prevention
			$where = 'pid='.$this->pid.' AND authcode='.$GLOBALS['TYPO3_DB']->fullQuoteStr($this->piVars['auth_code'],'tx_kequestionnaire_authcodes');
			$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*','tx_kequestionnaire_authcodes',$where);
			//t3lib_div::devLog('authCode res', $this->prefixId, 0, array($GLOBALS['TYPO3_DB']->SELECTquery('*','tx_kequestionnaire_authcodes',$where)));
			if ($res){
				$row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
				if (is_array($row)) {
					$content = true;
					if ($row['feuser']){
						if ($row['feuser'] != $this->user_id){
							$content = false;
							$this->no_authcodeKey = 'no_authcode_falseuser';
						}
					}
					$this->authCode = $row['authcode'];
				}
				t3lib_div::devLog('authCode', $this->prefixId, 0, $row);
			}
		}

		return $content;
	}

	/**
	 * getAuthCodeId(): Get the Id of the AuthCode Dataset
	 * The AutCode-Dataset is the bridge between user/authcode and resultset.
	 * If the acces is fe_user you'll need and authcode-Dataset too
	 *
	 * @return	auth Code id
	 */	
	function getAuthCodeId(){
		$authCode_id = -1;

		//t3lib_div::devLog('getAuthCodeId '.$this->authCode, $this->prefixId, 0, $this->ffdata);
		$where = '1=2';
		//due to the access, create the where clause
		switch ($this->ffdata['access']){
			case 'FREE':
				return 0;
				break;
			case 'FE_USERS':
				if ($this->user_id) $where = 'feuser='.$this->user_id;
				break;
			case 'AUTH_CODE':
				if ($this->authCode) $where = 'authcode="'.$this->authCode.'"';
				break;
		}
		$where .= ' AND pid='.$this->pid;
		$where .= $this->cObj->enableFields('tx_kequestionnaire_authcodes');
		
		$res_authCode = $GLOBALS['TYPO3_DB']->exec_SELECTquery('uid','tx_kequestionnaire_authcodes',$where,'',$orderBy);
		if ($res_authCode){
			$row_authCode = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res_authCode);
			$authCode_id = $row_authCode['uid'];
			//if there is no auth-code-id and the acces is fe_user, create the bridge
			if ($authCode_id == 0 AND $this->ffdata['access'] == 'FE_USERS'){
				$saveFields = array();
				$saveFields['tstamp'] = mktime();
				$saveFields['crdate'] = '';
				$saveFields['cruser_id'] = $GLOBALS['TSFE']->fe_user->user['uid'];
				$saveFields['qpid'] = $this->pid;
				$saveFields['pid'] = $this->pid;
				$saveFields['feuser'] = $this->user_id;

				$GLOBALS['TYPO3_DB']->exec_INSERTquery('tx_kequestionnaire_authcodes',$saveFields);
				$authCode_id = $GLOBALS['TYPO3_DB']->sql_insert_id();
			} else {
				//Hook to get another access method
				if (is_array($GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][$this->extKey]['pi1_getAuthCodeId'])){
					foreach($GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][$this->extKey]['pi1_getAuthCodeId'] as $_classRef){
						$_procObj = & t3lib_div::getUserObj($_classRef);
						$authCode_id = $_procObj->pi1_getAuthCodeId($this);
					}
				}
			}
		}

		return $authCode_id;
	}

	/**
	 * checkResults(): Check if the user or authCode already created a result
	 *
	 * @return	array of informtion about the existing results for this user/authcode
	 */
	function checkResults(){
		$content = array();
		$results = array();

		//get the authCodeId
		$authCodeId = $this->getAuthCodeId();
		//and create the where clause
		$where = 'auth='.$authCodeId;
		$where .= ' AND finished_tstamp = 0';
		$where .= ' AND deleted = 0';
		$where .= ' AND pid = '.$this->pid;
		$orderBy = 'start_tstamp DESC,tstamp DESC';
		$res_results = $GLOBALS['TYPO3_DB']->exec_SELECTquery('uid,finished_tstamp','tx_kequestionnaire_results',$where,'',$orderBy,1);
		if ($res_results){
			$results = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res_results);
			$content['last_result'] = $results['uid'];
			//if there are existing results the resultset is not new
			$this->new = false;
		}
		$where = 'auth='.$authCodeId;
		$where .= ' AND finished_tstamp != 0';
		$where .= ' AND pid = '.$this->pid;
		$where .= ' AND deleted = 0';
		$res_results = $GLOBALS['TYPO3_DB']->exec_SELECTquery('count(uid) as counter','tx_kequestionnaire_results',$where);
		//get the count of the results to be able to check if the user has already used all his possible accesses to this questionnaire
		if ($res_results){
			$counter = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res_results);
			$content['finished_count'] = $counter['counter'];
		}

		return $content;
	}

	/**
	 * getResults(): Get the last results of the user or authCode
	 *
	 * @return	void
	 */
	function getResults($result_id, $makeHistory = false){
		if (intval($result_id) == 0) return false;
		$where = 'uid='.$result_id;
		
		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*','tx_kequestionnaire_results',$where);
		//if there is a result, edit the old one
		if ($res){
			$row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
			if ($row['xmldata'] != ''){
				######################################
				//encoding-block for non-utf-8-DBs
				$encoding = "UTF-8";
				$temp_array = '';
				if ( true === mb_check_encoding ($row['xmldata'], $encoding ) ){
					$temp_array = t3lib_div::xml2array($row['xmldata']);
					if (count($temp_array) == 1) $temp_array = t3lib_div::xml2array(utf8_encode($row['xmldata']));
				} else {
					$temp_array = t3lib_div::xml2array(utf8_encode($row['xmldata']));
				}
				#########################################
				$this->saveArray = $temp_array;
				$this->piVars['result_id'] = $row['uid'];
				//Hook to manipulate the loaded Array
				if (is_array($GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][$this->extKey]['pi1_getResultsSaveArray'])){
					foreach($GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][$this->extKey]['pi1_getResultsSaveArray'] as $_classRef){
						$_procObj = & t3lib_div::getUserObj($_classRef);
						$this->saveArray = $_procObj->pi1_getResultsSaveArray($this);
					}
				}
				
				//update the resultset to set the timestamp of the last access
				$saveFields = array();
				$saveFields['last_tstamp'] = mktime();
				$where = 'uid='.$row['uid'];
				$GLOBALS['TYPO3_DB']->exec_UPDATEquery('tx_kequestionnaire_results',$where,$saveFields);

				//create the history-Dataset if needed
				if ($makeHistory){
					$saveFields = array();
					$saveFields['xmldata'] = $row['xmldata'];
					$saveFields['result_id'] = $result_id;
					$saveFields['pid'] = $row['pid'];
					$saveFields['history_time'] = mktime();
					$GLOBALS['TYPO3_DB']->exec_INSERTquery('tx_kequestionnaire_history',$saveFields);
				}
			}
		//if there is no result already existing, create a new one
		} else {
			//t3lib_div::devLog('getResults insert', $this->prefixId, 0, '');
			$saveFields = array();
			$saveFields['pid'] = $this->pid;
			$saveFields['tstamp'] = mktime();
			//removed due to data-security-law in germany
			/*$saveFields['ip'] = $_SERVER['REMOTE_ADDR'];*/
			$saveFields['auth'] = $this->getAuthCodeId();
			$saveFields['crdate'] = mktime();
			$saveFields['cruser_id'] = $GLOBALS['TSFE']->fe_user->user['uid'];
			$saveFields['start_tstamp'] = mktime();
			$saveFields['last_tstamp'] = mktime();
			$GLOBALS['TYPO3_DB']->exec_INSERTquery('tx_kequestionnaire_results',$saveFields);
			$this->piVars['result_id'] = $GLOBALS['TYPO3_DB']->sql_insert_id();
		}

		//Only if you din't want a pdf rendered and the save array is filled
		if (is_array($this->saveArray) AND !($this->piVars['pdf'])){
			foreach ($this->saveArray as $idy => $values){
				//check the last answered question, so you can direct the user to the last answered question
				$this->getLastAnsweredId($idy,$values);
				if (!$this->piVars[$idy]){
					//fill all variables for the last question
					$this->getQuestionTypeRender($this->questionsByID[$idy]);
				}
			}
		}
	}

	/**
	 * getLastAnsweredId(): Get the last answered question. Will set the actual question as last answered question
	 * 			if it is in the answer-array
	 *
	 * @param 	int	$idy: id of the question
	 * @param	array	$values: array of parameters from the answer-resultset for this question
	 *
	 * @return	void
	 */
	function getLastAnsweredId($idy, $values){
		//t3lib_div::devLog('getLastAnsweredId '.$idy, $this->prefixId, 0, $values);
		switch($values['type']){
			case 'open': if ($values['answer']) $this->lastAnswered = $idy;
				break;
			case 'closed': if (is_array($values['answer'])) $this->lastAnswered = $idy;
				break;
			case 'matrix': switch ($values['subtype']){
						case 'input':
							break;
						default: if (is_array($values['answer'])) $this->lastAnswered = $idy;
							break;
					}
				break;
			default: if (is_array($values['answer'])) $this->lastAnswered = $idy;
				break;
		}
	}

	/**
	 * clearResults(): Clear the last results of the user or authCode
	 *
	 * @param	int	$result_id: Id of result to be cleared (when the user wants to restart the questionnaire)
	 */
	function clearResults($result_id){
		//TODO: HistorienFunktion aktivieren
		$saveFields = array();
		$saveFields['last_tstamp'] = mktime();
		$saveFields['xmldata'] = '';
		$where = 'uid='.$result_id;
		$GLOBALS['TYPO3_DB']->exec_UPDATEquery('tx_kequestionnaire_results',$where,$saveFields);
	}

	/**
	 * setResults(): Set the actual results of the user or authCode
	 *
	 * @param	int 	$result_id: id of the actual resultset
	 */
	function setResults($result_id){
		//t3lib_div::devLog('to be saved saveArray setResults', $this->prefixId, 0, $this->saveArray);
		$saveFields = array();
		$saveFields['pid'] = $this->pid;
		$saveFields['tstamp'] = mktime();
		$saveFields['sys_language_uid'] = $GLOBALS['TSFE']->sys_language_uid;
		//Hook to manipulate the saved Array
		if (is_array($GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][$this->extKey]['pi1_setResultsSaveArray'])){
			foreach($GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][$this->extKey]['pi1_setResultsSaveArray'] as $_classRef){
				$_procObj = & t3lib_div::getUserObj($_classRef);
				$this->saveArray = $_procObj->pi1_setResultsSaveArray($this);
			}
		}
		//Hook to manipulate the saveFields
		if (is_array($GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][$this->extKey]['pi1_setResultsSaveFields'])){
			foreach($GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][$this->extKey]['pi1_setResultsSaveFields'] as $_classRef){
				$_procObj = & t3lib_div::getUserObj($_classRef);
				$saveFields = $_procObj->pi1_setResultsSaveFields($this,$saveFields);
			}
		}
		
		if (is_array($this->saveArray)) $saveFields['xmldata'] = t3lib_div::array2xml($this->saveArray);

		//when the questionnaire is finished and all questions are answered
		if ($this->finished){
			$saveFields['finished_tstamp'] = mktime();
		}

		//if there exists an result, make an update
		if ($result_id){
			$where = 'uid='.$result_id;
			$GLOBALS['TYPO3_DB']->exec_UPDATEquery('tx_kequestionnaire_results',$where,$saveFields);
		//else create a new database entry
		} else {
			$saveFields['auth'] = $this->getAuthCodeId();
			$saveFields['crdate'] = mktime();
			$saveFields['cruser_id'] = $GLOBALS['TSFE']->fe_user->user['uid'];
			if ($this->piVars['start_tstamp']) $saveFields['start_tstamp'] = $this->piVars['start_tstamp'];
			else $saveFields['start_tstamp'] = mktime();
			$saveFields['last_tstamp'] = mktime();
			$GLOBALS['TYPO3_DB']->exec_INSERTquery('tx_kequestionnaire_results',$saveFields);
			$result_id = $GLOBALS['TYPO3_DB']->sql_insert_id();
		}

		return $result_id;
	}

	/**
	 * getPages(): Check if there should be more than one page and return the page needed
	 *
	 * @param	string	$form_pre_add: stuff to be included into the rendered content before the form
	 * @param	string 	$form_post_add: stuff to be included into the rendered content after the form
	 *
	 * @return 	string	content to be rendered
	 */
	function getPages($form_pre_add = '',$form_post_add = ''){
		$content = '';
		$page_nr = $this->piVars['page'];
		if ($this->ffdata['description'] == '' AND $page_nr == 0) $page_nr = 1;

		if ($this->ffdata['linear'] == 1) $this->ffdata['render_type'] = 'QUESTIONS';

		$page_count = $this->pageCount;
		//if the timer is set and the type is "total" the questionnaire is finnished as soon as the timer reaches 0
		if (isset($this->piVars['timer']) AND $this->ffdata['timer_type'] == 'TOTAL' AND $this->piVars['timer'] <= 0) $page_nr = $page_count +1;
		
		//if there is a description text for the questionnaire, make a first page
		//to show it before the questionnaire starts
		if ($this->ffdata['description'] != '' AND $page_nr == 0){
			$content = $this->renderFirstPage();
		//else if the last page of the questionnaire is reached
		} elseif ($page_nr > $page_count){
			$content = $this->renderLastPage();
		//else show the question-pages
		} else {
			$content = $this->renderPage($page_nr,$page_count,$form_pre_add,$form_post_add);
		}
		return $content;
	}

	/**
	 * getPageNr(): gets the Page Nr (needed for linear questionnaires)
	 *
	 * @return 	int	Number of the actual page
	 */
	function getPageNr(){
		//basicly the pageNr is the one given with the piVars
		$pageNr = $this->piVars['page'];
		
		//if the questionnaire is marked as linear (one question/page and no turning back)
		if ($this->ffdata['linear'] == 1){
			foreach ($this->questions as $nr => $question){
				if (is_array($this->saveArray[$question['uid']]) AND $this->saveArray[$question['uid']]['answer'] != ''){
					$pageNr = $nr+2;
				}
			}
		//when the user returns, he shoud be moved to his last answered question
		//but only if he is at the first page (intro-page) of the questionnaire
		} elseif ($this->lastAnswered > 0 AND $pageNr == 0){
			//get the amount of questions in the questionnaire
			//if the pages are calculated without the dependant questions
			if ($this->ffdata['render_count_withoutdependant'] == 1) {
				$amount = $this->questionCount['no_dependants'];
			//or if the pages are calculated with counting the bind questions
			} elseif ($this->ffdata['render_count_withblind'] == 1) {
				$amount = $this->questionCount['total'];
			//else use the normal question count
			} else {
				$amount = $this->questionCount['only_questions'];
			}
			//calculate the amount of pages
			$pagecount = $this->getPageCount();
			switch ($this->ffdata['render_type']){
				//if based on questions
				//$qpp: Questions Per Page
				case 'QUESTIONS':
					$qpp = $this->ffdata['render_count'];
					break;
				//if based on pages, calculate the pages
				case 'PAGES':
					$qpp = ceil($amount / $pagecount);
					break;
			}
			$c_page = 1;
			$c_q = 0;
			if (is_array($this->questions)){
				foreach($this->questions as $q_nr => $q_question){
					if ($q_question['is_dependant'] == 1 AND $this->ffdata['render_count_withoutdependant'] == 1){
					} else {
						$c_q ++;
					}

					if ($c_q == $qpp){
						$c_q = 0;
						$c_page ++;
					}
					if ($q_question['uid'] == $this->lastAnswered) {
						$pageNr = $c_page;
						break;
					}
				}
			}
			//t3lib_div::devLog('getPageNr lastAnswered '.$this->lastAnswered, 'test', 0, array('amount' => $amount,'pages'=>$pagecount, 'p Nr'=>$pageNr, 'qpp' =>$qpp, 'page-nr'=>$this->piVars['page'], 'q_nr'=>$q_nr));
		}
		//when there should be a timer, set the session-keys for the timer
		if ($this->ffdata['timer_type'] != 'FREE'){
			// If page is not given, our sessions must be deleted.
			if(!$pageNr) {
				$GLOBALS['TSFE']->fe_user->setKey('ses', 'kequestionnaire_page', 0);
				$GLOBALS['TSFE']->fe_user->setKey('ses', 'kequestionnaire_start_tstamp', time());
			} else {
				// If page is given we have to check if there are some modifications made in url
				if($GLOBALS['TSFE']->fe_user->getKey('ses', 'kequestionnaire_page') && $GLOBALS['TSFE']->fe_user->getKey('ses', 'kequestionnaire_page') > $pageNr) {
					$pageNr = $GLOBALS['TSFE']->fe_user->getKey('ses', 'kequestionnaire_page');
				}
				$GLOBALS['TSFE']->fe_user->setKey('ses', 'kequestionnaire_page', $pageNr);
			}
		}
		//set the piVars with the correct pageNr
		$this->piVars['page']=$pageNr;
		return $pageNr;
	}

	/**
	 * getPageCount(): gets the Page Count
	 *
	 * @return	int 	amount of pages
	 */
	function getPageCount(){
		//due to the flexform instructions the amount of questions to be calculated with is taken
		if ($this->ffdata['render_count_withoutdependant'] == 1) {
			$amount = $this->questionCount['no_dependants'];
		} elseif ($this->ffdata['render_count_withblind'] == 1) {
			$amount = $this->questionCount['total'];
		} else {
			$amount = $this->questionCount['only_questions'];
		}
		//not shown dependants never count for pagecount
		$amount = $amount - $this->questionCount['notshown_dependants'];

		//calculate the pagecount on the rendertype chosen in the flexforms
		switch ($this->ffdata['render_type']){
			//all on one page
			case 'ALL':
				$page_count = 1;
				break;
			//based on the amount of questions
			case 'QUESTIONS':
				//if linear one question per page
				if ($this->ffdata['linear'] == 1){
					$page_count = $amount;
				//else calculate
				} else {
					$page_count = ceil($amount / $this->ffdata['render_count']);
				}
				break;
			//based on the amount of needed pages
			case 'PAGES':
				$page_count = $this->ffdata['render_count'];
				break;
		}
		//t3lib_div::devLog('getPageCount', $this->prefixId, 0, array('render_type'=>$this->ffdata['render_type'],'amount'=>$amount,'page_count'=>$page_count));

		return $page_count;
	}

	/**
	 * renderPage(): renders a Single Page for the Questionnaire
	 *
	 * @param	int	$page_nr: Number of the rendered page
	 * @param	int	$page_count: Total amount of pages
	 * @param	string 	$form_pre_add: stuff to be added to the template before the form
	 * @param	string	$form_post_add: stuff to be added to the template after the form
	 *
	 * @return	string	rendered page to be shown
	 */
	function renderPage($page_nr,$page_count,$form_pre_add='',$form_post_add=''){
		$questions = '';
		$markerArray = array();
		if ($this->user_id){
			foreach ($this->userMarker as $marker => $value){
				$markerArray[$marker] = $value;
			}
		}
		//if all ins rendered on one page
		if ($page_nr == $page_count AND $page_count == 1){
			$markerArray['###ACT_PAGE###'] = '';
			$markerArray['###TOTAL_PAGES###'] = '';
			$markerArray['###COUNTER_PARTER###'] = '';
			$markerArray['###PAGE_COUNTER###'] = '';
		} else {
			$markerArray['###ACT_PAGE###'] = $page_nr;
			$markerArray['###TOTAL_PAGES###'] = $page_count;
			$markerArray['###COUNTER_PARTER###'] = $this->pi_getLL('counter_parter');
			$percent = $page_nr/$page_count *100;
			$markerArray['###COUNTERBAR_WIDTH###'] = $percent.'%';
			$markerArray['###PAGE_COUNTER###'] = $this->renderContent('###PAGECOUNTER###',$markerArray);
		}

		$markerArray['###FORM_PRE_ADD###'] = $form_pre_add;
		$markerArray['###FORM_POST_ADD###'] = $form_post_add;
		
		//get the questions shown on this page
		$page_questions = $this->getQuestionsOfPage($page_nr,$page_count);
		//t3lib_div::debug($page_questions);
		$shown = $this->shown;
		foreach ($page_questions as $quest){
			//render reach question
			$questions .= $this->getQuestionTypeRender($quest);
		}
		
		//navigation for the questionnaire (first page, last page, next page, last page)
		$nav_markerArray = array();		
		if (($page_nr - 1) > 0 AND $this->ffdata['linear'] != 1 AND $this->ffdata['type'] != 'QUIZ'){
			$nav_markerArray['###HREF###'] = 'javascript:';
			$nav_markerArray['###HREF###'] .= 'document.ke_questionnaire.action=\''.htmlspecialchars($this->pi_getPageLink($GLOBALS['TSFE']->id,'',array($this->prefixId.'[page]'=>($page_nr-1)))).'\';';
			$nav_markerArray['###HREF###'] .= 'document.ke_questionnaire.submit()';
			$nav_markerArray['###TEXT###'] = htmlspecialchars($this->pi_getLL('to_last'));
			$nav_markerArray['###PI###'] = $this->prefixId;
			$nav_markerArray['###NAME###'] = 'last';
			$nav_markerArray['###LAST###'] = $this->renderContent('###NAV_BUTTON###',$nav_markerArray);
		} else {
			$nav_markerArray['###LAST###'] = $this->renderContent('###NAV_BUTTON_EMPTY###',array('1' => 1));
		}
		$nav_markerArray['###HREF###'] = 'javascript:document.ke_questionnaire.submit()';
		//when the rendered page is the past page then submit the questionnaire
		if (($this->ffdata['end_text'] == '' AND ($page_nr == $page_count)) OR ($page_nr == $page_count)){
			$nav_markerArray['###TEXT###'] = htmlspecialchars($this->pi_getLL('submit'));
		//else goto the next page of the questionnaire
		} else {
			$nav_markerArray['###TEXT###'] = htmlspecialchars($this->pi_getLL('to_next'));
		}
		//additional js for the questionnaire rendering
		$add_js = '
				<script type="text/javascript">
					window.history.forward();
				</script>';	
		if ($add_js != '') $this->addHeaderData[] = $add_js;

		$nav_markerArray['###PI###'] = $this->prefixId;
		$nav_markerArray['###NAME###'] = 'next';
		$nav_markerArray['###NEXT###'] = $this->renderContent('###NAV_BUTTON###',$nav_markerArray);
		
		//no "next" navigation if current type of question = "sbm_button"
		if($this->ffdata['render_type'] == 'QUESTIONS' && $this->ffdata['render_count'] == 1) {
			foreach ($this->allQuestions as $nr => $question){
				if($shown[0] == $question['uid']) {
					if($question['closed_type'] == 'sbm_button') {
						$nav_markerArray['###NEXT###'] = '';
					}
				}
			}
		}
		$markerArray['###FORM_ACTION###'] = htmlspecialchars($this->pi_getPageLink($GLOBALS['TSFE']->id,'',array($this->prefixId.'[page]'=>($page_nr+1),$this->prefixId.'[next]'=>(1))));
		
		$markerArray['###NAV###'] = $this->renderContent('###NAVIGATION###',$nav_markerArray);
		$markerArray['###QUESTIONS###'] = $questions;

		$markerArray['###HIDDEN_FIELDS###'] = $this->renderHiddenFields($shown);
		$markerArray['###CAPTCHA###'] = '';
		//check if a captcha should be shown and render it if it's the last page
		//chaptcha is only shown on the last page of the questionnaire
		if ($this->ffdata['show_captcha'] == 1 AND $page_count == $page_nr){
			if (is_object($this->freeCap)){
				$c_markerArray = array();
				$c_markerArray = $this->freeCap->makeCaptcha();
				$c_markerArray['###PI###'] = $this->prefixId;
				$markerArray['###CAPTCHA###'] = $this->renderContent('###CAPTCHA_BOX###',$c_markerArray);
			} else {
				$markerArray['###CAPTCHA###'] = 'install sr_freecap';
			}
		}
		
		//embedding the timer if needed
		$markerArray['###SHOW_TIMER###'] = '';
		switch ($this->ffdata['timer_type']){
			case 'TOTAL':
					if ($this->ffdata['max_time'] > 0){
						$markerArray['###SHOW_TIMER###'] = $this->getTimer($this->ffdata['max_time'],'total');
					}
				break;
			case 'PAGES':
					if ($this->ffdata['max_time'] > 0){
						$markerArray['###SHOW_TIMER###'] = $this->getTimer($this->ffdata['max_time'],'pages');
					}
				break;
		}
		
		$markerArray['###JS###'] = '';
		if ($this->pageJS != ''){
			$markerArray['###JS###'] = '<script type="text/javascript">'.$this->pageJS.'</script>';
		}


		$content = $this->renderContent('###PAGE###',$markerArray);
		
		$content = $this->renderMarker($content, $this->userMarker);

		return $content;
	}
	
	/**
	 * getQuestionsOfPage(): get the questions for the actual page to be rendered
	 *
	 * @param	int	$page_nr: Number of the page
	 * @param	int	$page_count: Total amount of pages
	 *
	 * @return	array	Questions to be rendered on the page
	 */
	function getQuestionsOfPage ($page_nr, $page_count){
		$questions = array();
		
		if ($this->ffdata['render_count_withoutdependant'] == 1) {
			$amount = $this->questionCount['no_dependants'];
		} elseif ($this->ffdata['render_count_withblind'] == 1) {
			$amount = $this->questionCount['total'];
		} else {
			$amount = $this->questionCount['only_questions'];
		}
		//not shown dependants never count for pagecount
		$amount = $amount - $this->questionCount['notshown_dependants'];
		if ($this->ffdata['linear'] == 1) $this->ffdata['render_type'] = 'QUESTIONS';
		switch ($this->ffdata['render_type']){
			case 'ALL':
				$qpp = $this->questionCount['total'];
				break;
			case 'QUESTIONS':
				if ($this->ffdata['linear'] == 1){
					$qpp = 1;
				} else {
					$qpp = $this->ffdata['render_count'];
				}
				break;
			case 'PAGES':
				$qpp = ceil($amount / $page_count);
				break;
		}
		//compute the startquestion
		$start = $qpp * ($page_nr-1);
		//and the endquestion
		
		//t3lib_div::debug($this->questionCount);
		
		//get the shown questions
		$shown = array();
		$q_count = 0;
		$p_count = 0;
		$counter = 0;
		foreach ($this->allQuestions as $nr => $question){
			$check = '.';
			//if there are only one question/page and the question should not be shown, move on
			if ($qpp == 1 AND $question['no_show'] == 1){
				$counter ++;
			} else {
				if ($question['type'] == 'refusal'){
					if ($p_count == ($page_nr - 1)){
						$questions[] = $question;
						$shown[] = $question['uid'];
					}
				} elseif ($question['is_dependant'] == 1 AND !in_array($nr,$shown)){
					//t3lib_div::devLog('SHOWN '.$question['uid'], $this->prefixId, 0, $shown);
					//$questions .= 'test';
					if ($p_count == ($page_nr - 1)){
						$questions[] = $question;
						$shown[] = $question['uid'];
					}
					$counter ++;
				} elseif ($question['is_dependant_ns'] !=1 AND $check != '') {
					if ($q_count == $qpp){
						$p_count ++;
						$q_count = 0;
					}
					if ($p_count == $page_nr AND $question['is_dependant'] == 0 AND $question['type'] != 'blind') {
						break;
					}
					if ($p_count == ($page_nr - 1)){
						if ($question['is_dependant_ns'] == 1){
							$check_obj = $this->getQuestionTypeObject($question);
							//t3lib_div::devLog('shown '.$nr, $this->prefixId, 0, array($shown,$question));
							if ($check->checkDependancies()) {
								$check = '+';
							} else {
								$check = '';
							}
						}
						if ($question['is_dependant'] == 1 AND !in_array($question['uid'],$shown)){
							//$questions .= '<br>counter:'.$counter .'<br>q_count:'.$q_count.'<br>p_count:'.$p_count;
							//t3lib_div::devLog('SHOWN '.$question['uid'], $this->prefixId, 0, $shown);
							$questions[] = $question;
							$shown[] = $question['uid'];
						} elseif ($question['type'] != 'blind' AND (!in_array($question['uid'],$shown)) AND $question['is_dependant'] != 1 AND $question['is_dependant_ns'] != 1) {
							//$questions .= '<br>counter:'.$counter .'<br>q_count:'.$q_count.'<br>p_count:'.$p_count;
							//t3lib_div::devLog('shown '.$nr, $this->prefixId, 0, $question);
							$questions[] = $question;
							$shown[] = $question['uid'];
						} elseif (!in_array($question['uid'],$shown) AND $question['is_dependant_ns'] != 1){
							//$questions .= '<br>counter:'.$counter .'<br>q_count:'.$q_count.'<br>p_count:'.$p_count;
							$questions[] = $question;
							$shown[] = $question['uid'];
						} elseif ($question['is_dependant_ns'] == 1 AND $check != '') {
							//$questions .= '<br>counter:'.$counter .'<br>q_count:'.$q_count.'<br>p_count:'.$p_count;
							$questions[] = $question;
							$shown[] = $question['uid'];
						} else {
							//$questions .= '<br>counter:'.$counter .'<br>q_count:'.$q_count.'<br>p_count:'.$p_count;
							//t3lib_div::devLog('check', $this->prefixId, 0, array($check));
						}
					} elseif ($p_count == $page_nr AND ($question['is_dependant'] == 1 OR $question['type'] == 'blind') AND !in_array($question['uid'],$shown)){
						$questions[] = $question;
						$shown[] = $question['uid'];
					}
					//if ($p_count == 4) t3lib_div::devLog('shown '.$question['uid'].'/'.$p_count, $this->prefixId, 0, array($shown,$question));
	
					if ($question['type'] != 'blind'){
						if ($question['is_dependant'] == 0) $q_count ++;
						$counter ++;
					} elseif ($this->ffdata['render_count_withblind'] == 1) {
						$q_count ++;
						//t3lib_div::devLog('q'.$nr, $this->prefixId, 0, array($questions));
					}
				}
			}
		}
		$this->shown = $shown;
		//t3lib_div::debug($questions,'qustios');
		return $questions;
	}

	/**
	 * checkValidation(): check Validation of the current page
	 *
	 * @return	boolean		validated or not
	 */
	function checkValidation(){
		$page_nr = $this->getPageNr() -1;
		$validation = true;
		
		$val_questions = $this->getQuestionsOfPage($page_nr,$this->pageCount);
		//t3lib_div::devLog('val_queston', $this->prefixId, 0, $val_questions);
		//t3lib_div::devLog('PIVars', $this->prefixId, 0, $this->piVars);
		foreach ($val_questions as $question){
			$question_obj = $this->getQuestionTypeObject($question);
			if (is_object($question_obj)) {
				//t3lib_div::devLog('question '.$question['type'], $this->prefixId, 0, $question);
				$question_obj->validate();
			}
			if ($question_obj->error == 1) {
				$this->validated = true;
				$validation = false;
				$focus_id = $question['uid'];
				if (count($question_obj->errorFields) > 0) $focus_id .= '_'. $question_obj->errorFields[0];
				$this->pageJS = 'document.getElementById("keq_'.$focus_id.'").focus();';
			}
		}		
		if ($page_nr == $this->pageCount){
			if ($this->ffdata['show_captcha'] == 1){
				if (is_object($this->freeCap) && !$this->freeCap->checkWord($this->piVars['captcha_response'])) {
					$validation = false;
					$this->validated = true;
				}
			}
		}
		return $validation;
	}

	/**
	 * renderHiddenFields(): renders the PI-Var hidden fields
	 *
	 * @param	array	$shown: The questions shown on the page
	 */
	function renderHiddenFields($shown=array()){
		$content = '';
		$markerArray = array();
		$markerArray['###ID###'] = '';
		//t3lib_div::devLog('renderHiddenFields', $this->prefixId, 0, $shown);

		if (is_array($this->piVars)){
			foreach ($this->piVars as $name => $arry){
				$markerArray['###ID###'] = $name;
				//t3lib_div::devLog('renderHiddenFields name '.$name, $this->prefixId, 0, array('arry'=>$arry));
				if (is_array($arry) AND $name != 'page' AND !in_array($name,$shown)){
					$this->getQuestionTypeRender($this->questionsByID[$name]);
					foreach ($arry as $add => $value){
						if (is_array($value)){
							foreach ($value as $nr => $subvalue){
								if (is_array($subvalue)){
									foreach ($subvalue as $subnr => $subsubvalue){
										if (is_array($subsubvalue)){
											foreach ($subsubvalue as $subsubnr => $subsubsubvalue){
												if (!is_array($subsubsubvalue)) $subsubsubvalue = str_replace('"','&quot;',$subsubsubvalue);
												$markerArray['###NAME###'] = $this->prefixId.'['.$name.']['.$add.']['.$nr.']['.$subnr.']['.$subsubnr.']';
												$markerArray['###VALUE###'] = $subsubsubvalue;
												$content .= $this->renderContent('###HIDDEN_FIELD###',$markerArray);
											}
										} else {
											$subsubvalue = str_replace('"','&quot;',$subsubvalue);
											//else t3lib_div::devLog('renderHiddenFields name '.$name, $this->prefixId, 0, array('subsubvalue'=>$subsubvalue));
											$markerArray['###NAME###'] = $this->prefixId.'['.$name.']['.$add.']['.$nr.']['.$subnr.']';
											$markerArray['###VALUE###'] = $subsubvalue;
											$content .= $this->renderContent('###HIDDEN_FIELD###',$markerArray);
										}

									}
								} else {
									$subvalue = str_replace('"','&quot;',$subvalue);
									$markerArray['###NAME###'] = $this->prefixId.'['.$name.']['.$add.']'.'['.$nr.']';
									$markerArray['###VALUE###'] = $subvalue;
									$content .= $this->renderContent('###HIDDEN_FIELD###',$markerArray);
								}
							}
						} else {
							$value = str_replace('"','&quot;',$value);
							$markerArray['###NAME###'] = $this->prefixId.'['.$name.']['.$add.']';
							$markerArray['###VALUE###'] = $value;
							$content .= $this->renderContent('###HIDDEN_FIELD###',$markerArray);
						}

					}
				} elseif (($name == 'result_id' OR $name == 'auth_code')){// OR ($name == 'page' AND $this->lastAnswered > 0)) {
					$markerArray['###NAME###'] = $this->prefixId.'['.$name.']';
					$markerArray['###VALUE###'] = $arry;
					$content .= $this->renderContent('###HIDDEN_FIELD###',$markerArray);
				}
			}
			$timestamp_start = 1;
			if ($this->conf['timestamp_startpage']) $timestamp_start = $this->conf['timestamp_startpage'];
			//Inserted by Stefan Froemken
			if($timestamp_start > 0 AND $this->piVars['page'] >= $timestamp_start) {
				if(!$GLOBALS['TSFE']->fe_user->getKey('ses', 'kequestionnaire_start_tstamp')) $started = mktime();
				else $started = $GLOBALS['TSFE']->fe_user->getKey('ses', 'kequestionnaire_start_tstamp');
				$GLOBALS['TSFE']->fe_user->setKey('ses', 'kequestionnaire_start_tstamp', $started);
 			}
			if ($this->ffdata['timer_type'] AND $this->piVars['page'] > 0){
				$started = mktime();
				$markerArray['###ID###'] = 'page_tstamp';
				$markerArray['###NAME###'] = $this->prefixId.'[page_tstamp]['.$this->piVars['page'].']';
				$markerArray['###VALUE###'] = $started;
				$this->piVars['page_tstamp'][$this->piVars['page']] = $started;
				$content .= $this->renderContent('###HIDDEN_FIELD###',$markerArray);
			}
		}
		
		//Hook to add hidden fields
		if (is_array($GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][$this->extKey]['pi1_renderHiddenFields'])){
			foreach($GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][$this->extKey]['pi1_renderHiddenFields'] as $_classRef){
				$_procObj = & t3lib_div::getUserObj($_classRef);
				$content .= $_procObj->pi1_renderHiddenFields($this);
			}
		}
		
		//t3lib_div::devLog('renderHiddenFields', $this->prefixId, 0, array($timestamp_start,$content));
		
		return $content;
	}

	/**
	 * renderFirstPage(): renders the Start-Page for the Questionnaire
	 *
	 * @return	string	content to be rendered
	 */
	function renderFirstPage(){
		$content = '';
		$markerArray = array();
		if ($this->user_id){
			foreach ($this->userMarker as $marker => $value){
				$markerArray[$marker] = $value;
			}
		}
		
		$result_id = $this->setResults(0);
		$this->piVars['result_id'] = $result_id;

		$markerArray['###TEXT###'] = $this->pi_RTEcssText($this->ffdata['description']);
		//$markerArray['###NAV###'] = $this->pi_linkTP($this->pi_getLL('to_questionnaire'),array($this->prefixId.'[page]'=>1));

		$nav_markerArray = array();
		$nav_markerArray['###HREF###'] = 'javascript:';
		$nav_markerArray['###HREF###'] .= 'document.ke_questionnaire.action=\''.htmlspecialchars($this->pi_getPageLink($GLOBALS['TSFE']->id,'',array($this->prefixId.'[page]'=>1))).'\';';
		$nav_markerArray['###HREF###'] .= 'document.ke_questionnaire.submit()';
		$nav_markerArray['###TEXT###'] = $this->pi_getLL('to_questionnaire');
		$nav_markerArray['###PI###'] = $this->prefixId;
		$nav_markerArray['###NAME###'] = 'go';

		$markerArray['###NAV###'] = '<div class="keq_q_list_link">'.$this->renderContent('###NAV_BUTTON###',$nav_markerArray).'</div>';
		$markerArray['###FORM_ACTION###'] = htmlspecialchars($this->pi_getPageLink($GLOBALS['TSFE']->id,'',array($this->prefixId.'[page]'=>($page_nr+1))));
		$markerArray['###PDF###'] = '';

		$markerArray['###HIDDEN_FIELDS###'] = $this->renderHiddenFields();
		$markerArray['###EMAILONFINISH###'] = '';
		//t3lib_div::devLog('renderFirstPage', $this->prefixId, 0, $markerArray);
		//t3lib_div::debug($markerArray, $result_id);
		$content = $this->renderContent('###OTHER_PAGE###',$markerArray);
		$content = $this->renderMarker($content, $this->userMarker);

		return $content;
	}

	/**
	 * renderLastPage(): renders the End-Page for the Questionnaire
	 *
	 * @return	string	content to be rendered
	 */
	function renderLastPage(){
		//if it is a free-access questionnaire, the save array needs to be rendered:
		if ($this->ffdata['access'] == 'FREE') $this->renderHiddenFields();
		//when the user calls the end-page of the questionnaire,
		//he is finished with the current participation has ended
		if ($this->type != "CONSTANT") $this->finished = true;
		//save the Results when showing the last page, regardless of access-type
		$resultId = $this->setResults($this->piVars['result_id']);
		if (!$this->piVars['result_id']) $this->piVars['result_id'] = $resultId;
		//t3lib_div::devLog('renderLastPage '.$resultId, $this->prefixId, 0, array($this->saveArray));

		//if the mailing is active and set to direct, send the information mail
		if ($this->ffdata['mailing'] == 1 AND $this->ffdata['mail_turn'] == 'PROMPT'){
			$email_adresses = $this->ffdata['emails'];
			$mail_texts = array();
			$mail_texts['subject'] = $this->ffdata['inform_mail_subject'];
			$mail_texts['body'] = $this->ffdata['inform_mail_text'];
			$mail_texts['fromEmail'] = $this->ffdata['mail_sender'];
			$mail_texts['fromName'] = $this->ffdata['mail_from'];
			$this->sendMail($email_adresses,$mail_texts);
			
			$saveField = array();
			$saveField['mailsent_tstamp'] = mktime();
			$GLOBALS['TYPO3_DB']->exec_UPDATEquery('tx_kequestionnaire_results','uid='.$resultId,$saveField);
		}
		
		$content = '';
		$markerArray = array();
		if ($this->user_id){
			foreach ($this->userMarker as $marker => $value){
				$markerArray[$marker] = $value;
			}
		}

		$markerArray['###TEXT###'] = $this->pi_RTEcssText($this->ffdata['end_text']);
		if ($markerArray['###TEXT###'] == '') $markerArray['###TEXT###'] = $this->pi_getLL('standard_endtext');
		
		//PDF-Output
		$markerArray['###PDF###'] = '';
		if ($this->ffdata['pdf_type']){
			$pdf_types = explode(',',$this->ffdata['pdf_type']);
			$pdf_links = '';
			foreach ($pdf_types as $pdf_type){
				$temp_markerArray = array();
				$pdf_type = strtolower($pdf_type);
				$temp_markerArray['###TYPE###'] = $pdf_type;
				
				$add_params = array();
				$add_params[$this->prefixId.'[pdf]'] = 1;
				$add_params[$this->prefixId.'[p_id]'] = $this->piVars['result_id'];
				$add_params['no_cache'] = 1;
				switch ($pdf_type){
					case 'empty':
							$link_title = $this->pi_getLL('pdf_empty');
							$add_params[$this->prefixId.'[type]'] = 'empty';
						break;
					case 'filled':
							$link_title = $this->pi_getLL('pdf_filled');
							$add_params[$this->prefixId.'[type]'] = 'filled';
						break;
					case 'compare':
							$link_title = $this->pi_getLL('pdf_compare');
							$add_params[$this->prefixId.'[type]'] = 'compare';
						break;
					case 'outcomes':
							$link_title = $this->pi_getLL('pdf_outcomes');
							$add_params[$this->prefixId.'[type]'] = 'outcomes';
						break;
				}
				$pdf_link = $this->pi_linkToPage($link_title,
					$GLOBALS['TSFE']->id,
					'',
					$add_params
				);
				$temp_markerArray['###LINK###'] = $pdf_link;
				$pdf_links .= $this->renderContent('###PDF_LINK_LINE###',$temp_markerArray);
			}
			$markerArray['###PDF###'] = $pdf_links;	
		}
		
		//send result as e-mail after finishing
		if ($this->ffdata['send_finish_mail']){
			//t3lib_div::debug($this->ffdata);
			$emailLink = $this->pi_linkTP_keepPIvars_url(array('sendemail' => 1,'p_id' => $this->piVars['result_id']),0,1);
			
			$email_markerArray['###URL###'] = $emailLink;
			$email_markerArray['###INTROTEXT###'] = $this->pi_getLL('intro_email_send');
			$emailForm = $this->renderContent('###EMAILONFINISHFORM###',$email_markerArray);
			
			$markerArray['###EMAILONFINISH###'] = $emailForm;
		} else {
			$markerArray['###EMAILONFINISH###'] = '';	
		}
		
		
		$add_info = '';
		switch ($this->type){
			case 'QUIZ':
					if ($this->ffdata['user_reports'] == 1){
						$add_info = $this->getQuizReport();
					}
				break;
			case 'POINTS':
					$add_info = $this->getPointsReport();
				break;
		}
		$markerArray['###TEXT###'] .= $add_info;

		$markerArray['###NAV###'] = '';
		$markerArray['###HIDDEN_FIELDS###'] = '';
		
		//t3lib_div::debug($result_id,'pi1');
		//Hook to do something after the questionnaire is finished
		if (is_array($GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][$this->extKey]['pi1_renderLastPage'])){
			foreach($GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][$this->extKey]['pi1_renderLastPage'] as $_classRef){
				$_procObj = & t3lib_div::getUserObj($_classRef);
				$markerArray = $_procObj->pi1_renderLastPage($this,$resultId,$markerArray);
			}
		}
		
		//if finish page differs by answer, check this here
		if(intval($this->ffdata['redirect_on_finish_uid'] != 0)) {
			$res_answers = $GLOBALS['TYPO3_DB']->exec_SELECTquery('tx_kequestionnaire_answers.uid,tx_kequestionnaire_answers.finish_page_uid','tx_kequestionnaire_questions,tx_kequestionnaire_answers','tx_kequestionnaire_questions.uid = tx_kequestionnaire_answers.question_uid AND tx_kequestionnaire_questions.uid = '.$this->ffdata['redirect_on_finish_uid']);
			
			//get finish pages from answers
			while($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res_answers)) {
				$finishPids[$row['uid']] = $row['finish_page_uid'];
			}
			
			//set finish page by current answer
			$finishPage = $finishPids[$this->saveArray[$this->ffdata['redirect_on_finish_uid']]['answer']['options']];
			
			//if no finishing page given in answer, ignore and go on - else: redirect
			if(strlen($finishPage) && $finishPage > 0) {
				//if the conf Var is set, give the resultId to the link
				$fp_params = array();
				if ($this->conf['resultIdToFinishPage'] == 1){
					$fp_params[$this->prefixId]['resultId'] = $resultId;
				}
				$link = $this->pi_getPageLink($finishPage,'',$fp_params);
				if ($GLOBALS['TSFE']->config['config']['baseURL']) $link = $GLOBALS['TSFE']->config['config']['baseURL'].$link;
				header('Location:'.$link);
				//t3lib_div::devLog('renderLastPage', $this->prefixId, 0, array($fp_params,$link));
			}
		}
		
		//if the redirect page is set
		if ($this->ffdata['end_page']){
			$link = $this->pi_getPageLink($this->ffdata['end_page']);
			if ($GLOBALS['TSFE']->config['config']['baseURL']) $link = $GLOBALS['TSFE']->config['config']['baseURL'].$link;
			header('Location:'.$link);
		}
		
		$this->renderHiddenFields();
		$content = $this->renderContent('###OTHER_PAGE###',$markerArray);
		$content = $this->renderMarker($content, $this->userMarker);

		//t3lib_div::devLog('renderLastPage', $this->prefixId, 0, $markerArray);

		return $content;
	}
	
	/**
	 * getPointsReport(): get the Report for Points Questionnaire Type
	 *
	 * @return	string	content to be rendered
	 */
	function getPointsReport(){
		$content = '';
		$markerArray = array();
		if ($this->ffdata['user_reports'] == 1){
			$markerArray['###TEXT###'] = $this->pi_getll('points_report_text');
		} else {
			$markerArray['###TEXT###'] = '';
		}
			
		//get the calculated points
		$calced = $this->calculatePoints();
		//t3lib_div::debug(array($max_points,$points),'own');
		//t3lib_div::debug($calced,'calced');
		//replace the Marker in the Info-Text
		// ###TOTAL### max points to be achieved
		$markerArray['###TEXT###'] = str_replace('###TOTAL###',$calced['max'],$markerArray['###TEXT###']);
		// ###POINTS### actual points reached
		$markerArray['###TEXT###'] = str_replace('###POINTS###',$calced['own'],$markerArray['###TEXT###']);
		// ###PERCENT### of max points
		$markerArray['###TEXT###'] = str_replace('###PERCENT###',$calced['percent'],$markerArray['###TEXT###']);
		
		$markerArray['###REPORT###'] = '';
		//Render outcomes
		$markerArray['###REPORT###'] = $this->renderOutcome($calced['own'],$this->piVars);
		
				
		$content = $this->renderContent('###POINTS_REPORT###',$markerArray);
		
		return $content;
	}
	
	/**
	 * renderOutcome(): render the outcome of the questionnaire
	 *
	 * @param	int	gathered points
	 * @param	array	answers given
	 *
	 * @return	outcome to be rendered
	 */
	function renderOutcome($points = 0, $answers = array()) {
		$content = '';
		//t3lib_div::devLog('answers', $this->prefixId, 0, $answers);
		
		$where = 'pid='.$this->pid.' AND hidden=0 AND deleted=0';
		$res_outcomes = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*','tx_kequestionnaire_outcomes',$where,'','sorting');
		if ($res_outcomes){
			while ($outcome = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res_outcomes)){
				//t3lib_div::devLog('outcome', $this->prefixId, 0, $outcome);
				if ($outcome['type'] == 'dependancy'){
					//get the dependancies
					$dependancies = array();
					$dep_where = 'dependant_outcome='.$outcome['uid'].' AND hidden=0 AND deleted=0';
					$dep_res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*','tx_kequestionnaire_dependancies',$dep_where,'','sorting');
					if ($dep_res){
						while ($dep_row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($dep_res)){
							$dependancies[] = $dep_row;
						}
					}
					$dep_counter = count($dependancies);
					$own_counter = 0;
					//t3lib_div::devLog('deps', $this->prefixId, 0, $dependancies);
					foreach ($dependancies as $dep){
						//t3lib_div::devLog('dep '.$dep['title'], $this->prefixId, 0, $dep);
						$temp = '';
						foreach ($this->questions as $question){
							//t3lib_div::devLog('q '.$question['title'], $this->prefixId, 0, $question);
							if ($question['uid'] == $dep['activating_question']){
								switch ($question['closed_type']){
									case 'radio_single':
										//t3lib_div::devLog('mmm '.$dep['activating_value'], $this->prefixId, 0, array($answers[$dep['activating_question']]['options']));
										if ($answers[$dep['activating_question']]['options'] == $dep['activating_value']){
											//t3lib_div::devLog('mmm '.$dep['activating_value'], $this->prefixId, 0, array($answers[$dep['activating_question']]['options']));
											$own_counter ++;
										}
										break;
									case 'check_multi':
										if (in_array($dep['activating_value'],$answers[$dep['activating_question']]['options'])){
											$own_counter ++;
										}
										break;
								}
							}
						}
						//t3lib_div::devLog('outcome '.$outcome['title'], $this->prefixId, 0, array('dep'=>$dep_counter,'own'=>$own_counter));
					}
					$temp = $this->pi_RTEcssText($outcome['text']);
					if ($outcome['dependancy_simple'] == 1){
						if ($own_counter > 0){
							$content .= $temp;
						}
					} else {
						if ($dep_counter == $own_counter){
							$content .= $temp;
						}
					}
				} else {
					//t3lib_div::devLog('outcome', $this->prefixId, 0, $outcome);
					if ($points >= $outcome['value_start'] AND $points <= $outcome['value_end']) {
						$content .= $this->pi_RTEcssText($outcome['text']);
					}
				} 
			}
		}
		
		return $content;
	}
	
	/**
	 * calculatePoints(): Calculate the points gathered
	 *
	 * @param	array	$results: results made for this questionnaire
	 *
	 * @return	array	gathered points, own, total and max
	 */
	function calculatePoints($results = NULL){
		//t3lib_div::devLog('PIVars', $this->prefixId, 0, $this->piVars);
		$returner = array();
		
		foreach ($this->questionsByID as $qid => $question){
			$temp .= $qid;
			$titles[] = $question['title'];
			$bars['total'][$qid] = 0;
			$bars['own'][$qid] = 0;
			$bars['titles'][$qid] = $question['title'];
			switch ($question['type']){
				case 'closed':
					$answers = array();
					$where = 'question_uid='.$qid.$this->cObj->enableFields('tx_kequestionnaire_answers');
					$res_answers = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*','tx_kequestionnaire_answers',$where);
					$answer_max_points = 0;
					if ($res_answers){
						while ($answer = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res_answers)){
							$answers[$answer['uid']]['points'] = $answer['value'];
							switch ($question['closed_type']){
								case 'radio_single':
								case 'sbm_button':
								case 'select_single':
									if ($answer['value']>$answer_max_points) $answer_max_points = $answer['value'];
									break;
								case 'check_multi':
								case 'select_multi':
									if($answer['value'] > 0) $answer_max_points += $answer['value'];
									break;
							}
						}
					}
					
					$total_points = 0;
					if ($results){
						foreach ($results as $rid => $result){
							switch ($question['closed_type']){
								case 'radio_single':
								case 'sbm_button':
								case 'select_single':
									$total_points += $answers[$result[$qid]['answer']['options']]['points'];
									//t3lib_div::devLog('total_points', $this->prefixId, 0, array($total_points,$answers[$result[$qid]['answer']['options']],$answers,$result[$qid]['answer']['options'],$result[$qid]['answer']));
									break;
								case 'check_multi':
								case 'select_multi':
									//t3lib_div::devLog('result', $this->prefixId, 0, array($result[$qid]));
									//t3lib_div::devLog('answer', $this->prefixId, 0, array($answers));
									if (is_array($result[$qid]['answer']['options'])){
										foreach ($result[$qid]['answer']['options'] as $item){
											$total_points += $answers[$item]['points'];
										}
									}
									break;
							}
						}
						$bars['total'][$qid] = $total_points/count($results);
					}
					
					switch ($question['closed_type']){
						case 'sbm_button':
						case 'radio_single':
						case 'select_single':
							$bars['own'][$qid] = intval($answers[$this->piVars[$qid]['options']]['points']);
							break;
						case 'check_multi':
						case 'select_multi':
							//t3lib_div::devLog('piVar', $this->prefixId, 0, array($this->piVars[$qid]['options']));
							if (is_array($this->piVars[$qid]['options'])){
								foreach ($this->piVars[$qid]['options'] as $item){
									$bars['own'][$qid] += intval($answers[$item]['points']);
								}
							}
							break;
					}

					$own_total += $bars['own'][$qid];
					$max_points += $answer_max_points;
					break;
				case 'dd_words':
				case 'dd_area':
					$answers = array();
					// get all answers
					$where = 'question_uid='.$qid.$this->cObj->enableFields('tx_kequestionnaire_answers');
					$res_answers = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*','tx_kequestionnaire_answers',$where);
					$answer_max_points = 0;
					if ($res_answers){
						// create array with points of each answer
						while ($answer = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res_answers)){
							$answers[$answer['uid']]['points'] = $answer['value'];
							$answer_max_points += $answer['value'];
						}
					}
					
					// sum points of all answers of each question
					$total_points = 0;
					if ($results){
						foreach ($results as $rid => $result){
							if (is_array($result[$qid]['answer']['options'])){
								foreach ($result[$qid]['answer']['options'] as $item){
									$total_points += $answers[$item]['points'];
								}
							}
						}
						// calculate average points
						$bars['total'][$qid] = $total_points/count($results);
					}
					
					//t3lib_div::devLog('piVar', $this->prefixId, 0, array($this->piVars[$qid]['options']));
					if (is_array($this->piVars[$qid]['options'])){
						foreach ($this->piVars[$qid]['options'] as $item){
							$bars['own'][$qid] += $answers[$item]['points'];
						}
					}
					
					$own_total += $bars['own'][$qid];
					$max_points += $answer_max_points;
					break;
			}
		}
		if ($own_total < 0) $own_total = 0;
		if ($max_points > 0) $returner['percent'] = ($own_total/$max_points)*100;
		else $returner['precent'] = 0;
		$returner['own'] = $own_total;
		$returner['max'] = $max_points;
		$returner['bars'] = $bars;
		
		return $returner;
	}
	
	/**
	 * getQuizReport(): get the Report for Quiz/eLearning Questionnaire Type
	 *
	 * @return	string	content to be rendered for the report
	 */
	function getQuizReport(){
		$markerArray = array();
		$markerArray['###TEXT###'] = $this->pi_getll('quiz_report_text');
		
		$temp = '';
		$bars = array();
		
		$max_points = 0;
		$own_total = 0;
		
		//To make the middle, you need all the results till now
		$results = array();
		$where = 'pid='.$this->pid.$this->cObj->enableFields('tx_kequestionnaire_results');
		$res_results = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*','tx_kequestionnaire_results',$where);
		if ($res_results){
			while ($result = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res_results)){
				if ($result['xmldata'] != ''){
					$results[$result['uid']] = t3lib_div::xml2array($result['xmldata']);
					if (count($results[$result['uid']]) == 1) $results[$result['uid']] = t3lib_div::xml2array(utf8_encode($result['xmldata']));
				}				
			}
		}
		
		if (is_array($results)){
			$calculated = $this->calculatePoints($results);
		}
		
		$bars = $calculated['bars'];
		foreach ($bars['titles'] as $temp_title){
			$titles[] = $temp_title;
		}
		unset($bars['titles']);	
		$max_points = $calculated['max'];
		$own_total = $calculated['own'];
		$own_percent = $calculated['percent'];
		$own_percent = number_format($own_percent,2,',','.');
		
		//replace the Marker in the Info-Text
		// ###TOTAL### max points to be achieved
		$markerArray['###TEXT###'] = str_replace('###TOTAL###',$max_points,$markerArray['###TEXT###']);
		// ###POINTS### actual points reached
		$markerArray['###TEXT###'] = str_replace('###POINTS###',$own_total,$markerArray['###TEXT###']);
		// ###PERCENT### of max points
		$own_percent = ($own_total/$max_points)*100;
		$own_percent = number_format($own_percent,2,',','.');
		$markerArray['###TEXT###'] = str_replace('###PERCENT###',$own_percent,$markerArray['###TEXT###']);
		
		$markerArray['###REPORT###'] = '';
		//if premium is loaded render a graphic report
		if (t3lib_extMgm::isLoaded('ke_questionnaire_premium')){
			require_once(t3lib_extMgm::extPath('ke_questionnaire_premium').'res/other/class.open_flcharts2.php');
			
			if ($this->ffdata['q_report_graph'] == 1){
				$y_scale = array();
				$y_scale['max'] = 8;
				$y_scale['min'] = 0;
				$y_scale['step'] = 1;
				//t3lib_div::devLog('bars', $this->prefixId, 0, $bars);
				foreach ($bars as $type => $bar){
					$bars[$type] = array();
					foreach ($bar as $key => $value){
						$bars[$type][] = $value;
						if ($value > $y_scale['max']) $y_scale['max'] = $value;
						if ($value < $y_scale['min']) $y_scale['min'] = $value;
					}
				}
				$temp = array();
				$temp = $bars;
				$bars = array();
				foreach ($temp as $key => $values){
					$bars[] = $values;
				}
				
				$charts = new open_flcharts2();
				$charts->path = 'typo3conf/ext/ke_questionnaire_premium/';
				$this->addHeaderData['of_charts'] = $charts->fe_js();
				$marker = 'quiz_report';
				$title = $this->pi_getLL('quiz_report_title');
				$keys = array($this->pi_getLL('quiz_report_total'),$this->pi_getLL('quiz_report_own'));
				$colors = array('#39BB2C','#FF9C00');
				
				$markerArray['###REPORT###'] = '<div id="'.$marker.'"> </div>'."\n";
				$markerArray['###REPORT###'] .= $charts->getBarChart($marker, $title, $bars, $titles, $keys, $colors, $y_scale);
			}
		}
		
		// Hook give more Fields for Questionnaire List
		$markerArray['###CERTIFICATE###'] = '';
		if (is_array($GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][$this->extKey]['pi1_markerArray'])){
			foreach($GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][$this->extKey]['pi1_markerArray'] as $_classRef){
				$_procObj = & t3lib_div::getUserObj($_classRef);
				$markerArray = $_procObj->pi1_markerArray($this,$markerArray,$this->cObj->data['uid'],$this->ffdata,$own_percent);
			}
		}
		
		$content = $this->renderContent('###QUIZ_REPORT###',$markerArray);
		
		return $content;
	}
	
	/**
	 * sendByMail(): E-Mail-Content
	 *
	 * @param	string	$email: Email adress to be send to
	 *
	 * @return	boolean	mail send or not
	 */
	function sendByMail($email){
		require_once(t3lib_extMgm::extPath('ke_questionnaire').'res/other/class.plain_export.php');

		$plain_conf = $this->conf;
		$storage_pid = $this->ffdata['storage_pid'];
	
		$plain = new plain_export($plain_conf,$storage_pid, 'test',$this->cObj->data['pi_flexform']['data']);
		$this->getResults($this->piVars['p_id'],false);
			
		$mailData = $plain->getPlain($this->saveArray);
		
		$emailText .= $this->ffdata['send_finish_mail_subject']."\r\n";
		$emailText .= $this->ffdata['send_finish_mail_emailhead']."\r\n";
		foreach($mailData as $emailContent) {
			if(is_array($emailContent) && count($emailContent) >= 2) {
				$emailText .= $emailContent['title']."\r\n";
				if(is_array($emailContent['value'])) {
					foreach($emailContent['value'] as $answer) {
						$emailText .= $answer."\r\n";
						$emailText .= "\r\n";
					}
				} else {
					$emailText .= $emailContent['value']."\r\n";
				}
				$emailText .= "\r\n";
			}
		}

		$emailText = strip_tags($emailText);
		if($this->cObj->sendNotifyEmail($emailText,$email,'',$this->ffdata['send_finish_mail_email'],$this->ffdata['send_finish_mail_email'],$this->ffdata['send_finish_mail_email'])) {
			return true;
		} else {
			return false;
		}
	}
	
	/**
	 * getPDF(): Render the PDF
	 *
	 * @param	string	$type: Type of PDF to be rendered
	 */
	function getPDF($type = 'empty'){
		//you'll need ke_dompdf
		if (t3lib_extMgm::isLoaded('ke_dompdf')){
			require_once(t3lib_extMgm::extPath('ke_questionnaire').'res/other/class.dompdf_export.php');
			$pdfdata = '';
	
			$pdf_conf = $this->conf;
			$storage_pid = $this->ffdata['storage_pid'];
	
			$pdf = new dompdf_export($pdf_conf,$storage_pid, $this->cObj->data['header'],$this->cObj->data['pi_flexform']['data']);
			$pdf->user_marker = $this->userMarker;
	
			switch ($type){
				case 'empty':
					$this->getResults($this->piVars['p_id'],false);
					$pdfdata = $pdf->getPDFBlank($this->saveArray);
					break;
				case 'filled':
					$this->getResults($this->piVars['p_id'],false);
					$pdfdata = $pdf->getPDFFilled($this->saveArray);
					break;
				case 'compare':
					$this->getResults($this->piVars['p_id'],false);
					$pdfdata = $pdf->getPDFCompare($this->saveArray);
					break;
				case 'outcomes':
					$this->getResults($this->piVars['p_id'],false);
					$pdfdata = $pdf->getPDFOutcomes($this->saveArray);
					break;
				default:
					break;
			}
		}
	}
	
	/**
	 * getQuestionTypeObject(): Find the Questions type and get the question-Object
	 *
	 * @param	array	$question: the question-array the object should be get for
	 * @param	int	$validate: validate the question or not
	 */
	function getQuestionTypeObject($question,$validate = 0){
		$uid = $question['uid'];
		$content = '';
		$saveArray = array();

		switch ($question['type']){
			case 'open':
				$question_obj = new question_open();
				$answer = array();
				if (is_array($this->saveArray[$question['uid']]) AND !$this->piVars[$question['uid']] AND $this->saveArray[$question['uid']]){
					$this->piVars[$question['uid']]['text'] = $this->saveArray[$question['uid']]['answer'];
				}
				$answer['text'] = $this->piVars[$question['uid']]['text'];
				if ($answer['text'] == '') $answer['text'] = $question["open_in_text"];
				$question_obj->init($uid,$this,$answer,$validate,"error","d.m.y",",");
				break;
			case 'closed':
				$question_obj = new question_closed();
				if (is_array($this->saveArray[$question['uid']]) AND !$this->piVars[$question['uid']] AND $this->saveArray[$question['uid']]){
					if (!is_array($this->saveArray[$question['uid']]['answer']) AND stristr($this->saveArray[$question['uid']]['answer'],'<phparray>')){
						$this->piVars[$question['uid']]['options'] = $this->saveArray[$question['uid']]['answer'];
					} else {
						$this->piVars[$question['uid']] = $this->saveArray[$question['uid']]['answer'];
					}
				}
				$answer = $this->piVars[$question['uid']];
				$question_obj->init($uid, $this, $answer, $validate);
			break;
			case 'dd_words':
				$question_obj = new question_dd_words();
				if (is_array($this->saveArray[$question['uid']]) AND !$this->piVars[$question['uid']] AND $this->saveArray[$question['uid']]){
					if (!is_array($this->saveArray[$question['uid']]['answer']) AND stristr($this->saveArray[$question['uid']]['answer'],'<phparray>')){
						$this->piVars[$question['uid']]['options'] = $this->saveArray[$question['uid']]['answer'];
					} else {
						$this->piVars[$question['uid']] = $this->saveArray[$question['uid']]['answer'];
					}
				}
				$answer = $this->piVars[$question['uid']];
				$question_obj->init($uid, $this, $answer, $validate);
			break;
			case 'dd_area':
				$question_obj = new question_dd_area();
				if (is_array($this->saveArray[$question['uid']]) AND !$this->piVars[$question['uid']] AND $this->saveArray[$question['uid']]){
					if (!is_array($this->saveArray[$question['uid']]['answer']) AND stristr($this->saveArray[$question['uid']]['answer'],'<phparray>')){
						$this->piVars[$question['uid']]['options'] = $this->saveArray[$question['uid']]['answer'];
					} else {
						$this->piVars[$question['uid']] = $this->saveArray[$question['uid']]['answer'];
					}
				}
				$answer = $this->piVars[$question['uid']];
				$question_obj->init($uid, $this, $answer, $validate);
			break;
			case 'matrix':
				$question_obj = new question_matrix();
				if (is_array($this->saveArray[$question['uid']]) AND !$this->piVars[$question['uid']] AND $this->saveArray[$question['uid']]){
					$this->piVars[$question['uid']] = $this->saveArray[$question['uid']]['answer'];
				}
				$answer=$this->piVars[$question['uid']];
				$question_obj->init($uid,$this,$answer,$validate,'error',"d.m.y",",");
				break;
			case 'semantic':
				$question_obj = new question_semantic();

				if (is_array($this->saveArray[$question['uid']]) AND !$this->piVars[$question['uid']] AND $this->saveArray[$question['uid']]){
					$this->piVars[$question['uid']] = $this->saveArray[$question['uid']]['answer'];
				}
				$answer = $this->piVars[$question['uid']];
				$question_obj->init($uid,$this,$answer,$validate);
				break;
			case 'demographic':
				$question_obj = new question_demographic();

				if (is_array($this->saveArray[$question['uid']]) AND !$this->piVars[$question['uid']] AND $this->saveArray[$question['uid']]){
					$this->piVars[$question['uid']] = $this->saveArray[$question['uid']]['answer'];
				}
				$answer=$this->piVars[$question['uid']];

				if (is_array($answer['fe_users'])){
					foreach ($answer['fe_users'] as $field => $value){
						//t3lib_div::devLog('demographic answer field '.$question['uid'], $this->prefixId, 0, array($field,$value));
						if ($value == ''){
							$answer['fe_users'][$field] = $GLOBALS['TSFE']->fe_user->user[$field];
						}
					}
				}
				if (is_array($options['fields'])){
					foreach ($options['fields'] as $field => $type){
						//t3lib_div::devLog('demographic answer field '.$question['uid'], $this->prefixId, 0, array($field,$type));
						if ($answer['fe_users'][$field] == ''){
							$answer['fe_users'][$field] = $GLOBALS['TSFE']->fe_user->user[$field];
						}
					}
				}
				$question_obj->init($uid,$this,$answer,$validate,"error","","");
				break;
			case 'privacy':
				$question_obj = new question_privacy();
				$answer = $this->piVars[$question['uid']];
				$question_obj->init($uid,$this,$answer,$validate);
				break;
			case 'blind':
				$question_obj = new question_blind();
				$answer = array();
				$question_obj->init($uid,$this,$answer);
				break;
			default:
			/*Hook*/
				if (is_array($GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][$this->extKey]['getDifferentQuestionTypeObject'])){
					foreach($GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][$this->extKey]['getDifferentQuestionTypeObject'] as $_classRef){
						$_procObj = & t3lib_div::getUserObj($_classRef);
						if (!is_object($question_obj)) $question_obj = $_procObj->getDifferentQuestionType($this,$question,$this->piVars);
					}
				}
		}
		return $question_obj;
	}
	
	/**
	 * getQuestionTypeRender(): Get the Question-Object and render it
	 *
	 * @param	array	$question: to be rendered
	 */
	function getQuestionTypeRender($question){
		//t3lib_div::debug($question);
		$uid = $question['uid'];
		$content = '';
		//$saveString = '';
		$saveArray = array();
		
		//get the object for the question
		$question_obj = $this->getQuestionTypeObject($question);
		if (is_object($question_obj)){
			if ($question_obj->checkDependancies() AND $this->validated){
				$question_obj->validateInput = 1;
			}
			
			$saveArray = $question_obj->getSaveArray();
			//t3lib_div::debug($saveArray,"getQuestionTypeRender");
			$content = $question_obj->render();
			//t3lib_div::debug($question_obj);
			
			if ($this->user_id){
				foreach ($this->userMarker as $marker => $value){
					$markerArray[$marker] = $value;
				}
				$content = $this->cObj->substituteMarkerArrayCached($content, $markerArray, array(), array());
			}
			
			//t3lib_div::debug($content,"getQuestionTypeRender");
				
			if (is_array($saveArray[$question['uid']])){
				$this->saveArray[$question['uid']] = $saveArray[$question['uid']];
			}
		}
		//t3lib_div::debug($content);
		return $content;
	}

	/**
	 * init(): The init method of the PlugIn
	 */
	function init(){
		//t3lib_div::debug($this->cObj->data);
		// Assign the flexform data to a local variable for easier access
		$piFlexForm = $this->cObj->data['pi_flexform'];

		// Traverse the entire flexform array based on the language
		// and write the content to an array
		if (is_array($piFlexForm['data'])) {
			foreach ( $piFlexForm['data'] as $sheet => $data ) {
				foreach ( $data as $lang => $value ) {
					foreach ( $value as $key => $val ) {
						$this->ffdata[$key] = $this->pi_getFFvalue($piFlexForm, $key, $sheet);
					}
				}
			}
		}
		
		//questionnaire Type
		$this->type = $this->ffdata['type'];
		
		//t3lib_div::devLog('ffdata', $this->prefixId, 0, $this->ffdata);
		$this->pid = $this->ffdata['storage_pid'];

		//get the Template
		//check if there is given a template, or use the standard
		$template = 'questionnaire.html';
		$this->tmpl_path = t3lib_extMgm::siteRelPath('ke_questionnaire').'res/templates/';
		if ($this->conf['template_dir'] != '') $this->tmpl_path = trim($this->conf['template_dir']);
		if ($this->ffdata['template_dir'] != '') $this->tmpl_path = trim($this->ffdata['template_dir']);
		//t3lib_div::debug($GLOBALS['TSFE']->config);
		//language vars
		//$this->conf['sys_language_uid'] = $GLOBALS['TSFE']->config['config']['sys_language_uid'];
		$this->conf['sys_langauge_uid'] = $this->cObj->data['sys_language_uid'];
		$this->conf['language'] = strtolower($GLOBALS['TSFE']->config['config']['language']);
		
		$this->tmpl = $this->cObj->fileResource($this->tmpl_path.$template);
		//t3lib_div::devLog('template base', $this->prefixId, 0, array($this->tmpl_path,$this->tmpl,$this->tmpl_path.$template));

		// if $this->ffdata['render_count'] is null, set it to one
		if ($this->ffdata['render_count'] == 0) $this->ffdata['render_count'] = 1;
		
		$this->extConf = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['ke_questionnaire']);

		//get the user id
		$this->user_id = $GLOBALS['TSFE']->fe_user->user['uid'];
		if ($this->user_id){
			$markerArray = array();
			$user_res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*','fe_users','uid='.$this->user_id);
			if ($user_res){
				$user = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($user_res);
				foreach ($user as $key => $value){
					if ($key != 'pid' AND $key != 'password'){
						$markerArray['###USER_'.strtoupper($key).'###'] = $value;
					}
				}
			}
			$this->userMarker = $markerArray;
		}

		//init Captcha
		if (t3lib_extMgm::isLoaded('sr_freecap') ) {
			require_once(t3lib_extMgm::extPath('sr_freecap').'pi2/class.tx_srfreecap_pi2.php');
			$this->freeCap = t3lib_div::makeInstance('tx_srfreecap_pi2');
		}
		
		//get the questions of the questionnaire
		$this->getQuestions();
		
		//centralize the pagecount
		$this->pageCount = $this->getPageCount();
		
		//clear piVars (XSS and SQLInjection)
		$this->clearPiVars();
		
		$this->no_authcodeKey = 'no_authcode';
	}
	
	/**
	 * clearPiVars(): clear the piVars as counter to xss and hacking attemps
	 */
	function clearPiVars(){
		$piVars = $this->piVars;
		foreach ($piVars as $key => $value){
			$key = htmlspecialchars($key);
			if (is_array($value)){
				foreach ($value as $s_key => $s_value){
					$s_key = htmlspecialchars($s_key);
					if (is_array($s_value)){
						foreach ($s_value as $subs_key => $subs_value){
							$subs_key = htmlspecialchars($subs_key);
							if (is_array($subs_value)){
								foreach ($subs_value as $ssubs_key => $ssubs_value){
									if (!is_array($ssubs_value)){
										$ssubs_key = htmlspecialchars($ssubs_key);
										$piVars[$key][$s_key][$subs_key][$ssubs_key] = htmlspecialchars($ssubs_value);
									}
								}
							} else {
								$piVars[$key][$s_key][$subs_key] = htmlspecialchars($subs_value);
							}
						}
					} else {
						switch ($s_key){
							case 'options':
									$piVars[$key][$s_key] = intval($s_value);
								break;
							default:
									$piVars[$key][$s_key] = htmlspecialchars($s_value);
								break;
						}
					}
				}
			} else {
				switch ($key){
					case 'result_id':
					case 'start_tstamp':
					case 'page':
							$piVars[$key] = intval($value);
						break;
					default:
							$piVars[$key] = htmlspecialchars($value);
						break;
				}
			}
		}
		$this->piVars = $piVars;
	}

	/**
	 * getQuestions(): Gather all the questions of this questionnaire ready for showing
	 * 			Also builds the array for the question counts
	 */
	function getQuestions(){
		$this->allQuestions = array();
		$this->questions = array();
		$this->questionCount = array();
		$this->questionsByID = array();
		$this->questionCount['total'] = 0; //total of questions
		$this->questionCount['notshown_dependants'] = 0; //don't count dependants when not shown if not activated
		$this->questionCount['only_questions'] = 0; //no blind-texts counting
		$this->questionCount['no_dependants'] = 0; //don't count the dependants
		
		$temp_count = 0;
		$temp_count_hidden = 0;
		$questions = array();
		
		//Hook to manipulate the Question-Array
		if (is_array($GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][$this->extKey]['pi1_getQuestions'])){
			foreach($GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][$this->extKey]['pi1_getQuestions'] as $_classRef){
				$_procObj = & t3lib_div::getUserObj($_classRef);
				$questions = $_procObj->pi1_getQuestions($this);
			}
		}
		
		//if there are no questions (could be out if the hook) take the normal way
		if (!$questions){
			$selectFields = '*';
			$where = 'pid = ' . $this->pid;
			$where .= ' AND sys_language_uid = ' . $GLOBALS['TSFE']->sys_language_uid;
			$where .= $this->cObj->enableFields('tx_kequestionnaire_questions');
			$orderBy = 'sorting';
			$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
				$selectFields,
				'tx_kequestionnaire_questions',
				$where,
				'',$orderBy, ''
			);
			if ($res){
				while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)){
					$questions[] = $row;
				}
			}
		}

		//if there are active questions for the questionnaire
		if (is_array($questions)){
			foreach ($questions as $row){
				$question_obj = $this->getQuestionTypeObject($row);
				if ($row['type'] == 'pool' AND $row['show_title'] == 0){
					//t3lib_div::debug($row);
				} else {
					if (count($question_obj->dependancies) > 0){
						if ($this->ffdata['render_count_withoutdependant'] == 1) $row['is_dependant'] = 1;
						else {
							if ($row['dependant_show'] == 0){
								if ($question_obj->checkDependancies()) $row['no_show'] = 0;
								else $row['no_show'] = 1;	
								//$row['no_show'] = $this->checkQuestionIfActivated($row);
							}
						}
					} else {
						if ($this->ffdata['render_count_withoutdependant'] == 1 AND $row['type'] != 'refusal'){
							if ($this->ffdata['record_count_withblind'] == 0 AND $row['type'] != 'blind') $temp_count ++;
							elseif ($this->ffdata['record_count_withblind'] == 1) $temp_count ++;
						}
					}
					if ($row['no_show'] == 1) {
						$temp_count_hidden ++;
					}
					if ($row['type'] != 'blind' AND $row['type'] != 'refusal') $this->questions[] = $row;
					$this->allQuestions[] = $row;
					$this->questionsByID[$row['uid']] = $row;
				}
			}
			$this->questionCount['no_dependants'] = $temp_count;
			$this->questionCount['notshown_dependants'] = $temp_count_hidden;
			$this->questionCount['only_questions'] = count($this->questions);
			$this->questionCount['total'] = count($this->allQuestions);
		}
	}
	
	/**
	 * checkQuestionIfActivated(): Check if the question is activeted and should be shown
	 *
	 * @param	array	$question: question to be checked
	 *
	 * @return	int 	0 or 1 if it is activated or not
	 */
	function checkQuestionIfActivated($question){
		$question_obj = $this->getQuestionTypeObject($question);
		if ($question_obj->checkDependancies()) return 0;
		else return 1;
	}

	/**
	 * Render the Content in the Template
	 *
	 * @param       string     	$subpart: Subpart to be filled
	 * @param       array     	$markerArray: to fill the template
	 *
	 * @return      the whole content ready rendered
	 */
	function renderContent($subpart,$markerArray){
		$wrappedSubpartArray = array();
	  	if ($this->errorText == '') {
	  		$markerArray['###ERROR###'] = '';
	  	} else {
	  		$markerArray['###ERRORCLASS###'] = 'error';
			$markerArray['###ERROR###'] = $this->errorText;
	  	}

		$subpart = $this->cObj->getSubpart($this->tmpl,$subpart);
		$content = $this->cObj->substituteMarkerArrayCached($subpart, $markerArray, array(), $wrappedSubpartArray);

		return $content;
	}
	
	/**
	 * Render the Content in the Template
	 *
	 * @param       string     	$text: Subpart to be filled
	 * @param       array     	$markerArray: to fill the template
	 *
	 * @return      the whole content ready rendered
	 */
	function renderMarker($text,$markerArray){
		$wrappedSubpartArray = array();
	  	if ($this->errorText == '') {
	  		$markerArray['###ERROR###'] = '';
	  	} else {
	  		$markerArray['###ERRORCLASS###'] = 'error';
			$markerArray['###ERROR###'] = $this->errorText;
	  	}

		$content = $this->cObj->substituteMarkerArrayCached($text, $markerArray, array(), $wrappedSubpartArray);

		return $content;
	}

	/**
	 * sendMail(): send Emails with the html-mail functions of t3lib
	 *
	 * @param	string	$email: mail address to be send to
	 * @param	array	$mailTexts: Texts to be used in the mail
	 */
	function sendMail($email,$mailTexts){
		$body = $mailTexts["body"];
		$subject = $mailTexts['subject'];
		if ($this->user_id){
			$body = $this->renderMarker($body, $this->userMarker);
			$subject = $this->renderMarker($subject, $this->userMarker);
		}
		
		$html_start="<html><head><title>".$subject."</title></head><body>";
		$html_end="</body></html>";

		$this->htmlMail = t3lib_div::makeInstance('t3lib_htmlmail');
		$this->htmlMail->start();
		$this->htmlMail->recipient = $email;
		$this->htmlMail->subject = $subject;
		$this->htmlMail->from_email = $mailTexts['fromEmail'];
		$this->htmlMail->from_name = $mailTexts['fromName'];
		$this->htmlMail->replyto_name = $mailTexts['fromName'];
		$this->htmlMail->organisation = $mailTexts['fromName'];
		$this->htmlMail->returnPath = $mailTexts['fromEmail'];
		$this->htmlMail->addPlain($body);
		$this->htmlMail->setHTML($this->htmlMail->encodeMsg($html_start.$body.$html_end));
		$mails = explode(',',$email);
		foreach ($mails as $mail){
			$out .= $this->htmlMail->send($mail).'<br />';
		}
		//t3lib_div::devLog('sendMail out', $this->prefixId, 0, array($out,$mails,$mailTexts));
		return $out;
	}

}



if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/ke_questionnaire/pi1/class.tx_kequestionnaire_pi1.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/ke_questionnaire/pi1/class.tx_kequestionnaire_pi1.php']);
}

?>
