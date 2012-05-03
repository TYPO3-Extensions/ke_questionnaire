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

class question_dd_pictures extends question {

	var $templateName = 'question_dd_pictures.html'; //Name of default Templatefile

	/**
	 * The initiation method of the PlugIn
	 *
	 * @param	array		$conf: The plugin configuration
	 * @param	object		$parent: parent-Object
	 * @param	array		$answer: prefill Values for answers id/text
	 *
	 */
	public function base_init($uid){
		$this->options = array();
		$where = 'question_uid = ' . $uid;
		$where .= ' AND sys_language_uid = ' . $GLOBALS['TSFE']->sys_language_uid;
		$where .= $this->cObj->enableFields('tx_kequestionnaire_answers');
		$answers = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows(
			'*',
			'tx_kequestionnaire_answers',
			$where,
			'','sorting'
		);

		foreach($answers as $answer){
			$answer = $this->processRTEFields($answer, 'tx_kequestionnaire_answers');
			$this->options[$answer['uid']] = $answer;
			$this->answers[$answer['uid']] = $answer;
		}
	}


	/**
	 * Defines all fields in Template
	 */
	public function buildFieldArray(){
		$dropAreas = $this->renderPlaceholders();
		$answers = $this->renderAnswers();

		$imgSource = $this->renderImage(
			array(
				'title' => $this->question['title'],
				'image' => $this->question['image'],
				'image_position' => $this->question['image_position']
			)
		);

		$this->fields['ddarea'] = new kequestionnaire_input(
			'text',
			'ddarea',
			array('ddimage' => $imgSource, 'dropareas' => $dropAreas),
			'###DDAREA###',
			$this
		);

		$this->fields['checkboxes'] = new kequestionnaire_input(
			'text',
			'blind',
			array('text' => $checkboxes),
			'###CHECKBOXES###',
			$this
		);

		$this->fields[] = new kequestionnaire_input(
			'text',
			'ddpicture',
			array('ddpic' => $answers,'additional_js' => $additional_js),
			'###DDPICTURES###',
			$this
		);

		// put JS to header
		$GLOBALS['TSFE']->additionalHeaderData['keq-js-core'] = '<script src="'.t3lib_extMgm::siteRelPath('ke_questionnaire').'res/jquery/jquery-1.5.1.min.js" type="text/javascript"></script>';
		$GLOBALS['TSFE']->additionalHeaderData['keq-js-ui'] = '<script src="'.t3lib_extMgm::siteRelPath('ke_questionnaire').'res/jquery/jquery-ui-1.8.11.custom.min.js" type="text/javascript"></script>';
		//t3lib_div::debug($js_rendered,'rendered');
		$GLOBALS['TSFE']->register['kequestionnaire'][$this->question['uid']] = $this->renderJs();

		//t3lib_div::devLog('buildFieldArray', 'ke_questionnaire', -1, array($this->fields, $markerArray));
	}

	function getMaxitemsForArea( $area ){
		$items = 0;

		foreach ($this->answers as $answer){
			if ($answer['answerarea'] == $area) $items ++;
		}

		return $items;
	}

	/**
	 * render a html part by a given subpart name
	 * if the subpartname is not valid return an empty string
	 *
	 * @param string $subpartName The marker name of the subpart to render
	 * @param array $markerArray An Array containing markers to replace within the subpart
	 * @return string the rendered html
	 */
	public function renderSubpart($subpartName, array $markerArray = array()) {
		// check if subpartName is a string and not an array. Further check if the name starts with ###
		if(is_string($subpartName) && t3lib_div::isFirstPartOfStr($subpartName, '###')) {
			$subpart = $this->cObj->getSubpart($this->tmpl, $subpartName);
			$content = $this->cObj->substituteMarkerArrayCached($subpart, $markerArray);
			return $content;
		} else return '';
	}


	/**
	 * render a subpart for each placeholder
	 * one placeholder = one row in coords
	 *
	 * @return string The rendered html
	 */
	public function renderPlaceholders() {
		$coords = $this->getCoords();

		foreach($coords as $rowId => $row) {
			$markerArray = array();
			foreach($this->answers as $answer) {
				if($answer['answerarea'] == $rowId) {
					$markerArray['###ANSWERID###'] = $answer['uid'];
				}
			}
			$markerArray['###ID###'] = $this->question['uid'];
			$markerArray['###ROW###'] = $rowId;
			$markerArray['###PH_TOP###'] = $row['start']['top'];
			$markerArray['###PH_LEFT###'] = $row['start']['left'];
			$markerArray['###PH_HEIGHT###'] = $row['end']['top'] - $row['start']['top'];
			$markerArray['###PH_WIDTH###'] = $row['end']['left'] - $row['start']['left'];

			$content .= $this->renderSubpart('###PLACEHOLDER###', $markerArray);
		}
		return $content;
	}


	/**
	 * render a subpart for each answer
	 *
	 * @return array One rendered answer for each array entry
	 */
	public function renderAnswers() {
		$answer = array();
		foreach($this->answers as $answer) {
			$conf['file'] = 'uploads/tx_kequestionnaire/' . $answer['image'];
			$conf['altText'] = $answer['title'];
			$markerArray = array();
			$markerArray['###ID###'] = $answer['answerarea'];
			$markerArray['###IMG###'] = $this->cObj->IMAGE($conf);
			$size = getimagesize($conf['file']);
			$markerArray['###WIDTH###'] = $size[0];
			$markerArray['###HEIGHT###'] = $size[1];

			$renderedAnswer[] = $this->renderSubpart('###ANSWER_PICTURE###', $markerArray);
		}
		return $renderedAnswer;
	}


	/**
	 * get coords from answer
	 *
	 * @TODO Maybe it's good to call this method from an initialization method once
	 * @return array Array containing the defined coords
	 */
	public function getCoords() {
		$coordRow = t3lib_div::trimExplode(CHR(10), $this->question['coords']);
		foreach($coordRow as $keyRow => $row) {
			$coordParts = t3lib_div::trimExplode('|', $row);
			foreach($coordParts as $keyPart => $part) {
				$keyPart = ($keyPart === 0) ? 'start' : 'end';
				$positions = t3lib_div::trimExplode(':', $part);
				$coords[($keyRow + 1)][$keyPart]['left'] = $positions[0];
				$coords[($keyRow + 1)][$keyPart]['top'] = $positions[1];
			}
		}
		return $coords;
	}

	/**
	 * render JS for all placeholders and answers
	 *
	 * @return string the rendered html
	 */
	public function renderJs(){
		$subpartPic = $this->cObj->getSubpart($this->tmpl,"###DD_PICTURES_JAVASCRIPT###");
		$markerArray['###ID###'] = $this->question['uid'];
		$out = $this->cObj->substituteMarkerArrayCached($subpartPic, $markerArray);
		return $out;
	}


	/**
	 * render Image
	 *
	 * @param array image configuration
	 * @return string HTML-Code with IMG
	 */
	public function renderImage($data=array()){
		if($data['image']) {
			$imgConf['file'] = 'uploads/tx_kequestionnaire/' . $data['image'];
			$imgConf['altText'] = $data['title'];
			return $this->cObj->IMAGE($imgConf);
		} else {
			return '';
		}
	}

	/**
	 * Selects Subpartname depending on Qustiontype
	 *
	 * @return the whole question ready rendered
	 *
	 */
	function getTemplateName(){
		return "QUESTION_DD_PICTURES";
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

	/**
	 * get simple Answer-String
	 */
	public function getSimpleAnswer(){
		$saveA = $this->getSaveArray();
		$saveA = $saveA[$this->uid];

		$answer =  '';

		if (is_array($saveA['answer']['options'])){
			foreach ($saveA['answer']['options'] as $option){
				if ($answer != ''){
					$answer .= ', ';
				}
				//t3lib_div::debug($option);
				$answer .= $saveA['possible_answers'][$option];
				if (is_array($saveA['answer']['text'])){
					$text = $saveA['answer']['text'][$option];
					if ($text != '') $answer .= ' ('.$text.')';
				}
			}
		} else {
			$option = $saveA['answer']['options'];
			$answer .= $saveA['possible_answers'][$option];
		}
		//t3lib_div::debug($saveA);

		return $answer;
	}
}
?>