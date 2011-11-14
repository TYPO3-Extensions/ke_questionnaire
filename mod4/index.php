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

$LANG->includeLLFile('EXT:ke_questionnaire/mod4/locallang.xml');
require_once(PATH_t3lib.'class.t3lib_scbase.php');
$BE_USER->modAccess($MCONF,1);	// This checks permissions and exits if the users has no permission for entry.
	// DEFAULT initialization of a module [END]



/**
 * Module 'Invite' for the 'ke_questionnaire' extension.
 *
 * @author	Nadine Schwingler <schwingler@kennziffer.com>
 * @package	TYPO3
 * @subpackage	tx_kequestionnaire
 */
class  tx_kequestionnaire_module4 extends t3lib_SCbase {
				var $pageinfo;
				var $error;
				var $errorMsg;
				var $minAuthCodeLength=6;
				/**
				 * Initializes the Module
				 * @return	void
				 */
				function init()	{
					global $BE_USER,$LANG,$BACK_PATH,$TCA_DESCR,$TCA,$CLIENT,$TYPO3_CONF_VARS;

					parent::init();
					session_start();

					//get the given Parameters
					$this->q_id = intval(t3lib_div::_GP('q_id'));
					$this->pid = intval(t3lib_div::_GP('id'));

					$this->vars=isset($_REQUEST["vars"])?t3lib_div::_GP("vars"):array();
					$this->step=isset($this->vars["step"])?$this->vars["step"]:1;

					//t3lib_div::devLog('vars', 'Einlade-Mod', 0, $this->vars);
					//t3lib_div::devLog('vars', 'Einlade-Mod', 0, $_REQUEST["vars"]);

					if ($this->q_id == 0 AND $this->vars['q_id']) $this->q_id = $this->vars['q_id'];
					if ($this->pid == 0 AND $this->vars['id']) $this->pid = $this->vars['id'];

					if ($this->q_id > 0){
						$this->q_data = t3lib_BEfunc::getRecord('tt_content',$this->q_id);
						$ff_data = t3lib_div::xml2array($this->q_data['pi_flexform']);
						$this->ff_data = $ff_data['data'];
						$this->storagePid = $this->ff_data['sDEF']['lDEF']['storage_pid']['vDEF'];

						/*$_SESSION["tx_kequestionnaire_module4"]["storagePid"]=$this->storagePid;
						$_SESSION["tx_kequestionnaire_module4"]["q_id"]=$this->q_id;
						$_SESSION["tx_kequestionnaire_module4"]["pid"]=$this->pid;
						$_SESSION["tx_kequestionnaire_module4"]["ff_data"]=$this->ff_data;*/
					}/*elseif(isset($_SESSION["tx_kequestionnaire_module4"]["q_id"])){
						$this->q_id = $_SESSION["tx_kequestionnaire_module4"]["q_id"];
						$this->pid = $_SESSION["tx_kequestionnaire_module4"]["pid"];
						$this->ff_data = $_SESSION["tx_kequestionnaire_module4"]["ff_data"];
						$this->storagePid = $_SESSION["tx_kequestionnaire_module4"]["storagePid"];
					}*/



					$this->maxAuthCodeLength=0;

					$this->extConf = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']["ke_questionnaire"]);
					if(isset($this->extConf["maxAuthCodeLength"]) && is_numeric($this->extConf["maxAuthCodeLength"]) && intval($this->extConf["maxAuthCodeLength"])>=0){
						$this->maxAuthCodeLength=intval($this->extConf["maxAuthCodeLength"]);
						if($this->maxAuthCodeLength < $this->minAuthCodeLength) $this->maxAuthCodeLength=$this->minAuthCodeLength;
					}


				}

				/**
				 * Adds items to the ->MOD_MENU array. Used for the function menu selector.
				 *
				 * @return	void
				 */
				function menuConfig()	{
					global $LANG;
					$this->MOD_MENU = Array (
						'function' => Array (
							'1' => $LANG->getLL('function1'),
							'2' => $LANG->getLL('function2'),
							'3' => $LANG->getLL('function3'),
						)
					);
					parent::menuConfig();
				}

				/**
				 * Main function of the module. Write the content to $this->content
				 * If you chose "web" as main module, you will need to consider the $this->id parameter which will contain the uid-number of the page clicked in the page tree
				 *
				 * @return	[type]		...
				 */
				function main()	{
					global $BE_USER,$LANG,$BACK_PATH,$TCA_DESCR,$TCA,$CLIENT,$TYPO3_CONF_VARS;

					// Access check!
					// The page will show only if there is a valid page and if this page may be viewed by the user
					$this->pageinfo = t3lib_BEfunc::readPageAccess($this->id,$this->perms_clause);
					$access = is_array($this->pageinfo) ? 1 : 0;

					//if (($this->id && $access) || ($BE_USER->user['admin'] && !$this->id))	{

							// Draw the header.
						$this->doc = t3lib_div::makeInstance('mediumDoc');
						$this->doc->backPath = $BACK_PATH;
						$this->doc->form='<form action="" method="POST">';

							// JavaScript
						$this->doc->JScode = '
							<script language="javascript" type="text/javascript">
								script_ended = 0;
								function jumpToUrl(URL)	{
									document.location = URL;
								}
							</script>
						';
						$this->doc->postCode='
							<script language="javascript" type="text/javascript">
								script_ended = 1;
								if (top.fsMod) top.fsMod.recentIds["web"] = 0;
							</script>
						';

						$headerSection = $this->doc->getHeader('pages',$this->pageinfo,$this->pageinfo['_thePath']).'<br />'.$LANG->sL('LLL:EXT:lang/locallang_core.xml:labels.path').': '.t3lib_div::fixed_lgd_cs($this->pageinfo['_thePath'],50);

						$this->content.=$this->doc->startPage($LANG->getLL('title'));
						$this->content.=$this->doc->header($LANG->getLL('title'));
						$this->content.=$this->doc->spacer(5);
						// #################################################
						// KENNZIFFER Nadine Schwingler 23.10.2009
						// Changing the Menu, to pass the q_id-Parameter to the selection
						$func_array = array();
						$func_array['id'] = $this->id;
						$func_array['q_id'] = $this->q_id;
						$this->content.=$this->doc->section('',$this->doc->funcMenu($headerSection,t3lib_BEfunc::getFuncMenu($func_array,'SET[function]',$this->MOD_SETTINGS['function'],$this->MOD_MENU['function'])));
						// #################################################
						//$this->content.=$this->doc->section('',$this->doc->funcMenu($headerSection,t3lib_BEfunc::getFuncMenu($this->id,'SET[function]',$this->MOD_SETTINGS['function'],$this->MOD_MENU['function'])));
						$this->content.=$this->doc->divider(5);


						// Render content:
						$this->moduleContent();


						// ShortCut
						if ($BE_USER->mayMakeShortcut())	{
							$this->content.=$this->doc->spacer(20).$this->doc->section('',$this->doc->makeShortcutIcon('id',implode(',',array_keys($this->MOD_MENU)),$this->MCONF['name']));
						}

						$this->content.=$this->doc->spacer(10);
					/*} else {
							// If no access or if ID == zero

						$this->doc = t3lib_div::makeInstance('mediumDoc');
						$this->doc->backPath = $BACK_PATH;

						$this->content.=$this->doc->startPage($LANG->getLL('title'));
						$this->content.=$this->doc->header($LANG->getLL('title'));
						$this->content.=$this->doc->spacer(5);
						$this->content.=$this->doc->spacer(10);
					}*/
				}

				/**
				 * Prints out the module HTML
				 *
				 * @return	void
				 */
				function printContent()	{

					$this->content.=$this->doc->endPage();
					echo $this->content;
				}

				/**
				 * Generates the module content
				 *
				 * @return	void
				 */
				function moduleContent(){
					/*if ($this->q_id == 0){
						$content=$this->LL("none_selected","none_selected");
						$label=$this->LL("none_selected","none_selected");
					} else {*/
						// If $function changed we have to reset the step
						$function=$this->q_id == 0?0:$this->MOD_SETTINGS['function'];

						switch($function)	{
							case 0:
								$content=$this->LL("none_selected","none_selected");
								$label=$this->LL("none_selected","none_selected");
							break;
							case 1:
							switch($this->step){
								case 1:
									$content=$this->selectSourceForm();
									$label=$this->LL("invite","Invite");
								break;
								case 2:
									$content=$this->showAuthCodes($_SESSION["tx_kequestionnaire_module4"]["user"]);
									$label=$this->LL("authcodes","Authcodes");
								break;
								case 3:
									$content=$this->generateAuthCodes($_SESSION["tx_kequestionnaire_module4"]["user"]);
									$label=$this->LL("generatedAuthcodes","Authcodes");
								break;
							}
							break;
							case 2:
								$content=$this->showDownloadForm();
								$label=$this->LL("function2","Download Authcodes");
							break;
							case 3:
								$content=$this->showRemind();
								$label=$this->LL("function3","Remind");
							break;
						}
					//}

					$this->content.=$this->doc->section($label.':',$content,0,1);

				}
				function showRemind(){
					$users=$this->getUsersToInvite();
					$send=isset($this->vars["submit"]);
					if($send){
						$mailTexts=$this->getMailtexts("remind");
						if($this->error) return $this->LL($this->error);
					}

					$out="";

					$out.=$this->formLabel("remind_users");
					foreach($users as $user){
						if($send){
							$markerArray = array();
							if ($this->extConf['sendMailWithFeUserData']){
								$fe_user_data = t3lib_BEfunc::getRecord('fe_users',$user['feuser']);
								foreach ($fe_user_data as $key => $value){
									$markerArray['###'.strtoupper($key).'###'] = $value;
								}
							}

							$success=$this->sendMail($user["email"],$user["authcode"],$mailTexts, $markerArray);
							if(!$success) return $this->LL("error_send").$this->formLink(array("step"=>1),"back");;
						}else{
							$out.="<div>".$user["email"]."</div>";
						}
						// $out.=$this->sendMail($user["email"],$user["authcode"],"remind");
					}

					$out.="<br />";
					$out.=$send?$this->LL("success_send"):$this->formSubmitButton("submit","submit");
					$out.=$this->formLink(array("step"=>1),"back");


					return $out;

				}
				function getMailtexts($type){
					$fieldBody=$type=="remind"?"remind_mail_text":"invite_mail_text";
					$fieldSubject=$type=="remind"?"remind_mail_subject":"invite_mail_subject";


					$out["body"] = trim($this->ff_data['mDEF']['lDEF'][$fieldBody]['vDEF']);
					$out["subject"]= trim($this->ff_data['mDEF']['lDEF'][$fieldSubject]['vDEF']);
					$out["fromName"]= trim($this->ff_data['mDEF']['lDEF']["mail_from"]['vDEF']);
					$out["fromEmail"]= trim($this->ff_data['mDEF']['lDEF']["mail_sender"]['vDEF']);


					$this->error=0;
					if($out["body"]=="") $this->error="error_missing_text";
					elseif(substr_count($out["body"],"###LINK###") == 0 && substr_count($out["body"], "###AUTHCODE###") == 0) $this->error="error_missing_marker";
					if($out["subject"]=="") $this->error="error_missing_subject";
					if($out["fromName"]=="" || $out["fromEmail"]=="") $this->error="error_missing_sender";

					return $out;

				}
				function sendMail($email,$authcode,$mailTexts,$markerArray=array()){
					//t3lib_div::devLog('sendMail markerArray', 'Einlade-Mod', 0, $markerArray);
					if (is_array($markerArray['###PASSWORD'])){
						unset($markerArray['###PID###']);
						unset($markerArray['###PASSWORD###']);
					}
					//t3lib_div::devLog('sendMail markerArray', 'Einlade-Mod', 0, $markerArray);
					$link = t3lib_div::getIndpEnv('TYPO3_SITE_URL') . 'index.php?id=' . $this->pid . '&tx_kequestionnaire_pi1[auth_code]=' . $authcode;
					$html_link = '<a href="' . $link . '">' . $link . '</a>';

					$markerArray["###AUTHCODE###"] = $authcode;
					$markerArray["###LINK###"] = '<' . $link . '>';
					$markerArray["###ID###"] = $this->pid;
					$markerArray["###URL###"] = $link;

					$body=$mailTexts["body"];
					$html_body=$mailTexts["body"];
					foreach($markerArray as $key=>$val) $body=str_replace($key,$val,$body);
					$markerArray["###LINK###"]=$html_link;
					foreach($markerArray as $key=>$val) $html_body=str_replace($key,$val,$html_body);

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
					//$this->htmlMail->addPlain($body);
					$this->htmlMail->setHTML($this->htmlMail->encodeMsg($html_start.$html_body.$html_end));
					$out=$this->htmlMail->send($email);

					return $out;

				}
				function getUsersToInvite(){
					$out=array();
					$res_auth=$GLOBALS["TYPO3_DB"]->exec_SELECTgetRows("uid,email,feuser,authcode", "tx_kequestionnaire_authcodes", "qpid=".$this->storagePid." AND deleted=0 AND email<>''", "", "", "", "");
					//t3lib_div::devLog('getUsersToInvite res_auth', 'Einlade-Mod', 0, $res_auth);
					foreach($res_auth as $row){
						$res_result=$GLOBALS["TYPO3_DB"]->exec_SELECTgetRows("*", "tx_kequestionnaire_results", "auth=".$row["uid"], "", "", "", "");
						//t3lib_div::devLog('getUsersToInvite res', 'Einlade-Mod', 0, array("*", "tx_kequestionnaire_results", "auth=".$row["uid"], "", "", "", ""));
						//t3lib_div::devLog('getUsersToInvite res_result', 'Einlade-Mod', 0, $res_result);
						if($res_result) continue;
						$out[]=$row;
					}
					return $out;
				}

				function showDownloadForm(){
					$this->error=0;
					$numberOfAuthcodes=isset($this->vars["numberOfAuthcodes"])?$this->vars["numberOfAuthcodes"]:0;
					if($numberOfAuthcodes){
						if(!is_numeric($numberOfAuthcodes)  || intval($numberOfAuthcodes)!=$numberOfAuthcodes) $this->error=1;
					}
					if($numberOfAuthcodes && !$this->error){
						// generate CSV
						$this->generateCSV($numberOfAuthcodes);
						return;
					}

					$out.=$this->formLabel("numberOfAuthcodes");
					if($this->error) $out.=$this->formLabel("numberOfAuthcodesError",true);
					$out.=$this->formInput("numberOfAuthcodes",isset($this->vars["numberOfAuthcodes"])?$this->vars["numberOfAuthcodes"]:"");
					$out.=$this->formSubmitButton("submit","submit");

					return $out;
				}
				function generateCSV($numberOfAuthcodes){
					$authcodes=array();

					for($i=1;$i<=$numberOfAuthcodes;$i++){
						$authcodes[]=$this->generateSingleCode();
					}

					$csvdata=implode("\n",$authcodes);
					$filename=$this->q_id."_csv_authcodes.csv";
					header("content-type: application/csv-tab-delimited-table");
					header("content-length: ".strlen($csvdata));
					header("content-disposition: attachment; filename=$filename");
					print $csvdata;
					exit();
				}
				function selectAuthcodes(){
					$out=$GLOBALS["TYPO3_DB"]->exec_SELECTgetRows("email,authcode,qpid,fe_group,feuser", "tx_kequestionnaire_authcodes", "qpid=".$this->storagePid, "", "", "", "");
					return $out;
				}
				function generateAuthCodes($users){

					$mailTexts=$this->getMailtexts("invite");
					if($this->error) return $this->LL($this->error);


					$out.=$this->formLabel("generatedAuthcodes");


					$out.="<table border=0>";

					foreach ($users as $key => $value) {
						$email=trim($value["email"]);
						$feuser=$value["feUserUid"];
						$markerArray = array();
						if ($this->extConf['sendMailWithFeUserData']){
							$fe_user_data = t3lib_BEfunc::getRecord('fe_users',$feuser);
							foreach ($fe_user_data as $key => $value){
								$markerArray['###'.strtoupper($key).'###'] = $value;
							}
						}
						$authCode=$this->generateSingleCode($email,$feuser);
						if(!$authCode) continue;
						$success=$this->sendMail($email, $authCode, $mailTexts, $markerArray);
						if(!$success) return $this->LL("error_send");


						$out.="<tr><td>$email</td><td>$authCode</td></tr>";
					}


					$out.="</table>";
					$out.=$this->LL("success_generated");

					// $out.=$this->formLink(array("step"=>$this->step,"source"=>""),"back");

					$out.=$this->formLink(array("step"=>1,"source"=>""),"back");


					return $out;





				}
				function generateSingleCode($email=null,$feuser=null){
					if(!$this->storagePid) return false;

					// Update usergroup for existing user, if neccessary
					$where="email='$email' AND qpid=".$this->storagePid;

					if($feuser) $GLOBALS["TYPO3_DB"]->exec_UPDATEquery("tx_kequestionnaire_authcodes", $where, array("feuser"=>$feuser));
					$where .= ' AND deleted=0';

					// Check if authcode already exists
					$res=$GLOBALS["TYPO3_DB"]->exec_SELECTgetRows("*", "tx_kequestionnaire_authcodes", $where, "", "", "", "");
					if($res && $email) {
						//t3lib_div::devLog('res', 'Einlade-Mod', 0, $res);
						//return $res[0]['authcode'];
						return false;
					}

					$insertArr=array(
						"qpid"=>$this->storagePid,
						"pid"=>$this->storagePid,
						"feuser"=>$feuser,
						"email"=>$email,
					);

					$GLOBALS["TYPO3_DB"]->exec_INSERTquery("tx_kequestionnaire_authcodes", $insertArr);
					$uid=$GLOBALS["TYPO3_DB"]->sql_insert_id();

					// Generate authcode and write it to database
					$loop=1;
					while($loop){
						$out=md5(time().$uid."_".$this->storagePid."_".$TYPO3_CONF_VARS['SYS']['encryptionKey']);

						if($this->maxAuthCodeLength && $this->maxAuthCodeLength < strlen($out)) $out=substr($out,0,$this->maxAuthCodeLength);
						$where="authcode='$out'";


						$res=$GLOBALS["TYPO3_DB"]->exec_SELECTgetRows("*", "tx_kequestionnaire_authcodes", $where, "", "", "", "");

						if(empty($res)) $loop=0;

					}

					$GLOBALS["TYPO3_DB"]->exec_UPDATEquery("tx_kequestionnaire_authcodes", "uid=$uid", array("authcode"=>$out));



					return $out;
				}
				function showAuthCodes($users){
					$this->vars["step"]=3;
					$out=$this->generateHiddenFields(array("step","q_id","id"));


					$out.=$this->formLabel("authcode");

					$out.="<table border=0>";

					foreach ($users as $row) {
						$email=$row["email"];
						$out.="<tr><td>$email</td></tr>";
					}
					$out.="</table>";

					// $out.=$this->formLink(array("step"=>$this->step,"source"=>""),"back");
					$out.=$this->formSubmitButton("generateCodes","generateCodes");

					$out.=$this->formLink(array("step"=>$this->step-1,"source"=>""),"back");


					return $out;
				}
				function selectSourceForm(){
					$out=$this->generateHiddenFields(array("source","step","q_id","id"));
					$source=isset($this->vars["source"])?$this->vars["source"]:"";

					$submit=isset($this->vars["submit"]);
					if($submit){
						$user=$this->getMails($source);

						if(!$this->error){
							$url="index.php?vars[step]=2&vars[source]=$source&vars[q_id]=".$this->q_id."&vars[id]=".$this->pid;
							$_SESSION["tx_kequestionnaire_module4"]["user"]=$user;
							$_SESSION["tx_kequestionnaire_module4"]["source"]=$source;
							$_SESSION["tx_kequestionnaire_module4"]["usergroup"]=$source=="fe_users_group"?$this->vars["usergroup"]:0;
							Header("Location:$url");
							exit();
						}
					}

					switch($source){
						case "input":
							$out.=$this->formLabel("sourceInput");
							if($this->error) $out.=$this->formLabel($this->errorMsg,true);
							$out.=$this->formTextArea("addresses",isset($this->vars["addresses"])?$this->vars["addresses"]:"");
							$out.=$this->formSubmitButton("submit","submit");
							$out.=$this->formLink(array("step"=>$this->step,"source"=>""),"back");

						break;
						case "fe_users_group":
							$usergroups=$this->getUsergroups();
							$out.=$this->formLabel("sourceUserGroup");
							$out.=$this->formDropdown("usergroup",$usergroups,isset($this->vars["usergroup"])?$this->vars["usergroup"]:"");
							$out.=$this->formSubmitButton("submit","submit");
							$out.=$this->formLink(array("step"=>$this->step,"source"=>""),"back");
						break;
						case "fe_users":
						case "tt_address":
							$users=$this->getUsers($source);
							$options=array();
							foreach ($users as $key => $value) {
								$options[$key]=$value["displayName"];
							}

							$out.=$this->formLabel($source=="fe_users"?"sourceUser":"sourceAddress");
							if($this->error) $out.=$this->formLabel($this->errorMsg,true);
							$out.=$this->formCheckboxes("user",$options,isset($this->vars["user"])?$this->vars["user"]:"");
							$out.=$this->formSubmitButton("submit","submit");
							$out.=$this->formLink(array("step"=>$this->step,"source"=>""),"back");
						break;
						default:
							$out.=$this->sourceFormOptions();
						break;
					}
					return $out;
				}
				function getMails($source){
					$out=array();
					switch($source){
						case "input":
							$input=trim($this->vars["addresses"]);
							$input=str_replace(chr(10),chr(13),$input);
							$input=str_replace(";",chr(13),$input);
							$input=str_replace(",",chr(13),$input);
							while(substr_count($input,chr(13).chr(13))>0){
								$input=str_replace(chr(13).chr(13),chr(13),$input);
							}

							$mails=t3lib_div::trimExplode(chr(13),$input);
							$invalidAdresses=array();
							foreach($mails as $email){
								if(!t3lib_div::validEmail($email)){
									$invalidAdresses[]=$email;
									$this->error=1;
								} else{
									$out[]=array(
										"email"=>$email,
										"displayName"=>$email,
										"feUserGroup"=>0,
										"feUserUid"=>0,
									);
								}
							}

							if($this->error){
								$this->errorMsg=$this->LL("errorInput").implode(", ",$invalidAdresses);
							}

						break;
						case "fe_users_group":
							$val=$this->vars["usergroup"];
							$where="usergroup=$val OR usergroup LIKE '%,$val,%' OR usergroup LIKE '$val,%' OR usergroup LIKE '%,$val'";
							$where.=" AND disable=0 AND deleted=0 AND email<>''";

							$res=$GLOBALS["TYPO3_DB"]->exec_SELECTgetRows("*", "fe_users", $where,"", "", "", "");
							foreach($res as $row){
								$row["displayName"]=$row["name"]." (".$row["username"].")";
								$row["feUserGroup"]=$val;
								$row["feUserUid"]=$row["uid"];
								$out[$row["uid"]]=$row;
							}

						break;
						case "fe_users":
						case "tt_address":
							if(empty($this->vars["user"])){
								$this->error=1;
								$this->errorMsg=$this->LL("errorUser");
							}else{
								$uids=implode(",",$this->vars["user"]);
								$where="uid IN ($uids)";
								$res=$this->getUsers($source,$where,false);

								foreach ($res as $row) {
									$out[$row["uid"]]=$row;
								}
							}
						break;
						default:
							$out.=$this->sourceFormOptions();
						break;
					}

					return $out;
				}
				function sourceFormOptions(){
					$out=$this->formLabel("sourceTitle");
					$out.="<br />";

					// Enter adresses
					$out.=$this->formLink(array("step"=>$this->step,"source"=>"input","q_id"=>$this->q_id,"id"=>$this->pid),"sourceInput");
					$out.="<br />";

					// // Select tt_address group
					// 	$out.=$this->formLink(array("step"=>$this->step,"source"=>"tt_address_group"),"sourceAddressGroup");
					// 	$out.="<br />";


					// Select tt_address records
					$out.=$this->formLink(array("step"=>$this->step,"source"=>"tt_address","q_id"=>$this->q_id,"id"=>$this->pid),"sourceAddress");
					$out.="<br />";


					// >Select Usergroup
					$out.=$this->formLink(array("step"=>$this->step,"source"=>"fe_users_group","q_id"=>$this->q_id,"id"=>$this->pid),"sourceUserGroup");
					$out.="<br />";

					// >Select Users
					$out.=$this->formLink(array("step"=>$this->step,"source"=>"fe_users","q_id"=>$this->q_id,"id"=>$this->pid),"sourceUser");
					$out.="<br />";


					return $out;
				}
				function generateHiddenFields($fields){
					$out="";
					foreach($fields as $field){
						$out.=$this->formHiddenField($field,$this->vars[$field]);
					}
					return $out;
				}
				function getUsers($table,$where="TRUE"){
					if($table=="fe_users"){
						$res=$GLOBALS["TYPO3_DB"]->exec_SELECTgetRows("*", "fe_users", "$where AND deleted=0 and disable=0 AND email<>''", "name", "", "", "");
						$out=array();
						foreach($res as $row){
							$row["displayName"]=$row["name"]." (".$row["username"].")";
							$row["feUserGroup"]=0;
							$row["feUserUid"]=$row["uid"];
							$out[$row["uid"]]=$row;


						}

					}else{
						$res=$GLOBALS["TYPO3_DB"]->exec_SELECTgetRows("*", "tt_address", "$where AND deleted=0 and hidden=0 AND email<>''", "name", "", "", "");

						$out=array();
						foreach($res as $row){
							$row["displayName"]=$row["name"];
							$row["feUserGroup"]=0;
							$row["feUserUid"]=0;
							$out[$row["uid"]]=$row;
						}
					}
					return $out;
				}
				function getUsergroups(){
					$res=$GLOBALS["TYPO3_DB"]->exec_SELECTgetRows("*", "fe_groups", "deleted=0", "title", "", "", "");
					$out=array();
					foreach($res as $row){
						$out[$row["uid"]]=$row["title"];
					}
					return $out;
				}
				function formLabel($title,$error=false){
					$title=$this->LL($title);
					$style=$error?"color:red;":"";
					$out=$this->formDiv("<b style='$style'>$title</b>");
					return $out;
				}
				function formTable($rows,$header=0){
					$layout = array (

					   'table' =>  array ('<table cellpadding=1 cellspacing=1 border=1>','</table>'),

					   # normale Zelle
					   'defRow' => array (
					      'tr' => array ('<tr class="tr-normal">','</tr>'),
					      'defCol' => array ('<td valign="top">','</td>')
					      ),

					   # Ungrade Zeile
					   'defRowOdd' => array (
					      'tr' => array ('<tr class="tr-odd">','</tr>'),
					      'defCol' => array ('<td valign="top">','</td>')
					      ),

					   # Grade Zeile
					   'defRowEven' => array (
					      'tr' => array ('<tr class="tr-odd">','</tr>'),
					      'defCol' => array ('<td valign="top">','</td>')
					      )
					   );
					$out=$this->doc->table($rows,$layout);
					return $out;
				}
				function formLink($parameters,$title,$vars=1,$div=1){
					$title=$this->LL($title);
					$url="index.php?";
					foreach($parameters as $name=>$val){
						if($vars) $name="vars[$name]";
						$url.="&".$name."=".$val;
					}

					$out="<a href='$url'>$title</a>";
					if($div) $out=$this->formDiv($out);

					return $out;
				}
				function formDropdown($name,$options,$val,$vars=1,$div=1){
					if($vars) $name="vars[$name]";
					$out="<select name='$name'>";

					foreach($options as $key=>$title){
						$selected=$key==$val?"selected='selected'":"";
						$out.="<option $selected value='$key'>$title</option>";
					}
					$out.="</select>";
					if($div) $out=$this->formDiv($out);
					return $out;
				}

				function formCheckboxes($name,$options,$val,$vars=1,$div=1){
					if($vars) $name="vars[$name]";
					$name.="[]";if(!is_array($val)) $val=array();
					$out="<table border=0>";

					foreach($options as $key=>$title){
						$checked=in_array($key,$val)?"checked='checked'":"";
						$out.="<tr><td><input type='checkbox' name='$name' $checked value='$key' /></td><td>$title</td></tr>";
					}
					$out.="</table>";
					if($div) $out=$this->formDiv($out);
					return $out;
				}
				function formInput($name,$val,$vars=1,$div=1){
					if($vars) $name="vars[$name]";
					$out="<input type='text' name='$name' value='$val' />";
					if($div) $out=$this->formDiv($out);
					return $out;
				}
				function formTextArea($name,$val,$vars=1,$div=1){
					if($vars) $name="vars[$name]";
					$out="<input type='submit' name='$name' value='title' />";
					$out="<textarea name='$name' style='width:500px;height:300px;'>$val</textarea>";
					if($div) $out=$this->formDiv($out);
					return $out;
				}
				function formSubmitButton($name,$title,$vars=1,$div=1){
					$title=$this->LL($title);
					if($vars) $name="vars[$name]";
					$out="<input type='submit' name='$name' value='$title' />";
					if($div) $out=$this->formDiv($out);
					return $out;
				}
				function formHiddenField($name,$val,$vars=1){
					if($vars) $name="vars[$name]";
					$out="<input type='hidden' name='$name' value='$val' />";
					return $out;
				}
				function formDiv($val){
					$out="<div>$val</div>";
					return $out;
				}
				function LL($val){
					$out=$GLOBALS["LANG"]->getLL($val);
					if($out=="") $out=$val;

					return $out;

				}
			}



if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/ke_questionnaire/mod4/index.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/ke_questionnaire/mod4/index.php']);
}




// Make instance:
$SOBE = t3lib_div::makeInstance('tx_kequestionnaire_module4');
$SOBE->init();

// Include files?
foreach($SOBE->include_once as $INC_FILE)	include_once($INC_FILE);

$SOBE->main();
$SOBE->printContent();

?>
