<?php
require_once(PATH_t3lib.'class.t3lib_scbase.php');

class  tx_kequestionnaire_module3_ajax extends t3lib_SCbase {
    function init() {
        $myVars = $GLOBALS['BE_USER']->getSessionData('tx_kequestionnaire');
        $myVars['pointer'] = $pointer + 1;
        $GLOBALS['BE_USER']->setAndSaveSessionData('tx_kequestionnaire',$myVars);
        
        //get the given Parameters
        $this->q_id = $myVars['q_id'];
        $this->pid = $myVars['pid'];
        $this->temp_file = 'tx_kequestionnaire_temp_'.$this->q_id.'_'.$GLOBALS['BE_USER']->user['uid'];
                
        $this->extConf = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['ke_questionnaire']);
        if (t3lib_extMgm::isLoaded('ke_questionnaire_premium')) $this->pr_extConf = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['ke_questionnaire_premium']);

        $this->ff_data = $myVars['ff_data'];
	$this->type = $myVars['download_type'];
        
        //t3lib_div::devLog('ajax vars', 'ke_questionnaire Export Mod', 0, array($this->q_id,$this->pid,$this->ff_data));
        //t3lib_div::devLog('ajax vars', 'ke_questionnaire Export Mod', 0, $myVars);
        
        $this->results = $myVars['results'];
    }
    
    function ajaxCreateDataFile($params,&$ajaxObj){
        $this->init();
	
        $pointer = t3lib_div::_GP('pointer');
        $ajaxObj->addContent('pointerly', $pointer);
	
	//t3lib_div::devLog('ajaxCreateDataFile', 'ke_questionnaire Export Mod', 0, array($this->type));
        if ($this->type == 'questions') $this->createDataFile($pointer);
	elseif ($this->type == 'simple2') $this->createDataFileType2($pointer);
    }
    
    function createDataFile($pointer){
        //marker in CSV
        $marker = $this->extConf['CSV_marker'];
        //get the question-structure
	$fill_array = $this->createFillArray();
        
	//get the actual result
        $result = $this->results[$pointer];
        $auth = t3lib_BEfunc::getRecord('tx_kequestionnaire_authcodes',$result['auth']); //test
        $result['authcode'] = $auth['authcode'];
        $result_nrs[] = $result['uid'];
        //t3lib_div::devLog('create DataFile result '.$pointer, 'ke_questionnaire Export Mod', 0, $result);
        //t3lib_div::devLog('create DataFile this->result '.$pointer, 'ke_questionnaire Export Mod', 0, $this->results);

        //t3lib_div::devLog('simplify results value_arrays', 'ke_questionnaire Export Mod', 0, $value_arrays);
        $file_path = PATH_site.'typo3temp/'.$this->temp_file;
        //der Inhalt der Datei wird gelesen
        if (file_exists($file_path)) {
            $all_file = file($file_path);
            //t3lib_div::devLog('file_exists', 'ke_questionnaire Export Mod', 0, array($file_path));
            unlink($file_path);
        }
        //t3lib_div::devLog('file_path', 'ke_questionnaire Export Mod', 0, array($file_path));
        //t3lib_div::devLog('file', 'ke_questionnaire Export Mod', 0, $all_file);
        
        $store_file = fopen($file_path,'a+');
        $line = 0;
        foreach ($fill_array as $q_nr => $q_values){
                $data = $all_file[$line];
                //t3lib_div::devLog('line '.$line, 'ke_questionnaire Export Mod', 0, $data);
                $line ++;
                //t3lib_div::devLog('getCSVQBase q_values '.$q_nr, 'ke_questionnaire Export Mod', 0, $q_values);
                $write_array = array();
                $write_array = json_decode(trim(str_replace("\n",'',$data)),true);
                //t3lib_div::devLog('getCSVQBase write_array '.$q_nr, 'ke_questionnaire Export Mod', 0, $write_array);
                $v_values = $result;
                $v_nr = $result['uid'];
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
                        case 'authcode': $write_array['results'][$v_nr] = $result['authcode'];
                                break;
                        case 'start_tstamp':
                                    $write_array['results'][$v_nr] = $result['start_tstamp'];
                                break;
                        case 'finished_tstamp': $write_array['results'][$v_nr] = $result['finished_tstamp'];
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
                                        if (is_array($q_values['subquestions'])){
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
                                        if (is_array($act_v) AND is_array($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['ke_questionnaire']['CSVExportCreateDataFileQType'])){
                                                foreach($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['ke_questionnaire']['CSVExportCreateDataFileQType'] as $_classRef){
                                                        $_procObj = & t3lib_div::getUserObj($_classRef);
                                                        $write_array = $_procObj->CSVExportCreateDataFileQType($q_values,$act_v, $v_nr, $marker, $write_array);
                                                }
                                        }
                                break;
                }
                // Hook to make other types available for export
                if (is_array($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['ke_questionnaire']['CSVExportCreateDataFile'])){
                        foreach($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['ke_questionnaire']['CSVExportCreateDataFile'] as $_classRef){
                                $_procObj = & t3lib_div::getUserObj($_classRef);
                                $write_array = $_procObj->CSVExportCreateDataFile($write_array,$v_values,$v_nr);
                        }
                }
                //t3lib_div::devLog('fill_array', 'ke_questionnaire Export Mod', 0, $fill_array);
                //t3lib_div::devLog('write_array '.$q_nr.'/'.$v_nr, 'ke_questionnaire Export Mod', 0, $write_array);
                if (is_array($write_array) AND count($write_array) > 0) fwrite($store_file,json_encode($write_array)."\n");
        }
        fclose($store_file);	
    }
    
    /**
     * Data File for One-Result/Row CSV Export
     */    
    function createDataFileType2($pointer){
        //marker in CSV
        $marker = $this->extConf['CSV_marker'];
	//get the question structure
        $fill_array = $this->createFillArray();
        //get the actual result
        $result = $this->results[$pointer];
        $auth = t3lib_BEfunc::getRecord('tx_kequestionnaire_authcodes',$result['auth']);
	$result['authcode'] = $auth['authcode'];
	$result['result_row'] = t3lib_BEfunc::getRecord('tx_kequestionnaire_results',$result['uid']);
	$encoding = "UTF-8";
	if ( true === mb_check_encoding ($result['result_row']['xmldata'], $encoding ) ){
		$result['data'] = t3lib_div::xml2array($result['result_row']['xmldata']);
		if (count($result_array) == 1) $result['data'] = t3lib_div::xml2array(utf8_encode($result['result_row']['xmldata']));
	} else {
		$result['data'] = t3lib_div::xml2array(utf8_encode($result['result_row']['xmldata']));
	}
        //t3lib_div::devLog('type2 result '.$pointer, 'ke_questionnaire Export Mod', 0, $result);
	//if there is a result
	if (is_array($result['data'])){
	    //get the temp-file
	    $file_path = PATH_site.'typo3temp/'.$this->temp_file;
	    $store_file = fopen($file_path,'a+');
	    //foreach question in the structure
	    $result_line = array();
	    foreach ($fill_array as $q_nr => $values){
		    //t3lib_div::devLog('typ2 q_values '.$q_nr, 'ke_questionnaire Export Mod', 0, $values);
		    //get the csv-specifica
		    $delimeter = $this->extConf['CSV_qualifier'];
		    $parter = $delimeter.$this->extConf['CSV_parter'].$delimeter;
		    //create the result-line
		    if ($values['title'] != ''){
			switch ($values['type']){
                                case 'authcode': 
                                        $result_line[] = $result['authcode'];    
                                    break;
                                case 'start_tstamp':
                                        if ($this->extConf['exportNoTimestamp']){
                                            date($this->extConf['exportNoTimestampFormat'],$result['start_tstamp']);
                                        } else $result_line[] = $result['start_tstamp'];    
                                    break;
                                case 'finished_tstamp':
                                        if ($this->extConf['exportNoTimestamp']){
                                            date($this->extConf['exportNoTimestampFormat'],intval($result['finished_tstamp']);
                                        } else $result_line[] = $result['finished_tstamp'];    
                                    break;
				case 'open':
					$result_line[] = str_replace("\n"," ",$result['data'][$q_nr]['answer']);
					//$result_line[] = $result['data'][$q_nr]['answer'];
                                    break;
				case 'closed':
					foreach ($values['answers'] as $a_id => $a_values){
						//t3lib_div::devLog('read line closed ansers '.$a_id, 'ke_questionnaire Export Mod', 0, $a_values);
                                                if (is_array($result['data'][$q_nr]['answer']['options']) AND in_array($a_nr,$result['data'][$q_nr]['answer']['options'])){
						    if ($result['data'][$q_nr]['answer']['text'][$a_id]) $result_line[] = '('.$result['data'][$q_nr]['answer']['text'][$a_id].') '.$marker;
						    else $result_line[] = $marker;
						} elseif ($result['data'][$q_nr]['answer']['options'] == $a_id) {
                                                    if ($result['data'][$q_nr]['answer']['text'][$a_id]){
                                                        $result_line[] = '('.$result['data'][$q_nr]['answer']['text'][$a_id].') '.$marker;
                                                    } else $result_line[] = $marker;
						} else $result_line[] = '';
					}
				    break;
				case 'semantic':
					//t3lib_div::devLog('read line', 'ke_questionnaire Export Mod', 0, $read_line);
					//t3lib_div::devLog('values semantic', 'ke_questionnaire Export Mod', 0, $values);
				case 'matrix':                                
					//t3lib_div::devLog('matrix '.$q_nr, 'ke_questionnaire Export Mod', 0, $act_v);
					if (is_array($values['subquestions'])){
					    foreach ($values['subquestions'] as $sub_nr => $sub_values){
						    //t3lib_div::devLog('matrix sub '.$sub_nr, 'ke_questionnaire Export Mod', 0, $sub_values);
						    foreach ($sub_values['columns'] as $c_nr => $c_values){
							    //t3lib_div::devLog('matrix sub c '.$c_nr, 'ke_questionnaire Export Mod', 0, $c_values);
							    $temp_type = $result['data'][$q_nr]['subtype'];
							    if ($values['columns'][$c_nr]['different_type'] != '') $temp_type = $values['columns'][$c_nr]['different_type'];
							    //t3lib_div::devLog('matrix temp_type '.$temp_type, 'ke_questionnaire Export Mod', 0, $act_v);
							    if ($temp_type == 'input'){
								    $result_line[] = $result['data'][$q_nr]['answer']['options'][$sub_nr][$c_nr][0];
							    } elseif (is_array($result['data'][$q_nr]['answer']['options'][$sub_nr])){
								    //if (in_array($c_nr,$act_v['answer']['options'][$sub_nr])){
								    if ($result['data'][$q_nr]['answer']['options'][$sub_nr][$c_nr][0] == $c_nr){
									    $result_line[] = $marker;
								    } elseif ($c_nr == $result['data'][$q_nr]['answer']['options'][$sub_nr]['single']) {
									    $result_line[] = $marker;
								    }
							    } else {
								    if ($c_nr == $result['data'][$q_nr]['answer']['options'][$sub_nr]){
									    $result_line[] = $marker;
								    }
							    }
							    /* für Text-Zeilen anderes verhalten
							    if ($result['data'][$q_nr]['answer']['text'][$sub_nr]){
								    $result_line[] = '('.$act_v['answer']['text'][$sub_nr][0].') '.$write_array['subquestions'][$sub_nr]['columns'][$c_nr]['results'][$v_nr];
							    }*/
						    }
					    }
					}
				    break;
                                case 'demographic':
					//t3lib_div::devLog('demo '.$q_nr, 'ke_questionnaire Export Mod', 0, $act_v);
					if (is_array($result['data'][$q_nr]['answer']['fe_users'])){
						foreach ($result['data'][$q_nr]['answer']['fe_users'] as $fe_nr => $fe_values){
							$result_line[] = $fe_values;
						}
					}
					if (is_array($result['data'][$q_nr]['answer']['tt_address'])){
						foreach ($result['data'][$q_nr]['answer']['tt_address'] as $fe_nr => $fe_values){
							$result_line[] = $fe_values;
						}
					}
				    break;
				default:
					// Hook to make other types available for export
                                        if (is_array($act_v) AND is_array($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['ke_questionnaire']['CSVExportCreateDataType2FileQType'])){
                                                foreach($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['ke_questionnaire']['CSVExportCreateDataFileType2QType'] as $_classRef){
                                                        $_procObj = & t3lib_div::getUserObj($_classRef);
                                                        $result_line[] = $_procObj->CSVExportCreateDataFileType2QType($values,$result);
                                                }
                                        }
				    break;
			}
		    }
	    }
	    //t3lib_div::devLog('result_line '.$q_nr.'/'.$v_nr, 'ke_questionnaire Export Mod', 0, $result_line);
	    fwrite($store_file,$delimeter.implode($parter,$result_line).$delimeter."\n");
	    fclose($store_file);
	}
    }
    
    function createHookedDataFileType($nr){
        // Hook for other CSV-Export-Types
        if (is_array($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['ke_questionnaire']['CSVExportTypeCreateDataFile'])){
                foreach($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['ke_questionnaire']['CSVExportTypeCreateDataFile'] as $_classRef){
                        $_procObj = & t3lib_div::getUserObj($_classRef);
                        $_procObj->CSVExportTypeCreateDataFile($this,$nr);
                }
        }
    }
    
        function createFillArray(){
		//get the questions
		$storage_pid = $this->ff_data['sDEF']['lDEF']['storage_pid']['vDEF'];
		$where = 'pid='.$storage_pid.' and hidden=0 and deleted=0 and type!="blind"';
		if (htmlentities(t3lib_div::_GP('only_this_lang'))){
			$lang = explode('_',htmlentities(t3lib_div::_GP('only_this_lang')));
			$where .= ' AND sys_language_uid='.$lang[1];
		}
		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*','tx_kequestionnaire_questions',$where,'','sorting');
	
		//create the question structure
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
							if (is_array($flex)) $fe_user_fields = explode(',',$flex['data']['sDEF']['lDEF']['FeUser_Fields']['vDEF']);
							$flex = t3lib_div::xml2array($question['demographic_addressfields']);
							if (is_array($flex)) $fe_user_addressfields = explode(',',$flex['data']['sDEF']['lDEF']['FeUser_Fields']['vDEF']);
							//t3lib_div::devLog('getCSVQBase flex', 'ke_questionnaire Export Mod', 0, array($fe_user_fields,$fe_user_addressfields));
							if (is_array($fe_user_fields)){
                                                            foreach ($fe_user_fields as $field){
								$fill_array[$question['uid']]['fe_users'][$field] = array();
                                                            }
							}
                                                        if (is_array($fe_user_addressfields)){
                                                            foreach ($fe_user_addressfields as $field){
                                                            	$fill_array[$question['uid']]['tt_address'][$field] = array();
                                                            }
                                                        }
							//$lineset .= $this->getQBaseLine($free_cells,$question);
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
		
		return $fill_array;
	}

}
?>