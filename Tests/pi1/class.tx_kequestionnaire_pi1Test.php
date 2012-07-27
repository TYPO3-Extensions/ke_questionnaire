<?php
class Pi1Test extends Tx_Extbase_BaseTestCase {

	/**
	 * @var tx_kequestionnaire_pi1
	 */
	protected $pi1;





	public function setUp() {
		// Initialize TSFE object
		$GLOBALS['TSFE'] = t3lib_div::makeInstance('tslib_fe',  $TYPO3_CONF_VARS);
		$GLOBALS['TSFE']->config = array();
		$GLOBALS['TSFE']->connectToDB();
		$GLOBALS['TSFE']->initFEuser();
		$GLOBALS['TSFE']->sys_page = t3lib_div::makeInstance('t3lib_pageSelect');

		$this->pi1 = t3lib_div::makeInstance('tx_kequestionnaire_pi1');
		$this->pi1->conf = array(
			'type' =>	'QUIZ',
			'user_reports' =>	'1',
			'storage_pid' => $this->getSysfolderUid(),
			'access' =>	'FREE',
			'max_participations' =>	'1',
			'restart_possible' =>	'0',
			'history' => '0',
			'description' => '',
			'end_text' => '',
			'template_dir' => '',
			'render_type' => 'ALL',
			'render_count' => '1',
			'render_count_withblind' =>	'0',
			'render_count_withoutdependant' => '0',
			'show_lastanswer' => '0',
			'show_pagecounter' =>	'1',
			'show_pagemap' =>	'0',
			'pagemap_navigation' =>	'0',
			'redirect_on_finish_uid' =>	'0',
			'end_page' => '',
			'closed_multi_horizontal' => '1',
			'mailing' => '0',
			'mail_turn' => 'PROMPT',
			'emails' =>	'',
			'inform_mail_subject' => '',
			'inform_mail_text' => '',
			'send_finish_mail' =>	'1',
			'send_finish_mail_email' =>	'webmaster@kennziffer.com',
			'send_finish_mail_subject' =>	'Geschafft',
			'send_finish_mail_emailhead' =>	'Hier ist Deine Auswertung',
			'mail_from' => 'Webmaster',
			'mail_sender' => 'webmaster@kennziffer.com',
			'invite_mail_subject' => 'Einladung Umfrage',
			'invite_mail_text' => 'Hallo viel Spaß der Umfrage ###LINK### Stefan',
			'remind_mail_subject' => 'Einladung nochmal',
			'remind_mail_text' => 'Hallo Du hast vergessen teilzunehmen: ###LINK### Stefan',
		);
		$this->pi1->ffdata = $this->pi1->conf;
		$this->pi1->cObj = t3lib_div::makeInstance('tslib_cObj');
		$this->pi1->cObj->data = array(
			'dDEF' => array(
				'lDEF' => array(
					'template_dir' => array(
						'vDEF' => ''
					)
				)
			)
		);
		$this->pi1->piVars = array(
			'sendemail' => '1',
			'p_id' => $this->getLatestResultRecord(),
			'email' => 'froemken@kennziffer.com'
		);
		$this->pi1->saveArray = array();
	}

	public function tearDown() {
		unset($this->pi1);
		unset($GLOBALS['TSFE']);
	}





	/**
	 * get a sysfolder id containing questions
	 *
	 * @return void
	 */
	protected function getSysfolderUid() {
		$row = $GLOBALS['TYPO3_DB']->exec_SELECTgetSingleRow(
			'pid',
			'tx_kequestionnaire_questions',
			'deleted = 0 AND hidden = 0',
			'', ''
		);
		return $row['pid'];
	}


	/**
	 * get latest result record
	 *
	 * @return void
	 */
	protected function getLatestResultRecord() {
		$row = $GLOBALS['TYPO3_DB']->exec_SELECTgetSingleRow(
			'uid',
			'tx_kequestionnaire_results',
			'deleted = 0 AND hidden = 0',
			'', 'uid'
		);
		return $row['uid'];
	}


	/**
	 * Test sendByMail
	 *
	 * @test
	 */
	public function checkSendByMail() {
		$tempSubstituteOldMailApi = $GLOBALS['TYPO3_CONF_VARS']['MAIL']['substituteOldMailAPI'];

		$GLOBALS['TYPO3_CONF_VARS']['MAIL']['substituteOldMailAPI'] = 0;
		$this->pi1->ffdata['send_finish_mail_emailhead'] = 'Here is your result with substituteOldMailAPI set to 0';
		$sendMail = $this->pi1->sendByMail('froemken@kennziffer.com');
		$this->assertEquals(TRUE, $sendMail, 'There is a problem if substituteOldMail is set to FALSE');

		$GLOBALS['TYPO3_CONF_VARS']['MAIL']['substituteOldMailAPI'] = 1;
		$this->pi1->ffdata['send_finish_mail_emailhead'] = 'Here is your result with substituteOldMailAPI set to 1';
		$sendMail = $this->pi1->sendByMail('froemken@kennziffer.com');
		$this->assertEquals(TRUE, $sendMail, 'There is a problem if substituteOldMail is set to TRUE');

		$GLOBALS['TYPO3_CONF_VARS']['MAIL']['substituteOldMailAPI'] = $tempSubstituteOldMailApi;
	}
}
?>