<?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');
t3lib_extMgm::addUserTSConfig('
	options.saveDocNew.tx_kequestionnaire_questions=1
');
t3lib_extMgm::addPageTSConfig('

	# ***************************************************************************************
	# CONFIGURATION of RTE in table "tx_kequestionnaire_questions", field "text"
	# ***************************************************************************************
RTE.config.tx_kequestionnaire_questions.text {
  hidePStyleItems = H1, H4, H5, H6
  showButtons = fontsize
  hideButtons = formatblock, indent, outdent, line, chMode, blockstyle, textstyle, strikethrough, subscript, superscript, lefttoright, righttoleft, left, center, right, justifyfull, table, inserttag, findreplace, removeformat, copy, cut, paste
  proc.exitHTMLparser_db=1
  proc.exitHTMLparser_db {
    keepNonMatchedTags=1
    tags.font.allowedAttribs= color
    tags.font.rmTagIfNoAttrib = 1
    tags.font.nesting = global
  }
}
');
t3lib_extMgm::addPageTSConfig('

	# ***************************************************************************************
	# CONFIGURATION of RTE in table "tx_kequestionnaire_questions", field "helptext"
	# ***************************************************************************************
RTE.config.tx_kequestionnaire_questions.helptext {
  hidePStyleItems = H1, H4, H5, H6
  showButtons = fontsize
  hideButtons = formatblock, indent, outdent, line, chMode, blockstyle, textstyle, strikethrough, subscript, superscript, lefttoright, righttoleft, left, center, right, justifyfull, table, inserttag, findreplace, removeformat, copy, cut, paste
  proc.exitHTMLparser_db=1
  proc.exitHTMLparser_db {
    keepNonMatchedTags=1
    tags.font.allowedAttribs= color
    tags.font.rmTagIfNoAttrib = 1
    tags.font.nesting = global
  }
}
');
t3lib_extMgm::addUserTSConfig('
	options.saveDocNew.tx_kequestionnaire_answers=1
');
t3lib_extMgm::addPageTSConfig('

	# ***************************************************************************************
	# CONFIGURATION of RTE in table "tx_kequestionnaire_answers", field "text"
	# ***************************************************************************************
RTE.config.tx_kequestionnaire_answers.text {
  hidePStyleItems = H1, H4, H5, H6
  showButtons = fontsize
  hideButtons = formatblock, indent, outdent, line, chMode, blockstyle, textstyle, strikethrough, subscript, superscript, lefttoright, righttoleft, left, center, right, justifyfull, table, inserttag, findreplace, removeformat, copy, cut, paste
  proc.exitHTMLparser_db=1
  proc.exitHTMLparser_db {
    keepNonMatchedTags=1
    tags.font.allowedAttribs= color
    tags.font.rmTagIfNoAttrib = 1
    tags.font.nesting = global
  }
}
');
t3lib_extMgm::addPageTSConfig('

	# ***************************************************************************************
	# CONFIGURATION of RTE in table "tx_kequestionnaire_answers", field "helptext"
	# ***************************************************************************************
RTE.config.tx_kequestionnaire_answers.helptext {
  hidePStyleItems = H1, H4, H5, H6
  showButtons = fontsize
  hideButtons = formatblock, indent, outdent, line, chMode, blockstyle, textstyle, strikethrough, subscript, superscript, lefttoright, righttoleft, left, center, right, justifyfull, table, inserttag, findreplace, removeformat, copy, cut, paste
  proc.exitHTMLparser_db=1
  proc.exitHTMLparser_db {
    keepNonMatchedTags=1
    tags.font.allowedAttribs= color
    tags.font.rmTagIfNoAttrib = 1
    tags.font.nesting = global
  }
}
');
t3lib_extMgm::addPageTSConfig('

	# ***************************************************************************************
	# CONFIGURATION of RTE in table "tx_kequestionnaire_subquestions", field "text"
	# ***************************************************************************************
RTE.config.tx_kequestionnaire_subquestions.text {
  hidePStyleItems = H1, H4, H5, H6
  showButtons = fontsize
  hideButtons = formatblock, indent, outdent, line, chMode, blockstyle, textstyle, strikethrough, subscript, superscript, lefttoright, righttoleft, left, center, right, justifyfull, table, inserttag, findreplace, removeformat, copy, cut, paste
  proc.exitHTMLparser_db=1
  proc.exitHTMLparser_db {
    keepNonMatchedTags=1
    tags.font.allowedAttribs= color
    tags.font.rmTagIfNoAttrib = 1
    tags.font.nesting = global
  }
}
');
t3lib_extMgm::addPageTSConfig('

	# ***************************************************************************************
	# CONFIGURATION of RTE in table "tx_kequestionnaire_outcomes", field "text"
	# ***************************************************************************************
RTE.config.tx_kequestionnaire_outcomes.text {
  hidePStyleItems = H1, H4, H5, H6
  showButtons = fontsize
  hideButtons = formatblock, indent, outdent, line, chMode, blockstyle, textstyle, strikethrough, subscript, superscript, lefttoright, righttoleft, left, center, right, justifyfull, table, inserttag, findreplace, removeformat, copy, cut, paste
  proc.exitHTMLparser_db=1
  proc.exitHTMLparser_db {
    keepNonMatchedTags=1
    tags.font.allowedAttribs= color
    tags.font.rmTagIfNoAttrib = 1
    tags.font.nesting = global
  }
}
');

  ## Extending TypoScript from static template uid=43 to set up userdefined tag:
t3lib_extMgm::addTypoScript($_EXTKEY,'editorcfg','
	tt_content.CSS_editor.ch.tx_kequestionnaire_pi1 = < plugin.tx_kequestionnaire_pi1.CSS_editor
',43);

t3lib_extMgm::addPItoST43($_EXTKEY,'pi1/class.tx_kequestionnaire_pi1.php','_pi1','list_type',0);

//Add Scheduler Support
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['scheduler']['tasks']['tx_kequestionnaire_scheduler'] = array(
	'extension' => $_EXTKEY, // Selbsterklärend
	'title' => 'LLL:EXT:'.$_EXTKEY.'/locallang_db.xml:schedulerTask.name', // Der Titel der Aufgabe
	'description' => 'LLL:EXT:'.$_EXTKEY.'/locallang_db.xml:schedulerTask.description', // Die Beschreibung der Aufgabe
	//'additionalFields' => 'tx_kersssimulatettnews_scheduleradd' // Zusätzliche Felder
);
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['scheduler']['tasks']['tx_kequestionnaire_scheduler_export'] = array(
	'extension' => $_EXTKEY, // Selbsterklärend
	'title' => 'LLL:EXT:'.$_EXTKEY.'/locallang_db.xml:schedulerExportTask.name', // Der Titel der Aufgabe
	'description' => 'LLL:EXT:'.$_EXTKEY.'/locallang_db.xml:schedulerExportTask.description', // Die Beschreibung der Aufgabe
	//'additionalFields' => 'tx_kersssimulatettnews_scheduleradd' // Zusätzliche Felder
);

//Ajax support for BE-Module
$TYPO3_CONF_VARS['BE']['AJAX']['tx_kequestionnaire::csv_createDataFile'] = 'EXT:ke_questionnaire/mod3/ajax.php:tx_kequestionnaire_module3_ajax->ajaxCreateDataFile';

?>
