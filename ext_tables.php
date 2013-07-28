<?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');

t3lib_extMgm::addToInsertRecords('tx_kequestionnaire_questions');

if (TYPO3_MODE=="BE")	include_once(t3lib_extMgm::extPath("ke_questionnaire")."res/class.tx_kequestionnaire_tx_kequestionnaire_questions_type.php");
if (TYPO3_MODE=="BE")	include_once(t3lib_extMgm::extPath("ke_questionnaire")."res/class.tx_kequestionnaire_tx_kequestionnaire_questions_open_type.php");
if (TYPO3_MODE=="BE")	include_once(t3lib_extMgm::extPath("ke_questionnaire")."res/class.tx_kequestionnaire_tx_kequestionnaire_questions_open_validation.php");
if (TYPO3_MODE=="BE")	include_once(t3lib_extMgm::extPath("ke_questionnaire")."res/class.tx_kequestionnaire_tx_kequestionnaire_questions_closed_type.php");
if (TYPO3_MODE=="BE")	include_once(t3lib_extMgm::extPath("ke_questionnaire")."res/class.tx_kequestionnaire_tx_kequestionnaire_questions_dd_words_type.php");
if (TYPO3_MODE=="BE")	include_once(t3lib_extMgm::extPath("ke_questionnaire")."res/class.tx_kequestionnaire_tx_kequestionnaire_questions_matrix_type.php");
if (TYPO3_MODE=="BE")	include_once(t3lib_extMgm::extPath("ke_questionnaire")."res/class.tx_kequestionnaire_tx_kequestionnaire_questions_matrix_validation.php");
if (TYPO3_MODE=="BE")	include_once(t3lib_extMgm::extPath("ke_questionnaire")."res/class.tx_kequestionnaire_tx_kequestionnaire_questions_demographic_fields.php");
if (TYPO3_MODE=="BE")	include_once(t3lib_extMgm::extPath("ke_questionnaire")."res/class.tx_kequestionnaire_tx_kequestionnaire_dependancies_activating_value.php");
if (TYPO3_MODE=="BE")	include_once(t3lib_extMgm::extPath("ke_questionnaire")."res/class.tx_kequestionnaire_tx_kequestionnaire_dependancies_activating_question.php");
if (TYPO3_MODE=="BE")	include_once(t3lib_extMgm::extPath("ke_questionnaire")."res/class.tx_kequestionnaire_tx_kequestionnaire_redirect.php");
if (TYPO3_MODE=="BE")	include_once(t3lib_extMgm::extPath("ke_questionnaire")."res/class.tx_kequestionnaire_tx_kequestionnaire_questions_dd_area_type.php");
if (TYPO3_MODE=="BE")	include_once(t3lib_extMgm::extPath("ke_questionnaire")."res/class.tx_kequestionnaire_tx_kequestionnaire_outcomes_type.php");
if (TYPO3_MODE=="BE")	include_once(t3lib_extMgm::extPath("ke_questionnaire")."res/class.tx_kequestionnaire_type.php");

// CSH Definitions
t3lib_extMgm::addLLrefForTCAdescr('tx_kequestionnaire_questions','EXT:ke_questionnaire/locallang_csh_question.xml');
t3lib_extMgm::addLLrefForTCAdescr('tx_kequestionnaire_answers','EXT:ke_questionnaire/locallang_csh_answer.xml');
t3lib_extMgm::addLLrefForTCAdescr('tx_kequestionnaire_columns','EXT:ke_questionnaire/locallang_csh_column.xml');
t3lib_extMgm::addLLrefForTCAdescr('tx_kequestionnaire_dependancies','EXT:ke_questionnaire/locallang_csh_dependancy.xml');
t3lib_extMgm::addLLrefForTCAdescr('tx_kequestionnaire_sublines','EXT:ke_questionnaire/locallang_csh_subline.xml');
t3lib_extMgm::addLLrefForTCAdescr('tx_kequestionnaire_subquestions','EXT:ke_questionnaire/locallang_csh_subquestion.xml');
t3lib_extMgm::addLLrefForTCAdescr('tt_content.pi_flexform.ke_questionnaire_pi1.list','EXT:ke_questionnaire/locallang_csh_flexform.xml');

$TCA["tx_kequestionnaire_questions"] = array (
	"ctrl" => array (
		'title'     => 'LLL:EXT:ke_questionnaire/locallang_db.xml:tx_kequestionnaire_questions',
		'label'     => 'title',
		'tstamp'    => 'tstamp',
		'crdate'    => 'crdate',
		'cruser_id' => 'cruser_id',
                'dividers2tabs' => TRUE,
		'type' => 'type',
		'versioningWS' => TRUE,
		'origUid' => 't3_origuid',
		'languageField'            => 'sys_language_uid',
		'transOrigPointerField'    => 'l18n_parent',
		'transOrigDiffSourceField' => 'l18n_diffsource',
		'sortby' => 'sorting',
		'delete' => 'deleted',
                'requestUpdate' => 'closed_type,matrix_type,open_validation',
		'enablecolumns' => array (
			'disabled' => 'hidden',
			'starttime' => 'starttime',
			'endtime' => 'endtime',
			'fe_group' => 'fe_group',
		),
		'dynamicConfigFile' => t3lib_extMgm::extPath($_EXTKEY).'tca.php',
		'iconfile'          => t3lib_extMgm::extRelPath($_EXTKEY).'icon_tx_kequestionnaire_questions.gif',
	),
	"feInterface" => array (
		"fe_admin_fieldList" => "sys_language_uid, l18n_parent, l18n_diffsource, hidden, starttime, endtime, fe_group, type, title, show_title, text, helptext, image, mandatory, mandatory_correct, time, dependant_show, open_type, open_pre_text, open_in_text, open_post_text, open_validation, closed_type, closed_selectsize, closed_inputfield, closed_maxanswers,matrix_type, matrix_validation, demographic_type, demographic_fields, demographic_addressfields, privacy_post, privacy_link, privacy_file, answers, columns, dependancy, dependancy_simple, subquestions, sublines",
	)
);

t3lib_extMgm::addToInsertRecords('tx_kequestionnaire_answers');

$TCA["tx_kequestionnaire_answers"] = array (
	"ctrl" => array (
		'title'     => 'LLL:EXT:ke_questionnaire/locallang_db.xml:tx_kequestionnaire_answers',
		'label'     => 'title',
		'tstamp'    => 'tstamp',
		'crdate'    => 'crdate',
		'cruser_id' => 'cruser_id',
		'languageField'            => 'sys_language_uid',
		'transOrigPointerField'    => 'l18n_parent',
		'transOrigDiffSourceField' => 'l18n_diffsource',
		'sortby' => 'sorting',
		'delete' => 'deleted',
		'enablecolumns' => array (
			'disabled' => 'hidden',
			'starttime' => 'starttime',
			'endtime' => 'endtime',
			'fe_group' => 'fe_group',
		),
		'dynamicConfigFile' => t3lib_extMgm::extPath($_EXTKEY).'tca.php',
		'iconfile'          => t3lib_extMgm::extRelPath($_EXTKEY).'icon_tx_kequestionnaire_answers.gif',
	),
	"feInterface" => array (
		"fe_admin_fieldList" => "sys_language_uid, l18n_parent, l18n_diffsource, hidden, starttime, endtime, fe_group, question_uid, title, value, correct_answer, text, helptext, image, finish_page_uid, coordtop, coordbottom",
	)
);

$TCA["tx_kequestionnaire_columns"] = array (
	"ctrl" => array (
		'title'     => 'LLL:EXT:ke_questionnaire/locallang_db.xml:tx_kequestionnaire_columns',
		'label'     => 'title',
		'tstamp'    => 'tstamp',
		'crdate'    => 'crdate',
		'cruser_id' => 'cruser_id',
		'versioningWS' => TRUE,
		'origUid' => 't3_origuid',
		'languageField'            => 'sys_language_uid',
		'transOrigPointerField'    => 'l18n_parent',
		'transOrigDiffSourceField' => 'l18n_diffsource',
		'sortby' => 'sorting',
		'delete' => 'deleted',
		'enablecolumns' => array (
			'disabled' => 'hidden',
			'fe_group' => 'fe_group',
		),
		'dynamicConfigFile' => t3lib_extMgm::extPath($_EXTKEY).'tca.php',
		'iconfile'          => t3lib_extMgm::extRelPath($_EXTKEY).'icon_tx_kequestionnaire_columns.gif',
	),
	"feInterface" => array (
		"fe_admin_fieldList" => "sys_language_uid, l18n_parent, l18n_diffsource, hidden, fe_group, question_uid, title, image",
	)
);

$TCA["tx_kequestionnaire_subquestions"] = array (
	"ctrl" => array (
		'title'     => 'LLL:EXT:ke_questionnaire/locallang_db.xml:tx_kequestionnaire_subquestions',
		'label'     => 'title',
		'tstamp'    => 'tstamp',
		'crdate'    => 'crdate',
		'cruser_id' => 'cruser_id',
		'versioningWS' => TRUE,
		'origUid' => 't3_origuid',
		'languageField'            => 'sys_language_uid',
		'transOrigPointerField'    => 'l18n_parent',
		'transOrigDiffSourceField' => 'l18n_diffsource',
                'sortby' => 'sorting',
		'delete' => 'deleted',
		'enablecolumns' => array (
			'disabled' => 'hidden',
		),
		'dynamicConfigFile' => t3lib_extMgm::extPath($_EXTKEY).'tca.php',
		'iconfile'          => t3lib_extMgm::extRelPath($_EXTKEY).'icon_tx_kequestionnaire_subquestions.gif',
	),
	"feInterface" => array (
		"fe_admin_fieldList" => "sys_language_uid, l18n_parent, l18n_diffsource, hidden, question_uid, title_line, title, text, image",
	)
);

$TCA["tx_kequestionnaire_dependancies"] = array (
	"ctrl" => array (
		'title'     => 'LLL:EXT:ke_questionnaire/locallang_db.xml:tx_kequestionnaire_dependancies',
		'label'     => 'uid',
		'tstamp'    => 'tstamp',
		'crdate'    => 'crdate',
		'cruser_id' => 'cruser_id',
                'languageField'            => 'sys_language_uid',
		'transOrigPointerField'    => 'l18n_parent',
		'transOrigDiffSourceField' => 'l18n_diffsource',
		'sortby' => 'sorting',
		'delete' => 'deleted',
		'enablecolumns' => array (
			'disabled' => 'hidden',
			'fe_group' => 'fe_group',
		),
		'dynamicConfigFile' => t3lib_extMgm::extPath($_EXTKEY).'tca.php',
		'iconfile'          => t3lib_extMgm::extRelPath($_EXTKEY).'icon_tx_kequestionnaire_dependancies.gif',
                'requestUpdate'     => "activating_question",
	),
	"feInterface" => array (
		"fe_admin_fieldList" => "sys_language_uid, l18n_parent, l18n_diffsource, dependant_question, activating_question, activating_value",
                //"fe_admin_fieldList" => "sys_language_uid, l18n_parent, l18n_diffsource, hidden, fe_group, dependant_question, activating_question, activating_value",
                //"fe_admin_fieldList" => "sys_language_uid, l18n_parent, l18n_diffsource, activating_question, activating_value",
	)
);

$TCA["tx_kequestionnaire_sublines"] = array (
	"ctrl" => array (
		'title'     => 'LLL:EXT:ke_questionnaire/locallang_db.xml:tx_kequestionnaire_sublines',
		'label'     => 'start',
                'label_alt' => 'end',
                'label_alt_force' => true,
		'tstamp'    => 'tstamp',
		'crdate'    => 'crdate',
		'cruser_id' => 'cruser_id',
		'languageField'            => 'sys_language_uid',
		'transOrigPointerField'    => 'l18n_parent',
		'transOrigDiffSourceField' => 'l18n_diffsource',
		'sortby' => 'sorting',
		'delete' => 'deleted',
		'enablecolumns' => array (
			'disabled' => 'hidden',
		),
		'dynamicConfigFile' => t3lib_extMgm::extPath($_EXTKEY).'tca.php',
		'iconfile'          => t3lib_extMgm::extRelPath($_EXTKEY).'icon_tx_kequestionnaire_sublines.gif',
	),
	"feInterface" => array (
		"fe_admin_fieldList" => "sys_language_uid, l18n_parent, l18n_diffsource, hidden, question_uid, start, end",
	)
);

$TCA["tx_kequestionnaire_outcomes"] = array (
	"ctrl" => array (
		'title'     => 'LLL:EXT:ke_questionnaire/locallang_db.xml:tx_kequestionnaire_outcomes',
		'label'     => 'title',
		'tstamp'    => 'tstamp',
		'crdate'    => 'crdate',
		'cruser_id' => 'cruser_id',
                'type' => 'type',
		'languageField'            => 'sys_language_uid',
		'transOrigPointerField'    => 'l18n_parent',
		'transOrigDiffSourceField' => 'l18n_diffsource',
		'sortby' => 'sorting',
		'delete' => 'deleted',
		'enablecolumns' => array (
			'disabled' => 'hidden',
			'starttime' => 'starttime',
			'endtime' => 'endtime',
			'fe_group' => 'fe_group',
		),
		'dynamicConfigFile' => t3lib_extMgm::extPath($_EXTKEY).'tca.php',
		'iconfile'          => t3lib_extMgm::extRelPath($_EXTKEY).'icon_tx_kequestionnaire_outcomes.gif',
	),
	"feInterface" => array (
		"fe_admin_fieldList" => "sys_language_uid, l18n_parent, l18n_diffsource, hidden, starttime, endtime, fe_group, title, value_start, value_end, text, image",
	)
);

$TCA["tx_kequestionnaire_authcodes"] = array (
	"ctrl" => array (
		'title'     => 'LLL:EXT:ke_questionnaire/locallang_db.xml:tx_kequestionnaire_authcodes',
		'label'     => 'uid',
		'tstamp'    => 'tstamp',
		'crdate'    => 'crdate',
		'cruser_id' => 'cruser_id',
		'languageField'            => 'sys_language_uid',
		'transOrigPointerField'    => 'l18n_parent',
		'transOrigDiffSourceField' => 'l18n_diffsource',
		'default_sortby' => "ORDER BY crdate",
		'delete' => 'deleted',
		'enablecolumns' => array (
			'disabled' => 'hidden',
			'starttime' => 'starttime',
			'endtime' => 'endtime',
			'fe_group' => 'fe_group',
		),
		'dynamicConfigFile' => t3lib_extMgm::extPath($_EXTKEY).'tca.php',
		'iconfile'          => t3lib_extMgm::extRelPath($_EXTKEY).'icon_tx_kequestionnaire_authcodes.gif',
	),
	"feInterface" => array (
		"fe_admin_fieldList" => "sys_language_uid, l18n_parent, l18n_diffsource, hidden, starttime, endtime, fe_group, qpid, authcode, feuser",
	)
);

$TCA["tx_kequestionnaire_results"] = array (
	"ctrl" => array (
		'title'     => 'LLL:EXT:ke_questionnaire/locallang_db.xml:tx_kequestionnaire_results',
		'label'     => 'uid',
		'tstamp'    => 'tstamp',
		'crdate'    => 'crdate',
		'cruser_id' => 'cruser_id',
		'default_sortby' => "ORDER BY crdate",
		'delete' => 'deleted',
		'enablecolumns' => array (
			'disabled' => 'hidden',
			'fe_group' => 'fe_group',
		),
		'dynamicConfigFile' => t3lib_extMgm::extPath($_EXTKEY).'tca.php',
		'iconfile'          => t3lib_extMgm::extRelPath($_EXTKEY).'icon_tx_kequestionnaire_results.gif',
	),
	"feInterface" => array (
		"fe_admin_fieldList" => "sys_language_uid, hidden, fe_group, auth, start, last, finished, data_2a5c43f84b, ip",
	)
);

$TCA["tx_kequestionnaire_history"] = array (
	"ctrl" => array (
		'title'     => 'LLL:EXT:ke_questionnaire/locallang_db.xml:tx_kequestionnaire_history',
		'label'     => 'uid',
		'tstamp'    => 'tstamp',
		'crdate'    => 'crdate',
		'cruser_id' => 'cruser_id',
		'default_sortby' => "ORDER BY crdate",
		'delete' => 'deleted',
		'enablecolumns' => array (
			'disabled' => 'hidden',
			'fe_group' => 'fe_group',
		),
		'dynamicConfigFile' => t3lib_extMgm::extPath($_EXTKEY).'tca.php',
		'iconfile'          => t3lib_extMgm::extRelPath($_EXTKEY).'icon_tx_kequestionnaire_history.gif',
	),
	"feInterface" => array (
		"fe_admin_fieldList" => "sys_language_uid, hidden, fe_group, data_2e86c50d23, history_time, result_id",
	)
);
if (version_compare(TYPO3_branch, '6.1', '<')) {
   t3lib_div::loadTCA('tt_content');
}
t3lib_extMgm::addPlugin(array('LLL:EXT:ke_questionnaire/locallang_db.xml:tt_content.list_type_pi1', $_EXTKEY.'_pi1'),'list_type');
t3lib_extMgm::addStaticFile($_EXTKEY,"/pi1/static/","keq questionnaire");

// #################################################
// KENNZIFFER Nadine Schwingler 03.09.2009
// Flexforms
//$TCA['tt_content']['types']['list']['subtypes_excludelist'][$_EXTKEY.'_pi1']='layout,select_key';
$TCA['tt_content']['types']['list']['subtypes_excludelist'][$_EXTKEY.'_pi1']='layout,select_key,pages';
$TCA['tt_content']['types']['list']['subtypes_addlist'][$_EXTKEY.'_pi1']='pi_flexform';

//to enable the expansion of Flexforms,
//Two Markers are SET: ###ADDED_FFSHEET### and ###TIMER_FF###
$added_FF = '';
$timer_FF = '';
$pi1_flexform = file_get_contents(t3lib_extMgm::extPath($_EXTKEY).'pi1/flexform.xml');
$pi1_flexform = str_replace('###ADDED_FFSHEET###',$added_FF,$pi1_flexform);
$pi1_flexform = str_replace('###TIMER_FF###',$added_FF,$pi1_flexform);
t3lib_extMgm::addPiFlexFormValue($_EXTKEY.'_pi1',$pi1_flexform);
//TODO: Parsing of flexform
// #################################################

if (TYPO3_MODE=="BE")	$TBE_MODULES_EXT["xMOD_db_new_content_el"]["addElClasses"]["tx_kequestionnaire_pi1_wizicon"] = t3lib_extMgm::extPath($_EXTKEY).'pi1/class.tx_kequestionnaire_pi1_wizicon.php';

if (TYPO3_MODE=='BE')   {
	$extPath = t3lib_extMgm::extPath($_EXTKEY);

		// add module before 'Help'
	if (!isset($TBE_MODULES['txkequestionnaireM1']))	{
		$temp_TBE_MODULES = array();
		foreach($TBE_MODULES as $key => $val) {
			if ($key == 'tools') {
				$temp_TBE_MODULES['txkequestionnaireM1'] = '';
				$temp_TBE_MODULES[$key] = $val;
			} else {
				$temp_TBE_MODULES[$key] = $val;
			}
		}

		$TBE_MODULES = $temp_TBE_MODULES;
	}
	t3lib_extMgm::addModule('txkequestionnaireM1','','',t3lib_extMgm::extPath($_EXTKEY).'mod1/');
	t3lib_extMgm::addModule('txkequestionnaireM1','txkequestionnaireM2','',t3lib_extMgm::extPath($_EXTKEY).'mod2/');
	t3lib_extMgm::addModule('txkequestionnaireM1','txkequestionnaireM3','',t3lib_extMgm::extPath($_EXTKEY).'mod3/');
    t3lib_extMgm::addModule('txkequestionnaireM1','txkequestionnaireM4','',t3lib_extMgm::extPath($_EXTKEY).'mod4/');
}


?>
