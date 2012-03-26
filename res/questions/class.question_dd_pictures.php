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
	var $templateName           = 'question_dd_pictures.html';              //Name of default Templatefile

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
 		// check if frong answers should be maked as wrong
		if($this->question['ddarea_drop_once']) {
 			$jsForWrongAnswers = '
 				$("#keq-ddarea-checkbox" + answerId[0]).css("backgroundColor", "#DD0000");
 				$("#keq-ddarea-moveable" + answerId[0] + "-" + answerId[1]).fadeOut();
 			';
 		}

		// get coords of answerareas
		$coordRow = t3lib_div::trimExplode("\n", $this->question['coords']);
		foreach($coordRow as $keyRow => $row) {
			$coordParts = t3lib_div::trimExplode("|", $row);
			foreach($coordParts as $keyPart => $part) {
				$coords[($keyRow + 1)][$keyPart] = t3lib_div::trimExplode(":", $part);
			}
		}
		
		$js_markerArray = array();
                $js_markerArray['###ID###'] = $this->question['uid'];
		$js_rendered = $this->renderJs($js_markerArray);
		$js_rendered .= $this->renderJsRecycle($js_markerArray);

		// create the answers for each question
		// create also hidden fields to save the answer
		$i = 0;
		$count = count($this->answers);
		$dropAreas = '';
		$dropJs = '';
		//t3lib_div::debug($this->answer,'answer');
                $answers = array();
		$additional_js = '';
		foreach($this->answers as $key => $value) {
			//$answers .= $this->cObj->wrap($value['text'], '<div style="z-index: 10' . ($count - $i) . ';" id="keq-ddarea-moveable' . $key . '-' . $value['answerarea'] . '" class="keq-ddarea-moveable">|</div>');
			$conf['file'] = 'uploads/tx_kequestionnaire/' . $value['image'];
			$conf['altText'] = $value['title'];
			//$conf['wrap'] = '<div style="z-index: 10' . ($count - $i) . ';" id="keq-ddarea-moveable-' . $key . '-' . $value['answerarea'] . '" class="keq-ddarea-moveable">|</div>';
			$img_markerArray=array();
			$img_markerArray['###ID###'] = $key;
			$img_markerArray['###IMG###'] = $this->cObj->IMAGE($conf);
			$size = getimagesize($conf['file']);
			$img_markerArray['###WIDTH###'] = $size[0];
			$img_markerArray['###HEIGHT###'] = $size[1];
			//$answers[] = $this->cObj->IMAGE($conf);
			$answers[] = $this->renderAnswer($img_markerArray);
			//$checkboxes .= '<div id="keq-ddarea-checkbox' . $key . '" class="keq-ddarea-checkbox">&nbsp;</div>';

			// there can be more answers than areas
			// don't make more areas than needed
			$area_markerArray = array();
			$area_markerArray['###ID###'] = $this->question['uid'];
			$area_markerArray['###DISABLE_DRAGGABLE###'] = '';
			$area_markerArray['###MAXITEMS###'] = 0;
			if($this->question['ddarea_maxitems'] == 1) $area_markerArray['###MAXITEMS###'] = $this->getMaxitemsForArea($value['answerarea']);
			if($this->question['ddarea_onetry'] == 1) $area_markerArray['###DISABLE_DRAGGABLE###'] = '$item.draggable({disabled:true});';
			$area_markerArray['###AREA###'] = $value['answerarea'];
			$area_markerArray['###AREA_TOP###'] = $coords[$value['answerarea']][0][1];
			$area_markerArray['###AREA_LEFT###'] = $coords[$value['answerarea']][0][0];
			$area_markerArray['###AREA_HEIGHT###'] = ($coords[$value['answerarea']][1][1] - $coords[$value['answerarea']][0][1]);
			$area_markerArray['###AREA_WIDTH###'] = ($coords[$value['answerarea']][1][0] - $coords[$value['answerarea']][0][0]);
			$dropAreas[$value['answerarea']] = $this->renderArea($area_markerArray);
			
			//$dropJs[$value['answerarea']] = $this->renderAreaJs($area_markerArray);
			//check given answer
			if (is_array($this->answer['options'])){
				foreach ($this->answer['options'] as $a_area => $a_answers){
					if (in_array($value['uid'],$a_answers)) {
						$add_markerArray['###AREA###'] = $a_area;
						$add_markerArray['###ID###'] = $this->question['uid'];
						$add_markerArray['###ITEM_ID###'] = $value['uid'];
						//do somethin so the answers will be rendered where they should
						$additional_js .= $this->renderAdditionalJs($add_markerArray);
					}
				}
			}
		}
                
                $dropAreas = implode('', $dropAreas);
		//$js_rendered .= implode('', $dropJs);

		$checkboxes = $checkboxes . '<div style="clear: left;"></div>';

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
		$GLOBALS['TSFE']->register['kequestionnaire'][$this->question['uid']] = $js_rendered;

		//t3lib_div::devLog('buildFieldArray', 'ke_questionnaire', -1, array($this->fields, $markerArray));
	}
	
	function getMaxitemsForArea( $area ){
		$items = 0;
		
		foreach ($this->answers as $answer){
			if ($answer['answerarea'] == $area) $items ++;
		}
		
		return $items;
	}
	
	function renderJs($markerArray=array()){
                $subpartJs = $this->cObj->getSubpart($this->tmpl,"###DD_PICTURES_JAVASCRIPT###");
                //t3lib_div::debug($subpartJs,'js');
                //t3lib_div::debug($this->tmpl,'js');
                $out = $this->cObj->substituteMarkerArrayCached($subpartJs, $markerArray);
                
                return $out;
        }
	
	function renderAdditionalJs($markerArray=array()){
                $subpartJs = $this->cObj->getSubpart($this->tmpl,"###PRE_SELECT_ANSWER_JS###");
                //t3lib_div::debug($subpartJs,'js');
                //t3lib_div::debug($this->tmpl,'js');
                $out = $this->cObj->substituteMarkerArrayCached($subpartJs, $markerArray);
                
                return $out;
        }
	
	function renderJsRecycle($markerArray=array()){
		$subpartPic = $this->cObj->getSubpart($this->tmpl,"###DD_PICTURES_RECYCLE_JAVASCRIPT###");
                //t3lib_div::debug($subpartJs,'js');
                //t3lib_div::debug($this->tmpl,'js');
                $out = $this->cObj->substituteMarkerArrayCached($subpartPic, $markerArray);
                
                return $out;
	}
	
	function renderAnswer($markerArray=array()){
		$subpartPic = $this->cObj->getSubpart($this->tmpl,"###ANSWER_PICTURE###");
                //t3lib_div::debug($subpartJs,'js');
                //t3lib_div::debug($this->tmpl,'js');
                $out = $this->cObj->substituteMarkerArrayCached($subpartPic, $markerArray);
                
                return $out;
	}
	
	function renderArea($markerArray=array()){
		$subpartPic = $this->cObj->getSubpart($this->tmpl,"###DD_AREA###");
                //t3lib_div::debug($subpartJs,'js');
                //t3lib_div::debug($this->tmpl,'js');
                $out = $this->cObj->substituteMarkerArrayCached($subpartPic, $markerArray);
                
                return $out;
	}

	function renderImage($data=array()){
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
	 * @return      the whole question ready rendered
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
	 *
	 */
	function getSimpleAnswer(){
		$saveA = $this->getSaveArray();
		$saveA = $saveA[$this->uid];
		
		$answer =  '';
		
		if (is_array($saveA['answer']['options'])){
			foreach ($saveA['answer']['options'] as $area => $answers){
				foreach ($answers as $option){
					if ($answer != ''){
						$answer .= ', ';
					}
					//t3lib_div::debug($option);
					$answer .= $saveA['possible_answers'][$area][$option];
				}
			}
		}
		//t3lib_div::debug($saveA);
	   
		return $answer;
        }
	
	/**
	 * get the save-array for the question and values
	 *
	 * @param	int	$timestamp: Time the Question is answered
	 * @return	The Save String
	 *
	 */
	function getSaveArray($timestamp = ''){
		$saveArray = array();
		/**
		 * basic save-structure for the xml-array to store
		 * <questionn_id>
		 * 	question: ???
		 * 	question_id: id
		 * 	possible_answers:
		 * 		<answer_nr> 123 </answer_nr>
		 * 		<answer_nr> 234 </answer_nr>
		 * 	answer: 123
		 * 	time:
		 * </frage_id>
		*/
		if ($this->checkDependancies()){
			$saveArray[$this->question['uid']] = array();
			$saveArray[$this->question['uid']]['question'] = $this->question['text'];
			if ($this->question['text'] == '')$saveArray[$this->question['uid']]['question'] = $this->question['title'];
			$saveArray[$this->question['uid']]['question_id'] = $this->question['uid'];
			$saveArray[$this->question['uid']]['type'] = $this->question['type'];
			$saveArray[$this->question['uid']]['subtype'] = $this->type;
		
			if (count($this->answers) > 0){
				//t3lib_div::debug($this->answers,"answers");
				$saveArray[$this->question['uid']]['possible_answers'] = array();
				foreach ($this->answers as $nr => $answer){
					//t3lib_div::devLog('answer '.$this->question['uid'], 'class.question', 0, $answer);
					$i ++;
					$text=($answer['text'] != '')?$answer['text']:$answer['title'];
					$saveArray[$this->question['uid']]['possible_answers'][$answer['answerarea']][$nr] = $text;
				}
			}
			
			//t3lib_div::devLog('answer '.$this->question['uid'], 'class.question', 0, $this->answer);
			if ($this->answer['text']) $saveArray[$this->question['uid']]['answer'] = $this->answer['text'];
			if (is_array($this->answer['options']) AND count($this->answer['options']) > 0) {
				//t3lib_div::devLog('options '.$this->question['uid'], 'class.question', 0, $this->answer['options']);
				//if (is_array($this->answer['options'])) $saveArray[$this->question['uid']]['answer'] = implode(',',$this->answer['options']);
				//if (is_array($this->answer['options'])) $saveArray[$this->question['uid']]['answer'] = t3lib_div::array2xml($this->answer['options']);
				//if (is_array($this->answer['options'])) $saveArray[$this->question['uid']]['answer'] = $this->answer['options'];
				//else
				$saveArray[$this->question['uid']]['answer'] = $this->answer;
			} elseif (!is_array($this->answer['options']) AND $this->answer['options']){
				$saveArray[$this->question['uid']]['answer'] = $this->answer;
			}
			
			$saveArray[$this->question['uid']]['time'] = mktime();
			//t3lib_div::debug($saveArray,"saveArray");
		}
	
		return $saveArray;
	}

}

?>
