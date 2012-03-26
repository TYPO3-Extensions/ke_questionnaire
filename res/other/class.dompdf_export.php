<?php
/*
 * PDF Export Class for ke_questionnaire
 *
 * Copyright (C) 2010 kennziffer.com / Nadine Schwingler
 * All rights reserved.
 * License: GNU/GPL License
 *
 * $Id$
 *
 */

//require_once(t3lib_extMgm::extPath('fpdf').'class.tx_fpdf.php');
require_once(t3lib_extMgm::extPath('ke_dompdf') . 'res/dompdf/dompdf_config.inc.php');
require_once(PATH_tslib . 'class.tslib_content.php'); // load content file
require_once(t3lib_extMgm::extPath('ke_questionnaire') . 'pi1/class.tx_kequestionnaire_pi1.php');

class dompdf_export {
	var $conf = array();      //Basis PDF Conf
	var $pdf = '';            //PDF-Objekt
	var $pid = 0;             //Pid of data Storage
	var $ffdata = '';
	var $templateFolder = '';
	var $title = '';
	var $templates = array();
	var $result = array();

	var $cellHeight = 0;      //Base-Definition Cell Height
	var $cellWidth = array(); //Base-Definition Cell Width

	var $questions = array();  //Question-array
	var $user_marker = array();//array for user-marker

	/**
	 * dompdf_export(): initialisation of the export object
	 *
	 * @param       array   $conf: configuration data
	 * @param       int     $pid: Page Id of the questionnaire Data
	 * @param       string  $title: title of the export
	 * @param       array   $ffdata: flexform data of the questionnaire
	 */
	public function dompdf_export($conf, $pid, $title, $ffdata){
		spl_autoload_register('DOMPDF_autoload');
		$this->title = $title;
		$this->ffdata = $ffdata;
		$this->pid = $pid;
		$this->conf = $conf;

		$this->templateFolder = trim(PATH_site . ltrim($this->ffdata['dDEF']['lDEF']['template_dir']['vDEF'], '/'));
		if (ltrim($this->ffdata['dDEF']['lDEF']['template_dir']['vDEF'], '/') == '') {
			$this->templateFolder = PATH_site . t3lib_extMgm::siteRelPath('ke_questionnaire').'res/templates/';
		}
		//t3lib_div::devLog('lang temp', 'DOMPDF Export', 0, array($this->templateFolder));

		$this->pdf = new DOMPDF();

		//get the Locallang of the pi1 / the questionnaire
		$basePath = t3lib_extMgm::extPath('ke_questionnaire').'pi1/locallang.php';
		
		//t3lib_div::devLog('conf', 'DOMPDF Export', 0, $this->conf);
		$lang = $this->conf['language'];
		$tempLOCAL_LANG = t3lib_div::readLLfile($basePath,$lang);
		//t3lib_div::devLog('lang temp', 'DOMPDF Export', 0, $tempLOCAL_LANG);
		//array_merge with new array first, so a value in locallang (or typoscript) can overwrite values from ../locallang_db
		$this->LOCAL_LANG = array_merge_recursive($tempLOCAL_LANG,is_array($this->LOCAL_LANG) ? $this->LOCAL_LANG : array());
		//if (is_array($this->LOCAL_LANG[$lang])) t3lib_div::devLog('lang temp '.$lang, 'DOMPDF Export', 0, $this->LOCAL_LANG[$lang]);
		if (count($this->LOCAL_LANG[$lang]) > 0) $this->LOCAL_LANG = $this->LOCAL_LANG[$lang];
		else $this->LOCAL_LANG = $this->LOCAL_LANG['default'];
		//t3lib_div::devLog('lang end', 'DOMPDF Export', 0, $this->LOCAL_LANG);
		
		//t3lib_div::devLog('ffdata', 'DOMPDF Export', 0, $this->ffdata);
	}

	/**
	 * getQuestions(): Gather all the questions of this questionnaire ready for showing
	 */
	function getQuestions(){
		$this->questionCount['total'] = 0; //total of questions
		$this->questionCount['only_questions'] = 0; //no blind-texts counting
		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
			'*',
			'tx_kequestionnaire_questions',
			'pid=' . $this->pid . ' AND hidden = 0 AND deleted = 0 AND sys_language_uid=' . intval($this->conf['sys_language_uid']),
			'', 'sorting', ''
		);
		//t3lib_div::devLog('where', 'pdf_export', 0, array($where));
		//t3lib_div::devLog('conf', 'pdf_export', 0, $this->conf);

		if ($res){
			while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)){
				//replace all drag and drop placeholder marks in question text (question type: dd_words) for export
				if($row['type'] === 'dd_words') {
					$replaceText = (TYPO3_MODE === 'BE')?'ZU_ERSETZENDES_WORT':$this->LOCAL_LANG['dd_words_replacetext'];
					if(preg_match('/###(.|\n)*?###/iu', $row['text']) === 1) {
						$row['text'] = preg_replace('/###(.|\n)*?###/iu', $replaceText, $row['text']);
					}
				}
				
				$this->allQuestions[] = $row;
				$this->questions[] = $row;
				$this->questionsByID[$row['uid']] = $row;
			}
		}

		$this->questionCount['only_questions'] = count($this->questions);
		$this->questionCount['total'] = count($this->allQuestions);

		if (is_array($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['ke_questionnaire']['dompdf_export_getQuestions'])){
			foreach($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['ke_questionnaire']['dompdf_export_getQuestions'] as $_classRef){
				$_procObj = & t3lib_div::getUserObj($_classRef);
				/*$hook_questions = $_procObj->dompdf_export_getQuestions($this);
				if (is_array($hook_questions)){
					$this->questions = $hook_questions;					
				}*/
				$_procObj->dompdf_export_getQuestions($this);
			}
		}

		//t3lib_div::devLog('questions', 'DOMPDF Export', 0, $this->questions);
	}
        
        /**
         * getOutcomes(): get the outcome-Data of the questionnaire (Point-Report)
         */
	function getOutcomes(){
		$selectFields = '*';
		$where = 'pid='.$this->pid.' AND hidden = 0 AND deleted = 0';
		$orderBy = 'sorting';
		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery($selectFields,'tx_kequestionnaire_outcomes',$where,'',$orderBy);
		while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)){
			$this->outcomes[] = $row;
		}

		if (is_array($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['ke_questionnaire']['dompdf_export_getOutcomes'])){
			foreach($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['ke_questionnaire']['dompdf_export_getOutcomes'] as $_classRef){
				$_procObj = & t3lib_div::getUserObj($_classRef);
				$hook_outcomes = $_procObj->dompdf_export_getOutcomes($this);
				if (is_array($hook_outcomes)) $this->outcomes = $hook_outcomes;
			}
		}

		//t3lib_div::devLog('outcomes', 'DOMPDF Export', 0, $this->outcomes);
	}
        
        /**
         * getOptions(): get the answer-options of the closed question
         *
         * @param       int     $uid: id of the question
         *
         * @return      array   answer-options of the question
         */
	function getOptions($uid){
		$options = array();

		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
			'*',
			'tx_kequestionnaire_answers',
			'question_uid=' . $uid . ' AND hidden = 0 AND deleted = 0',
			'', 'sorting', ''
		);
		if ($res) {
			while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)){
				$options[] = $row;
			}
		}
		return $options;
	}

        /**
         * getMatrixLines(): get the lines of the matrix
         *
         * @return      array   lines of the matrix
         */
	function getMatrixLines($uid){
		$lines = array();

		$selectFields = '*';
		$where = 'question_uid='.$uid.' AND hidden=0 AND deleted=0';
		$orderBy = 'sorting';
		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery($selectFields,'tx_kequestionnaire_subquestions',$where,'',$orderBy);
		if ($res){
			while ($row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)){
				$lines[] = $row;
			}
		}

		return $lines;
	}

        /**
         * getSematicLines(): get the lines of the semantic differential
         *
         * @return      array   lines
         */
	function getSemanticLines($uid){
		$lines = array();

		$selectFields = '*';
		$where = 'question_uid='.$uid.' AND hidden=0 AND deleted=0';
		$orderBy = 'sorting';
		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery($selectFields,'tx_kequestionnaire_sublines',$where,'',$orderBy);
		if ($res){
			while ($row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)){
				$lines[] = $row;
			}
		}

		return $lines;
	}

	/**
	 * getDependants(): Find the Questions type and get the question-Object
	 *
	 * @param       array   $question: array of question attributes
	 *
	 * @return      array   dependant questions of the given question
	 */
	function getDependants($question){
		$dependants = array();
		$uid = $question['uid'];
		if ($uid != 0){
		    $where = "activating_question=".$uid .' AND hidden=0 AND deleted=0';
		    $res=$GLOBALS["TYPO3_DB"]->exec_SELECTgetRows("*", "tx_kequestionnaire_dependancies", $where,'','sorting');
		    //t3lib_div::devLog('where', 'input', 0, array($where));
		    foreach($res as $row){
			$dependants[$row["uid"]]=$row;
		    }
		}

		return $dependants;
	}

        /**
         * getColums(): get the columns of a matrix or sematic differential
         *
         * @param:      int     $uid: id of the question
         *
         * @return      array   the columns
         */
	function getColumns($uid){
		$lines = array();

		$selectFields = '*';
		$where = 'question_uid='.$uid.' AND hidden=0 AND deleted=0';
		$orderBy = 'sorting';
		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery($selectFields,'tx_kequestionnaire_columns',$where,'',$orderBy);
		//t3lib_div::devLog('columns', $this->prefixId, 0, array($GLOBALS['TYPO3_DB']->SELECTquery($selectFields,'tx_kequestionnaire_columns',$where,'',$orderBy)));
		if ($res){
			while ($row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)){
				$lines[] = $row;
			}
		}

		return $lines;
	}

        /**
         * getPDFBlank(): get a pdf with the empty questionnaire
         *
         * @param       array   $result: result to be rendered into the pdf
         * @param       string  $date: date to be rendered at the top of the questionnaire
         */
	function getPDFBlank($result = array(), $date = ''){
		//t3lib_div::devLog('result', 'pdf_export', 0, $result);
		$this->result = $result;
		$this->getQuestions();
		$html = $this->getHTML('blank');
		//t3lib_div::devLog('html', 'pdf_export', 0, array($html));
		//$html ='test';
		$this->pdf->load_html($html);

		$this->pdf->render();
		$this->pdf->stream("questionnaire_".$this->pid.".pdf");

		//return $html;
	}

        /**
         * getPDFFilled(): get the pdf with the filled questionnaire
         *
         * @param       array   $result: result to be rendered into the pdf
         * @param       string  $date: date to be rendered at the top of the questionnaire
         */
	function getPDFFilled($result,$date = ''){
		$this->result = $result;
		$this->getQuestions();
		//t3lib_div::devLog('result', 'pdf_export', 0, $result);

		$html = $this->getHTML('filled',$date);

		$this->pdf->load_html($html);

		$this->pdf->render();
		$this->pdf->stream("questionnaire_".$this->pid.".pdf");
		//t3lib_div::devLog('html', 'pdf_export', 0, array($html));

		//return $html;
	}

        /**
         * getPDFCompare(): get the pdf filled and compared to the standard-answers
         *
         * @param       array   $result: result to be rendered and compared
         * @date        string  $date: date to be rendered at the top of the questionnaire
         */
	function getPDFCompare($result, $date=''){
		$this->result = $result;
		$this->getQuestions();
		//t3lib_div::devLog('result', 'pdf_export', 0, $result);

		$html = $this->getHTML('compare', $date);

		$this->pdf->load_html($html);

		$this->pdf->render();
		$this->pdf->stream('questionnaire_' . $this->pid . '.pdf');
		//t3lib_div::devLog('html', 'pdf_export', 0, array($html));

		//return $html;
	}

        /**
         * getPDFOutcomes(): get the pdf with the matched outcomes for the achieved points
         *
         * @param       array   $result: result the outcomes are rendered for
         */
	function getPDFOutcomes($result){
		$this->result = $result;
		$this->getQuestions();
		$this->getOutcomes();
		//t3lib_div::devLog('result', 'pdf_export', 0, $result);

		$html = $this->getHTML('outcomes');

		$this->pdf->load_html($html);

		$this->pdf->render();
		$this->pdf->stream("questionnaire_".$this->pid.".pdf");
		//t3lib_div::devLog('html', 'pdf_export', 0, array($html));

		//return $html;
	}

        /**
         * getHTML(): get the HMTL-Template for the PDF
         *
         * @param       string  $type: type of PDF to be rendered
         * @param       string  $date: date to be rendered into the pdf
         *
         * @return      string  html-content
         */
	function getHTML($type, $date='') {
		$content = '';
		//$content .= intval($this->conf['sys_language_uid']);

		$this->getTemplates();
		if ($date == '') $date = date('d.m.Y');
		switch ($type){
			case 'blank':
				$content .= $this->renderFirstPage();
				//t3lib_div::devLog('getHTML '.$type, 'pdf_export', 0,array($content));
				foreach ($this->questions as $nr => $question){
					//$content .= $question['title'];
					$content .= $this->renderQuestion($question,false,false);
				}
				//$content = mb_convert_encoding($content, "Windows-1252", "UTF-8");
			break;
			case 'filled':
				$content .= $this->renderFirstPage();
				foreach ($this->questions as $nr => $question){
					//t3lib_div::devLog('columns', $this->prefixId, 0, $question);
					$content .= $this->renderQuestion($question,false);
				}
			break;
			case 'compare':
				$content .= $this->renderFirstPage();
				foreach ($this->questions as $nr => $question){
					$content .= $this->renderQuestion($question, true);
				}
			break;
			case 'outcomes':
				$content .= $this->renderFirstPage();
				$content .= $this->renderOutcomes();
			break;
		}
		
		$html = str_replace('###CONTENT###',$content,$this->templates['base']);
		$html = str_replace('###PDF_TITLE###',$this->LOCAL_LANG['pdf_title'],$html);
		$html = str_replace('###DATE###',$date,$html);
		$html = str_replace('###QUESTIONNAIRE_NAME###',$this->title,$html);
		//t3lib_div::devLog('getHTML html '.$type, 'pdf_export', 0,array($html,$content,$this->templates['base']));

		$css = $this->getCSS();
		$html = str_replace('###CSS###',$css,$html);
		$html = str_replace('###BASE_PATH###',PATH_site,$html);
		
		$html = $this->renderContent($html,$this->user_marker);
		
		return $html;
	}

        /**
         * getTemplates(): get the templates for the pdf-rendering
         */
	function getTemplates(){
		$templateFolder = $this->templateFolder;

		//open questions
		$templateName = 'question_open.html';
		if (file_exists($templateFolder.$templateName)) $temp = file_get_contents($templateFolder.$templateName);
		//t3lib_div::devLog('open', 'pdf', 0, array($templateFolder.$templateName,$open));
		if ($temp == ''){
			$templateFolder = t3lib_extMgm::extPath('ke_questionnaire').'res/templates/';
			$temp = file_get_contents($templateFolder.$templateName);
		}
		$open_template = t3lib_parsehtml::getSubpart($temp, '###DOMPDF_SINGLE###');
		$this->templates['open_single'] = $open_template;
		$open_template = t3lib_parsehtml::getSubpart($temp, '###DOMPDF_MULTI###');
		$this->templates['open_multi'] = $open_template;
		$open_template = t3lib_parsehtml::getSubpart($temp, '###DOMPDF_COMPARE###');
		$this->templates['open_compare'] = $open_template;

		//closed questions
		$templateName = 'question_closed.html';
		$temp = file_get_contents($templateFolder.$templateName);
		$this->templates['closed'] = t3lib_parsehtml::getSubpart($temp, '###DOMPDF###');
		$this->templates['closed_options'] = t3lib_parsehtml::getSubpart($temp, '###DOMPDF_OPTION###');
		$this->templates['closed_compare'] = t3lib_parsehtml::getSubpart($temp, '###DOMPDF_COMPARE###');

		//drag 'n drop pictures
		$templateName = 'question_dd_pictures.html';
		$temp = file_get_contents($templateFolder.$templateName);
		$this->templates['dd_pictures'] = t3lib_parsehtml::getSubpart($temp, '###DOMPDF###');
		$this->templates['dd_pictures_options'] = t3lib_parsehtml::getSubpart($temp, '###DOMPDF_OPTION###');
		$this->templates['dd_pictures_compare'] = t3lib_parsehtml::getSubpart($temp, '###DOMPDF_COMPARE###');

		//drag 'n drop words
		$templateName = 'question_dd_words.html';
		$temp = file_get_contents($templateFolder.$templateName);
		$this->templates['dd_words'] = t3lib_parsehtml::getSubpart($temp, '###DOMPDF###');
		$this->templates['dd_words_options'] = t3lib_parsehtml::getSubpart($temp, '###DOMPDF_OPTION###');
		$this->templates['dd_words_compare'] = t3lib_parsehtml::getSubpart($temp, '###DOMPDF_COMPARE###');

	
		//semantic questions
		$templateName = 'question_semantic.html';
		$temp = file_get_contents($templateFolder.$templateName);
		$this->templates['semantic'] = t3lib_parsehtml::getSubpart($temp, '###DOMPDF###');
		$this->templates['semantic_line'] = t3lib_parsehtml::getSubpart($temp, '###DOMPDF_LINE###');
		$this->templates['semantic_column'] = t3lib_parsehtml::getSubpart($temp, '###DOMPDF_COLUMN###');
		$this->templates['semantic_compare'] = t3lib_parsehtml::getSubpart($temp, '###DOMPDF_COMPARE###');

		//matrix questions
		$templateName = 'question_matrix.html';
		$temp = file_get_contents($templateFolder.$templateName);
		$this->templates['matrix'] = t3lib_parsehtml::getSubpart($temp, '###DOMPDF###');
		$this->templates['matrix_line'] = t3lib_parsehtml::getSubpart($temp, '###DOMPDF_LINE###');
		$this->templates['matrix_column'] = t3lib_parsehtml::getSubpart($temp, '###DOMPDF_COLUMN###');
		$this->templates['matrix_compare'] = t3lib_parsehtml::getSubpart($temp, '###DOMPDF_COMPARE###');

		//blind questions
		$templateName = 'question_blind.html';
		$temp = file_get_contents($templateFolder.$templateName);
		$this->templates['blind'] = t3lib_parsehtml::getSubpart($temp, '###DOMPDF###');

		//demograhic questions
		$templateName = 'question_demographic.html';
		$temp = file_get_contents($templateFolder.$templateName);
		$this->templates['demographic'] = t3lib_parsehtml::getSubpart($temp, '###DOMPDF###');
		$this->templates['demographic_line'] = t3lib_parsehtml::getSubpart($temp, '###DOMPDF_LINE###');

		//privacy questions
		$templateName = 'question_privacy.html';
		$temp = file_get_contents($templateFolder.$templateName);
		$this->templates['privacy'] = t3lib_parsehtml::getSubpart($temp, '###DOMPDF###');

		//base
		$templateName = 'questionnaire.html';
		$temp = file_get_contents($templateFolder.$templateName);
		$this->templates['base'] = t3lib_parsehtml::getSubpart($temp, '###DOMPDF###');
		$this->templates['outcomes'] = t3lib_parsehtml::getSubpart($temp, '###DOMPDF_OUTCOMES###');

                //Hook to include other Templates for the pdf rendering
		if (is_array($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['ke_questionnaire']['dompdf_export_getTemplates'])){
			foreach($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['ke_questionnaire']['dompdf_export_getTemplates'] as $_classRef){
				$_procObj = & t3lib_div::getUserObj($_classRef);
				$this->templates = $_procObj->dompdf_export_getTemplates($this,$templateFolder,$this->templates);
			}
		}
		//t3lib_div::devLog('templates', 'pdf', 0, $this->templates);
	}

        /**
         * getCSS(): get the CSS needed for the right display of the pdf. Stored in a file 
         */
	function getCSS(){
		$css = '';

		$templateFolder = $this->templateFolder;
		$templateName = 'dompdf_template.css';
		if (file_exists($templateFolder.$templateName)) $temp = file_get_contents($templateFolder.$templateName);
		//t3lib_div::devLog('open', 'pdf', 0, array($templateFolder.$templateName,$open));
		if ($temp == ''){
			$templateFolder = t3lib_extMgm::extPath('ke_questionnaire').'res/templates/';
			$temp = file_get_contents($templateFolder.$templateName);
		}
		$css = $temp;

		return $css;
	}

	/**
	 * calculatePoints(): Calculate the points for the result
	 *
	 * @param       array   $result: result to be calculated
	 *
	 * @return      array   point values
	 */
	function calculatePoints($result){
		$returner = array();
		//t3lib_div::devLog('result', 'pdf_export', 0, $result);

		foreach ($this->questionsByID as $qid => $question){
			$temp .= $qid;
			$titles[] = $question['title'];
			$bars['total'][$qid] = 0;
			$bars['own'][$qid] = 0;
			$bars['titles'][$qid] = $question['title'];
			switch ($question['type']){
				case 'closed':
					$options = $this->getOptions($qid);
					//t3lib_div::devLog('result answers '.$question['title'], 'pdf_export', 0, $options);
					$answer_max_points = 0;
					foreach ($options as $answer){
						$answers[$answer['uid']]['points'] = $answer['value'];
						switch ($question['closed_type']){
							case 'radio_single':
							case 'sbm_button':
							case 'select_single':
								if ($answer['value']>$answer_max_points) $answer_max_points = $answer['value'];
								break;
							case 'check_multi':
							case 'select_multi':
								$answer_max_points += $answer['value'];
								break;
						}
					}

					//t3lib_div::devLog('result answers '.$question['title'], 'pdf_export', 0, $answers);
					switch ($question['closed_type']){
						case 'sbm_button':
						case 'radio_single':
						case 'select_single':
							$bars['own'][$qid] = intval($answers[$result[$qid]['answer']['options']]['points']);
							break;
						case 'check_multi':
						case 'select_multi':
							if (is_array($result[$qid]['answer']['options'])){
								foreach ($result[$qid]['answer']['options'] as $item){
									$bars['own'][$qid] += $answers[$item]['points'];
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
									$bars['own'][$qid] += $answers[$item]['points'];
								}
							}
						}
						// calculate average points
					}
					
					$own_total += $bars['own'][$qid];
					$max_points += $answer_max_points;
					break;
				case 'dd_pictures':
					$answers = array();
					$areas = array();
					// get all answers
					$where = 'question_uid='.$qid.$this->cObj->enableFields('tx_kequestionnaire_answers');
					$res_answers = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*','tx_kequestionnaire_answers',$where);
					$answer_max_points = 0;
					if ($res_answers){
						// create array with points of each answer
						while ($answer = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res_answers)){
							$answers[$answer['uid']]['points'] = $answer['value'];
							$areas[$answer['answerarea']][] = $answer['uid'];
							$answer_max_points += $answer['value'];
						}
					}
					
					// sum points of all answers of each question
					$total_points = 0;
					if (is_array($result[$qid]['answer']['options'])){
						foreach ($result[$qid]['answer']['options'] as $area => $areaitems){
							foreach ($areaitems as $item){
								if (in_array($item,$areas[$area])) $total_points += $answers[$item]['points'];
								$bars['own'][$qid] += $answers[$item]['points'];
							}
						}
					}
					
									
					$own_total += $bars['own'][$qid];
					$max_points += $answer_max_points;
					break;
			}
		}
		//t3lib_div::devLog('points bars', 'pdf_export', 0, $bars);
		$returner['percent'] = ($own_total/$max_points)*100;
		$returner['own'] = $own_total;
		$returner['max'] = $max_points;

		return $returner;
	}

        /**
         * renderOutcomes(): render the outcomes for the result
         *
         * @return      string outcome content
         */
	function renderOutcomes(){
		$content = '';
		$answers = $this->result;
		//t3lib_div::devLog('result', 'pdf_export', 0, $this->result);
		//t3lib_div::devLog('outcomes', 'pdf_export', 0, $this->outcomes);
		$points = $this->calculatePoints($this->result);
		//t3lib_div::devLog('points', 'pdf_export', 0, $points);
		foreach ($this->outcomes as $outcome){
			if ($outcome['type'] == 'dependancy' AND $outcome['uid'] != 0){
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
				foreach ($dependancies as $dep){
					$temp = '';
					foreach ($this->questions as $question){
						if ($question['uid'] == $dep['activating_question']){
							switch ($question['closed_type']){
								case 'radio_single':
									if ($answers[$dep['activating_question']]['answer']['options'] == $dep['activating_value']){
										$own_counter ++;
									}
									break;
								case 'check_multi':
									if (in_array($dep['activating_value'],$answers[$dep['activating_question']]['answer']['options'])){
										$own_counter ++;
									}
									break;
							}
						}
					}
				}
				$temp = '<div class="outcome">'.nl2br($outcome['text']).'</div>';
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
				if ($points['own'] >= $outcome['value_start'] AND $points['own'] < $outcome['value_end']) {
					$content .= '<div class="outcome">'.nl2br($outcome['text']).'</div>';
				}
			}
		}

		return $content;
	}

        /**
         * renderQuestion(): render the question
         *
         * @param       array   $question: question to be rendered
         * @param       bool    $compare: compare the question or not
         * @param       bool    $filled: show the result in the question or not
         *
         * @return      string  rendered question
         */
	function renderQuestion($question, $compare = false, $filled=true){
		$markerArray = array();
		$markerArray['###QUESTION_TITLE###'] = '';
		$markerArray['###QUESTION###'] = '';
		$markerArray['###COMPARE###'] = '';
		if ($compare) $markerArray['###COMPARE###'] = $this->renderCompare($question);
		$markerArray['###HELPTEXT###'] = $question['helptext'];

		if ($question['text'] == '') {
			$markerArray['###QUESTION_TITLE###'] = $question['title'];
		} else {
			if ($question['show_title'] == 1) {
				$markerArray['###QUESTION_TITLE###'] = $question['title'];
			}
			$markerArray['###QUESTION###'] = nl2br($question['text']);
		}
		$value = '&nbsp;';
		$markerArray['###VALUE###'] = $value;
		$answered = array();
		if (is_array($this->result) AND $filled) {
			if (is_array ($this->result[$question['uid']])){
				$answered = $this->result[$question['uid']]['answer'];
			}
		}
		//t3lib_div::devLog('answered', 'pdf_export', 0, array($answered));
		//t3lib_div::devLog('question', 'pdf_export', 0, $question);
		switch ($question['type']){
			case 'blind':
				$html = $this->renderContent($this->templates['blind'],$markerArray);
				break;
			case 'open':
				$markerArray['###OPEN_PRE_TEXT###'] = (strlen($question['open_pre_text']))?$question['open_pre_text']:'';
				$markerArray['###OPEN_POST_TEXT###'] = (strlen($question['open_post_text']))?$question['open_post_text']:'';
				
				if ($answered) $markerArray['###VALUE###'] = $answered;
				if ($question['open_type'] == 1){
					if ($answered) $markerArray['###VALUE###'] = nl2br($answered);
					$markerArray['###CLASS###'] = '';
					if (trim($answered) == '') $markerArray['###CLASS###'] = '_empty';
					$html = $this->renderContent($this->templates['open_multi'],$markerArray);
				} else {
					if ($answered) $markerArray['###VALUE###'] = $answered;
					$html = $this->renderContent($this->templates['open_single'],$markerArray);
				}
				break;
			case 'closed':
				$options = $this->getOptions($question['uid']);
				$markerArray['###OPTIONS###'] = '';
				foreach ($options as $option){
					$o_markerArray = array();
					$o_markerArray['###VALUE###'] = $value;
					$o_markerArray['###INPUT_TEXT###'] = '';
					if (is_array($answered['options'])){
						if (in_array($option['uid'],$answered['options'])){
							$o_markerArray['###VALUE###'] = 'X';
						}
					} else {
						if ($answered['options'] == $option['uid']) {
							$o_markerArray['###VALUE###'] = 'X';
						}
					}
					if (is_array($answered['text'])){
						if ($answered['text'][$option['uid']] != '') $o_markerArray['###INPUT_TEXT###'] = '['.$answered['text'][$option['uid']].']';
					}
					$text = $option['title'];
					if ($option['text'] != '') $text = $option['text'];
					$o_markerArray['###TEXT###'] = $text;
					$markerArray['###OPTIONS###'] .= $this->renderContent($this->templates['closed_options'],$o_markerArray);
				}
				$html = $this->renderContent($this->templates['closed'],$markerArray);
				break;
			case 'dd_pictures':
				$options = $this->getOptions($question['uid']);
				//t3lib_div::devLog('question', 'pdf_export', 0, $question);
				//t3lib_div::devLog('options', 'pdf_export', 0, $options);
				$markerArray['###DDIMAGE###'] = 'uploads/tx_kequestionnaire/' . $question['image'];
				$markerArray['###OPTIONS###'] = '';
				foreach ($options as $option){
					$o_markerArray = array();
					$o_markerArray['###AREA_ID###'] = '';
					$text = $option['title'];
					if ($option['text'] != '') $text = $option['text'];
					$o_markerArray['###TEXT###'] = $text;
					$o_markerArray['###IMAGE###'] = 'uploads/tx_kequestionnaire/' . $option['image'];
					if (is_array($answered['options'])){
						//t3lib_div::devLog('area '.$area, 'pdf_export', 0, $answered);
						foreach ($answered['options'] as $area => $aoptions){
							//t3lib_div::devLog('area '.$area. ' => ' .$option['uid'], 'pdf_export', 0, $aoptions);
							if (in_array($option['uid'],$aoptions)){
								$o_markerArray['###AREA_ID###'] = '=> '.$area;
							}
						}
					}
					$markerArray['###OPTIONS###'] .= $this->renderContent($this->templates['dd_pictures_options'],$o_markerArray);
				}
				$html = $this->renderContent($this->templates['dd_pictures'],$markerArray);
				break;
			case 'dd_words':
				$options = $this->getOptions($question['uid']);
				$markerArray['###OPTIONS###'] = '';
				foreach ($options as $option){
					$o_markerArray = array();
					$o_markerArray['###VALUE###'] = $value;
					$o_markerArray['###INPUT_TEXT###'] = '';
					if (is_array($answered['options'])){
						if (in_array($option['uid'],$answered['options'])){
							$o_markerArray['###VALUE###'] = 'X';
						}
					} else {
						if ($answered['options'] == $option['uid']) {
							$o_markerArray['###VALUE###'] = 'X';
						}
					}
					if (is_array($answered['text'])){
						if ($answered['text'][$option['uid']] != '') $o_markerArray['###INPUT_TEXT###'] = '['.$answered['text'][$option['uid']].']';
					}
					$text = $option['title'];
					if ($option['text'] != '') $text = $option['text'];
					$o_markerArray['###TEXT###'] = $text;
					$markerArray['###OPTIONS###'] .= $this->renderContent($this->templates['dd_words_options'],$o_markerArray);
				}
				$html = $this->renderContent($this->templates['dd_words'],$markerArray);
				break;
			case 'matrix':
				$html = $this->renderMatrixQuestion($question,$markerArray,$answered);
				break;
			case 'semantic':
				$html = $this->renderSemanticQuestion($question,$markerArray,$answered);
				break;
			case 'demographic':
				$html = $this->renderDemographicQuestion($question,$markerArray,$answered);
				break;
			case 'privacy':
				$markerArray['###PRIVACY_TEXT###'] = $question['privacy_post'];
				$markerArray['###VALUE###'] = 'X';
				$html = $this->renderContent($this->templates['privacy'],$markerArray);
				break;
			default:
				if (is_array($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['ke_questionnaire']['dompdf_export_renderQuestion'])){
					foreach($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['ke_questionnaire']['dompdf_export_renderQuestion'] as $_classRef){
						$_procObj = & t3lib_div::getUserObj($_classRef);
						$html = $_procObj->dompdf_export_renderQuestion($this,$markerArray,$question, $answered);
					}
				}
		}
		//$html .= '</div>';
		return $html;
	}

        /**
         * renderCompare(): render the compare content for the question
         *
         * @param       array   $question: question to be compared
         *
         * @return      string  rendered compare
         */
	function renderCompare($question){
		$content = '';
		$markerArray = array();

		$markerArray['###COMPARE_TITLE###'] = $this->LOCAL_LANG['pdf_compare_title'];
		switch ($question['type']){
			case 'open':
				if ($question['open_compare_text']){
					$markerArray['###CLASS###'] = '';
					if (trim($question['open_compare_text']) == '') $markerArray['###CLASS###'] = '_empty';
					$markerArray['###VALUE###'] = nl2br($question['open_compare_text']);
					$content .= $this->renderContent($this->templates['open_compare'],$markerArray);
				}
				break;
			case 'closed':
				$options = $this->getOptions($question['uid']);
				$markerArray['###OPTIONS###'] = '';
				foreach ($options as $option){
					$o_markerArray = array();
					$o_markerArray['###VALUE###'] = $value;
					$o_markerArray['###INPUT_TEXT###'] = '';
					if ($option['correct_answer']){
						$o_markerArray['###VALUE###'] = 'X';
					}
					$text = $option['title'];
					if ($option['text'] != '') $text = $option['text'];
					$o_markerArray['###TEXT###'] = $text;
					$markerArray['###OPTIONS###'] .= $this->renderContent($this->templates['closed_options'],$o_markerArray);
				}
				if ($markerArray['###OPTIONS###'] != '') $content .= $this->renderContent($this->templates['closed_compare'],$markerArray);
				break;
			case 'dd_pictures':
				$options = $this->getOptions($question['uid']);
				$markerArray['###DDIMAGE###'] = 'uploads/tx_kequestionnaire/' . $question['image'];
				$markerArray['###OPTIONS###'] = '';
				foreach ($options as $option){
					$o_markerArray = array();
					$o_markerArray['###AREA_ID###'] = '';
					$o_markerArray['###VALUE###'] = $value;
					$text = $option['title'];
					if ($option['text'] != '') $text = $option['text'];
					$o_markerArray['###TEXT###'] = $text;
					$o_markerArray['###IMAGE###'] = 'uploads/tx_kequestionnaire/' . $option['image'];
					$o_markerArray['###AREA_ID###'] = '=> '.$option['answerarea'];
					$markerArray['###OPTIONS###'] .= $this->renderContent($this->templates['dd_pictures_options'],$o_markerArray);
				}
				if ($markerArray['###OPTIONS###'] != '') $content .= $this->renderContent($this->templates['dd_pictures_compare'],$markerArray);
				break;
			case 'dd_words':
				$options = $this->getOptions($question['uid']);
				$markerArray['###OPTIONS###'] = '';
				foreach ($options as $option){
					$o_markerArray = array();
					$o_markerArray['###VALUE###'] = $value;
					$o_markerArray['###INPUT_TEXT###'] = '';
					if ($option['correct_answer']){
						$o_markerArray['###VALUE###'] = 'X';
					}
					$text = $option['title'];
					if ($option['text'] != '') $text = $option['text'];
					$o_markerArray['###TEXT###'] = $text;
					$markerArray['###OPTIONS###'] .= $this->renderContent($this->templates['dd_words_options'],$o_markerArray);
				}
				if ($markerArray['###OPTIONS###'] != '') $content .= $this->renderContent($this->templates['dd_words_compare'],$markerArray);
				break;
		}

		return $content;
	}
        
        /**
         * renderDemographicQuestion(): render the demographic question for the pdf
         *
         * @param       array   $question
         * @param       array   $markerArray: prefilled markerArray
         * @answered    array   $answered: answers given
         *
         * @return      string  content to be rendered
         */
	function renderDemographicQuestion($question,$markerArray,$answered){
		//t3lib_div::devLog('answered', 'pdf_export', 0, $answered);
		$html = '';
		$value = '&nbsp;';

		$markerArray['###LINES###'] = '';
		if (is_array($answered['fe_users'])){
			foreach ($answered['fe_users'] as $key => $value){
				$l_markerArray = array();
				//todo: get Label out of locallang
				$l_markerArray['###TITLE###'] = $key;
				$l_markerArray['###VALUE###'] = $value;
				$markerArray['###LINES###'] .= $this->renderContent($this->templates['demographic_line'],$l_markerArray);
			}
		}

		$html = $this->renderContent($this->templates['demographic'],$markerArray);

		return $html;
	}

        /**
         * renderSemanticQuestion(): render a semantic question
         *
         * @param       array   $question
         * @param       array   $markerArray: prefilled marker array for rendering
         * @param       array   $answered: answers given
         *
         * @return      string  content to be rendered
         */
	function renderSemanticQuestion($question,$markerArray,$answered){
		//t3lib_div::devLog('answered', 'pdf_export', 0, $answered);
		$html = '';
		$value = '&nbsp;';

		$sublines = $this->getSemanticLines($question['uid']);
		$columns = $this->getColumns($question['uid']);
		//t3lib_div::devLog('columns', $this->prefixId, 0, $columns);

		if (is_array($columns)){
			$l_markerArray = array();
			$l_markerArray['###COLUMNS###'] = '<td>&nbsp;</td>';
			foreach ($columns as $column){
				$c_markerArray = array();
				$c_markerArray['###CLASS###'] = 'column';
				$c_markerArray['###VALUE###'] = $column['title'];
				$l_markerArray['###COLUMNS###'] .= $this->renderContent($this->templates['semantic_column'],$c_markerArray);
			}
			$l_markerArray['###COLUMNS###'] .= '<td class="semantic_end">&nbsp;</td>';
			$markerArray['###ROWS###'] = $this->renderContent($this->templates['semantic_line'],$l_markerArray);
		}

		foreach ($sublines as $subline){
			$l_markerArray = array();
			$c_markerArray = array();
			$c_markerArray['###CLASS###'] = '';
			$c_markerArray['###VALUE###'] = $subline['start'];
			$l_markerArray['###COLUMNS###'] = $this->renderContent($this->templates['semantic_column'],$c_markerArray);
			foreach ($columns as $column){
				$value = '&nbsp;';
				if (is_array($answered['options'])){
					if ($answered['options'][$subline['uid']] == $column['uid']) $value = 'X';
				}
				$c_markerArray = array();
				$c_markerArray['###CLASS###'] = 'column';
				$c_markerArray['###VALUE###'] = '<div class="semantic_check">'.$value.'</div>';
				$l_markerArray['###COLUMNS###'] .= $this->renderContent($this->templates['semantic_column'],$c_markerArray);
			}
			$c_markerArray = array();
			$c_markerArray['###CLASS###'] = 'semantic_end';
			$c_markerArray['###VALUE###'] = $subline['end'];
			$l_markerArray['###COLUMNS###'] .= $this->renderContent($this->templates['semantic_column'],$c_markerArray);
			$markerArray['###ROWS###'] .= $this->renderContent($this->templates['semantic_line'],$l_markerArray);
		}
		$html = $this->renderContent($this->templates['semantic'],$markerArray);

		return $html;
	}

        /**
         * renderMatrixQuestion(): render a matrix question for pdf
         
         * @param       array   $question
         * @param       array   $markerArray: prefilled marker array for rendering
         * @param       array   $answered: answers given
         *
         * @return      string  content to be rendered
         */
	function renderMatrixQuestion($question,$markerArray,$answered){
		//t3lib_div::devLog('answered', 'pdf_export', 0, $answered);
		$html = '';
		$value = '&nbsp;';

		$subquestions = $this->getMatrixLines($question['uid']);
		$columns = $this->getColumns($question['uid']);
		//t3lib_div::devLog('columns', $this->prefixId, 0, $columns);

		if (is_array($columns)){
			$l_markerArray = array();
			$l_markerArray['###COLUMNS###'] = '<td>&nbsp;</td>';
			foreach ($columns as $column){
				$c_markerArray = array();
				$c_markerArray['###CLASS###'] = 'header_column';
				$c_markerArray['###VALUE###'] = $column['title'];
				$l_markerArray['###COLUMNS###'] .= $this->renderContent($this->templates['matrix_column'],$c_markerArray);
			}
			$markerArray['###ROWS###'] = $this->renderContent($this->templates['matrix_line'],$l_markerArray);
		}

		foreach ($subquestions as $subquestion){
			//t3lib_div::devLog('sub', 'DomPDF', 0, $subquestion);
			$l_markerArray = array();
			$c_markerArray = array();
			$c_markerArray['###CLASS###'] = '';
			$text = $subquestion['title'];
			if ($subquestion['text'] != '') $text = $subquestion['text'];
			$c_markerArray['###VALUE###'] = $text;

			$l_markerArray['###COLUMNS###'] = $this->renderContent($this->templates['matrix_column'],$c_markerArray);
			foreach ($columns as $column){
				//t3lib_div::devLog('column', 'DomPDF', 0, $column);
				$value = '&nbsp;';
				$c_markerArray = array();
				$c_markerArray['###CLASS###'] = 'column';

				if ($column['different_type'] != ''){
					$m_type = $column['different_type'];
				} else {
					$m_type = $question['matrix_type'];
				}
				switch ($m_type){
					case 'check':
						if (is_array($answered['options'])){
							if ($answered['options'][$subquestion['uid']][$column['uid']]) $value = 'X';
						}
					case 'radio':
						if (is_array($answered['options'])){
							if ($answered['options'][$subquestion['uid']]['single'] == $column['uid']) $value = 'X';
						}
						$c_markerArray['###VALUE###'] = '<div class="matrix_check">'.$value.'</div>';
						break;
					default:
						if (is_array($answered['options'])){
							$value = $answered['options'][$subquestion['uid']][$column['uid']][0];
						}
						$c_markerArray['###VALUE###'] = '<div class="matrix_input">'.$value.'</div>';
						break;
				}
				if ($subquestion['title_line'] == 1) $c_markerArray['###VALUE###'] = $value;

				$l_markerArray['###COLUMNS###'] .= $this->renderContent($this->templates['matrix_column'],$c_markerArray);
			}
			$markerArray['###ROWS###'] .= $this->renderContent($this->templates['matrix_line'],$l_markerArray);
		}

		$html = $this->renderContent($this->templates['matrix'],$markerArray);

		return $html;
	}

	/**
	 * renderFirstPage(): renders the Start-Page for the Questionnaire
	 *
	 * @return      string   content
	 */
	public function renderFirstPage(){
		$content = '';

		if ($this->ffdata['tDEF']['lDEF']['description']['vDEF'] != '') $content .= '<div class="questionnaire_description">'.$this->ffdata['tDEF']['lDEF']['description']['vDEF'].'</div>';

		return $content;
	}

        /**
         * renderContent(): renders the content into the template
         *
         * @param       string  $content: rendered content
         * @param       array   $markerArray: array of markers and values to be parsed into the content given
         *
         * @return      string  parsed content
         */
	function renderContent($content,$markerArray){
		//t3lib_div::devLog('renderContent', 'pdf', 0, array($content,$markerArray));
		if (is_array($markerArray)){
			foreach($markerArray as $key => $value){
				$content = str_replace($key,$value,$content);
			}
		}
		return $content;
	}

        /**
         * buildTSFE(): bild the TSFE-Functionality to be used in this class
         */
	function buildTSFE() {
		#needed for TSFE
		require_once(PATH_t3lib.'class.t3lib_timetrack.php');
		require_once(PATH_t3lib.'class.t3lib_tsparser_ext.php');
		require_once(PATH_t3lib.'class.t3lib_page.php');
		require_once(PATH_t3lib.'class.t3lib_stdgraphic.php');

		require_once(PATH_tslib.'class.tslib_fe.php');
		require_once(PATH_tslib.'class.tslib_content.php');
		require_once(PATH_tslib.'class.tslib_gifbuilder.php');

		/* Declare */
		$temp_TSFEclassName = t3lib_div::makeInstanceClassName('tslib_fe');

		/* Begin */
		if (!is_object($GLOBALS['TT'])) {
			$GLOBALS['TT'] = new t3lib_timeTrack;
			$GLOBALS['TT']->start();
		}

		if (!is_object($GLOBALS['TSFE']) && $this->pid) {
			//*** Builds TSFE object
			$GLOBALS['TSFE'] = new $temp_TSFEclassName($GLOBALS['TYPO3_CONF_VARS'],$this->pid,0,0,0,0,0,0);

			//*** Builds sub objects
			$GLOBALS['TSFE']->tmpl = t3lib_div::makeInstance('t3lib_tsparser_ext');
			$GLOBALS['TSFE']->sys_page = t3lib_div::makeInstance('t3lib_pageSelect');

			//*** init template
			$GLOBALS['TSFE']->tmpl->tt_track = 0;// Do not log time-performance information
			$GLOBALS['TSFE']->tmpl->init();

			$rootLine = $GLOBALS['TSFE']->sys_page->getRootLine($this->pid);

			//*** This generates the constants/config + hierarchy info for the template.

			$GLOBALS['TSFE']->tmpl->runThroughTemplates($rootLine,$template_uid);
			$GLOBALS['TSFE']->tmpl->generateConfig();
			$GLOBALS['TSFE']->tmpl->loaded=1;

			//*** Get config array and other init from pagegen
			$GLOBALS['TSFE']->getConfigArray();
			$GLOBALS['TSFE']->linkVars = ''.$GLOBALS['TSFE']->config['config']['linkVars'];

			if ($GLOBALS['TSFE']->config['config']['simulateStaticDocuments_pEnc_onlyP'])
			{
				foreach (t3lib_div::trimExplode(',',$GLOBALS['TSFE']->config['config']['simulateStaticDocuments_pEnc_onlyP'],1) as $temp_p)
				{
					$GLOBALS['TSFE']->pEncAllowedParamNames[$temp_p]=1;
				}
			}
			//*** Builds a cObj
			$GLOBALS['TSFE']->newCObj();
		}
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/ke_questionnaire/res/other/class.dompdf_export.php']){
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/ke_questionnaire/res/other/class.dompdf_export.php']);
}
?>