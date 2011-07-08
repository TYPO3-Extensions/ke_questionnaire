<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2009 Stefan Froemken <froemken@kennziffer.com>
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
 * @author	Stefan Froemken <froemken@kennziffer.com>
 * @package	TYPO3
 * @subpackage	tx_kequestionnaire
 * */

class question_dd_words extends question {
	var $templateName = "question_dd_words.html";              //Name of default Templatefile
	
	/**
	 * Saves the language object
	 * 
	 * @var language
	 */
	var $lang;

	/**
	 * The initiation method of the PlugIn
	 *
	 * @param	array		$conf: The plugin configuration
	 * @param	object		$parent: parent-Object
	 * @param	array		$answer: prefill Values for answers id/text
	 *
	 */
	function base_init($uid){
		// initialize language
		$language = $GLOBALS['TSFE']->tmpl->setup['config.']['language'];
		$this->lang = t3lib_div::makeInstance('language');
		$this->lang->init($language ? $language : 'default');
		
		$this->options = array();
		$where = 'question_uid = ' . $uid;
		$where .= ' AND sys_language_uid = ' . $GLOBALS['TSFE']->sys_language_uid;
		$where .= $this->cObj->enableFields('tx_kequestionnaire_answers');
		$res = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows(
			'*',
			'tx_kequestionnaire_answers',
			$where,
			'','sorting'
		);

		// loop answers
		foreach($res as $row){
			$row = $this->processRTEFields($row, 'tx_kequestionnaire_answers');

			$this->options[$row['uid']] = $row;
			$this->answers[$row['uid']] = $row;
		}
	}


	/**
	 * Defines all fields in Template
	 */
	function buildFieldArray(){
		// put JS to header
		$GLOBALS['TSFE']->additionalHeaderData['keq-js-core'] = '<script src="'.t3lib_extMgm::siteRelPath('ke_questionnaire').'res/jquery/jquery-1.5.1.min.js" type="text/javascript"></script>';
		$GLOBALS['TSFE']->additionalHeaderData['keq-js-ui'] = '<script src="'.t3lib_extMgm::siteRelPath('ke_questionnaire').'res/jquery/jquery-ui-1.8.11.custom.min.js" type="text/javascript"></script>';
		$GLOBALS['TSFE']->register['kequestionnaire'][$this->question['uid']] = '
			$("select#keq_' . $this->question['uid'] . '").hide();
			$("div#question_' . $this->question['uid'] . ' div.keq-moveable").draggable({
				revert: true,
				helper: "clone",
				opacity: 0.7
			});
			$("div#question_' . $this->question['uid'] . ' p.bodytext").css({
				display: "inline-block"
			});
			
			$("div#question_' . $this->question['uid'] . ' span.keq-placeholder").droppable({
				accept: "div#question_' . $this->question['uid'] . ' div.keq-moveable",	
				activeClass: "keq-possible",
				hoverClass: "keq-hover",
				drop: function( event, ui ) {
					if($("div#question_' . $this->question['uid'] . ' .keq-moveable:contains(" + $(this).text() + ")").length) {
						answerIdOld = $("div#question_' . $this->question['uid'] . ' .keq-moveable:contains(" + $(this).text() + ")").attr("id").replace(/keq-moveable/g, "");
						$("#keq-moveable" + answerIdOld).show();
						$("select#keq_' . $this->question['uid'] . ' option[value=" + answerIdOld + "]").attr("selected", false);
					}
					answerIdNew = $("div#question_' . $this->question['uid'] . ' .keq-moveable:contains(" + ui.draggable.text() + ")").attr("id").replace(/keq-moveable/g, "");
					placeholderId = $(this).attr("id").replace(/keq-placeholder/g, "");

					$("#keq-moveable" + answerIdNew).draggable({revert:false}).hide();
					
					// Set only if answer is correct
					if(answerIdNew == placeholderId) {
						$("select#keq_' . $this->question['uid'] . ' option[value=" + answerIdNew + "]").attr("selected", true);						
					}

					$(this).text(ui.draggable.text());
				}
			});
		';

		// create the answers for each question
		// create also hidden fields to save the answer
		foreach($this->answers as $key => $value) {
			$value['text'] = strip_tags($value['text']);
			$answers .= $this->cObj->wrap($value['text'], '<div id="keq-moveable' . $key . '" class="keq-moveable">|</div>');
			$markerArray['###WORD_' . strtoupper($value['text']) . '###'] = '<span id="keq-placeholder' . $key . '" class="keq-placeholder">' . $this->lang->sL('LLL:EXT:ke_questionnaire/pi1/locallang.xml:question_ddwords_placeholder') . '</span>';
		}

		$this->question['text'] = $this->cObj->substituteMarkerArray($this->question['text'], $markerArray);
		$this->fields['list'] = new kequestionnaire_input(
			'list',
			'selectbox_multi',
			$this->answer['options'],
			'###LIST###',
			$this->obj,
			$this->options, array(), array(), array(),
			'', $this->dependants
		);
		$this->fields['answers'] = new kequestionnaire_input(
			'text',
			'blind',
			array('text' => $answers),
			'###BLIND###',
			$this
		);
		//t3lib_div::devLog('buildFieldArray', 'ke_questionnaire', -1, array($this->fields, $markerArray));
	}

	/**
	 * Selects Subpartname depending on Qustiontype
	 *
	 * @return      the whole question ready rendered
	 *
	 */
	function getTemplateName(){
		return "QUESTION_DD_WORDS";
	}

	/**
	 * Validate if words are set correctly
	 */
	function validate() {
		if(!$this->question['mandatory']) return;

		$value = $this->answer['options'];
		if(!empty($value)) return;
		
		if (!$this->checkDependancies()){
			$this->error = 0;
		} else {
			$this->error = 1;
			$this->errorMsg = $this->obj->pi_getLL('error_required');
		}
	}
}

?>
