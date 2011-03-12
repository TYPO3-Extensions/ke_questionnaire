<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2008 Your name <email@example.com>
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
//class tx_kequestionnaire_schedulerTask extends tx_scheduler_Task {
class tx_kequestionnaire_scheduler_export extends tx_scheduler_Task {
        var $questionnaires = array();

	/**
	 * Function executed from scheduler.
	 * Send the newsletter
	 * 
	 * @return	void
	 */
	function execute() {
                if (!$this->pointer) $this->pointer = 0;
                $this->extConf = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['ke_questionnaire']);
                                
                $this->createDataFile();
                //t3lib_div::debug($this);
                
                if ($this->pointer >= count($this->results)) {
                    $this->sendTheFile();
                    $this->remove();
                } else {
                    //$this->pointer ++;
                    $this->save();
                }
                return true;
	} // end of 'function execute() {..}'
        
        function createDataFile(){
                include_once(PATH_site.'typo3conf/ext/ke_questionnaire/mod3/ajax.php');
		$creator = t3lib_div::makeInstance('tx_kequestionnaire_module3_ajax');
		//set needed Vars
                $creator->q_id = $this->q_id;
                $creator->pid = $this->pid;
                $creator->temp_file = $this->temp_file;
                $creator->extConf = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['ke_questionnaire']);
                $creator->type = $this->type;
                $creator->results = $this->results;
                $creator->ff_data = $this->ff_data;
		$creator->q_lang = $this->q_data['sys_language_uid'];
		$creator->only_this_lang = $this->only_this_lang;
		
		//delete the old generated file
		$file_path = PATH_site.'typo3temp/'.$this->temp_file;
		
                //t3lib_div::devLog('cron '.$this->pointer, 'ke_questionnaire Export Mod', 0, $this->extConf);
                if ($this->pointer < count($this->results)) {
                    for ($i = 0; $i < $this->extConf['exportInterval']; $i++){
                        if ($this->pointer < count($this->results)) {
                            if ($this->export_type == 'questions') $creator->createDataFile($this->pointer);
                            elseif ($this->export_type == 'simple2') $creator->createDataFileType2($this->pointer);
                            $this->pointer ++;
                            //t3lib_div::devLog('cron '.$this->pointer, 'ke_questionnaire Export Mod', 0, $this->extConf);
                        } else {
                            break;
                        }
                    }
                }		
        }
        
        function sendTheFile(){
                $LOCAL_LANG = t3lib_div::readLLfile(t3lib_extMgm::extPath('ke_questionnaire').'scheduler/locallang.xml','default');
		$LOCAL_LANG = $LOCAL_LANG['default'];
                //t3lib_div::devLog('cron '.$this->pointer, 'ke_questionnaire Export Mod', 0, $LOCAL_LANG);
                
                $mailTexts['subject'] = $LOCAL_LANG['export_subject'];
                $mailTexts['fromName'] = $LOCAL_LANG['export_fromName'];
                $mailTexts['fromEmail'] = $LOCAL_LANG['export_fromEmail'];
                $mailTexts['body'] = $LOCAL_LANG['export_body'];
                
                //create the file to send
                $file = $this->createMailFile();
                $this->sendMail($this->mailTo,$mailTexts,$file);
        }
        
        function createMailFile(){
                require_once(t3lib_extMgm::extPath('ke_questionnaire').'res/other/class.csv_export.php');
		$csv_export = new csv_export($this->extConf,$this->results,$this->q_data,$this->ff_data,$this->temp_file, $this->only_this_lang);
		
		switch ($this->export_type){
			case 'simple':
				//$csvdata = $this->getCSVSimple();
				$csvdata = $csv_export->getCSVSimple();
				break;
			case 'simple2':
				//$csvdata = $this->getCSVSimple2();
				$csvdata = $csv_export->getCSVSimple2();
				break;
			case 'questions':
				//$csvdata = $this->getCSVQBased();
				$csvdata = $csv_export->getCSVQBased();
				break;
			default:
				break;
		}
	
		$csvdata = mb_convert_encoding($csvdata, "Windows-1252", "UTF-8");
                
                $file_path = PATH_site.'typo3temp/'.$this->temp_file.'.csv';
                if (file_exists($file_path)) {
		    unlink($file_path);
		}
                $file = fopen($file_path,'w');
                //t3lib_div::devLog('csvdata', 'scheduler', 0, array($csvdata));
                fwrite($file,$csvdata);
                fclose($file);
                
                return $file_path;
        }
        
	function sendMail($email,$mailTexts,$file){
		$body = $mailTexts["body"];

		$html_start="<html><head><title>".$mailTexts["subject"]."</title></head><body>";
		$html_end="</body></html>";

		$this->htmlMail = t3lib_div::makeInstance('t3lib_htmlmail');
		$this->htmlMail->start();
		$this->htmlMail->recipient = $email;
		$this->htmlMail->subject = $mailTexts['subject'];
		$this->htmlMail->from_email = $mailTexts['fromEmail'];
		$this->htmlMail->from_name = $mailTexts['fromName'];
		$this->htmlMail->replyto_name = $mailTexts['fromName'];
		$this->htmlMail->organisation = $mailTexts['fromName'];
		$this->htmlMail->returnPath = $mailTexts['fromEmail'];
		$this->htmlMail->addPlain($body);
                $this->htmlMail->addAttachment($file);
		$this->htmlMail->setHTML($this->htmlMail->encodeMsg($html_start.$body.$html_end));
		$mails = explode(',',$email);
		foreach ($mails as $mail){
			$out .= $this->htmlMail->send($mail).'<br />';
		}
		//t3lib_div::devLog('sendMail out', 'scheduler', 0, array($out,$mails,$mailTexts,$file));
		return $out;
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/ke_questionnaire/scheduler/class.tx_kequestionnaire_scheduler_export.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/ke_questionnaire/scheduler/class.tx_kequestionnaire_scheduler_export.php']);
}
?>