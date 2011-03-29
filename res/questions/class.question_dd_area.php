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

class question_dd_area extends question {
	var $templateName           = "question_dd_area.html";              //Name of default Templatefile

	/**
	 * The initiation method of the PlugIn
	 *
	 * @param	array		$conf: The plugin configuration
	 * @param	object		$parent: parent-Object
	 * @param	array		$answer: prefill Values for answers id/text
	 *
	 */
	function base_init($uid){
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
			$("div#question_' . $this->question['uid'] . '").css("padding-bottom", "25px");
			
			offset = $("div#question_' . $this->question['uid'] . '").find("div.keq-moveable").css({
				"position": "absolute",
				"border": 0,
				"backgroundColor": "transparent",
				"width": "auto",
				"padding": 0,
				"margin": 0
			}).find(":first").offset();
			$("div#question_' . $this->question['uid'] . '").find("div.keq-moveable").draggable({
				revert: true
			}).offset(offset);
			
			$("div#question_' . $this->question['uid'] . '").find("div.keq-placeholder").css("opacity", .7).droppable({
				accept: "div#question_' . $this->question['uid'] . ' div.keq-moveable",	
				activeClass: "keq-possible",
				hoverClass: "keq-hover",
				drop: function( event, ui ) {
					answerId = ui.draggable.attr("id").replace(/keq-moveable/g, "");
					answerId = answerId.split("-");
					placeholderId = $(this).attr("id").replace(/keq-placeholder' . $this->question['uid'] . '-/g, "");
					
					// moveable was moved. So first of all deselect option in selectbox
					$("select#keq_' . $this->question['uid'] . ' option[value=" + answerId[0] + "]").attr("selected", false);
					
					// If answer is correct
					if(answerId[1] == placeholderId) {
						$("select#keq_' . $this->question['uid'] . ' option[value=" + answerId[0] + "]").attr("selected", true);
						$("#keq-moveable" + answerId[0] + "-" + answerId[1]).fadeOut();
						$("#keq-ddarea-checkbox" + answerId[0]).css("backgroundColor", "#00FF00");
					} else {
						$("#keq-ddarea-checkbox" + answerId[0]).css("backgroundColor", "#DDDDDD");
						var moveItem = $("#keq-moveable" + answerId[0] + "-" + answerId[1]);
						var index = $("div#question_' . $this->question['uid'] . '").find("div.keq-moveable").index(moveItem);
						$("div#question_' . $this->question['uid'] . '").find("div.keq-moveable:eq(" + index + ")").css({
							top: 0,
							left: 0
						});
					}
				}
			});
		';
		
		// get coords of answerareas
		$coordRow = t3lib_div::trimExplode("\n", $this->question['coords']);
		foreach($coordRow as $keyRow => $row) {
			$coordParts = t3lib_div::trimExplode("|", $row);
			foreach($coordParts as $keyPart => $part) {
				$coords[($keyRow + 1)][$keyPart] = t3lib_div::trimExplode(":", $part);
			}
		}
		
		// create the answers for each question
		// create also hidden fields to save the answer
		$i = 0;
		$count = count($this->answers);
		$dropAreas = '';
		foreach($this->answers as $key => $value) {
			//$answers .= $this->cObj->wrap($value['text'], '<div style="z-index: 10' . ($count - $i) . ';" id="keq-moveable' . $key . '-' . $value['answerarea'] . '" class="keq-moveable">|</div>');
			$conf['file'] = 'uploads/tx_kequestionnaire/' . $value['image'];
			$conf['wrap'] = '<div style="z-index: 10' . ($count - $i) . ';" id="keq-moveable' . $key . '-' . $value['answerarea'] . '" class="keq-moveable">|</div>';
			$answers .= $this->cObj->IMAGE($conf);
			$checkboxes .= '<div id="keq-ddarea-checkbox' . $key . '" class="keq-ddarea-checkbox">&nbsp;</div>';
			
			// there can be more answers than areas
			// don't make more areas than needed
			$dropAreas[$value['answerarea']] = '
				<div id="keq-placeholder' . $this->question['uid'] . '-' . $value['answerarea'] . '" class="keq-placeholder" style="
					z-index: '.$i.';
					position: absolute;
					top: ' . $coords[$value['answerarea']][0][1] . 'px;
					left: ' . $coords[$value['answerarea']][0][0] . 'px;
					height: ' . ($coords[$value['answerarea']][1][1] - $coords[$value['answerarea']][0][1]) . 'px;
					width: ' . ($coords[$value['answerarea']][1][0] - $coords[$value['answerarea']][0][0]).'px;
				">&nbsp;</div>';
			$i++;
		}
		
		$dropAreas = implode('', $dropAreas);
		
		$checkboxes = $checkboxes . '<div style="clear: left;"></div>';
		
		//$imgSource = '<div class="keq_img_bottom" style="position: relative; z-index: 1;">'.$this->renderImage(array('title' => $this->question['title'],'image' => $this->question['image'],'image_position' => $this->question['image_position'])).'<div style="z-index: 2; position: absolute; top: 10px; left: 10px; border: 1px solid red;">&nbsp;TEST&nbsp;</div></div>';
		$imgSource = $this->renderImage(array('title' => $this->question['title'],'image' => $this->question['image'],'image_position' => $this->question['image_position']));
		
		$this->fields['ddarea'] = new kequestionnaire_input(
			'text',
			'ddarea',
			array('ddimage' => $imgSource, 'dropareas' => $dropAreas),
			'###DDAREA###',
			$this
		);
		
		$this->fields['list'] = new kequestionnaire_input(
			'list',
			'selectbox_multi',
			$this->answer['options'],
			'###LIST###',
			$this->obj,
			$this->options, array(), array(), array(),
			'', $this->dependants
		);
		
		$this->fields['checkboxes'] = new kequestionnaire_input(
			'text',
			'blind',
			array('text' => $checkboxes),
			'###CHECKBOXES###',
			$this
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
	
	function renderImage($data=array()){
			if ($data['image']){
				$img_path = 'uploads/tx_kequestionnaire/';
				$img_first = '<img alt="'.$data['title'].'" src="';
				$img_last = '" />';
				$img = '';
				$img = $img_first.$img_path.$data['image'].$img_last;
			}
			return $img;
		}
	
	/**
	 * Selects Subpartname depending on Qustiontype
	 *
	 * @return      the whole question ready rendered
	 *
	 */
	function getTemplateName(){
		return "QUESTION_DD_AREA";
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
