<?php
	define(KEQUESTIONAIRE_EMPTY,-1);
	class kequestionnaire_input{
		var $type           	= "";           // Type of field (input, textarea, select, multiselect, radiobutton, checkbox)
		var $value      	= FALSE;	// value entered by user
		var $options 		= array();	// Options to select from
		var $fieldName 		= "";		// Name of field
		var $errors    		= array();     	// Errortext to display
		var $table="";
		var $maxAnswers 	= 0;
		
		function kequestionnaire_input($fieldName,$type,$value,$subpart,$obj,$options=array(),$subquestions=array(),$columns=array(),$sublines=array(),$label="",$dependants=array()){
			$this->type=$type;
			$this->value=$value;
			$this->options=$options;
			$this->subquestions=$subquestions;
			$this->columns=$columns;
			$this->sublines=$sublines;
			$this->fieldName=$fieldName;
			$this->subpart=$subpart;
			$this->obj=$obj;
			$this->cObj=$obj->cObj;
			$this->dependants = $dependants;
			$this->label=$label;
			$this->odd=0;

			$arr=explode("__",$fieldName);
			if(count($arr)>1){
				$this->table=$arr[0];
				$this->fieldName=$arr[1];
			}
			//t3lib_div::devLog('fieldname '.$fieldName, 'kequestionnaire_input', 0, $arr);
		}
		
		/**
		 * Select rendering method depending on type
		 */
		function render(){
			switch($this->type){
				case "input":
					$out=$this->renderInput();
				break;
				case "radiobutton":
					$out=$this->renderRadiobutton();
				break;
				case "radiobutton_with_input":
					$out=$this->renderRadiobuttonWithInput();
				break;
				case "checkbox":
					$out=$this->renderCheckbox();
				break;
				case "checkbox_with_input":
					$out=$this->renderCheckboxWithInput();
				break;
				case "selectbox":
					$out=$this->renderSelectbox(0);
				break;
				case "selectbox_multi":
					$out=$this->renderSelectbox(1);
				break;
				case "matrix_head":
					$out=$this->renderMatrixHead();
				break;
				case "semantic":
					$out=$this->renderMatrixElement($this->type);
				break;
				case "semantic_with_input":
					$type=substr($this->type,0,strlen($this->type)-strlen("_with_input"));
					$out=$this->renderMatrixElement($type,1);
				break;
				case "matrix_title_line":
					$out=$this->renderMatrixTitleLine();
				break;
				case "demographic_radio":
					$out=$this->renderDemographicRadio();
				break;
				case "privacy":
					$out=$this->renderPrivacy();
				break;
				case "blind":
					$out=$this->renderBlind();
				break;
				case "sbm_button":
					$out=$this->renderSubmit();
				break;
				default:
					$out="Type $this->type not defined.";
					//t3lib_div::debug($out,"Inputklasse");

			}
			//t3lib_div::devLog('render', 'input', 0, array($out));

			// Helptext
			$subpartHelp=$this->cObj->getSubpart($out,"###HELP###");
			$subpartArray=array(
				"###HELP###"=>$this->options[$this->fieldName]["helptext"]==""?"":$subpartHelp,
			);
			
			// Help
			$markerArray['###A_HELPBOX###'] = '';
			if (trim($this->options[$this->fieldName]['helptext']) != ''){
			    $h_out = array();
			    $h_out['###HELPIMAGE###'] =  t3lib_extMgm::siteRelPath('ke_questionnaire').'/res/images/helpbubble.gif';
			    $h_out['###HELPTEXT###'] = $this->options[$this->fieldName]['helptext'];
			    $h_out['###A_ID###'] = $this->options[$this->fieldName]['uid'];
			    $h_out_subpart = $this->cObj->getSubpart($this->tmplHelp,'###HELPBOX_ANSWER###');
			    $h_out_content = $this->cObj->substituteMarkerArrayCached($h_out_subpart, $h_out, array(), array());
			    $markerArray['###A_HELPBOX###'] = trim($h_out_content);
			}

			//#############################################
			// KENNZIFFER Nadine Schwingler 03.11.2009
			// Anpassungen Title-Line für Matrix
			if ($this->type == 'matrix_title_line'){
				$markerArray["###ODD_EVEN###"]='title_line';
			} else {
				$markerArray["###ODD_EVEN###"]=$this->odd?"odd":"even";
			}
			//#############################################

			// Replace demograpic markers
			if($this->table!=""){
				$markerArray["###TABLE###"]=$this->table;

			}
			//St3lib_div::devLog('markerArraySub marker', 'input', 0, array($out,$markerArray));
			$out=$this->cObj->substituteMarkerArrayCached($out, $markerArray,$subpartArray);

			$this->html=$out;
			return $out;
		}
		
		/**
		 * Rendering of an Input Field
		 */
		function renderInput(){
			$this->value = str_replace('"','&quot;',$this->value);
			$markerArray["###VALUE###"]=$this->value;
			$markerArray["###LABEL###"]=$this->label;
			$markerArray["###FIELDNAME###"]=$this->fieldName;
			$markerArray['###DEPENDANT_AJAX###'] = $this->checkDependant($this->fieldName,'\'\'');
			//t3lib_div::devLog('markerArray', 'input', 0, $markerArray);

			if(empty($this->errors)){
				$subpartArray["###ERROR_MESSAGE###"]="";
			}else{
				$msg="";
				foreach($this->errors as $error){
					$msg .= '<span class="keq_input_error">';
					$msg.=$this->obj->pi_getLL("error_".$error);
					$msg .= '</span>';
				}
				$subpartArray["###ERROR_MESSAGE###"]=str_replace("###ERROR###",$msg,$this->tmplError);
			}

			$this->html=$this->cObj->substituteMarkerArrayCached($this->tmpl, $markerArray,$subpartArray);
			return $this->html;

		}

		function renderRadiobutton(){
			$markerArray['###STYLE###'] = '';
			t3lib_div::devLog('renderRadio Button '.$this->fieldName, 'input', 0, $this->options[$this->fieldName]);
			$markerArray["###LABEL###"]=$this->options[$this->fieldName]["title"];
			if ($this->options[$this->fieldName]['text'] != '') {
				$temp_val = str_replace('&nbsp;','',$this->options[$this->fieldName]['text']);
				$temp_val = str_replace('<br />','',$temp_val);
				if (trim($temp_val) != '') $markerArray['###LABEL###'] = $this->obj->pi_RTEcssText((string)$this->options[$this->fieldName]['text']);
				$markerArray['###STYLE###'] = 'class="keq_radio_rte"';
			}

			$markerArray['###HELPIMAGE###'] = t3lib_extMgm::siteRelPath('ke_questionnaire').'/res/images/helpbubble.gif';
			$markerArray['###HELPTEXT###'] = $this->options[$this->fieldName]['helptext'];

			$markerArray["###CHECKED###"]=$this->value==$this->fieldName?"checked='checked'":'';
			$markerArray["###VALUE###"]=$this->options[$this->fieldName]['uid'];
			$markerArray['###DEPENDANT_AJAX###'] = $this->checkDependant($this->fieldName,$this->options[$this->fieldName]['uid']);
			//t3lib_div::devLog('markerArray', 'input', 0, $markerArray);
			
			//images
			$markerArray = $this->renderImage($markerArray,$this->options[$this->fieldName]);

			if(empty($this->errors)){
				$subpartArray["###ERROR_MESSAGE###"]="";
			}else{
				$msg="";
				foreach($this->errors as $error){
					$msg.=$this->obj->pi_getLL("error_".$error);
				}
				$subpartArray["###ERROR_MESSAGE###"]=str_replace("###ERROR###",$msg,$this->tmplError);
			}
			$this->html=$this->cObj->substituteMarkerArrayCached($this->tmpl, $markerArray,$subpartArray);
			return $this->html;

		}

		function renderRadiobuttonWithInput(){
			$markerArray['###STYLE###'] = '';
			$markerArray["###LABEL###"]=$this->options[$this->fieldName]["title"];
			if ($this->options[$this->fieldName]['text'] != '') {
				$temp_val = str_replace('&nbsp;','',$this->options[$this->fieldName]['text']);
				$temp_val = str_replace('<br />','',$temp_val);
				if (trim($temp_val) != '') $markerArray['###LABEL###'] = $this->obj->pi_RTEcssText((string)$this->options[$this->fieldName]['text']);
				$markerArray['###STYLE###'] = 'class="keq_radio_rte"';
			}
			$markerArray['###HELPIMAGE###'] = t3lib_extMgm::siteRelPath('ke_questionnaire').'/res/images/helpbubble.gif';
			$markerArray['###HELPTEXT###'] = $this->options[$this->fieldName]['helptext'];

			if ($this->value["options"] == $this->fieldName){
				$markerArray["###CHECKED###"]= "checked='checked'";
				$markerArray['###DEPENDANT_TEXT###']= '';
			} else {
				$markerArray["###CHECKED###"]= '';
				$markerArray['###DEPENDANT_TEXT###']= 'disabled';
			}

			
			$this->value["text"][$this->fieldName] = str_replace('"','&quot;',$this->value["text"][$this->fieldName]);
			$markerArray["###VALUE_TEXT###"]=$this->value["text"][$this->fieldName];
			$markerArray["###VALUE_OPTION###"]=$this->options[$this->fieldName]['uid'];
			$markerArray["###VALUE###"]=$this->options[$this->fieldName]['uid'];
			$markerArray['###DEPENDANT_AJAX###'] = $this->checkDependant($this->fieldName,$this->options[$this->fieldName]['uid'], true);

			//t3lib_div::devLog('markerArray', 'input', 0, $markerArray);
			
			//images
			$markerArray = $this->renderImage($markerArray,$this->options[$this->fieldName]);

			if(empty($this->errors)){
				$subpartArray["###ERROR_MESSAGE###"]="";
			}else{
				$msg="";
				foreach($this->errors as $error){
					$msg.=$this->obj->pi_getLL("error_".$error);
				}
				$subpartArray["###ERROR_MESSAGE###"]=str_replace("###ERROR###",$msg,$this->tmplError);
			}

			$this->html=$this->cObj->substituteMarkerArrayCached($this->tmpl, $markerArray,$subpartArray);
			return $this->html;

		}

		function renderCheckbox(){
			$markerArray['###STYLE###'] = '';
			$markerArray["###LABEL###"]=$this->options[$this->fieldName]["title"];
			if ($this->options[$this->fieldName]['text'] != '') {
				$temp_val = str_replace('&nbsp;','',$this->options[$this->fieldName]['text']);
				$temp_val = str_replace('<br />','',$temp_val);
				if (trim($temp_val) != '') $markerArray['###LABEL###'] = $this->obj->pi_RTEcssText((string)$this->options[$this->fieldName]['text']);
				$markerArray['###STYLE###'] = 'class="keq_check_rte"';
			}

			$markerArray['###HELPIMAGE###'] = t3lib_extMgm::siteRelPath('ke_questionnaire').'/res/images/helpbubble.gif';
			$markerArray['###HELPTEXT###'] = $this->options[$this->fieldName]['helptext'];

			if (is_array($this->value)){
				$markerArray["###CHECKED###"] = in_array($this->fieldName,$this->value) ?"checked='checked'":'';
			} else {
				$markerArray["###CHECKED###"] = ($this->fieldName==$this->value) ?"checked='checked'":'';
			}			
			
			$markerArray["###VALUE###"]=$this->options[$this->fieldName]['uid'];

			$markerArray['###DEPENDANT_AJAX###'] = $this->checkDependant($this->fieldName,$this->options[$this->fieldName]['uid'],false,$this->maxAnswers);
			//t3lib_div::devLog('markerArray', 'input', 0, $markerArray);
			
			//images
			$markerArray = $this->renderImage($markerArray,$this->options[$this->fieldName]);

			if(empty($this->errors)){
				$subpartArray["###ERROR_MESSAGE###"]="";
			}else{
				$msg="";
				foreach($this->errors as $error){
					$msg.=$this->obj->pi_getLL("error_".$error);
				}
				$subpartArray["###ERROR_MESSAGE###"]=str_replace("###ERROR###",$msg,$this->tmplError);
			}
			//t3lib_div::devLog('renderCheckbox', 'input', 0, array($this->tmpl,$subpartArray));
			$this->html=$this->cObj->substituteMarkerArrayCached($this->tmpl, $markerArray,$subpartArray);
			return $this->html;

		}

		function renderCheckboxWithInput(){
			$markerArray['###STYLE###'] = '';
			$markerArray["###LABEL###"]=$this->options[$this->fieldName]["title"];
			if ($this->options[$this->fieldName]['text'] != '') {
				$temp_val = str_replace('&nbsp;','',$this->options[$this->fieldName]['text']);
				$temp_val = str_replace('<br />','',$temp_val);
				if (trim($temp_val) != '') $markerArray['###LABEL###'] = $this->obj->pi_RTEcssText((string)$this->options[$this->fieldName]['text']);
				$markerArray['###STYLE###'] = 'class="keq_check_rte"';
			}
			$markerArray['###HELPIMAGE###'] = t3lib_extMgm::siteRelPath('ke_questionnaire').'/res/images/helpbubble.gif';
			$markerArray['###HELPTEXT###'] = $this->options[$this->fieldName]['helptext'];

			$markerArray['###DEPENDANT_TEXT###']= 'disabled';
			$markerArray["###CHECKED###"] = '';
			if (is_array($this->value['options'])){
				if (in_array($this->fieldName,$this->value["options"])){
					$markerArray["###CHECKED###"] = "checked='checked'";
					$markerArray['###DEPENDANT_TEXT###']= '';
				} else {
					$markerArray["###CHECKED###"]= "";
					$markerArray['###DEPENDANT_TEXT###']= 'disabled';
				}
			}
			
			$this->value["text"][$this->fieldName] = str_replace('"','&quot;',$this->value["text"][$this->fieldName]);
			$markerArray["###VALUE_TEXT###"]=$this->value["text"][$this->fieldName];
			$markerArray["###VALUE_OPTION###"]=$this->options[$this->fieldName]['uid'];
			$markerArray["###VALUE###"]=$this->options[$this->fieldName]['uid'];
			$markerArray['###DEPENDANT_AJAX###'] = $this->checkDependant($this->fieldName,$this->options[$this->fieldName]['uid'],true,$this->maxAnswers);
			
			//images
			$markerArray = $this->renderImage($markerArray,$this->options[$this->fieldName]);

			//t3lib_div::devLog('markerArray', 'input', 0, $markerArray);

			if(empty($this->errors)){
				$subpartArray["###ERROR_MESSAGE###"]="";
				$markerArray["###ERRORCLASS###"] ="";
			}else{
				$msg="";
				foreach($this->errors as $error){
					$msg.=$this->obj->pi_getLL("error_".$error);
				}
				$subpartArray["###ERROR_MESSAGE###"]=str_replace("###ERROR###",$msg,$this->tmplError);
			}

			$this->html=$this->cObj->substituteMarkerArrayCached($this->tmpl, $markerArray,$subpartArray);
			return $this->html;

		}
		function renderSelectbox($multi){
			$markerArray["###LABEL###"]=$this->label;
			$markerArray["###FIELDNAME###"]=$this->fieldName;

			$options=$multi?"":"<option value='".KEQUESTIONAIRE_EMPTY."'></option>";
			foreach($this->options as $row){
				if (is_array($this->value)){
					$selected=in_array($row["uid"],$this->value)?"selected='selected'":"";
				} else {
					$selected=($row["uid"]==$this->value)?"selected='selected'":"";
				}
				//t3lib_div::devLog('$this->value', 'input', 0, array($this->value));
				$options.="<option value='".$row["uid"]."' $selected>";
				$option_title = $row["title"];
				if ($row['text'] != '') {
					$temp_val = str_replace('&nbsp;','',$row['text']);
					$temp_val = str_replace('<br />','',$temp_val);
					if (trim($temp_val) != '') $option_title = $temp_val;
				}
				$options .= $option_title;
				//$options .= $row["title"];
				$options .= "</option>";
			}
			$subpartArray["###OPTIONS###"]=$options;
			$markerArray['###DEPENDANT_AJAX###'] = $this->checkDependant($this->fieldName,$row["uid"],false,$this->maxAnswers);
			//images
			$markerArray = $this->renderImage($markerArray,$this->options[$this->fieldName]);


			if(empty($this->errors)){
				$subpartArray["###ERROR_MESSAGE###"]="";
			}else{
				$msg="";
				foreach($this->errors as $error){
					$msg.=$this->obj->pi_getLL("error_".$error);
				}
				$subpartArray["###ERROR_MESSAGE###"]=str_replace("###ERROR###",$msg,$this->tmplError);
			}

			$this->html=$this->cObj->substituteMarkerArrayCached($this->tmpl, $markerArray,$subpartArray);
			return $this->html;

		}
		function renderMatrixHead(){
			$tmplCol=$this->cObj->getSubpart($this->tmplHead,"###COLUMN###");
			$this->html="";

			foreach($this->columns as $column){
				$markerArray = arry();
				//images
				$markerArray = $this->renderImage($markerArray,$column);
				$markerArray['###VALUE###'] = $column["title"];
				$this->cObj->substituteMarkerArrayCached($tmplCol, $markerArray);
				$this->html.=$col;
			}


			return $this->html;
		}
		function renderMatrixElement($type,$input=0){
			$tmplInput=$this->cObj->getSubpart($this->tmpl,"###INPUT###");

			$question=$type=="semantic"?$this->sublines[$this->fieldName]:$this->subquestions[$this->fieldName];

			$markerArrayInput=array(
				"###SUBQUESTION_ID###"=>$question["uid"],
				"###VALUE###"=>isset($this->value["text"][$question["uid"]])?$this->value["text"][$question["uid"]]:"",
			);
			$markerArraySub["###SUBQUESTION_ID###"]=$question["uid"];

			$inputHtml=$this->cObj->substituteMarkerArrayCached($tmplInput, $markerArrayInput);

			$subpartArray["###INPUT###"]=$input?$inputHtml:"";
			//t3lib_div::devLog('markerArraySub subquestions '.$type, 'input->MatrixElement', 0, array($this->tmpl));

			// #################################################
			// KENNZIFFER Nadine Schwingler 04.11.2009
			// changed the matrix template to get along with new rendering stuff for matrix
			// change: columns can be selected to show as other type as the whole matrix
			switch ($type){
				case 'matrix_radio': $marker = '###RADIO_COLUMN###';
					break;
				case 'matrix_checkbox': $marker = '###CHECK_COLUMN###';
					break;
				case 'matrix_input': $marker = '###INPUT_COLUMN###';
					break;
				case 'matrix_input_numeric': $marker = '###INPUT_NUMERIC_COLUMN###';
					break;
				case 'matrix_input_date': $marker = '###INPUT_DATE_COLUMN###';
					break;
				case 'matrix_input_percent': $marker = '###INPUT_PERCENT_COLUMN###';
					break;
				default: $marker = '###COLUMN###';
					break;
			}
			$tmplCol=$this->cObj->getSubpart($this->tmpl,$marker);
			// #################################################

			$html="";
			//t3lib_div::devLog('markerArraySub colmns', 'input->MatrixElement', 0, $this->columns);
			foreach($this->columns as $column){
				$markerArraySub=array();
				$markerArraySub["###SUBQUESTION_ID###"]=$question["uid"];
				$markerArraySub["###VALUE###"]=$column["uid"];
				$markerArraySub["###COLUMN_ID###"]=$column["uid"];
				
				$value=isset($this->value["options"][$question["uid"]])?($this->value["options"][$question["uid"]]):($type=="matrix_checkbox"?array():0);
				if ($column['different_type'] != ''){
					$temp_type = $type;
					switch ($column['different_type']){
						case 'check': $type = 'matrix_checkbox';
							break;
						case 'input': $type = 'matrix_input';
							break;
						case 'radio': $type = 'matrix_radio';
							break;
					}
					$temp_tmplCol=$this->cObj->getSubpart($this->tmpl,$temp_marker);
					$html.=$this->cObj->substituteMarkerArrayCached($temp_tmplCol, $markerArraySub,$subpartArray);
				}

				switch($type){
					case "matrix_radio":
					case "semantic":
						$markerArraySub["###CHECKED###"]=$value['single']==$column["uid"]?"checked='checked'":"";
					break;
					case "matrix_checkbox":
						if (is_array($value)){
							$markerArraySub["###CHECKED###"]=in_array($column["uid"],$value)?"checked='checked'":"";
						} else {
							$markerArraySub["###CHECKED###"]=$value==$column["uid"]?"checked='checked'":"";
						}
					break;
					case "matrix_input_numeric":
					case "matrix_input_date":
					case "matrix_input_percent":
					case "matrix_input":
						if (is_array($value)) $value[$column["uid"]] = str_replace('"','&quot;',$value[$column["uid"]]);
						$markerArraySub["###VALUE###"]=$value[$column["uid"]];

					break;
				}

				//t3lib_div::devLog('markerArraySub', 'input->MatrixElement', 0, $markerArraySub);
				// #################################################
				// KENNZIFFER Nadine Schwingler 04.11.2009
				// changed the matrix template to get along with new rendering stuff for matrix
				// change: columns can be selected to show as other type as the whole matrix
				if ($column['different_type'] != ''){
					$type = $temp_type;
					switch ($column['different_type']){
						case 'check': $temp_marker = '###CHECK_COLUMN###';
							break;
						case 'input': $temp_marker = '###INPUT_COLUMN###';
								$markerArraySub["###VALUE###"]=$value[$column["uid"]];
							break;
						case 'radio': $temp_marker = '###RADIO_COLUMN###';
							break;
					}
					$temp_tmplCol=$this->cObj->getSubpart($this->tmpl,$temp_marker);
					$html.=$this->cObj->substituteMarkerArrayCached($temp_tmplCol, $markerArraySub,$subpartArray);
				} else {
					$html.=$this->cObj->substituteMarkerArrayCached($tmplCol, $markerArraySub,$subpartArray);
				}
				// #################################################
			}

			if(!$question["error"]){
				$subpartArray["###ERROR_MESSAGE###"]="";
				$markerArray["###ERRORCLASS###"] ="";
			}else{

				$msg=$this->obj->pi_getLL("error_".$question["error"]);
				$subpartArray["###ERROR_MESSAGE###"]=str_replace("###ERROR###",$msg,$this->tmplError);
			}

			if ($type == 'semantic') $markerArray["###QUESTION###"]=$question["start"];
			else {
				$temp_val = str_replace('&nbsp;','',$question["text"]);
				$temp_val = str_replace('<br />','',$temp_val);
				if (trim($temp_val) != '') $markerArray["###QUESTION###"] = $question["text"];
				else $markerArray["###QUESTION###"] = $question["title"];
			}
			$markerArray["###QUESTION2###"]=$type=="semantic"?$question["end"]:"";
			$subpartArray["###COLUMNS###"]=$html;

			// $subpartArray["###ERROR_MESSAGE###"]="ERR";

			$this->html=$this->cObj->substituteMarkerArrayCached($this->tmpl, $markerArray,$subpartArray);

			return $this->html;
		}

		function renderMatrixSum(){
			$tmplCol=$this->cObj->getSubpart($this->tmpl,"###COLUMN###");

			$html="";
			foreach($this->columns as $column){
				$sum=0;
				foreach($this->subquestions as $subquestion){
					//$value=isset($this->value[$subquestion["uid"]][$column["uid"]])?$this->value[$subquestion["uid"]][$column["uid"]]:0;
					$value=isset($this->value[$subquestion["uid"]][$column["uid"]][0])?$this->value[$subquestion["uid"]][$column["uid"]][0]:0;
					//t3lib_div::devLog('validate', 'input->MatrixElement', 0, $this->value[$subquestion["uid"]][$column["uid"]]);
					$sum += $value;
				}
				$markerArraySub["###VALUE###"]=$sum;

				$html.=$this->cObj->substituteMarkerArrayCached($tmplCol, $markerArraySub);
			}
			$subpartArray["###COLUMNS###"]=$html;

			if(empty($this->errors)){
				$subpartArray["###ERROR_MESSAGE###"]="";
			}else{
				$msg="";
				foreach($this->errors as $error){
					$msg.=$this->obj->pi_getLL("error_".$error);
				}
				$subpartArray["###ERROR_MESSAGE###"]=str_replace("###ERROR###",$msg,$this->tmplError);
			}

			$this->html=$this->cObj->substituteMarkerArrayCached($this->tmpl, $markerArray,$subpartArray);

			return $this->html;
		}

		function renderMatrixTitleLine(){
			$question=$type=="semantic"?$this->sublines[$this->fieldName]:$this->subquestions[$this->fieldName];

			$tmplCol=$this->cObj->getSubpart($this->tmpl,"###TITLELINE###");

			$temp = count($this->columns);

			$markerArray["###COLSPAN###"] = $temp+2;

			$markerArray["###QUESTION###"]=$type=="semantic"?$question["start"]:($question["text"]!=""?$question["text"]:$question["title"]);

			$this->html=$this->cObj->substituteMarkerArrayCached($tmplCol, $markerArray);
			//t3lib_div::devLog('markerArraySub tpl', 'input->MatrixElement', 0, array($this->html));

			return $this->html;
		}

		function renderDemographicRadio(){
			$markerArray["###LABEL###"]=$this->label;
			$markerArray["###FIELDNAME###"]=$this->fieldName;

			$tmplOption=$this->cObj->getSubpart($this->tmpl,"###OPTION###");

			$html="";

			foreach($this->options as $option){

				$markerArraySub=array();
				$markerArraySub["###LABEL###"]=$option["title"];
				$markerArraySub["###VALUE###"]=$option["uid"];
				$markerArraySub["###FIELDNAME###"]=$this->fieldName;

				$markerArraySub["###CHECKED###"]=$this->value==$option["uid"]?"checked='checked'":"";


				$html.=$this->cObj->substituteMarkerArrayCached($tmplOption, $markerArraySub);
			}

			if(!$question["error"]){
				$subpartArray["###ERROR_MESSAGE###"]="";
				$markerArray["###ERRORCLASS###"] ="";
			}else{

				$msg=$this->obj->pi_getLL("error_".$question["error"]);
				$subpartArray["###ERROR_MESSAGE###"]=str_replace("###ERROR###",$msg,$this->tmplError);
			}

			$subpartArray["###OPTIONS###"]=$html;


			$this->html=$this->cObj->substituteMarkerArrayCached($this->tmpl, $markerArray,$subpartArray);

			return $this->html;
		}
		function renderPrivacy(){
			$markerArray["###VALUE###"]=$this->value;
			if(empty($this->errors)){
				$subpartArray["###ERROR_MESSAGE###"]="";
			}else{
				$msg="";
				foreach($this->errors as $error){
					$msg.=$this->obj->pi_getLL("error_".$error);
				}
				$subpartArray["###ERROR_MESSAGE###"]=str_replace("###ERROR###",$msg,$this->tmplError);
			}

			$this->html=$this->cObj->substituteMarkerArrayCached($this->tmpl, $markerArray,$subpartArray);
			return $this->html;

		}
		
		function renderSubmit(){
			if (is_array($this->value)){
				foreach($this->value as $answer) {
					$markerArray["###VALUE###"]=$answer['title'];
					$markerArray["###ANSWERVALUE###"]=$answer['uid'];
					
					$markerArrayTt = array();
					if(strlen($answer['helptext'])) {
						$markerArrayTt['###HELPIMAGETT###'] = t3lib_extMgm::siteRelPath('ke_questionnaire').'/res/images/helpbubble.gif';
						$markerArrayTt['###HELPTEXTTT###'] = $answer['helptext'];
					} else {
						$markerArrayTt['###HELPIMAGETT###'] = '';
						$markerArrayTt['###HELPTEXTTT###'] = '';
					}
					
					if(empty($this->errors)){
						$subpartArray["###ERROR_MESSAGE###"]="";
					}else{
						$msg="";
						foreach($this->errors as $error){
							$msg.=$this->obj->pi_getLL("error_".$error);
						}
						$subpartArray["###ERROR_MESSAGE###"]=str_replace("###ERROR###",$msg,$this->tmplError);
					}
					
					$htmlTmp = $this->cObj->substituteMarkerArrayCached($this->tmpl, $markerArray,$subpartArray);
					
					//render tooltip
					$toolTip = $this->cObj->getSubpart($this->tmpl,'###HELPTT###');
					$toolTip = $this->cObj->substituteMarkerArray($toolTip,$markerArrayTt);
					$htmlTmp = (strlen($markerArrayTt['###HELPTEXTTT###']))?$this->cObj->substituteSubpart($htmlTmp,'###HELPTT###',$toolTip):$this->cObj->substituteSubpart($htmlTmp,'###HELPTT###','');
					
					$this->html.=$htmlTmp;
				}
			}
			
			return $this->html;

		}
		
		function renderBlind(){

			$this->html=$this->value["text"];
			return $this->html;

		}
		
		function renderImage($markerArray,$data=array()){
			$markerArray['###IMG_LEFT###'] = '';
			$markerArray['###IMG_TOP###'] = '';
			$markerArray['###IMG_RIGHT###'] = '';
			$markerArray['###IMG_BOTTOM###'] = '';
			if ($data['image']){
				$img_path = 'uploads/tx_kequestionnaire/';
				$img_first = '<img alt="'.$data['title'].'" src="';
				$img_last = '" />';
				$img = '';
				$img = $img_first.$img_path.$data['image'].$img_last;
				$markerArray['###IMG_'.strtoupper($data['image_position']).'###'] = $img;
			}
			return $markerArray;
		}

		function checkDependant($fieldName, $value = '\'\'', $withText = false, $maxAnswers = 0){
			//t3lib_div::devLog('checkDependant', 'input', 0, array('fieldName'=>$fieldName,'value'=>$value,'maxAnswers'=>$maxAnswers,'type'=>$this->type));
			//t3lib_div::devLog('dependants '.$fieldName, 'input', 0, $this->dependants);
			$dependant_id = 0;
			$dependant_ids = array();

			$js_disable = "
function keq_disable(idy,par_id) {
  input = document.getElementById(idy);
  par = document.getElementById(par_id);

  if(par.checked == true) {
    input.disabled = false;
  } else {
    input.disabled = true;
  }
}";
			$maxAnswers_error = $this->obj->pi_getLL('error_maxAnswers');
			$maxAnswers_error = str_replace('###MAX###',$maxAnswers,$maxAnswers_error);
			$js_maxAnswers_checkbox = "
function keq_checkMax(namy,idy) {
  var amount = 0;
  var max = ".($maxAnswers).";
  
  for (var i=0;i<document.getElementsByName(namy).length;i++){
	if (document.getElementsByName(namy)[i].checked==true) amount ++;
	if (amount>max){
		document.getElementById('keq_'+idy).checked=false;
		alert ('".$maxAnswers_error."');
		break;
	}
  }  
}";

			$js_maxAnswers_select = "
function keq_selectMax(namy) {
  var amount = 0;
  var max = ".($maxAnswers).";
  
  for (var i=0;i<document.getElementsByName(namy)[0].length;i++){
	if (document.getElementsByName(namy)[0][i].selected==true) amount ++;
	if (amount>max){
		for (var j=0;j<document.getElementsByName(namy)[0].length;j++){
			document.getElementsByName(namy)[0][j].selected = false;
		}
		alert ('".$maxAnswers_error."');
		break;
	}
  }
}";

			$js = '';
			$onchange = '';

			foreach ($this->dependants as $dependant){
				$dependant_id = $dependant['dependant_question'];
				if (!in_array($dependant_id,$dependant_ids)){
					$dependant_ids[] = $dependant['dependant_question'];
					$js .= 'ke_questionnaire_checkDependants('.$dependant_id.',###NAME###,'.$value.',xajax.getFormValues(\'ke_questionnaire\')); ';
				}
			}
			if ($withText){
				$js .= 'document.getElementById(\'keq_###NAME###_'.$fieldName.'_text\').disabled = false;';
				$onchange = 'keq_disable(\'keq_###NAME###_'.$fieldName.'_text\',\'keq_###NAME###_'.$fieldName.'\');';
				$js .= $onchange;
				$GLOBALS['TSFE']->setJS('ke_questionnaire_disable',$js_disable);
			}
			if ($maxAnswers > 0){
				if ($this->type == 'checkbox'){
					$onchange .= "keq_checkMax('tx_kequestionnaire_pi1[###NAME###][options][]','###NAME###_$value');";
					$GLOBALS['TSFE']->setJS('ke_questionnaire_checkMax',$js_maxAnswers_checkbox);
				} else {
					$onchange .= "keq_selectMax('tx_kequestionnaire_pi1[###NAME###][options][]');";
					$GLOBALS['TSFE']->setJS('ke_questionnaire_selectMax',$js_maxAnswers_select);
				}
				$js .= $onchange;
			}

			if ($js != ''){
				//$content = 'onchange="';
				$content = 'onclick="';
				$content .= $js;
				$content .= '"';
				/*if ($onchange != ''){
					$content .= 'onchange="';
					$content .= $onchange;
					$content .= '"';
				}*/
				$this->closed_onchange .= $onchange;
			}
			//t3lib_div::devLog('fieldname '.$fieldName, 'input->checkDependant', 0, array($js, $content));
			//

			return $content;
		}

		#################################
		## VALIDATION AND ERROR MARKERS
		#################################

		/**
		 * Validation of a form element
		 * @param       array      	$validationTypes: validation types to check
		 *
		 * @return		array 		Array with list of unsuccessfully validated types, on success empty Array
		 *
		 */
		function validate($validationTypes,$value=NULL,$validationOptions=array()){

			if(!is_array($validationTypes)) $validationTypes=array($validationTypes);
			if(is_null($value)) $value=$this->value;


			foreach($validationTypes as $validationType){
				$valid=$this->validateType($value,$validationType,$validationOptions);
				if(!$valid) $this->errors[]=$validationType;
			}

			$out=$this->errors;

			return $out;
		}

		/**
		 * Validation of a single type
		 * @param       string      $formElement: type of formElement
		 * @param       string     	$value: value to validate
		 * @param       string      $type: type of validation to do
		 *
		 * @return	boolean Success of validation
		 *
		 */

		function validateType($value,$validationType,$validationOptions){
			$out=1;
			//t3lib_div::devLog('validate '.$value, 'input->MatrixElement', 0, array('type'=>$validationType,'options'=>$validationOptions));


			switch ($validationType){
				case "required":
					$out=$value!="";
				break;
				case "required_option":
					if(is_array($value)){
						$out=count($value)>0;
					}else{
						$out=$value!=KEQUESTIONAIRE_EMPTY;
					}
				break;
				case 'numeric':
					if(!isset($validationOptions["numberDivider"]) || $value=="") break;
					$valNumeric=str_replace(",",".",$value);
					if($validationOptions["numberDivider"]=="," && substr_count($value,".")>0) $out=0;
					elseif($validationOptions["numberDivider"]=="." && substr_count($value,",")>0) $out=0;
					elseif($valNumeric!="") $out=is_numeric($valNumeric);
				break;
				case 'date':
					if(!isset($validationOptions["dateFormat"]) || $value=="") break;
					if($value!="") $out=$this->is_date($value,$validationOptions["dateFormat"]);
				break;
    				case 'email':
					if($value!="") $out=t3lib_div::validEmail($value);
				break;
				case 'text':
					//t3lib_div::devLog('validate '.$value, 'open->text', 0, $validationOptions);
					$out = 0;
					foreach ($validationOptions['textOptions'] as $option){
						if ($value == $option) $out = 1;
					}
				break;
				case "semantic_required":
					foreach($this->sublines as $key=>$subline){
						if(isset($value[$key])) continue;
						$out=0;
						$this->sublines[$key]["error"]=$validationType;
					}
				break;
			}


			return $out;
		}

		function is_date($value, $format){
			// find separator
			$separator_only = str_replace(array('m','d','y'),'', $format);
			$separator = $separator_only[0]; // separator is first character

			if(!$separator) return false;
			if(substr_count($value,$separator)!=2) return false;

			// check for numbers
			$numbers=explode($separator,$value);

			foreach($numbers as $number){
				if(substr_count($number,".")>0) return false;
				if(!is_numeric($number)) return false;
			}

			$formatParts=explode($separator,$format);

			$i=0;$m=0;$d=0;$y=0;
			for($i=0;$i<3;$i++){
				if(substr_count($formatParts[$i],"m")>0) $m=$numbers[$i];
				if(substr_count($formatParts[$i],"d")>0) $d=$numbers[$i];
				if(substr_count($formatParts[$i],"y")>0) $y=$numbers[$i];

			}


			return checkdate($m,$d,$y);
		}

	    /**
		 * Validation of a single type
		 * @param       array      $errors: array of Errortypes
		 *
		 * @return	string 			Errortext
		 *
		 */
		function buildErrorText($errors){
			$out = '';
			// TODO: build correct error string from LL
			foreach ($errors as $error){
				if ($out != '') $out .= '<br />';
				$out .= $error;
			}
			$out=implode(",",$errors);

			return $out;
		}


	}
?>
