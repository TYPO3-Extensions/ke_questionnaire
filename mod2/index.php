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

$LANG->includeLLFile('EXT:ke_questionnaire/mod2/locallang.xml');
require_once(PATH_t3lib.'class.t3lib_scbase.php');
$BE_USER->modAccess($MCONF,1);	// This checks permissions and exits if the users has no permission for entry.
	// DEFAULT initialization of a module [END]

/**
 * Module 'Analyse' for the 'ke_questionnaire' extension.
 *
 * @author	Nadine Schwingler <schwingler@kennziffer.com>
 * @package	TYPO3
 * @subpackage	tx_kequestionnaire
 */
class  tx_kequestionnaire_module2 extends t3lib_SCbase {
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

		if ($this->q_id > 0){
			$this->q_data = t3lib_BEfunc::getRecord('tt_content',$this->q_id);
			$ff_data = t3lib_div::xml2array($this->q_data['pi_flexform']);
			$this->ff_data = $ff_data['data'];
		}

		$this->extConf = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['ke_questionnaire']);
		$this->extConf_premium = array();
		if (t3lib_extMgm::isLoaded('ke_questionnaire_premium'))	$this->extConf_premium = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['ke_questionnaire_premium']);

		$this->standardColors = array();
		$this->standardColors[] = '#A2BF2F';
		$this->standardColors[] = '#BF2F2F';
		$this->standardColors[] = '#BF5A2F';
		$this->standardColors[] = '#BFA22F';
		$this->standardColors[] = '#772FBF';
		$this->standardColors[] = '#00337F';
		$this->standardColors[] = '#6D860D';

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
				'2' => $LANG->getLL('function2'),
				//'3' => $LANG->getLL('function3'),
			)
		);
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

			//#############################################
			// KENNZIFFER Nadine Schwingler 23.11.2009
			//
			if (t3lib_extMgm::isLoaded('ke_questionnaire_premium') AND $this->extConf_premium['chart_lib'] == 'openfl2'){
				require_once(t3lib_extMgm::extPath('ke_questionnaire_premium').'res/other/class.open_flcharts2.php');
				$this->charts = new open_flcharts2();
				$this->charts->be_js_includes($this);
			} else {
				require_once(t3lib_extMgm::extPath('ke_questionnaire').'res/other/class.js_raphael.php');
				$this->charts = new js_raphael();
				$this->charts->be_js_includes($this);
			}
			//#############################################
			$this->doc->postCode='
				<script language="javascript" type="text/javascript">
					script_ended = 1;
					if (top.fsMod) top.fsMod.recentIds["web"] = 0;
				</script>
			';

			$headerSection = $this->doc->getHeader('pages',$this->pageinfo,$this->pageinfo['_thePath']).'<br />'.$LANG->sL('LLL:EXT:lang/locallang_core.xml:labels.path').': '.t3lib_div::fixed_lgd_cs($this->pageinfo['_thePath'],50);

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
	function moduleContent()	{
		global $LANG;
		if ($this->q_id == 0){
			$title = $LANG->getLL('none_selected');
			$content = $LANG->getLL('none_selected');
		} else {
			switch((string)$this->MOD_SETTINGS['function'])	{
				case 1:
					$title = $LANG->getLL('basic_charts');
					if ($this->extConf_premium['chart_lib'] == 'openfl2') {
						$content = $this->getOFBasicCharts();
					} elseif ($this->extConf_premium['chart_lib'] == 'graph' OR !t3lib_extMgm::isLoaded('ke_questionnaire_premium')) {
						$content = $this->getGRBasicCharts();
					}
					else $content = 'keine Chart-Library definiert';
				break;
				case 2:
					if ($this->ff_data['sDEF']['lDEF']['type']['vDEF'] == 'RANDOM') $title = $LANG->getLL('question_result_charts');
					else $title = $LANG->getLL('question_charts');
					if ($this->extConf_premium['chart_lib'] == 'openfl2') {
						$content = $this->getOFQuestionCharts();
					} elseif ($this->extConf_premium['chart_lib'] == 'graph' OR !t3lib_extMgm::isLoaded('ke_questionnaire_premium')) {
						$content = $this->getGRQuestionCharts();
					}
					else $content = 'keine Chart-Library definiert';
				break;
				case 3:
					$content='<div align=center><strong>Menu item #3...</strong></div>';
				break;
			}
		}
		$this->content.=$this->doc->section($title,$content,0,1);
	}

##############################################################################################
# Open Flash Map 2 Charts
##############################################################################################
	function getOFBasicCharts(){
		global $LANG;
		$templ = file_get_contents('res/OF_basic.html');
		require_once(t3lib_extMgm::extPath('ke_questionnaire_premium').'res/other/class.keq_analysis.php');
		$analyse = new keq_analysis(new open_flcharts2());

		$markerArray = array();

		$finished = 0;
		$counting = 0;
		$parted = 0;
		$storage_pid = $this->ff_data['sDEF']['lDEF']['storage_pid']['vDEF'];
		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*','tx_kequestionnaire_results','pid='.$storage_pid.' AND hidden=0 AND deleted=0','','uid');
		//t3lib_div::devLog('getCSVInfos', 'ke_questionnaire Export Mod', 0, array($GLOBALS['TYPO3_DB']->SELECTquery('*','tx_kequestionnaire_results','pid='.$storage_pid.' AND hidden=0 AND deleted=0')));
		if ($res){
			while($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)){
				if ($row['xmldata'] != '') {
					if ($row['finished_tstamp'] > 0) $finished ++;
					else $parted ++;
					$counting ++;
				}
			}
		}

		$markerArray['###COUNT###'] = $LANG->getLL('result_count').': '.$counting.'<br />';
		$markerArray['###COUNT###'] .= $LANG->getLL('parted_count').': '.$parted.'<br />';
		$markerArray['###COUNT###'] .= $LANG->getLL('finished_count').': '.$finished;

		$markerArray['###WEEK###'] = $LANG->getLL('OFchart_select_week');
		$markerArray['###SELECTED_WEEK###'] = '';
		$markerArray['###SELECTED_DAY###'] = '';
		if (t3lib_div::_GP('range_select') == 'week') $markerArray['###SELECTED_WEEK###'] = 'selected';
		if (t3lib_div::_GP('range_select') == 'day') $markerArray['###SELECTED_DAY###'] = 'selected';
		$markerArray['###DAY###'] = $LANG->getLL('OFchart_select_day');

		$content = $this->fillTemplate($templ, $markerArray);
		//$script = $this->charts->testChart('timeline_chart');
		if (t3lib_div::_GP('range_select') == 'day') $timeline = $analyse->getOFTimelineChartForDays('timeline_chart',$this->ff_data);
		else $timeline = $analyse->getOFTimelineChartForWeeks('timeline_chart', $this->ff_data);
		//t3lib_div::devLog('getOFBasicCharts', 'BE Auswertungen', 0, array($timeline));
		$content .= $timeline;
		$content .= $analyse->getOFParticipationChart('parti_pie',$this->ff_data);

		return $content;
	}

	function getOFQuestionCharts(){
		global $LANG;
		$chart = '';
		$templ = file_get_contents('res/OF_questions.html');
		require_once(t3lib_extMgm::extPath('ke_questionnaire_premium').'res/other/class.keq_analysis.php');
		$analyse = new keq_analysis(new open_flcharts2());
		$markerArray = array();

		$types = array('\'open\'','\'closed\'','\'dd_words\'','\'dd_area\'','\'semantic\'','\'matrix\'');
		$markerArray['###QUESTION_SELECT###'] = $this->getQuestionSelect($types);

		$q_id = t3lib_div::_GP('question');
		$question = t3lib_BEfunc::getRecord('tx_kequestionnaire_questions',$q_id);

		$storage_pid = $this->ff_data['sDEF']['lDEF']['storage_pid']['vDEF'];
		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('xmldata,finished_tstamp','tx_kequestionnaire_results','pid='.$storage_pid.' AND hidden=0 AND deleted=0','','uid');
		if ($res){
			while($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)){
				if ($row['xmldata'] != '') {
					$encoding = "UTF-8";
					$temp_array = '';
					if ( true === mb_check_encoding ($row['xmldata'], $encoding ) ){
						$temp_array = t3lib_div::xml2array($row['xmldata']);
						if (count($temp_array) == 1) $temp_array = t3lib_div::xml2array(utf8_encode($row['xmldata']));
					} else {
						$temp_array = t3lib_div::xml2array(utf8_encode($row['xmldata']));
					}
					$results[$row['uid']] = $temp_array;
					if ($row['finished_tstamp'] > 0) $finished ++;
					$counting ++;
				}
			}
		}
		//t3lib_div::devLog('question', 'ke_questionnaire auswert Mod', 0, $question);
		//t3lib_div::devLog('results', 'ke_questionnaire auswert Mod', 0, $results);
		$markerArray['###DIV###'] = '';

		if ($q_id > 0){
			$legend = '';
			switch ($question['type']){
				case 'open':
					$markerArray['###DIV###'] = '<h2>'.$question['title'].'</h2>';
					$list = '';
					$list = $analyse->getOpenAnswers($results,$q_id);
					$markerArray['###DIV###'] .= $list;
					break;
				case 'closed':
				case 'dd_words':
				case 'dd_area':
					$markerArray['###DIV###'] = '<h2 style="width:600px;">'.$question['title'].'</h2>';
					$markerArray['###DIV###'] .= '<div id="pie"> </div>';
					$res_answers = $GLOBALS['TYPO3_DB']->exec_SELECTquery('uid,title','tx_kequestionnaire_answers','question_uid='.$q_id.' and hidden=0 and deleted=0','','sorting');
					$answers = array();
					if ($res_answers){
						while ($answer = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res_answers)){
							$answers[] = $answer;
						}
						$charts .= $analyse->getOFQClosedPieChart($answers,$results,$question);
					};
					break;
				case 'matrix':
					$res_cols = $GLOBALS['TYPO3_DB']->exec_SELECTquery('uid,title,different_type','tx_kequestionnaire_columns','question_uid='.$q_id.' and hidden=0 and deleted=0','','sorting');
					$divs = '';
					$columns = array();
					if ($res_cols){
						while($column = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res_cols)){
							$columns[] = $column;
						}
						$markerArray['###DIV###'] = $analyse->getOFQMatrixPieCharts($columns,$results,$question);
					}
					break;
				case 'semantic':
					$res_cols = $GLOBALS['TYPO3_DB']->exec_SELECTquery('uid,title','tx_kequestionnaire_columns','question_uid='.$q_id.' and hidden=0 and deleted=0','','sorting');
					$columns = array();
					if ($res_cols){
						while($column = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res_cols)){
							$columns[] = $column;
						}
						$markerArray['###DIV###'] = $analyse->getOFQSemanticPieCharts($columns,$results,$question);
					}
					break;
			}
		}

		$template = $this->fillTemplate($templ, $markerArray);
		$content = $template.$charts;

		// Hook to enable different BE-OFQuestionCharts
		if (is_array($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['ke_questionnaire']['mod2_getOFQuestionCharts'])){
			foreach($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['ke_questionnaire']['mod2_getOFQuestionCharts'] as $_classRef){
				$_procObj = & t3lib_div::getUserObj($_classRef);
				$content = $_procObj->mod2_getOFQuestionCharts($this,$templ,$markerArray,$content,$charts);
			}
		}

		return $content;
	}
##############################################################################################
# g.Raphael Charts
##############################################################################################
	/**
	 * Generates the Question Chart content
	 *
	 * @return	string
	 */
	function getGRQuestionCharts(){
		global $LANG;
		$templ = file_get_contents('res/questions.html');
		$charts = '';
		$markerArray = array();
		$types = array('\'open\'','\'closed\'','\'dd_words\'','\'dd_area\'','\'semantic\'','\'matrix\'');
		$markerArray['###QUESTION_SELECT###'] = $this->getQuestionSelect($types);

		$q_id = t3lib_div::_GP('question');
		$question = t3lib_BEfunc::getRecord('tx_kequestionnaire_questions',$q_id);

		$storage_pid = $this->ff_data['sDEF']['lDEF']['storage_pid']['vDEF'];
		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*','tx_kequestionnaire_results','pid='.$storage_pid.' AND hidden=0 AND deleted=0','','uid');
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
					$results[$row['uid']] = $temp_array;
					if ($row['finished_tstamp'] > 0) $finished ++;
					$counting ++;
				}
			}
		}
		//t3lib_div::devLog('question', 'ke_questionnaire auswert Mod', 0, $question);
		//t3lib_div::devLog('results', 'ke_questionnaire auswert Mod', 0, $results);
		$markerArray['###DIV1###'] = '';
		$markerArray['###DIV2###'] = '';
		$markerArray['###LEGEND###'] = '';

		if ($q_id > 0){
			$legend = '';
			switch ($question['type']){
				case 'open':
					$markerArray['###DIV1###'] = '<h2>'.$question['title'].'</h2>';
					$list = '';
					if (is_array($results)){
						$alternate = false;
						if (is_array($results)){
							foreach ($results as $r_id => $r){
								if(is_array($r)){
									if ($r[$q_id]['answer'] != ''){
										$list .= '<div style="display:block;';
										if ($alternate){
											$list .= 'background-color: #FAFAFA;';
											$alternate = false;
										} else {
											$list .= 'background-color: #FFF6CC;';
											$alternate = true;
										}
										$list .= 'margin: 4px;
											border: 1px solid #D7DBE2;
											width: 500px;
											padding: 2px;">';
										$list .= $r[$q_id]['answer'];
										$list .= '</div>';
									}
								}
							}
						}
					}
					$markerArray['###DIV2###'] = $list;
					break;
				case 'closed':
				case 'dd_words':
				case 'dd_area':
					//$markerArray['###DIV1###'] = '<div id="chart" style="height:300px; width:600px;"> </div>';
					$markerArray['###DIV1###'] = '<h2>'.$question['title'].'</h2>';
					$markerArray['###DIV2###'] = '<div id="pie" style="height:450px; width:600px;"> </div>';
					$res_answers = $GLOBALS['TYPO3_DB']->exec_SELECTquery('uid,title','tx_kequestionnaire_answers','question_uid='.$q_id,'','sorting');
					$answers = array();
					if ($res_answers){
						while ($answer = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res_answers)){
							//$legend .= $answer['uid'].'=>'.$answer['title'].'<br />';
							$answers[] = $answer;
						}
						//$charts = $this->getQClosedBarChart($answers,$results);
						$charts .= $this->getGRQClosedPieChart($answers,$results);
					};
					break;
				case 'matrix':
					//$markerArray['###DIV1###'] = '<div id="chart" style="height:300px; width:600px;"> </div>';
					$res_cols = $GLOBALS['TYPO3_DB']->exec_SELECTquery('uid,title','tx_kequestionnaire_columns','question_uid='.$q_id,'','sorting');
					$columns = array();
					if ($res_cols){
						while($column = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res_cols)){
							//$legend .= $column['uid'].'=>'.$column['title'].'</br>';
							$columns[] = $column;
						}
						//$charts = $this->getGRQMatrixBarChart($columns,$results);
						$markerArray['###DIV1###'] = $this->getGRQMatrixPieCharts($columns,$results);
					}
					break;
				case 'semantic':
					$res_cols = $GLOBALS['TYPO3_DB']->exec_SELECTquery('uid,title','tx_kequestionnaire_columns','question_uid='.$q_id,'','sorting');
					$columns = array();
					if ($res_cols){
						while($column = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res_cols)){
							//$legend .= $column['uid'].'=>'.$column['title'].'</br>';
							$columns[] = $column;
						}
						$markerArray['###DIV1###'] = $this->getGRQSemanticBarCharts($columns,$results);
					}
					break;
			}
			$markerArray['###LEGEND###'] = $legend;
		}
		//t3lib_div::devLog('answers', 'ke_questionnaire auswert Mod', 0, $answers);
		//t3lib_div::devLog('markerArray', 'ke_questionnaire auswert Mod', 0, $markerArray);
		$content = $this->fillTemplate($templ, $markerArray);
		if ($charts != '') $content .= $this->charts->wrapIt($charts);
		return $content;
	}

	/**
	 * Generates the Question Bar Chart
	 *
	 * @param 	$marker	div-id to show the chart
	 * @return	string
	 */
	function getGRQSemanticBarCharts($columns, $results, $marer = 'bar'){
		//t3lib_div::devLog('results', 'ke_questionnaire auswert Mod', 0, $results);
		//t3lib_div::devLog('bars', 'ke_questionnaire auswert Mod', 0, $columns);
		global $LANG;
		$q_id = t3lib_div::_GP('question');
		$title = '';
		$templ = file_get_contents('res/questions.html');

		$labels = array();
		$values = array();
		$colors = $this->standardColors;

		$charts = '';
		$content = '';
		$colors = $this->standardColors;

		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('uid,start,end','tx_kequestionnaire_sublines','question_uid='.$q_id,'','sorting');
		if ($res){
			while ($sub = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)){
				$max = 1;
				$values = array();
				$labels = array();
				$markerArray = array();
				$markerArray['###LEGEND###'] = '';
				$markerArray['###QUESTION_SELECT###'] = '';
				$markerArray['###DIV1###'] = '<h2>'.$sub['start'].' => '.$sub['end'].'</h2>';
				$markerArray['###DIV2###'] = '<div id="'.$marker.'_'.$sub['uid'].'" style="height:300px; width:600px;"> </div>';
				if (is_array($columns)){
					foreach ($columns as $bar){
						$labels[$bar['uid']] = $bar['title'];
						$values[$bar['uid']] = 0;
						if (is_array($results)){
							foreach ($results as $result){
								if (is_array($result)) if ((string)$result[$q_id]['answer']['options'][$sub['uid']] == (string)$bar['uid']) $values[$bar['uid']] ++;
								//elseif (is_array($result[$q_id]['answer']['options']) AND in_array($bar['uid'],$result[$q_id]['answer']['options'])) $values[$bar['uid']] ++;
								//t3lib_div::devLog('result '.$bar['uid'].'/'.$sub['uid'], 'ke_questionnaire auswert Mod', 0, array($result[$q_id]['answer']));
							}
						}
					}
				}
				//t3lib_div::devLog('values '.$sub['uid'], 'ke_questionnaire auswert Mod', 0, $values);
				//t3lib_div::devLog('labels', 'ke_questionnaire auswert Mod', 0, $labels);
				if (is_array($values)){
					foreach ($values as $t_v => $t_vals){
						if (is_array($t_vals)){
							foreach ($t_vals as $tt_v => $tt_vals){
								if ($max < $tt_vals) $max = $tt_vals;
							}
						} elseif ($max < $t_vals) $max = $t_vals;
					}
				}

				$charts .= $this->charts->getBarChart($marker.'_'.$sub['uid'],$title,$labels,$values,$max,$colors,false,'east',150);
				$content .= $this->fillTemplate($templ, $markerArray);
			}
		}
		if ($charts != '') $content .= $this->charts->wrapIt($charts);
		return $content;
	}

	/**
	 * Generates the Question Bar Chart
	 *
	 * @param 	$marker	div-id to show the chart
	 * @return	string
	 */
	function getGRQMatrixBarChart($cols,$results,$marker = 'chart'){
		//t3lib_div::devLog('results', 'ke_questionnaire auswert Mod', 0, $results);
		//t3lib_div::devLog('bars', 'ke_questionnaire auswert Mod', 0, $bars);
		global $LANG;
		$q_id = t3lib_div::_GP('question');

		$title = $LANG->getLL('question_matrixbar_chart');

		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('uid,title','tx_kequestionnaire_subquestions','question_uid='.$q_id,'','sorting');
		$subquestions = array();
		if ($res){
			while ($sub = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)){
				$subquestions[$sub['uid']] = $sub;
			}
		}

		$labels = array();
		$values = array();
		$colors = $this->standardColors;

		if (is_array($cols)){
			foreach ($cols as $bar){
				$labels[$bar['uid']] = $bar['uid'];
				if (is_array($subquestions)){
					foreach ($subquestions as $sub_nr => $sub_q){
						$values[$bar['uid']][$sub_q['uid']] = 0;
					}
				}
			}
		}
		//t3lib_div::devLog('subs '.$q_id, 'ke_questionnaire auswert Mod', 0, $subquestions);
		//t3lib_div::devLog('cols '.$q_id, 'ke_questionnaire auswert Mod', 0, $cols);
		//t3lib_div::devLog('values '.$q_id, 'ke_questionnaire auswert Mod', 0, $values);

		$max = 1;
		if (is_array($results)){
			foreach ($results as $result){
				if (is_array($values)){
					foreach ($values as $nr => $subval){
						//t3lib_div::devLog('result '.$q_id.'/'.$sub_nr, 'ke_questionnaire auswert Mod', 0, $result[$q_id]['answer']);
						if (is_array($subval)){
							foreach ($subval as $sub_nr => $value){
								//t3lib_div::devLog('result '.$q_id.'/'.$sub_nr.'/'.$nr, 'ke_questionnaire auswert Mod', 0, $result[$q_id]['answer']['options'][$sub_nr]);
								if ($result[$q_id]['answer']['options'][$sub_nr]['single'] == $nr){
									$values[$nr][$sub_nr] ++;
								}
								elseif (is_array($result[$q_id]['answer']['options']) AND in_array($nr,$result[$q_id]['answer']['options'])) {
									$values[$nr] ++;
								}
							}
						}
					}
				}
				//t3lib_div::devLog('result '.$q_id, 'ke_questionnaire auswert Mod', 0, $result[$q_id]['answer']);
			}
		}
		if (is_array($values)){
			foreach ($values as $t_v => $t_vals){
				if (is_array($t_vals)){
					foreach ($t_vals as $tt_v => $tt_vals){
						if ($max < $tt_vals) $max = $tt_vals;
					}
				} elseif ($max < $t_vals) $max = $t_vals;
			}
		}
		//t3lib_div::devLog('values', 'ke_questionnaire auswert Mod', 0, $values);
		//t3lib_div::devLog('max', 'ke_questionnaire auswert Mod', 0, array($max));


		$chart = $this->charts->getBarChart($marker,$title,$labels,$values,$max,$colors,false,true);
		return $chart;
	}

	/**
	 * Generates the Question Pie Chart
	 *
	 * @param 	$marker	div-id to show the chart
	 * @return	string
	 */
	function getGRQMatrixPieCharts($columns,$results,$marker = 'pie'){
		//t3lib_div::devLog('columns', 'ke_questionnaire auswert Mod', 0, $columns);
		//t3lib_div::devLog('results', 'ke_questionnaire auswert Mod', 0, array($results));
		global $LANG;
		$q_id = t3lib_div::_GP('question');
		$templ = file_get_contents('res/questions.html');

		//$title = $LANG->getLL('question_pie_chart');
		$title = '';
		$charts = '';

		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('uid,title','tx_kequestionnaire_subquestions','question_uid='.$q_id,'','sorting');
		if ($res){
			while ($sub = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)){
				$values = array();
				$labels = array();
				$colors = array();
				$markerArray = array();
				$markerArray['###LEGEND###'] = '';
				$markerArray['###QUESTION_SELECT###'] = '';
				$markerArray['###DIV1###'] = '<h2>'.$sub['title'].'</h2>';
				$markerArray['###DIV2###'] = '<div id="'.$marker.'_'.$sub['uid'].'" style="height:300px; width:600px;"> </div>';
				if (is_array($columns)){
					foreach ($columns as $bar){
						$labels[$bar['uid']] = $bar['title'].' (%%.%%)';
						$values[$bar['uid']] = 0;
						if (is_array($results)){
							foreach ($results as $result){
								if (is_array($result[$q_id]) AND is_array($result[$q_id]['answer'])){
									if (is_array($result[$q_id]['answer']['options'])){
										if ((string)$result[$q_id]['answer']['options'][$sub['uid']] == (string)$bar['uid']) $values[$bar['uid']] ++;
										elseif (is_array($result[$q_id]['answer']['options'][$sub['uid']]) AND ((string)$result[$q_id]['answer']['options'][$sub['uid']]['single'] == (string)$bar['uid'])) $values[$bar['uid']] ++;
										elseif (in_array($bar['uid'],$result[$q_id]['answer']['options'])) $values[$bar['uid']] ++;
									}
								}
								//t3lib_div::devLog('result '.$bar['uid'], 'ke_questionnaire auswert Mod', 0, array($result[$q_id]['answer']['options'][$sub['uid']]));
							}
						}
					}
				}
				//t3lib_div::devLog('values '.$sub['uid'], 'ke_questionnaire auswert Mod', 0, $values);
				//t3lib_div::devLog('labels', 'ke_questionnaire auswert Mod', 0, $labels);

				$charts .= $this->charts->getPieLegendChart($marker.'_'.$sub['uid'],$title,$values,$labels,$colors,false,'east',150);
				$content .= $this->fillTemplate($templ, $markerArray);
			}
		}
		if ($charts != '') $content .= $this->charts->wrapIt($charts);
		return $content;
	}


	/**
	 * Generates the Question Bar Chart
	 *
	 * @param 	$marker	div-id to show the chart
	 * @return	string
	 */
	function getGRQClosedBarChart($bars,$results,$marker = 'chart'){
		//t3lib_div::devLog('results', 'ke_questionnaire auswert Mod', 0, $results);
		//t3lib_div::devLog('bars', 'ke_questionnaire auswert Mod', 0, $bars);
		global $LANG;
		$q_id = t3lib_div::_GP('question');

		//$title = $LANG->getLL('question_bar_chart');
		$title = '';

		$labels = array();
		$values = array();
		if (is_array($bars)){
			foreach ($bars as $bar){
				$label = $bar['title'];
				if (strlen($label) > 75) $label = substr($label,0,75).'...';
				$labels[$bar['uid']] = $label.' (%%.%%)';
				$values[$bar['uid']] = 0;
			}
		}
		if (is_array($results)){
			foreach ($results as $result){
				if (is_array($values)){
					foreach ($values as $nr => $value){
						if ($result[$q_id]['answer']['options'] == $nr) $values[$nr] ++;
						elseif (is_array($result[$q_id]['answer']['options']) AND in_array($nr,$result[$q_id]['answer']['options'])) $values[$nr] ++;
						//t3lib_div::devLog('result '.$q_id, 'ke_questionnaire auswert Mod', 0, $result);
					}
				}
			}
		}
		$max = 0;
		foreach ($values as $value){
			if ($max < $value) $max = $value;
		}
		//t3lib_div::devLog('values', 'ke_questionnaire auswert Mod', 0, $values);

		$chart = $this->charts->getBarChart($marker,$title,$labels,$values,$max,'#A2BF2F',false);
		return $chart;
	}

	/**
	 * Generates the Question Pie Chart
	 *
	 * @param 	$marker	div-id to show the chart
	 * @return	string
	 */
	function getGRQClosedPieChart($pieces,$results,$marker = 'pie'){
		global $LANG;
		$q_id = t3lib_div::_GP('question');

		//$title = $LANG->getLL('question_pie_chart');
		$title = '';

		$labels = array();
		$values = array();
		if (is_array($pieces)){
			foreach ($pieces as $bar){
				$labels[$bar['uid']] = $bar['title'].'  (%%.%%)';
				$values[$bar['uid']] = 0;
			}
		}
		if (is_array($results)){
			foreach ($results as $result){
				if (is_array($values) AND is_array($result)){
					foreach ($values as $nr => $value){
						if ($result[$q_id]['answer']['options'] == $nr) $values[$nr] ++;
						elseif (is_array($result[$q_id]['answer']['options']) AND in_array($nr,$result[$q_id]['answer']['options'])) $values[$nr] ++;
						//t3lib_div::devLog('result '.$q_id, 'ke_questionnaire auswert Mod', 0, $result);
					}
				}
			}
		}
		$max = 0;
		foreach ($values as $value){
			if ($max < $value) $max = $value;
		}

		$chart = $this->charts->getPieLegendChart($marker,$title,$values,$labels,$colors,false);
		return $chart;
	}

	/**
	 * Generates the Basic Chart content
	 *
	 * @return	string
	 */
	function getGRBasicCharts(){
		global $LANG;
		$templ = file_get_contents('res/basic.html');
		$markerArray = array();

		$finished = 0;
		$counting = 0;
		$parted = 0;
		$storage_pid = $this->ff_data['sDEF']['lDEF']['storage_pid']['vDEF'];
		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*','tx_kequestionnaire_results','pid='.$storage_pid.' AND hidden=0 AND deleted=0','','uid');
		//t3lib_div::devLog('getCSVInfos', 'ke_questionnaire Export Mod', 0, array($GLOBALS['TYPO3_DB']->SELECTquery('*','tx_kequestionnaire_results','pid='.$storage_pid.' AND hidden=0 AND deleted=0')));
		if ($res){
			while($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)){
				if ($row['xmldata'] != '') {
					if ($row['finished_tstamp'] > 0) $finished ++;
					else $parted ++;
					$counting ++;
				}
			}
		}

		$markerArray['###COUNT###'] = $LANG->getLL('result_count').': '.$counting.'<br />';
		$markerArray['###COUNT###'] .= $LANG->getLL('parted_count').': '.$parted.'<br />';
		$markerArray['###COUNT###'] .= $LANG->getLL('finished_count').': '.$finished;

		$content = $this->fillTemplate($templ, $markerArray);

		//Timeline, weeks if participations
		$charts = $this->getGRTimelineChart();
		//Piechart, finished/not finished
		$charts .= $this->getGRParticipationChart();

		$content .= $this->charts->wrapIt($charts);

		//$content .= $this->charts->getTest();
		return $content;
	}

	/**
	 * Generates the Participation Procent Chart
	 *
	 * @param 	$marker	div-id to show the chart
	 * @return	string
	 */
	function getGRParticipationChart($marker = 'part'){
		global $LANG;
		$label = $LANG->getLL('participation_chart');

		$results = array();
		$finished = array();
		$parted = array();

		$first_started = 0;
		$last_started = 0;
		$first_finished = 0;
		$last_finished = 0;

		$storage_pid = $this->ff_data['sDEF']['lDEF']['storage_pid']['vDEF'];
		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*','tx_kequestionnaire_results','pid='.$storage_pid.' AND hidden=0 AND deleted=0','','uid');
		if ($res){
			while($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)){
				if ($row['xmldata'] != '') {
					//t3lib_div::devLog('times', 'ke_questionnaire auswert Mod', 0, array('is'=>$row['start_tstamp'],$first_started));
					if ($row['start_tstamp'] < $first_started OR $first_started == 0) $first_started = $row['start_tstamp'];
					if ($row['start_tstamp'] > $last_started) $last_started = $row['start_tstamp'];
					if ($row['finished_tstamp'] < $first_finished OR $first_finished == 0) $first_finished = $row['finished_tstamp'];
					if ($row['finished_tstamp'] > $last_finished) $last_finished = $row['finished_tstamp'];
					$results[] = $row;
					if ($row['finished_tstamp'] > 0) $finished[] = $row;
					else $parted[] = $row;
				}
			}
		}
		$parts = array();
		$parts[] = count($finished);
		$parts[] = count($parted);

		$labels = array();
		$labels[] = '%%.%% - '.$LANG->getLL('participation_finished');
		$labels[] = '%%.%% - '.$LANG->getLL('participation_parted');

		$colors = array();
		$colors[] = '#A2BF2F';
		$colors[] = '#BF2F2F';

		$chart = $this->charts->getPieLegendChart($marker,$label,$parts,$labels,$colors,false);
		return $chart;
	}

	/**
	 * Generates the Timeline Chart
	 *
	 * @param 	$marker	div-id to show the chart
	 * @return	string
	 */
	function getGRTimelineChart($marker = 'chart'){
		global $LANG;
		$label = $LANG->getLL('timeline_chart');

		$labels = array();
		$x_axis = array();
		$y_axis = array();
		$y_step = 0;
		$x_step = 0;

		$results = array();
		$finished = array();
		$parted = array();

		$first_started = 0;
		$last_started = 0;
		$first_finished = 0;
		$last_finished = 0;
		$first_edited = 0;
		$last_edited = 0;

		$storage_pid = $this->ff_data['sDEF']['lDEF']['storage_pid']['vDEF'];
		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*','tx_kequestionnaire_results','pid='.$storage_pid.' AND hidden=0 AND deleted=0','','start_tstamp');
		if ($res){
			while($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)){
				if ($row['xmldata'] != '') {
					//t3lib_div::devLog('times', 'ke_questionnaire auswert Mod', 0, array('is'=>$row['start_tstamp'],$first_started));
					if ($row['start_tstamp'] < $first_started OR $first_started == 0) $first_started = $row['start_tstamp'];
					if ($row['start_tstamp'] > $last_started) $last_started = $row['start_tstamp'];
					if ($row['finished_tstamp'] > 0 AND ($row['finished_tstamp'] < $first_finished OR $first_finished == 0)) $first_finished = $row['finished_tstamp'];
					if ($row['finished_tstamp'] > $last_finished) $last_finished = $row['finished_tstamp'];
					if ($row['last_tstamp'] < $first_edited OR $first_edited == 0) $first_edited = $row['last_tstamp'];
					if ($row['last_tstamp'] > $last_edited) $last_edited = $row['last_tstamp'];
					$results[] = $row;
					if ($row['finished_tstamp'] > 0) $finished[] = $row;
					else $parted[] = $row;
				}
			}
		}

		//get the weeks for the x-axis
		$finished_diff = $last_finished - $first_finished;
		$days_diff = $finished_diff / 86400;
		$weeks = ceil($days_diff / 7);
		//t3lib_div::devLog('times', 'ke_questionnaire auswert Mod', 0, array('fs'=>$first_started,'ls'=>$last_started,'ff'=>$first_finished,'lf'=>$last_finished));
		//t3lib_div::devLog('weeks', 'ke_questionnaire auswert Mod', 0, array($weeks));

		$y_axis[] = 0;
		$check_tstmp = $first_finished;
		$week_int = 86400 * 7;
		$week_count = 1;

		for ($i = 0; $i <= $weeks; $i++){
			$y_axis[$i] = 0;
		}
		if (is_array($finished)){
			foreach ($finished as $fin){
				while ($fin['finished_tstamp'] > ($check_tstmp + $week_int)){
					$week_count ++;
					$check_tstmp += $week_int;
				}
				$y_axis[$week_count] ++;
				if ($y_axis[$week_count] > $y_step) $y_step = $y_axis[$week_count];
			}
		}
		//$y_axis[] = count($results);
		//$y_axis[] = count($finished);

		//get the x_axis
		for ($i = 0; $i <= $weeks; $i++){
			$x_axis[] = $i;
			$labels[] = 'KW '.date('W',($finished[0]['finished_tstamp']+($week_int*$i)));
		}
		if (count($x_axis) > $x_step) $x_step = count($x_axis);

		//only if there ARE any partially filled results
		if (count($parted) > 0){
			/*$started_diff = $last_started - $first_stared;
			$days_diff = $started_diff / 86400;
			$started_weeks = ceil($days_diff / 7);*/
			$edited_diff = $last_edited - $first_edited;
			$days_diff = $edited_diff / 86400;
			$weeks = ceil($days_diff / 7);
			//t3lib_div::devLog('weeks', 'ke_questionnaire auswert Mod', 0, array($weeks));

			$x_axis_finished = $x_axis;
			$x_axis_parted = array();
			for ($i = 0; $i <= $weeks; $i++){
				$x_axis_parted[] = $i;
			}
			if (count($x_axis_parted) > $x_step) $x_step = count($x_axis_parted);
			$x_axis = array();
			$x_axis[] = $x_axis_finished;
			$x_axis[] = $x_axis_parted;

			$week_count = 1;
			$check_tstmp = $first_started;
			$y_axis_finished = $y_axis;
			$y_axis_parted = array();
			for ($i = 0; $i <= $weeks; $i++){
				$y_axis_parted[$i] = 0;
			}
			if (is_array($parted)){
				foreach ($parted as $par){
					while ($par['last_tstamp'] > ($check_tstmp + $week_int)){
						$week_count ++;
						$check_tstmp += $week_int;
					}
					$y_axis_parted[$week_count] ++;
					if ($y_axis_parted[$week_count] > $y_step) $y_step = $y_axis_parted[$week_count];
				}
			}
			$y_axis = array();
			$y_axis[] = $y_axis_finished;
			$y_axis[] = $y_axis_parted;
		}

		//t3lib_div::devLog('params', 'ke_questionnaire auswert Mod', 0, array('m'=>$marker,'l'=>$label,'x'=>$x_axis,'cx'=>count($x_axis)-1,'y'=>$y_axis,'cy'=>$y_step));
		$colors = array();
		$colors[] = '#A2BF2F';
		$colors[] = '#BF2F2F';
		$chart = $this->charts->getLineChart($marker,$label,$x_axis,$x_step-1,$y_axis,$y_step,$labels,$colors,false);
		return $chart;
	}

##############################################################################################

	/**
	 * Generates the Question Select
	 *
	 * @param 	$types 	types of Questions allowed
	 * @return	string
	 */
	function getQuestionSelect($types){
		if ($this->ff_data['sDEF']['lDEF']['type']['vDEF'] == 'RANDOM') $content = '<select id="keq_mod2_question" name="question" onchange="document.getElementById(\'keq_mod2_result\').value=0;this.form.submit()">';
		else $content = '<select id="keq_mod2_question" name="question" onchange="this.form.submit()">';
		$storage_pid = $this->ff_data['sDEF']['lDEF']['storage_pid']['vDEF'];

		$q_id = t3lib_div::_GP('question');

		//get the questions
		$where = 'pid='.$storage_pid.' AND hidden=0 AND deleted=0 AND type IN('.implode(',',$types).')';
		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('uid,title,matrix_type','tx_kequestionnaire_questions',$where,'','sorting');
		//t3lib_div::devLog('qs', 'ke_questionnaire auswert Mod', 0, array($GLOBALS['TYPO3_DB']->SELECTquery('uid,title','tx_kequestionnaire_questions',$where,'','sorting')));
		if ($res){
			$content .= '<option value="0">---</option>';
			while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)){
				if ($row['matrix_type'] != 'input'){
					$content .= '<option value="'.$row['uid'].'"';
					if ($row['uid'] == $q_id){
						$content .= ' selected ';
					}
					$content .= '>';
					$content .= $row['title'];
					$content .= '</option>';
				}
			}
		}

		$content .= '</select>';

		return $content;
	}


	function fillTemplate ($templ, $markerArray){
		$content = $templ;

		foreach ($markerArray as $marker => $value){
			$content = str_replace($marker,$value,$content);
		}

		return $content;
	}
}



if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/ke_questionnaire/mod2/index.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/ke_questionnaire/mod2/index.php']);
}




// Make instance:
$SOBE = t3lib_div::makeInstance('tx_kequestionnaire_module2');
$SOBE->init();

// Include files?
foreach($SOBE->include_once as $INC_FILE)	include_once($INC_FILE);

$SOBE->main();
$SOBE->printContent();

?>
