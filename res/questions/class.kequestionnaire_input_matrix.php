<?php
define(KEQUESTIONAIRE_EMPTY,-1);
class kequestionnaire_input_matrix extends kequestionnaire_input{
        function kequestionnaire_input_matrix($fieldName,$type,$value,$subpart,$obj,$subquestions=array(),$columns=array(),$maxAnswers = 0){
                $this->type=$type;
                $this->value=$value;
                $this->subquestions=$subquestions;
                $this->columns=$columns;
                $this->fieldName=$fieldName;
                $this->subpart=$subpart;
                $this->obj=$obj;
                $this->cObj=$obj->cObj;
                $this->odd=0;
                $this->maxAnswers = $maxAnswers;
        
                $arr=explode("__",$fieldName);
                if(count($arr)>1){
                        $this->table=$arr[0];
                        $this->fieldName=$arr[1];
                }
                //t3lib_div::devLog('fieldname '.$fieldName, 'kequestionnaire_input', 0, $arr);
                //t3lib_div::devLog('value '.$fieldName, 'kequestionnaire_input', 0,array($value));
        }
        
        /**
        * Select rendering method depending on type
        */
        function render(){
                switch($this->type){
                        case "matrix_head":
                                $out=$this->renderMatrixHead();
                                break;
                        case "matrix_radio":
                        case "matrix_checkbox":
                        case "matrix_input":
                        case "matrix_input_numeric":
                        case "matrix_input_integer":
                        case "matrix_input_date":
                        case "matrix_input_percent":
                                $out=$this->renderMatrixElement($this->type);
                                break;
                        case "matrix_radio_with_input":
                        case "matrix_checkbox_with_input":
                        case "matrix_input_with_input":
                        case "matrix_input_numeric_with_input":
                        case "matrix_input_integer_with_input":
                        case "matrix_input_date_with_input":
                        case "matrix_input_percent_with_input":
                                $type=substr($this->type,0,strlen($this->type)-strlen("_with_input"));
                                $out=$this->renderMatrixElement($type,1);
                                break;
                        case "matrix_input_percent_sum":
                                $out=$this->renderMatrixSum();
                                break;
                        case "matrix_title_line":
                                $out=$this->renderMatrixTitleLine();
                                break;
                        case "matrix_slider":
                                $out=$this->renderMatrixSlider();
                                break;
                        default:
                                $out="Type $this->type not defined.";
                                //t3lib_div::debug($out,"Inputklasse");
                                break;
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
                // Anpassungen Title-Line f�r Matrix
                if ($this->type == 'matrix_title_line'){
                        $markerArray["###ODD_EVEN###"]='title_line';
                } else {
                        $markerArray["###ODD_EVEN###"]=$this->odd?"odd":"even";
                }
                //#############################################

                //#############################################
                // KENNZIFFER Stefan Froemken 15.12.2010
                // Anpassungen Slider f�r Matrix
                if ($this->type == 'matrix_slider'){
                        $markerArray["###ODD_EVEN###"]='slider';
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
        
        function renderMatrixHead(){
                $tmplCol=$this->cObj->getSubpart($this->tmplHead,"###COLUMN###");
                //t3lib_div::devLog('markerArraySub head', 'input->MatrixElement', 0, array($tmplCol));
                $this->html="";
        
                foreach($this->columns as $column){
                    $markerArray = array();
                    $markerArray['###VALUE###'] = $column['title'];
                    $markerArray = $this->renderImage($markerArray,$column);
                    $col = $this->cObj->substituteMarkerArrayCached($tmplCol, $markerArray);
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
                        case 'matrix_input_integer': $marker = '###INPUT_INTEGER_COLUMN###';
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
                                        $markerArraySub["###CHECKED###"]=$value['single']==$column["uid"]?"checked='checked'":"";
                                break;
                                case "matrix_checkbox":
                                        $markerArraySub['###ONCHANGE###'] = $this->checkOnchange($question['uid'],$column);
                                        $markerArraySub["###CHECKED###"]= '';
                                        if (is_array($value)){
                                                //t3lib_div::devLog('matrix_checkbox '.$fieldName, 'kequestionnaire_input', 0, array($value[$column['uid']]));
                                                if (is_array($value[$column['uid']])){
                                                        if ($value[$column['uid']][0] == $column['uid']) $markerArraySub["###CHECKED###"] = "checked='checked'";
                                                        else $markerArraySub["###CHECKED###"] = '';
                                                }
                                                //$markerArraySub["###CHECKED###"]=in_array($column["uid"],$value)?"checked='checked'":"";
                                        } else {
                                                if ($value == $column["uid"]) $markerArraySub["###CHECKED###"] =  "checked='checked'";
                                                else $markerArraySub["###CHECKED###"] = '';
                                        }
                                break;
                                case "matrix_input_numeric":
                                case "matrix_input_integer":
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
        
                //images
                $markerArray = $this->renderImage($markerArray,$question);
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

				function renderMatrixSlider(){
					$question = $type=="semantic" ? $this->sublines[$this->fieldName] : $this->subquestions[$this->fieldName];
					$temp = count($this->columns);
					$i = 1;
					foreach($this->columns as $value) {
						$jsCase .= '
							case ' . $i . ':
							value = "' . $value['title'] . '";
							break;
						';
						$i++;
					}
					
					$tmplCol=$this->cObj->getSubpart($this->tmpl, '###SLIDER###');
					$header = $this->cObj->getSubpart($this->cObj->fileResource($this->template), '###HEADER_ADDITIONS###');
					$GLOBALS['TSFE']->register['kequestionnaire'][$question['question_uid']] = '
						$( "#keq_'.$question['question_uid'].'_'.$question['uid'].'" ).val(1);
						$( "#span_'.$question['question_uid'].'_'.$question['uid'].'" ).text(id2title(1));
						$( "#slider_'.$question['question_uid'].'_'.$question['uid'].'" ).slider({
							value: 1,
							min: 1,
							max: '.$temp.',
							step: 1,
							slide: function( event, ui ) {
								$( "#keq_'.$question['question_uid'].'_'.$question['uid'].'" ).val(ui.value);
								$( "#span_'.$question['question_uid'].'_'.$question['uid'].'" ).text(id2title(ui.value));
							}
						});
						function id2title(id) {
							switch(id) {
								' . $jsCase . '
							}
							
							return value;
						}
						';
					$GLOBALS['TSFE']->additionalHeaderData['keq-css-slider'] = '<link rel="stylesheet" type="text/css" href="'.t3lib_extMgm::siteRelPath('ke_questionnaire').'res/jquery/jquery-ui-1.8.11.custom.css" media="all" />';
					$GLOBALS['TSFE']->additionalHeaderData['keq-js-core'] = '<script src="'.t3lib_extMgm::siteRelPath('ke_questionnaire').'res/jquery/jquery-1.5.1.min.js" type="text/javascript"></script>';
					$GLOBALS['TSFE']->additionalHeaderData['keq-js-ui'] = '<script src="'.t3lib_extMgm::siteRelPath('ke_questionnaire').'res/jquery/jquery-ui-1.8.11.custom.min.js" type="text/javascript"></script>';
					
					$markerArray["###COLSPAN###"] = $temp;
					
					$markerArray["###QUESTION###"] = $type=="semantic"?$question["start"]:($question["text"]!=""?$question["text"]:$question["title"]);
					$markerArray["###JQ_SLIDER###"] = '<label for="keq_'.$question['question_uid'].'_'.$question['uid'].'">Aktueller Wert: <span id="span_'.$question['question_uid'].'_'.$question['uid'].'"></span></label><input type="hidden" value="" id="keq_'.$question['question_uid'].'_'.$question['uid'].'" name="tx_kequestionnaire_pi1['.$question['question_uid'].'][options]['.$question['uid'].'][single]" style="border: none;" /><br /><div id="slider_'.$question['question_uid'].'_'.$question['uid'].'" style="margin-left: 30px; margin-right: 30px;"></div>';
					
					$this->html=$this->cObj->substituteMarkerArrayCached($tmplCol, $markerArray);
					//t3lib_div::devLog('markerArraySub tpl', 'input->MatrixElement', 0, array($this->html));
					
					return $this->html;
				}
				
				function checkOnchange ($act_subq_id,$act_col) {
                $onchange = '';
                
                //If there are max Answers for the whole matrix
                if ($this->maxAnswers > 0){
                        $maxAnswers_error = $this->obj->pi_getLL('error_maxAnswersMatrix');
        		$maxAnswers_error = str_replace('###MAX###',$this->maxAnswers,$maxAnswers_error);

                        $js_maxAnswers_matrix = "
function keq_checkMaxMatrix(qid,subqs,cols,idy,max) {
  var amount = 0;
  var subqs = subqs.split(',');
  var cols = cols.split(',');
  
  for (var sc=0;sc<subqs.length;sc++){
        for (var cc=0;cc<cols.length;cc++){
                if (document.getElementById('keq_'+qid+'_'+subqs[sc]+'_'+cols[cc])){
                        if (document.getElementById('keq_'+qid+'_'+subqs[sc]+'_'+cols[cc]).checked==true) amount ++;
                }                
        }
        if (amount>max){
                document.getElementById('keq_'+idy).checked=false;
                alert ('".$maxAnswers_error."');
                break;
        }
  }
}";
                        $GLOBALS['TSFE']->setJS('ke_questionnaire_checkMaxMatrix',$js_maxAnswers_matrix);
                        $sub_qs = array();
                        foreach ($this->subquestions as $nr => $subq){
                                $sub_qs[] = $subq['uid'];
                        }
                        $subq_ids = implode(',',$sub_qs);
                        $cols = array();
                        foreach ($this->columns as $nr => $col){
                                $cols[] = $col['uid'];
                        }
                        $col_ids = implode(',',$cols);
                        $onchange .= 'keq_checkMaxMatrix(###NAME###,"'.$subq_ids.'","'.$col_ids.'","###NAME###_'.$act_subq_id.'_'.$act_col['uid'].'",'.$this->maxAnswers.');';
                }
                
                //if there are max answers for the columns
                if ($act_col['maxanswers'] > 0){
                        $maxAnswers_error = $this->obj->pi_getLL('error_maxAnswersColumn');
        		$maxAnswers_error = str_replace('###MAX###',$act_col['maxanswers'],$maxAnswers_error);

                        $js_maxAnswers_column = "
function keq_checkMaxColumn(qid,subqs,colid,idy,max,title) {
  var amount = 0;
  var subqs = subqs.split(',');
  
  for (var sc=0;sc<subqs.length;sc++){
        //alert('keq_'+qid+'_'+subqs[sc]+'_'+colid);
        if (document.getElementById('keq_'+qid+'_'+subqs[sc]+'_'+colid)){
                if (document.getElementById('keq_'+qid+'_'+subqs[sc]+'_'+colid).checked==true) amount ++;
        }                
        if (amount>max){
                document.getElementById('keq_'+idy).checked=false;
                alert ('".$maxAnswers_error. "'+' ('+title+')');
                break;
        }
  }
}";
                        $GLOBALS['TSFE']->setJS('ke_questionnaire_checkMaxColumn',$js_maxAnswers_column);
                        $sub_qs = array();
                        foreach ($this->subquestions as $nr => $subq){
                                $sub_qs[] = $subq['uid'];
                        }
                        $subq_ids = implode(',',$sub_qs);
                        $onchange .= 'keq_checkMaxColumn(###NAME###,"'.$subq_ids.'","'.$act_col['uid'].'","###NAME###_'.$act_subq_id.'_'.$act_col['uid'].'",'.$act_col['maxanswers'].',"'.$act_col['title'].'");';
                }
                
                if ($onchange != '') $onchange = "onchange='".$onchange."'";
                return $onchange;
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
                        case "matrix_required_option":
                                foreach($this->subquestions as $key=>$subquestion){
                                        if(isset($value[$key])) continue;
                                        $out=0;
                                        $this->subquestions[$key]["error"]=$validationType;
                                }
                        break;
                        case "matrix_required_input":
                                foreach($this->subquestions as $key=>$subquestion){
                                        foreach($this->columns as $keyCol => $column){
                                                if(isset($value[$key][$keyCol]) && $value[$key][$keyCol]!="") continue;
                                                $out=0;
                                                $this->subquestions[$key]["error"]=$validationType;
                                        }
                                }
                        break;
                        case "matrix_integer":
                                foreach($this->subquestions as $key=>$subquestion){
                                        foreach($this->columns as $keyCol => $column){
                                                $check = $value[$key][$keyCol][0];
                                                $out = 0;
                                                if (is_numeric($check)){
                                                        if ((int)$check == $check) $out = 1;
                                                }
                                        }
                                }
                        break;
                        case "matrix_numeric":
                        case "matrix_percent":
                                foreach($this->subquestions as $key=>$subquestion){
                                        foreach($this->columns as $keyCol => $column){
                                                //t3lib_div::devLog('validate '.$value, 'input->MatrixElement', 0, array($valNumeric));
                                                if(!isset($value[$key][$keyCol][0]) || !isset($validationOptions["numberDivider"]) || $value[$key][$keyCol][0]=="") continue;
                                                $valNumeric=str_replace(",",".",$value[$key][$keyCol][0]);

                                                if($validationOptions["numberDivider"]=="," && substr_count($value[$key][$keyCol][0],".")>0) $out=0;
                                                elseif($validationOptions["numberDivider"]=="." && substr_count($value[$key][$keyCol][0],",")>0) $out=0;
                                                elseif($valNumeric!="") $out=is_numeric($valNumeric);
                                                if(!$out) $this->subquestions[$key]["error"]=$validationType;
                                        }
                                }
                        break;
                        case "matrix_date":
                                foreach($this->subquestions as $key=>$subquestion){
                                        foreach($this->columns as $keyCol => $column){
                                                if(!isset($value[$key][$keyCol]) || !isset($validationOptions["dateFormat"]) || $value[$key][$keyCol]=="") continue;
                                                if($this->is_date($value[$key][$keyCol],$validationOptions["dateFormat"])) continue;
                                                $this->subquestions[$key]["error"]=$validationType;
                                                if (count($this->subquestions[$key]["error"]) >0 ) $out = 0;
                                                //t3lib_div::devLog('validate', 'input->MatrixElement', 0, array('value'=>$value,'type'=>$validationType,'options'=>$validationOptions,'errors'=>$this->subquestions[$key]["error"]));
                                        }
                                }
                        break;
                        case "matrix_sum":
                                $sum=array();

                                foreach($this->columns as $column){
                                        $sum=0;
                                        foreach($this->subquestions as $subquestion){
                                                $sumValue=isset($value[$subquestion["uid"]][$column["uid"]][0])?$value[$subquestion["uid"]][$column["uid"]][0]:0;
                                                //t3lib_div::devLog('validate', 'input->MatrixElement', 0, $this->value[$subquestion["uid"]][$column["uid"]]);
                                                //$sumValue=isset($value[$subquestion["uid"]][$column["uid"]])?$value[$subquestion["uid"]][$column["uid"]]:0;
                                                $sum+=$sumValue;
                                        }
                                        if($sum==100 OR $sum==0) continue;
                                        $out=0;
                                }

                        break;
                }


                return $out;
        }
}
?>