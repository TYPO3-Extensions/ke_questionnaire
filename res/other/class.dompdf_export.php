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
        var $ffdata = '';
        var $templateFolder = '';
        var $title = '';
        var $templates = array();
        var $result = array();
        
        var $cellHeight = 0;      //Base-Definition Cell Height
        var $cellWidth = array(); //Base-Definition Cell Width
      
        var $questions = array();  //Question-array
        
        function dompdf_export($conf, $pid, $title, $ffdata){
                spl_autoload_register('DOMPDF_autoload');
                $this->title = $title;
                $this->ffdata = $ffdata;
                $this->pid = $pid;
                $this->conf = $conf;
                
                $this->templateFolder = $this->ffdata['dDEF']['lDEF']['template_dir']['vDEF'];
                if ($this->templateFolder == '') '../../../../'.trim($this->templateFolder);
                
                t3lib_div::devLog('conf', 'pdf_export', 0, $conf);
                t3lib_div::devLog('ffdata', 'pdf_export', 0, $ffdata);
                
                $this->pdf = new DOMPDF();
                
                $this->getQuestions();
                t3lib_div::devLog('questions', 'pdf_export', 0, $this->questions);
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
                t3lib_div::devLog('questionCount', $this->prefixId, 0, $this->questionCount);
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
                //t3lib_div::devLog('columns', $this->prefixId, 0, array($GLOBALS['TYPO3_DB']->SELECTquery($selectFields,'tx_kequestionnaire_columns',$where,'',$orderBy)));
                if ($res){
                        while ($row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)){
                                $lines[] = $row;
                        }
                }
                                
                return $lines;
        }
      
        function getPDFBlank(){
                $html = $this->getHTML('blank');
                t3lib_div::devLog('html', 'pdf_export', 0, array($html));
                
                $this->pdf->load_html($html);
                
                $this->pdf->render();
                $this->pdf->stream("questionnaire_".$this->pid.".pdf");
            
                //return $html;
        }
        
        function getPDFFilled($result){
                $this->result = $result;
                t3lib_div::devLog('result', 'pdf_export', 0, $result);
                
                $html = $this->getHTML('filled');
                
                $this->pdf->load_html($html);
                
                $this->pdf->render();
                $this->pdf->stream("questionnaire_".$this->pid.".pdf");
                t3lib_div::devLog('html', 'pdf_export', 0, array($html));
            
                //return $html;
        }
        
        function getHTML($type){
                $content = '';
                
                $this->getTemplates();
                switch ($type){
                        case 'blank':
                                $content .= $this->renderFirstPage();
                                t3lib_div::devLog('getHTML '.$type, 'pdf_export', 0,array($content));
                                foreach ($this->questions as $nr => $question){
                                        $content .= $this->renderQuestion($question);
                                }
                                //$content = mb_convert_encoding($content, "Windows-1252", "UTF-8");
                        break;
                        case 'filled':
                                $content .= $this->renderFirstPage();
                                foreach ($this->questions as $nr => $question){
                                        t3lib_div::devLog('columns', $this->prefixId, 0, $question);
                                        $content .= $this->renderQuestion($question,$result[$question['uid']]);
                                }
                        break;
                }
                
                $html = str_replace('###CONTENT###',$content,$this->templates['base']);
                //t3lib_div::devLog('getHTML html '.$type, 'pdf_export', 0,array($html,$content,$this->templates['base']));
                
                $css = $this->getCSS();
                $html = str_replace('###CSS###',$css,$html);
                
                return $html;
        }
        
        function getTemplates(){
                $templateFolder = $this->templateFolder;
                
                //open questions
                $templateName = 'question_open.html';
                $temp = file_get_contents($templateFolder.$templateName);
                //t3lib_div::devLog('open', 'pdf', 0, array($templateFolder.$templateName,$open));
                if ($temp == ''){
                        $templateFolder = t3lib_extMgm::extPath('ke_questionnaire').'res/templates/';
                        $temp = file_get_contents($templateFolder.$templateName);
                }
                $open_template = t3lib_parsehtml::getSubpart($temp, '###DOMPDF_SINGLE###');
                $this->templates['open_single'] = $open_template;
                $open_template = t3lib_parsehtml::getSubpart($temp, '###DOMPDF_MULTI###');
                $this->templates['open_multi'] = $open_template;
                
                //closed questions
                $templateName = 'question_closed.html';
                $temp = file_get_contents($templateFolder.$templateName);
                $this->templates['closed'] = t3lib_parsehtml::getSubpart($temp, '###DOMPDF###');
                $this->templates['closed_options'] = t3lib_parsehtml::getSubpart($temp, '###DOMPDF_OPTION###');
                
                //semantic questions
                $templateName = 'question_semantic.html';
                $temp = file_get_contents($templateFolder.$templateName);
                $this->templates['semantic'] = t3lib_parsehtml::getSubpart($temp, '###DOMPDF###');
                $this->templates['semantic_line'] = t3lib_parsehtml::getSubpart($temp, '###DOMPDF_LINE###');
                $this->templates['semantic_column'] = t3lib_parsehtml::getSubpart($temp, '###DOMPDF_COLUMN###');
                
                //matrix questions
                $templateName = 'question_matrix.html';
                $temp = file_get_contents($templateFolder.$templateName);
                $this->templates['matrix'] = t3lib_parsehtml::getSubpart($temp, '###DOMPDF###');
                $this->templates['matrix_line'] = t3lib_parsehtml::getSubpart($temp, '###DOMPDF_LINE###');
                $this->templates['matrix_column'] = t3lib_parsehtml::getSubpart($temp, '###DOMPDF_COLUMN###');
                
                //blind questions
                $templateName = 'question_blind.html';
                $temp = file_get_contents($templateFolder.$templateName);
                $this->templates['blind'] = t3lib_parsehtml::getSubpart($temp, '###DOMPDF###');
                
                t3lib_div::devLog('templates', 'pdf', 0, $this->templates);
                
                $templateName = 'questionnaire.html';
                $temp = file_get_contents($templateFolder.$templateName);
                $this->templates['base'] = t3lib_parsehtml::getSubpart($temp, '###DOMPDF###');
                
        }
        
        function getCSS(){
                $css = '';
                
                $templateFolder = $this->templateFolder;
                $templateName = 'dompdf_template.css';
                $temp = file_get_contents($templateFolder.$templateName);
                //t3lib_div::devLog('open', 'pdf', 0, array($templateFolder.$templateName,$open));
                if ($temp == ''){
                        $templateFolder = t3lib_extMgm::extPath('ke_questionnaire').'res/templates/';
                        $temp = file_get_contents($templateFolder.$templateName);
                }
                $css = $temp;
                
                return $css;
        }
        
        function renderQuestion($question){
                $markerArray = array();
                $markerArray['###QUESTION_TITLE###'] = '';
                $markerArray['###QUESTION###'] = '';
                $markerArray['###HELPTEXT###'] = $question['helptext'];
                
                if ($question['text'] == '') {
                        $markerArray['###QUESTION_TITLE###'] = $question['title'];
                } else {
                        if ($question['show_title'] == 1) {
                                $markerArray['###QUESTION_TITLE###'] = $question['title'];
                        }
                        $markerArray['###QUESTION###'] = $question['text'];
                }
                $value = '&nbsp;';
                $markerArray['###VALUE###'] = $value;
                $answered = array();
                if (is_array($this->result)) {
                        if (is_array ($this->result[$question['uid']])){
                                $answered = $this->result[$question['uid']]['answer'];
                        }
                }
                //t3lib_div::devLog('answered', 'pdf_export', 0, $answered);
                switch ($question['type']){
                        case 'blind':
                                $html = $this->renderContent($this->templates['blind'],$markerArray);
                                break;
                        case 'open':
                                if ($answered) $markerArray['###VALUE###'] = $answered;
                                if ($question['open_type'] == 1){
                                        if ($answered) $markerArray['###VALUE###'] = nl2br($answered);
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
                        case 'matrix':
                                $html = $this->renderMatrixQuestion($question,$markerArray,$answered);
                                break;
                        case 'semantic':
                                $html = $this->renderSemanticQuestion($question,$markerArray,$answered);
                                break;
                        case 'demographic':
                                //$html = $this->render
                                break;
                        default:
                                if (is_array($GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][$this->extKey]['dompdf_export_renderQuestion'])){
                                        foreach($GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][$this->extKey]['dompdf_export_renderQuestion'] as $_classRef){
                                                $_procObj = & t3lib_div::getUserObj($_classRef);
                                                $html = $_procObj->dompdf_export_renderQuestion($this,$question);
                                        }
                                }
                }
                //$html .= '</div>';
                return $html;
        }
        
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
                                $c_markerArray['###CLASS###'] = 'column';
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
                        
                        $l_markerArray['###COLUMNS###'] = $this->renderContent($this->templates['semantic_column'],$c_markerArray);
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
                                
                                $l_markerArray['###COLUMNS###'] .= $this->renderContent($this->templates['semantic_column'],$c_markerArray);
                        }
                        $c_markerArray = array();
                        $c_markerArray['###CLASS###'] = 'semantic_end';
                        $c_markerArray['###VALUE###'] = $subline['end'];
                        $l_markerArray['###COLUMNS###'] .= $this->renderContent($this->templates['semantic_column'],$c_markerArray);
                        $markerArray['###ROWS###'] .= $this->renderContent($this->templates['semantic_line'],$l_markerArray);
                }
                
                $html = $this->renderContent($this->templates['matrix'],$markerArray);
                
                return $html;
        }
        
        /**
         * renders the Start-Page for the Questionnaire
         */
        function renderFirstPage(){
                $content = '';
                
                if ($this->ffdata['tDEF']['lDEF']['description']['vDEF'] != '') $content .= '<div class="questionnaire_description">'.$this->ffdata['tDEF']['lDEF']['description']['vDEF'].'</div>';
                 
                return $content;
        }
        
        function renderContent($content,$markerArray){
                //t3lib_div::devLog('renderContent', 'pdf', 0, array($content,$markerArray));
                if (is_array($markerArray)){
                        foreach($markerArray as $key => $value){
                                $content = str_replace($key,$value,$content);
                        }
                }
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