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
		$this->temp_file = 'tx_kequestionnaire_temp_'.$this->q_id.'_'.$GLOBALS['BE_USER']->user['uid'];
		
		$this->extConf = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['ke_questionnaire']);
		if (t3lib_extMgm::isLoaded('ke_questionnaire_premium')) $this->pr_extConf = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['ke_questionnaire_premium']);

		if ($this->q_id > 0){
			$this->q_data = t3lib_BEfunc::getRecord('tt_content',$this->q_id);
			$ff_data = t3lib_div::xml2array($this->q_data['pi_flexform']);
			$this->ff_data = $ff_data['data'];
		}
		
		//t3lib_div::devLog('getCSVInfos POST', 'ke_questionnaire Export Mod', 0, $_POST);
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
			
			$this->doc->loadJavascriptLib('contrib/prototype/prototype.js');
			$this->doc->loadJavascriptLib('js/common.js');

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
		
		//t3lib_div::debug($_GET,'get');
		//t3lib_div::debug($this->MOD_SETTINGS,'settings');
		if ($this->q_id == 0){
			$title = $LANG->getLL('none_selected');
			$content = $LANG->getLL('none_selected');
		} else {
			switch((string)$this->MOD_SETTINGS['function'])	{
				//CSV
				case 1:
					$title = $LANG->getLL('function1');
					if (!t3lib_div::_GP('get_csv_parted')){
						$content = $this->getCSVInfos();
						if (t3lib_div::_GP('get_csv_parted_download')){
							$content .= $this->getCSVDownload();
							exit;
						} else {
							//t3lib_div::debug($_POST,'post');
							if (t3lib_div::_GP('get_csv')){
								$this->createDataFileAtOnce();
								$content .= $this->getCSVDownload();
								exit;
							}
						}
					} else {
						$myVars = $GLOBALS['BE_USER']->getSessionData('tx_kequestionnaire');
						$pointer = $myVars['pointer'];
						if (t3lib_div::_GP('download_type') != '') {
							$myVars['download_type'] = t3lib_div::_GP('download_type');
							//t3lib_div::devLog('ein Download Type', 'ke_questionnaire Export Mod', 0, $_POST);
						}
						if (t3lib_div::_GP('only_this_lang') != '') {
							$myVars['only_this_lang'] = t3lib_div::_GP('only_this_lang');
							t3lib_div::devLog('only_this_lang POST', 'ke_questionnaire Export Mod', 0, $_POST);
						}
						if (t3lib_div::_GP('only_finished') != '') {
							$myVars['only_finished'] = t3lib_div::_GP('only_finished');
							t3lib_div::devLog('only_finished POST', 'ke_questionnaire Export Mod', 0, $_POST);
						}
						$GLOBALS['BE_USER']->setAndSaveSessionData('tx_kequestionnaire',$myVars);
						
						$this->results = $myVars['results'];
						//t3lib_div::debug($_POST,'post');
						//t3lib_div::debug($myVars,'myVars');
						if ($myVars['giveDownload'] == 1){
							$content .= $this->getCSVDownload();
							exit;
						} else {
							//t3lib_div::debug(t3lib_div::_GP('createTask'));
							if (t3lib_div::_GP('createTask')){
								if (t3lib_div::_GP('mailExportTo') == ''){
									$content = $LANG->getLL('task_no_mail');
								} else {
									$this->createSchedulerTask();
									$content = $LANG->getLL('task_created');
								}
							} else {
								$content = $this->createDataFile($pointer);
							}
						}
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
		//t3lib_div::debug($content);
		$content = $this->doc->insertStylesAndJS($content);
		$this->content.=$this->doc->section($title,$content,0,1);
		
	}
	
	function loadResults(){
		$counters = array();
		$counters['counting'] = 0;
		$counters['finished'] = 0;
		
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
		
		if ($res){
			$result_array = '';
			$encoding = "UTF-8";
			while($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)){
				if ($row['xmldata'] != '') {
					//$temp .= t3lib_div::view_array($row);
					$temp_array = array();
					$temp_array['uid'] = $row['uid'];
					$temp_array['start_tstamp'] = $row['start_tstamp'];
					$temp_array['finished_tstamp'] = $row['finished_tstamp'];
					$this->results[] = $temp_array;
					
					$langs[$row['sys_language_uid']] = 1;
					if ($row['finished_tstamp'] > 0) $finished ++;
					$counting ++;
				}
			}
		}
	
		$counters['counting'] = $counting;
		$counters['finished'] = $finished;
		$counters['langs'] = $langs;
		
		//t3lib_div::debug($this->results, 'result_array');
		
		return $counters;
	}

	function getCSVInfos(){
		t3lib_div::devLog('getCSVInfos POST', 'ke_questionnaire Export Mod', 0, $_POST);
		//t3lib_div::devLog('extconf', 'ke_questionnaire Export Mod', 0, $this->extConf);
		global $LANG;

		$content = '';
		$counters = $this->loadResults();
		$langs = $counters['langs'];
		
		$content = $LANG->getLL('result_count').': '.$counters['counting'].'<br />';
		$content .= $LANG->getLL('finished_count').': '.$counters['finished'];

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
			foreach ($counters['langs'] as $key => $is){
				if ($key != 0) {
					$content .= '<p><input type="checkbox" name="only_this_lang" value="L_'.(string)$this->q_data['sys_language_uid'].'" /> '.$LANG->getLL('download_only_this_lang').'</p>';
					break;
				}
			}
		}
		$content .= '<br />';
		
		//set some vars in the session
		$myVars = $GLOBALS['BE_USER']->getSessionData('tx_kequestionnaire');
		$myVars['q_id'] = $this->q_id;
		$myVars['pid'] = $this->pid;
		$myVars['ff_data'] = $this->ff_data;
		$myVars['q_lang'] = $this->q_data['sys_language_uid'];
		if (t3lib_div::_GP('only_this_lang') != '') {
			$myVars['only_this_lang'] = t3lib_div::_GP('only_this_lang');
			//t3lib_div::devLog('ein Download Type', 'ke_questionnaire Export Mod', 0, $_POST);
		}
		if (t3lib_div::_GP('only_finished') != '') {
			$myVars['only_finished'] = t3lib_div::_GP('only_finished');
			//t3lib_div::devLog('ein Download Type', 'ke_questionnaire Export Mod', 0, $_POST);
		}
		if (t3lib_div::_GP('download_type') != '') {
			$myVars['download_type'] = t3lib_div::_GP('download_type');
			//t3lib_div::devLog('ein Download Type', 'ke_questionnaire Export Mod', 0, $_POST);
		}
		//else
		//t3lib_div::devLog('hmm', 'ke_questionnaire Export Mod', 0, $_POST);
		$myVars['results'] = $this->results;
		if ($counters['counting'] > $this->extConf['exportParter']){
			$content .= '<div style="color:red">'.$LANG->getLL('download_parts').'</div><br />';
			//t3lib_div::devLog('session', 'ke_questionnaire Export Mod', 0, $myVars);
			$myVars['pointer'] = 0;
			$content .= '<input type="checkbox" name="createTask"/> '.$LANG->getLL('create_with_task').'<br />';
			$content .= $LANG->getLL('create_export_mail').' <input type="text" name="mailExportTo" style="width:200px"/><br /><br /><br />';
			$content .= '<input type="submit" name="get_csv_parted" value="'.$LANG->getLL('create_export_button').'" />';
		} else {
			$content .= '<input type="submit" name="get_csv" value="'.$LANG->getLL('download_button').'" />';	
		}
		//t3lib_div::devLog('session', 'ke_questionnaire Export Mod', 0, $myVars);
		//t3lib_div::devLog('_POST', 'ke_questionnaire Export Mod', 0, $_POST);
		//t3lib_div::devLog('_GET', 'ke_questionnaire Export Mod', 0, $_GET);
		$GLOBALS['BE_USER']->setAndSaveSessionData('tx_kequestionnaire',$myVars);
		$content .= '</p>';

		return $content;
	}

	function getSPSSInfos(){
		//t3lib_div::devLog('getSPSSInfos GET', 'ke_questionnaire Export Mod', 0, $_GET);
		//t3lib_div::devLog('getSPSSInfos POST', 'ke_questionnaire Export Mod', 0, $_POST);
		global $LANG;

		$content = '';
		$counters = $this->loadResults();
		
		$content = $LANG->getLL('result_count').': '.$counters['counting'].'<br />';
		$content .= $LANG->getLL('finished_count').': '.$counters['finished'];

		$content .= '<p><br /><hr />';
		$content .= '<p><input type="checkbox" name="only_finished" value="1" checked /> '.$LANG->getLL('download_only_finished').'</p><br />';
		if ($this->ff_data['sDEF']['lDEF']['access']['vDEF'] == 'AUTH_CODE'){
			$content .= '<p><input type="checkbox" name="with_authcode" value="1" /> '.$LANG->getLL('download_with_authcode').'</p>';
		}
		//check if the selected plugin lang has own results
		if ($this->q_data['sys_language_uid'] > 0 AND $langs[$this->q_data['sys_language_uid']] == 1){
			$content .= '<p><input type="checkbox" name="only_this_lang" value="L_'.$this->q_data['sys_language_uid'].'" /> '.$LANG->getLL('download_only_this_lang').'</p>';
		} else if ($this->q_data['sys_language_uid'] == 0 AND $langs[0] == 1){
			foreach ($counters['langs'] as $key => $is){
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
	
	function createSchedulerTask(){
		$file_path = PATH_site.'typo3temp/'.$this->temp_file;
		if (file_exists($file_path)) {
		    unlink($file_path);
		}
		//instance of scheduler
		$scheduler = t3lib_div::makeInstance('tx_scheduler');
		//instance of task-class
		$task = t3lib_div::makeInstance('tx_kequestionnaire_scheduler_export');
		//make it recurrent
		//$task->registerSingleExecution(time());
		$task->registerRecurringExecution(time(),'1');
		
		//add Mail Address
		$task->mailTo = t3lib_div::_GP('mailExportTo');
		//add Question id
		$task->q_id = $this->q_id;
		//add pid
		$task->pid = $this->pid;
		//add result-array
		$task->results = $this->results;
		//add download-type
		$type = t3lib_div::_GP('download_type');
		if ($type == ''){
			$myVars = $GLOBALS['BE_USER']->getSessionData('tx_kequestionnaire');
			$type = $myVars['download_type'];
		}
		$task->export_type = $type;
		//file-name for export
		$task->temp_file = $this->temp_file;
		//ffdata
		$task->ff_data = $this->ff_data;
		//q_data
		$task->q_data = $this->q_data;
		//download only this lang
		$only_this_lang = t3lib_div::_GP('only_this_lang');
		if ($only_this_lang == ''){
			$myVars = $GLOBALS['BE_USER']->getSessionData('tx_kequestionnaire');
			$only_this_lang = $myVars['only_this_lang'];
		}
		$task->only_this_lang = $only_this_lang;
		//download only finished
		$only_finished = t3lib_div::_GP('only_finished');
		if ($only_this_lang == ''){
			$myVars = $GLOBALS['BE_USER']->getSessionData('tx_kequestionnaire');
			$only_finished = $myVars['only_finished'];
		}
		$task->only_finished = $only_finished;
		//add to database
		$scheduler->addTask($task);
	}
	
	function createDataFileAtOnce(){
		include_once('ajax.php');
		$creator = t3lib_div::makeInstance('tx_kequestionnaire_module3_ajax');
		$creator->init();
		
		$type = t3lib_div::_GP('download_type');
		if ($type == ''){
			$myVars = $GLOBALS['BE_USER']->getSessionData('tx_kequestionnaire');
			$type = $myVars['download_type'];
		}
		
		//delete the old generated file
		$file_path = PATH_site.'typo3temp/'.$this->temp_file;
		if (file_exists($file_path)) {
		    unlink($file_path);
		}
		
		//t3lib_div::devLog('atOnce', 'ke_questionnaire Export Mod', 0, $this->results);
		foreach ($this->results as $nr => $result){
			switch ($type){
				case 'questions': $creator->createDataFile($nr);
					break;
				case 'simple2': $creator->createDataFileType2($nr);
					break;
				default: $creator->createHookedDataFileType($nr);
					break;
			}
		}
	}
	
	function createDataFile($pointer){
		//t3lib_div::debug($_ENV);
		//t3lib_div::debug($_SERVER);
		global $LANG;
		//simplify the results for better export
		$content = $LANG->getLL('download_count');
		//t3lib_div::debug($content);
		$counted = count($this->results);
		$content = sprintf($content,$pointer,$counted);
		//javascript hinzu fügen für reload
		//t3lib_div::debug($_REQUEST);
		//if (t3lib_div::_GP('get_csv_parted') != 1) $content .= '<script type="text/javascript">window.location.href=window.location.href+"&get_csv_parted=1"</script>';
		//else $content .= '<script type="text/javascript">window.location.href=window.location.href</script>';
		$content .= "<script type=\"text/javascript\">
//var max = $counted;
var max = 3;
var pointer = $pointer;
 function callFileCreate () {
	new Ajax.Request('../../../../typo3/ajax.php', {
	    method: 'get',
	    
	    parameters: 'ajaxID=tx_kequestionnaire::csv_createDataFile&pointer='+pointer,
	    onComplete: function(xhr, json) {
		// display results, should be The tree works
		if (xhr.responseText <= max){
			$('pointer').update(xhr.responseText);
			pointer = pointer + 1;
			callFileCreate();
		} else {
			window.location.href=window.location.href+\"&get_csv_parted_download=1\";
		}
	    }.bind(this),
	    onT3Error: function(xhr, json) {
		//display error
	    }.bind(this)
	});
}
Event.observe(window, 'load', function() { 
	callFileCreate();
});
</script>
";

		//delete the old generated file
		$file_path = PATH_site.'typo3temp/'.$this->temp_file;
		if (file_exists($file_path)) {
		    unlink($file_path);
		}
		
		//return the marker to show wich result is worked on
		return $content;
	}

	function getCSVDownload(){
		//t3lib_div::devLog('getCSVInfos GET', 'ke_questionnaire Export Mod', 0, $_GET);
		//t3lib_div::devLog('getCSVInfos POST', 'ke_questionnaire Export Mod', 0, $_POST);

		$csvdata = '';
		$parter = $this->extConf['CSV_parter'];
		$type = t3lib_div::_GP('download_type');
		if ($type == ''){
			$myVars = $GLOBALS['BE_USER']->getSessionData('tx_kequestionnaire');
			$type = $myVars['download_type'];
		}
		$only_this_lang = t3lib_div::_GP('only_this_lang');
		if ($only_this_lang == ''){
			$only_this_lang = $myVars['only_this_lang'];
		}
		$only_finished = t3lib_div::_GP('only_finished');
		if ($only_finished == ''){
			$only_finished = $myVars['only_finished'];
		}
		t3lib_div::devLog('getCSVDownload session '.$only_this_lang, 'ke_questionnaire Export Mod', 0, $myVars);
		
		require_once(t3lib_extMgm::extPath('ke_questionnaire').'res/other/class.csv_export.php');
		$csv_export = new csv_export($this->extConf,$this->results,$this->q_data,$this->ff_data,$this->temp_file,$only_this_lang,$only_finished);
		
		switch ($type){
			/*case 'simple':
				//$csvdata = $this->getCSVSimple();
				$csvdata = $csv_export->getCSVSimple();
				break;*/
			case 'simple2':
				//$csvdata = $this->getCSVSimple2();
				$csvdata = $csv_export->getCSVSimple2();
				break;
			case 'questions':
				//$csvdata = $this->getCSVQBased();
				$csvdata = $csv_export->getCSVQBased();
				break;
			default:
				// Hook for other CSV-Export-Types
				if (is_array($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['ke_questionnaire']['CSVExportTypeDownload'])){
					foreach($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['ke_questionnaire']['CSVExportTypeDownload'] as $_classRef){
						$_procObj = & t3lib_div::getUserObj($_classRef);
						$csvdata = $_procObj->CSVExportTypeDownload($this);
					}
				}
				break;
		}
	
		$csvdata = mb_convert_encoding($csvdata, "Windows-1252", "UTF-8");
		header("content-type: application/csv-tab-delimited-table");
		header("content-length: ".strlen($csvdata));
		header("content-disposition: attachment; filename=\"".$this->q_id."_csv_export.csv\"");
	
		print $csvdata;
	}

	
/**
 * old function, will be deleted soon
	function simplifyResults(){
		$results = $this->results;
		//t3lib_div::devLog('results', 'ke_questionnaire Export Mod', 0, $this->results);
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
							//$lineset .= $this->getQBaseLine($free_cells,$question);
						break;
				}
			}
		}
		$value_arrays = array();
		//t3lib_div::devLog('simplify results results', 'ke_questionnaire Export Mod', 0, $results);
		$result_nrs = array();
		foreach ($results as $result){
			//t3lib_div::devLog('simplify results result', 'ke_questionnaire Export Mod', 0, $result);
			$value_arrays[$result['uid']] = $result;//t3lib_div::xml2array($result['xmldata']);
			$value_arrays[$result['uid']]['start_tstamp'] = $result['start_tstamp'];
			$value_arrays[$result['uid']]['finished_tstamp'] = $result['finished_tstamp'];
			$auth = t3lib_BEfunc::getRecord('tx_kequestionnaire_authcodes',$result['auth']); //test
			$value_arrays[$result['uid']]['authcode'] = $auth['authcode'];
			$result_nrs[] = $result['uid'];
		}
		//t3lib_div::devLog('simplify results value_arrays', 'ke_questionnaire Export Mod', 0, $value_arrays);
		//t3lib_div::devLog('fill array', 'ke_questionnaire Export Mod', 0, $fill_array);
		$file_path = PATH_site.'typo3temp/'.$this->temp_file;
		if (file_exists($file_path)) unlink($file_path);
		$store_file = fopen($file_path,'a+');
		foreach ($fill_array as $q_nr => $q_values){
			//t3lib_div::devLog('getCSVQBase q_values '.$q_nr, 'ke_questionnaire Export Mod', 0, $q_values);
			$write_array = array();
			foreach ($value_arrays as $v_nr => $v_values){
				$write_array['results'][$v_nr] = array();
				$act_v = $v_values[$q_nr];
				$get_where = 'uid = '.$v_nr;
				$get_answers = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*','tx_kequestionnaire_results',$get_where);
				if ($get_answers){
					$arow = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($get_answers);
					$encoding = "UTF-8";
					if ( true === mb_check_encoding ($arow['xmldata'], $encoding ) ){
						$result_array = t3lib_div::xml2array($arow['xmldata']);
						if (count($result_array) == 1) $result_array = t3lib_div::xml2array(utf8_encode($arow['xmldata']));
					} else {
						$result_array = t3lib_div::xml2array(utf8_encode($arow['xmldata']));
					}
					//t3lib_div::devLog('r '.$q_nr, 'ke_questionnaire Export Mod', 0, array($result_array, $row));
					$act_v = $result_array[$q_nr];
				}
				//t3lib_div::devLog('simplify results value_arrays '.$q_nr, 'ke_questionnaire Export Mod', 0, array($act_v,$v_values));
				switch ($q_values['type']){
					case 'authcode': $write_array['results'][$v_nr] = $act_v;
						break;
					case 'start_tstamp': $write_array['results'][$v_nr] = $act_v;
						break;
					case 'finished_tstamp': $write_array['results'][$v_nr] = $act_v;
						break;
					case 'open': $write_array['results'][$v_nr] = $act_v['answer'];
						break;
					case 'closed':
							//t3lib_div::devLog('closed '.$q_nr, 'ke_questionnaire Export Mod', 0, $act_v);
							if (is_array($act_v['answer']['options'])){
								foreach ($q_values['answers'] as $a_nr => $a_values){
									if (in_array($a_nr,$act_v['answer']['options'])){
										if ($act_v['answer']['text'][$a_nr]){
											$write_array['answers'][$a_nr]['results'][$v_nr] = '('.$act_v['answer']['text'][$a_nr].') '.$marker;
										} else {
											$write_array['answers'][$a_nr]['results'][$v_nr] = $marker;
										}
									}
								}
							} else {
								foreach ($q_values['answers'] as $a_nr => $a_values){
									//t3lib_div::devLog('closed '.$q_nr, 'ke_questionnaire Export Mod', 0, array($a_nr,$a_values,$act_v['answer']['options']));
									if ($a_nr == $act_v['answer']['options']){
										if ($act_v['answer']['text'][$a_nr]){
											$write_array['answers'][$a_nr]['results'][$v_nr] = '('.$act_v['answer']['text'][$a_nr].') '.$marker;
										} else {
											$write_array['answers'][$a_nr]['results'][$v_nr] = $marker;
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
										$write_array['subquestions'][$sub_nr]['columns'][$c_nr]['results'][$v_nr] = $act_v['answer']['options'][$sub_nr][$c_nr][0];
									} elseif (is_array($act_v['answer']['options'][$sub_nr])){
										//if (in_array($c_nr,$act_v['answer']['options'][$sub_nr])){
										if ($act_v['answer']['options'][$sub_nr][$c_nr][0] == $c_nr){
											$write_array['subquestions'][$sub_nr]['columns'][$c_nr]['results'][$v_nr] = $marker;
										} elseif ($c_nr == $act_v['answer']['options'][$sub_nr]['single']) {
											$write_array['subquestions'][$sub_nr]['columns'][$c_nr]['results'][$v_nr] = $marker;
										}
									} else {
										if ($c_nr == $act_v['answer']['options'][$sub_nr]){
											$write_array['subquestions'][$sub_nr]['columns'][$c_nr]['results'][$v_nr] = $marker;
										}
									}
									if ($act_v['answer']['text'][$sub_nr]){
										$write_array['subquestions'][$sub_nr]['columns'][$c_nr]['results'][$v_nr] = '('.$act_v['answer']['text'][$sub_nr][0].') '.$write_array['subquestions'][$sub_nr]['columns'][$c_nr]['results'][$v_nr];
									}
								}
							}


						break;
					case 'demographic':
							//t3lib_div::devLog('demo '.$q_nr, 'ke_questionnaire Export Mod', 0, $act_v);
							if (is_array($act_v['answer']['fe_users'])){
								foreach ($act_v['answer']['fe_users'] as $fe_nr => $fe_values){
									$write_array['fe_users'][$fe_nr]['results'][$v_nr] = $fe_values;
								}
							}
							if (is_array($act_v['answer']['tt_address'])){
								foreach ($act_v['answer']['tt_address'] as $fe_nr => $fe_values){
									$write_array['tt_address'][$fe_nr]['results'][$v_nr] = $fe_values;
								}
							}
						break;
					default: 	
							// Hook to make other types available for export
							if (is_array($act_v) AND is_array($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['ke_questionnaire']['CSVExportSimplifyResults'])){
								foreach($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['ke_questionnaire']['CSVExportSimplifyResults'] as $_classRef){
									$_procObj = & t3lib_div::getUserObj($_classRef);
									$write_array = $_procObj->CSVExportSimplifyResults($q_values,$act_v, $v_nr, $marker, $write_array);
								}
							}
						break;
				}
				// Hook to make other types available for export
				if (is_array($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['ke_questionnaire']['CSVExportSimplifyResultsOther'])){
					foreach($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['ke_questionnaire']['CSVExportSimplifyResultsOther'] as $_classRef){
						$_procObj = & t3lib_div::getUserObj($_classRef);
						$write_array = $_procObj->CSVExportSimplifyResultsOther($write_array,$v_values,$v_nr);
					}
				}
			}
			//t3lib_div::devLog('fill_array', 'ke_questionnaire Export Mod', 0, $fill_array);
			//t3lib_div::devLog('write_array '.$q_nr.'/'.$v_nr, 'ke_questionnaire Export Mod', 0, $write_array);
			if (is_array($write_array) AND count($write_array) > 0) fwrite($store_file,json_encode($write_array)."\n");
		}
		fclose($store_file);
		$fill_array['result_nrs'] = $result_nrs;
		$this->simpleResults = $fill_array;
		//t3lib_div::devLog('getCSVQBase fill_array', 'ke_questionnaire Export Mod', 0, $fill_array);
		//t3lib_div::devLog('getCSVQBase simple results', 'ke_questionnaire Export Mod', 0, $this->simpleResults);
	}
	 */
	
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
							$base_row[$row['uid']]['inputs'] = 0;
							$base_row[$row['uid']]['inputs'] = $row['closed_inputfield'];
			    
							$where = 'question_uid='.$row['uid'].' and hidden=0 and deleted=0';
							$res_answers = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*','tx_kequestionnaire_answers',$where,'','sorting');
							//t3lib_div::devLog('getSPSSBase '.$row['type'], 'ke_questionnaire Export Mod', 0, array($GLOBALS['TYPO3_DB']->SELECTquery('*','tx_kequestionnaire_answers',$where,'','sorting')));
							if ($res_answers){
							    while ($answer = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res_answers)){
								$base_row[$row['uid']]['possible_answers'][$answer['uid']] = $answer['title'];
								if ($answer['show_input'] == 1) $base_row[$row['uid']]['inputs'] ++;
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
							//$lineset .= $this->getQBaseLine($free_cells,$question);
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
