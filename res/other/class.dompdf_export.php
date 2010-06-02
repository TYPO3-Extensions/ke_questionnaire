<?php
/*
 * PDF Export Class for ke_questionnaire
 *
 * Copyright (C) 2010 kennziffer.com / Nadine Schwingler
 * All rights reserved.
 * License: GNU/GPL License
 *
 */

//require_once(t3lib_extMgm::extPath('fpdf').'class.tx_fpdf.php');
require_once(t3lib_extMgm::extPath('ke_dompdf')."res/dompdf/dompdf_config.inc.php");
require_once(PATH_tslib . 'class.tslib_content.php'); // load content file

class dompdf_export {
        var $conf = array();      //Basis PDF Conf
        var $pdf = '';            //PDF-Objekt
        var $pid = 0;             //Pid of data Storage
        var $description = '';
        var $title = '';
        
        var $cellHeight = 0;      //Base-Definition Cell Height
        var $cellWidth = array(); //Base-Definition Cell Width
      
        var $questions = array();  //Question-array
        
        function dompdf_export($conf, $pid, $title, $description){
                spl_autoload_register('DOMPDF_autoload');
                $this->title = $title;
                $this->description = $description;
                $this->pid = $pid;
                $this->conf = $conf;
                // Get a new instance of the FPDF library
                $this->pdf = new DOMPDF();
                
                $this->getQuestions();
                
                // Set the template
                if ($this->conf['template']){
                  
                }
        }
      
        /**
         * Gather all the questions of this questionnaire ready for showing
         *
         */
        function getQuestions(){
                $this->questionCount['total'] = 0; //total of questions
                $this->questionCount['only_questions'] = 0; //no blind-texts counting
                // $selectFields = 'uid,type,title,demographic_type,open_in_text,open_validation';
                $selectFields = '*';
                $where = 'pid='.$this->pid.' AND hidden = 0 AND deleted = 0';
                $orderBy = 'sorting';
                $res = $GLOBALS['TYPO3_DB']->exec_SELECTquery($selectFields,'tx_kequestionnaire_questions',$where,'',$orderBy);
                //t3lib_div::devLog('where', 'pdf_export', 0, array($where));
            
                if ($res){
                        while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)){
                                $this->allQuestions[] = $row;
                                $this->questions[] = $row;
                                $this->questionsByID[$row['uid']] = $row;
                        }
                }
            
                $this->questionCount['only_questions'] = count($this->questions);
                $this->questionCount['total'] = count($this->allQuestions);
                //t3lib_div::devLog('questionCount', $this->prefixId, 0, $this->questionCount);
        }
        
        function getOptions($uid){
                $options = array();
                
                $selectFields = '*';
                $where = 'question_uid='.$uid.' AND hidden = 0 AND deleted = 0';
                //t3lib_div::devLog('where', 'pdf_export', 0, array($where));
                $orderBy = 'sorting';
                $res = $GLOBALS['TYPO3_DB']->exec_SELECTquery($selectFields,'tx_kequestionnaire_answers',$where,'',$orderBy);
                if ($res){
                        while ($row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)){
                                $options[] = $row;
                        }
                }
                return $options;
        }
        
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
        
        function getColumns($uid){
                $lines = array();
                
                $selectFields = '*';
                $where = 'question_uid='.$uid.' AND hidden=0 AND deleted=0';
                $orderBy = 'sorting';
                $res = $GLOBALS['TYPO3_DB']->exec_SELECTquery($selectFields,'tx_kequestionnaire_columns',$where,'',$orderBy);
                t3lib_div::devLog('columns', $this->prefixId, 0, array($GLOBALS['TYPO3_DB']->SELECTquery($selectFields,'tx_kequestionnaire_columns',$where,'',$orderBy)));
                if ($res){
                        while ($row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)){
                                $lines[] = $row;
                        }
                }
                t3lib_div::devLog('columns', $this->prefixId, 0, $lines);
                
                return $lines;
        }
      
        function getPDFBlank(){
                $html = $this->getHTML();
                
                $this->pdf->load_html($html);
                
                $this->pdf->render();
                $this->pdf->stream("questionnaire_".$this->pid.".pdf");
            
                //return $html;
        }
        
        function getHTML(){
                $content = '';
                
                $content .= $this->renderFirstPage();
                foreach ($this->questions as $nr => $question){
                    $content .= $this->renderQuestion($question);
                }
                //$content = mb_convert_encoding($content, "Windows-1252", "UTF-8");
                
                $html = file_get_contents('../res/other/dompdf_template.html');
                $html = str_replace('###CONTENT###',$content,$html);
                
                $css = $this->getCSS();
                $html = str_replace('###CSS###',$css,$html);
                
                return $html;
        }
        
        function getCSS(){
                $css = '';
                
                $css = file_get_contents('../res/other/dompdf_template.css');
                
                return $css;
        }
        
        function renderQuestion($question){
                $html = '<div class="question">';
                if ($question['text'] == '') {
                        $html .= '<div class="question_title">'.$question['title'].'</div>';
                } else {
                        $html .= '<div class="question_title">';
                        if ($question['show_title'] == 1) $html .= $question['title'];
                        $html .= $question['text'];
                        $html .= '</div>';
                }
                $value = '&nbsp;';
                switch ($question['type']){
                        case 'open':
                                if ($question['open_type'] == 1){
                                        $html .= '<div class="open_question_multi">'.$value.'</div>';        
                                } else {
                                        $html .= '<div class="open_question">'.$value.'</div>';        
                                }
                                break;
                        case 'closed':
                                $options = $this->getOptions($question['uid']);
                                foreach ($options as $option){
                                        $html .= '<div class="closed_question_option">';
                                        $html .= $value;
                                        $html .= '</div>';
                                        $html .= $option['title'];
                                }
                                break;
                        case 'matrix':
                                $html .= $this->renderMatrixQuestion($question);
                                break;
                        case 'semantic':
                                $html .= $this->renderSemanticQuestion($question);
                                break;
                        default:
                                if (is_array($GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][$this->extKey]['dompdf_export_renderQuestion'])){
                                        foreach($GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][$this->extKey]['dompdf_export_renderQuestion'] as $_classRef){
                                                $_procObj = & t3lib_div::getUserObj($_classRef);
                                                $html .= $_procObj->dompdf_export_renderQuestion($this,$question);
                                        }
                                }
                }
                $html .= '</div>';
                return $html;
        }
        
        function renderSemanticQuestion($question){
                $html = '';
                
                $html .= '<table class="semantic_question">';
                
                $sublines = $this->getSemanticLines($question['uid']);
                $columns = $this->getColumns($question['uid']);
                //t3lib_div::devLog('columns', $this->prefixId, 0, $columns);
                
                if (is_array($columns)){
                        $html .= '<tr>';
                        $html .= '<td>&nbsp;</td>';
                        foreach ($columns as $column){
                                $html .= '<td class="column">';
                                $html .= $column['title'];
                                $html .= '</td>';
                        }
                        $html .= '<td class="semantic_end">&nbsp;</td>';
                        $html .= '</tr>';
                }
                
                foreach ($sublines as $subline){
                        $html .= '<tr>';
                        $html .= '<td>'.$subline['start'].'</td>';
                        foreach ($columns as $column){
                                $html .= '<td class="column">';
                                $html .= '<div class="semantic_check">'.$value.'</div>';
                                $html .= '</td>';
                        }
                        $html .= '<td class="semantic_end">'.$subline['end'].'</td>';
                        $html .= '</tr>';
                }                
                $html .= '</table>';
                
                return $html;
        }
        
        function renderMatrixQuestion($question){
                $html = '';
                
                $html .= '<table class="matrix_question">';
                
                $subquestions = $this->getMatrixLines($question['uid']);
                $columns = $this->getColumns($question['uid']);
                //t3lib_div::devLog('columns', $this->prefixId, 0, $columns);
                if (is_array($columns)){
                        $html .= '<tr>';
                        $html .= '<td>&nbsp;</td>';
                        foreach ($columns as $column){
                                $html .= '<td class="column">';
                                $html .= $column['title'];
                                $html .= '</td>';
                        }
                        $html .= '</tr>';
                }
                foreach ($subquestions as $subquestion){
                        $html .= '<tr>';
                        $html .= '<td>'.$subquestion['title'].'</td>';
                        foreach ($columns as $column){
                                $html .= '<td class="column">';
                                if ($column['different_type'] != ''){
                                        $m_type = $column['different_type'];
                                } else {
                                        $m_type = $question['matrix_type'];
                                }
                                switch ($m_type){
                                        case 'check':
                                        case 'radio':
                                                $html .= '<div class="matrix_check">'.$value.'</div>';
                                                break;
                                        default:
                                                $html .= '<div class="matrix_input">'.$value.'</div>';
                                }
                                $html .= $value;
                                $html .= '</td>';
                        }
                        $html .= '</tr>';
                }
                
                $html .= '</table>';
                
                return $html;
        }
        
        /**
         * renders the Start-Page for the Questionnaire
         */
        function renderFirstPage(){
                $content = '';
                
                if ($this->description != '') $content .= '<div class="questionnaire_description">'.$this->description.'</div>';
                 
                return $content;
        }  
        
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