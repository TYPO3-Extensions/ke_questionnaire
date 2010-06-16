<?php
/**
 * Base Questions Class
 *
 * Class for the  'ke_questionnaire' extension.
 *
 * @author	Nadine Schwingler <schwingler@kennziffer.com>
 * @package	TYPO3
 * @subpackage	tx_kequestionnaire
 * */

require_once(t3lib_extMgm::extPath('ke_questionnaire').'res/questions/class.kequestionnaire_input.php');
require_once(t3lib_extMgm::extPath('ke_questionnaire').'res/questions/class.question_open.php');
require_once(t3lib_extMgm::extPath('ke_questionnaire').'res/questions/class.question_closed.php');
require_once(t3lib_extMgm::extPath('ke_questionnaire').'res/questions/class.question_matrix.php');
require_once(t3lib_extMgm::extPath('ke_questionnaire').'res/questions/class.question_semantic.php');
require_once(t3lib_extMgm::extPath('ke_questionnaire').'res/questions/class.question_demographic.php');
require_once(t3lib_extMgm::extPath('ke_questionnaire').'res/questions/class.question_privacy.php');
require_once(t3lib_extMgm::extPath('ke_questionnaire').'res/questions/class.question_blind.php');

if (t3lib_extMgm::isLoaded('ke_questionnaire_premium')){
    require_once(t3lib_extMgm::extPath('ke_questionnaire_premium').'question_includes.php');
}


//XAJAX
require_once (t3lib_extMgm::extPath('xajax') . 'class.tx_xajax.php');


class question{
    var $conf           = array();              //configuration
    var $extKey         = 'ke_questionnaire';	// The extension key.
    var $debug 		= TRUE;					// Debug Flag
    var $template    	= '';                  	//Template of the Question
    var $tmpl 	  		= '';                 	//Template of the Question
    var $tmplInput 	  	= '';                 	//Template for Input
    var $values		= array();		//Values for prefilling the answer
    var $fields		= array();		//Fields to render
    var $xajax;

    var $uid 		= 0;			//Question-ID
    var $question	= array();		//Table-Fields of the Question
    var $subquestions	= array();		//Questions for matrix
    var $columns	= array();		//Columns for matrix
    var $dependancies   = array();		//Dependancies for this Question
    var $dependants     = array();		//Dependant question from this Question
    var $answers	= array();		//Answers and their table-fields
    var $validateInput	=	0;		//Validate the User Input?

    var $error		= false;		//Errorflag
    var $errorMsg	= '';			//Errormessage
    var $errorFields 	= array();
    
	/**
	 * The initiation method of the PlugIn
	 *
	 * @param	array		$conf: The plugin configuration
	 * @param	object		$parent: parent-Object
	 * @param	array		$answer: prefill Values for answers id/text
	 *
	 */
	function init($uid,$parent,$answer,$validateInput=0,$errorClass="error",$dateFormat="m-d-y",$numberDivider=".",$addOptions=array()){
		$this->conf = $parent->conf;
		$this->cObj = $parent->cObj;
		$this->obj=$parent;
		$this->validateInput=$validateInput;
		$this->dateFormat=$dateFormat;
		$this->numberDivider=$numberDivider;
		$this->errorClass=$errorClass;
		$this->addOptions=$addOptions;
		$this->answers=array();
		//t3lib_div::devLog('dateFormat '.$uid, 'questions', 0, array($dateFormat,$this->dateFormat));

		if ($GLOBALS['TYPO3_CONF_VARS']['SYS']['compat_version'] > 4.1) {
		    $this->answer = $this->removeXSSFromAnswer($answer);
		} else {
		    $this->answer = $answer;
		}
		if(!isset($this->answer["options"])) $this->answer["options"]=array();

		$this->prefixId = $parent->prefixId;
		$this->uid=$uid;

		// Debug
		$GLOBALS['TYPO3_DB']->debugOutput=$this->debug;


		// getRecord	
		$where = "uid=".$uid .$this->cObj->enableFields('tx_kequestionnaire_questions');
		$res=$GLOBALS["TYPO3_DB"]->exec_SELECTgetRows("*", "tx_kequestionnaire_questions", $where,'','sorting');
		
		foreach($res as $row){
			$row=$this->processRTEFields($row,"tx_kequestionnaire_questions");
			foreach($row as $name => $value){
			    $this->question[$name] = isset($addOptions["question"][$name])?$addOptions["question"][$name]:$value;
			}
		}
		$this->type=$this->question["closed_type"];

		//t3lib_div::devLog('test '.$uid, 'questions', 0, array('test'));
		
		// Dependancies
		$this->getDependancies();
		
		// Dependants
		$this->getDependants();
		
		//Get other tables
		//look into the type-classes and base_init-section
		$this->base_init($this->uid);

		// Prepare Template
		//check if there is given a template, or use the standard
		if ($this->obj->tmpl_path == ''){
		    $this->template = t3lib_extMgm::siteRelPath('ke_questionnaire').'/res/templates/'.$this->templateName;
		} else {
		    $this->template = $this->obj->tmpl_path.$this->templateName;
		}

		// Read Subparts
		$this->tmpl = $this->cObj->fileResource($this->template);
		
		//Template not found in base extension? Check premium!
		if($this->tmpl == '' && t3lib_extMgm::isLoaded('ke_questionnaire_premium')) {
		     $this->template = t3lib_extMgm::siteRelPath('ke_questionnaire_premium').'/res/templates/'.$this->templateName;
		     $this->tmpl = $this->cObj->fileResource($this->template);
		}
		
		$mainSubpartName=$this->getTemplateName();
		$this->tmplMain=$this->cObj->getSubpart($this->tmpl,$mainSubpartName);
		$this->tmplFields=$this->cObj->getSubpart($this->tmplMain,"###FIELDS###");
		$this->tmplHelp = $this->cObj->fileResource($this->obj->tmpl_path.'helpbox.html');

		// Prepare Fields
		$this->buildFieldArray();
		$this->generateFieldTemplates();

		//start Xajax
		$this->XAJAX_start();
	}
	
	/**
	 * The initiation method of the PlugIn
	 *
	 */
	function base_init($uid){
	    //To be defined in the lower classes
	}
	
	function getDependants($uid = 0, $l18n_parent = 0){
	    if ($uid == 0) $uid = $this->question['uid'];
	    if ($l18n_parent == 0) $l18n_parent = $this->question['l18n_parent'];
	    if ($uid != 0){
		$where = "activating_question=".$uid .$this->cObj->enableFields('tx_kequestionnaire_dependancies');
		$res=$GLOBALS["TYPO3_DB"]->exec_SELECTgetRows("*", "tx_kequestionnaire_dependancies", $where,'','sorting');
		//t3lib_div::devLog('where', 'input', 0, array($where));
		foreach($res as $row){
		    $this->dependants[$row["uid"]]=$row;
		}
		
		if (count($this->dependants) == 0){
		    $where = "activating_question=".$l18n_parent .$this->cObj->enableFields('tx_kequestionnaire_dependancies');
		    $res=$GLOBALS["TYPO3_DB"]->exec_SELECTgetRows("*", "tx_kequestionnaire_dependancies", $where,'','sorting');
		    //t3lib_div::devLog('res', 'question class', 0, array($GLOBALS["TYPO3_DB"]->SELECTquery("*", "tx_kequestionnaire_dependancies", $where,'','sorting')));
		    foreach($res as $row){
			$this->dependants[$row['uid']] = $row;
		    }
		}
	    }
	    //t3lib_div::devLog('this->dependants '.$this->question['title'], 'question class', 0, $this->dependants);
	}
	
	function getDependancies($uid = 0, $l18n_parent = 0){
	    if ($uid == 0) $uid = $this->question['uid'];
	    if ($l18n_parent == 0) $l18n_parent = $this->question['l18n_parent'];
	    if ($uid != 0){
		$where = "dependant_question=".$uid .$this->cObj->enableFields('tx_kequestionnaire_dependancies');
		$res = $GLOBALS["TYPO3_DB"]->exec_SELECTgetRows("*", "tx_kequestionnaire_dependancies", $where,'','sorting');
		foreach($res as $row){
		    $this->dependancies[$row["uid"]]=$row;
		}
		
		if (count($this->dependancies) == 0){
		    $where = "dependant_question=".$l18n_parent .$this->cObj->enableFields('tx_kequestionnaire_dependancies');
		    $res = $GLOBALS["TYPO3_DB"]->exec_SELECTgetRows("*", "tx_kequestionnaire_dependancies", $where,'','sorting');
		    //t3lib_div::devLog('res', 'question class', 0, array($GLOBALS["TYPO3_DB"]->SELECTquery("*", "tx_kequestionnaire_dependancies", $where,'','sorting')));
		    foreach($res as $row){
			$this->dependancies[$row['uid']] = $row;
		    }
		}
	    }
	    //t3lib_div::devLog('this->dependancies '.$this->question['title'], 'question class', 0, $this->dependancies);
	}

	function removeXSSFromAnswer($answer){
		if(!is_array($answer)){
			return t3lib_div::removeXSS($answer);
		}

		$out=array();
		foreach($answer as $key=>$val){
			switch($key){
				case "fe_users":
				case "text":
				case "tt_address":
					$out[$key]=$this->removeXSS($val);
				break;
				case "options":
					if(!is_array($val)){
						$out[$key]=$val;break;
					}

					foreach($val as $keyOption=>$valOption){
						$out[$key][$keyOption]=$this->removeXSS($valOption);
					}
				break;

				default:
					$out[$key]=$val;
			}
		}


		return $out;
	}

	function removeXSS($answer){

		if(!is_array($answer)) return t3lib_div::removeXSS($answer);
		$out=array();
		foreach($answer as $key=>$val){
			if (!is_array($val)) $out[$key]=t3lib_div::removeXSS($val);
			else {
			    foreach ($val as $vkey => $vval){
				if (is_array($vval)){
				    foreach ($vval as $vvkey => $vvval){
					$out[$key][$vkey][$vvkey]=t3lib_div::removeXSS($vvval);
				    }
				} else {
				    $out[$key][$vkey]=t3lib_div::removeXSS($vval);
				}
			    }
			}
		}
		return $out;
	}

	/**
	 * Generates Templates for all Fields
	 *
	 * @return	Complete HMTL Template with Markers
	 *
	 */

	function generateFieldTemplates(){
		foreach($this->fields as $key=>$field){
			$tmpl = $this->cObj->getSubpart($this->tmplFields,$field->subpart);
			$this->fields[$key]->tmpl=$tmpl;
			$this->fields[$key]->tmplHead=$this->cObj->getSubpart($this->tmpl,"###HEAD###");
			$this->fields[$key]->tmplError=$this->cObj->getSubpart($tmpl,"ERROR_MESSAGE");
		}
	}

    /**
	 * generate Standard-Marker-Array
	 *
	 * @return	Prefilled Marker-Array,divided into Input-Array and Marker-Array
	 *
	 */

    function generateMarkerArray(){
		$out=array();

		// General
		$out['###PI###'] = $this->prefixId;
		$out['###NAME###'] = $this->uid;
		$out['###TEXT###'] = $this->question['text'];
		
		// dependancy related
		$out['###DEPENDANT###'] = '';
		$out['###DEPENDANT_STYLE###'] = '';
		if ($this->dependancies){
		    if (!$this->checkDependancies()){
			if ($this->question['dependant_show'] != 1){
			    $out['###DEPENDANT_STYLE###'] = 'style="display:none"';
			} else {
			    $out['###DEPENDANT_STYLE###'] = '
			    style="
			    zoom:1;
			    opacity:.50;
			    -ms-filter:alpha(opacity = 50);
			    filter:alpha(opacity = 50);
			    -khtml-opacity:.50;
			    -moz-opacity:.50;
			    "';
			    $out['###DEPENDANT###'] = 'disabled="true"';
			}
		    }
		}

		// dependant related
		$out['###DEPENDANT_AJAX###'] = '';

		// Open
		$out['###OPEN_PRE_TEXT###'] = $this->question['open_pre_text'];
		$out['###OPEN_POST_TEXT###'] = $this->question['open_post_text'];

		// Closed
		$out['###CLOSED_INPUT###'] = $this->question['closed_inputfield'];
		$out['###CLOSED_SIZE###'] = $this->question['closed_selectsize']>0?"size='".$this->question['closed_selectsize']."'":"";
		
		// Privacy
		if($this->question['privacy_link']!="") $out['###PRIVACY_LINK###'] = $this->obj->pi_getPageLink($this->question['privacy_link']);
		if($this->question['privacy_file']!="") $out['###PRIVACY_LINK###'] = $this->obj->pi_getPageLink("uploads/tx_kequestionnaire/".$this->question['privacy_file']);
		$out['###PRIVACY_TEXT###'] =$this->question['privacy_post'];

		// Title
		$out['###TITLE###'] = '';
		if ($this->question['show_title'] == 1) $out['###TITLE###'] = $this->question['title'];
		if ($this->question['text'] == "") $out['###TITLE###'] = $this->question['title'];

		// Help in q-Template
		$out['###HELPIMAGE###'] = t3lib_extMgm::siteRelPath('ke_questionnaire').'/res/images/helpbubble.gif';
		$out['###HELPTEXT###'] = $this->question['helptext'];
		
		// Help in separate Template
		$out['###HELPBOX###'] = '';
		if ($this->question['helptext'] != ''){
		    $h_out = array();
		    $h_out['###HELPIMAGE###'] = t3lib_extMgm::siteRelPath('ke_questionnaire').'/res/images/helpbubble.gif';
		    $h_out['###HELPTEXT###'] = $this->question['helptext'];
		    $h_out['###Q_ID###'] = $this->question['uid'];
		    $h_out_subpart = $this->cObj->getSubpart($this->tmplHelp,'###HELPBOX_QUESTION###');
		    $h_out_content = $this->cObj->substituteMarkerArrayCached($h_out_subpart, $h_out, array(), array());
		    $out['###HELPBOX###'] = trim($h_out_content);
		}

		// Error
		$out['###ERRORCLASS###'] = $this->error?$this->errorClass:"";

		return $out;

	}
    /**
	 * Processes RTE fields in a row depending on table TCA
	 *
	 * @return	processed row
	 *
	 */

	function processRTEFields($row,$table){
		t3lib_div::loadTCA($table);
		$TCA = $GLOBALS["TCA"][$table];

		$out=array();
		foreach($row as $col=>$val){
			$rte=isset($TCA["columns"][$col]["config"]["wizards"]["RTE"]);
			if ($rte){
			    $temp_val = str_replace('&nbsp;','',$val);
			    $temp_val = str_replace('<br />','',$temp_val);
			    if (trim($temp_val) != '') $out[$col] = $this->obj->pi_RTEcssText($val);
			    else $out[$col] = '';
			} else {
			    $out[$col] = $val;
			}
		}
		//t3lib_div::devLog('question row', 'questions', 0, $row);
		//t3lib_div::devLog('question out', 'questions', 0, $out);
		return $out;
	}

    /**
	 * Checks if the question dependancies are fullfilled
	 * true if question is activated, false if not
	 *
	 * @return	true or false
	 *
	 */

	function checkDependancies(){
		$content = false;

		//t3lib_div::devLog('check dependancies '.$this->question['uid'], 'question base class', 0, $this->dependancies);
		//t3lib_div::devLog('check parent->piVars '.$this->question['uid'], 'question base class', 0, $this->obj->piVars);

		//if the dependancy is simple
		$simple = $this->question['dependancy_simple'];
		
		$need = count ($this->dependancies);
		$is = 0;
		foreach ($this->dependancies as $key => $dependancy){
		    $activating_q = $this->getActivatingQuestion($dependancy['activating_question']);
		    //t3lib_div::devLog('dependancy '.$this->question['uid'].'/'.$key, 'question base class', 0, $dependancy);
		    //if ($dependancy['sys_language_uid'] != $GLOBALS['TSFE']->sys_language_uid OR $act_q['sys_language_uid'] != $GLOBALS['TSFE']->sys_language_uid){
		    if ($activating_q['sys_language_uid'] != $GLOBALS['TSFE']->sys_language_uid){
			$whereq = 'l18n_parent='.$dependancy['activating_question'] .' AND sys_language_uid='.$GLOBALS['TSFE']->sys_language_uid.$this->obj->cObj->enableFields('tx_kequestionnaire_questions');
			$resq = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*','tx_kequestionnaire_questions',$whereq);
			//t3lib_div::devLog('resq', 'question base class', 0, array($GLOBALS['TYPO3_DB']->SELECTquery('*','tx_kequestionnaire_questions',$whereq)));
			if ($resq){
			    $act_q = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($resq);
			    $dependancy['activating_question'] = $act_q['uid'];
			}
			$wherea = 'l18n_parent='.$dependancy['activating_value'] .' AND sys_language_uid='.$GLOBALS['TSFE']->sys_language_uid.$this->obj->cObj->enableFields('tx_kequestionnaire_answers');
			$resa = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*','tx_kequestionnaire_answers',$wherea);
			if ($resa){
			    $act_a = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($resa);
			    $dependancy['activating_value'] = $act_a['uid'];
			}
			//echo 'test';
		    }
		    //t3lib_div::devLog('dependancy '.$this->question['uid'].'/'.$key, 'question base class', 0, $dependancy);
		    $check_against = $this->obj->piVars[$dependancy['activating_question']]['options'];
		    //t3lib_div::devLog('check_against '.$this->question['uid'].'/'.$key, 'question base class', 0, array($check_against));
		    if (is_array($check_against)){
			if (in_array($dependancy['activating_value'],$check_against)) $is++;
		    } else {
			if ($this->obj->piVars[$dependancy['activating_question']]['options'] == $dependancy['activating_value']){
			    $is ++;
			}
		    }
		    /*$options = $this->obj->piVars[$dependancy['activating_question']]['options'];
		    if (is_array($options)){
			//t3lib_div::devLog('options', 'question base class', 0, $options);
			foreach ($options as $value){
			    if ($value == $dependancy['activating_value']){
				$is ++;
			    }
			}
		    } else {
			if ($this->obj->piVars[$dependancy['activating_question']]['options'] == $dependancy['activating_value']){
			    $is ++;
			}
		    }*/
		}
		//t3lib_div::devLog('need/is', 'question base class', 0, array($need,$is));
		if ($need == $is) $content = true;
		if ($simple == 1 AND $is > 0) $content = true;

		return $content;
	}

    function getSpecialJS(){
	return '';
    }

    function getActivatingQuestion($uid){
	$where = 'uid='.$uid;
	$where .= $this->obj->cObj->enableFields('tx_kequestionnaire_questions');
	$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*','tx_kequestionnaire_questions',$where);
	$q = array();
	if ($res){
	    $q = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);   
	}
	return $q;
    }

    /**
	 * The render method of the Question-Class
	 *
	 * @return	The question to be displayed in the questionnaire
	 *
	 */
    function render(){

		if($this->validateInput) $this->validate();


		$this->markerArray=$this->generateMarkerArray();

		$this->htmlFields=$this->renderFields();
		$out = $this->renderQuestion();
		return $out;
    }
    /**
	 * Render HTML for each Field
	 *
	 *
	 */

    function renderFields(){
		$out=array();
		$odd=0;
		foreach($this->fields as $key=>$field){

			if (!$this->checkDependancies()) {
			    $this->fields[$key]->value = FALSE;
			}
			$marker=$field->subpart=="###HEAD###"?"head":"fields";
			//#############################################
			// KENNZIFFER Nadine Schwingler 03.11.2009
			// Anpassung Title Line
			if ($this->fields[$key]->type != 'matrix_title_line'){
			    $this->fields[$key]->odd=$odd; $odd=!$odd;
			}
			//#############################################
			$render_temp = $this->fields[$key]->render();
			if ($this->fields[$key]->closed_onchange) {
			    $out['closed_onchangejs'] .= $this->fields[$key]->closed_onchange;
			}
			$out[$marker].= $render_temp;
		}
		return $out;
	}

    /**
	 * Render the Content in the Template
	 *
	 * @param       array     	$markerArray: to fill the template
	 * @param       array      	$inputMarkerArray: to fill the subpart
	 * @return      the whole question ready rendered
	 *
	 */
    function renderQuestion(){
		$subpartHelp=$this->cObj->getSubpart($this->tmplMain,"###HELP###");
		$subpartError=$this->cObj->getSubpart($this->tmplMain,"###ERROR_MESSAGE###");
		$subpartError=str_replace("###ERROR###",$this->errorMsg,$subpartError);

		$subpartArray=array(
			"###FIELDS###"=>$this->htmlFields["fields"],
			"###HEAD###"=>$this->htmlFields["head"],
			"###HELP###"=>$this->question["helptext"]==""?"":$subpartHelp,
			"###ERROR_MESSAGE###"=>$this->error?$subpartError:"",

		);
		//t3lib_div::devLog('this->question '.$this->question['uid'], 'question', 0, $this->question);
		//t3lib_div::devLog('this->dependants '.$this->question['uid'], 'question', 0, $this->dependants);
		//t3lib_div::devLog('this->dependancies '.$this->question['uid'], 'question', 0, $this->dependancies);
		//#############################################
		// KENNZIFFER Nadine Schwingler 09.11.2009
		// For Javascript to deactivate the textboxes
		$replace_rbjs = '';
		if ($this->htmlFields['closed_onchangejs']){
		    $replace_onchangejs = 'onchange="'.$this->htmlFields['closed_onchangejs'].'"';
		}
		$subpartArray['###FIELDS###'] = str_replace("###ONCHANGE_JS###",$replace_onchangejs,$subpartArray['###FIELDS###']);
		//#############################################
		//#############################################
		// KENNZIFFER Nadine Schwingler 19.04.2010
		// Show the Images
		$subpartArray['###IMG_LEFT###'] = '';
		$subpartArray['###IMG_RIGHT###'] = '';
		$subpartArray['###IMG_TOP###'] = '';
		$subpartArray['###IMG_BOTTOM###'] = '';
		if ($this->question['image']){
		    $img_path = 'uploads/tx_kequestionnaire/';
		    $img_first = '<img alt="'.$this->question['title'].'" src="';
		    $img_last = '" />';
		    $img = '';
		    $img = $img_first.$img_path.$this->question['image'].$img_last;
		    $subpartArray['###IMG_'.strtoupper($this->question['image_position']).'###'] = $img;
		}
		//t3lib_div::devLog('subpartArray '.$this->question['uid'], 'question', 0, $subpartArray);
		//#############################################

		$out = $this->cObj->substituteMarkerArrayCached($this->tmplMain, array(), $subpartArray);
		$out = $this->cObj->substituteMarkerArrayCached($out, $this->markerArray);

		foreach($this->obj->LOCAL_LANG["default"] as $key=>$val){
			$val=$this->obj->pi_getLL($key);
			$out=str_replace("###".strtoupper($key)."###",$val,$out);
		}
		return $out;
    }

    // #################################################
    // KENNZIFFER Nadine Schwingler 18.09.2009
    // XAJAX Section

    /**
	 * Init the Xajax-Support
	 *
	 */
    function XAJAX_start() {
	// Make the instance
	$this->xajax = t3lib_div::makeInstance('tx_xajax');
	// Decode form vars from utf8
	$this->xajax->decodeUTF8InputOn();
	// Encoding of the response to utf-8.
	$this->xajax->setCharEncoding('utf-8');
	// To prevent conflicts, prepend the extension prefix.
	$this->xajax->setWrapperPrefix('ke_questionnaire_');
	// Do you want messages in the status bar?
	$this->xajax->statusMessagesOn();
	// Turn only on during testing
	$this->xajax->debugOff();
	// Register the names of the PHP functions you want to be able to call through xajax
	$this->xajax->registerFunction(array('checkDependants', &$this, 'checkDependants'));

	// If this is an xajax request call our registered function, send output and exit
	$this->xajax->processRequest();
	 // Else create javacript and add it to the normal output
	$this->obj->addHeaderData['question_xajax'] = $this->xajax->getJavascript(t3lib_extMgm::siteRelPath('xajax'));
	//$GLOBALS['TSFE']->additionalHeaderData[$this->prefixId] = $this->xajax->getJavascript(t3lib_extMgm::siteRelPath('xajax'));
    }
    
    /**
    * XAJAX-Function checkDependants
    *
    * @param	int	Question-ID to be activated
    */
    function checkDependants($dependant_id,$question_id,$answer_id,$form_values){
	//t3lib_div::devLog('form_values '.$dependant_id, 'checkDependants', 0, $form_values);
	$objResponse = new tx_xajax_response();
	$activating_types = array();
	$activating_values = array();
	$check_against = array();
	$activate = false;

	//Check all the dependancies the dependant question is based on
	//TODO: rewrite the function for localisation. If for the act. localized version no dep. are set the original dep. should act.
	$where = 'dependant_question='.$dependant_id;
	$where .= ' AND hidden=0 AND deleted=0';
	$where .= ' AND sys_language_uid='.$GLOBALS['TSFE']->sys_language_uid;
	$res_all_activating = $GLOBALS['TYPO3_DB']->exec_SELECTquery('activating_question','tx_kequestionnaire_dependancies',$where);
	if ($res_all_activating){
	    while ($question_row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res_all_activating)){
		$selectFields = 'uid,activating_value,activating_formula';
		$res_questiontype = $GLOBALS['TYPO3_DB']->exec_SELECTquery('type','tx_kequestionnaire_questions','uid='.$question_id);
		if ($res_questiontype){
		    $question_type = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res_questiontype);
		    $question_type = $question_type['type'];
		}
		$res_dependants = $GLOBALS['TYPO3_DB']->exec_SELECTquery($selectFields,'tx_kequestionnaire_dependancies','activating_question='.$question_row['activating_question'].' AND dependant_question='.$dependant_id.' AND hidden=0 AND deleted=0');
		//t3lib_div::devLog('res_dependant '.$dependant_id, 'checkDependants', 0, array($GLOBALS['TYPO3_DB']->SELECTquery($selectFields,'tx_kequestionnaire_dependancies','activating_question='.$question_row['activating_question'].' AND dependant_question='.$dependant_id.' AND hidden=0 AND deleted=0')));
		if ($res_dependants){
		    while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res_dependants)){
			$activating_values[$question_row['activating_question']][] = $row['activating_value'];
			$activating_formulas[$question_row['activating_question']][] = $row['activating_formula'];
			$activating_types[$question_row['activating_question']] = $question_type;
		    }
		}
	    }
	}
	//t3lib_div::devLog('activating_types', 'checkDependants', 0, $activating_types);
	//t3lib_div::devLog('activating_values', 'checkDependants', 0, $activating_values);
	//t3lib_div::devLog('activating_formulas', 'checkDependants', 0, $activating_formulas);

	//if the dependancy is simple
	$selectFields = 'type,dependancy_simple,dependant_show';
	$res_question = $GLOBALS['TYPO3_DB']->exec_SELECTquery($selectFields,'tx_kequestionnaire_questions','uid='.$dependant_id);
	if ($res_question){
	    $question = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res_question);
	    $simple = $question['dependancy_simple'];
	    $dependant_show = $question['dependant_show'];
	    //t3lib_div::devLog('question', 'checkDependants', 0, $question);
	}

	//foreach activating question
	$a = 0;
	foreach($activating_values as $q_id => $a_values){
	    if ($activating_types[$q_id] == 'closed'){
		//get the values from the form
		$check_against = $form_values[$this->obj->prefixId][$q_id]['options'];
		//t3lib_div::devLog('check_against '.$q_id, 'checkDependants', 0, array($a_values, $check_against));
		//check if all the needed values are given
		if (is_array($check_against)) {
		    if ($simple == 0){
			$check = array_diff($a_values,$check_against);
			//if (count($check)==0) $activate = true;
			if (count($check)==0) $a++;
			//else $activate = false;
			//t3lib_div::devLog('check '.count($check), 'checkDependants', 0, $check);
		    } else {
			foreach ($a_values as $nr){
			    if (in_array($nr,$check_against)) $a++;
			}
		    }
		} else {
		    //if ($check_against == $a_values[0]) $activate = true;
		    foreach ($a_values as $a_value){
			if ($check_against == $a_value) $a++;
		    }
		    //else $activate = false;
		}
	    } elseif ($activating_types[$q_id] == 'open') {
		//t3lib_div::devLog('activating_formulas '.$q_id, 'checkDependants', 0, $activating_formulas[$q_id]);
		$split = explode(',',$activating_formulas[$q_id][0]);
		$formula_type = $split[0];
		$formula_value = $split[1];
		//t3lib_div::devLog('split ', 'checkDependants', 0, $split);
		switch ($formula_type){
		    case '=':
			    if (intval($form_values[$this->obj->prefixId][$q_id]['text']) == $formula_value) $a ++;
			break;
		    case '<':
			    if (intval($form_values[$this->obj->prefixId][$q_id]['text']) < $formula_value) $a ++;
			break;
			    if (intval($form_values[$this->obj->prefixId][$q_id]['text']) > $formula_value) $a ++;
		    case '>':
			break;
		}
	    }
	}
	//t3lib_div::devLog('question', 'checkDependants', 0, array($checker,$a));
	$checker = count($activating_values);
	if ($checker == $a) $activate = true;
	if ($simple == 1 AND $a > 0) $activate = true;

	switch ($question['type']){
	    case 'closed':
		//get all the answers of the dependant_question
		$answers = array();
		$res_answers = $GLOBALS['TYPO3_DB']->exec_SELECTquery('uid','tx_kequestionnaire_answers','question_uid='.$dependant_id.' AND hidden=0 AND deleted=0');
		if ($res_answers){
		    while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res_answers)){
			$answers[] = $row['uid'];
		    }
		    $answer_count = count($answers);
		    //t3lib_div::devLog('answers '.$dependant_id, 'checkDependants', 0, $answers);
		}
		break;
	    case 'matrix':
		//get all the subquestions of the dependant_question
		$subquestions = array();
		$res_subquestions = $GLOBALS['TYPO3_DB']->exec_SELECTquery('uid','tx_kequestionnaire_subquestions','question_uid='.$dependant_id.' AND hidden=0 AND deleted=0');
		//t3lib_div::devLog('subquestions '.$dependant_id, 'checkDependants', 0, array($GLOBALS['TYPO3_DB']->SELECTquery('uid','tx_kequestionnaire_subquestions','question_uid='.$dependant_id.' AND hidden=0 AND deleted=0')));
		if ($res_subquestions){
		    while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res_subquestions)){
			$subquestions[] = $row['uid'];
		    }
		    $subquestion_count = count($subquestions);
		    //t3lib_div::devLog('subquestions '.$dependant_id, 'checkDependants', 0, $subquestions);
		}
		//get all the columns of the dependant_question
		$columns = array();
		$res_columns = $GLOBALS['TYPO3_DB']->exec_SELECTquery('uid','tx_kequestionnaire_columns','question_uid='.$dependant_id.' AND hidden=0 AND deleted=0');
		//t3lib_div::devLog('columns '.$dependant_id, 'checkDependants', 0, array($GLOBALS['TYPO3_DB']->SELECTquery('uid','tx_kequestionnaire_columns','question_uid='.$dependant_id.' AND hidden=0 AND deleted=0')));
		if ($res_columns){
		    while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res_columns)){
			$columns[] = $row['uid'];
		    }
		    $column_count = count($columns);
		    //t3lib_div::devLog('columns '.$dependant_id, 'checkDependants', 0, $columns);
		}
		break;
	    case 'semantic':
		//get all the subquestions of the dependant_question
		$sublines = array();
		$res_sublines = $GLOBALS['TYPO3_DB']->exec_SELECTquery('uid','tx_kequestionnaire_sublines','question_uid='.$dependant_id.' AND hidden=0 AND deleted=0');
		//t3lib_div::devLog('subquestions '.$dependant_id, 'checkDependants', 0, array($GLOBALS['TYPO3_DB']->SELECTquery('uid','tx_kequestionnaire_subquestions','question_uid='.$dependant_id.' AND hidden=0 AND deleted=0')));
		if ($res_sublines){
		    while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res_sublines)){
			$sublines[] = $row['uid'];
		    }
		    $sublines_count = count($sublines);
		    //t3lib_div::devLog('subquestions '.$dependant_id, 'checkDependants', 0, $subquestions);
		}
		//get all the columns of the dependant_question
		$columns = array();
		$res_columns = $GLOBALS['TYPO3_DB']->exec_SELECTquery('uid','tx_kequestionnaire_columns','question_uid='.$dependant_id.' AND hidden=0 AND deleted=0');
		//t3lib_div::devLog('columns '.$dependant_id, 'checkDependants', 0, array($GLOBALS['TYPO3_DB']->SELECTquery('uid','tx_kequestionnaire_columns','question_uid='.$dependant_id.' AND hidden=0 AND deleted=0')));
		if ($res_columns){
		    while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res_columns)){
			$columns[] = $row['uid'];
		    }
		    $column_count = count($columns);
		    //t3lib_div::devLog('columns '.$dependant_id, 'checkDependants', 0, $columns);
		}
		break;
	}
	//t3lib_div::devLog('subs and cols', 'checkDependants', 0, array($subquestions,$columns));

	if ($activate){
	    switch ($question['type']){
		case 'open': $objResponse->addAssign('keq_'.$dependant_id,"disabled", false);
		    break;
		case 'closed':
		    foreach ($answers as $a_id){
			$objResponse->addAssign('keq_'.$dependant_id.'_'.$a_id,"disabled", false);
		    }
		    break;
		case 'matrix':
		    foreach ($subquestions as $sub){
			foreach ($columns as $col){
			    $objResponse->addAssign('keq_'.$dependant_id.'_'.$sub.'_'.$col,"disabled", false);
			}
			$objResponse->addAssign('keq_'.$dependant_id.'_'.$sub,"disabled", false);
		    }
		    break;
		case 'semantic':
		    foreach ($sublines as $sub){
			foreach ($columns as $col){
			    $objResponse->addAssign('keq_'.$dependant_id.'_'.$sub.'_'.$col,"disabled", false);
			}
		    }
		    break;
	    }
	    if ($dependant_show == 1){
		$objResponse->addAssign('question_'.$dependant_id,"style.zoom", '');
	        $objResponse->addAssign('question_'.$dependant_id,"style.opacity", '');
		$objResponse->addAssign('question_'.$dependant_id,"style.-ms-filter", '');
		$objResponse->addAssign('question_'.$dependant_id,"style.filter", '');
		$objResponse->addAssign('question_'.$dependant_id,"style.-khtml-opacity", '');
		$objResponse->addAssign('question_'.$dependant_id,"style.-moz-opacity", '');
		//t3lib_div::devLog('question style.opacity', 'checkDependants', 0, $question);
	    } else {
		$objResponse->addAssign('question_'.$dependant_id,"style.display", '');
		//t3lib_div::devLog('question style.display', 'checkDependants', 0, $question);
	    }
	} else {
	    switch ($question['type']){
		case 'open': $objResponse->addAssign('keq_'.$dependant_id,"disabled", true);
		    break;
		case 'closed':
		    foreach ($answers as $a_id){
			$objResponse->addAssign('keq_'.$dependant_id.'_'.$a_id,"disabled", true);
		    }
		    break;
		case 'matrix':
		    foreach ($subquestions as $sub){
			foreach ($columns as $col){
			    $objResponse->addAssign('keq_'.$dependant_id.'_'.$sub.'_'.$col,"disabled", true);
			}
			$objResponse->addAssign('keq_'.$dependant_id.'_'.$sub,"disabled", true);
		    }
		    break;
		case 'semantic':
		    foreach ($sublines as $sub){
			foreach ($columns as $col){
			    $objResponse->addAssign('keq_'.$dependant_id.'_'.$sub.'_'.$col,"disabled", true);
			}
		    }
		    break;
	    }
	    if ($dependant_show == 1){
		$objResponse->addAssign('question_'.$dependant_id,"style.zoom", '1');
		$objResponse->addAssign('question_'.$dependant_id,"style.opacity", '.50');
		$objResponse->addAssign('question_'.$dependant_id,"style.-ms-filter", 'alpha(opacity = 50)');
		$objResponse->addAssign('question_'.$dependant_id,"style.filter", 'alpha(opacity = 50)');
		$objResponse->addAssign('question_'.$dependant_id,"style.-khtml-opacity", '.50');
		$objResponse->addAssign('question_'.$dependant_id,"style.-moz-opacity", '.50');
	    } else {
		$objResponse->addAssign('question_'.$dependant_id,"style.display", 'none');
	    }
	}
	//return the  xajaxResponse object
	return $objResponse->getXML();
    }

    // #################################################


    ##############################
    ## UNUSED BY NOW
    ##############################

    /**
     * get the save-String for the question and values for the save-array
     *
     * @param	int	$timestamp: Time the Question is answered
     * @return	The Save String
     *
     */
    function getSaveString($timestamp = ''){
	$content = '';
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

	$content .= '<'.$this->question['uid'].'>'."\n";
	$content .= '	question:'.$this->question['text']."\n";
	$content .= '	question_id:'.$this->question['uid']."\n";
	$i = 0;
	if (count($this->answers) > 0){
	    $content .= '	possible_answers:'."\n";
	    foreach ($this->answers as $nr => $answer){
		$i ++;
		//$content .= '		<'.$answer['uid'].'>';
		$content .= '		<'.$i.'>';
		$content .= '			'.$answer['text'];
		$content .= '		</'.$i.'>';
		//$content .= '		<'.$answer['uid'].'>';
	    }
	}
	$content .= '	answer:'.$this->answer['text']."\n";
	$content .= '	time:'.$timestamp."\n";
	$content .= '</'.$this->question['uid'].'>'."\n";

	return $content;
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
		$saveArray[$this->question['uid']]['possible_answers'][$nr] = $text;
	    }
	}
	// for Matrix
	if (count($this->subquestions) > 0){
	    $saveArray[$this->question['uid']]['possible_answers']['lines'] = array();
	    foreach ($this->subquestions as $nr => $subquestion){
		if ($subquestion['title_line'] == 0){
		    $text=($subquestion['text'] != '')?$subquestion['text']:$subquestion['title'];
		    $saveArray[$this->question['uid']]['possible_answers']['lines'][$nr] = $text;
		}
	    }
	}
	// for Semantic
	if (count($this->sublines) > 0){
	    $saveArray[$this->question['uid']]['possible_answers']['lines'] = array();
	    foreach ($this->sublines as $nr => $subline){
		$saveArray[$this->question['uid']]['possible_answers']['lines'][$nr]['start'] = $subline['start'];
		$saveArray[$this->question['uid']]['possible_answers']['lines'][$nr]['end'] = $subline['end'];
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
	if (is_array($this->answer['fe_users'])){
	    //t3lib_div::devLog('fe_users '.$this->question['uid'], 'class.question', 0, $this->answer['fe_users']);
	    //if (is_array($this->answer['options'])) $saveArray[$this->question['uid']]['answer'] = t3lib_div::array2xml($this->answer);
	    $saveArray[$this->question['uid']]['answer'] = $this->answer;
	    //if (count($saveArray[$this->question['uid']]['answer']['options']) == 0) unset($saveArray[$this->question['uid']]['answer']['options']);
	}
	$saveArray[$this->question['uid']]['time'] = mktime();
	// t3lib_div::debug($saveArray,"saveArray");
	// t3lib_div::debug($this->columns,"columns");

	return $saveArray;
    }
}

?>
