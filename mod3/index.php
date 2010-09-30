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


	// DEFAULT initialization of a module [BEGIN]
unset($MCONF);
require_once('conf.php');
require_once($BACK_PATH.'init.php');
require_once($BACK_PATH.'template.php');

$LANG->includeLLFile('EXT:ke_questionnaire/mod3/locallang.xml');
require_once(PATH_t3lib.'class.t3lib_scbase.php');
$BE_USER->modAccess($MCONF,1);	// This checks permissions and exits if the users has no permission for entry.
	// DEFAULT initialization of a module [END]



/**
 * Module 'Export' for the 'ke_questionnaire' extension.
 *
 * @author	Nadine Schwingler <schwingler@kennziffer.com>
 * @package	TYPO3
 * @subpackage	tx_kequestionnaire
 */
class  tx_kequestionnaire_module3 extends t3lib_SCbase {
	var $pageinfo;

	/**
	 * Initializes the Module
	 * @return	void
	 */
	function init()	{
		global $BE_USER,$LANG,$BACK_PATH,$TCA_DESCR,$TCA,$CLIENT,$TYPO3_CONF_VARS;
		parent::init();

		//get the given Parameters
		$this->q_id = intval(t3lib_div::_GP('q_id'));
		$this->pid = intval(t3lib_div::_GP('id'));
		
		$this->extConf = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['ke_questionnaire']);
		if (t3lib_extMgm::isLoaded('ke_questionnaire_premium')) $this->pr_extConf = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['ke_questionnaire_premium']);

		if ($this->q_id > 0){
			$this->q_data = t3lib_BEfunc::getRecord('tt_content',$this->q_id);
			$ff_data = t3lib_div::xml2array($this->q_data['pi_flexform']);
			$this->ff_data = $ff_data['data'];
		}
		//t3lib_div::devLog('getCSVInfos ffdata', 'ke_questionnaire Export Mod', 0, $this->ff_data);
		/*
		if (t3lib_div::_GP('clear_all_cache'))	{
			$this->include_once[] = PATH_t3lib.'class.t3lib_tcemain.php';
		}
		*/
	}

	/**
	 * Adds items to the ->MOD_MENU array. Used for the function menu selector.
	 *
	 * @return	void
	 */
	function menuConfig()	{
		global $LANG;
		$this->MOD_MENU = Array (
			'function' => Array (
				'1' => $LANG->getLL('function1'),
				'3' => $LANG->getLL('function3'),
			)
		);
		if (t3lib_extMgm::isLoaded('ke_questionnaire_premium')) $this->MOD_MENU['function']['2'] = $LANG->getLL('function2');
				
		parent::menuConfig();
	}

	/**
	 * Main function of the module. Write the content to $this->content
	 * If you chose "web" as main module, you will need to consider the $this->id parameter which will contain the uid-number of the page clicked in the page tree
	 *
	 * @return	[type]		...
	 */
	function main()	{
		global $BE_USER,$LANG,$BACK_PATH,$TCA_DESCR,$TCA,$CLIENT,$TYPO3_CONF_VARS;
		
		// Access check!
		// The page will show only if there is a valid page and if this page may be viewed by the user
		$this->pageinfo = t3lib_BEfunc::readPageAccess($this->id,$this->perms_clause);
		$access = is_array($this->pageinfo) ? 1 : 0;

		//if (($this->id && $access) || ($BE_USER->user['admin'] && !$this->id))	{
			// Draw the header.
			$this->doc = t3lib_div::makeInstance('mediumDoc');
			$this->doc->backPath = $BACK_PATH;
			$this->doc->form='<form action="" method="POST">';

			// JavaScript
			$this->doc->JScode = '
				<script language="javascript" type="text/javascript">
					script_ended = 0;
					function jumpToUrl(URL)	{
						document.location = URL;
					}
				</script>
			';
			$this->doc->postCode='
				<script language="javascript" type="text/javascript">
					script_ended = 1;
					if (top.fsMod) top.fsMod.recentIds["web"] = 0;
				</script>
			';

			$headerSection = $this->doc->getHeader('pages',$this->pageinfo,$this->pageinfo['_thePath']).'<br />'.$LANG->sL('LLL:EXT:lang/locallang_core.xml:labels.path').': '.t3lib_div::fixed_lgd_pre($this->pageinfo['_thePath'],50);

			$this->content.=$this->doc->startPage($LANG->getLL('title'));
			$this->content.=$this->doc->header($LANG->getLL('title'));
			$this->content.=$this->doc->spacer(5);
			// #################################################
			// KENNZIFFER Nadine Schwingler 23.10.2009
			// Changing the Menu, to pass the q_id-Parameter to the selection
			$func_array = array();
			$func_array['id'] = $this->id;
			$func_array['q_id'] = $this->q_id;
			$this->content.=$this->doc->section('',$this->doc->funcMenu($headerSection,t3lib_BEfunc::getFuncMenu($func_array,'SET[function]',$this->MOD_SETTINGS['function'],$this->MOD_MENU['function'])));
			// #################################################
			//$this->content.=$this->doc->section('',$this->doc->funcMenu($headerSection,t3lib_BEfunc::getFuncMenu($this->id,'SET[function]',$this->MOD_SETTINGS['function'],$this->MOD_MENU['function'])));
			$this->content.=$this->doc->divider(5);

			// Render content:
			$this->moduleContent();

			// ShortCut
			if ($BE_USER->mayMakeShortcut())	{
				$this->content.=$this->doc->spacer(20).$this->doc->section('',$this->doc->makeShortcutIcon('id',implode(',',array_keys($this->MOD_MENU)),$this->MCONF['name']));
			}

			$this->content.=$this->doc->spacer(10);
		/*} else {
			// If no access or if ID == zero

			$this->doc = t3lib_div::makeInstance('mediumDoc');
			$this->doc->backPath = $BACK_PATH;

			$this->content.=$this->doc->startPage($LANG->getLL('title'));
			$this->content.=$this->doc->header($LANG->getLL('title'));
			$this->content.=$this->doc->spacer(5);
			$this->content.=$this->doc->spacer(10);
		}*/
	}

	/**
	 * Prints out the module HTML
	 *
	 * @return	void
	 */
	function printContent()	{
		$this->content.=$this->doc->endPage();
		echo $this->content;
	}

	/**
	 * Generates the module content
	 *
	 * @return	void
	 */
	function moduleContent(){
		global $LANG;
		//set_time_limit(240);
		if ($this->q_id == 0){
			$title = $LANG->getLL('none_selected');
			$content = $LANG->getLL('none_selected');
		} else {
			switch((string)$this->MOD_SETTINGS['function'])	{
				//CSV
				case 1:
					$title = $LANG->getLL('function1');

					$content = $this->getCSVInfos();
					if (t3lib_div::_GP('get_css')){
						$content .= $this->getCSVDownload();
						exit;
					}
				break;
				//SPSS
				case 2:
					$title = $LANG->getLL('function2');

					$content = $this->getSPSSInfos();
					if (t3lib_div::_GP('get_spss_base')){
						$content .= $this->getSPSSDownload('base');
						exit;
					} elseif (t3lib_div::_GP('get_spss_data')){
						$content .= $this->getSPSSDownload('data');
						exit;
					}
				break;
				//PDF
				case 3:
					$title = $LANG->getLL('function3');

					$content = $this->getPDFInfos();
					if (t3lib_div::_GP('get_pdf_blank')){
						$content .= $this->getPDFDownload('blank');
						exit;
					}
					if (t3lib_div::_GP('get_pdf_filled')){
						$content .= $this->getPDFDownload('filled');
						exit;
					}
					if (t3lib_div::_GP('get_pdf_compare')){
						$content .= $this->getPDFDownload('compare');
						exit;
					}
				break;
			}
		}

		$this->content.=$this->doc->section($title,$content,0,1);
	}

	function getCSVInfos(){
		//t3lib_div::devLog('getCSVInfos POST', 'ke_questionnaire Export Mod', 0, $_POST);
		global $LANG;

		$content = '';
		$this->results = array();
		$finished = 0;
		$counting = 0;

		//$content .= t3lib_div::view_array($this->ff_data);
		$storage_pid = $this->ff_data['sDEF']['lDEF']['storage_pid']['vDEF'];
		//t3lib_div::devLog('getCSVInfos', 'ke_questionnaire Export Mod', 0, $storage_pid);
		
		$where = 'pid='.$storage_pid.' AND hidden=0 AND deleted=0';
		
		//Check if only the actual plugin-lang should be selected
		$langs = array();
		if (htmlentities(t3lib_div::_GP('only_this_lang'))){
			$only_lang = explode('_',htmlentities(t3lib_div::_GP('only_this_lang')));
			$where .= ' AND sys_language_uid='.$only_lang[1];
		}
		//t3lib_div::devLog('getCSVInfos', 'ke_questionnaire Export Mod', 0, $only_lang);
		
		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*','tx_kequestionnaire_results',$where,'','uid');
		//t3lib_div::devLog('getCSVInfos', 'ke_questionnaire Export Mod', 0, array($GLOBALS['TYPO3_DB']->SELECTquery('*','tx_kequestionnaire_results',$where,'','uid')));
		if ($res){
			while($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)){
				if ($row['xmldata'] != '') {
					$temp_array = '';
					$encoding = "UTF-8";
					if ( true === mb_check_encoding ($row['xmldata'], $encoding ) ){
						$temp_array = t3lib_div::xml2array($row['xmldata']);
						if (count($temp_array) == 1) $temp_array = t3lib_div::xml2array(utf8_encode($row['xmldata']));
					} else {
						$temp_array = t3lib_div::xml2array(utf8_encode($row['xmldata']));
					}
					//$temp .= t3lib_div::view_array($row);
					$temp_array['uid'] = $row['uid'];
					$this->results[] = $temp_array;
					$langs[$row['sys_language_uid']] = 1;
					if ($row['finished_tstamp'] > 0) $finished ++;
					$counting ++;
				}
			}
		}
		//t3lib_div::devLog('getCSVInfos langs', 'ke_questionnaire Export Mod', 0, $langs);

		$content = $LANG->getLL('result_count').': '.$counting.'<br />';
		$content .= $LANG->getLL('finished_count').': '.$finished;

		$content .= '<p><br /><hr />';
		$content .= '<p>'.$LANG->getLL('CSV_download_type');
		$content .= ' <select name="download_type">';
		$content .= '<option value="questions">'.$LANG->getLL('CSV_download_questions').'</option>';
		//$content .= '<option value="simple">'.$LANG->getLL('CSV_download_simple').'</option>';
		$content .= '<option value="simple2">'.$LANG->getLL('CSV_download_simple2').'</option>';
		//$content .= '<option value="results">'.$LANG->getLL('CSV_download_results').'</option>';
		$content .= '</select></p>';
		//$content .= '<input type="hidden" name="download_type" value="simple" />';
		$content .= '<br /><p>';
		$content .= '<input type="checkbox" name="only_finished" value="1" checked /> '.$LANG->getLL('download_only_finished').'</p><br />';
		if ($this->ff_data['sDEF']['lDEF']['access']['vDEF'] == 'AUTH_CODE'){
			$content .= '<p><input type="checkbox" name="with_authcode" value="1" /> '.$LANG->getLL('download_with_authcode').'</p>';
		}
		//check if the selected plugin lang has own results
		if ($this->q_data['sys_language_uid'] > 0 AND $langs[$this->q_data['sys_language_uid']] == 1){
			$content .= '<p><input type="checkbox" name="only_this_lang" value="L_'.$this->q_data['sys_language_uid'].'" /> '.$LANG->getLL('download_only_this_lang').'</p>';
		} else if ($this->q_data['sys_language_uid'] == 0 AND $langs[0] == 1){
			foreach ($langs as $key => $is){
				if ($key != 0) {
					$content .= '<p><input type="checkbox" name="only_this_lang" value="L_'.(string)$this->q_data['sys_language_uid'].'" /> '.$LANG->getLL('download_only_this_lang').'</p>';
					break;
				}
			}
		}
		$content .= '<br />';
		$content .= '<input type="submit" name="get_css" value="'.$LANG->getLL('download_button').'" />';
		$content .= '</p>';

		return $content;
	}

	function getSPSSInfos(){
		//t3lib_div::devLog('getSPSSInfos GET', 'ke_questionnaire Export Mod', 0, $_GET);
		//t3lib_div::devLog('getSPSSInfos POST', 'ke_questionnaire Export Mod', 0, $_POST);
		global $LANG;

		$content = '';
		$this->results = array();
		$finished = 0;
		$counting = 0;

		//$content .= t3lib_div::view_array($this->ff_data);
		$storage_pid = $this->ff_data['sDEF']['lDEF']['storage_pid']['vDEF'];
		//t3lib_div::devLog('getCSVInfos', 'ke_questionnaire Export Mod', 0, $storage_pid);

		$where = 'pid='.$storage_pid.' AND hidden=0 AND deleted=0';
		
		//Check if only the actual plugin-lang should be selected
		$langs = array();
		if (htmlentities(t3lib_div::_GP('only_this_lang'))){
			$only_lang = explode('_',htmlentities(t3lib_div::_GP('only_this_lang')));
			$where .= ' AND sys_language_uid='.$only_lang[1];
		}
		
		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*','tx_kequestionnaire_results',$where,'','uid');
		//t3lib_div::devLog('getCSVInfos', 'ke_questionnaire Export Mod', 0, array($GLOBALS['TYPO3_DB']->SELECTquery('*','tx_kequestionnaire_results','pid='.$storage_pid.' AND hidden=0 AND deleted=0')));
		$langs = array();
		if ($res){
			while($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)){
				if ($row['xmldata'] != '') {
					$temp_array = '';
					$encoding = "UTF-8";
					if ( true === mb_check_encoding ($row['xmldata'], $encoding ) ){
						$temp_array = t3lib_div::xml2array($row['xmldata']);
						if (count($temp_array) == 1) $temp_array = t3lib_div::xml2array(utf8_encode($row['xmldata']));
					} else {
						$temp_array = t3lib_div::xml2array(utf8_encode($row['xmldata']));
					}
					//$temp .= t3lib_div::view_array($row);
					$temp_array['uid'] = $row['uid'];
					$this->results[] = $temp_array;
					$langs[$row['sys_language_uid']] = 1;
					$value_array = t3lib_div::xml2array($row['xmldata']);
					//t3lib_div::devLog('value_array ', 'ke_questionnaire Export Mod', 0, $value_array);
					if ($row['finished_tstamp'] > 0) $finished ++;
					$counting ++;
				}
			}
		}
		//t3lib_div::devLog('getSPSSInfos RESULTS', 'ke_questionnaire Export Mod', 0, $this->results);

		$content = $LANG->getLL('result_count').': '.$counting.'<br />';
		$content .= $LANG->getLL('finished_count').': '.$finished;

		$content .= '<p><br /><hr />';
		$content .= '<p><input type="checkbox" name="only_finished" value="1" checked /> '.$LANG->getLL('download_only_finished').'</p><br />';
		if ($this->ff_data['sDEF']['lDEF']['access']['vDEF'] == 'AUTH_CODE'){
			$content .= '<p><input type="checkbox" name="with_authcode" value="1" /> '.$LANG->getLL('download_with_authcode').'</p>';
		}
		//check if the selected plugin lang has own results
		if ($this->q_data['sys_language_uid'] > 0 AND $langs[$this->q_data['sys_language_uid']] == 1){
			$content .= '<p><input type="checkbox" name="only_this_lang" value="L_'.$this->q_data['sys_language_uid'].'" /> '.$LANG->getLL('download_only_this_lang').'</p>';
		} else if ($this->q_data['sys_language_uid'] == 0 AND $langs[0] == 1){
			foreach ($langs as $key => $is){
				if ($key != 0) {
					$content .= '<p><input type="checkbox" name="only_this_lang" value="L_'.(string)$this->q_data['sys_language_uid'].'" /> '.$LANG->getLL('download_only_this_lang').'</p>';
					break;
				}
			}
		}
		$content .= '<br />';
		$content .= '<p><input type="submit" name="get_spss_base" value="'.$LANG->getLL('download_button_spss_base').'" /><br /><br /></p>';
		$content .= '<p><input type="submit" name="get_spss_data" value="'.$LANG->getLL('download_button_spss_data').'" /></p>';
		$content .= '</p>';

		return $content;
	}

	function getPDFInfos(){
		//t3lib_div::devLog('getSPSSInfos GET', 'ke_questionnaire Export Mod', 0, $_GET);
		//t3lib_div::devLog('getSPSSInfos POST', 'ke_questionnaire Export Mod', 0, $_POST);
		global $LANG;

		$content = '';
		if (t3lib_extMgm::isLoaded('fpdf') or t3lib_extMgm::isLoaded('ke_dompdf')){
			$content .= '<p><input type="submit" name="get_pdf_blank" value="'.$LANG->getLL('download_button_pdf_blank').'" /></p>';
			$content .= '<br /><hr><br />';
			$content .= '<p>'.$this->getResultSelect().'</p><br />';
			$content .= '<p><input type="submit" name="get_pdf_filled" value="'.$LANG->getLL('download_button_pdf_filled').'" /></p>';
			$content .= '<br /><hr><br />';
			$content .= '<p>'.$this->getResultSelect('compare').'</p><br />';
			$content .= '<p><input type="submit" name="get_pdf_compare" value="'.$LANG->getLL('download_button_pdf_compare').'" /></p>';
		} else {
			$content .= '<p>'.$LANG->getLL('error_no_fpdf_kedompdf').'</p>';
		}
		return $content;
	}
	
	function getResultSelect($type = 'filled'){
		$content = '';
		$content .= '<select name="result_id_'.$type.'">';
		
		$table = 'tx_kequestionnaire_results';
		$where = 'pid='.$this->ff_data['sDEF']['lDEF']['storage_pid']['vDEF'];
		$where .= ' AND deleted=0 AND hidden=0';
		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('uid',$table,$where);
		//t3lib_div::devLog('getResults', 'ke_questionnaire Export Mod', 0, array($GLOBALS['TYPO3_DB']->SELECTquery('uid',$table,$where)));
		if ($res){
			while ($row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)){
				$content .= '<option value="'.$row['uid'].'">'.$row['uid'].'</option>';
			}
		}
		
		$content .= '</select>';
		return $content;
	}

	function getCSVDownload(){
		//t3lib_div::devLog('getCSVInfos GET', 'ke_questionnaire Export Mod', 0, $_GET);
		//t3lib_div::devLog('getCSVInfos POST', 'ke_questionnaire Export Mod', 0, $_POST);

		$csvdata = '';
		$parter = $this->extConf['CSV_parter'];

		switch (t3lib_div::_GP('download_type')){
			case 'simple':
				$csvdata = $this->getCSVSimple();
				break;
			case 'simple2':
				$csvdata = $this->getCSVSimple2();
				break;
			case 'questions':
				$csvdata = $this->getCSVQBased();
				break;
			default:
				break;
		}

		$csvdata = mb_convert_encoding($csvdata, "Windows-1252", "UTF-8");
		header("content-type: application/csv-tab-delimited-table");
		header("content-length: ".strlen($csvdata));
		header("content-disposition: attachment; filename=\"".$this->q_id."_csv_export.csv\"");

		print $csvdata;
	}

	function getCSVQBased(){
		global $LANG;

		$csvdata = '';
		$csvheader = '';
		$delimeter = $this->extConf['CSV_qualifier'];
		$pure_parter = $this->extConf['CSV_parter'];
		$parter = $delimeter.$this->extConf['CSV_parter'].$delimeter;

		$csvheader = $this->getQBaseHeaderLine();
		/*foreach ($this->results as $nr => $values){
			$result_array[$values['uid']] = t3lib_div::xml2array($values['xmldata']);
		}*/

		$storage_pid = $this->ff_data['sDEF']['lDEF']['storage_pid']['vDEF'];
		//$where = 'pid='.$storage_pid.' and hidden=0 and deleted=0 and type!="blind"';
		//$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*','tx_kequestionnaire_questions',$where,'','sorting');
		//t3lib_div::devLog('getCSVQBase res', 'ke_questionnaire Export Mod', 0, array($GLOBALS['TYPO3_DB']->SELECTquery('*'.'tx_kequestionnaire_questions',$where,'','sorting')));
		//simplify the results for better export
		$this->simplifyResults();
		//t3lib_div::devLog('Simple Results ', 'ke_questionnaire Export Mod', 0, $this->simpleResults);

		$lineset = ''; //stores the CSV-data
		$line = array(); //single line, will be imploded
		$free_cells = 0;
		$result_line = $this->getQBaseResultLine($free_cells);
		
		$lineset .= $pure_parter.$pure_parter.$pure_parter.$result_line."\n";
		foreach ($this->simpleResults as $q_id => $question){
			$line = array();
			$line[] = $question['uid'];
			$line[] = $this->stripString($question['title']);
			if ($question['type']){
				$lineset .= $delimeter.implode($parter,$line).$delimeter;
				//$lineset .= $pure_parter.$result_line."\n";
				$lineset .= $pure_parter;
				//t3lib_div::devLog('getCSVQBase '.$question['type'], 'ke_questionnaire Export Mod', 0, $question);
				//t3lib_div::devLog('lineset '.$question['type'], 'ke_questionnaire Export Mod', 0, array($lineset));
		/*if ($res){
			while($question = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)){
				$line = array();
				$line[] = $question['uid'];
				$line[] = $this->stripString($question['title']);
				$lineset .= $delimeter.implode($parter,$line).$delimeter;
				$lineset .= $pure_parter.$result_line."\n";
				//t3lib_div::devLog('getCSVQBase '.$question['type'], 'ke_questionnaire Export Mod', 0, '');
		*/		switch ($question['type']){
					case 'authcode':	$lineset .= $this->getQBaseLine($free_cells,$question['uid'],$question['type']);
						break;
					case 'start_tstamp':	$lineset .= $this->getQBaseLine($free_cells,$question['uid'],$question['type']);
						break;
					case 'finished_tstamp':	$lineset .= $this->getQBaseLine($free_cells,$question['uid'],$question['type']);
						break;
					case 'open':	$lineset .= $this->getQBaseLine($free_cells,$question['uid'],$question['type']);
						break;
					case 'closed':
							$lineset .= "\n";
							foreach ($question['answers'] as $a_id => $a_values){
								$answer = t3lib_BEfunc::getRecord('tx_kequestionnaire_answers',$a_id);
								$lineset .= $this->getQBaseLine($free_cells+2,$question['uid'],$question['type'],$answer);
							}
							/*$where = 'question_uid='.$question['uid'].' and hidden=0 and deleted=0';
							$res_answers = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*','tx_kequestionnaire_answers',$where,'','sorting');
							//t3lib_div::devLog('getCSVQBase '.$question['type'], 'ke_questionnaire Export Mod', 0, array($GLOBALS['TYPO3_DB']->SELECTquery('*','tx_kequestionnaire_answers',$where,'','sorting')));
							if ($res_answers){
								while ($answer = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res_answers)){
									$lineset .= $this->getQBaseLine($free_cells,$question['uid'],$question['type'],$answer);
								}
							}*/
						break;
					case 'matrix':
							$lineset .= "\n";
							foreach ($question['subquestions'] as $sub_id => $sub_values){
								$line = array();
								for ($i = 0;$i < ($free_cells+1);$i ++){
									$line[] = '';
								}
								$subquestion = t3lib_BEfunc::getRecord('tx_kequestionnaire_subquestions',$sub_id);
								//t3lib_div::devLog('getCSVQBase '.$question['type'], 'ke_questionnaire Export Mod', 0, $subquestion);
								$line[] = $subquestion['title'];
								$lineset .= $delimeter.implode($parter,$line).$delimeter."\n";
								foreach ($sub_values['columns'] as $c_id => $c_values){
									$column = t3lib_BEfunc::getRecord('tx_kequestionnaire_columns',$c_id);
									$lineset .= $this->getQBaseLine($free_cells+2,$question['uid'],$question['type'],array(),$subquestion['uid'],$column);
								}
							}
							/*$columns = array();
							$where = 'question_uid='.$question['uid'].' and hidden=0 and deleted=0';
							$res_columns = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*','tx_kequestionnaire_columns',$where,'','sorting');
							if ($res_columns){
								while ($column = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res_columns)){
									$columns[] = $column;
								}
							}
							$res_subquestions = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*','tx_kequestionnaire_subquestions',$where,'','sorting');
							if ($res_subquestions){
								while ($subquestion = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res_subquestions)){
									if ($subquestion['title_line'] == 1){
									} else {
										$line = array();
										for ($i = 0;$i < ($free_cells-1);$i ++){
											$line[] = '';
										}
										$line[] = $subquestion['title'];
										$lineset .= $delimeter.implode($parter,$line).$delimeter."\n";
										foreach ($columns as $column){
											$lineset .= $this->getQBaseLine($free_cells,$question['uid'],$question['type'],array(),$subquestion['uid'],$column);
										}
									}
								}
							}*/
						break;
					case 'semantic':
							$lineset .= "\n";
							//t3lib_div::devLog('getCSVQBase '.$question['type'], 'ke_questionnaire Export Mod', 0, $question);
							foreach ($question['subquestions'] as $sub_id => $sub_values){
								$line = array();
								for ($i = 0;$i < ($free_cells+1);$i ++){
									$line[] = '';
								}
								$subquestion = t3lib_BEfunc::getRecord('tx_kequestionnaire_sublines',$sub_id);
								$line[] = $subquestion['title'];
								$lineset .= $delimeter.implode($parter,$line).$delimeter."\n";
								foreach ($sub_values['columns'] as $c_id => $c_values){
									$column = t3lib_BEfunc::getRecord('tx_kequestionnaire_columns',$c_id);
									$lineset .= $this->getQBaseLine($free_cells+2,$question['uid'],$question['type'],array(),$subquestion['uid'],$column);
								}
							}
							/*$columns = array();
							$where = 'question_uid='.$question['uid'].' and hidden=0 and deleted=0';
							$res_columns = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*','tx_kequestionnaire_columns',$where,'','sorting');
							if ($res_columns){
								while ($column = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res_columns)){
									$columns[] = $column;
								}
							}
							$res_sublines = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*','tx_kequestionnaire_sublines',$where,'','sorting');
							if ($res_sublines){
								while ($subline = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res_sublines)){
									$line = array();
									for ($i = 0;$i < ($free_cells-1);$i ++){
										$line[] = '';
									}
									$line[] = $subline['start'].' - '.$subline['end'];
									$lineset .= $delimeter.implode($parter,$line).$delimeter."\n";
									foreach ($columns as $column){
										$lineset .= $this->getQBaseLine($free_cells,$question['uid'],$question['type'],array(),$subline['uid'],$column);
									}
								}
							}*/
						break;
					case 'demographic':
							if (is_array($question['fe_users'])){
								foreach ($question['fe_users'] as $field => $f_values){
									$lineset .= $this->getQBaseLine($free_cells,$question['uid'],$question['type'],array(),0,array(),$field);
								}
							}
							if (is_array($question['tt_address'])){
								foreach ($question['tt_address'] as $field => $f_values){
									$lineset .= $this->getQBaseLine($free_cells,$question['uid'],$question['type'],array(),0,array(),$field);
								}
							}
							//t3lib_div::devLog('getCSVQBase '.$question['type'], 'ke_questionnaire Export Mod', 0, $question);
							/*$flex = t3lib_div::xml2array($question['demographic_fields']);
							$fe_user_fields = explode(',',$flex['data']['sDEF']['lDEF']['FeUser_Fields']['vDEF']);
							$flex = t3lib_div::xml2array($question['demographic_addressfields']);
							$fe_user_addressfields = explode(',',$flex['data']['sDEF']['lDEF']['FeUser_Fields']['vDEF']);
							//t3lib_div::devLog('getCSVQBase flex', 'ke_questionnaire Export Mod', 0, array($fe_user_fields,$fe_user_addressfields));
							foreach ($fe_user_fields as $field){
								$lineset .= $this->getQBaseLine($free_cells,$question['uid'],$question['type'],array(),0,array(),$field);
							}
							foreach ($fe_user_addressfields as $field){
								$lineset .= $this->getQBaseLine($free_cells,$question['uid'],$question['type'],array(),0,array(),$field);
							}*/
							//$lineset .= $this->getQBaseLine($free_cells,$question['uid'],$question['type']);
						break;
					default:
							$delimeter = $this->extConf['CSV_qualifier'];
							$parter = $delimeter.$this->extConf['CSV_parter'].$delimeter;
							// Hook to make other types available for export
							if (is_array($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['ke_questionnaire']['CSVExportQBaseLine'])){
								foreach($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['ke_questionnaire']['CSVExportQBaseLine'] as $_classRef){
									$_procObj = & t3lib_div::getUserObj($_classRef);
									$lineset .= $_procObj->CSVExportQBaseLine($free_cells,$question['type'],$question['uid'],$this->simpleResults,$delimeter,$parter);
								}
							}
						break;
					
				}
			}
		}
		$csvdata .= $lineset."\n";

		//t3lib_div::devLog('getCSVQBase return', 'ke_questionnaire Export Mod', 0, array($csvheader,$csvdata));
		return $csvheader.$csvdata;
	}

	function simplifyResults(){
		$results = $this->results;
		$this->simpleResults = array();

		$marker = $this->extConf['CSV_marker'];

		$storage_pid = $this->ff_data['sDEF']['lDEF']['storage_pid']['vDEF'];
		$where = 'pid='.$storage_pid.' and hidden=0 and deleted=0 and type!="blind"';
		if (htmlentities(t3lib_div::_GP('only_this_lang'))){
			$lang = explode('_',htmlentities(t3lib_div::_GP('only_this_lang')));
			$where .= ' AND sys_language_uid='.$lang[1];
		}
		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*','tx_kequestionnaire_questions',$where,'','sorting');

		$fill_array = array();
		if ($res){
			if (t3lib_div::_GP('with_authcode')) {
				$fill_array['authcode'] = array();
				$fill_array['authcode']['uid'] = 'authcode';
				$fill_array['authcode']['title'] = 'authcode';
				$fill_array['authcode']['type'] = 'authcode';
			}
			$fill_array['start_tstamp'] = array();
			$fill_array['start_tstamp']['uid'] = 'start_tstamp';
			$fill_array['start_tstamp']['title'] = 'start tstamp';
			$fill_array['start_tstamp']['type'] = 'start_tstamp';
			$fill_array['finished_tstamp'] = array();
			$fill_array['finished_tstamp']['uid'] = 'finished_tstamp';
			$fill_array['finished_tstamp']['title'] = 'finished tstamp';
			$fill_array['finished_tstamp']['type'] = 'finished_tstamp';
			while($question = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)){
				$fill_array[$question['uid']] = array();
				$fill_array[$question['uid']]['uid'] = $question['uid'];
				$fill_array[$question['uid']]['title'] = $question['title'];
				$fill_array[$question['uid']]['type'] = $question['type'];
				switch ($question['type']){
					case 'closed':
							$where = 'question_uid='.$question['uid'].' and hidden=0 and deleted=0';
							$res_answers = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*','tx_kequestionnaire_answers',$where,'','sorting');
							//t3lib_div::devLog('getCSVQBase '.$question['type'], 'ke_questionnaire Export Mod', 0, array($GLOBALS['TYPO3_DB']->SELECTquery('*','tx_kequestionnaire_answers',$where,'','sorting')));
							if ($res_answers){
								$fill_array[$question['uid']]['answers'] = array();
								while ($answer = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res_answers)){
									$fill_array[$question['uid']]['answers'][$answer['uid']] = array();
									//$fill_array[$question['uid']]['answers'][$answer['uid']]['uid'] = $answer['uid'];
								}
							}
						break;
					case 'matrix':
							$columns = array();
							$where = 'question_uid='.$question['uid'].' and hidden=0 and deleted=0';
							$res_columns = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*','tx_kequestionnaire_columns',$where,'','sorting');
							if ($res_columns){
								while ($column = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res_columns)){
									$columns[] = $column;
									$fill_array[$question['uid']]['columns'][$column['uid']] = array();
									$fill_array[$question['uid']]['columns'][$column['uid']]['different_type'] = $column['different_type'];
									//$fill_array[$question['uid']]['columns'][$column['uid']]['uid'] = $column['uid'];
								}
							}
							$res_subquestions = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*','tx_kequestionnaire_subquestions',$where,'','sorting');
							if ($res_subquestions){
								while ($subquestion = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res_subquestions)){
									if ($subquestion['title_line'] == 1){
									} else {
										$fill_array[$question['uid']]['subquestions'][$subquestion['uid']] = array();
										$fill_array[$question['uid']]['subquestions'][$subquestion['uid']]['columns'] = array();
										if (is_array($columns)){
											foreach ($columns as $column){
												$fill_array[$question['uid']]['subquestions'][$subquestion['uid']]['columns'][$column['uid']] = array();
												//$fill_array[$question['uid']][$subline['uid']][$column['uid']] = 1;
											}
										}
									}
								}
							}
						break;
					case 'semantic':
							$columns = array();
							$where = 'question_uid='.$question['uid'].' and hidden=0 and deleted=0';
							$res_columns = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*','tx_kequestionnaire_columns',$where,'','sorting');
							if ($res_columns){
								while ($column = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res_columns)){
									$columns[] = $column;
									$fill_array[$question['uid']]['columns'][$column['uid']] = array();
									$fill_array[$question['uid']]['columns'][$column['uid']]['different_type'] = $column['different_type'];
									//$fill_array[$question['uid']]['columns'][$column['uid']]['uid'] = $column['uid'];
								}
							}
							$res_subquestions = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*','tx_kequestionnaire_sublines',$where,'','sorting');
							if ($res_subquestions){
								while ($subquestion = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res_subquestions)){
									if ($subquestion['title_line'] == 1){
									} else {
										$fill_array[$question['uid']]['subquestions'][$subquestion['uid']] = array();
										$fill_array[$question['uid']]['subquestions'][$subquestion['uid']]['columns'] = array();
										if (is_array($columns)){
											foreach ($columns as $column){
												$fill_array[$question['uid']]['subquestions'][$subquestion['uid']]['columns'][$column['uid']] = array();
												//$fill_array[$question['uid']][$subline['uid']][$column['uid']] = 1;
											}
										}
									}
								}
							}
						break;
					case 'demographic':
							//t3lib_div::devLog('getCSVQBase '.$question['type'], 'ke_questionnaire Export Mod', 0, $question);
							$flex = t3lib_div::xml2array($question['demographic_fields']);
							$fe_user_fields = explode(',',$flex['data']['sDEF']['lDEF']['FeUser_Fields']['vDEF']);
							$flex = t3lib_div::xml2array($question['demographic_addressfields']);
							$fe_user_addressfields = explode(',',$flex['data']['sDEF']['lDEF']['FeUser_Fields']['vDEF']);
							//t3lib_div::devLog('getCSVQBase flex', 'ke_questionnaire Export Mod', 0, array($fe_user_fields,$fe_user_addressfields));
							foreach ($fe_user_fields as $field){
								$fill_array[$question['uid']]['fe_users'][$field] = array();
							}
							foreach ($fe_user_addressfields as $field){
								$fill_array[$question['uid']]['tt_address'][$field] = array();
							}
							//$lineset .= $this->getQBaseLine($free_cells,$question['uid'],$question['type']);
						break;
				}
			}
		}
		$value_arrays = array();
		//t3lib_div::devLog('simplify results results', 'ke_questionnaire Export Mod', 0, $results);
		foreach ($results as $result){
			//t3lib_div::devLog('simplify results result', 'ke_questionnaire Export Mod', 0, $result);
			$value_arrays[$result['uid']] = $result;//t3lib_div::xml2array($result['xmldata']);
			$value_arrays[$result['uid']]['start_tstamp'] = $result['start_tstamp'];
			$value_arrays[$result['uid']]['finished_tstamp'] = $result['finished_tstamp'];
			$auth = t3lib_BEfunc::getRecord('tx_kequestionnaire_authcodes',$result['auth']); //test
			$value_arrays[$result['uid']]['authcode'] = $auth['authcode'];
			$fill_array['result_nrs'][] = $result['uid'];
		}
		//t3lib_div::devLog('simplify results value_arrays', 'ke_questionnaire Export Mod', 0, $value_arrays);

		foreach ($fill_array as $q_nr => $q_values){
			//t3lib_div::devLog('getCSVQBase q_values '.$q_nr, 'ke_questionnaire Export Mod', 0, $q_values);
			foreach ($value_arrays as $v_nr => $v_values){
				//$fill_array[$q_nr]['results'][$v_nr] = array();
				$act_v = $v_values[$q_nr];
				//t3lib_div::devLog('simplify results value_arrays '.$q_nr, 'ke_questionnaire Export Mod', 0, array($act_v,$v_values));
				switch ($q_values['type']){
					case 'authcode': $fill_array[$q_nr]['results'][$v_nr] = $act_v;
						break;
					case 'start_tstamp': $fill_array[$q_nr]['results'][$v_nr] = $act_v;
						break;
					case 'finished_tstamp': $fill_array[$q_nr]['results'][$v_nr] = $act_v;
						break;
					case 'open': $fill_array[$q_nr]['results'][$v_nr] = $act_v['answer'];
						break;
					case 'closed':
							//t3lib_div::devLog('closed '.$q_nr, 'ke_questionnaire Export Mod', 0, $act_v);
							if (is_array($act_v['answer']['options'])){
								foreach ($q_values['answers'] as $a_nr => $a_values){
									if (in_array($a_nr,$act_v['answer']['options'])){
										if ($act_v['answer']['text'][$a_nr]){
											$fill_array[$q_nr]['answers'][$a_nr]['results'][$v_nr] = '('.$act_v['answer']['text'][$a_nr].') '.$marker;
										} else {
											$fill_array[$q_nr]['answers'][$a_nr]['results'][$v_nr] = $marker;
										}
									}
								}
							} else {
								foreach ($q_values['answers'] as $a_nr => $a_values){
									if ($a_nr == $act_v['answer']['options']){
										if ($act_v['answer']['text'][$a_nr]){
											$fill_array[$q_nr]['answers'][$a_nr]['results'][$v_nr] = '('.$act_v['answer']['text'][$a_nr].') '.$marker;
										} else {
											$fill_array[$q_nr]['answers'][$a_nr]['results'][$v_nr] = $marker;
										}
									}
								}
							}

						break;
					case 'matrix':
					case 'semantic':
							//t3lib_div::devLog('matrix '.$q_nr, 'ke_questionnaire Export Mod', 0, $act_v);
							foreach ($q_values['subquestions'] as $sub_nr => $sub_values){
								//t3lib_div::devLog('matrix sub '.$sub_nr, 'ke_questionnaire Export Mod', 0, $sub_values);
								foreach ($sub_values['columns'] as $c_nr => $c_values){
									//t3lib_div::devLog('matrix sub c '.$c_nr, 'ke_questionnaire Export Mod', 0, $c_values);
									$temp_type = $act_v['subtype'];
									if ($q_values['columns'][$c_nr]['different_type'] != '') $temp_type = $q_values['columns'][$c_nr]['different_type'];
									//t3lib_div::devLog('matrix temp_type '.$temp_type, 'ke_questionnaire Export Mod', 0, $act_v);
									if ($temp_type == 'input'){
										$fill_array[$q_nr]['subquestions'][$sub_nr]['columns'][$c_nr]['results'][$v_nr] = $act_v['answer']['options'][$sub_nr][$c_nr][0];
									} elseif (is_array($act_v['answer']['options'][$sub_nr])){
										//if (in_array($c_nr,$act_v['answer']['options'][$sub_nr])){
										if ($act_v['answer']['options'][$sub_nr][$c_nr][0] == $c_nr){
											$fill_array[$q_nr]['subquestions'][$sub_nr]['columns'][$c_nr]['results'][$v_nr] = $marker;
										} elseif ($c_nr == $act_v['answer']['options'][$sub_nr]['single']) {
											$fill_array[$q_nr]['subquestions'][$sub_nr]['columns'][$c_nr]['results'][$v_nr] = $marker;
										}
									} else {
										if ($c_nr == $act_v['answer']['options'][$sub_nr]){
											$fill_array[$q_nr]['subquestions'][$sub_nr]['columns'][$c_nr]['results'][$v_nr] = $marker;
										}
									}
									if ($act_v['answer']['text'][$sub_nr]){
										$fill_array[$q_nr]['subquestions'][$sub_nr]['columns'][$c_nr]['results'][$v_nr] = '('.$act_v['answer']['text'][$sub_nr][0].') '.$fill_array[$q_nr]['subquestions'][$sub_nr]['columns'][$c_nr]['results'][$v_nr];
									}
								}
							}


						break;
					case 'demographic':
							//t3lib_div::devLog('demo '.$q_nr, 'ke_questionnaire Export Mod', 0, $act_v);
							if (is_array($act_v['answer']['fe_users'])){
								foreach ($act_v['answer']['fe_users'] as $fe_nr => $fe_values){
									$fill_array[$q_nr]['fe_users'][$fe_nr]['results'][$v_nr] = $fe_values;
								}
							}
							if (is_array($act_v['answer']['tt_address'])){
								foreach ($act_v['answer']['tt_address'] as $fe_nr => $fe_values){
									$fill_array[$q_nr]['tt_address'][$fe_nr]['results'][$v_nr] = $fe_values;
								}
							}
						break;
					default: 	
							// Hook to make other types available for export
							if (is_array($act_v) AND is_array($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['ke_questionnaire']['CSVExportSimplifyResults'])){
								foreach($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['ke_questionnaire']['CSVExportSimplifyResults'] as $_classRef){
									$_procObj = & t3lib_div::getUserObj($_classRef);
									$fill_array[$q_nr] = $_procObj->CSVExportSimplifyResults($q_values,$act_v, $v_nr, $marker, $fill_array[$q_nr]);
								}
							}
						break;
				}
				// Hook to make other types available for export
				if (is_array($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['ke_questionnaire']['CSVExportSimplifyResultsOther'])){
					foreach($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['ke_questionnaire']['CSVExportSimplifyResultsOther'] as $_classRef){
						$_procObj = & t3lib_div::getUserObj($_classRef);
						$fill_array = $_procObj->CSVExportSimplifyResultsOther($fill_array,$v_values,$v_nr);
					}
				}
			}
		}
		$this->simpleResults = $fill_array;
		//t3lib_div::devLog('getCSVQBase fill_array', 'ke_questionnaire Export Mod', 0, $fill_array);
		//t3lib_div::devLog('getCSVQBase simple results', 'ke_questionnaire Export Mod', 0, $this->simpleResults);
	}

	function getQBaseLine($free_cells,$question,$type,$answer=array(),$subquestion=0,$column=array(),$dem_field=''){
		//t3lib_div::devLog('getQBaseLine', 'ke_questionnaire Export Mod', 0, array($free_cells,$question,$type,$answer,$subquestion,$column,$dem_field));
		global $LANG;

		$delimeter = $this->extConf['CSV_qualifier'];
		$parter = $delimeter.$this->extConf['CSV_parter'].$delimeter;
		$line = array();
		for ($i = 0;$i < $free_cells;$i ++){
			$line[] = '';
		}

		$line_add = '';
		$take = $this->simpleResults[$question];
		$results = $this->simpleResults['result_nrs'];
		//t3lib_div::devLog('getCSVQBase take', 'ke_questionnaire Export Mod', 0, $take);
		//t3lib_div::devLog('getCSVQBase results '.$type, 'ke_questionnaire Export Mod', 0, $results);
		switch($type){
			case 'authcode': $line[] = '';
					foreach ($results as $nr => $result_id){
						$take['results'][$result_id] = str_replace($delimeter,$delimeter.$delimeter,$take['results'][$result_id]);
						$line[] = $take['results'][$result_id];
					}
					//t3lib_div::devLog('simplify results value_arrays '.$q_nr, 'ke_questionnaire Export Mod', 0, $line);
				break;
			case 'start_tstamp': $line[] = '';
					foreach ($results as $nr => $result_id){
						$take['results'][$result_id] = str_replace($delimeter,$delimeter.$delimeter,$take['results'][$result_id]);
						$line[] = $take['results'][$result_id];
					}
				break;
			case 'finished_tstamp': $line[] = '';
					foreach ($results as $nr => $result_id){
						$take['results'][$result_id] = str_replace($delimeter,$delimeter.$delimeter,$take['results'][$result_id]);
						$line[] = $take['results'][$result_id];
					}
				break;
			case 'open':	$line[] = '';
					foreach ($results as $nr => $result_id){
						$take['results'][$result_id] = str_replace($delimeter,$delimeter.$delimeter,$take['results'][$result_id]);
						$line[] = $take['results'][$result_id];
					}
				break;
			case 'closed': $line[] = $answer['title'];
					if (is_array($take['answers'][$answer['uid']]['results'])){
						//t3lib_div::devLog('getQbaseLine take '.$question, 'ke_questionnaire Export Mod', 0, $take);
						foreach ($results as $nr => $result_id){
							//t3lib_div::devLog('getQbaseLine take '.$result_id, 'ke_questionnaire Export Mod', 0, $take['answers'][$answer['uid']]['results']);
							if ($take['answers'][$answer['uid']]['results'][$result_id]){
								$take['answers'][$answer['uid']]['results'][$result_id] = str_replace($delimeter,$delimeter.$delimeter,$take['answers'][$answer['uid']]['results'][$result_id]);
								$line[] = $take['answers'][$answer['uid']]['results'][$result_id];
							} else {
								$line[] = '';
							}
						}
					} else {
						$line[] = '';
					}
				break;
			case 'semantic':
			case 'matrix': $line[] = $column['title'];
					if(is_array($take['subquestions'][$subquestion]['columns'][$column['uid']]['results'])){
						foreach ($results as $nr => $result_id){
							if ($take['subquestions'][$subquestion]['columns'][$column['uid']]['results'][$result_id]){
								$take['subquestions'][$subquestion]['columns'][$column['uid']]['results'][$result_id] = str_replace($delimeter,$delimeter.$delimeter,$take['subquestions'][$subquestion]['columns'][$column['uid']]['results'][$result_id]);
								$line[] = $take['subquestions'][$subquestion]['columns'][$column['uid']]['results'][$result_id];
							} else {
								$line[] = '';
							}
						}
					} else {
						$line[] = '';
					}
				break;
			case 'demographic': $line[] = $dem_field;
					if (is_array($take['fe_users'][$dem_field]['results'])){
						foreach ($results as $nr => $result_id){
							if ($take['fe_users'][$dem_field]['results'][$result_id]){
								$take['fe_users'][$dem_field]['results'][$result_id] = str_replace($delimeter,$delimeter.$delimeter,$take['fe_users'][$dem_field]['results'][$result_id]);
								$line[] = $take['fe_users'][$dem_field]['results'][$result_id];
							} else {
								$line[] = '';
							}
						}
					}
					if (is_array($take['tt_address'][$dem_field]['results'])){
						foreach ($results as $nr => $result_id){
							if ($take['tt_address'][$dem_field]['results'][$result_id]){
								$take['tt_address'][$dem_field]['results'][$result_id] = str_replace($delimeter,$delimeter.$delimeter,$take['tt_address'][$dem_field]['results'][$result_id]);
								$line[] = $take['tt_address'][$dem_field]['results'][$result_id];
							} else {
								$line[] = '';
							}
						}
					}
				break;
			default: 	
				break;
		}
		
		//t3lib_div::devLog('getCSVQBase line '.$type, 'ke_questionnaire Export Mod', 0, $line);
		return $delimeter.implode($parter,$line).$delimeter."\n";
	}

	function getQBaseHeaderLine(){
		global $LANG;
		$delimeter = $this->extConf['CSV_qualifier'];
		$parter = $delimeter.$this->extConf['CSV_parter'].$delimeter;

		$csvheader = $delimeter.$this->q_data['uid'].$parter.$this->q_data['header'].$delimeter."\n\n";

		$csvheader .= $delimeter;
		$csvheader .= $LANG->getLL('CSV_questionId').$parter;
		$csvheader .= $LANG->getLL('CSV_questionPlus').$parter;
		$csvheader .= $LANG->getLL('CSV_answer').$parter;
		$csvheader .= $LANG->getLL('CSV_resultIdPlus').$parter;
		$csvheader .= $parter;
		$csvheader .= $delimeter."\n";

		return $csvheader;
	}

	function getQBaseResultLine($free_cells){
		global $LANG;

		$delimeter = $this->extConf['CSV_qualifier'];
		$parter = $delimeter.$this->extConf['CSV_parter'].$delimeter;
		$line = array();
		for ($i = 0;$i < $free_cells;$i ++){
			$line[] = '';
		}
		foreach ($this->simpleResults['result_nrs'] as $nr => $values){
			//t3lib_div::devLog('getQbaseResultLine values', 'ke_questionnaire Export Mod', 0, $values);
			$line[] = $values;
		}
		return $delimeter.implode($parter,$line).$delimeter;
	}
	
	function getCSVSimple2(){
		global $LANG;

		$csvdata = '';
		$csvheader = '';
		$delimeter = $this->extConf['CSV_qualifier'];
		$parter = $delimeter.$this->extConf['CSV_parter'].$delimeter;
		
		$csvheader = $this->q_data['header']."\n\n";
		$this->simplifyResults();
		//t3lib_div::devLog('getCSVSimple simpleResults', 'ke_questionnaire Export Mod', 0, $this->simpleResults);
		
		if (is_array($this->simpleResults)){
			$headline = array();
			foreach ($this->simpleResults as $question_id => $values){
				if ($values['title'] != ''){
					switch ($values['type']){
						case 'closed':
							foreach ($values['answers'] as $a_id => $a_values){
								$answer = t3lib_BEfunc::getRecord('tx_kequestionnaire_answers',$a_id);
								$headline[] = $values['title'].'_'.$answer['title'];
							}
						break;
						case 'matrix':
							foreach ($values['subquestions'] as $sub_id => $sub_values){
								$subl = t3lib_BEfunc::getRecord('tx_kequestionnaire_subquestions',$sub_id);
								foreach ($sub_values['columns'] as $col_id => $col_values){
									$col = t3lib_BEfunc::getRecord('tx_kequestionnaire_columns',$col_id);
									$headline[] = $values['title'].'_'.$subl['title'].'_'.$col['title'];
								}
							}
						break;
						case 'semantic':
							foreach ($values['subquestions'] as $sub_id => $sub_values){
								$subl = t3lib_BEfunc::getRecord('tx_kequestionnaire_sublines',$sub_id);
								foreach ($sub_values['columns'] as $col_id => $col_values){
									$col = t3lib_BEfunc::getRecord('tx_kequestionnaire_columns',$col_id);
									$headline[] = $values['title'].'_'.$subl['title'].'_'.$col['title'];
								}
							}
						break;
						default:
							$headline[] = $values['title'];
						break;
					}
				}
			}
		}
		$csvheader .= $delimeter.implode($parter,$headline).$delimeter."\n";
		if (is_array($this->simpleResults['result_nrs'])){
			foreach ($this->simpleResults['result_nrs'] as $nr){
				$result_line = array();
				foreach ($this->simpleResults as $question_id => $values){
					if ($values['title'] != ''){
						switch ($values['type']){
							case 'closed':
								foreach ($values['answers'] as $a_id => $a_values){
									if ($values['answers'][$a_id]['results'][$nr]) $result_line[] = $values['answers'][$a_id]['results'][$nr];
									else $result_line[] = '';
								}
							break;
							case 'matrix':
							case 'sematic':
								foreach ($values['subquestions'] as $sub_id => $sub_values){
									foreach ($sub_values['columns'] as $col_id => $col_values){
										if ($values['subquestions'][$sub_id]['columns'][$col_id]['results'][$nr]) $result_line[] = $values['subquestions'][$sub_id]['columns'][$col_id]['results'][$nr];
										else $result_line[] = '';
									}
								}
							break;
							default:
								$result_line[] = $values['results'][$nr];
							break;
						}
					}	
				}
				$csvdata .= $delimeter.implode($parter,$result_line).$delimeter."\n";
			}
		}
	
		return $csvheader.$csvdata;
	}

	function getCSVSimple(){
		global $LANG;

		$csvdata = '';
		$csvheader = '';
		$delimeter = $this->extConf['CSV_qualifier'];
		$parter = $delimeter.$this->extConf['CSV_parter'].$delimeter;

		$csvheader = $this->q_data['header']."\n\n";
		//t3lib_div::devLog('getCSVSimple q_data', 'ke_questionnaire Export Mod', 0, $this->q_data);

		foreach ($this->results as $nr => $values){
			$result_array[$values['uid']] = t3lib_div::xml2array($values['xmldata']);
		}
		//t3lib_div::devLog('getCSVSimple result_array', 'ke_questionnaire Export Mod', 0, $result_array);

		foreach ($result_array as $nr => $result){
			$lineset = ''; //stores the CSV-data
			$line = array(); //single line, will be imploded
			$line[] = $LANG->getLL('CSV_resultId');
			$line[] = $nr;
			if (t3lib_div::_GP('with_authcode')) {
				$auth = t3lib_BEfunc::getRecord('tx_kequestionnaire_authcodes',$result['auth']);
				$line[] = 'AuthCode: '.$auth['authcode'];;
			}
			$lineset .= $delimeter.implode($parter,$line).$delimeter."\n";
			if (is_array($result)){
				foreach ($result as $question_id => $values){
					if ($values['type'] != ''){
						//t3lib_div::devLog('getCSVSimple values '.$question_id, 'ke_questionnaire Export Mod', 0, $values);
						//make a line with the question name and id
						$line = array();
						$line[] = $LANG->getLL('CSV_question').' ('.$values['type'].')';
						$line[] = $values['question_id'];
	
						$quest_text = $this->stripString($values['question']);
	
						$line[] = $quest_text;
						$lineset .= $delimeter.implode($parter,$line).$delimeter."\n";
						switch ($values['type']){
							case 'open':
								$line = array();
								$line[] = $LANG->getLL('CSV_answer');
								$line[] = '';
								$line[] = $values['answer'];
								$lineset .= $delimeter.implode($parter,$line).$delimeter."\n";
								break;
							case 'closed':
								if (is_array($values['answer']['options'])){
									foreach ($values['answer']['options'] as $option => $value){
										$line = array();
										$line[] = '';
										$line[] = $value;
										if ($values['answer']['text'][$value]){
											$temp_text = '';
											$temp_text = $this->stripString($values['answer']['text'][$value]);
											$line[] = $temp_text;
										} elseif ($values['possible_answers'][$value]){
											$temp_text = '';
											$temp_text = $this->stripString($values['possible_answers'][$value]);
											$line[] = $temp_text;
										} else {
											$line[] = $this->getPossibleAnswersData($values['type'],$value);
										}
										$lineset .= $delimeter.implode($parter,$line).$delimeter."\n";
									}
								}
								break;
							case 'matrix':
								//t3lib_div::devLog('getCSVSimple matrix '.$question_id, 'ke_questionnaire Export Mod', 0, $values);
								if (is_array($values['answer']['options'])){
									foreach ($values['answer']['options'] as $option => $value){
										$line = array();
										$line[] = '';
										$line[] = $option;
										$temp = '';
										$temp_text = '';
										$temp_text = $this->stripString($values['possible_answers']['lines'][$option]);
										$temp = $temp_text;
										if ($temp == '') $temp = $this->getPossibleAnswersData($values['type'].'_line',$option);
										$line[] = $temp;
										//t3lib_div::devLog('getCSVSimple matrix '.$question_id, 'ke_questionnaire Export Mod', 0, $line);
										$lineset .= $delimeter.implode($parter,$line).$delimeter."\n";
										if (is_array($value)){
											foreach($value as $c_option => $c_value){
												$line = array();
												$line[] = '';
												$line[] = '';
												$temp = '';
												$temp_text = '';
												$temp_text = $this->stripString($values['possible_answers'][$c_option]);
												$temp = $temp_text;
												if ($temp == '') $temp = $this->getPossibleAnswersData($values['type'].'_column',$c_option);
												$line[] = $temp;
												$line[] = $c_value;
												$lineset .= $delimeter.implode($parter,$line).$delimeter."\n";
											}
										} else {
											$line = array();
											$line[] = '';
											$line[] = '';
											$line[] = $c_value;
											$temp = '';
											$temp = $values['possible_answers'][$value];
											if ($temp == '') $temp = $this->getPossibleAnswersData($values['type'].'_column',$c_option);
											$line[] = $temp;
											$lineset .= $delimeter.implode($parter,$line).$delimeter."\n";
										}
									}
								}
								break;
							case 'demographic':
								if (is_array($values['answer']['fe_users'])){
									foreach ($values['answer']['fe_users'] as $field => $value){
										$line = array();
										$line[] = '';
										$line[] = $field;
										$line[] = $value;
										$lineset .= $delimeter.implode($parter,$line).$delimeter."\n";
									}
								}
								if (is_array($values['answer']['tt_address'])){
									foreach ($values['answer']['tt_address'] as $field => $value){
										$line = array();
										$line[] = '';
										$line[] = $field;
										$line[] = $value;
										$lineset .= $delimeter.implode($parter,$line).$delimeter."\n";
									}
								}
								break;
							case 'sematic':
								//t3lib_div::devLog('getCSVSimple semantic '.$question_id, 'ke_questionnaire Export Mod', 0, $values);
								//Muss auf Basis der "Possible Answers" gerendert werden.
								if (is_array($values['answer']['options'])){
									foreach ($values['answer']['options'] as $option => $value){
										$line = array();
										$line[] = '';
										$line[] = $option;
										$temp = '';
										$temp = $values['possible_answers']['lines'][$value]['start'].'...'.$values['possible_answers']['lines'][$value]['end'];
										if ($temp == '') $temp = $this->getPossibleAnswersData($values['type'].'_line',$option);
										$line[] = $temp;
										$lineset .= $delimeter.implode($parter,$line).$delimeter."\n";
										if (is_array($value)){
											foreach($value as $c_option => $c_value){
												$line = array();
												$line[] = '';
												$line[] = '';
												$temp = '';
												$temp = $values['possible_answers'][$value];
												if ($temp == '') $temp = $this->getPossibleAnswersData($values['type'].'_column',$c_option);
												$line[] = $temp;
												$line[] = $c_value;
												$lineset .= $delimeter.implode($parter,$line).$delimeter."\n";
											}
										} else {
											$line = array();
											$line[] = '';
											$line[] = '';
											$line[] = $c_value;
											$temp = '';
											$temp = $values['possible_answers'][$value];
											if ($temp == '') $temp = $this->getPossibleAnswersData($values['type'].'_column',$c_option);
											$line[] = $temp;
											$lineset .= $delimeter.implode($parter,$line).$delimeter."\n";
										}
									}
								}
								break;
							default:
								// Hook to make other types available for export
								if (is_array($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['ke_questionnaire']['CSVExportSimple'])){
									foreach($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['ke_questionnaire']['CSVExportSimple'] as $_classRef){
										$_procObj = & t3lib_div::getUserObj($_classRef);
										$lineset .= $_procObj->CSVSimpleExport($values,$delimeter);
									}
								}
								break;
						}
					}
				}
			}
			$csvdata .= $lineset."\n";
		}

		//t3lib_div::devLog('getCSVSimple return', 'ke_questionnaire Export Mod', 0, array($csvheader,$csvdata));
		return $csvheader.$csvdata;
	}

	function getSPSSDownload($type){
		require_once(t3lib_extMgm::extPath('ke_questionnaire_premium').'res/other/class.spss_export.php');
		$csvdata = '';
		$parter = $this->extConf['CSV_parter'];

		$base_filename = $this->q_id.'_spss_syntaxfile.sps';
		$data_filename = $this->q_id.'_spss_datafile.dat';

		$spss = $this->getSPSSBase($data_filename);
		$spss->qualifier = $this->pr_extConf['SPSS_qualifier'];
		$spss->delimeter = $this->pr_extConf['SPSS_delimeter'];
		switch ($type){
			case 'base':
				$data = $spss->get_def();
				$filename = $base_filename;
				break;
			case 'data':
				$data = $spss->get_data($this->results);
				$filename = $data_filename;
			default:
				break;
		}

		//$data = mb_convert_encoding($data, "Windows-1252", "UTF-8");
		header("content-type: application/spss");
		header("content-length: ".strlen($data));
		header("content-disposition: attachment; filename=\"$filename\"");

		print $data;
	}

	function getSPSSBase($data_filename){
		$base_row = array();
		/*$selectFields = '*';
		$where = 'pid='.$this->ff_data['sDEF']['lDEF']['storage_pid']['vDEF'];
		$where .= ' AND hidden=0 AND deleted=0';
		$orderBy = 'sorting';

		//t3lib_div::devLog('SPSS res', 'ke_questionnaire Export Mod', 0, array($GLOBALS['TYPO3_DB']->SELECTquery($selectFields,'tx_kequestionnaire_questions',$where,'',$orderBy)));
		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery($selectFields,'tx_kequestionnaire_questions',$where,$orderBy);
		if ($res){
			while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)){
				if ($row['type'] != 'blind'){
					$base_row[$row['uid']]['question_id'] = $row['uid'];
					$temp_text = $row['text'];
					if ($temp_text == '') $temp_text=$row['title'];
					$temp_text = strip_tags($temp_text);
					$temp_text = html_entity_decode($temp_text);
					$temp_text = preg_replace("/\r|\n/s", "", $temp_text);
					$base_row[$row['uid']]['question'] = $temp_text;
					$base_row[$row['uid']]['type'] = $row['type'];
					switch ($row['type']){
						case 'closed': $base_row[$row['uid']]['subtype'] = $row['closed_type'];
							break;
						case 'matrix': $base_row[$row['uid']]['subtype'] = $row['matrix_type'];
							break;
					}
				}
			}
		}*/
		$storage_pid = $this->ff_data['sDEF']['lDEF']['storage_pid']['vDEF'];
		$where = 'pid='.$storage_pid.' and hidden=0 and deleted=0 and type!="blind"';
		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*','tx_kequestionnaire_questions',$where,'','sorting');
		//t3lib_div::devLog('getCSVQBase res', 'ke_questionnaire Export Mod', 0, array($GLOBALS['TYPO3_DB']->SELECTquery('*'.'tx_kequestionnaire_questions',$where,'','sorting')));

		if ($res){
			while($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)){
			if ($row['type'] != 'blind'){
				$base_row[$row['uid']]['question_id'] = $row['uid'];
				$temp_text=$row['title'];
				//$temp_text = strip_tags($temp_text);
				//$temp_text = html_entity_decode($temp_text);
				//$temp_text = preg_replace("/\r|\n/s", "", $temp_text);
				$base_row[$row['uid']]['question'] = $temp_text;
				$base_row[$row['uid']]['type'] = $row['type'];
				switch ($row['type']){
					case 'open':
							$base_row[$row['uid']]['valtype'] = $row['open_validation'];
						break;
					case 'closed':
							$base_row[$row['uid']]['subtype'] = $row['closed_type'];
							//t3lib_div::devLog('getCSVQBase '.$question['type'], 'ke_questionnaire Export Mod', 0, $row);

							//if there are Inputfields in the closed answers spss needs to know
							$base_row[$row['uid']]['inputs'] = $row['closed_inputfield'];

							$where = 'question_uid='.$row['uid'].' and hidden=0 and deleted=0';
							$res_answers = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*','tx_kequestionnaire_answers',$where,'','sorting');
							//t3lib_div::devLog('getSPSSBase '.$row['type'], 'ke_questionnaire Export Mod', 0, array($GLOBALS['TYPO3_DB']->SELECTquery('*','tx_kequestionnaire_answers',$where,'','sorting')));
							if ($res_answers){
								while ($answer = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res_answers)){
									$base_row[$row['uid']]['possible_answers'][$answer['uid']] = $answer['title'];
								}
							}
						break;
					case 'matrix':
							$base_row[$row['uid']]['subtype'] = $row['matrix_type'];
							$columns = array();
							$where = 'question_uid='.$row['uid'].' and hidden=0 and deleted=0';
							$res_columns = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*','tx_kequestionnaire_columns',$where,'','sorting');
							if ($res_columns){
								while ($column = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res_columns)){
									//t3lib_div::devLog('getCSVQBase '.$question['type'], 'ke_questionnaire Export Mod', 0, $column);
									$base_row[$row['uid']]['possible_answers'][$column['uid']]['title'] = $column['title'];
									if ($column['different_type'] != '') $base_row[$row['uid']]['possible_answers'][$column['uid']]['diff_type'] = $column['different_type'];
									//else unset($base_row[$row['uid']]['diff_answertype']);
								}
							}
							$res_subquestions = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*','tx_kequestionnaire_subquestions',$where,'','sorting');
							if ($res_subquestions){
								while ($subquestion = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res_subquestions)){
									if ($subquestion['title_line'] == 1){
									} else {
										$base_row[$row['uid']]['possible_answers']['lines'][$subquestion['uid']]= $subquestion['title'];
									}
								}
							}
						break;
					case 'semantic':
							//t3lib_div::devLog('getCSVQBase '.$question['type'], 'ke_questionnaire Export Mod', 0, $question);
							$columns = array();
							$where = 'question_uid='.$row['uid'].' and hidden=0 and deleted=0';
							$res_columns = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*','tx_kequestionnaire_columns',$where,'','sorting');
							if ($res_columns){
								while ($column = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res_columns)){
									$base_row[$row['uid']]['possible_answers'][$column['uid']] = $column['title'];
								}
							}
							$res_sublines = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*','tx_kequestionnaire_sublines',$where,'','sorting');
							if ($res_sublines){
								while ($subline = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res_sublines)){
									$base_row[$row['uid']]['possible_answers']['lines'][$subline['uid']] = $subline['start'].'-'.$subline['end'];
								}
							}
						break;
					case 'demographic':
							$flex = t3lib_div::xml2array($row['demographic_fields']);
							if (is_array($flex)) $fe_user_fields = explode(',',$flex['data']['sDEF']['lDEF']['FeUser_Fields']['vDEF']);
							else $fe_user_fields = array();
							$flex = t3lib_div::xml2array($row['demographic_addressfields']);
							if (is_array($flex)) $fe_user_addressfields = explode(',',$flex['data']['sDEF']['lDEF']['FeUser_Fields']['vDEF']);
							else $fe_user_addressfields = array();
							foreach ($fe_user_fields as $field){
								$base_row[$row['uid']]['possible_answers']['fe_users'][] = $field;
							}
							foreach ($fe_user_addressfields as $field){
								$base_row[$row['uid']]['possible_answers']['tt_address'][] = $field;
							}
							//$lineset .= $this->getQBaseLine($free_cells,$question['uid'],$question['type']);
						break;
				}
			}
			}
		}
		//t3lib_div::devLog('SPSS base_row', 'ke_questionnaire Export Mod', 0, $base_row);
		$with_authcode = false;
		if ($this->ff_data['sDEF']['lDEF']['access']['vDEF'] == 'AUTH_CODE' AND t3lib_div::_GP('with_authcode') == 1){
			$with_authcode = true;
		}
		$spss = new spss_export($data_filename,$base_row,array(),$with_authcode);

		return $spss;
	}

	function getPDFDownload($type){
		if (t3lib_extMgm::isLoaded('ke_dompdf')){
			require_once(t3lib_extMgm::extPath('ke_questionnaire').'res/other/class.dompdf_export.php');
			$pdfdata = '';
	
			$conf = $this->loadTypoScriptForBEModule('tx_kequestionnaire');
			//t3lib_div::devLog('ts conf', 'ke_questionnaire Export Mod', 0, $conf);
			$pdf_conf = $conf['pdf.'];
			$storage_pid = $this->ff_data['sDEF']['lDEF']['storage_pid']['vDEF'];
	
			$pdf = new dompdf_export($pdf_conf,$storage_pid, $this->q_data['header'],$this->ff_data);
	
			switch ($type){
				case 'blank':
					$pdfdata = $pdf->getPDFBlank();
					break;
				case 'filled':
					$row = t3lib_BEfunc::getRecord('tx_kequestionnaire_results',t3lib_div::_GP('result_id_filled'));
					//t3lib_div::devLog('result_row', 'ke_questionnaire Export Mod', 0, $row);
					$temp_array = '';
					$encoding = "UTF-8";
					if ( true === mb_check_encoding ($row['xmldata'], $encoding ) ){
						$temp_array = t3lib_div::xml2array($row['xmldata']);
						if (count($temp_array) == 1) $temp_array = t3lib_div::xml2array(utf8_encode($row['xmldata']));
					} else {
						$temp_array = t3lib_div::xml2array(utf8_encode($row['xmldata']));
					}
					$pdfdata = $pdf->getPDFFilled($temp_array);
					break;
				case 'compare':
					$row = t3lib_BEfunc::getRecord('tx_kequestionnaire_results',t3lib_div::_GP('result_id_compare'));
					t3lib_div::devLog('result_row', 'ke_questionnaire Export Mod', 0, $row);
					$temp_array = '';
					$encoding = "UTF-8";
					if ( true === mb_check_encoding ($row['xmldata'], $encoding ) ){
						$temp_array = t3lib_div::xml2array($row['xmldata']);
						if (count($temp_array) == 1) $temp_array = t3lib_div::xml2array(utf8_encode($row['xmldata']));
					} else {
						$temp_array = t3lib_div::xml2array(utf8_encode($row['xmldata']));
					}
					$pdfdata = $pdf->getPDFCompare($temp_array);
					break;
				default:
					break;
			}
		} /*elseif (t3lib_extMgm::isLoaded('fpdf')){
			require_once(t3lib_extMgm::extPath('ke_questionnaire').'res/other/class.fpdf_export.php');
			$pdfdata = '';
	
			$conf = $this->loadTypoScriptForBEModule('tx_kequestionnaire');
			$pdf_conf = $conf['pdf.'];
			$storage_pid = $this->ff_data['sDEF']['lDEF']['storage_pid']['vDEF'];
	
			$pdf = new pdf_export($pdf_conf,$storage_pid, $this->q_data['header'],$this->ff_data['tDEF']['lDEF']['description']['vDEF']);
	
			switch ($type){
				case 'blank':
					$pdfdata = $pdf->getPDFBlank();
					break;
				default:
					break;
			}
	
			//$pdfdata = mb_convert_encoding($pdfdata, "Windows-1252", "UTF-8");
			header("content-type: application/pdf");
			header("content-length: ".strlen($pdfdata));
			header("content-disposition: attachment; filename=\"".$this->q_id."_blank.pdf\"");
	
			print $pdfdata;	
		}*/		
	}

	function getPossibleAnswersData($q_type,$answer_id){
		$data = '';

		switch ($q_type){
			case 'closed':
					$answer = t3lib_BEfunc::getRecord('tx_kequestionnaire_answers',$answer_id);
					$data = $answer['title'];
					if ($data == '') $data = $answer['text'];
				break;
			case 'matrix_line':
					$line = t3lib_BEfunc::getRecord('tx_kequestionnaire_subquestions',$answer_id);
					//t3lib_div::devLog('getCSVSimple line', 'ke_questionnaire Export Mod', 0, $line);
					$data = $line['title'];
					if ($data == '') $data = $line['text'];
				break;
			case 'matrix_column':
			case 'semantic_column':
					$column = t3lib_BEfunc::getRecord('tx_kequestionnaire_columns',$answer_id);
					//t3lib_div::devLog('getCSVSimple column', 'ke_questionnaire Export Mod', 0, $column);
					$data = $column['title'];
				break;
			case 'semantic_line':
					$line = t3lib_BEfunc::getRecord('tx_kequestionnaire_sublines',$answer_id);
					//t3lib_div::devLog('getCSVSimple column', 'ke_questionnaire Export Mod', 0, $column);
					$data = $line['start'].'...'.$line['end'];
				break;
		}

		$temp_text = $this->stripString($data);

		return $temp_text;
	}

	/**
	* Loads the TypoScript for the given extension prefix, e.g. tx_cspuppyfunctions_pi1, for use in a backend module.
	*
	* @param string $extKey
	* @return array
	*/
        function loadTypoScriptForBEModule($extKey) {
			global $TYPO3_CONF_VARS;
			require_once(PATH_t3lib . 'class.t3lib_page.php');
			require_once(PATH_t3lib . 'class.t3lib_tstemplate.php');
			require_once(PATH_t3lib . 'class.t3lib_tsparser_ext.php');
			//list($page) = t3lib_BEfunc::getRecordsByField('pages', 'pid', 0);
			//$pageUid = intval($page['uid']);
			$pageUid = $this->id;
			$sysPageObj = t3lib_div::makeInstance('t3lib_pageSelect');
			$rootLine = $sysPageObj->getRootLine($pageUid);
			$TSObj = t3lib_div::makeInstance('t3lib_tsparser_ext');
			$TSObj->tt_track = 0;
			$TSObj->init();
			$TSObj->runThroughTemplates($rootLine);
			$TSObj->generateConfig();
			//t3lib_div::devLog('PDF constants', 'ke_questionnaire Export Mod', 0, $TYPO3_CONF_VARS);
			//return $TSObj->flatSetup;
			return $TSObj->setup_constants['plugin.'][$extKey.'.'];
        }

	function stripString($temp){
		$temp = strip_tags($temp);
		$temp = html_entity_decode($temp);
		//$temp = preg_replace("/\r|\n/s", "", $temp);
		return $temp;
	}
}



if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/ke_questionnaire/mod3/index.php']){
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/ke_questionnaire/mod3/index.php']);
}




// Make instance:
$SOBE = t3lib_div::makeInstance('tx_kequestionnaire_module3');
$SOBE->init();

// Include files?
foreach($SOBE->include_once as $INC_FILE)	include_once($INC_FILE);

$SOBE->main();
$SOBE->printContent();

?>
