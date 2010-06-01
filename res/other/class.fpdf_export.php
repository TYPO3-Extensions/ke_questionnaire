<?php
/*
 * PDF Export Class for ke_questionnaire
 *
 * Copyright (C) 2009 kennziffer.com / Nadine Schwingler
 * All rights reserved.
 * License: GNU/GPL License
 *
 */

//require_once(t3lib_extMgm::extPath('fpdf').'class.tx_fpdf.php');
require_once(t3lib_extMgm::extPath('ke_questionnaire').'res/other/html2fpdf/html2fpdf.php');
require_once(PATH_tslib . 'class.tslib_content.php'); // load content file

class pdf_export {
  var $conf = array();      //Basis PDF Conf
  var $pdf = '';            //PDF-Objekt
  var $pid = 0;             //Pid of data Storage
  var $description = '';
  var $title = '';
  
  var $cellHeight = 0;      //Base-Definition Cell Height
  var $cellWidth = array(); //Base-Definition Cell Width

  var $questions = array();  //Question-array

  function pdf_export($conf, $pid, $title, $description){
    $this->title = $title;
    $this->description = $description;
    $this->pid = $pid;
    $this->conf = $conf;
    // Get a new instance of the FPDF library
    $this->pdf = new HTML2FPDF($this->conf['orientation'], $this->conf['unit'], $this->conf['format']);
    
    //Initialization WriteHTML
    $this->B=0;
    $this->I=0;
    $this->U=0;
    $this->HREF='';

    // Determine the widths and height of cells
    $this->cellHeight = ($this->conf['font_size']+6)/$this->pdf->k;
    $this->cellWidth = array(13*$this->conf['font_size']/$this->pdf->k, $this->pdf->pageWidth*0.75);
    $this->cellWidth[] = $this->pdf->fw - $this->conf['margin_left'] - $this->conf['margin_right'] - $this->cellWidth['0'];

    // Set the template
    if ($this->conf['template']){
      $this->pdf->tx_fpdf->template = PATH_site.$this->conf['template'];
    }
    
    //testen wir doch mal

    // Set the page margins and start a new page
    $this->pdf->SetMargins(intval($this->conf['margin_left']), intval($this->conf['margin_top']), intval($this->conf['margin_right']));
    $this->pdf->AddPage();
    $this->pdf->SetFont($this->conf['font'],'',intval($this->conf['font_size']));
    //$this->pdf->SetFillColor(intval($this->conf['fill_color']));
    
    $this->getQuestions();
    // The data must be in iso-8859-1: Determine the charset from the database
    $this->fromCharset = $GLOBALS['TYPO3_CONF_VARS']['BE']['forceCharset'] ? $GLOBALS['TYPO3_CONF_VARS']['BE']['forceCharset'] : 'iso-8859-1';
    // Convert to iso-8859-1
    $this->cs = t3lib_div::makeInstance('t3lib_cs');
    $this->cs->convArray($this->questions, $this->fromCharset, 'iso-8859-1');
    $this->description = $this->cs->conv($this->description,$this->fromCharset,'iso-8859-1');
    
    //t3lib_div::devLog('PDF '.$this->pid, 'pdf_export', 0, $this->conf);
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
        if ($row['type'] != 'blind') $this->questions[] = $row;
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
      while ($row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($res))
        $options[] = $row;
    }
    $this->cs->convArray($options,$this->fromCharset, 'iso-8859-1');
    return $options;
  }

  function getPDFBlank(){
    //t3lib_div::devLog('PDF conf', 'pdf_export', 0, $this->conf);
    //t3lib_div::devLog('PDF questions', 'pdf_export', 0, $this->questions);
    
    $this->pdf->SetFont($this->conf['font'],'B',$this->conf['font_size']+2);  
    $this->pdf->WriteHTML($this->title);
    $this->pdf->SetFont($this->conf['font'],'',$this->conf['font_size']);  
    $this->renderFirstPage();
    $this->pdf->AddPage();
    foreach ($this->questions as $id => $question){
      $this->pdf->SetFont($this->conf['font'],'B',$this->conf['font_size']+2);  
      $this->renderQuestion($question);
    }    
    // Convert to PDF
    $content = $this->pdf->Output('', 'S');

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
  
  function renderQuestion($question){
    if ($question['text'] == '') $title_text = $question['title'];
    else $title_text = $question['text'];
    // Label
    $this->pdf->WriteHTML($title_text);
    //$this->pdf->MultiCell('',$this->cellHeight,$title_text,0,'L');
    $this->pdf->Cell('',($this->cellHeight *1.5), '', '', 1);
    
    switch ($question['type']){
      case 'open':
          $this->pdf->Cell('',$this->cellHeight, '', 1, 1);
        break;
      case 'closed':
          $options = $this->getOptions($question['uid']);
          foreach ($options as $nr => $option){
            $option_text = $option['text'];
            if ($option_text == '')$option_text = $option['title'];
            switch ($question['closed_type']){
              case 'radio_single':
              case 'select_single':
              case 'check_multi':
              case 'select_multi':
                  $this->pdf->Cell($this->cellHeight-2,$this->cellHeight-2, '', 1, 0);
                  $this->pdf->Cell($this->cellHeight-2,$this->cellHeight-2, '', 0, 0);
                break;
            }
            $this->pdf->WriteHTML($option_text);
            $this->pdf->Ln($this->cellHeight);
          }
	  $this->pdf->Cell('',$this->cellHeight, '', 0, 1);
        break;
    }
    $this->pdf->Cell('',($this->cellHeight *2), '', '', 1);
  }
  
  /**
   * renders the Start-Page for the Questionnaire
   */
  function renderFirstPage(){
    $content = '';
    
    $this->pdf->Cell('',$this->cellHeight, '', 0, 1);
    $this->pdf->WriteHTML(html_entity_decode($this->description));
    //$this->pdf->MultiCell('',$this->cellHeight, $this->description, 1, 1);
    $this->pdf->Cell('',$this->cellHeight, '', 0, 1);
     
    return $content;
  }  
}

?>
