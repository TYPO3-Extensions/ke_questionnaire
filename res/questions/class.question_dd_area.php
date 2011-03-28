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
		$GLOBALS['TSFE']->additionalHeaderData['keq-js-core'] = '<script src="'.t3lib_extMgm::siteRelPath('ke_questionnaire').'res/jquery/jquery-1.4.4.min.js" type="text/javascript"></script>';
		$GLOBALS['TSFE']->additionalHeaderData['keq-js-ui-core'] = '<script src="'.t3lib_extMgm::siteRelPath('ke_questionnaire').'res/jquery/jquery.ui.core.min.js" type="text/javascript"></script>';
		$GLOBALS['TSFE']->additionalHeaderData['keq-js-ui-widget'] = '<script src="'.t3lib_extMgm::siteRelPath('ke_questionnaire').'res/jquery/jquery.ui.widget.min.js" type="text/javascript"></script>';
		$GLOBALS['TSFE']->additionalHeaderData['keq-js-ui-mouse'] = '<script src="'.t3lib_extMgm::siteRelPath('ke_questionnaire').'res/jquery/jquery.ui.mouse.min.js" type="text/javascript"></script>';
		$GLOBALS['TSFE']->additionalHeaderData['keq-js-ui-draggable'] = '<script src="'.t3lib_extMgm::siteRelPath('ke_questionnaire').'res/jquery/jquery.ui.draggable.min.js" type="text/javascript"></script>';
		$GLOBALS['TSFE']->additionalHeaderData['keq-js-ui-droppable'] = '<script src="'.t3lib_extMgm::siteRelPath('ke_questionnaire').'res/jquery/jquery.ui.droppable.min.js" type="text/javascript"></script>';
		$GLOBALS['TSFE']->additionalHeaderData['keq-js-ui-dd-words'] = '
	        <script type="text/javascript">	
				$(document).ready(function() {
				$("select#keq_' . $this->question['uid'] . '").hide();
				$("div.keq-moveable").draggable({
					revert: true,
					helper: "clone",
					opacity: 0.7
				});
	
				$("div.keq-placeholder").droppable({
					activeClass: "keq-possible",
					hoverClass: "keq-hover",
					accept: ":not(.ui-sortable-helper)",
					drop: function( event, ui ) {
						if($(".keq-moveable:contains(" + $(this).text() + ")").length) {
							$( "div.keq-moveable3" ).draggable( "disable" );
							answerIdOld = $(".keq-moveable:contains(" + $(this).text() + ")").attr("id").replace(/keq-moveable/g, "");
							alert(answerIdOld);
							$("#keq-moveable" + answerIdOld).show();
							$("select#keq_' . $this->question['uid'] . ' option[value=" + answerIdOld + "]").attr("selected", false);
						}
						answerIdNew = $(".keq-moveable:contains(" + ui.draggable.text() + ")").attr("id").replace(/keq-moveable/g, "");
						placeholderId = $(this).attr("id").replace(/keq-placeholder/g, "");

						//$("#keq-moveable" + answerIdNew).hide();
						
						// Set only if answer is correct
						if(answerIdNew != placeholderId) {
							$("select#keq_' . $this->question['uid'] . ' option[value=" + answerIdNew + "]").attr("selected", true);						
						}

						$(this).text(ui.draggable.text());
					}
				});
			});
			</script>
		';

		// create the answers for each question
		// create also hidden fields to save the answer
		$i = 2;
		$dropAreas = '';
		foreach($this->answers as $key => $value) {
			$value['text'] = strip_tags($value['text']);
			$answers .= $this->cObj->wrap($value['text'], '<div style="z-index: 10'.$i.';" id="keq-moveable' . $key . '" class="keq-moveable">|</div>');
			$markerArray['###WORD_' . strtoupper($value['text']) . '###'] = '<span id="keq-placeholder' . $key . '" class="keq-placeholder">Add the correct word here</span>';
			$coord['top'] = explode(',',$value['coordtop']);
			$coord['bottom'] = explode(',',$value['coordbottom']);
			//t3lib_div::debug($coord);
			$dropAreas .= '<div id="keq-placeholder' . $key . '" class="keq-placeholder" style="z-index: '.$i.'; position: absolute; top: '.$coord['top'][1].'px; left: '.$coord['top'][0].'px;height: '.($coord['bottom'][1]-$coord['top'][1]).'px; width: '.($coord['bottom'][0]-$coord['top'][0]).'px; border: 1px solid red;">&nbsp;TEST&nbsp;</div>';
			$i++;
		}
		
		
		$this->question['text'] = $this->cObj->substituteMarkerArray($this->question['text'], $markerArray);
		
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
