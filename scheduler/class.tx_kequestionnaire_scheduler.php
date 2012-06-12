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
class tx_kequestionnaire_scheduler extends tx_scheduler_Task {
        var $questionnaires = array();

	/**
	 * Function executed from scheduler.
	 * Send the newsletter
	 * 
	 * @return	void
	 */
	function execute() {
		// Check if cronjob is already running:
		/*$lockfile = PATH_site . 'typo3temp/tx_kequestionnaire_cron.lock';
		if (@file_exists($lockfile)) {
				// If the lock is not older than 1 day, skip index creation:
			if (filemtime($lockfile) > (time() - (60*60*24))) {
				$GLOBALS['BE_USER']->writelog(
					4,
					0,
					1,
					'tx_kequestionnaire',
					'TYPO3 ke questionnaire Cron: Aborting, another process is already running!',
					array()
				);
				return false;
			} else {
				$GLOBALS['BE_USER']->writelog(
					4,
					0,
					0,
					'tx_kequestionnaire',
					'TYPO3 ke questionnaire Cron: A .lock file was found but it is older than 1 day! Processing mails ...',
					array()
				);
			}
		}*/

		//touch ($lockfile);
		
		//echo $this->storagePids;
                $this->getQuestionnaires();
                if (count($this->questionnaires) > 0) $this->sendMails($this->questionnaires);
		
		//unlink ($lockfile);
		return true;
	} // end of 'function execute() {..}'
        
        function getQuestionnaires(){
            global $TYPO3_DB;
            //get the activated plugins
	    $res = $TYPO3_DB->exec_SELECTquery(
		'*',
		'tt_content',
		'CType="list" AND list_type="ke_questionnaire_pi1" AND hidden=0 AND deleted=0'
            );
            if ($res){
                while($row = $TYPO3_DB->sql_fetch_assoc($res)){
                    $result_res = $TYPO3_DB->exec_SELECTquery(
                        'count(uid) as counter',
                        'tx_kequestionnaire_results',
                        'pid='.$row['pid'].' AND mailsent_tstamp==0'
                    );
                    if ($result_res){
                        $counting = $TYPO3_DB->sql_fetch_assoc($result_res);
                        if ($counting['counter'] > 0) $this->questionnaires[] = $row;  
                    } 
                }
            }
        }
        
        function sendMails($questionnaires){
            foreach ($questionnaires as $quest){
                $ffdata = t3lib_div::xml2array($quest['pi_flexform']);
		$ffdata = $ffdata['data'];
                if ($this->ffdata['mailing'] == 1 AND $ffdata['mail_turn'] == 'SCHEDULER'){
                    $email_adresses = $ffdata['emails'];
                    $mail_texts = array();
                    $mail_texts['subject'] = $ffdata['inform_mail_subject'];
                    $mail_texts['body'] = $ffdata['inform_mail_text'];
                    $mail_texts['fromEmail'] = $ffdata['mail_sender'];
                    $mail_texts['fromName'] = $ffdata['mail_from'];
                    $this->sendMail($email_adresses,$mail_texts);
                }
            }
        }
	
	function sendMail($email,$mailTexts){
		$body = $mailTexts["body"];

		$html_start="<html><head><title>".$mailTexts["subject"]."</title></head><body>";
		$html_end="</body></html>";
		$mails = explode(',',$email);
		
		if($GLOBALS['TYPO3_CONF_VARS']['MAIL']['substituteOldMailAPI'] == 0 && $GLOBALS['TYPO3_CONF_VARS']['SYS']['compat_version'] < '4.6') {
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
			$this->htmlMail->setHTML($this->htmlMail->encodeMsg($html_start.$body.$html_end));
			foreach ($mails as $mail){
				$out .= $this->htmlMail->send($mail).'<br />';
			}
		} else {
			//use swiftmailer
			$swiftParams = array(
					'setFrom' => array( $mailTexts['fromEmail'] => $mailTexts['fromName']),
					'setReturnPath' => $mailTexts['fromEmail'],
					'setReplyTo' => $mailTexts['fromEmail'],
					'setContentType' => '',
					'setCharset' => 'uft-8',
					'setTo' => array($mails),
					'setSubject' => $mailTexts['subject'],
					'setBody' => array($html_start.$body.$html_end, 'text/html'),
					'addPart' => array($body, 'text/plain')
			);
					
			$mail = t3lib_div::makeInstance('tx_kequestionnaire_swiftmailer');
			$out = $mail->send($swiftParams);
			unset($mail);
		}
		
		return $out;
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/ke_questionnaire/scheduler/class.tx_kequestionnaire_scheduler.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/ke_questionnaire/scheduler/class.tx_kequestionnaire_scheduler.php']);
}
?>