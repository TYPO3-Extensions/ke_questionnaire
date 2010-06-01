<?php

########################################################################
# Extension Manager/Repository config file for ext "ke_questionnaire".
#
# Auto generated 30-05-2010 11:25
#
# Manual updates:
# Only the data in the array - everything else is removed by next
# writing. "version" and "dependencies" must not be touched!
########################################################################

$EM_CONF[$_EXTKEY] = array(
	'title' => 'Questionnaire',
	'description' => 'Easily creating any type of questionnaire, survey, poll, quiz or eLearning. The results may at any time evaluated and displayed graphically. See further information - also about the premium version - at www.ke-questionnaire.de',
	'category' => 'fe',
	'author' => 'Nadine Schwingler (kennziffer.com)',
	'author_email' => 'schwingler@kennziffer.com',
	'shy' => '',
	'dependencies' => 'cms,xajax',
	'conflicts' => '',
	'priority' => '',
	'module' => 'mod1,mod2,mod3,mod4',
	'state' => 'stable',
	'internal' => '',
	'uploadfolder' => 1,
	'createDirs' => 'uploads/tx_kequestionnaire/rte/',
	'modify_tables' => '',
	'clearCacheOnLoad' => 0,
	'lockType' => '',
	'author_company' => 'kennziffer.com',
	'version' => '2.2.3',
	'constraints' => array(
		'depends' => array(
			'cms' => '',
			'xajax' => '0.2.5',
		),
		'conflicts' => array(
		),
		'suggests' => array(
		),
	),
	'suggests' => array(
	),
	'_md5_values_when_last_written' => 'a:149:{s:9:"ChangeLog";s:4:"575a";s:10:"README.txt";s:4:"ee2d";s:21:"ext_conf_template.txt";s:4:"e55b";s:12:"ext_icon.gif";s:4:"3b96";s:17:"ext_localconf.php";s:4:"298f";s:14:"ext_tables.php";s:4:"4d3e";s:14:"ext_tables.sql";s:4:"1a1c";s:28:"ext_typoscript_constants.txt";s:4:"2de0";s:24:"ext_typoscript_setup.txt";s:4:"6fea";s:35:"icon_tx_kequestionnaire_answers.gif";s:4:"d2db";s:37:"icon_tx_kequestionnaire_authcodes.gif";s:4:"6470";s:35:"icon_tx_kequestionnaire_columns.gif";s:4:"a325";s:40:"icon_tx_kequestionnaire_dependancies.gif";s:4:"da1b";s:35:"icon_tx_kequestionnaire_history.gif";s:4:"a9b7";s:36:"icon_tx_kequestionnaire_outcomes.gif";s:4:"b4cd";s:37:"icon_tx_kequestionnaire_questions.gif";s:4:"3b96";s:35:"icon_tx_kequestionnaire_results.gif";s:4:"a9b7";s:36:"icon_tx_kequestionnaire_sublines.gif";s:4:"98b5";s:40:"icon_tx_kequestionnaire_subquestions.gif";s:4:"98b5";s:13:"locallang.xml";s:4:"6700";s:26:"locallang_csh_flexform.xml";s:4:"7595";s:16:"locallang_db.xml";s:4:"3780";s:7:"tca.php";s:4:"99ea";s:14:"doc/manual.sxw";s:4:"fb37";s:19:"doc/wizard_form.dat";s:4:"dd17";s:20:"doc/wizard_form.html";s:4:"082d";s:14:"mod1/clear.gif";s:4:"cc11";s:13:"mod1/conf.php";s:4:"f553";s:14:"mod1/index.php";s:4:"99d3";s:18:"mod1/locallang.xml";s:4:"45bc";s:22:"mod1/locallang_mod.xml";s:4:"ea2f";s:19:"mod1/moduleicon.gif";s:4:"3b96";s:14:"mod2/clear.gif";s:4:"cc11";s:13:"mod2/conf.php";s:4:"8e48";s:14:"mod2/index.php";s:4:"124e";s:18:"mod2/locallang.xml";s:4:"8960";s:22:"mod2/locallang_mod.xml";s:4:"2ecd";s:19:"mod2/moduleicon.gif";s:4:"3b96";s:22:"mod2/res/OF_basic.html";s:4:"2604";s:26:"mod2/res/OF_questions.html";s:4:"cf01";s:19:"mod2/res/basic.html";s:4:"44df";s:23:"mod2/res/questions.html";s:4:"7c30";s:14:"mod3/clear.gif";s:4:"cc11";s:13:"mod3/conf.php";s:4:"8be7";s:14:"mod3/index.php";s:4:"6d44";s:18:"mod3/locallang.xml";s:4:"ec8a";s:22:"mod3/locallang_mod.xml";s:4:"97f9";s:19:"mod3/moduleicon.gif";s:4:"3b96";s:14:"mod4/clear.gif";s:4:"cc11";s:13:"mod4/conf.php";s:4:"30e1";s:14:"mod4/index.php";s:4:"05bb";s:18:"mod4/locallang.xml";s:4:"0950";s:22:"mod4/locallang_mod.xml";s:4:"151f";s:19:"mod4/moduleicon.gif";s:4:"3b96";s:14:"pi1/ce_wiz.gif";s:4:"02b6";s:36:"pi1/class.tx_kequestionnaire_pi1.php";s:4:"d971";s:44:"pi1/class.tx_kequestionnaire_pi1_wizicon.php";s:4:"77c7";s:13:"pi1/clear.gif";s:4:"cc11";s:16:"pi1/flexform.xml";s:4:"baa7";s:17:"pi1/locallang.xml";s:4:"cfcf";s:24:"pi1/static/editorcfg.txt";s:4:"2c52";s:20:"pi1/static/setup.txt";s:4:"dbd1";s:20:"res/cert_example.pdf";s:4:"1b4e";s:84:"res/class.tx_kequestionnaire_tx_kequestionnaire_dependancies_activating_question.php";s:4:"debc";s:81:"res/class.tx_kequestionnaire_tx_kequestionnaire_dependancies_activating_value.php";s:4:"94ac";s:73:"res/class.tx_kequestionnaire_tx_kequestionnaire_questions_closed_type.php";s:4:"ad1f";s:80:"res/class.tx_kequestionnaire_tx_kequestionnaire_questions_demographic_fields.php";s:4:"2459";s:73:"res/class.tx_kequestionnaire_tx_kequestionnaire_questions_matrix_type.php";s:4:"44e4";s:79:"res/class.tx_kequestionnaire_tx_kequestionnaire_questions_matrix_validation.php";s:4:"0381";s:71:"res/class.tx_kequestionnaire_tx_kequestionnaire_questions_open_type.php";s:4:"75b9";s:77:"res/class.tx_kequestionnaire_tx_kequestionnaire_questions_open_validation.php";s:4:"27d0";s:66:"res/class.tx_kequestionnaire_tx_kequestionnaire_questions_type.php";s:4:"9d6b";s:60:"res/class.tx_kequestionnaire_tx_kequestionnaire_redirect.php";s:4:"c234";s:25:"res/images/helpbubble.gif";s:4:"7e7e";s:29:"res/images/keq_arrow_icon.gif";s:4:"4146";s:30:"res/images/keq_arrow_icon2.gif";s:4:"6e0d";s:29:"res/images/keq_list_icon1.gif";s:4:"59a6";s:29:"res/images/keq_list_icon2.gif";s:4:"742e";s:29:"res/images/keq_list_icon3.gif";s:4:"a02a";s:28:"res/images/keq_watchtime.jpg";s:4:"1d08";s:30:"res/other/class.js_raphael.php";s:4:"936b";s:30:"res/other/class.pdf_export.php";s:4:"8601";s:31:"res/other/html2fpdf/credits.txt";s:4:"bc62";s:28:"res/other/html2fpdf/fpdf.php";s:4:"0fa4";s:27:"res/other/html2fpdf/gif.php";s:4:"8100";s:33:"res/other/html2fpdf/html2fpdf.php";s:4:"476f";s:35:"res/other/html2fpdf/htmltoolkit.php";s:4:"71b1";s:31:"res/other/html2fpdf/license.txt";s:4:"3a35";s:30:"res/other/html2fpdf/no_img.gif";s:4:"de06";s:34:"res/other/html2fpdf/source2doc.php";s:4:"e130";s:36:"res/other/html2fpdf/font/courier.php";s:4:"fc24";s:38:"res/other/html2fpdf/font/helvetica.php";s:4:"18a8";s:39:"res/other/html2fpdf/font/helveticab.php";s:4:"5363";s:40:"res/other/html2fpdf/font/helveticabi.php";s:4:"8eba";s:39:"res/other/html2fpdf/font/helveticai.php";s:4:"54e8";s:35:"res/other/html2fpdf/font/symbol.php";s:4:"56b0";s:34:"res/other/html2fpdf/font/times.php";s:4:"bbf9";s:35:"res/other/html2fpdf/font/timesb.php";s:4:"6704";s:36:"res/other/html2fpdf/font/timesbi.php";s:4:"7295";s:35:"res/other/html2fpdf/font/timesi.php";s:4:"4ff5";s:41:"res/other/html2fpdf/font/zapfdingbats.php";s:4:"0529";s:44:"res/other/html2fpdf/font/makefont/cp1250.map";s:4:"8a02";s:44:"res/other/html2fpdf/font/makefont/cp1251.map";s:4:"ee2f";s:44:"res/other/html2fpdf/font/makefont/cp1252.map";s:4:"8d73";s:44:"res/other/html2fpdf/font/makefont/cp1253.map";s:4:"9073";s:44:"res/other/html2fpdf/font/makefont/cp1254.map";s:4:"46e4";s:44:"res/other/html2fpdf/font/makefont/cp1255.map";s:4:"c469";s:44:"res/other/html2fpdf/font/makefont/cp1257.map";s:4:"fe87";s:44:"res/other/html2fpdf/font/makefont/cp1258.map";s:4:"86a4";s:43:"res/other/html2fpdf/font/makefont/cp874.map";s:4:"4fba";s:48:"res/other/html2fpdf/font/makefont/iso-8859-1.map";s:4:"53bf";s:49:"res/other/html2fpdf/font/makefont/iso-8859-11.map";s:4:"83ec";s:49:"res/other/html2fpdf/font/makefont/iso-8859-15.map";s:4:"3d09";s:49:"res/other/html2fpdf/font/makefont/iso-8859-16.map";s:4:"b56b";s:48:"res/other/html2fpdf/font/makefont/iso-8859-2.map";s:4:"4750";s:48:"res/other/html2fpdf/font/makefont/iso-8859-4.map";s:4:"0355";s:48:"res/other/html2fpdf/font/makefont/iso-8859-5.map";s:4:"82a2";s:48:"res/other/html2fpdf/font/makefont/iso-8859-7.map";s:4:"d071";s:48:"res/other/html2fpdf/font/makefont/iso-8859-9.map";s:4:"8647";s:44:"res/other/html2fpdf/font/makefont/koi8-r.map";s:4:"04f5";s:44:"res/other/html2fpdf/font/makefont/koi8-u.map";s:4:"9046";s:46:"res/other/html2fpdf/font/makefont/makefont.php";s:4:"934c";s:30:"res/other/raphael/g.bar-min.js";s:4:"25e1";s:26:"res/other/raphael/g.bar.js";s:4:"e167";s:30:"res/other/raphael/g.dot-min.js";s:4:"a6b1";s:31:"res/other/raphael/g.line-min.js";s:4:"6643";s:30:"res/other/raphael/g.pie-min.js";s:4:"c930";s:34:"res/other/raphael/g.raphael-min.js";s:4:"b141";s:28:"res/other/raphael/raphael.js";s:4:"4f73";s:45:"res/questions/class.kequestionnaire_input.php";s:4:"2551";s:32:"res/questions/class.question.php";s:4:"cef5";s:38:"res/questions/class.question_blind.php";s:4:"2ae4";s:39:"res/questions/class.question_closed.php";s:4:"08b3";s:44:"res/questions/class.question_demographic.php";s:4:"3979";s:39:"res/questions/class.question_matrix.php";s:4:"8a05";s:37:"res/questions/class.question_open.php";s:4:"6fa3";s:40:"res/questions/class.question_privacy.php";s:4:"2042";s:41:"res/questions/class.question_semantic.php";s:4:"fdea";s:26:"res/templates/helpbox.html";s:4:"d488";s:33:"res/templates/question_blind.html";s:4:"0ffe";s:34:"res/templates/question_closed.html";s:4:"6fec";s:39:"res/templates/question_demographic.html";s:4:"5acf";s:35:"res/templates/question_ematrix.html";s:4:"b365";s:34:"res/templates/question_matrix.html";s:4:"0009";s:32:"res/templates/question_open.html";s:4:"a1ef";s:35:"res/templates/question_privacy.html";s:4:"eeac";s:36:"res/templates/question_semantic.html";s:4:"2d9e";s:32:"res/templates/questionnaire.html";s:4:"ea85";s:24:"res/templates/styles.css";s:4:"eb92";}',
);

?>