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

/**
 * Class to export the ke_questionnaire results in csv-format.
 *
 * @package 	TYPO3
 * @subpackage 	tx_kequequestionnaire csv_export
 */
//require_once(PATH_tslib . 'class.tslib_content.php'); // load content file

class csv_export {
	/**
	 * csv_export(): Initalisation of the class
	 *
	 * @param 	array	$extConf: extension configuration array => ext_conf_template.txt
	 * @param	array	$results: base-array of the results to be exportet
	 * @param	array	$ff_data: flexform data of the questionnaire
	 * @param	string	$temp_file: name of the file with the temp data
	 * @param	bool	$only_this_lang: export only the selected language
	 * @param	vbool	$only_finished: export only finished results
	 * @param	bool	$with_authcode: export with authcode
	 */
        function csv_export($extConf,$results,$q_data,$ff_data,$temp_file,$only_this_lang,$only_finished,$with_authcode){
                $this->extConf = $extConf;
                $this->results = $results;
                $this->q_data = $q_data;
                $this->ff_data = $ff_data;
                $this->temp_file = $temp_file;
		$this->only_this_lang = $only_this_lang;
		$this->only_finished = $only_finished;
		$this->with_authcode = $with_authcode;
                
                //t3lib_div::devLog('extConf', 'ke_questionnaire Export Mod', 0, $this->extConf);
		//t3lib_div::devLog('with_authcode', 'ke_questionnaire Export Mod', 0, array($this->with_authcode));
        }
        
	/**
	 * getCSVQBased(): get the CSV-Data based on Questions (one question per row, results as columns)
	 *
	 * @return	string	csv-Data
	 */
        function getCSVQBased(){
		global $LANG;

		$csvdata = '';
		$csvheader = '';
		//defines the cell of the csv
		$delimeter = $this->extConf['CSV_qualifier'];
		//parts the cells of the csv
		$pure_parter = $this->extConf['CSV_parter'];
		//to be put between two values/cells
		$parter = $delimeter.$this->extConf['CSV_parter'].$delimeter;
		
		//get the Header of the CSV-File
		$csvheader = $this->getQBaseHeaderLine();
		
		$lineset = ''; //stores the CSV-data
		$line = array(); //single line, will be imploded
		$free_cells = 0;
		//gets the line with the column definition: result-ids
		$result_line = $this->getQBaseResultLine($free_cells);
		//add the result line to the csv-data
		$lineset .= $pure_parter.$pure_parter.$pure_parter.$result_line."\n";
		//get the temp-file the data is stored in
		$file_path = PATH_site.'/typo3temp/'.$this->temp_file;
		$store_file = fopen($file_path,'r');
		
		//definition array for the export
		$fill_array = $this->createFillArray();
		foreach ($fill_array as $q_nr => $question){
			//read the data from the file
			$read_line = fgets($store_file);
			$read_line = str_replace("\n",'',$read_line);
			$read_line = json_decode($read_line,true);
			$question['data'] = array();
			$question['data'] = $read_line;
			//create the line
			$line = array();
			//question id
			$line[] = $question['uid'];
			//title
			$line[] = $this->stripString($question['title']);
			if ($question['type']){
				$lineset .= $delimeter.implode($parter,$line).$delimeter;
				//$lineset .= $pure_parter.$result_line."\n";
				$lineset .= $pure_parter;
				//t3lib_div::devLog('getCSVQBase '.$question['type'], 'ke_questionnaire Export Mod', 0, $question);
				//t3lib_div::devLog('lineset '.$question['type'], 'ke_questionnaire Export Mod', 0, array($lineset));
				switch ($question['type']){
					case 'authcode':	$lineset .= $this->getQBaseLine($free_cells,$question);
						break;
					case 'start_tstamp':	$lineset .= $this->getQBaseLine($free_cells,$question);
						break;
					case 'finished_tstamp':	$lineset .= $this->getQBaseLine($free_cells,$question);
						break;
					case 'open':	$lineset .= $this->getQBaseLine($free_cells,$question);
						break;
					case 'closed':
							$lineset .= "\n";
							$where = 'question_uid='.$question['uid'].' and hidden=0 and deleted=0';
							$res_answers = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*','tx_kequestionnaire_answers',$where,'','sorting');
							//t3lib_div::devLog('getCSVQBase '.$question['type'], 'ke_questionnaire Export Mod', 0, array($GLOBALS['TYPO3_DB']->SELECTquery('*','tx_kequestionnaire_answers',$where,'','sorting')));
							if ($res_answers){
								while ($answer = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res_answers)){
									$lineset .= $this->getQBaseLine($free_cells+2,$question,$answer);
								}
							}
						break;
					case 'matrix':
							$lineset .= "\n";
							$columns = array();
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
										for ($i = 0;$i < ($free_cells+1);$i ++){
											$line[] = '';
										}
										$line[] = $subquestion['title'];
										$lineset .= $delimeter.implode($parter,$line).$delimeter."\n";
										foreach ($columns as $column){
											$lineset .= $this->getQBaseLine($free_cells+2,$question,array(),$subquestion['uid'],$column);
										}
									}
								}
							}
						break;
					case 'semantic':
							$lineset .= "\n";
							$columns = array();
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
									for ($i = 0;$i < ($free_cells+2);$i ++){
										$line[] = '';
									}
									$line[] = $subline['start'].' - '.$subline['end'];
									$lineset .= $delimeter.implode($parter,$line).$delimeter."\n";
									foreach ($columns as $column){
										$lineset .= $this->getQBaseLine($free_cells+2,$question,array(),$subline['uid'],$column);
									}
								}
							}
						break;
					case 'demographic':
							//t3lib_div::devLog('lineset '.$question['type'], 'ke_questionnaire Export Mod', 0, $question);
							$lineadd = "\n";
							if (is_array($question['fe_users'])){
								if (count($question['fe_users']) > 0){
									//$lineadd .= "\n";
									foreach ($question['fe_users'] as $field => $f_values){
										$lineadd .= $this->getQBaseLine($free_cells+2,$question,array(),0,array(),$field);
									}
								}
							}
							//if ($lineadd == '') $lineadd .= "\n";
							$lineset .= $lineadd;
						break;
					default:
							$delimeter = $this->extConf['CSV_qualifier'];
							$parter = $delimeter.$this->extConf['CSV_parter'].$delimeter;
							// Hook to make other types available for export
							if (is_array($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['ke_questionnaire']['CSVExportQBaseLine'])){
								foreach($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['ke_questionnaire']['CSVExportQBaseLine'] as $_classRef){
									$_procObj = & t3lib_div::getUserObj($_classRef);
									$getit = $_procObj->CSVExportQBaseLine($free_cells,$question,$this->results,$delimeter,$parter);
									//t3lib_div::devLog('getCSVQBase getit', 'ke_questionnaire Export Mod', 0, array($getit));
									if ($getit != '') $lineset .= $getit;
									else $lineset .= "\n";
								}
							}
						break;
					
				}
			}
		}
	
		fclose($store_file);
		//linebreak at the end of the line
		$csvdata .= $lineset."\n";

		return $csvheader.$csvdata;
	}
        
	/**
	 *	getQBaseLine(): Get the Line for the QBased Export. After the free cells add the answer for every result to be exportet
	 *
	 *	@param	int	$free_cells: cells to be left without result
	 *	@param	array	$question: data of the question
	 *	@param	array	$answer: given answer
	 *	@param	int	$subquestion: id of subquestion
	 *	@param	array	$column: information of the column
	 *	@param	string	$dem_field: demographic to be exportet
	 *
	 *	@return	string	Line ready to be exportet
	 */
	function getQBaseLine($free_cells,$question,$answer=array(),$subquestion=0,$column=array(),$dem_field=''){
		//t3lib_div::devLog('getQBaseLine', 'ke_questionnaire Export Mod', 0, array('free'=>$free_cells,'q'=>$question,'type'=>$type,'answer'=>$answer,'subq'=>$subquestion,'col'=>$column,$dem_field));
		global $LANG;
		$type = $question['type'];

		$delimeter = $this->extConf['CSV_qualifier'];
		$parter = $delimeter.$this->extConf['CSV_parter'].$delimeter;
		$line = array();
		for ($i = 0;$i < $free_cells;$i ++){
			$line[] = '';
		}

		$line_add = '';
		$take = $question['data'];
		
                $question = $question['uid'];
		switch($type){
			case 'authcode': $line[] = '';
					//t3lib_div::devLog('qbaseline', 'ke_questionnaire Export Mod', 0, $take);
					foreach ($this->results as $nr => $r_data){
						$result_id = $r_data['uid'];
						$take['results'][$result_id] = str_replace($delimeter,$delimeter.$delimeter,$take['results'][$result_id]);
						$line[] = $take['results'][$result_id];
					}
				break;
			case 'start_tstamp': $line[] = '';
					//t3lib_div::devLog('results '.$q_nr, 'ke_questionnaire Export Mod', 0, $this->results);
					foreach ($this->results as $nr => $r_data){
						$result_id = $r_data['uid'];
						$take['results'][$result_id] = str_replace($delimeter,$delimeter.$delimeter,$take['results'][$result_id]);
						$line[] = $take['results'][$result_id];
					}
				break;
			case 'finished_tstamp': $line[] = '';
					foreach ($this->results as $nr => $r_data){
						$result_id = $r_data['uid'];
						$take['results'][$result_id] = str_replace($delimeter,$delimeter.$delimeter,$take['results'][$result_id]);
						$line[] = $take['results'][$result_id];
					}
				break;
			case 'open':	$line[] = '';
					foreach ($this->results as $nr => $r_data){
						$result_id = $r_data['uid'];
						$take['results'][$result_id] = str_replace($delimeter,$delimeter.$delimeter,$take['results'][$result_id]);
						$line[] = $take['results'][$result_id];
					}
				break;
			case 'closed':  $line[] = $answer['title'];
					if (is_array($take)){
						//t3lib_div::devLog('getQbaseLine take '.$question, 'ke_questionnaire Export Mod', 0, $take);
						foreach ($this->results as $nr => $r_data){
							$result_id = $r_data['uid'];
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
					/*if (is_array($take['answers'][$answer['uid']]['results'])){
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
					}*/
				break;
			case 'semantic':
			case 'matrix': $line[] = $column['title'];
					if(is_array($take['subquestions'][$subquestion]['columns'][$column['uid']]['results'])){
						foreach ($this->results as $nr => $r_data){
							$result_id = $r_data['uid'];
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
					//t3lib_div::devLog('getCSVQBase line '.$type.' - '.$dem_field, 'ke_questionnaire Export Mod', 0, $take);
					if (is_array($take['fe_users'][$dem_field]['results'])){
						foreach ($this->results as $nr => $r_data){
							$result_id = $r_data['uid'];
							if ($take['fe_users'][$dem_field]['results'][$result_id]){
								//t3lib_div::devLog('getCSVQBase line '.$result_id, 'ke_questionnaire Export Mod', 0, $take['fe_users'][$dem_field]);
								$take['fe_users'][$dem_field]['results'][$result_id] = str_replace($delimeter,$delimeter.$delimeter,$take['fe_users'][$dem_field]['results'][$result_id]);
								$take['fe_users'][$dem_field]['results'][$result_id] = preg_replace('#[\r\n\t]#', ' ', $take['fe_users'][$dem_field]['results'][$result_id]);
								$take['fe_users'][$dem_field]['results'][$result_id] = preg_replace('# {2,}#', ' ', $take['fe_users'][$dem_field]['results'][$result_id]);
								$line[] = strip_tags(nl2br($take['fe_users'][$dem_field]['results'][$result_id]));
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

	/**
	 *	getQBaseHeaderLine(): Get the Header Line for the Question Based CSV-Export
	 *
	 *	@return	string	Line reade to be exportet
	 */
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
	
	/**
	 *	getQBaseResultLine(): get the Result-ID Line for the CSV-Export
	 *
	 *	@param	int	$free_cells: amount of cells left free at the beginning of the line
	 *
	 *	@return	string	line ready to be exportet
	 */
	function getQBaseResultLine($free_cells){
		global $LANG;

		$delimeter = $this->extConf['CSV_qualifier'];
		$parter = $delimeter.$this->extConf['CSV_parter'].$delimeter;
		$line = array();
		for ($i = 0;$i < $free_cells;$i ++){
			$line[] = '';
		}
		foreach ($this->results as $nr => $values){
			//t3lib_div::devLog('getQbaseResultLine values', 'ke_questionnaire Export Mod', 0, $values);
			$line[] = $values['uid'];
		}
		return $delimeter.implode($parter,$line).$delimeter;
	}
	
	/**
	 * 	getCSVSimple2(): get the result-based Export (one result per line)
	 *	
	 *	@return	string	ready to be send csv
	 */
	function getCSVSimple2(){
		global $LANG;

		$csvdata = '';
		$csvheader = '';
		$delimeter = $this->extConf['CSV_qualifier'];
		$parter = $delimeter.$this->extConf['CSV_parter'].$delimeter;
		
		$csvheader = $this->q_data['header']."\n\n";
		
		$fill_array = $this->createFillArray();
		//t3lib_div::devLog('getCSVSimple2 fill_array', 'ke_questionnaire Export Mod', 0, $fill_array);
		
		if (is_array($fill_array)){
			$headline = array();
			foreach ($fill_array as $question_id => $values){
				if ($values['title'] != ''){
					switch ($values['type']){
						case 'authcode': 
						case 'start_tstamp':
						case 'finished_tstamp':
								$headline[] = $values['title'];
							break;
						case 'closed':
							foreach ($values['answers'] as $a_id => $a_values){
								$answer = t3lib_BEfunc::getRecord('tx_kequestionnaire_answers',$a_id);
								$headline[] = $values['title'].'_'.$answer['title'];
							}
						break;
						case 'matrix':
							//t3lib_div::devLog('getCSVSimple matrix', 'ke_questionnaire Export Mod', 0, $values);
							if (is_array($values['subquestions'])){
								foreach ($values['subquestions'] as $sub_id => $sub_values){
									$subl = t3lib_BEfunc::getRecord('tx_kequestionnaire_subquestions',$sub_id);
									foreach ($sub_values['columns'] as $col_id => $col_values){
										$col = t3lib_BEfunc::getRecord('tx_kequestionnaire_columns',$col_id);
										$headline[] = $values['title'].'_'.$subl['title'].'_'.$col['title'];
									}
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
						case 'demographic':
							//t3lib_div::devLog('getCSVSimple2 demo', 'ke_questionnaire Export Mod', 0, $values);
						break;
						default:
							// Hook to make other types available for export
							if (is_array($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['ke_questionnaire']['CSVExportType2Headline'])){
								foreach($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['ke_questionnaire']['CSVExportType2Headline'] as $_classRef){
									$_procObj = & t3lib_div::getUserObj($_classRef);
									$headline = $_procObj->CSVExportType2Headline($headline,$values);
                                                                        //t3lib_div::devLog('headline', 'ke_questionnaire Export Mod', 0, $headline);
								}
							} else {
                                                            $headline[] = $values['title'];  
                                                        }
						break;
					}
				}
			}
		}
		$csvheader .= $delimeter.implode($parter,$headline).$delimeter."\n";
		if (is_array($this->results)){
			$file_path = PATH_site.'typo3temp/'.$this->temp_file;
			$store_file = fopen($file_path,'r');
			//t3lib_div::devLog('simple 2 results', 'ke_questionnaire Export Mod', 0, $this->results);
			foreach ($this->results as $r_id => $r_values){
				$read_line = fgets($store_file);
				$csvdata .= $read_line;
				//t3lib_div::devLog('simple 2 '.$r_id, 'ke_questionnaire Export Mod', 0, array($read_line,$r_values));
			}
			fclose($store_file);
		}
	
		return $csvheader.$csvdata;
	}

/* old simple export, not supported anymore
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
*/
	/**
	 *	createFillArray(): creates the definition Array for the export.
	 *
	 *	@return	array	Array of defintions for the export. Created of the questions and other informations needed in the export
	 */
        function createFillArray(){
		//get the questions
		$storage_pid = $this->ff_data['sDEF']['lDEF']['storage_pid']['vDEF'];
		$where = 'pid='.$storage_pid.' and hidden=0 and deleted=0 and type!="blind"';
		if ($this->only_this_lang != ''){
			$lang = explode('_',$this->only_this_lang);
			$where .= ' AND sys_language_uid='.$lang[1];
		}
		//t3lib_div::devLog('where', 'ke_questionnaire Export Mod', 0, array($where));
		//$where .= ' AND sys_language_uid='.$this->q_data['sys_language_uid'];
		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*','tx_kequestionnaire_questions',$where,'','sorting');
	
		//create the question structure
		$fill_array = array();
		if ($res){			
			if ($this->with_authcode) {
				//t3lib_div::devLog('where', 'ke_questionnaire Export Mod', 0, array($where));
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
							if (is_array($flex)) $fe_user_fields = explode(',',$flex['data']['sDEF']['lDEF']['FeUser_Fields']['vDEF']);
							$flex = t3lib_div::xml2array($question['demographic_addressfields']);
							if (is_array($flex)) $fe_user_addressfields = explode(',',$flex['data']['sDEF']['lDEF']['FeUser_Fields']['vDEF']);
							//t3lib_div::devLog('getCSVQBase flex', 'ke_questionnaire Export Mod', 0, array($fe_user_fields,$fe_user_addressfields));
							if (is_array($fe_user_fields)){                          
								
								foreach ($fe_user_fields as $field){
								$fill_array[$question['uid']]['fe_users'][$field] = array();
                                                            }
							}
							//$lineset .= $this->getQBaseLine($free_cells,$question);
						break;
                                        default:
						    // Hook to make other types available for export
						    if (is_array($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['ke_questionnaire']['CSVExportCreateFillArray'])){
							    foreach($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['ke_questionnaire']['CSVExportCreateFillArray'] as $_classRef){
								    $_procObj = & t3lib_div::getUserObj($_classRef);
								    $fill_array[$question['uid']] = $_procObj->CSVExportCreateFillArray($question,$fill_array[$question['uid']]);
							    }
						    }
						break;
				}
			}
		}
                // Hook to make other types available for export
                if (is_array($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['ke_questionnaire']['CSVExportFillArray'])){
                        foreach($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['ke_questionnaire']['CSVExportFillArray'] as $_classRef){
                                $_procObj = & t3lib_div::getUserObj($_classRef);
                                $fill_array = $_procObj->CSVExportFillArray($fill_array);
                        }
                }
		//t3lib_div::devLog('fill_array', 'ke_questionnaire Export Mod', 0, $fill_array);
		return $fill_array;
	}
    
/* part of old simple export, not supported anymore    
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
*/
        
        function stripString($temp){
		$temp = strip_tags($temp);
		$temp = html_entity_decode($temp);
		//$temp = preg_replace("/\r|\n/s", "", $temp);
		return $temp;
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/ke_questionnaire/res/other/class.csv_export.php']){
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/ke_questionnaire/res/other/class.csv_export.php']);
}
?>