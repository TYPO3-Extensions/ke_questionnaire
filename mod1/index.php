<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2009 Nadine Schwingler <schwingler@kennziffer.com>
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


	// DEFAULT initialization of a module [BEGIN]
unset($MCONF);
require_once('conf.php');
require_once($BACK_PATH.'init.php');
require_once($BACK_PATH.'template.php');

$LANG->includeLLFile('EXT:ke_questionnaire/mod1/locallang.xml');
require_once(PATH_t3lib.'class.t3lib_scbase.php');
$BE_USER->modAccess($MCONF,1);	// This checks permissions and exits if the users has no permission for entry.
	// DEFAULT initialization of a module [END]

/**
 * Module 'Questionnaire' for the 'ke_questionnaire' extension.
 *
 * @author	Nadine Schwingler <schwingler@kennziffer.com>
 * @package	TYPO3
 * @subpackage	tx_kequestionnaire
 */
/**
 * Class to producing navigation frame of the tx_kequestionnaire extension
 *
 * @package 	TYPO3
 * @subpackage 	tx_kequestionnaire_navframe
 */

/**
 * [CLASS/FUNCTION INDEX of SCRIPT]
 *
 *   62: class tx_kequestionnaire_navframe
 *   68:     function init()
 *  134:     function main()
 *  177:     function printContent()
 *
 * TOTAL FUNCTIONS: 3
 * (This index is automatically created/updated by the extension "extdeveval")
 *
 */
class tx_kequestionnaire_navframe{
	/**
 * first initialization of the global variables. Set some JS-code
 *
 * @return	void		...
 */
	function init()	{
		global $BE_USER,$LANG,$BACK_PATH,$TYPO3_CONF_VARS;

		$this->doc = t3lib_div::makeInstance('template');
		$this->doc->backPath = $BACK_PATH;


		$this->currentSubScript = t3lib_div::_GP('currentSubScript');

			// Setting highlight mode:
		$this->doHighlight = !$BE_USER->getTSConfigVal('options.pageTree.disableTitleHighlight');

		$this->doc->JScode='';

			// Setting JavaScript for menu.
		$this->doc->JScode=$this->doc->wrapScriptTags(
			($this->currentSubScript?'top.currentSubScript=unescape("'.rawurlencode($this->currentSubScript).'");':'').'

			function jumpTo(params,linkObj,highLightID)	{ //
				var theUrl = top.TS.PATH_typo3+top.currentSubScript+"?"+params;

				if (top.condensedMode)	{
					top.content.document.location=theUrl;
				} else {
					parent.list_frame.document.location=theUrl;
				}
				'.($this->doHighlight?'hilight_row("row"+top.fsMod.recentIds["txkequestionnaireM1"],highLightID);':'').'
				'.(!$GLOBALS['CLIENT']['FORMSTYLE'] ? '' : 'if (linkObj) {linkObj.blur();}').'
				return false;
			}


				// Call this function, refresh_nav(), from another script in the backend if you want to refresh the navigation frame (eg. after having changed a page title or moved pages etc.)
				// See t3lib_BEfunc::getSetUpdateSignal()
			function refresh_nav() { //
				window.setTimeout("_refresh_nav();",0);
			}


			function _refresh_nav()	{ //
				document.location="'.htmlspecialchars(t3lib_div::getIndpEnv('SCRIPT_NAME').'?unique='.time()).'";
			}

				// Highlighting rows in the page tree:
			function hilight_row(frameSetModule,highLightID) { //
					// Remove old:
				theObj = document.getElementById(top.fsMod.navFrameHighlightedID[frameSetModule]);
				if (theObj)	{
					theObj.style.backgroundColor="";
				}

					// Set new:
				top.fsMod.navFrameHighlightedID[frameSetModule] = highLightID;
				theObj = document.getElementById(highLightID);
				if (theObj)	{
					theObj.style.backgroundColor="'.t3lib_div::modifyHTMLColorAll($this->doc->bgColor,-5).'";
				}
			}
		');
	}

	/**
	 * Main function, rendering the browsable page tree
	 *
	 * @return	void		...
	 */
	function main()	{
		global $LANG,$BACK_PATH, $TYPO3_DB;
		$this->extConf = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['ke_questionnaire']);

		$this->content = '';
		$this->content.= $this->doc->startPage('Navigation');

		/* from dmail... to orientation for creation of the nav-tree
		 $res = $TYPO3_DB->exec_SELECTquery(
			'*',
			'pages',
			'doktype != 255 AND module in (\'dmail\')'. t3lib_BEfunc::deleteClause('pages')
		);
		$out = '';
		while ($row = $TYPO3_DB->sql_fetch_assoc($res)){
			if(t3lib_BEfunc::readPageAccess($row['uid'],$GLOBALS['BE_USER']->getPagePermsClause(1))){
				$out .= '<tr onmouseover="this.style.backgroundColor=\''.t3lib_div::modifyHTMLColorAll($this->doc->bgColor,-5).'\'" onmouseout="this.style.backgroundColor=\'\'">'.
					'<td id="dmail_'.$row['uid'].'" ><a href="#" onclick="top.fsMod.recentIds[\'txdirectmailM1\']='.$row['uid'].';jumpTo(\'id='.$row['uid'].'\',this,\'dmail_'.$row['uid'].'\');">&nbsp;&nbsp;'.
					t3lib_iconWorks::getIconImage('pages',$row,$BACK_PATH,'title="'.htmlspecialchars(t3lib_BEfunc::getRecordPath($row['uid'], ' 1=1',20)).'" align="top"').
					htmlspecialchars($row['title']).'</a></td></tr>';
			}
		}

		$out = '<table cellspacing="0" cellpadding="0" border="0" width="100%">'.$out.'</table>';
		//$modlist
		$this->content.= $this->doc->section($LANG->getLL('dmail_folders').t3lib_BEfunc::cshItem($this->cshTable,'folders',$BACK_PATH), $out, 1, 1, 0 , TRUE);
		*/
		
		//get the activated plugins
		$res = $TYPO3_DB->exec_SELECTquery(
			'*',
			'tt_content',
			'CType="list" AND list_type="ke_questionnaire_pi1" AND hidden=0 AND deleted=0'
		);
		$out = '';
		if ($res){
			while ($row = $TYPO3_DB->sql_fetch_assoc($res)){
				if(t3lib_BEfunc::readPageAccess($row['pid'],$GLOBALS['BE_USER']->getPagePermsClause(1))){
					$pagy = t3lib_BEfunc::getRecord('pages',$row['pid']);
					$out .= '<tr onmouseover="this.style.backgroundColor=\'';
					$out .= t3lib_div::modifyHTMLColorAll($this->doc->bgColor,-5);
					$out .= '\'" onmouseout="this.style.backgroundColor=\'\'">';
					$out .= '<td style="padding:2px; border:1px #FFFFFF solid" id="ke_questionnaire_'.$row['uid'].'" >';
					$out .= '<a href="#" onclick="top.fsMod.recentIds[\'txkequestionnaireM1\']='.$row['uid'];
					//Add Parameters here, to pass them to the submodule
					$out .= ';jumpTo(\'id='.$pagy['uid'].'&q_id='.$row['uid'];
					$out .= '\',this,\'ke_questionnaire_'.$row['uid'].'\');">';
					$out .= '<img src="../../../../typo3conf/ext/ke_questionnaire/mod2/moduleicon.gif" align="top"/>&nbsp;&nbsp;';
					//t3lib_iconWorks::getIconImage('pages',$row,$BACK_PATH,'title="'.htmlspecialchars(t3lib_BEfunc::getRecordPath($pagy['uid'], ' 1=1',20)).'" align="top"');
					if ($this->extConf['BE_showPageTitle'] == 1){
						$out .= htmlspecialchars($pagy['title']);
					} else {
						$out .= htmlspecialchars($row['header']);
					}
					if ($row['sys_language_uid'] != 0){
						$out .= ' (';
						$lang = t3lib_BEfunc::getRecord('sys_language',$row['sys_language_uid']);
						$out .= $lang['title'];
						$out .= ')';
					}
					$out .= '</a></td></tr>';
					
				}
			}
		}
		$out = '<table cellspacing="0" cellpadding="0" border="0" width="100%">'.$out.'</table>';
		//$modlist
		$this->content.= $this->doc->section($LANG->getLL('questionnaires_label').t3lib_BEfunc::cshItem($this->cshTable,'folders',$BACK_PATH), $out, 1, 1, 0 , TRUE);

		$this->content.= $this->doc->spacer(10);

		$this->content.= '
			<p class="c-refresh">
				<a href="'.htmlspecialchars(t3lib_div::linkThisScript(array('unique' => uniqid('directmail_navframe')))).'">'.
				'<img'.t3lib_iconWorks::skinImg($GLOBALS['BACK_PATH'], 'gfx/refresh_n.gif','width="14" height="14"').' title="'.$LANG->sL('LLL:EXT:lang/locallang_core.xml:labels.refresh',1).'" alt="" />'.
				$LANG->sL('LLL:EXT:lang/locallang_core.xml:labels.refresh',1).'</a>
			</p>
			<br />';

			// Adding highlight - JavaScript
		if ($this->doHighlight)	$this->content .=$this->doc->wrapScriptTags('
			hilight_row("",top.fsMod.navFrameHighlightedID["web"]);
		');
	}

	/**
	 * Outputting the accumulated content to screen
	 *
	 * @return	void
	 */
	function printContent()	{
		$this->content.= $this->doc->endPage();
		echo $this->content;
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/ke_questionnaire/mod1/index.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/ke_questionnaire/mod1/index.php']);
}

// Make instance:
$GLOBALS['SOBE'] = t3lib_div::makeInstance('tx_kequestionnaire_navframe');
$SOBE->init();
$SOBE->main();
$SOBE->printContent()

?>
