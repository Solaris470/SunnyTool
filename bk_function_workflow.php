<?php
function show_user_detail($rec, $field_name)
{
    $arr_data = array();
    $sql_usr_set = db::query("SELECT FIELD_NAME,FIELD_RELETION,WF_MAIN_ID,FIELD_TEXT,FIELD_TYPE,FIELD_LABEL FROM USR_SETTING WHERE FIELD_NAME='" . $field_name . "'");
    $rec_us = db::fetch_array($sql_usr_set);
    $arr_data['label'] = $rec_us["FIELD_LABEL"];


    if ($rec_us['FIELD_TYPE'] == 'S') {
        if ($rec_us['FIELD_NAME'] == 'DEP_ID') {
            $arr_data['value'] = get_data('USR_DEPARTMENT', 'DEP_ID', 'DEP_NAME', $rec['DEP_ID']);
        } elseif ($rec_us['FIELD_NAME'] == 'POS_ID') {
            $arr_data['value'] = get_data('USR_POSITION', 'POS_ID', 'POS_NAME', $rec['POS_ID']);
        } else {
            $arr_data['value'] = $rec[$rec_us['FIELD_NAME']];
        }
    } elseif ($rec_us['FIELD_TYPE'] == 'O') {
        if ($rec_us['FIELD_RELETION'] == 'T') {
            $arr_data['value'] = $rec[$rec_us['FIELD_NAME']];
        } elseif ($rec_us['FIELD_RELETION'] == '' and  $rec_us['WF_MAIN_ID'] != '') {
            $sql_m = "SELECT WF_MAIN_SHORTNAME,WF_TYPE,WF_FIELD_PK FROM WF_MAIN WHERE WF_MAIN_ID='" . $rec_us["WF_MAIN_ID"] . "' ";
            $query_m = db::query($sql_m);
            $rec_m = db::fetch_array($query_m);

            $sql_mt = "SELECT * FROM " . $rec_m["WF_MAIN_SHORTNAME"] . " WHERE " . $rec_m["WF_FIELD_PK"] . "='" . $rec[$rec_us["FIELD_NAME"]] . "'";
            $sql_m_t = db::query($sql_mt);

            if (db::num_rows($sql_m_t) > 0) {
                $data_m = db::fetch_array($sql_m_t);

                $arr_data['value'] = $data_m[str_replace(["##", "!!"], "", $rec_us['FIELD_TEXT'])]; //bsf_show_text($rec_us["WF_MAIN_ID"], $data_m, $rec_us['FIELD_TEXT'], $rec_m["WF_TYPE"]);
            }
        } elseif ($rec_us['FIELD_RELETION'] == 'M') {
            $sql_m = "SELECT WF_MAIN_SHORTNAME,WF_TYPE,WF_FIELD_PK FROM WF_MAIN WHERE WF_MAIN_ID='" . $rec_us["WF_MAIN_ID"] . "' ";
            $query_m = db::query($sql_m);
            $rec_m = db::fetch_array($query_m);

            if ($rec[$rec_us["FIELD_NAME"]] != '') {
                $sql_mt = "SELECT * FROM " . $rec_m["WF_MAIN_SHORTNAME"] . " WHERE " . $rec_m["WF_FIELD_PK"] . " IN (" . $rec[$rec_us["FIELD_NAME"]] . ")";
                $sql_m_t = db::query($sql_mt);
                $arr_master_m = array();
                while ($data_m = db::fetch_array($sql_m_t)) {

                    $arr_master_m[] = $data_m[str_replace(["##", "!!"], "", $rec_us['FIELD_TEXT'])];; //bsf_show_text($rec_us["WF_MAIN_ID"], $data_m, $rec_us['FIELD_TEXT'], $rec_m["WF_TYPE"]);
                }

                $data_master = implode(',', $arr_master_m);
                $arr_data['value'] = $data_master;
            }
        } else {
            if ($rec_us['FIELD_NAME'] == 'DEP_ID') {
                $arr_data['value'] = get_data('USR_DEPARTMENT', 'DEP_ID', 'DEP_NAME', $rec['DEP_ID']);
            } elseif ($rec_us['FIELD_NAME'] == 'POS_ID') {
                $arr_data['value'] = get_data('USR_POSITION', 'POS_ID', 'POS_NAME', $rec['POS_ID']);
            }
        }
    }
    return $arr_data;
}
function bsf_language($l_code,$l_ref,$default_text,$l_null=''){
	if($_SESSION['WF_LANGUAGE'] == ""){
		return $default_text;
	}else{
		$sql_lang = db::query("SELECT CONF_VALUE FROM WF_LANG_CONFIG WHERE CONF_CODE = '".$l_code."' AND CONF_REF = '".$l_ref."' AND CONF_LANG = '".$_SESSION['WF_LANGUAGE']."'");
		$L = db::fetch_array($sql_lang);
		if($L['CONF_VALUE'] != ""){
			return json_decode(sprintf('"%s"', $L['CONF_VALUE']));
		}else{
			if($l_null == ""){
				return $default_text;
			}
		}
	}
}
function bsf_language_m($l_code,$l_ref,$lang){
 
		$sql_lang = db::query("SELECT CONF_VALUE FROM WF_LANG_CONFIG WHERE CONF_CODE = '".$l_code."' AND CONF_REF = '".$l_ref."' AND CONF_LANG = '".$lang."'");
		$L = db::fetch_array($sql_lang);

		return json_decode(sprintf('"%s"', $L['CONF_VALUE']));


}
function wf_convert_var($txt,$decode=''){
if($txt != ""){
	if($decode == "Y"){
	$txt = htmlspecialchars_decode($txt, ENT_QUOTES);
	}
	
	preg_match_all("/(@@)([a-zA-Z0-9_]+)(!!)/", $txt, $new_sql1, PREG_SET_ORDER); 
	foreach ($new_sql1 as $val_new) 
	{ 
		$txt = str_replace("@@".$val_new[2]."!!",$_SESSION[$val_new[2]],$txt); 
	}
	
	preg_match_all("/(@#)([a-zA-Z0-9_]+)(!!)/", $txt, $new_sql2, PREG_SET_ORDER); 
	foreach ($new_sql2 as $val_new) 
	{
		$txt = str_replace('@#'.$val_new[2].'!!',$_GET[$val_new[2]],$txt); 
	}
	return $txt;
	}	
}
function wf_profile($UID){
	$arr = array();
	$sql = db::query("select USR_ID,USR_PREFIX,USR_FNAME,USR_LNAME,USR_PICTURE from USR_MAIN where USR_ID = '".$UID."'");
	$rec = db::fetch_array($sql);
	
	if($rec["USR_PICTURE"] != ''){
		$arr["img"] = '../profile/'.$rec["USR_PICTURE"];
	}else{
		$arr["img"] = '../assets/images/avatar-2.png';
	}
	$arr["name"] = $rec["USR_PREFIX"].$rec["USR_FNAME"].' '.$rec["USR_LNAME"];
	return $arr;
}
function step_name($WFD,$ico=''){
	$sql_det = db::query("select WFD_NAME from WF_DETAIL where WFD_ID = '".$WFD."' ");
	$BSF_D = db::fetch_array($sql_det);
	return $BSF_D["WFD_NAME"];
}
function workflow_name($W,$ico=''){
	$sql_det = db::query("select WF_MAIN_NAME from WF_MAIN where WF_MAIN_ID = '".$W."' ");
	$BSF_D = db::fetch_array($sql_det);
	return $BSF_D["WF_MAIN_NAME"];
}
function wf_number_format($number,$digit=999,$thousands_sep = ","){
	if(trim($number)==''){$number=0; }
	$num = str_replace(',','',$number);
	if($digit==999){
		$c_dit = explode('.',$num);
		$digit = strlen($c_dit[1]);
	}
	if(!is_numeric($num)){
		$num = 0;
	}
	$number = number_format($num,$digit,'.',$thousands_sep);
	return $number;
}
function wf_search_function($W,$WF=array(),$WF_TABLE='',$PK=''){
	$filter = "";
	$simple_operator = array(1 => '=',5 => '>',6 => '>=',7 => '<',8 => '<=',9 => '!=');
	$sql_search = db::query("SELECT * FROM WF_STEP_FORM where WF_MAIN_ID = '".$W."' AND WF_TYPE = 'S' ORDER BY WFS_ORDER,WFS_OFFSET");
	while($rec_search = db::fetch_array($sql_search)){  
		if(trim($rec_search["WFS_SEARCH_CON"]) != "99" AND trim($rec_search["WFS_FIELD_NAME"]) != "" AND ($WF[trim($rec_search["WFS_FIELD_NAME"])] != "" OR $rec_search['FORM_MAIN_ID'] == "5")){
			 
			if($rec_search['FORM_MAIN_ID'] == "5" AND $rec_search["WFS_INPUT_FORMAT"]=="M"){ 
			$check_is_chkbox = db::query("SELECT COUNT(WFS_ID) AS NUM FROM WF_STEP_FORM WHERE WF_MAIN_ID = '".$W."' AND WF_TYPE != 'S'");
			$NUM_C = db::fetch_array($check_is_chkbox);
			if($NUM_C['NUM']>0){
				$num_c = conText($WF[$rec_search["WFS_FIELD_NAME"].'_COUNT']);
				 for($c=0;$c<$num_c;$c++){
					 $chk_val = conText($WF[$rec_search["WFS_FIELD_NAME"].'_'.$c]);
					 if($chk_val != ""){
						 $filter .= " AND ((SELECT COUNT(CHECKBOX_ID) FROM WF_CHECKBOX WHERE W_ID = '".$W."' AND WFS_FIELD_NAME = '".$rec_search["WFS_FIELD_NAME"]."' AND WF_CHECKBOX.WFR_ID = ".$WF_TABLE.".".$PK." AND CHECKBOX_VALUE = '".$chk_val."') > 0 ) ";
					 }
				 }
			}else{
				$search_f = trim($rec_search["WFS_SEARCH_FIELD_NAME"]);
				if($search_f == ""){ $search_f = trim($rec_search["WFS_FIELD_NAME"]); } 
				$num_c = conText($WF[$rec_search["WFS_FIELD_NAME"].'_COUNT']);
				$filter1 = ""; 
				 for($c=0;$c<$num_c;$c++){
					 $chk_val = conText($WF[$rec_search["WFS_FIELD_NAME"].'_'.$c]);
					 
					 if($chk_val != ""){
						 $filter1 .= " OR ".$search_f." = '".$chk_val."' ";
					 }
					 
				 }
				 if($filter1 != ""){
					$filter .= " AND ( 1=0 ".$filter1." ) "; 
				 }
				 
			}
			}elseif($WF[trim($rec_search["WFS_FIELD_NAME"])] != ""){
			$conTxt = conText($WF[trim($rec_search["WFS_FIELD_NAME"])]);
			if($rec_search['FORM_MAIN_ID'] == "1"){
				if($rec_search['WFS_INPUT_FORMAT']=="TU"){
					$conTxt = mb_strtoupper($conTxt,'UTF-8');
				}elseif($rec_search['WFS_INPUT_FORMAT']=="TL"){
					$conTxt = mb_strtolower($conTxt,'UTF-8');
				}
			}
			 
			if($rec_search['FORM_MAIN_ID'] == "3"){
				if($rec_search["WFS_CALENDAR_EN"] == "Y"){
				$conTxt = date2db_en($conTxt);
				}else{
				$conTxt = date2db($conTxt);
				}	
			}
			$search_f = $rec_search["WFS_SEARCH_FIELD_NAME"];
			if($search_f == ""){ $search_f = $rec_search["WFS_FIELD_NAME"]; } 
			
			$array_s = array();
			$e = explode(',',$search_f);
			if($rec_search['WFS_SEARCH_CON']=="10"){
				$filter .= " AND ('".$conTxt."' BETWEEN ".$e[0]." AND ".$e[1].") ";
			}else{
			foreach($e as $val){
				if(array_key_exists($rec_search['WFS_SEARCH_CON'], $simple_operator)){
					$array_s[] = trim($val).$simple_operator[$rec_search['WFS_SEARCH_CON']]." '".$conTxt."'";
				}elseif($rec_search['WFS_SEARCH_CON'] == 2){
					$array_s[] = trim($val)." LIKE '%".$conTxt."%'";
				}elseif($rec_search['WFS_SEARCH_CON'] == 3){
					$array_s[] = trim($val)." LIKE '".$conTxt."%'";
				}elseif($rec_search['WFS_SEARCH_CON'] == 4){
					$array_s[] = trim($val)." LIKE '%".$conTxt."'";
				}
			}
				if(count($array_s) > 0){
				$con = implode(' OR ',$array_s);
				$filter .= " AND (".$con.")";
				}
			}
			}
		}elseif($rec_search['FORM_MAIN_ID'] == "10"){
				if(trim((string)$rec_search["WFS_CODING_POST"]) != ''){
					$c_save = explode(',',trim($rec_search["WFS_CODING_POST"]));
					$array_s = array();
					foreach($c_save as $val){
						$conTxt = conText($_GET[trim($val)]);
						if(array_key_exists($rec_search['WFS_SEARCH_CON'], $simple_operator)){
							$array_s[] = trim($val).$simple_operator[$rec_search['WFS_SEARCH_CON']]." '".$conTxt."'";
						}elseif($rec_search['WFS_SEARCH_CON'] == 2){
							$array_s[] = trim($val)." LIKE '%".$conTxt."%'";
						}elseif($rec_search['WFS_SEARCH_CON'] == 3){
							$array_s[] = trim($val)." LIKE '".$conTxt."%'";
						}elseif($rec_search['WFS_SEARCH_CON'] == 4){
							$array_s[] = trim($val)." LIKE '%".$conTxt."'";
						} 
					}
					if(count($array_s) > 0){
					$con = implode(' AND ',$array_s);
					$filter .= " AND (".$con.")";
					}
				}
				if($rec_search["WFS_CODING_SAVE"] != '' AND file_exists('../save/'.$rec_search["WFS_CODING_SAVE"])){
					include('../save/'.$rec_search["WFS_CODING_SAVE"]);
				}
		}
	} 
	return $filter;	
}
function bsf_gen_select($sql){ //แปลง ##FIELD!! เป็น statement
		$text = str_replace("&#039;","'",$sql);
		$text = str_replace("&quot;",'"',$text);
            $search  = array();
            $replace = array(); 
if(strpos($text, '##') !== false) {
		preg_match_all("/(##)([a-zA-Z0-9_]+)(!!)/", $text, $matches, PREG_SET_ORDER);
         foreach ($matches as $val){
            array_push($search,"##".$val[2]."!!"); 
            array_push($replace,$val[2]);
         } 

        $contents = str_replace($search, $replace, $text); 
}else{
	if(db::$_dbType=="ORACLE"){
        preg_match_all("/(##)([a-zA-Z0-9_]+)(!!)/", $text, $matches, PREG_SET_ORDER);
         foreach ($matches as $val){
            array_push($search,"##".$val[2]."!!"); 
            array_push($replace,"'||".$val[2]."||'");
         } 

        $contents = "'".str_replace($search, $replace, $text)."'"; 
	}elseif(db::$_dbType=="MSSQL"){
        preg_match_all("/(##)([a-zA-Z0-9_]+)(!!)/", $text, $matches, PREG_SET_ORDER);
         foreach ($matches as $val){
            array_push($search,"##".$val[2]."!!"); 
            array_push($replace,"'+".$val[2]."+'");
         } 

        $contents = "'".str_replace($search, $replace, $text)."'"; 
	}else{
        preg_match_all("/(##)([a-zA-Z0-9_]+)(!!)/", $text, $matches, PREG_SET_ORDER);
         foreach ($matches as $val){
            array_push($search,"##".$val[2]."!!"); 
            array_push($replace,"',".$val[2].",'");
         } 

        $contents = "CONCAT('".str_replace($search, $replace, $text)."')"; 
	}
}
        return $contents;
}
function bsf_show_text($W,$A_DATA,$text,$WF_TYPE=''){ //replace ##FIELD!! จาก table และหาค่า text
		global $WF_LIST_DATA;
		 
		$text = wf_convert_var($text);
		if($text != ""){
		/*$text = str_replace("&#039;","'",$text);
		$text = str_replace("&quot;",'"',$text);
		preg_match_all("/(@@)([a-zA-Z0-9_]+)(!!)/", $text, $new_sql1, PREG_SET_ORDER);
		foreach ($new_sql1 as $val_new) {
			$text = str_replace("@@".$val_new[2]."!!",$_SESSION[$val_new[2]],$text);
		} */
		 
            $search  = array();
            $replace = array(); 
		preg_match_all("/(##)([a-zA-Z0-9_]+)(!!)/", $text, $matches, PREG_SET_ORDER);
        foreach ($matches as $val){
			$value = "";
			if($WF_TYPE != ""){
				$con = " AND WSF.WF_TYPE = '".$WF_TYPE."'";
			}else{
				$con = " AND WSF.WF_TYPE != 'S'";
			}
			$sql_form = db::query("SELECT WSF.* FROM WF_STEP_FORM WSF WHERE WSF.WF_MAIN_ID = '".$W."' ".$con." AND WSF.WFS_FIELD_NAME ='".$val[2]."' ORDER BY WFS_MAIN_SHOW ASC");

            $form_step = db::fetch_array($sql_form);  
			if(isset($form_step["FORM_MAIN_ID"])){
				switch($form_step["FORM_MAIN_ID"]){
					case '':
					case '1':	//textbox
					case '2': 	//textarea 
		if($form_step["WFS_INPUT_FORMAT"] == "N"){ $value = wf_number_format($A_DATA[$val[2]],0); 
		}elseif($form_step["WFS_INPUT_FORMAT"] == "N1"){ $value = wf_number_format($A_DATA[$val[2]],1); 
		}elseif($form_step["WFS_INPUT_FORMAT"] == "N2"){ $value = wf_number_format($A_DATA[$val[2]],2); 
		}elseif($form_step["WFS_INPUT_FORMAT"] == "N3"){	$value = wf_number_format($A_DATA[$val[2]],3); 
		}elseif($form_step["WFS_INPUT_FORMAT"] == "N4"){	$value = wf_number_format($A_DATA[$val[2]],4); 
		}elseif($form_step["WFS_INPUT_FORMAT"] == "N5"){	$value = wf_number_format($A_DATA[$val[2]],5); 
		}elseif($form_step["WFS_INPUT_FORMAT"] == "N6"){	$value = wf_number_format($A_DATA[$val[2]],6); 
		}elseif($form_step["WFS_INPUT_FORMAT"] == "TU"){	$value = mb_strtoupper($A_DATA[$val[2]],'UTF-8'); 
		}elseif($form_step["WFS_INPUT_FORMAT"] == "TL"){	$value = mb_strtolower($A_DATA[$val[2]],'UTF-8'); 
		}elseif($form_step["WFS_INPUT_FORMAT"] == "TC"){	$value = ucfirst(mb_strtolower($A_DATA[$val[2]],'UTF-8'));
		}elseif($form_step["WFS_INPUT_FORMAT"] == "ED"){	
					$sql_main_wf = db::query("select WF_FIELD_PK from WF_MAIN where WF_MAIN_ID = '".$W."'");
					$rec_main = db::fetch_array($sql_main_wf);
					$pk_field = $rec_main["WF_FIELD_PK"];
					$editor_folder2 = '../wysiwyg/w'.$W;
					$fp = @fopen($editor_folder2.'/e_'.$form_step["WFS_FIELD_NAME"].'_'.$A_DATA[$pk_field].'.tmp','r');
					$value = @fread($fp, filesize($editor_folder2.'/e_'.$form_step["WFS_FIELD_NAME"].'_'.$A_DATA[$pk_field].'.tmp'));
					@fclose($fp);
		}else{ $value = $A_DATA[$val[2]];  }
						break;
					case '4': 	//radio  
					case '7': 	//hidden
					case '9': 	//select
					if($A_DATA[$val[2]] != ""){
						$sql_main_wf = db::query("select WF_MAIN_ID,WF_MAIN_SHORTNAME,WF_FIELD_PK,WF_MAIN_TAB_STATUS from WF_MAIN where WF_MAIN_ID = '".$W."'");
							$rec_main = db::fetch_array($sql_main_wf);

						$pk_field = $rec_main["WF_FIELD_PK"];
						$result_relation = array();
						$result_relation = wf_call_relation($form_step["WFS_ID"],$A_DATA[$pk_field],$A_DATA,'',$A_DATA[$val[2]]);
						if(count($result_relation)>0){ 
							foreach($result_relation as $rel_val){
								if($rel_val["selected"] == "selected" OR $rel_val["selected"] == "checked"){
									$value = $rel_val["text"];
								}
							}
						}
					}
 	
						break;
					case '11': 	//Province
				if($A_DATA[$val[2]] != ""){
					$aflag = "";
					if($A_DATA[$val[2]] != "10"){ $aflag = ""; }
				if($_SESSION['WF_LANGUAGE'] == ""){ $pr_name = "PROVINCE_NAME"; }else{ $pr_name = "PROVINCE_NAME_EN"; }
				$sql_option = db::query("select ".$pr_name." from G_PROVINCE where PROVINCE_CODE = '".$A_DATA[$val[2]]."'  ");
				$rec_option = db::fetch_array($sql_option);
				$value = $aflag.$rec_option[$pr_name];
				}
						break;
					case '12': 	//Amphur
				if($A_DATA[$val[2]] != ""){
					$aflag = "";
				//	if(substr($A_DATA[$val[2]],0,2) != "10"){ $aflag = "อ."; }else{ $aflag = "เขต"; }
				if($_SESSION['WF_LANGUAGE'] == ""){ $amp_name = "AMPHUR_NAME"; }else{ $amp_name = "AMPHUR_NAME_EN"; }
				$sql_option = db::query("select ".$amp_name." from G_AMPHUR where PROVINCE_CODE = '".substr($A_DATA[$val[2]],0,2)."' AND AMPHUR_CODE = '".substr($A_DATA[$val[2]],2,2)."' ");
				$rec_option = db::fetch_array($sql_option);
				$value = $aflag.str_replace("*","",$rec_option[$amp_name]);
				}
						break;
					case '13': 	//Tambon
				if($A_DATA[$val[2]] != ""){
					$aflag = "";
				//	if(substr($A_DATA[$val[2]],0,2) != "10"){ $aflag = "ต."; }else{ $aflag = "แขวง"; }
				if($_SESSION['WF_LANGUAGE'] == ""){ $tam_name = "TAMBON_NAME"; }else{ $tam_name = "TAMBON_NAME_EN"; }
				$sql_option = db::query("select ".$tam_name." from G_TAMBON where PROVINCE_CODE = '".substr($A_DATA[$val[2]],0,2)."' AND AMPHUR_CODE = '".substr($A_DATA[$val[2]],2,2)."' AND TAMBON_CODE = '".substr($A_DATA[$val[2]],4,2)."'");
				$rec_option = db::fetch_array($sql_option);
				$value = $aflag.str_replace("*","",$rec_option[$tam_name]);
				}
						break;
					case '14': 	//Zipcode
						$value = $A_DATA[$val[2]];
						break;
					case '17': 	//Year
						$value = $A_DATA[$val[2]];
						break;
					case '3': //date 
						if($form_step["WFS_CALENDAR_EN"] == "Y"){
						$value = db2date_en($A_DATA[$val[2]]);
						}else{
						$value = db2date($A_DATA[$val[2]]);
						}
						break;
					case '5': //checkbox
						if($form_step["WFS_INPUT_FORMAT"] == "M"){
							$sql_main_wf = db::query("select WF_MAIN_ID,WF_MAIN_SHORTNAME,WF_FIELD_PK,WF_MAIN_TAB_STATUS from WF_MAIN where WF_MAIN_ID = '".$W."'");
							$rec_main = db::fetch_array($sql_main_wf);

							$pk_field = $rec_main["WF_FIELD_PK"];
							$data_list = array();
							$data_list = wf_call_relation($form_step["WFS_ID"],$A_DATA[$pk_field],$A_DATA);
							if(count($data_list)>0){ 
								foreach($data_list as $wf_v){
									if($wf_v['checked'] == 'checked'){
										$value .= '&nbsp;<i class="fa fa-check-square text-primary"></i> '.$wf_v['text'].'<br />';
									}
								} 
							}
						}else{ 
							if($WF_LIST_DATA=="Y" AND $form_step["WFS_OPTION_SHORT_SELECT"] == "Y"){
								if($A_DATA[$val[2]] != ''){
									$value = '<i class="fa fa-check-square text-primary"></i>';
								}else{
									$value = '<i class="fa fa-square-o text-muted"></i>';
								}
							}else{ 
								if($A_DATA[$val[2]] != ''){
									$value = '&nbsp;<i class="fa fa-check-square text-primary"></i> '.$form_step["WFS_NAME"];
								}else{
									$value = '&nbsp;<i class="fa fa-square-o text-muted"></i> '.$form_step["WFS_NAME"];
								}
								
							}
						}
						break;
					case '6': //browsefile 
					 
						$sql_main_wf = db::query("select WF_MAIN_ID,WF_MAIN_SHORTNAME,WF_FIELD_PK,WF_MAIN_TAB_STATUS from WF_MAIN where WF_MAIN_ID = '".$W."'"); 
						$rec_main = db::fetch_array($sql_main_wf);

						$pk_field = $rec_main["WF_FIELD_PK"];
						$value = bsa_show($form_step["WFS_FIELD_NAME"],$A_DATA[$pk_field],$W,$form_step["WFS_FILE_ORDER"],$form_step["WFS_FILE_LIGHTBOX"],'N');
						break;
				}
			}

           
            array_push($search,"##".$val[2]."!!"); 
            array_push($replace,$value); 
         } 

        $contents = str_replace($search, $replace, $text);
		$contents = str_replace("##!!", "", $contents); 
		}
        return $contents; 
}
function bsf_show_input($W,$A_DATA,$text,$WF_TYPE='W',$array_out=array(),$Flag=''){ //replace ##FIELD!! จาก table และหาค่า text
		$text = str_replace("&#039;","'",$text);
		$text = str_replace("&quot;",'"',$text);

        preg_match_all("/(##)([a-zA-Z0-9_]+)(!!)/", $text, $matches, PREG_SET_ORDER);
         foreach ($matches as $val) { 
			$value = "";
			$sql_form = db::query("SELECT WSF.WFS_ID FROM WF_STEP_FORM WSF WHERE WSF.WF_MAIN_ID = '".$W."' AND WSF.WF_TYPE = '".$WF_TYPE."' AND WSF.WFS_FIELD_NAME ='".$val[2]."'");
			 
            $form_step = db::fetch_array($sql_form);
			bsf_show_form($W,0,$A_DATA,$WF_TYPE,$form_step["WFS_ID"],$Flag);
			$array_out[] = $val[2];
         } 
		 return $array_out;
}
function bsf_show_input_view($text){ //get wf_step_form_id to ##Field!!
		$array_out = array();
		$exv = explode(",",$text);
         foreach ($exv as $val) {  
			$sql_form = db::query("SELECT WSF.WFS_FIELD_NAME FROM WF_STEP_FORM WSF WHERE WSF.WFS_ID ='".$val."'");	 
            $form_step = db::fetch_array($sql_form);
			if($form_step['WFS_FIELD_NAME'] != ""){
			$array_out[] = "##".$form_step['WFS_FIELD_NAME']."!!";
			}
         } 
		 return $array_out;
}
function bsf_show_input_hidden($W,$A_DATA,$text,$WF_TYPE='W',$array_out=array(),$Flag=''){ //replace ##FIELD!! จาก table และหาค่า text
		$text = str_replace("&#039;","'",$text);
		$text = str_replace("&quot;",'"',$text);

        preg_match_all("/(##)([a-zA-Z0-9_]+)(!!)/", $text, $matches, PREG_SET_ORDER);
         foreach ($matches as $val) { 
			$value = "";
			$sql_form = db::query("SELECT WSF.WFS_ID,WSF.FORM_MAIN_ID,WSF.WFS_CALENDAR_EN FROM WF_STEP_FORM WSF WHERE WSF.WF_MAIN_ID = '".$W."' AND WSF.WF_TYPE = '".$WF_TYPE."' AND WSF.WFS_FIELD_NAME ='".$val[2]."'");
			 
            $form_step = db::fetch_array($sql_form);
			//bsf_show_form($W,0,$A_DATA,$WF_TYPE,$form_step["WFS_ID"],$Flag);
			if($form_step['FORM_MAIN_ID'] == "3"){
						if($form_step["WFS_CALENDAR_EN"] == "Y"){
						$A_DATA[$val[2]] = db2date_en($A_DATA[$val[2]]);
						}else{
						$A_DATA[$val[2]] = db2date($A_DATA[$val[2]]);
						}
			}
			echo "<input type=\"hidden\" name=\"".$val[2].$Flag."\" value=\"".$A_DATA[$val[2]]."\">";
			$array_out[] = $val[2];
         } 
		 return $array_out;
}
function bsf_show_field($W,$A_DATA,$text,$WF_TYPE='W'){ //replace ##FIELD!! ตรงๆ จาก table
	if($text != ''){
		$text = str_replace("&#039;","'",$text);
		$text = str_replace("&quot;",'"',$text);
		$text = wf_convert_var($text);
            $search  = array();
            $replace = array(); 
        preg_match_all("/(##)([a-zA-Z0-9_]+)(!!)/", $text, $matches, PREG_SET_ORDER);
         foreach ($matches as $val){ 
            array_push($search,"##".$val[2]."!!"); 
            array_push($replace,$A_DATA[$val[2]]); 
         } 

        $contents = str_replace($search, $replace, $text); 
        return $contents; 
	}
}
function bsa_icon($ext,$tag="i",$opt=""){
	$icon = 'fa fa-file-text-o text-info';
	switch($ext){
			case 'pdf':
				$icon = 'fas fa-file-pdf text-danger';
			break;
			case 'jpg':
			case 'jpeg':
				$icon = 'fas fa-file-image text-warning';
			break;
			case 'gif':
				$icon = 'fas fa-file-image text-success';
			break;
			case 'png':
				$icon = 'fas fa-file-image text-danger';
			break;
			case 'doc':
			case 'docx':
				$icon = 'fas fa-file-word text-info';
			break;
			case 'xls':
			case 'xlsx':
			case 'csv':
				$icon = 'fas fa-file-excel text-success';
			break;
			case 'zip':
			case 'rar':
				$icon = 'fas fa-file-archive text-warning';
			break;
	}
	return '<'.$tag.' class="'.$icon.'" '.$opt.'></'.$tag.'>';
}
function bsa_iicon($ext){
	$icon = 'fas fa-file';
	if($ext != ''){ $ext = strtolower($ext); }
	switch($ext){
			case 'pdf':
				$icon = 'fas fa-file-pdf text-danger';
			break;
			case 'jpg':
			case 'jpeg':
			case 'gif':
			case 'png':
				$icon = 'fas fa-file-image';
			break;
			case 'doc':
			case 'docx':
				$icon = 'fas fa-file-word text-info';
			break;
			case 'xls':
			case 'xlsx':
				$icon = 'fas fa-file-excel text-success';
			break;
			case 'csv':
				$icon = 'fas fa-file-csv text-success';
			break;
			case 'zip':
			case 'rar':
				$icon = 'fas fa-file-archive text-warning';
			break;
	}
	return $icon;
}
function bsa_show($FIELD,$WFR,$W,$ORDER='0',$lightbox='',$EDIT=''){
	$array_img = array('png','jpg','jpeg','gif','bmp');
	$arr_order_file = array(''=>'FILE_ID ASC','0'=>'FILE_ID ASC',1=>'FILE_ID DESC',2=>'FILE_NAME ASC',3=>'FILE_NAME DESC',4=>'FILE_SIZE ASC',5=>'FILE_SIZE DESC',6=>'FILE_EXT ASC',7=>'FILE_EXT ASC');
	$txt = ''; 
	$sql_attach = db::query("SELECT * FROM WF_FILE where WFS_FIELD_NAME ='".$FIELD."' AND WFR_ID='".$WFR."' AND WF_MAIN_ID = '".$W."' AND FILE_STATUS = 'Y' ORDER BY ".$arr_order_file[$ORDER]);
	$rows = db::num_rows($sql_attach);
	if($rows > 0){
		 
		if($lightbox=="Y"){
		$txt .= '<div class="demo-gallery row"><ul id="profile-lightgallery">';
		}else{
		$txt .= '<div class="row"><div class="data_table_main icon-list-demo">';	
		}
	while($rec_a = db::fetch_array($sql_attach)){
		if($rec_a["FILE_TYPE"] == "DROPBOX"){
		$bsf_link = $rec_a["FILE_SAVE_NAME"];	
		$bsf_title = "";
		}else{
		$bsf_link = '../attach/w'.$W.'/'.$rec_a["FILE_SAVE_NAME"];
		$bsf_title = $rec_a["FILE_NAME"];
		}
		if($lightbox=="Y"){
			$txt .= '<li id="BSA_FILE'.$rec_a["FILE_ID"].'" class="col-md-4 col-lg-2 col-sm-6 col-xl-12  p-3"  title="'.$bsf_title.'">';
			$txt .= '<div class="social-profile">';
			if(in_array($rec_a["FILE_EXT"],$array_img)){
			$txt .= '<a  href="'.$bsf_link.'"  data-toggle="lightbox" data-footer="'.$rec_a["FILE_NAME"].'">';
			$txt .= '<img class="img-fluid width-100" src="'.$bsf_link.'" style="cursor:pointer"></a>';
			$txt .= '<div class="profile-hvr m-t-10">';
			$txt .= '<i class="icofont icofont-ui-search p-r-10" href="'.$bsf_link.'"  data-toggle="lightbox" data-footer="'.$rec_a["FILE_NAME"].'"></i>';
			if($EDIT==''){
			$txt .= '<i class="icofont icofont-ui-delete" onClick="wf_file_d(\''.$W.'\',\''.$rec_a["FILE_ID"].'\',\''.$rec_a["WFR_ID"].'\',\''.$rec_a["FILE_NAME"].'\');"></i>';
			}
			$txt .= '</div>';
			}else{
			if($EDIT==''){
			$txt .= '<div class="f-right"><a href="#!" onClick="wf_file_d(\''.$W.'\',\''.$rec_a["FILE_ID"].'\',\''.$rec_a["WFR_ID"].'\',\''.$rec_a["FILE_NAME"].'\');"><i class="icofont icofont-ui-delete"></i></a></div>';
			}
			$txt .= '<a  href="'.$bsf_link.'" target="_blank"><div align="center">'.bsa_icon($rec_a["FILE_EXT"],'b',' style="font-size:40px;"').'<br />'.$rec_a["FILE_NAME"].'</div></a>';	
			}
			$txt .= '</div>';
            $txt .= '</li>';
		}else{
			$txt .= '<div id="BSA_FILE'.$rec_a["FILE_ID"].'" class="to-do-list col-sm-12" title="'.$bsf_title.'">'.bsa_icon($rec_a["FILE_EXT"],'b').' <a  href="'.$bsf_link.'" target="_blank">'.$rec_a["FILE_NAME"].'</a>';
			if($EDIT==''){
			$txt .= '<div class="f-right"><a href="#!" onClick="wf_file_d(\''.$W.'\',\''.$rec_a["FILE_ID"].'\',\''.$rec_a["WFR_ID"].'\',\''.$rec_a["FILE_NAME"].'\');"><i class="icofont icofont-ui-delete"></i></a></div>';
			}
			$txt .= '</div>';
		}
	}
		if($lightbox=="Y"){
		$txt .= '</ul></div>';
		}else{
		$txt .= '</div></div>';	
		}
	}	
		return $txt;
} 
function bsf_show_form($W,$WFD=0,$WF=array(),$WF_TYPE='W',$WFS='',$SHOW='',$VIEW='',$WFS_CONF='',$INLINE_USE=''){
/*
$W = Workflow ID , $WFD = ขั้นตอน , $WF = array , $WF_TYPE = ประเภท workflow , $WFS = ระบุ $WFS , $SHOW = ค่า flag ที่ต่อ input id , $VIEW = Y = view, $WFS_CONF = WFS_ID ที่เป็นตัวโชว์ซ่อนใน form , $INLINE_USE
*/ 
////////////////////////////////////////////////////////////////////////
if(!isset($WF)){ $WF = array(); }
if($W == "" OR !is_numeric($W)){ exit; }
////////////////////////////////////////////////////////////////////////
$WFS_FORM_FIELD_EDIT = array();
$WFS_FORM_FIELD_VIEW = array();
////////////////////////////////////////////////////////////////////////
if($WFS != "" AND is_numeric($WFS)){
	$sql_form = db::query_first("select * from WF_STEP_FORM where WFS_ID = '".$WFS."' ");
	if($sql_form['WFS_ID'] != ''){
		$arr = array();
		$arr[0] = $sql_form;
		$arrwf = array();
		$arrwf['WF_MAIN_ID'] = $W;
		bsf_show_form_area($arr,$arrwf,$WFD,$WF,$WF_TYPE,$WFS,$SHOW,$VIEW,$WFS_CONF,$INLINE_USE);
	}
}else{
$rec_main = db::query_first("SELECT WF_MAIN_ID,WF_MAIN_SHORTNAME,WF_FIELD_PK,WF_TYPE,WF_JQUERY_VALIDATE FROM WF_MAIN WHERE WF_MAIN_ID = '".$W."'");
$pk_field = $rec_main["WF_FIELD_PK"];
	
$sql_form_con = " WFD_ID = '".$WFD."' AND WF_MAIN_ID = '".$W."' AND WF_TYPE = '".$WF_TYPE."' ";
if($WFS_CONF != ''){
	$WFSCONF = db::query_first("SELECT WFS_FORM_INPUT_SHOW,WFS_FORM_ADD_POPUP,WFS_FORM_FIELD_EDIT,WFS_FORM_FIELD_VIEW FROM WF_STEP_FORM WHERE WFS_ID = '".$WFS_CONF."'");
	if($WFSCONF['WFS_FORM_INPUT_SHOW'] == "M" AND $WFSCONF['WFS_FORM_ADD_POPUP'] == "Y"){
		$WFS_FORM_FIELD_EDIT = array();
		$WFS_FORM_FIELD_VIEW = array();
		if($WFSCONF['WFS_FORM_FIELD_EDIT'] != ""){
		$WFS_FORM_FIELD_EDIT = explode(',',$WFSCONF['WFS_FORM_FIELD_EDIT']);
		}
		if($WFSCONF['WFS_FORM_FIELD_VIEW'] != ""){
		$WFS_FORM_FIELD_VIEW = explode(',',$WFSCONF['WFS_FORM_FIELD_VIEW']);
		}
		$WFS_ARR_MERGE = array_filter(array_merge($WFS_FORM_FIELD_EDIT, $WFS_FORM_FIELD_VIEW));
		array_push($WFS_ARR_MERGE,'0');
		$sql_form_con .= " AND WF_STEP_FORM.WFS_ID IN (".implode(',',$WFS_ARR_MERGE).") ";
	}
}
$sql_form = db::query("SELECT * FROM WF_STEP_FORM WHERE ".$sql_form_con." ORDER BY FIELD_G_ID,WFS_ORDER,WFS_OFFSET");
$arr_wfs = array();
while($rec_form = db::fetch_array($sql_form)){
if($rec_form['FIELD_G_ID'] == ""){
	$rec_form['FIELD_G_ID'] = "0";
}
$arr_wfs[$rec_form['FIELD_G_ID']][] = $rec_form;

} 
$TAB_RR = array();
$TAB_DATA = array();
$TAB_START = ""; 
$TAB_RR[0]["FIELD_G_ID"] = "0";
$TAB_RR[0]["FIELD_G_NAME"] = "";
$TAB_RR[0]["FIELD_G_OFFSET"] = "0";
$TAB_RR[0]["FIELD_G_ORDER"] = "";
$TAB_RR[0]["FIELD_G_WIDTH"] = "12";
$TAB_RR[0]["FIELD_G_PARENT"] = "0";
$TAB_RR[0]["FIELD_G_TYPE"] = "D";
$TAB_RR[0]["FIELD_G_HIDDEN"] = "";
$TAB_RR[0]["FIELD_G_HIDE_LABEL"] = "Y";
$sql_tab = db::query("SELECT * FROM WF_FIELD_GROUP WHERE WF_MAIN_ID='".$W."' AND WFD_ID='".$WFD."' AND WF_TYPE = '".$WF_TYPE."' AND FIELD_G_PARENT  = '0' ORDER BY FIELD_G_ORDER ASC,FIELD_G_OFFSET ASC");
	$t=1;
	while($TAB1=db::fetch_array($sql_tab)){
		$TAB_RR[$t]["FIELD_G_ID"] = $TAB1["FIELD_G_ID"];
		$TAB_RR[$t]["FIELD_G_NAME"] = $TAB1["FIELD_G_NAME"];
		$TAB_RR[$t]["FIELD_G_OFFSET"] = $TAB1["FIELD_G_OFFSET"];
		$TAB_RR[$t]["FIELD_G_ORDER"] = $TAB1["FIELD_G_ORDER"];
		$TAB_RR[$t]["FIELD_G_WIDTH"] = $TAB1["FIELD_G_WIDTH"];
		$TAB_RR[$t]["FIELD_G_PARENT"] = "0";
		$TAB_RR[$t]["FIELD_G_TYPE"] = $TAB1["FIELD_G_TYPE"];
		$TAB_RR[$t]["FIELD_G_HIDDEN"] = $TAB1["FIELD_G_HIDDEN"];
		$TAB_RR[$t]["FIELD_G_HIDE_LABEL"] = $TAB1["FIELD_G_HIDE_LABEL"];
		$t++;
		if($TAB1["FIELD_G_TYPE"] == ""){
			if($TAB_START == ""){ 
			$TAB_START = $TAB1["FIELD_G_ID"];
			$txt = '<li class="nav-item"><a class="nav-link active" id="tabx'.$TAB1["FIELD_G_ID"].'" data-bs-toggle="tab" href="#tab_'.$TAB1["FIELD_G_ID"].'" role="tab" aria-controls="tab_'.$TAB1["FIELD_G_ID"].'" aria-selected="true" ';
			if($TAB1["FIELD_G_HIDDEN"] == "Y"){ $txt .= ' style="display:none;" '; }
			$txt .= '>'.$TAB1["FIELD_G_NAME"].'</a></li>';
			}else{
			$txt = '<li class="nav-item"><a class="nav-link" id="tab'.$TAB1["FIELD_G_ID"].'" data-bs-toggle="tab" href="#tab_'.$TAB1["FIELD_G_ID"].'" role="tab" aria-controls="tab_'.$TAB1["FIELD_G_ID"].'" aria-selected="false" ';
			if($TAB1["FIELD_G_HIDDEN"] == "Y"){ $txt .= ' style="display:none;" '; }
			$txt .= '>'.$TAB1["FIELD_G_NAME"].'</a></li>';	
			}
			$TAB_DATA[] = $txt;
		}
	} 
$TAB_SUB = array();
$sql_sub = db::query("SELECT * FROM WF_FIELD_GROUP WHERE WF_MAIN_ID='".$W."' AND WFD_ID='".$WFD."' AND WF_TYPE = '".$WF_TYPE."' AND FIELD_G_PARENT  > 0 ORDER BY FIELD_G_PARENT,FIELD_G_ORDER ASC,FIELD_G_OFFSET ASC"); 
while($TAB2=db::fetch_array($sql_sub)){
	$TAB_SUB[$TAB2['FIELD_G_PARENT']][] = $TAB2;
}

echo '<div class="row">';
$flag_order = "";
foreach($TAB_RR as $TAB){
	$class_offset = "";
	if($flag_order != $TAB['FIELD_G_ORDER'] AND $TAB["FIELD_G_TYPE"] == "G"){
		$offset_cal = 0;
		echo '</div><div class="row">';
		$flag_order = $TAB['FIELD_G_ORDER'];
	}
	if($TAB['FIELD_G_OFFSET'] > 0  AND $TAB['FIELD_G_OFFSET'] > $offset_cal){
			$offset_x = $TAB['FIELD_G_OFFSET'] - $offset_cal;
			$class_offset = " offset-md-".$offset_x;
			$offset_cal += $offset_x;
	}

if($TAB["FIELD_G_TYPE"] == ""){
if($TAB_START == $TAB["FIELD_G_ID"]){ 
echo '<div class="row"><div class="col-sm-12"><ul class="nav nav-tabs mb-3" id="myTab'.$W.'" role="tablist">';
echo implode('',$TAB_DATA);	
echo '</ul></div></div>';
echo '<div class="tab-content" id="myTabContent'.$W.'">';
}
	
	echo '<div class="tab-pane fade px-1';
	if($TAB_START == $TAB["FIELD_G_ID"]){ echo ' show active'; }
	echo '" ';
	if($TAB["FIELD_G_HIDDEN"] == "Y"){ echo ' style="display:none;" '; }
	echo ' id="tab_'.$TAB["FIELD_G_ID"].'" role="tabpanel" aria-labelledby="tab_'.$TAB["FIELD_G_ID"].'">';
}

echo '<div id="bsf_area_g_'.$TAB["FIELD_G_ID"].'" class="col-md-'.$TAB['FIELD_G_WIDTH'].$class_offset.'" ';
if($TAB["FIELD_G_HIDDEN"] == "Y"){ echo ' style="display:none;" '; }
echo '>';
$offset_cal += $TAB['FIELD_G_WIDTH']; 

if($TAB["FIELD_G_HIDE_LABEL"] == "" AND $TAB["FIELD_G_TYPE"] == "G"){ echo '<h5 class="py-2">'.$TAB['FIELD_G_NAME'].'</h5>'; }
?>
<div class="row">  
	<?php 
	if(isset($arr_wfs[$TAB['FIELD_G_ID']])){  
		bsf_show_form_area($arr_wfs[$TAB['FIELD_G_ID']],$rec_main,$WFD,$WF,$WF_TYPE,$WFS,$SHOW,$VIEW,$WFS_CONF,$INLINE_USE);
	} ?> 
</div>
<?php

echo '</div>';


if($TAB['FIELD_G_TYPE'] == ""){
	$flag_sub_order = "";
if(isset($TAB_SUB[$TAB["FIELD_G_ID"]])){
	if(count($TAB_SUB[$TAB["FIELD_G_ID"]]) > 0){
	echo "<div class=\"row\">";
	foreach($TAB_SUB[$TAB["FIELD_G_ID"]] as $TAB2){
		$class_offset_sub = "";
		if($flag_sub_order != $TAB2['FIELD_G_ORDER']){
			$offset_cal_sub = 0;
			echo "\n</div><div class=\"row\">\n";
			$flag_sub_order = $TAB2['FIELD_G_ORDER'];
		}
		if($TAB2['FIELD_G_OFFSET'] > 0  AND $TAB2['FIELD_G_OFFSET'] > $offset_cal_sub){
			$offset_x = $TAB2['FIELD_G_OFFSET'] - $offset_cal_sub;
			$class_offset_sub = " offset-md-".$offset_x;
			$offset_cal_sub += $offset_x;
		}
		echo '<div class="col-md-'.$TAB2['FIELD_G_WIDTH'].$class_offset_sub.'">';
		$offset_cal_sub += $TAB2['FIELD_G_WIDTH']; 
		if($TAB2["FIELD_G_HIDE_LABEL"] == ""){ echo '<h5 class="py-2">'.$TAB2['FIELD_G_NAME'].'</h5>'; }
		?>	<div class="row">  
				<?php
				if(isset($arr_wfs[$TAB2['FIELD_G_ID']])){  
				bsf_show_form_area($arr_wfs[$TAB2['FIELD_G_ID']],$rec_main,$WFD,$WF,$WF_TYPE,$WFS,$SHOW,$VIEW,$WFS_CONF,$INLINE_USE);
				} ?> 
			</div> 
		  <?php
		echo "</div>";
	}
	echo "</div>";
	}
}
}

if($TAB["FIELD_G_TYPE"] == ""){
	echo '</div>';
}
}

if(count($TAB_DATA) >0){ echo '</div>'; }
echo '</div>';
 
}
}
function bsf_show_form_area($sql_form,$rec_main,$WFD,$WF,$WF_TYPE,$WFS,$SHOW,$VIEW,$WFS_CONF,$innline_form_use){
$W = $rec_main['WF_MAIN_ID'];
$pk_field = $rec_main["WF_FIELD_PK"];
$g_pos = 0;
$align_pos = array('L'=>'text-start','C'=>'text-center','R'=>'text-lg-end');
$oper_arr = array("0"=>"==","1"=>">","2"=>">=","3"=>"<","4"=>"<=","5"=>"!=",""=>"=="); 
$bsf_script = "";
$WFS_FORM_FIELD_EDIT = array();
$WFS_FORM_FIELD_VIEW = array();
$WFSCONF = array();
if($WFS_CONF != ''){
	$WFSCONF = db::query_first("SELECT WFS_FORM_INPUT_SHOW,WFS_FORM_ADD_POPUP,WFS_FORM_FIELD_EDIT,WFS_FORM_FIELD_VIEW FROM WF_STEP_FORM WHERE WFS_ID = '".$WFS_CONF."'");
	if($WFSCONF['WFS_FORM_INPUT_SHOW'] == "M" AND $WFSCONF['WFS_FORM_ADD_POPUP'] == "Y"){
		if($WFSCONF['WFS_FORM_FIELD_EDIT'] != ""){
		$WFS_FORM_FIELD_EDIT = explode(',',$WFSCONF['WFS_FORM_FIELD_EDIT']);
		}
		if($WFSCONF['WFS_FORM_FIELD_VIEW'] != ""){
		$WFS_FORM_FIELD_VIEW = explode(',',$WFSCONF['WFS_FORM_FIELD_VIEW']);
		}
	}
}

foreach($sql_form as $BSF_DET){

	$WFS_FIELD_NAME = $BSF_DET["WFS_FIELD_NAME"];
	
	$class_left = "";
	$class_right = "";
	$class_required = "";
	$class_offset = "";
	$class_space = "";
	$class_nobr = "";
	$class_tooltip = "";
	$class_extra = "";	
	$class_input = "";	
	$right_data_val = "";
	$left_data_val = "";
	$data_list = array(); 
	$chk = 0;
	$style_display = "";
	$WFS = $BSF_DET["WFS_ID"];
	$NUM_SCRIPT = $BSF_DET["WFS_NUM_STEP_JS"]; 
	$NUM_THROW = $BSF_DET["WFS_NUM_STEP_THROW"];
	$NUM_ONCHANGE = $BSF_DET["WFS_NUM_ONCHANGE"];
	$NUM_ONCHANGE_SEND = $BSF_DET["WFS_NUM_ONCHANGE_SEND"];
	
	$BSF_DET["WFS_FIELD_NAME_ORI"] = $BSF_DET["WFS_FIELD_NAME"];
	$BSF_DET['SHOW'] = $SHOW;
	if($SHOW != ''){ $BSF_DET["WFS_FIELD_NAME"] = $BSF_DET["WFS_FIELD_NAME"].$SHOW; }
	
	//check form edit or view
	if($WF_TYPE=='F' AND $WFS_CONF != '' AND $WFSCONF['WFS_FORM_INPUT_SHOW'] == "M"){
		if(count($WFS_FORM_FIELD_EDIT)> 0 AND in_array($BSF_DET["WFS_ID"],$WFS_FORM_FIELD_EDIT)){
			$VIEW_F = "";
		}
		if(count($WFS_FORM_FIELD_VIEW)> 0 AND in_array($BSF_DET["WFS_ID"],$WFS_FORM_FIELD_VIEW)){
			$VIEW_F = "Y";
		}
	}
	
	/*  End ตัวแปร */
	
	/* Start ขึ้นบรรทัดใหม่  */	
	if($g_pos != $BSF_DET["WFS_ORDER"] AND ($SHOW=='' OR $innline_form_use == "Y")){ 
		$offset_cal = 0;
		echo '</div><div class="row">';
		$g_pos = $BSF_DET["WFS_ORDER"];
	}/* End ขึ้นบรรทัดใหม่  */	
		/* Start คำนวณ offset  */	
	if($BSF_DET["WFS_OFFSET"] > 0  AND ($SHOW=='' OR $innline_form_use == "Y")){
		if($BSF_DET["WFS_OFFSET"] > $offset_cal){
			$offset_x = $BSF_DET["WFS_OFFSET"] - $offset_cal;
			$class_offset = " offset-md-".$offset_x;
			$offset_cal += $offset_x;
		}
	}
	/* End คำนวณ offset  */
	/* Start required  */	
	if($BSF_DET["WFS_REQUIRED"] == "Y" AND ($VIEW == "" AND $VIEW_F == "")){
		$class_required = ' <span class="text-danger">*</span>';
	}
	/* End required  */
	if(($VIEW == "" AND $VIEW_F == "")){
		if($BSF_DET["WFS_HIDDEN_FORM"] == "Y"){
			$style_display = ' id="'.$BSF_DET["WFS_FIELD_NAME"].$SHOW.'_BSF_AREA" style="display:none" ';
		}elseif($BSF_DET["WFS_FIELD_NAME"] != ""){
			$style_display = ' id="'.$BSF_DET["WFS_FIELD_NAME"].$SHOW.'_BSF_AREA" ';
		}
	}else{
		if($BSF_DET["WFS_HIDDEN_FORM"] == "Y" OR $BSF_DET["WFS_HIDDEN_VIEW"] == "Y"){
			$style_display = ' id="'.$BSF_DET["WFS_FIELD_NAME"].$SHOW.'_BSF_AREA" style="display:none" ';
		}elseif($BSF_DET["WFS_FIELD_NAME"] != ""){
			$style_display = ' id="'.$BSF_DET["WFS_FIELD_NAME"].$SHOW.'_BSF_AREA" ';
		}
	}
	
	if($BSF_DET["WFS_NO_BR"] == 'Y'){ $class_nobr = ' nowrap_break'; } //NoBR

	//if($BSF_DET["FORM_MAIN_ID"] == "10"){ $BSF_DET["WFS_COLUMN_TYPE"] = "1"; }
	if($BSF_DET["FORM_MAIN_ID"] == "6" AND $BSF_DET["WFS_FILE_EXTEND_ALLOW"] != ''){ $class_space = "-c"; }
	if($BSF_DET["WFS_COMMENT"] != "" AND ($VIEW == "" AND $VIEW_F == "")){ $class_space = "-c"; } 
	if(($SHOW=='' OR $innline_form_use == "Y")){
		if($BSF_DET["WFS_COLUMN_TYPE"] == "2"){ //Class Left
			$class_left = 'col-md-'.$BSF_DET["WFS_COLUMN_LEFT"].' '.$align_pos[$BSF_DET["WFS_COLUMN_LEFT_ALIGN"]];
			$class_right = 'col-md-'.$BSF_DET["WFS_COLUMN_RIGHT"].' '.$align_pos[$BSF_DET["WFS_COLUMN_RIGHT_ALIGN"]].' wf-space-i'.$class_space;
		}else{
			$class_left = 'col-md-'.($BSF_DET["WFS_COLUMN_LEFT"]+$BSF_DET["WFS_COLUMN_RIGHT"]).' '.$align_pos[$BSF_DET["WFS_COLUMN_LEFT_ALIGN"]].' wf-space-i'.$class_space;   
			$class_right = 'col-md-'.$BSF_DET["WFS_COLUMN_RIGHT"].' '.$align_pos[$BSF_DET["WFS_COLUMN_RIGHT_ALIGN"]];
		}
//Start Div		
	echo '<div '.$style_display.' class="'.$class_left.$class_offset.'">';
	
	
	if($BSF_DET["FORM_MAIN_ID"] == "16" AND $BSF_DET["WFS_COLUMN_TYPE"] == "1" AND ($BSF_DET["WFS_INPUT_FORMAT"]=="M" OR $BSF_DET["WFS_INPUT_FORMAT"]=="D")){ 
		//
	}elseif($BSF_DET["FORM_MAIN_ID"] == "8"){ 
		echo bsf_form_itextshow($BSF_DET,$WF,$rec_main,'L');
	}elseif(!(($BSF_DET["FORM_MAIN_ID"] == "5" AND $BSF_DET["WFS_INPUT_FORMAT"] == "O") OR $BSF_DET["FORM_MAIN_ID"] == "10")){
	echo '<label class="form-label'.$class_nobr.'"';
	if(($VIEW == "" AND $VIEW_F == "")){
	echo 'for="'.$BSF_DET["WFS_FIELD_NAME"].$SHOW.'"';
	}
	echo '>';
	echo $BSF_DET["WFS_NAME"].$class_required;
	echo '</label>';
	}
	
		if($BSF_DET["WFS_COLUMN_TYPE"] == "2"){ 
		echo "</div>\n<div ".$style_display.' class="'.$class_right.'">'; 
		}
	}
	if(($VIEW == "" AND $VIEW_F == "") AND $BSF_DET["FORM_MAIN_ID"] != "3"){
		if($BSF_DET["WFS_TXT_BEFORE_INPUT"] != "" OR $BSF_DET["WFS_TXT_AFTER_INPUT"] != ""){ echo '<div class="input-group input-group-sm">'; }
		if($BSF_DET["WFS_TXT_BEFORE_INPUT"] != ""){ echo '<div class="input-group-text">'.wf_convert_var($BSF_DET["WFS_TXT_BEFORE_INPUT"],'Y').'</div>'; }
	}else{
		//if($BSF_DET["WFS_TXT_BEFORE_INPUT"] != ""){ echo $BSF_DET["WFS_TXT_BEFORE_INPUT"].' '; }
	}
	if(($VIEW == "" AND $VIEW_F == "")){
		
		if($BSF_DET['FORM_MAIN_ID'] == "10"){
			if($BSF_DET["WFS_CODING_FORM"] != '' AND file_exists('../form/'.$BSF_DET["WFS_CODING_FORM"])){
					@include('../form/'.$BSF_DET["WFS_CODING_FORM"]);
				}
		}else{
			$WF = bsf_show_form_input($BSF_DET,$rec_main,$WFD,$WF,$WF_TYPE,$WFS,$SHOW);
		}
	}else{  
		if($BSF_DET['FORM_MAIN_ID'] == "10"){
			if($BSF_DET["WFS_CODING_VIEW"] != '' AND file_exists('../view/'.$BSF_DET["WFS_CODING_VIEW"])){
					@include('../view/'.$BSF_DET["WFS_CODING_VIEW"]);
				}
		}else{
			echo bsf_show_itext($BSF_DET['WFS_FIELD_NAME'],$rec_main,$WF,$BSF_DET,'Y'); 
		}
	} 
	if(($VIEW == "" AND $VIEW_F == "") AND $BSF_DET["FORM_MAIN_ID"] != "3"){
		if($BSF_DET["WFS_TXT_AFTER_INPUT"] != ""){ echo '<div class="input-group-text">'.wf_convert_var($BSF_DET["WFS_TXT_AFTER_INPUT"],'Y').'</div>'; }
		if($BSF_DET["WFS_TXT_BEFORE_INPUT"] != "" OR $BSF_DET["WFS_TXT_AFTER_INPUT"] != ""){ echo '</div>'; }
	}else{
		//if($BSF_DET["WFS_TXT_AFTER_INPUT"] != ""){ echo ' '.$BSF_DET["WFS_TXT_AFTER_INPUT"]; }
	}
	if(($VIEW == "" AND $VIEW_F == "")){
		if($BSF_DET["WFS_COMMENT"] != ""){ echo '<small class="form-text text-muted">'.nl2br($BSF_DET["WFS_COMMENT"]).'</small>'; }
	}
	
// Script	
	if($BSF_DET["WFS_NUM_STEP_JS"] > 0){
	$default = $WF[$BSF_DET["WFS_FIELD_NAME_ORI"]];
	if($default == ""){
		$default = $BSF_DET["WFS_DEFAULT_DATA"];
	}
	$default = bsf_default_var($default,$WF,'');
	if($BSF_DET["FORM_MAIN_ID"]=="5"){
	$bsf_script .= 'bsf_chk_obj'.$WF_TYPE.'_'.$WFS."(document.getElementById('".$BSF_DET["WFS_FIELD_NAME"]."'));";
	}else{
	$bsf_script .= 'bsf_change_obj'.$WF_TYPE.'_'.$WFS."('".$default."');";
	}
	}
	if($BSF_DET["WFS_NUM_ONCHANGE_SEND"] > 0 AND $BSF_DET["FORM_MAIN_ID"]=="4"){ 
	$bsf_script .= 'bsf_change_process'.$WF_TYPE.'_'.$WFS."('".$default."');";
	}
	if($BSF_DET["WFS_NUM_STEP_JS"] > 0){ 
		$sql_js = db::query("select * from WF_STEP_JS where WFS_ID = '".$WFS."' order by WFSJ_ID"); 
		?> 
	  <script type="text/javascript">
	  <?php if($BSF_DET["FORM_MAIN_ID"]!="5"){ ?>
		function bsf_change_obj<?php echo $WF_TYPE; ?>_<?php echo $WFS; ?>(vals){
			<?php 
			while($CH = db::fetch_array($sql_js)){
			$WFSJ_VAR = bsf_show_field($W,$WF,$CH["WFSJ_VAR"],$WF_TYPE);
			if($CH["WFSJ_OPERATE"]=='1' OR $CH["WFSJ_OPERATE"]=='2' OR $CH["WFSJ_OPERATE"]=='3' OR $CH["WFSJ_OPERATE"]=='4'){
				?>
				var vals_txt = vals.replace(/,/g , "");
				<?php
			}else{
				$WFSJ_VAR = "'".$WFSJ_VAR."'";
				?>
				var vals_txt = vals; 
				<?php
			}
			?>
			if(vals_txt <?php echo $oper_arr[$CH["WFSJ_OPERATE"]];?> <?php echo $WFSJ_VAR; ?>){ 
			<?php 
			if($CH["WFSJ_SHOW"] != ""){ 
				$e = explode(",",$CH["WFSJ_SHOW"]); 
				foreach($e as $vals){ 
					echo "$(\"[id^=".$vals."_BSF_AREA]\").show();"; 
				} 
			} 
			if($CH["WFSJ_HIDE"] != ""){ 
				$e = explode(",",$CH["WFSJ_HIDE"]); 
				foreach($e as $vals){ 
					echo "$(\"[id^=".$vals."_BSF_AREA]\").hide();"; 
				} 
			} 
			if($CH["WFSJ_JAVASCRIPT"] != ""){ 
				$search  = array();
				$replace = array(); 
				preg_match_all("/(##)([a-zA-Z0-9_]+)(!!)/", $CH["WFSJ_JAVASCRIPT"], $matches, PREG_SET_ORDER);
				foreach ($matches as $val){ 
					$value = bsf_show_itext($val[2],$rec_main,$WF,$BSF_DET); 
					array_push($search,"##".$val[2]."!!"); 
					array_push($replace,$value); 
				}
				$contents = str_replace($search, $replace, $CH["WFSJ_JAVASCRIPT"]);
				echo $data = htmlspecialchars_decode(str_replace("##!!", "", $contents), ENT_QUOTES); 
			} 
			?> 
			}
			<?php } ?> 
		}
		<?php }else{ ?> 
		function bsf_chk_obj<?php echo $WF_TYPE; ?>_<?php echo $WFS; ?>(obj){
		<?php 
			while($CH = db::fetch_array($sql_js)){
			$WFSJ_VAR = bsf_show_field($W,$WF,$CH["WFSJ_VAR"],$WF_TYPE);
			if($CH["WFSJ_OPERATE"]=='0' OR $CH["WFSJ_OPERATE"]=='5'){
				?>
			if(obj.value=='<?php echo $WFSJ_VAR; ?>'){
			if(obj.checked==<?php if($CH["WFSJ_OPERATE"]=='0' OR $CH["WFSJ_OPERATE"]==''){ echo 'true'; }else{ echo 'false'; } ?>){
			<?php 
			if($CH["WFSJ_SHOW"] != ""){ 
				$e = explode(",",$CH["WFSJ_SHOW"]); 
				foreach($e as $vals){ 
					echo "$(\"[id^=".$vals."_BSF_AREA]\").show();"; 
				} 
			} 
			if($CH["WFSJ_HIDE"] != ""){ 
				$e = explode(",",$CH["WFSJ_HIDE"]); 
				foreach($e as $vals){ 
					echo "$(\"[id^=".$vals."_BSF_AREA]\").hide();"; 
				} 
			} 
			if($CH["WFSJ_JAVASCRIPT"] != ""){ 
				$search  = array();
				$replace = array(); 
				preg_match_all("/(##)([a-zA-Z0-9_]+)(!!)/", $CH["WFSJ_JAVASCRIPT"], $matches, PREG_SET_ORDER);
				foreach ($matches as $val){ 
					$value = bsf_show_itext($val[2],$rec_main,$WF,$BSF_DET); 
					array_push($search,"##".$val[2]."!!"); 
					array_push($replace,$value); 
				}
				$contents = str_replace($search, $replace, $CH["WFSJ_JAVASCRIPT"]);
				echo $data = htmlspecialchars_decode(str_replace("##!!", "", $contents),ENT_QUOTES); 
			} 
			?> 
			}}
			<?php }} ?> 
		}
		<?php } ?>
		</script>
	<?php
		}
	if($BSF_DET["WFS_INPUT_EVENT"] != "" AND $BSF_DET["WFS_JAVASCRIPT_EVENT"] != "" AND $BSF_DET["WFS_FIELD_NAME"] !=''){
	?>
	<script type="text/javascript">
	$(document).ready(function(){
		$("#<?php echo $BSF_DET["WFS_FIELD_NAME"]; ?>").<?php echo $BSF_DET["WFS_INPUT_EVENT"]; ?>(function (){
			<?php
				$search  = array();
				$replace = array(); 
				preg_match_all("/(##)([a-zA-Z0-9_]+)(!!)/", $BSF_DET["WFS_JAVASCRIPT_EVENT"], $matches, PREG_SET_ORDER);
				foreach ($matches as $val){ 
					$value = bsf_show_itext($val[2],$rec_main,$WF,$BSF_DET); 
					array_push($search,"##".$val[2]."!!"); 
					array_push($replace,$value); 
				}
				$contents = str_replace($search, $replace, $BSF_DET["WFS_JAVASCRIPT_EVENT"]);
				echo $data = htmlspecialchars_decode(str_replace("##!!", "", $contents), ENT_QUOTES); 
				?>
		});
	})
	</script>
	<?php
	}

	if($BSF_DET["WFS_FIELD_NAME"] !='' AND $BSF_DET["WFS_NUM_ONCHANGE_SEND"] > 0){
		$sql_change = db::query("SELECT WFS_ID FROM WF_ONCHANGE WHERE WFS_FIELD_SEND = '".$BSF_DET["WFS_FIELD_NAME_ORI"]."' AND WF_MAIN_ID = '".$W."' AND WF_TYPE = '".$WF_TYPE."' GROUP BY WFS_ID");
		while($ONC = db::fetch_array($sql_change)){
			if($ONC['WFS_ID'] != ''){
				$sql_change_obj = db::query("select WFS_ID,WFS_NAME,WFS_FIELD_NAME,WFS_OPTION_SELECT2,WFS_OPTION_SELECT_DATA,FORM_MAIN_ID from WF_STEP_FORM where WFS_ID = '".$ONC['WFS_ID']."' AND WFD_ID = '".$WFD."' ");
				$ONC_O = db::fetch_array($sql_change_obj);

				if($ONC_O['WFS_ID'] != ''){
					if($BSF_DET["FORM_MAIN_ID"] == "4"){
						
						?><script>function bsf_change_process<?php echo $WF_TYPE; ?>_<?php echo $WFS; ?>(val){
							<?php
					}else{
						?><script>$('#<?php echo $BSF_DET["WFS_FIELD_NAME"]; ?>').change(function(){
							var val = $('#<?php echo $BSF_DET["WFS_FIELD_NAME"]; ?>').val();
							<?php
					}
					
						$onchtxt = "";
						$sql_change2 = db::query("SELECT WFS_FIELD_SEND FROM WF_ONCHANGE WHERE WFS_ID = '".$ONC['WFS_ID']."' AND WF_MAIN_ID = '".$W."' AND WF_TYPE = '".$WF_TYPE."'");
						while($ONC2 = db::fetch_array($sql_change2)){
							if($ONC2['WFS_FIELD_SEND'] == $BSF_DET["WFS_FIELD_NAME"]){
							echo "var ".$ONC2['WFS_FIELD_SEND']." = val;\n";
							}else{
							echo "var ".$ONC2['WFS_FIELD_SEND']." = $('#".$ONC2['WFS_FIELD_SEND'].$SHOW."').val();\n";	
							}
							echo "if(".$ONC2['WFS_FIELD_SEND']."==null){ ".$ONC2['WFS_FIELD_SEND']." = ''; }\n"; 
							$onchtxt .= "+'&".$ONC2['WFS_FIELD_SEND']."='+".$ONC2['WFS_FIELD_SEND'];
						}
						?>
						var url = "<?php if ($_SESSION["WF_USER_ID"] != ""){ echo '../workflow/'; } ?>wf_onchange.php";
						var dataString = 'TARGET=<?php echo $ONC['WFS_ID']; ?>&WFR_ID=<?php echo $WF[$rec_main["WF_FIELD_PK"]]; ?>&WF_DEFAULT=<?php echo $WF[$ONC_O["WFS_FIELD_NAME"]]; ?>&VAL='+val+'&FORM_MAIN_ID=<?php echo $ONC_O['FORM_MAIN_ID']; ?>&W=<?php echo $ONC_O['WFS_OPTION_SELECT_DATA']; ?>'<?php echo $onchtxt; ?>;
						$.ajax({
							type: "GET",
							url: url,
							data: dataString, // serializes the form's elements.
							cache: false, 
							success: function(html)
							{
									<?php if($ONC_O['FORM_MAIN_ID']=="4"){ ?>
									$('#WF_RADIO<?php echo $ONC_O['WFS_FIELD_NAME'].$SHOW; ?>').html(html); 
									<?php }elseif($ONC_O['FORM_MAIN_ID']=="5"){ ?>
									$('#WF_CHKBOX<?php echo $ONC_O['WFS_FIELD_NAME'].$SHOW; ?>').html(html); 
									<?php }elseif($ONC_O['FORM_MAIN_ID']=="7"){ ?>
									$('#WF_HIDDEN<?php echo $ONC_O['WFS_FIELD_NAME'].$SHOW; ?>').html(html);
									<?php }else{ ?>
									var modalsid = $('select#<?php echo $ONC_O['WFS_FIELD_NAME'].$SHOW; ?>').closest('.modal').attr('id');
									$('select#<?php echo $ONC_O['WFS_FIELD_NAME'].$SHOW; ?>').html('<option value="" disabled="" selected="">เลือก<?php echo $ONC_O['WFS_NAME']; ?></option>').select2();
									/*$('select#<?php echo $ONC_O['WFS_FIELD_NAME'].$SHOW; ?>').select2({
										allowClear: true,
										data: html
									});*/
									if(!modalsid){ 
									$('select#<?php echo $ONC_O['WFS_FIELD_NAME'].$SHOW; ?>').select2({
											allowClear: true,
											data: html,
											width: '100%'
										});
									}else{
										$('select#<?php echo $ONC_O['WFS_FIELD_NAME'].$SHOW; ?>').select2({
											allowClear: true,
											data: html,
											width: '100%',
											dropdownParent: $("#" + modalsid)
										});
									}
									$('select#<?php echo $ONC_O['WFS_FIELD_NAME'].$SHOW; ?>').select2("open"); 
									$('select#<?php echo $ONC_O['WFS_FIELD_NAME'].$SHOW; ?>').select2("close");

									<?php if($ONC_O['WFS_OPTION_SELECT2'] != "Y"){ ?>
									$('#<?php echo $ONC_O['WFS_FIELD_NAME'].$SHOW; ?>').select2("destroy");
									<?php } ?>
									<?php } ?>
							}
							});
					<?php 
					if($BSF_DET["FORM_MAIN_ID"] == "4"){ echo "}"; }else{ echo "});"; } ?>
					</script><?php
				}
			}
		}
	}
	if($NUM_THROW > 0 AND $BSF_DET["FORM_MAIN_ID"] == "9"){ //throw
		?><span id="<?php echo $BSF_DET['WFS_FIELD_NAME']; ?>_LOAD_AREA<?php echo $BSF_DET['WFS_ID']; ?>"></span>
	<script>
		$("#<?php echo $BSF_DET['WFS_FIELD_NAME'] ?>").change(function (){ 
			var WFS = '<?php echo $BSF_DET['WFS_ID']; ?>';
			var VAL = $(this).val();
			var dataString = 'WFS='+WFS+'&VAL='+VAL+'&SHOW=<?php echo $SHOW; ?>';
			
			$.ajax({
				type: "GET",
				url: "<?php if($_SESSION["WF_USER_ID"] != ""){ echo '../workflow/'; } ?>wf_onthrow.php",
				data: dataString,
				cache: false,
				success: function(html){
					$('#<?php echo $BSF_DET['WFS_FIELD_NAME']; ?>_LOAD_AREA<?php echo $BSF_DET['WFS_ID']; ?>').html(html);
				} 
			});
		});
	</script>	
		<?php
		}
	//Date change function
	if($BSF_DET["WFS_ONCHANGE"] != "" AND $BSF_DET["WFS_FIELD_NAME"] != "" AND ($BSF_DET["FORM_MAIN_ID"]=="1" OR $BSF_DET["FORM_MAIN_ID"]=="2" OR $BSF_DET["FORM_MAIN_ID"]=="3")){
		$arr = explode("@",$BSF_DET["WFS_ONCHANGE"]);
		$txt_java = "";
		$txt_java1 = "";
		foreach ($arr as &$value){
		$string = "@".$value;
		$matches = array();
		$pattern = '/(@[a-zA-Z_][a-zA-Z0-9\$_.]*)?/';
		preg_match($pattern,$string,$matches);
			if($matches[0] != ""){
			$obj_orginal = substr($matches[0], 1);
			$obj = $obj_orginal.$SHOW;
			$txt_java .= "$('#".$obj."').blur(function(){ get_".$BSF_DET["WFS_FIELD_NAME"]."(); });\n";
			$txt_java1 .= "+'&".$obj_orginal."='+$('#".$obj."').val()";
			}
		}
	?>
	<script type="text/javascript">
		$(document).ready(function(){
			<?php echo $txt_java; ?>
			function get_<?php echo $BSF_DET["WFS_FIELD_NAME"]; ?>(){
				var dataString = 'WF_ONCTYPE=<?php echo $BSF_DET["FORM_MAIN_ID"]; ?>&WFS_INPUT_FORMAT=<?php echo $BSF_DET['WFS_INPUT_FORMAT']; ?>&CFlag=<?php echo rawurlencode($BSF_DET["WFS_ONCHANGE"]); ?>'<?php echo $txt_java1; ?>;
				$.ajax({
					type: "GET",
					url: "<?php if($_SESSION["WF_USER_ID"] != ""){ echo '../workflow/'; } ?>date_function.php",
					data: dataString,
					cache: false,
					success: function(html){
						$('#<?php echo $BSF_DET["WFS_FIELD_NAME"]; ?>').val(html);
						$('#<?php echo $BSF_DET["WFS_FIELD_NAME"]; ?>').trigger('blur');
					}
				 });
			}
			<?php if($txt_java != "" AND $BSF_DET["WFS_OPTION_RADIO_CLEAR"] != 'Y'){ echo "$('#".$obj."').trigger('blur');"; }; ?>
		})
	</script>
	<?php
	}
	
	
	
	
	
	if(($SHOW=='' OR $innline_form_use == "Y")){
	echo "</div>\n"; //End Div
	$offset_cal += ($BSF_DET["WFS_COLUMN_LEFT"]+$BSF_DET["WFS_COLUMN_RIGHT"]);
	}
} //End $BSF_DET


if($bsf_script != ''){
	echo "<script>".$bsf_script."</script>";
}
}
function bsf_show_form_input($BSF_DET,$rec_main,$WFD,$WF,$WF_TYPE,$WFS,$SHOW){ 
	$default = $WF[$BSF_DET["WFS_FIELD_NAME_ORI"]];
	if($default == "" AND $BSF_DET["WFS_DEFAULT_DATA"] != ''){
		$default = bsf_default_var($BSF_DET["WFS_DEFAULT_DATA"],$WF,'');
		$WF[$BSF_DET["WFS_FIELD_NAME_ORI"]] = $default; 
	}
	$W = $rec_main['WF_MAIN_ID'];
	$BSF_DET['SHOW'] = $SHOW;
	switch($BSF_DET["FORM_MAIN_ID"]){
			case '1': //textbox
				echo bsf_form_itext($BSF_DET,$WF,$rec_main);
				break;
			case '2': //textarea 
				echo bsf_form_itext($BSF_DET,$WF,$rec_main);
				break;
			case '3': //date
				echo bsf_form_idate($BSF_DET,$WF,$rec_main);
				break;
			case '4': //radio  
				$ONCHANGE = '';
				echo '<span id="WF_RADIO'.$BSF_DET['WFS_FIELD_NAME'].'">';
				echo bsf_form_iradio($BSF_DET,$WF,$rec_main);
				echo '</span>';
				break;
			case '5': //checkbox
				echo '<span id="WF_CHKBOX'.$BSF_DET['WFS_FIELD_NAME'].'">';
				echo bsf_form_icheck($BSF_DET,$WF,$rec_main,$WF_TYPE);
				echo '</span>';
				break;
			case '6': //browsefile
				echo bsf_form_ifile($BSF_DET,$WF,$rec_main);
				break;
			case '7': //hidden
				$ONCHANGE = ''; 
				if($BSF_DET["WFS_FIELD_NAME"] !='' AND $BSF_DET["WFS_NUM_ONCHANGE"] > 0){
					$ONCHANGE = 'Y'; 
					$sql_change = db::query("SELECT WFS_FIELD_SEND FROM WF_ONCHANGE WHERE WFS_ID = '".$BSF_DET["WFS_ID"]."' ");
					$WF_PSEND = "";
					while($ONC = db::fetch_array($sql_change)){ 
						$WF_PSEND .= "&".$ONC['WFS_FIELD_SEND']."=".$WF[$ONC['WFS_FIELD_SEND']];
					} 
				} 
				echo '<span id="WF_HIDDEN'.$BSF_DET['WFS_FIELD_NAME'].'">'; 
				echo bsf_form_ihidden($BSF_DET,$WF,$rec_main,$ONCHANGE,$WF_PSEND);
				echo '</span>';
				break;
			case '8': //text
				echo bsf_form_itextshow($BSF_DET,$WF,$rec_main,'R');
				break;
			case '9': //select
			case '11': //Province
			case '12': //Amphur
			case '13': //Tambon
				echo bsf_form_iselect($BSF_DET,$WF,$rec_main);
				break;
			case '10': //coding  
				if($BSF_DET["WFS_CODING_FORM"] != '' AND file_exists('../form/'.$BSF_DET["WFS_CODING_FORM"])){
					@include('../form/'.$BSF_DET["WFS_CODING_FORM"]);
				}
				echo bsf_form_icoding($BSF_DET,$WF,$rec_main); 
				break; 
			case '14': //Zipcode
				echo bsf_form_itext($BSF_DET,$WF,$rec_main);
				break;
			case '15': //View
				if($BSF_DET["WFS_OPTION_SELECT_DATA"] != ''){
					$sql_detail = db::query("select WF_MAIN_ID from WF_DETAIL where WFD_ID = '".$BSF_DET["WFS_OPTION_SELECT_DATA"]."'");
					$rec_detail = db::fetch_array($sql_detail); 
					echo bsf_show_form($rec_detail['WF_MAIN_ID'], $BSF_DET["WFS_OPTION_SELECT_DATA"], $WF, 'W', '', '', 'Y');
				 
				}
				break;
			case '16': //Form
				bsf_form_iform($BSF_DET,$WF,$rec_main,$WFD);
				break;
			case '17': //Year
				echo bsf_form_iyear($BSF_DET,$WF,$rec_main,$WFD);
				break;
		} 
		return $WF;
}
function bsf_default_var($txt,$WF,$en){
	if($txt != ""){
		if( strpos($txt,'@') !== false) {
			$wfgb_year = (date("Y")+543);
			if(date("m") >= 10){
				$wfgb_bdyear = (date("Y")+544);
			}else{
				$wfgb_bdyear = (date("Y")+543);
			}
			$wfgb_fulltoday = conDateText(date('Y-m-d'),'F');
			$wfgb_shorttoday = conDateText(date('Y-m-d'),'S');
			
			if($en == ""){
				$txt = preg_replace("/@today/i",db2date(date('Y-m-d')),$txt);
			}else{
				$txt = preg_replace("/@today/i",db2date_en(date('Y-m-d')),$txt);
			}
				$txt = preg_replace("/@year/i",$wfgb_year,$txt);
				$txt = preg_replace("/@adyear/i",date("Y"),$txt);
				$txt = preg_replace("/@budgetyear/i",$wfgb_bdyear,$txt);
				$txt = preg_replace("/@fulltoday/i",$wfgb_fulltoday,$txt);
				$txt = preg_replace("/@shorttoday/i",$wfgb_shorttoday,$txt);
			preg_match_all("/(@@)([a-zA-Z0-9_]+)(!!)/", $txt, $new_sql1, PREG_SET_ORDER); 
			foreach ($new_sql1 as $val_new) 
			{ 
				$txt = str_replace("@@".$val_new[2]."!!",$_SESSION[$val_new[2]],$txt); 
			}
			
			preg_match_all("/(@#)([a-zA-Z0-9_]+)(!!)/", $txt, $new_sql2, PREG_SET_ORDER); 
			foreach ($new_sql2 as $val_new) 
			{
				$txt = str_replace('@#'.$val_new[2].'!!',$_GET[$val_new[2]],$txt); 
			}
		}
		if( strpos($txt,'##') !== false) { 
			$search  = array();
            $replace = array(); 
			preg_match_all("/(##)([a-zA-Z0-9_]+)(!!)/", $txt, $matches, PREG_SET_ORDER);
			 foreach ($matches as $val){ 
				array_push($search,"##".$val[2]."!!"); 
				array_push($replace,$WF[$val[2]]); 
			 } 

			$txt = str_replace($search, $replace, $txt); 
		}
	}
	return $txt;
}
function bsf_form_itextshow($BSF_DET,$WF,$rec_main,$side){
	$W = $rec_main['WF_MAIN_ID']; 
	$mtext_label_s = '<span class="badge text-start f-14 bg-light-dark">';
	$mtext_label_e = '</span>';
	$flag = '';
	if($side == "L"){ 
		$text = $BSF_DET["WFS_TXT_C_LEFT"];
		if($BSF_DET["WFS_TXT_C_LEFT_HIGHLIGHT"] == "Y"){
			//$flag = 'Y';
			$text = $mtext_label_s.$text.$mtext_label_e;
		}
		if($text != ""){
			$text = '<label class="form-label">'.$text.'</label>';
		} 
	}
	if($side == "R"){ 
		$text = $BSF_DET["WFS_TXT_C_RIGHT"];
		if($BSF_DET["WFS_TXT_C_RIGHT_HIGHLIGHT"] == "Y"){
			//$flag = 'Y';
			$text = $mtext_label_s.$text.$mtext_label_e;
		}
	}
	if($text != "" and str_contains($text, '##')) {
		$search  = array();
		$replace = array();
		$value = '';
		preg_match_all("/(##)([a-zA-Z0-9_]+)(!!)/", $text, $matches, PREG_SET_ORDER);		
		foreach ($matches as $val) { 
		$form_step = db::query_first("SELECT WSF.* FROM WF_STEP_FORM WSF WHERE WSF.WF_MAIN_ID = '" . $W . "' AND WSF.WF_TYPE = '" . $rec_main["WF_TYPE"] . "' AND WSF.WFS_FIELD_NAME ='" . $val[2] . "' ORDER BY WFS_MAIN_SHOW ASC");
		$value = bsf_show_itext($val[2], $rec_main, $WF, $form_step,$flag);
		if($form_step['FORM_MAIN_ID'] == '2'){ $value = nl2br($value); }
		array_push($search, "##" . $val[2] . "!!");
		array_push($replace, $value); 
		}
		$contents = str_replace($search, $replace, $text);
		$text = str_replace("##!!", "", $contents); 
	} 
	return $text;
}
function bsf_form_itext($BSF_DET,$WF,$rec_main){
    
    global $system_conf;

	$html = "";
    $class_extra = "";
	$W = $rec_main['WF_MAIN_ID'];
	$default = $WF[$BSF_DET["WFS_FIELD_NAME_ORI"]];
	if($default == ""){
		$default = $BSF_DET["WFS_DEFAULT_DATA"];
	}
	$default = bsf_default_var($default,$WF,'');
	$name = $BSF_DET["WFS_FIELD_NAME"];
	$input_itype = 'text';
	$class_input = "form-control"; 
	if($BSF_DET["WFS_DEFINE_CLASS"] != ''){
	$class_input .= ' '.str_replace(".","",$BSF_DET["WFS_DEFINE_CLASS"]);
	} 
	if(trim((string)$BSF_DET["WFS_PLACEHOLDER"]) != ""){ 
	$class_extra .= ' placeholder ="'.$BSF_DET["WFS_PLACEHOLDER"].'" '; 
	}
	if($BSF_DET["WFS_NUM_STEP_JS"] > 0 OR $BSF_DET["WFS_NUM_ONCHANGE_SEND"] > 0){  
	$class_extra .= ' onBlur="';
		if($BSF_DET["WFS_NUM_STEP_JS"] > 0){ 
		$class_extra .= 'bsf_change_obj'.$BSF_DET['WF_TYPE'].'_'.$BSF_DET["WFS_ID"].'(this.value);'; 
		}
		if($BSF_DET["WFS_NUM_ONCHANGE_SEND"] > 0){
		//$class_extra .= 'bsf_change_process'.$rec_main['WF_TYPE'].'_'.$BSF_DET["WFS_ID"].'(this.value);'; 
		}
	$class_extra .= '"';
	}
	
	
	if($BSF_DET["WFS_REQUIRED"] == "Y"){
		if($BSF_DET['WFS_NAME']==""){
			$r_txt = "กรุณากรอกข้อมูล";
		}else{
			$r_txt = "กรุณากรอก".$BSF_DET['WFS_NAME'];
		}
	//	$class_extra .= ' oninvalid="this.setCustomValidity(\''.$r_txt.'\')" oninput="this.setCustomValidity(\'\')" '; 
		$class_extra .= ' required aria-required="true" '; 
	}
	if(trim((string)$BSF_DET["WFS_TOOLTIP"]) != ""){
	$class_extra .= ' data-bs-toggle="tooltip" data-bs-placement="top" data-bs-original-title="'.$BSF_DET["WFS_TOOLTIP"].'"';
	}
	if($BSF_DET["WFS_READONLY"] == 'Y'){
	$class_extra .= ' readonly="true" ';
	}
	if($BSF_DET["WFS_DISABLE"] == 'Y'){
	$class_extra .= ' disabled="true" ';
	}
	if($BSF_DET["WFS_CHECK_DUP"] == "Y"){ 
	$class_input .= ' wf_check_dup';
	}
	if($BSF_DET['WFS_MAX_LENGTH'] != 0){ $class_extra .= ' maxlength="'.$BSF_DET['WFS_MAX_LENGTH'].'"';  $class_input .= '  max-textarea'; }
	if($BSF_DET['WFS_OPTION_TXT_HEIGHT'] != 0){ $class_extra .= ' style="height: '.$BSF_DET['WFS_OPTION_TXT_HEIGHT'].'px"';}
	if($BSF_DET['WFS_INPUT_FORMAT'] == "C"){ $class_input .= ' idcard'; }
	if($BSF_DET['WFS_INPUT_FORMAT'] == "E"){ $class_input .= ' email'; $input_itype = 'email';}
	if($BSF_DET['WFS_INPUT_FORMAT'] == "N"){ $class_input .= ' autonumber'; $class_extra .= ' data-v-max="9999999999999999999" data-v-min="-9999999999999999999"'; }
	if($BSF_DET['WFS_INPUT_FORMAT'] == "N1"){ $class_input .= ' autonumber'; $class_extra .= ' data-v-max="9999999999999999999.0" data-v-min="-9999999999999999999.0"'; }
	if($BSF_DET['WFS_INPUT_FORMAT'] == "N2"){ $class_input .= ' autonumber'; $class_extra .= ' data-v-max="9999999999999999999.00" data-v-min="-9999999999999999999.00"'; }
	if($BSF_DET['WFS_INPUT_FORMAT'] == "N3"){ $class_input .= ' autonumber'; $class_extra .= ' data-v-max="9999999999999999999.000" data-v-min="-9999999999999999999.000"'; }
	if($BSF_DET['WFS_INPUT_FORMAT'] == "N4"){ $class_input .= ' autonumber'; $class_extra .= ' data-v-max="9999999999999999999.0000" data-v-min="-9999999999999999999.0000"'; }
	if($BSF_DET['WFS_INPUT_FORMAT'] == "N5"){ $class_input .= ' autonumber'; $class_extra .= ' data-v-max="9999999999999999999.00000" data-v-min="-9999999999999999999.00000"'; }
	if($BSF_DET['WFS_INPUT_FORMAT'] == "N6"){ $class_input .= ' autonumber'; $class_extra .= ' data-v-max="9999999999999999999.000000" data-v-min="-9999999999999999999.000000"'; }
	if($BSF_DET['WFS_INPUT_FORMAT'] == "TU"){ $class_input .= ' text-uppercase'; }
	if($BSF_DET['WFS_INPUT_FORMAT'] == "TL"){ $class_input .= ' text-lowercase'; }
	if($BSF_DET['WFS_INPUT_FORMAT'] == "TC"){ $class_input .= ' text-capitalize'; }
	if($BSF_DET['WFS_INPUT_FORMAT'] == "C"){ $class_input .= ' idcard'; }
	if($BSF_DET['WFS_INPUT_FORMAT'] == "P"){ $input_itype = 'password'; }
	if($BSF_DET['WFS_INPUT_FORMAT'] == "I"){ $input_itype = 'number'; }


	if($BSF_DET['WFS_INPUT_FORMAT'] == "ED"){
		$class_extra .= 'style="width:1px;height:24px;position: absolute;z-index:-1;';
		$html .= '<input type="text" id="' . $name . '_ED" class="'.$class_input.'" '.$class_extra.'" value="' . wf_convert_var($default,'') . '">';
		$class_extra = '';
	}

    if($BSF_DET["FORM_MAIN_ID"] =="2"){
		$html .= '<textarea name="' . $name . '" id="' . $name . '" class="' . $class_input . '" ' . $class_extra . '>' . wf_convert_var($default,'Y') . '</textarea>';
	}else{
	    $html .= '<input type="'.$input_itype.'" name="' . $name . '" id="' . $name . '" class="' . $class_input . '" value="' .  wf_convert_var($default,'') . '" ' . $class_extra . '>';
	}

    if($BSF_DET['WFS_INPUT_FORMAT'] == "ED"){
       wf_editor($name);
    }
	$html .= '<small id="DUP_' . $name . '_ALERT" class="form-text text-danger" style="display:none"></small>';
	if($BSF_DET["WFS_MASKING"] != ""){ $html .= '<script>$(function() { $("#'.$BSF_DET["WFS_FIELD_NAME"].'").inputmask({ mask: "'.$BSF_DET["WFS_MASKING"].'"}); });</script>'; }
    return $html;
}
function bsf_form_idate($BSF_DET,$WF,$rec_main){ 
	$W = $rec_main['WF_MAIN_ID'];
	if($WF[$BSF_DET["WFS_FIELD_NAME_ORI"]] != "" AND str_contains($WF[$BSF_DET["WFS_FIELD_NAME_ORI"]], '-')){
		if($BSF_DET["WFS_CALENDAR_EN"] == 'Y'){
		$default = db2date_en($WF[$BSF_DET["WFS_FIELD_NAME_ORI"]]);
		}else{
		$default = db2date($WF[$BSF_DET["WFS_FIELD_NAME_ORI"]]);	
		}
	}elseif($WF[$BSF_DET["WFS_FIELD_NAME_ORI"]] != "" AND str_contains($WF[$BSF_DET["WFS_FIELD_NAME_ORI"]], '/')){
		$default = $WF[$BSF_DET["WFS_FIELD_NAME_ORI"]];
	}
	
	
	if($default == ""){
		$default = $BSF_DET["WFS_DEFAULT_DATA"];
	}
	
	$name = $BSF_DET["WFS_FIELD_NAME"]; 
	$class_input = "form-control datepicker"; 
	if($BSF_DET["WFS_CALENDAR_EN"] == 'Y'){
	$class_input .= '_en';
		$default = bsf_default_var($default,$WF,'Y');
	}else{
		$default = bsf_default_var($default,$WF,'');
	}
	
	if($BSF_DET["WFS_DEFINE_CLASS"] != ''){
	$class_input .= ' '.str_replace(".","",$BSF_DET["WFS_DEFINE_CLASS"]);
	} 
	if($BSF_DET["WFS_NUM_STEP_JS"] > 0 OR $BSF_DET["WFS_NUM_ONCHANGE_SEND"] > 0){  
	$class_extra .= ' onBlur="';
		if($BSF_DET["WFS_NUM_STEP_JS"] > 0){ 
		$class_extra .= 'bsf_change_obj'.$BSF_DET['WF_TYPE'].'_'.$BSF_DET["WFS_ID"].'(this.value);'; 
		}
		if($BSF_DET["WFS_NUM_ONCHANGE_SEND"] > 0){
		//$class_extra .= 'bsf_change_process'.$BSF_DET['WF_TYPE'].'_'.$BSF_DET["WFS_ID"].'(this.value);'; 
		}
	$class_extra .= '"';
	}
	if(trim((string)$BSF_DET["WFS_PLACEHOLDER"]) != ""){ 
	$class_extra .= ' placeholder ="'.$BSF_DET["WFS_PLACEHOLDER"].'" '; 
	}else{
		if($BSF_DET["WFS_CALENDAR_EN"] == 'Y'){
			$class_extra .= ' placeholder="dd/mm/YYYY" '; 
		}else{
			$class_extra .= ' placeholder="วว/ดด/ปปปป" '; 
		}			
	}
	if($BSF_DET["WFS_REQUIRED"] == "Y"){
		if($BSF_DET['WFS_NAME']==""){
			$r_txt = "กรุณากรอกข้อมูล";
		}else{
			$r_txt = "กรุณากรอก".$BSF_DET['WFS_NAME'];
		}
		$class_extra .= ' required aria-required="true" '; 
	}
	if(trim((string)$BSF_DET["WFS_TOOLTIP"]) != ""){
	$class_extra .= ' data-bs-toggle="tooltip" data-bs-placement="top" data-bs-original-title="'.$BSF_DET["WFS_TOOLTIP"].'"';
	}
	if($BSF_DET["WFS_READONLY"] == 'Y'){
	$class_extra .= ' readonly="true" ';
	}
	if($BSF_DET["WFS_DISABLE"] == 'Y'){
	$class_extra .= ' disabled="true" ';
	}
	if($BSF_DET["WFS_CHECK_DUP"] == "Y"){ 
	$class_input .= ' wf_check_dup';
	} 
	$html = '<label class="input-group input-group-sm date">';
	if($BSF_DET["WFS_TXT_BEFORE_INPUT"] != ""){ $html .= '<div class="input-group-text">'.$BSF_DET["WFS_TXT_BEFORE_INPUT"].'</div>'; }
	$html .= '<input type="text" name="' . $name . '" id="'.$name.'" class="' . $class_input . '" autocomplete="off" value="' . $default . '" ' . $class_extra . ' /><span class="input-group-text"><i class="fas fa-calendar-alt"></i></span>';
	if($BSF_DET["WFS_TXT_AFTER_INPUT"] != ""){ $html .= '<div class="input-group-text">'.$BSF_DET["WFS_TXT_AFTER_INPUT"].'</div>'; }
	$html .= '</label>';

	$html .= '<small id="DUP_' . $name . '_ALERT" class="form-text text-danger" style="display:none"></small>';
	if($BSF_DET["WFS_SHOW_PROVINCE"] != ''){
	$html .= '<script>
$(document).ready(function () {
$("#'.$name.'").on("change", function(){
   var startVal = $("#'.$name.'").val();
   $("#'.$BSF_DET["WFS_SHOW_PROVINCE"].$BSF_DET['SHOW'].'").data("datepicker").setStartDate(startVal);
});
$("#'.$BSF_DET["WFS_SHOW_PROVINCE"].$BSF_DET['SHOW'].'").on("change", function(){
   var endVal = $("#'.$BSF_DET["WFS_SHOW_PROVINCE"].$BSF_DET['SHOW'].'").val();
   $("#'.$name.'").data("datepicker").setEndDate(endVal);
}); 
});
</script>';
	}
	if($BSF_DET["WFS_SHOW_AMPHUR"] != '' OR $BSF_DET["WFS_SHOW_TAMBON"] != ''){
	$html .= '<script>
$(document).ready(function () {
$("#'.$name.'").on("click", function(){ ';
if($BSF_DET["WFS_SHOW_AMPHUR"] != ''){
$html .= '$("#'.$name.'").data("datepicker").setStartDate("'.$BSF_DET["WFS_SHOW_AMPHUR"].$BSF_DET['SHOW'].'");';
}
if($BSF_DET["WFS_SHOW_TAMBON"] != ''){
$html .= '$("#'.$name.'").data("datepicker").setEndDate("'.$BSF_DET["WFS_SHOW_TAMBON"].$BSF_DET['SHOW'].'");';
}
$html .= '});
});
</script>';
	}
    return $html;
}
function bsf_form_iradio($BSF_DET,$WF,$rec_main){
	global $ONCHANGE;
	$W = $rec_main['WF_MAIN_ID'];
	$default = $WF[$BSF_DET["WFS_FIELD_NAME_ORI"]];
	$class_input_div = "";
	if($default == ""){
		$default = $BSF_DET["WFS_DEFAULT_DATA"];
	}
	$default = bsf_default_var($default,$WF,''); 
	$name = $BSF_DET["WFS_FIELD_NAME"]; 
	$class_input = "form-check-input";
	
	if($BSF_DET["WFS_DEFINE_CLASS"] != ''){
	$class_input .= ' '.str_replace(".","",$BSF_DET["WFS_DEFINE_CLASS"]);
	}
	if($BSF_DET["WFS_OPTION_NEW_LINE"] == "" OR $BSF_DET["WFS_OPTION_NEW_LINE"] == "N"){
		$class_input_div = ' form-check-inline';
	}
	if($BSF_DET["WFS_NUM_STEP_JS"] > 0 OR $BSF_DET["WFS_NUM_ONCHANGE_SEND"] > 0){  
	$class_extra .= ' onClick="';
		if($BSF_DET["WFS_NUM_STEP_JS"] > 0){ 
		$class_extra .= 'bsf_change_obj'.$BSF_DET['WF_TYPE'].'_'.$BSF_DET["WFS_ID"].'(this.value);'; 
		}
		if($BSF_DET["WFS_NUM_ONCHANGE_SEND"] > 0){
		$class_extra .= 'bsf_change_process'.$BSF_DET['WF_TYPE'].'_'.$BSF_DET["WFS_ID"].'(this.value);'; 
		}
	$class_extra .= '"';
	}
	if(trim((string)$BSF_DET["WFS_PLACEHOLDER"]) != ""){ 
	$class_extra .= ' placeholder ="'.$BSF_DET["WFS_PLACEHOLDER"].'" '; 
	}
	if($BSF_DET["WFS_REQUIRED"] == "Y"){
		if($BSF_DET['WFS_NAME']==""){
			$r_txt = "กรุณากรอกข้อมูล";
		}else{
			$r_txt = "กรุณากรอก".$BSF_DET['WFS_NAME'];
		}
		$class_extra .= ' required aria-required="true" '; 
	}
	if(trim((string)$BSF_DET["WFS_TOOLTIP"]) != ""){
	$class_extra .= ' data-bs-toggle="tooltip" data-bs-placement="top" data-bs-original-title="'.$BSF_DET["WFS_TOOLTIP"].'"';
	}
	if($BSF_DET["WFS_READONLY"] == 'Y'){
	$class_extra .= ' readonly="true" ';
	}
	if($BSF_DET["WFS_DISABLE"] == 'Y'){
	$class_extra .= ' disabled="true" ';
	}
	if($BSF_DET["WFS_CHECK_DUP"] == "Y"){ 
	$class_input .= ' wf_check_dup';
	}
	$data_list = array();
	$data_list = wf_call_irelation($BSF_DET,$rec_main,$WF[$rec_main["WF_FIELD_PK"]],$WF,$ONCHANGE);
	if($BSF_DET["WFS_TXT_BEFORE_INPUT"] != "" OR $BSF_DET["WFS_TXT_AFTER_INPUT"] != ""){ $html = '<div class="input-group-text">'; }
	if($BSF_DET["WFS_LIST_WIDTH"] > 0){
		$html .= '<div class="row">';
	}
	foreach($data_list as $_key => $_val)
	{
		if($BSF_DET["WFS_LIST_WIDTH"] > 0){
			$html .= '<div class="col-md-'.$BSF_DET["WFS_LIST_WIDTH"].'">';
		}
		$check_data = $_val['id'] == $default ? 'checked' : ''; 
		$html .= '<div class="form-check'.$class_input_div.'"><label class="form-check-label" for="'.$name.$_key.'"><input class="'.$class_input.'" type="radio" name="'.$name.'" value="'.$_val['id'].'" id="'.$name.$_key.'" '.$class_extra.' '.$check_data.' />'.$_val['text'].'</label></div>';
		
		if($BSF_DET["WFS_LIST_WIDTH"] > 0){
			$html .= '</div>';
		}
	}
	if($BSF_DET["WFS_LIST_WIDTH"] > 0){
		$html .= '</div>';
	}
	if($BSF_DET["WFS_TXT_BEFORE_INPUT"] != "" OR $BSF_DET["WFS_TXT_AFTER_INPUT"] != ""){ $html .= '</div>'; }
	$html .= '<small id="DUP_' . $name . '_ALERT" class="form-text text-danger" style="display:none"></small>';
	
	if($BSF_DET["WFS_OPTION_RADIO_CLEAR"] == 'Y'){
	  $html .= '
	  <script>
	  $("input[name=\''.$name.'\']").on("click", function (e){
		var inp = $(this);
		if (inp.is(".theone")) {
		  inp.prop("checked", false).removeClass("theone");
		} else {
		  $("input[name=\''.$name.'\'].theone").removeClass(
			"theone"
		  );
		  inp.addClass("theone");
		}
	  });
	  </script>';
	}
    return $html;
}
function bsf_form_icheck($BSF_DET,$WF,$rec_main,$WF_TYPE){
	global $ONCHANGE;
	$W = $rec_main['WF_MAIN_ID'];
	$default = $WF[$BSF_DET["WFS_FIELD_NAME_ORI"]];
	$class_input_div = "";
	if($default == ""){
		$default = $BSF_DET["WFS_DEFAULT_DATA"];
	}
	$name = $BSF_DET["WFS_FIELD_NAME"]; 
	$class_input = "form-check-input";

	if($BSF_DET["WFS_DEFINE_CLASS"] != ''){
	$class_input .= ' '.str_replace(".","",$BSF_DET["WFS_DEFINE_CLASS"]);
	}
	if($BSF_DET["WFS_OPTION_NEW_LINE"] == "" OR $BSF_DET["WFS_OPTION_NEW_LINE"] == "N"){
		$class_input_div = ' form-check-inline';
	}
	if($BSF_DET["WFS_NUM_STEP_JS"] > 0 OR $BSF_DET["WFS_NUM_ONCHANGE_SEND"] > 0){  
	$class_extra .= ' onClick="';
		if($BSF_DET["WFS_NUM_STEP_JS"] > 0){
		$class_extra .= 'bsf_chk_obj'.$BSF_DET['WF_TYPE'].'_'.$BSF_DET["WFS_ID"].'(this);';
		}
		if($BSF_DET["WFS_NUM_ONCHANGE_SEND"] > 0){
		$class_extra .= 'bsf_change_process'.$BSF_DET['WF_TYPE'].'_'.$BSF_DET["WFS_ID"].'(this.value);'; 
		} 		
	$class_extra .= '"';
	}
	if(trim((string)$BSF_DET["WFS_PLACEHOLDER"]) != ""){ 
	$class_extra .= ' placeholder ="'.$BSF_DET["WFS_PLACEHOLDER"].'" '; 
	}
	if($BSF_DET["WFS_REQUIRED"] == "Y"){
		if($BSF_DET['WFS_NAME']==""){
			$r_txt = "กรุณากรอกข้อมูล";
		}else{
			$r_txt = "กรุณากรอก".$BSF_DET['WFS_NAME'];
		}
		$class_extra .= ' required '; 
	}
	if(trim((string)$BSF_DET["WFS_TOOLTIP"]) != ""){
	$class_extra .= ' data-bs-toggle="tooltip" data-bs-placement="top" data-bs-original-title="'.$BSF_DET["WFS_TOOLTIP"].'"';
	}
	if($BSF_DET["WFS_READONLY"] == 'Y'){
	$class_extra .= ' readonly="true" ';
	}
	if($BSF_DET["WFS_DISABLE"] == 'Y'){
	$class_extra .= ' disabled="true" ';
	}
	if($BSF_DET["WFS_CHECK_DUP"] == "Y"){ 
	$class_input .= ' wf_check_dup';
	}
	if($BSF_DET["WFS_TXT_BEFORE_INPUT"] != "" OR $BSF_DET["WFS_TXT_AFTER_INPUT"] != ""){ $html = '<div class="input-group-text">'; }
	if($BSF_DET["WFS_INPUT_FORMAT"]== "O"){
		if($BSF_DET["WFS_REQUIRED"] == "Y"){
			$class_required = ' <span class="text-danger">*</span>';
		}
		$check_data = $BSF_DET["WFS_OPTION_VALUE"] == $default ? 'checked' : '';
		$html .= '<div class="form-check"><input class="'.$class_input.'" type="checkbox" name="'.$name.'" id="'.$name.'" value="'.$BSF_DET["WFS_OPTION_VALUE"].'" '.$check_data.' '.$class_extra.'><label class="form-check-label" for="'.$name.'"> '.$BSF_DET["WFS_NAME"].$class_required.'</label></div>';
	}else{
	$data_list = array();
	$data_list = wf_call_irelation($BSF_DET,$rec_main,$WF[$rec_main["WF_FIELD_PK"]],$WF,$ONCHANGE,'','',$WF_TYPE);
	$num = count($data_list);
	
	if($BSF_DET["WFS_OPTION_SELECT2COM"] == "Y"){
	if(trim((string)$BSF_DET["WFS_PLACEHOLDER"]) != ""){ 
	$class_extra .= ' data-placeholder="'.$BSF_DET["WFS_PLACEHOLDER"].'"';
	$html_p = '<option value="">'.$BSF_DET["WFS_PLACEHOLDER"].'</option>'; 
	}else{
	$class_extra .= ' data-placeholder=""';
	$html_p = ''; 	
	}
	if(($BSF_DET["WFS_TXT_BEFORE_INPUT"] != "" OR $BSF_DET["WFS_TXT_AFTER_INPUT"] != "") AND $BSF_DET["WFS_OPTION_SELECT2"] == "Y"){ $html = '<div class="input-group-text">'; }
	$class_input .= ' select2';
	$html_hidden = '';
	$html .= '<select name="' . $name . '[]" id="'.$name.'" multiple="true" class="form-control '.$class_input.'" '.$class_extra.'>'.$html_p;
	foreach($data_list as $_key => $_val)
	{
		$html .= '<option value="'.$_key.'" '; 
		if($_val['checked'] != ""){
		$html .= ' selected ';
		$arr_select[] = $_val['id'];
		}
		if($_val['checked'] == '' AND $BSF_DET["WFS_READONLY"] == 'Y'){ $html .= $class_option; }
		$html .= '>'; 
		$html_hidden .= '<input type="hidden" name="'.$name.'_'.$_key.'" value="'.$_val['id'].'" id="'.$name.'_'.$_key.'"  /><input type="hidden" name="'.$name.'_'.$_key.'_TYPE" id="'.$name.'_TYPE'.$_key.'" value="'.$_val['opt'].'">';
		$html .= $_val['text'].'</option>';
	}
	$html .= '</select>';
	if($num > 0){
	$html .='<input type="hidden" name="'.$name.'_COUNT" id="'.$name.'_COUNT" value="'.$num.'">';
	}
	$html .= $html_hidden;
	if(($BSF_DET["WFS_TXT_BEFORE_INPUT"] != "" OR $BSF_DET["WFS_TXT_AFTER_INPUT"] != "") AND $BSF_DET["WFS_OPTION_SELECT2"] == "Y"){ $html .= '</div>'; }
	}else{
	if($BSF_DET["WFS_REQUIRED"] == "Y"){
	$html .= '<div class="checkbox-group-required">';
	}
	if($BSF_DET["WFS_LIST_WIDTH"] > 0){
		$html .= '<div class="row">';
	}
	foreach($data_list as $_key => $_val)
	{
		if($BSF_DET["WFS_LIST_WIDTH"] > 0){
			$html .= '<div class="col-md-'.$BSF_DET["WFS_LIST_WIDTH"].'">';
		}
		$check_data = $_val['checked']; 
		$html .= '<div class="form-check'.$class_input_div.'"><input class="'.$class_input.'" type="checkbox" name="'.$name.'_'.$_key.'" chk-id="'.$name.'" chk-value="'.$_val['id'].'" value="'.$_val['id'].'" id="'.$name.'_'.$_key.'" '.$class_extra.' '.$check_data.' /><label class="form-check-label" for="'.$name.'_'.$_key.'">'.$_val['text'].'</label><input type="hidden" name="'.$name.'_'.$_key.'_TYPE" id="'.$name.'_TYPE'.$_key.'" value="'.$_val['opt'].'"></div>';
		if($BSF_DET["WFS_LIST_WIDTH"] > 0){
		$html .= '</div>';
		}
	}
	if($BSF_DET["WFS_LIST_WIDTH"] > 0){
		$html .= '</div>';
	}
	if($BSF_DET["WFS_REQUIRED"] == "Y"){
	$html .= '</div>';
	}

	if($num > 0){
	$html .='<input type="hidden" name="'.$name.'_COUNT" id="'.$name.'_COUNT" value="'.$num.'">';
	}
	}
	}
	if($BSF_DET["WFS_TXT_BEFORE_INPUT"] != "" OR $BSF_DET["WFS_TXT_AFTER_INPUT"] != ""){ $html .= '</div>'; }
	$html .= '<small id="DUP_' . $name . '_ALERT" class="form-text text-danger" style="display:none"></small>';
		
    return $html;
}
function bsf_form_ifile($BSF_DET,$WF,$rec_main){
	$html = '';
	$W = $rec_main['WF_MAIN_ID'];
	$html .= bsa_ishow($BSF_DET,$WF,$rec_main,'Y');
	//echo bsa_show($BSF_DET["WFS_FIELD_NAME"],$WF[$rec_main["WF_FIELD_PK"]],$W,$BSF_DET["WFS_FILE_ORDER"],$BSF_DET["WFS_FILE_LIGHTBOX"]);
	/*$default = $WF[$BSF_DET["WFS_FIELD_NAME_ORI"]]; 
	if($default == ""){
		$default = $BSF_DET["WFS_DEFAULT_DATA"];
	}*/
	echo '<input type="text" id="wfsfiles-chk-'.$BSF_DET["WFS_ID"].'" value="" ';
	if($BSF_DET["WFS_REQUIRED"]=="Y"){ echo ' required aria-required="true" '; }
	echo 'style="opacity:0;width:1px;height:1px;position:absolute;">';
	
	$name = $BSF_DET["WFS_FIELD_NAME"]; 
	$class_input = "form-control";

	if($BSF_DET["WFS_DEFINE_CLASS"] != ''){
	$class_input .= ' '.str_replace(".","",$BSF_DET["WFS_DEFINE_CLASS"]);
	}
	if($BSF_DET["WFS_INPUT_FORMAT"] == "O"){
		$class_input .= ' f-single';
		$html .= '<div id="'.$name.'_BSF-PREVIEW" class="f-single-preview row"></div>';
	}else{
		$class_input .= ' f-multiple';
		$class_extra .= ' multiple '; 
		$html .= '<div id="'.$name.'_BSF-PREVIEW" class="f-multiple-preview row"></div>';
	}
	if(trim((string)$BSF_DET["WFS_PLACEHOLDER"]) != ""){ 
	$class_extra .= ' placeholder ="'.$BSF_DET["WFS_PLACEHOLDER"].'" '; 
	}
	 
	if(trim((string)$BSF_DET["WFS_TOOLTIP"]) != ""){
	$class_extra .= ' data-bs-toggle="tooltip" data-bs-placement="top" data-bs-original-title="'.$BSF_DET["WFS_TOOLTIP"].'"';
	}
	if($BSF_DET["WFS_READONLY"] == 'Y'){
	$class_extra .= ' readonly="true" ';
	}
	if($BSF_DET["WFS_DISABLE"] == 'Y'){
	$class_extra .= ' disabled="true" ';
	} 
	if($BSF_DET["WFS_FILE_EXTEND_ALLOW"] != ''){
	$BSF_DET["WFS_FILE_EXTEND_ALLOW"] = str_replace('.','',$BSF_DET["WFS_FILE_EXTEND_ALLOW"]);
	$extra = explode(',',$BSF_DET["WFS_FILE_EXTEND_ALLOW"]);
	$arr = array();
	foreach($extra as $ext){
		$arr[] = '.'.$ext;
	}
	$comments .= '<small class="form-text text-muted">เฉพาะไฟล์นามสกุล '.$BSF_DET["WFS_FILE_EXTEND_ALLOW"].'</small>';
	$class_input .= ' f-check';
	$class_extra .= ' accept="'.implode(',',$arr).'"';
	} 
	if($BSF_DET["WFS_FILE_LIMIT"] == "Y" AND $BSF_DET["WFS_FILE_LIMIT_SIZE"] > 0){ 
		$arr_order_unit = array(''=>'Byte','KB'=>'KB.','MB'=>'MB.','GB'=>'GB.');
		$comments .= '<small class="form-text text-muted"> ขนาดไฟล์ไม่เกิน '.$BSF_DET["WFS_FILE_LIMIT_SIZE"].' '.$arr_order_unit[$BSF_DET["WFS_FILE_UNIT"]].'</small>';
		
		$file_limit = 9999999999999;
		$BSF_DET["WFS_FILE_LIMIT_SIZE"] = str_replace(',','',$BSF_DET["WFS_FILE_LIMIT_SIZE"]);
		if($BSF_DET["WFS_FILE_UNIT"]=="KB"){ $unit = 1024; }elseif($BSF_DET["WFS_FILE_UNIT"]=="MB"){ $unit = 1024000; }elseif($BSF_DET["WFS_FILE_UNIT"]=="GB"){ $unit = 1024000000; }else{ $unit = 1; }
		$file_limit = $BSF_DET["WFS_FILE_LIMIT_SIZE"]*$unit;
		$class_input .= ' f-check';
		$class_extra .= ' data-maxsize="'.$file_limit.'" data-sizedetail="'.$BSF_DET["WFS_FILE_LIMIT_SIZE"].'" data-maxunit="'.$BSF_DET["WFS_FILE_UNIT"].'"';
	}
 
	$html .= '<input type="file" wfs-id="'.$BSF_DET["WFS_ID"].'" class="'.$class_input.'" id="'.$name.'" name="'.$name.'[]" '.$class_extra.'>'.$comments; 
	if($BSF_DET["WFS_INPUT_FORMAT"] == "O"){
	$html .= '
	<script>
	$("#'.$name.'").change(function (e, v) {
    var input = this;
    var thisid = this.id;
	var wfsid = this.getAttribute(\'wfs-id\');
    var near_div = $(this).parent().children(".f-single-preview");
    var pathArray = $(this).val().split("\\\");
    if (near_div) {
      near_div.html("");
      if (input.files && input.files[0]) {
        var img_name = pathArray[pathArray.length - 1];
        var img_ext = img_name.split(".");
        var ext_n = img_ext[img_ext.length - 1];
        var ext = ext_n.toLowerCase();
        if (ext == "png" || ext == "jpg" || ext == "jpeg" || ext == "gif") {
          var reader = new FileReader();
          reader.onload = function (e) {
			var htm = \'<div class="card mx-3"><div class="card-body p-1"><div class="d-flex align-items-center p-1"><div class="flex-shrink-0"><div class="avtar avtar-s bg-light-primary"><i class="fas fa-search f-20"></i></div></div><div class="flex-grow-1 ms-3"><h6 class="mb-0">Preview รายการรอ Upload</h6></div><div class="flex-shrink-0 ms-3"><button type="button" class="btn bg-light-danger" onClick="$(\\\'#\' + thisid + "_BSF-PREVIEW\').html(\'\');$(\'#" + thisid + "\').val(\'\');wf_file_update(\'"+wfsid+\'\\\');\"> <i class="ti ti-trash"></i> Clear  </button> </div></div><div class="row">\'; 
			htm += \'<div class="wfs-files-id-\'+wfsid+\' col-md-6 p-0"><div class="card product-card p-1 mb-1"><div class="img-post card"><img src="\' + e.target.result + \'" class="card-img" /></div><div class="py-1 text-center text-truncate">\'+img_name+\'</div></div></div></div></div></div>\';
            near_div.html(htm);
          };
          reader.readAsDataURL(input.files[0]);
        } else {
          var htm = \'<div class="card mx-3"><div class="card-body p-1"><div class="d-flex align-items-center p-1"><div class="flex-shrink-0"><div class="avtar avtar-s bg-light-primary"><i class="fas fa-search f-20"></i></div></div><div class="flex-grow-1 ms-3"><h6 class="mb-0">รายการรอ Upload</h6></div><div class="flex-shrink-0 ms-3"><button type="button" class="btn bg-light-danger" onClick="$(\\\'#\' + thisid + "_BSF-PREVIEW\').html(\'\');$(\'#" + thisid + "\').val(\'\');wf_file_update(\'"+wfsid+\'\\\');\"> <i class="ti ti-trash"></i> Clear  </button> </div></div><div class="row">\'; 
			htm += \'<div class="wfs-files-id-\'+wfsid+\' col-md-12 px-3 py-1"><div class="card product-card p-1 mb-1"><div class="d-flex align-items-center"><div class="flex-shrink-0"><div class="avtar avtar-s bg-light-primary"><i class="fas fa-file-upload f-24"></i></div></div><div class="flex-grow-1 ms-3 text-truncate">\' + img_name + \'</div> </div></div></div></div></div></div>\';
			near_div.html(htm);
        }
      }
    }
	setTimeout(function(){ wf_file_update(wfsid); }, 1000);
  });
  </script>';		
	}else{
	$html .= '
	<script>
	$("#'.$name.'").change(function (e, v){
    var input = document.getElementById(this.id);
	var wfsid = this.getAttribute(\'wfs-id\'); 
    var thisprev = $(this).parent().children(".f-multiple-preview"); 
    thisprev.html("");
    if (input.files.length > 0) {
		var htm = \'<div class="row"><div class="card-body p-1"><div class="d-flex align-items-center p-1"><div class="flex-shrink-0"><div class="avtar avtar-s bg-light-primary"><i class="fas fa-search f-20"></i></div></div><div class="flex-grow-1 ms-3"><h6 class="mb-0">Preview รายการรอ Upload</h6></div><div class="flex-shrink-0 ms-3"><button type="button" class="btn bg-light-danger" onClick="$(\\\'#\' + this.id + "_BSF-PREVIEW\').html(\'\');$(\'#" + this.id + "\').val(\'\');wf_file_update(\'"+wfsid+\'\\\');\"> <i class="ti ti-trash"></i> Clear  </button> </div></div><div class="row">\'; 
      thisprev.html(htm);

      for (var x = 0; x < input.files.length; x++) {
        var img_ext = input.files[x].name.split(".");
        var ext_n = img_ext[img_ext.length - 1];
        var ext = ext_n.toLowerCase();
        if (ext == "png" || ext == "jpg" || ext == "jpeg" || ext == "gif") {
          var filereader = new FileReader();
          var fname = input.files[x].name;
          filereader.onload = function (e, v) {
            thisprev.append(\'<div class="wfs-files-id-\'+wfsid+\' col-md-6 p-1"><div class="card product-card p-1 mb-0"><div class="img-post card"><img src="\' + e.target.result + \'" class="card-img" /></div><div class="py-1 text-center text-truncate">\'+fname+\'</div></div></div>\');
          };
          filereader.readAsDataURL(input.files[x]);
        } else {
			htm = \'<div class="wfs-files-id-\'+wfsid+\' col-md-10 p-1"><div class="card product-card p-1 mb-0"><div class="d-flex align-items-center"><div class="flex-shrink-0"><div class="avtar avtar-s bg-light-primary"><i class="fas fa-file-upload f-24"></i></div></div><div class="flex-grow-1 ms-3 text-truncate">\' + input.files[x].name + \'</div> </div></div></div>\'; 
          thisprev.append(htm);
        }
      }
	  var htm = \'</div></div></div>\';
	  thisprev.append(htm);
    }
	setTimeout(function(){ wf_file_update(wfsid); }, 1000);
  });
  </script>  ';
	}
    return $html;
}
function bsa_ishow($BSF_DET,$WF,$rec_main,$EDIT=''){
	global $WF_LIST_DATA; 
	if($BSF_DET["WFS_FIELD_NAME_ORI"] == ""){ $BSF_DET["WFS_FIELD_NAME_ORI"] = $BSF_DET["WFS_FIELD_NAME"]; }
	//bsa_show($BSF_DET["WFS_FIELD_NAME"],$WF[$rec_main["WF_FIELD_PK"]],$W,$BSF_DET["WFS_FILE_ORDER"],$BSF_DET["WFS_FILE_LIGHTBOX"]);
	//bsa_show($FIELD,$WFR,$W,$ORDER='0',$lightbox='',$EDIT=''){
	if($WF_LIST_DATA == "Y" AND $EDIT == 'N'){
		$display_css = 'py-0 my-0';
	}else{
		$display_css = '';
	}
	$WFR = $WF[$rec_main["WF_FIELD_PK"]];
	$txt = ''; 
	if($WFR != '' AND is_numeric($WFR)){
	$FIELD = $BSF_DET["WFS_FIELD_NAME_ORI"];
	$W = $rec_main['WF_MAIN_ID'];
	$ORDER = $BSF_DET["WFS_FILE_ORDER"];
	$lightbox= $BSF_DET["WFS_FILE_LIGHTBOX"];
	
	$array_img = array('png','jpg','jpeg','gif','bmp'); 
	
	$arr_order_file = array(''=>'FILE_ID ASC','0'=>'FILE_ID ASC',1=>'FILE_ID DESC',2=>'FILE_NAME ASC',3=>'FILE_NAME DESC',4=>'FILE_SIZE ASC',5=>'FILE_SIZE DESC',6=>'FILE_EXT ASC',7=>'FILE_EXT ASC');
	
	$sql_attach = db::query("SELECT * FROM WF_FILE where WFS_FIELD_NAME ='".$FIELD."' AND WFR_ID='".$WFR."' AND WF_MAIN_ID = '".$W."' AND FILE_STATUS = 'Y' ORDER BY ".$arr_order_file[$ORDER]);
	$rows = db::num_rows($sql_attach);
	if($rows > 0){
		 
		if($lightbox=="Y"){
		$txt .= '<div class="row">';
		}else{
		$txt .= '<div class="row '.$display_css.'">';	
		}
	while($rec_a = db::fetch_array($sql_attach)){
		if($rec_a["FILE_TYPE"] == "DROPBOX"){
			$bsf_link = $rec_a["FILE_SAVE_NAME"];	
			$bsf_title = "";
		}else{
			if($rec_a["FILE_SAVE_FOLDER"] ==""){
			$bsf_link = '../attach/w'.$W.'/'.$rec_a["FILE_SAVE_NAME"];
			}else{
			$bsf_link = $rec_a["FILE_SAVE_FOLDER"].'/'.$rec_a["FILE_SAVE_NAME"];	
			}
		$bsf_title = $rec_a["FILE_NAME"];
		}
	if(file_exists($bsf_link)){	
		if($lightbox=="Y"){
		if($BSF_DET["WFS_LIST_WIDTH"] > 0){ $width = $BSF_DET["WFS_LIST_WIDTH"];}else{ $width = "4"; }
			$txt .= '<div id="BSA_FILE'.$rec_a["FILE_ID"].'" class="wfs-files-id-'.$BSF_DET["WFS_ID"].' col-md-'.$width.'">
						<div class="card product-card mx-0 mb-2">';
						if(in_array($rec_a["FILE_EXT"],$array_img)){
							$txt .= '<div class="card-img-top text-center"><a href="#!" class="img-post card" data-lightbox-'.$WFR.$BSF_DET["WFS_ID"].'="document_load.php?W='.$W.'&WFR='.$WFR.'&FILE='.$rec_a["FILE_ID"].'" data-title="'.$bsf_title.'" data-id="'.$rec_a["FILE_ID"].'" data-size="modal-lg"><img src="'.$bsf_link.'" class="card-img" alt="'.$bsf_title.'" /></a></div>';
						}elseif($rec_a["FILE_EXT"]=="pdf" OR $rec_a["FILE_EXT"]=="docx" OR $rec_a["FILE_EXT"]=="xlsx"){
							$txt .= '<div class="card-img-top my-2 text-center"><a href="#!" class="img-post" data-lightbox-'.$WFR.$BSF_DET["WFS_ID"].'="document_load.php?W='.$W.'&WFR='.$WFR.'&FILE='.$rec_a["FILE_ID"].'" data-title="'.$bsf_title.'" data-id="'.$rec_a["FILE_ID"].'" data-size=""><i class="fas '.bsa_iicon($rec_a["FILE_EXT"]).' f-40 text-secondary"></i></a></div>';
						}else{
							$txt .= '<div class="card-img-top my-2 text-center"><a href="document_view.php?W='.$W.'&WFR='.$WFR.'&FILE='.$rec_a["FILE_ID"].'" target="_blank"><i class="fas '.bsa_iicon($rec_a["FILE_EXT"]).' f-40 text-secondary"></i></a></div>'; 
						}		 					
				$txt .= '<div class="p-0">
							<div class="d-flex align-items-top justify-content-between p-1">
							  <span title="'.$bsf_title.'" class="text-truncate">'.$bsf_title.'</span>';
							if($EDIT=='Y'){  
						$txt .= '<div class="p-1">
									<button type="button" class="btn btn-mini btn-link-danger py-0 px-1"  onClick="wf_file_d_'.$BSF_DET["WFS_ID"].'(\''.$W.'\',\''.$rec_a["FILE_ID"].'\',\''.$rec_a["WFR_ID"].'\',\''.$rec_a["FILE_NAME"].'\');"><i class="ti ti-trash"></i></button>
								</div>';
							}
							$txt .= '</div>
						  </div>
						</div>
					</div>'; 
		}else{
		if($BSF_DET["WFS_LIST_WIDTH"] > 0){ $width = $BSF_DET["WFS_LIST_WIDTH"];}else{ $width = "12"; }
			$txt .= '<div id="BSA_FILE'.$rec_a["FILE_ID"].'" class="wfs-files-id-'.$BSF_DET["WFS_ID"].' wf-space-i-c '.$display_css.' col-md-'.$width.' btn-link-secondary rounded">
				<div class="d-flex align-items-center justify-content-between '.$display_css.'">
				  <a href="document_view.php?W='.$W.'&WFR='.$WFR.'&FILE='.$rec_a["FILE_ID"].'" target="_blank" title="'.$bsf_title.'" class="text-truncate align-items-top '.$display_css.'">
					 <h6 class="'.$display_css.'"><span class="badge bg-light-info"><span class="fas text-secondary '.bsa_iicon($rec_a["FILE_EXT"]).' f-16 p-0"></span></span> '.$bsf_title.'</h6>
				  </a>';
				  
				  if($EDIT=='Y'){
			$txt .= '<div class="p-1">
							<button type="button" class="btn btn-mini btn-link-danger py-0 px-1"  onClick="wf_file_d_'.$BSF_DET["WFS_ID"].'(\''.$W.'\',\''.$rec_a["FILE_ID"].'\',\''.$rec_a["WFR_ID"].'\',\''.$rec_a["FILE_NAME"].'\');"><i class="ti ti-trash"></i></button>
						</div>';
					}
			$txt .= '</div>
			 </div>'; 
		}
	}
	}
		if($lightbox=="Y"){
		$txt .= '</div>';
		$txt .= '
		<div class="modal bg-dark bg-opacity-25" id="bizModallightbox'.$BSF_DET["WFS_ID"].'" data-bs-backdrop="static" aria-hidden="true"> 
		  <div class="modal-dialog modal-lg modal-dialog-centered"> 
			<div class="modal-content">
				<div class="modal-header py-2">
				  <h1 class="modal-title fs-5"></h1><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
				</div> 
				<div class="modal-body text-center">
				</div>
			</div>
		  </div>
		</div>
		<script> 
			  var elem = document.querySelectorAll(\'[data-lightbox-'.$WFR.$BSF_DET["WFS_ID"].']\');
			  for (var j = 0; j < elem.length; j++) {
				elem[j].addEventListener(\'click\', function () { 
				  var recipient = this.getAttribute(\'data-lightbox-'.$WFR.$BSF_DET["WFS_ID"].'\');
				  var titles = \'<b>\'+this.getAttribute(\'data-title\')+\'</b> &nbsp;&nbsp;<a href="document_view.php?W='.$W.'&WFR='.$WFR.'&FILE=\'+this.getAttribute(\'data-id\')+\'" target="_blank" download="\'+this.getAttribute(\'data-title\')+\'" class="btn btn-mini text-end">[<i class="ti ti-download"></i> Download]</a>\'; 
				  var sizes = this.getAttribute(\'data-size\');  
				  open_modal(recipient,titles,\'lightbox'.$BSF_DET["WFS_ID"].'\',sizes);
				});
			  }  
		</script>';
		}else{
		$txt .= '</div>';	
		}
		if($EDIT=='Y'){
		$txt .= '<script>
			$("#wfsfiles-chk-'.$BSF_DET["WFS_ID"].'").val(\'';
			if($rows > 0){ $txt .= $rows; }
			$txt .= '\');
			function wf_file_d_'.$BSF_DET["WFS_ID"].'(w,f,wfr,txt){
				if(w != \'\' && f != \'\' && wfr != \'\'){
					
				const swalWithBootstrapButtons = Swal.mixin({
                    customClass: {
                        confirmButton: \'btn btn-danger\',
                        cancelButton: \'btn btn-warning\'
                    },
                    buttonsStyling: false
                });
                swalWithBootstrapButtons
                    .fire({
                        title: "",
                        text: "คุณต้องการลบไฟล์นี้หรือไม่?",
                        icon: \'warning\',
                        showCancelButton: true,
                        confirmButtonText: \'<i class="ti ti-trash"></i> ยืนยันการลบ\',
                        cancelButtonText: \'<i class="fas fa-undo"></i> ยกเลิก\',
                        reverseButtons: false
                    })
                    .then((result) => {
                        if (result.isConfirmed) {
                            var dataString = \'process=d&wfr=\'+wfr+\'&W=\'+w+\'&f=\'+f;
							$.ajax({
								type: "POST",
								url: "../workflow/wf_file_d.php",
								data: dataString,
								cache: false,
								success: function(html){ 
										$(\'#BSA_FILE\'+f).remove(); 
										wf_file_update(\''.$BSF_DET["WFS_ID"].'\');
								} 
							 });
                        }
                    }); 
				}
				}
		</script>';
		}
	}	
	}
		return $txt;
} 
function bsf_form_ihidden($BSF_DET,$WF,$rec_main,$ONCHANGE='',$WF_PSEND=''){ 
	if($BSF_DET["WFS_FIELD_NAME_ORI"] == ""){ $BSF_DET["WFS_FIELD_NAME_ORI"] = $BSF_DET["WFS_FIELD_NAME"]; }
	$W = $rec_main['WF_MAIN_ID'];
	$pk_field = $rec_main["WF_FIELD_PK"];
	$WF_TYPE = $BSF_DET['WF_TYPE']; 
	$default = $WF[$BSF_DET["WFS_FIELD_NAME_ORI"]]; 
	if($default == "" AND $BSF_DET["WFS_DEFAULT_DATA"] != ''){
		$default = bsf_default_var($BSF_DET["WFS_DEFAULT_DATA"],$WF,'');
		$WF[$BSF_DET["WFS_FIELD_NAME_ORI"]] = $default; 
	}
	
	$name = $BSF_DET["WFS_FIELD_NAME"]; 
	$class_input = "hidden2";

	if($BSF_DET["WFS_DEFINE_CLASS"] != ''){
	$class_input .= ' '.str_replace(".","",$BSF_DET["WFS_DEFINE_CLASS"]);
	}
	$btn = "เลือก";
	$class_extra = 'style="width:1px;height:24px;position: absolute;z-index:-1;"';
	if(trim((string)$BSF_DET["WFS_PLACEHOLDER"]) != ""){  
	$btn = $BSF_DET["WFS_PLACEHOLDER"];
	}
	if($BSF_DET["WFS_REQUIRED"] == "Y"){
		if($BSF_DET['WFS_NAME']==""){
			$r_txt = "กรุณากรอกข้อมูล";
		}else{
			$r_txt = "กรุณากรอก".$BSF_DET['WFS_NAME'];
		}
		$class_extra .= ' required aria-required="true" '; 
	}
	if(trim((string)$BSF_DET["WFS_TOOLTIP"]) != ""){
	$class_extra .= ' data-bs-toggle="tooltip" data-bs-placement="top" data-bs-original-title="'.$BSF_DET["WFS_TOOLTIP"].'"';
	}
	if($BSF_DET["WFS_READONLY"] == 'Y'){
	$class_extra .= ' readonly="true" ';
	}
	if($BSF_DET["WFS_DISABLE"] == 'Y'){
	$class_extra .= ' disabled="true" ';
	}
	if($BSF_DET["WFS_NUM_STEP_JS"] > 0 OR $BSF_DET["WFS_NUM_ONCHANGE_SEND"] > 0){  
	$class_extra .= ' onChange="';
		if($BSF_DET["WFS_NUM_STEP_JS"] > 0){ 
		$class_extra .= 'bsf_change_obj'.$BSF_DET['WF_TYPE'].'_'.$BSF_DET["WFS_ID"].'(this.value);'; 
		}
		if($BSF_DET["WFS_NUM_ONCHANGE_SEND"] > 0){
		//$class_extra .= 'bsf_change_process'.$BSF_DET['WF_TYPE'].'_'.$BSF_DET["WFS_ID"].'(this.value);'; 
		}
	$class_extra .= '"';
	} 
	$html .= '<input type="text" name="' . $name . '" id="' . $name . '" class="' . $class_input . '" value="' . $default . '" ' . $class_extra . '>';
	if($BSF_DET["WFS_OPTION_SELECT_DATA"] != ""){ 
	if($default != ''){ 
	$data = bsf_show_itext($BSF_DET['WFS_FIELD_NAME_ORI'],$rec_main,$WF,$BSF_DET);
	}
		if(is_numeric($BSF_DET["WFS_OPTION_SELECT_DATA"])){
			if($_SESSION["WF_USER_ID"] != ""){  
				$wflink = '../workflow/wf_main.php?W='; 
			}else{
				$wflink = "wf_main.php?W=";
			}
		}else{
			if($_SESSION["WF_USER_ID"] != ""){  
				$wflink = '../workflow/wf_usrmain.php?WUDP='; 
			}else{
				$wflink = "../workflow/wf_usrmain.php?WUDP=";
			} 
		}
	$html .= '<span id="WFH_'.$BSF_DET["WFS_FIELD_NAME"].'_SHOW">'.$data.'</span> ';
		$html .= ' <button type="button" id="WFHB_'.$BSF_DET["WFS_FIELD_NAME"].'_CLR" class="btn btn-mini btn-light-warning" data-bs-toggle="tooltip" data-bs-placement="top" title="เคลียร์ค่า" onClick="bsf_'.$BSF_DET["WFS_FIELD_NAME"].'_clr_'.$BSF_DET["WFS_ID"].'();" ';
		if($data == ""){ $html .= ' style="display: none;"'; }
		$html .= '><i class="fas fa-times"></i></button>';
		
	$html .= ' <button type="button" class="btn btn-mini btn-primary" onClick="open_modal(\''.$wflink.$BSF_DET["WFS_OPTION_SELECT_DATA"].'&WFS='.$BSF_DET["WFS_ID"].'&WFR='.$WF[$pk_field].'&WF_SHOW='.$BSF_DET['SHOW'].'&wfp='.conText($_GET['wfp']).$WF_PSEND.'\',\''.$BSF_DET["WFS_NAME"].'\',\''.$BSF_DET["WFS_ID"].'\',\''.$BSF_DET["WFS_MODAL_SIZE"].'\');"><i class="fab fa-sistrix"></i> '.$btn.'</button>';
	
	$html .= '<div class="modal bg-dark bg-opacity-25" id="bizModal'.$BSF_DET["WFS_ID"].'" data-bs-backdrop="static" aria-hidden="true">
        <div class="modal-dialog">
          <div class="modal-content">
            <div class="modal-header">
              <h1 class="modal-title fs-5"></h1><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body py-2" id="wf_hidden_content'.$BSF_DET["WFS_OPTION_SELECT_DATA"].$BSF_DET["WFS_ID"].'"></div>
            <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ปิด</button></div>
          </div>
        </div>
      </div>
	  ';
	  $html .= '<script>
			 function bsf_'.$BSF_DET["WFS_FIELD_NAME"].'_clr_'.$BSF_DET["WFS_ID"].'(){
				$(\'#WFH_'.$BSF_DET["WFS_FIELD_NAME"].'_SHOW\').html(\'\');
				$(\'#'.$BSF_DET["WFS_FIELD_NAME"].'\').val(\'\');
				$(\'#'.$BSF_DET["WFS_FIELD_NAME"].'\').trigger("change");
				$(\'#WFHB_'.$BSF_DET["WFS_FIELD_NAME"].'_CLR\').hide();
				';
if($BSF_DET["WFS_NUM_STEP_THROW"] > 0){
$sql_wft = db::query("select * from WF_STEP_THROW where WFS_ID = '".$BSF_DET["WFS_ID"]."' ORDER BY WFST_ID ASC");
while($THROW = db::fetch_array($sql_wft)){
	if($THROW['WFST_VALUE'] != ''){
		$html .= 'if($("#'.$THROW['WFST_VALUE'].$BSF_DET['SHOW'].'").length){
			if ($("#'.$THROW['WFST_VALUE'].$BSF_DET['SHOW'].'").hasClass( "select2-amphur" )) { 
				$("select#'.$THROW['WFST_VALUE'].$BSF_DET['SHOW'].'").on("select2:open", function(e) { 
				   $("select#'.$THROW['WFST_VALUE'].$BSF_DET['SHOW'].'").val("").trigger("change");
				});
			}else if ($("#'.$THROW['WFST_VALUE'].$BSF_DET['SHOW'].'").hasClass( "select2-tambon" )) {
				$("select#'.$THROW['WFST_VALUE'].$BSF_DET['SHOW'].'").on("select2:open", function(e) { 
				   $("select#'.$THROW['WFST_VALUE'].$BSF_DET['SHOW'].'").val("").trigger("change");
				});
			}else if ($("#'.$THROW['WFST_VALUE'].$BSF_DET['SHOW'].'").hasClass( "select2-province" )) { 
				$("select#'.$THROW['WFST_VALUE'].$BSF_DET['SHOW'].'").val("").trigger("change");
			}else if ($("#'.$THROW['WFST_VALUE'].$BSF_DET['SHOW'].'").hasClass( "select2-hidden-accessible" )) { 		
				$("select#'.$THROW['WFST_VALUE'].$BSF_DET['SHOW'].'").val("");
				$("select#'.$THROW['WFST_VALUE'].$BSF_DET['SHOW'].'").trigger("change");
			}else{
				$("#'.$THROW['WFST_VALUE'].$BSF_DET['SHOW'].'").val("");
			}
		}';
	}
}
}	
			 $html .= '}
			  </script>';
	  
	}
    return $html;
}
function wf_select_gen_lv($level,$pre){
$txt = '';
	if($pre != ""){
		if($level > 0){
			for($i=0;$i<=$level;$i++){
				$txt .= $pre;
			}
		}
	}
	return $txt;
}
function wf_arrange_parent($arr_data,$key,$level,$txt,$pre){
	$wf_data_arr = array();
	if(count((array)$arr_data[$key]) > 0){
	foreach($arr_data[$key] as $M){
	$mtext = $M['text']; 
	$M['text'] = $M['text'];
	$M['title'] = $txt;
	$M['level'] = $level;
	$wf_data_arr[] = $M;
	if(count((array)$arr_data[$M["id"]]) > 0){
		if($txt != ''){
		$txt2 = $txt.' > '.$mtext;
		}else{
		$txt2 = $mtext;	
		}
	$wf_data_arr1 = wf_arrange_parent($arr_data,$M["id"],($level+1),$txt2,$pre); 
	$wf_data_arr = array_merge($wf_data_arr, $wf_data_arr1);
	}
} 	
return $wf_data_arr;					
}
}
function wf_call_irelation_parent($data_list,$type='',$option=''){
	$arr_data = array();
	foreach($data_list as $_val){ 
		$arr_data_sub = $_val;
		if($_val['parent'] == ""){
		$arr_data_sub['parent'] = '0';
		$PARENT = 0;
		}else{ 
		$PARENT = $_val['parent'];
		} 
		$arr_data[$PARENT][] = $arr_data_sub;
	} 
	$data_list2 = wf_arrange_parent($arr_data,0,0,'','');
	return $data_list2;
}
function bsf_form_iselect($BSF_DET,$WF,$rec_main){
	if($BSF_DET["WFS_FIELD_NAME_ORI"] == ""){ $BSF_DET["WFS_FIELD_NAME_ORI"] = $BSF_DET["WFS_FIELD_NAME"]; }
	$W = $rec_main['WF_MAIN_ID'];
	$SHOW = $BSF_DET['SHOW'];
	$default = $WF[$BSF_DET["WFS_FIELD_NAME_ORI"]];
	$class_option = '';	
	if($default == "" AND $BSF_DET["WFS_DEFAULT_DATA"] != ''){
		$default = bsf_default_var($BSF_DET["WFS_DEFAULT_DATA"],$WF,'');
		$WF[$BSF_DET["WFS_FIELD_NAME_ORI"]] = $default; 
	} 

	$name = $BSF_DET["WFS_FIELD_NAME"]; 
	$class_input = "form-control"; 
	if($BSF_DET["WFS_DEFINE_CLASS"] != ''){
	$class_input .= ' '.str_replace(".","",$BSF_DET["WFS_DEFINE_CLASS"]);
	} 
	if($BSF_DET["WFS_REQUIRED"] == "Y"){
		if($BSF_DET['WFS_NAME']==""){
			$r_txt = "กรุณากรอกข้อมูล";
		}else{
			$r_txt = "กรุณากรอก".$BSF_DET['WFS_NAME'];
		}
		$class_extra .= ' required '; 
	}
	if(trim((string)$BSF_DET["WFS_TOOLTIP"]) != ""){
	$class_extra .= ' data-bs-toggle="tooltip" data-bs-placement="top" data-bs-original-title="'.$BSF_DET["WFS_TOOLTIP"].'"';
	}
	if($BSF_DET["WFS_READONLY"] == 'Y' ){
	$class_extra .= ' readonly="true" ';
		if($BSF_DET["WFS_OPTION_SELECT2"] != "Y"){
			$class_option ='disabled';
		}
	}
	if($BSF_DET["WFS_DISABLE"] == 'Y'){
	$class_extra .= ' disabled="true" ';
	}
	if($BSF_DET["WFS_CHECK_DUP"] == "Y"){ 
	$class_input .= ' wf_check_dup';
	}
	if($BSF_DET["WFS_NUM_STEP_JS"] > 0 OR $BSF_DET["WFS_NUM_ONCHANGE_SEND"] > 0){  
	$class_extra .= ' onChange="';
		if($BSF_DET["WFS_NUM_STEP_JS"] > 0){ 
		$class_extra .= 'bsf_change_obj'.$BSF_DET['WF_TYPE'].'_'.$BSF_DET["WFS_ID"].'(this.value);'; 
		}
		if($BSF_DET["WFS_NUM_ONCHANGE_SEND"] > 0){
		//$class_extra .= 'bsf_change_process'.$BSF_DET['WF_TYPE'].'_'.$BSF_DET["WFS_ID"].'(this.value);'; 
		}
	$class_extra .= '"';
	}
	
	if($BSF_DET["FORM_MAIN_ID"] == "11"){
		if($BSF_DET["WFS_SHOW_AMPHUR"] != ''){ $BSF_DET["WFS_SHOW_AMPHUR"].$SHOW; }
		if($BSF_DET["WFS_SHOW_TAMBON"] != ''){ $BSF_DET["WFS_SHOW_TAMBON"].$SHOW; }
		if($BSF_DET["WFS_SHOW_ZIPCODE"] != ''){ $BSF_DET["WFS_SHOW_ZIPCODE"].$SHOW; }
		if($BSF_DET["WFS_SHOW_AMPHUR"] !=""){
		$class_extra .= ' onChange="get_amphur(\''.$BSF_DET["WFS_FIELD_NAME"].'\',\''.$BSF_DET["WFS_SHOW_AMPHUR"].$SHOW.'\',\''.$BSF_DET["WFS_SHOW_TAMBON"].$SHOW.'\',\''.$BSF_DET["WFS_SHOW_ZIPCODE"].$SHOW.'\');" ';
		}
	$class_input .= ' select2';
	$class_extra .= ' data-placeholder="เลือกจังหวัด"';
	$html_p = '<option value="">เลือกจังหวัด</option>'; 
	$data_list = array();
	global $WF_G_PROVINCE;
	foreach($WF_G_PROVINCE as $pcode=>$pname){
		$data = array();
		$data['id'] = $pcode;
		$data['text'] = $pname;
		if($default == $pcode){
		$data['selected'] = 'selected';	
		}else{
		$data['selected'] = '';		
		}
		$data_list[] = $data;
		unset($data);
	}
	}elseif($BSF_DET["FORM_MAIN_ID"] == "12"){
		
		$data_list = array();
		if($BSF_DET["WFS_SHOW_PROVINCE"] != ''){ $BSF_DET["WFS_SHOW_PROVINCE"].$SHOW; }
		if($BSF_DET["WFS_SHOW_TAMBON"] != ''){ $BSF_DET["WFS_SHOW_TAMBON"].$SHOW; }
		if($BSF_DET["WFS_SHOW_ZIPCODE"] != ''){ $BSF_DET["WFS_SHOW_ZIPCODE"].$SHOW; }
		if($BSF_DET["WFS_SHOW_TAMBON"] !=""){
		$class_extra .= ' onChange="get_tambon(\''.$BSF_DET["WFS_SHOW_PROVINCE"].$SHOW.'\',\''.$BSF_DET["WFS_FIELD_NAME"].'\',\''.$BSF_DET["WFS_SHOW_TAMBON"].$SHOW.'\',\''.$BSF_DET["WFS_SHOW_ZIPCODE"].$SHOW.'\');" ';
		}else{
			$html_p = '<option value="">เลือกอำเภอ</option>'; 
			
		}
			if($default!=''){
				
			$html_p = '<option value="">เลือกอำเภอ</option>'; 
				$sql_option = db::query("select PROVINCE_CODE,AMPHUR_CODE,AMPHUR_NAME from G_AMPHUR WHERE PROVINCE_CODE = '".substr($default,0,2)."' order by AMPHUR_NAME");
				while($rec_option = db::fetch_array($sql_option)){ 
					$data = array();
					$data['id'] = $rec_option['PROVINCE_CODE'].$rec_option['AMPHUR_CODE'];
					$data['text'] = $rec_option['AMPHUR_NAME']; 
					if($default == $rec_option['PROVINCE_CODE'].$rec_option['AMPHUR_CODE']){
					$data['selected'] = 'selected';	
					}else{
					$data['selected'] = '';		
					}
					$data_list[] = $data;
					unset($data); 
				}
			}else{
			$html_p = '<option value="">เลือกจังหวัดก่อน</option>'; 	
			}
		
	$class_input .= ' select2';
	$class_extra .= ' data-placeholder="เลือกอำเภอ"';
	
	}elseif($BSF_DET["FORM_MAIN_ID"] == "13"){
		$data_list = array();
	if($BSF_DET["WFS_SHOW_PROVINCE"] != ''){ $BSF_DET["WFS_SHOW_PROVINCE"].$SHOW; }
	if($BSF_DET["WFS_SHOW_AMPHUR"] != ''){ $BSF_DET["WFS_SHOW_AMPHUR"].$SHOW; }
	if($BSF_DET["WFS_SHOW_ZIPCODE"] != ''){ $BSF_DET["WFS_SHOW_ZIPCODE"].$SHOW; }
	if($BSF_DET["WFS_SHOW_ZIPCODE"] !=""){
	$class_extra .= ' onChange="get_zipcode(\''.$BSF_DET["WFS_SHOW_PROVINCE"].$SHOW.'\',\''.$BSF_DET["WFS_SHOW_AMPHUR"].$SHOW.'\',\''.$BSF_DET["WFS_FIELD_NAME"].'\',\''.$BSF_DET["WFS_SHOW_ZIPCODE"].$SHOW.'\');"'; 
	}else{
	$html_p = '<option value="">เลือกตำบล</option>';  
	}
		if($default != ''){
			$html_p = '<option value="">เลือกตำบล</option>'; 
			$sql_option = db::query("select PROVINCE_CODE,AMPHUR_CODE,TAMBON_CODE,TAMBON_NAME from G_TAMBON WHERE PROVINCE_CODE = '".substr($default,0,2)."' AND AMPHUR_CODE = '".substr($default,2,2)."' order by TAMBON_NAME");
			while($rec_option = db::fetch_array($sql_option)){
				$data = array();
				$data['id'] = $rec_option['PROVINCE_CODE'].$rec_option['AMPHUR_CODE'].$rec_option['TAMBON_CODE'];
				$data['text'] = $rec_option['TAMBON_NAME']; 
				if($default == $rec_option['PROVINCE_CODE'].$rec_option['AMPHUR_CODE'].$rec_option['TAMBON_CODE']){
				$data['selected'] = 'selected';	
				}else{
				$data['selected'] = '';		
				}
				$data_list[] = $data;
				unset($data);  
				}
		}else{
			$html_p = '<option value="">เลือกอำเภอก่อน</option>';
		}
	
	$class_input .= ' select2';
	$class_extra .= ' data-placeholder="เลือกตำบล"';
	
	
	}else{
	if(trim($BSF_DET["WFS_OPTION_SELECT2COM"]) == "Y"){
			$class_input .= ' select2com'; 
			$class_extra .= ' data-url="'.$BSF_DET["WFS_ID"].'"';
	}elseif($BSF_DET["WFS_OPTION_SELECT2"] == "Y"){
			$class_input .= ' select2';
	}
	if(trim((string)$BSF_DET["WFS_PLACEHOLDER"]) != ""){ 
	$class_extra .= ' data-placeholder="'.$BSF_DET["WFS_PLACEHOLDER"].'"';
	$html_p = '<option value="">'.$BSF_DET["WFS_PLACEHOLDER"].'</option>'; 
	}
	
	if(trim($BSF_DET["WFS_OPTION_SELECT2COM"]) == "Y"){
		$data_list = array();
		if($default != ''){
		$data_list = wf_call_irelation($BSF_DET,$rec_main,$WF[$rec_main["WF_FIELD_PK"]],$WF,'',$default); 
		}
	}else{
		$data_list1 = array();
		$data_list1 = wf_call_irelation($BSF_DET,$rec_main,$WF[$rec_main["WF_FIELD_PK"]],$WF,'',''); 
		
			if(isset($data_list1[0]['parent'])){ 
				$data_list = wf_call_irelation_parent($data_list1);
				//print_pre($data_list);
			}else{
				$data_list = $data_list1;
			}
		}
	}

	if(($BSF_DET["WFS_TXT_BEFORE_INPUT"] != "" OR $BSF_DET["WFS_TXT_AFTER_INPUT"] != "") AND $BSF_DET["WFS_OPTION_SELECT2"] == "Y"){ $html = '<div class="input-group-text">'; }
	if($BSF_DET["WFS_OPTION_ADD_MAIN"] != "" AND $BSF_DET["WFS_OPTION_ADD_MAIN"] == "Y"){ $html = '<div class="input-group selectAdd" style="flex-wrap:nowrap;">'; }
	
	$html .= '<select name="' . $name . '" id="'.$name.'" class="'.$class_input.'" '.$class_extra.'>'.$html_p;
	
	foreach($data_list as $_key => $_val)
	{
		$html .= '<option value="'.$_val['id'].'" ';
		//if(isset($_val['title'])){ if($_val['title'] != ""){ $html .= 'title="ภายใต้'.$_val['title'].'" '; }else{ $html .= 'title="&nbsp;" '; } }
		$html .= ' '.$_val['selected'].' ';
		if($_val['selected'] == '' AND $BSF_DET["WFS_READONLY"] == 'Y'){ $html .= $class_option; }
		$html .= '>';
		if(isset($_val['level'])){ if($_val['level'] > 0){ $html .= wf_select_gen_lv($_val['level'],'&nbsp;&nbsp;&nbsp;'); }}
		
		$html .= $_val['text'].'</option>';
	}
	$html .= '</select>';
	if(($BSF_DET["WFS_TXT_BEFORE_INPUT"] != "" OR $BSF_DET["WFS_TXT_AFTER_INPUT"] != "") AND $BSF_DET["WFS_OPTION_SELECT2"] == "Y"){ $html .= '</div>'; }
	if($BSF_DET["WFS_OPTION_ADD_MAIN"] != "" AND $BSF_DET["WFS_OPTION_ADD_MAIN"] == "Y" AND $BSF_DET["WFS_OPTION_SELECT_DATA"] != ""){
		$html .= '<button class="btn btn-outline-secondary rounded-3" type="button" data-toggle="modal"  onclick=\'open_modal("../workflow/master_mgt.php?W='.$BSF_DET["WFS_OPTION_SELECT_DATA"].'&amp;TARGET_NAME='.$BSF_DET["WFS_FIELD_NAME"].'&amp;TARGET='.$BSF_DET["WFS_ID"].'&amp;use_select2=Y", "","'.$BSF_DET["WFS_ID"].'","")\'><i class="ti ti-file-plus fs-4"></i></button></div>';
		$html .=  '<div class="modal bg-dark bg-opacity-25" id="bizModal'.$BSF_DET["WFS_ID"].'" data-bs-backdrop="static" aria-hidden="true">
        <div class="modal-dialog">
          <div class="modal-content">
            <div class="modal-header">
              <h1 class="modal-title fs-5"></h1><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body py-2"></div> 
          </div>
        </div>
      </div>';
	}
	$html .= '<small id="DUP_' . $name . '_ALERT" class="form-text text-danger" style="display:none"></small>';
	if($BSF_DET["FORM_MAIN_ID"] == "12" AND $BSF_DET["WFS_SHOW_PROVINCE"] == ""){
	$html .= '<script>
			$.getJSON("../workflow/json_amphur.json", function (json) { 
			   $("select#'.$name.'").select2({
				 data: json,
				 allowClear: true,
				 width: "100%",
			  }); 
			  ';
			  if($default != ''){ $html .= '$("select#'.$name.'").val(\''.$default.'\').trigger(\'change\');
			  '; }
	$html .= '});
			  </script>';
	 
	}
	if($BSF_DET["FORM_MAIN_ID"] == "13" AND $BSF_DET["WFS_SHOW_AMPHUR"] == ""){
	$html .= '<script>
			$.getJSON("../workflow/json_tambon.json", function (json) {
			   $("select#'.$name.'").select2({
				 data: json,
				 allowClear: true,
				 width: "100%",
			  }); 
			  ';
			  if($default != ''){ $html .= '$("select#'.$name.'").val(\''.$default.'\').trigger(\'change\');
			  '; }
	$html .= '});
			  </script>';
	 
	} 
    return $html;
}
function bsf_form_iyear($BSF_DET,$WF,$rec_main){ 
	$W = $rec_main['WF_MAIN_ID'];
	$default = $WF[$BSF_DET["WFS_FIELD_NAME_ORI"]]; 
	if($default == ""){
		$default = $BSF_DET["WFS_DEFAULT_DATA"];
	}
	$default = bsf_default_var($default,$WF,'');
	$name = $BSF_DET["WFS_FIELD_NAME"]; 
	$class_input = "form-control"; 
	if($BSF_DET["WFS_DEFINE_CLASS"] != ''){
	$class_input .= ' '.str_replace(".","",$BSF_DET["WFS_DEFINE_CLASS"]);
	}
	if($BSF_DET["WFS_OPTION_SELECT2"] == "Y"){
		$class_input .= ' select2';
	}
	if($BSF_DET["WFS_REQUIRED"] == "Y"){
		if($BSF_DET['WFS_NAME']==""){
			$r_txt = "กรุณากรอกข้อมูล";
		}else{
			$r_txt = "กรุณากรอก".$BSF_DET['WFS_NAME'];
		}
		$class_extra .= ' required '; 
	}
	if(trim((string)$BSF_DET["WFS_TOOLTIP"]) != ""){
	$class_extra .= ' data-bs-toggle="tooltip" data-bs-placement="top" data-bs-original-title="'.$BSF_DET["WFS_TOOLTIP"].'"';
	}
	if($BSF_DET["WFS_READONLY"] == 'Y'){
	$class_extra .= ' readonly="true" ';
	}
	if($BSF_DET["WFS_DISABLE"] == 'Y'){
	$class_extra .= ' disabled="true" ';
	}
	if($BSF_DET["WFS_CHECK_DUP"] == "Y"){ 
	$class_input .= ' wf_check_dup';
	}
	if($BSF_DET["WFS_NUM_STEP_JS"] > 0 OR $BSF_DET["WFS_NUM_ONCHANGE_SEND"] > 0){  
	$class_extra .= ' onChange="';
		if($BSF_DET["WFS_NUM_STEP_JS"] > 0){ 
		$class_extra .= 'bsf_change_obj'.$BSF_DET['WF_TYPE'].'_'.$BSF_DET["WFS_ID"].'(this.value);'; 
		}
		if($BSF_DET["WFS_NUM_ONCHANGE_SEND"] > 0){
		//$class_extra .= 'bsf_change_process'.$BSF_DET['WF_TYPE'].'_'.$BSF_DET["WFS_ID"].'(this.value);'; 
		}
	$class_extra .= '"';
	}
	if(trim((string)$BSF_DET["WFS_PLACEHOLDER"]) != ""){ 
	$class_extra .= ' data-placeholder="'.$BSF_DET["WFS_PLACEHOLDER"].'"';
	$html_p = '<option value="">'.$BSF_DET["WFS_PLACEHOLDER"].'</option>'; 
	}
	$data_list = array();
	
	$y_start = bsf_default_var($BSF_DET["WFS_YEAR_START"],$WF,'');
	$year_start = eval('return '.$y_start.';'); 
	if(!is_numeric($year_start)){ $year_start = date("Y")+543; }
	
	$y_end = bsf_default_var($BSF_DET["WFS_YEAR_END"],$WF,'');
	$year_end = eval('return '.$y_end.';'); 
	if(!is_numeric($year_end)){ $year_end = date("Y")+543; }
	if($year_start >= $year_end){
		for($i=$year_start;$i>=$year_end;$i--){
			$data = array();
			$data['id'] = $i;
			$data['text'] = $i;
			if($i == $default){ $data['selected'] = 'selected'; }else{ $data['selected'] = ''; }
			$data_list[] = $data;
		}
	}else{
		for($i=$year_start;$i<=$year_end;$i++){
			$data = array();
			$data['id'] = $i;
			$data['text'] = $i;
			if($i == $default){ $data['selected'] = 'selected'; }else{ $data['selected'] = ''; }
			$data_list[] = $data;
		}
	}
	
	if(($BSF_DET["WFS_TXT_BEFORE_INPUT"] != "" OR $BSF_DET["WFS_TXT_AFTER_INPUT"] != "") AND $BSF_DET["WFS_OPTION_SELECT2"] == "Y"){ $html = '<div class="input-group-text">'; }
	$html .= '<select name="' . $name . '" id="' . $name . '" class="'.$class_input.'" '.$class_extra.'>'.$html_p;
	
	foreach($data_list as $_key => $_val)
	{
		$html .= '<option value="'.$_val['id'].'" '.$_val['selected'].'>'.$_val['text'].'</option>';
	}
	$html .= '</select>';
	if(($BSF_DET["WFS_TXT_BEFORE_INPUT"] != "" OR $BSF_DET["WFS_TXT_AFTER_INPUT"] != "") AND $BSF_DET["WFS_OPTION_SELECT2"] == "Y"){ $html .= '</div>'; }
	$html .= '<small id="DUP_' . $name . '_ALERT" class="form-text text-danger" style="display:none"></small>';
    return $html;
}
function bsf_form_icoding($BSF_DET,$WF,$rec_main){
	$html = ''; 
	if($BSF_DET["WFS_CODING_AJAX"] != ''){
		$html .= '<span id="show_ajax_'.$BSF_DET["WFS_ID"].'"></span>';
		$ajax = explode('?',$BSF_DET["WFS_CODING_AJAX"]);
		$html .= '<script type="text/javascript">var dataString = "'.bsf_show_text($W,$WF,$ajax[1],$WF_TYPE).'";'.PHP_EOL;
		$html .= '$.ajax({ type: "GET",url: "'.$ajax[0].'",data: dataString,cache: false,success: function(html){ $("#show_ajax_'.$BSF_DET["WFS_ID"].'").html(html); } }); </script>';
	}
	return $html;
}
function bsf_form_iform($BSF_DET,$WF,$rec_main,$WFD){
$align_pos = array('L'=>'text-start','C'=>'text-center','R'=>'text-lg-end');
$pk_field = $rec_main["WF_FIELD_PK"];
if($BSF_DET["WFS_INPUT_FORMAT"]=="O" OR $BSF_DET["WFS_INPUT_FORMAT"]=="T"){
	if($BSF_DET["WFS_FORM_SELECT"]!=''){
	$FRM = array();
	$sql_form_O = db::query("select WF_MAIN_SHORTNAME,WF_TYPE from WF_MAIN where WF_MAIN_ID = '".$BSF_DET["WFS_FORM_SELECT"]."'");
	$rec_main_form_O = db::fetch_array($sql_form_O);
	if($WF[$pk_field] != ''){
		$wfs_fcon = '';
		if($BSF_DET["WFS_INPUT_FORMAT"]=="T"){
			$wfs_fcon = " AND WFS_ID = '".$BSF_DET["WFS_ID"]."' ";
		}
		$sql_show_form = "select * from ".$rec_main_form_O['WF_MAIN_SHORTNAME']." where WF_MAIN_ID = '".$W."' AND WFR_ID = '".$WF[$pk_field]."' ".$wfs_fcon;
		$query_frm = db::query($sql_show_form);
		$FRM=db::fetch_array($query_frm);
	}
	bsf_show_form($BSF_DET["WFS_FORM_SELECT"],'0',$FRM,$rec_main_form_O['WF_TYPE'],'','');
	}
}else{
//////////////////////F_TEMP_ID/////////////////////

if(isset($WF[$pk_field])){
	$F_TEMP_ID = $WF[$pk_field];
	$WFR = $WF[$pk_field];
}else{
	$F_TEMP_ID = bsf_random_num(10);
	$WFR = 0;
}
if($BSF_DET["WFS_FORM_ADD_POPUP"] == "Y"){
echo '<input type="hidden" name="F_TEMP_ID_'.$BSF_DET["WFS_ID"].'" id="F_TEMP_ID_'.$BSF_DET["WFS_ID"].'" value="'.$F_TEMP_ID.'">';
}
/////////////////////Header//////////////////////
echo '<div class="row">';
echo '<input type="text" class="frm_rows" id="wfsflow-chk-'.$BSF_DET["WFS_ID"].'" value="" ';
if($BSF_DET["WFS_REQUIRED"]=="Y"){ echo ' required aria-required="true" '; }
echo 'style="opacity:0;width:1px;height:1px;position:absolute;">';
if($BSF_DET["WFS_COLUMN_TYPE"] == "1"){
	echo '<div class="col-8"><label class="form-label">'.$BSF_DET["WFS_NAME"].'</label></div>';
	echo '<div class="col-4 text-lg-end">';
}else{
	echo '<div class="col-12 '.$align_pos[$BSF_DET["WFS_COLUMN_RIGHT_ALIGN"]].'">';
}
/////////////////////////Add//////////////////////
if($BSF_DET["WFS_FORM_ADD_STATUS"] == "Y"){
global $WF_TEXT_MAIN_ADD;
if($BSF_DET["WFS_FORM_ADD_LABEL"] != ''){ $WF_TEXT_MAIN_ADD_F = $BSF_DET["WFS_FORM_ADD_LABEL"];}else{ $WF_TEXT_MAIN_ADD_F = $WF_TEXT_MAIN_ADD; }
	if($BSF_DET["WFS_FORM_ADD_POPUP"] == "Y"){
		if($BSF_DET["WFS_FORM_POPUP"] == "P"){
			$WFS_ONCLICK = " onclick=\"PopupCenter('";
			if($_SESSION["WF_USER_ID"] != ""){ $WFS_ONCLICK .= '../workflow/'; } 
			$WFS_ONCLICK .="form_mgt.php?W=".$BSF_DET["WFS_FORM_SELECT"]."&WFS=".$BSF_DET["WFS_ID"]."&WFD=".$WFD."&WFR_ID=".$WFR."&F_TEMP_ID=".$F_TEMP_ID."&WF_POP=P&wfp=".conText($_GET['wfp'])."', '','900','600')\"";
		}else{
			$WFS_ONCLICK = " onclick=\"open_modal('";
			if($_SESSION["WF_USER_ID"] != ""){ $WFS_ONCLICK .= '../workflow/'; } 
			$WFS_ONCLICK .="form_mgt.php?W=".$BSF_DET["WFS_FORM_SELECT"]."&WFS=".$BSF_DET["WFS_ID"]."&WFD=".$WFD."&WFR_ID=".$WFR."&F_TEMP_ID=".$F_TEMP_ID."&wfp=".conText($_GET['wfp'])."', '".$WF_TEXT_MAIN_ADD_F."','".$BSF_DET["WFS_ID"]."','".$BSF_DET["WFS_MODAL_SIZE"]."')\"";
		}
	}elseif($BSF_DET["WFS_FORM_ADD_POPUP"] == "N" AND $BSF_DET["WFS_INLINE_FORM"] == ""){
		$WFS_ONCLICK = " onClick=\"get_wfs_show('wfs_show".$BSF_DET["WFS_ID"]."','";
			if($_SESSION["WF_USER_ID"] != ""){ $WFS_ONCLICK .= '../workflow/'; } 
			$WFS_ONCLICK .="form_add.php','W=".$BSF_DET["WFS_FORM_SELECT"]."&WFD=".$WFD."&WFS=".$BSF_DET["WFS_ID"]."&WFR=".$WFR."&F_TEMP_ID=".$WFR."&wfp=".conText($_GET['wfp'])."','GET','A');\"";
	}elseif($BSF_DET["WFS_FORM_ADD_POPUP"] == "N" AND $BSF_DET["WFS_INLINE_FORM"] == "Y"){
		$WFS_ONCLICK = "onClick=\"get_wfs_show('wfs_show".$BSF_DET["WFS_ID"]."','";
			if($_SESSION["WF_USER_ID"] != ""){ $WFS_ONCLICK .= '../workflow/'; } 
			$WFS_ONCLICK .="form_add.php','W=".$BSF_DET["WFS_FORM_SELECT"]."&WFD=".$WFD."&WFS=".$BSF_DET["WFS_ID"]."&WFR=".$WFR."&F_TEMP_ID=".$WFR."&wfp=".conText($_GET['wfp'])."','GET','A');\"";
	}
echo '<button type="button" class="btn btn-mini btn-light-primary" ';
if($BSF_DET["WFS_FORM_ADD_RESIZE"] == 'Y'){
' data-bs-toggle="tooltip" data-bs-placement="top" data-bs-original-title="'.$WF_TEXT_MAIN_ADD_F.'"';
}
echo ' title="'.$WF_TEXT_MAIN_ADD_F.'" '.$WFS_ONCLICK.'><i class="fas fa-plus"></i> ';
if($BSF_DET["WFS_FORM_ADD_RESIZE"] != 'Y'){ echo $WF_TEXT_MAIN_ADD_F; }
echo '</button>';	
}
/////////////////////////End Add//////////////////////
echo '</div></div>';

////////////////////////////////////////////////
echo '<div class="row text-start">';
echo '<span id="WFS_FORM_'.$BSF_DET["WFS_ID"].'"></span>';
echo '<script type="text/javascript">$(document).ready(function() { get_wfs_show(\'WFS_FORM_'.$BSF_DET["WFS_ID"].'\',\'';
						if($_SESSION["WF_USER_ID"] != ""){ echo '../workflow/'; } 
						echo 'form_main.php\',\'W='.$BSF_DET["WFS_FORM_SELECT"].'&WFD='.$WFD.'&WFS='.$BSF_DET["WFS_ID"].'&WFR='.$WF[$pk_field].'&F_TEMP_ID='.$F_TEMP_ID.'&WFR_ID='.$WF[$pk_field].'&wfp='.conText($_GET['wfp']).'\',\'GET\',\'\'); });</script>';
}
echo '</div>';
if($BSF_DET["WFS_FORM_POPUP"] == "" OR $BSF_DET["WFS_FORM_POPUP"] == "Y"){
echo '<div class="modal bg-dark bg-opacity-25" id="bizModal'.$BSF_DET["WFS_ID"].'" data-bs-backdrop="static" aria-hidden="true">
        <div class="modal-dialog">
          <div class="modal-content">
            <div class="modal-header">
              <h1 class="modal-title fs-5"></h1><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body py-2"></div> 
          </div>
        </div>
      </div>';
}
}
function bsf_show_itext($filed,$rec_main,$A_DATA,$form_step,$label=''){ //replace ##FIELD!! จาก table และหาค่า text
global $WF_LIST_DATA;
$mtext_label_s = '<span class="badge text-start f-14 bg-light-dark" id="'.$filed.'_VIEW_TEXT">';
$mtext_label_e = '';
if($form_step["WFS_TXT_BEFORE_INPUT"] != ""){ $mtext_label_s .= wf_convert_var($form_step["WFS_TXT_BEFORE_INPUT"],'Y').' '; }
if($form_step["WFS_TXT_AFTER_INPUT"] != ""){ $mtext_label_e .= ' '.wf_convert_var($form_step["WFS_TXT_AFTER_INPUT"],'Y'); }
$mtext_label_e .= '</span>';
$mtext_default_null = '-'; 
$W = $rec_main['WF_MAIN_ID'];
$value = ""; 
if(isset($form_step["FORM_MAIN_ID"])){
	switch($form_step["FORM_MAIN_ID"]){
		case '':
		case '1':	//textbox
		case '2': 	//textarea 
if($A_DATA[$filed] != ''){
if($form_step["WFS_INPUT_FORMAT"] == "P"){ $value = "*********"; 
}elseif($form_step["WFS_INPUT_FORMAT"] == "N"){ $value = wf_number_format($A_DATA[$filed],0); 
}elseif($form_step["WFS_INPUT_FORMAT"] == "N1"){ $value = wf_number_format($A_DATA[$filed],1); 
}elseif($form_step["WFS_INPUT_FORMAT"] == "N2"){ $value = wf_number_format($A_DATA[$filed],2); 
}elseif($form_step["WFS_INPUT_FORMAT"] == "N3"){	$value = wf_number_format($A_DATA[$filed],3); 
}elseif($form_step["WFS_INPUT_FORMAT"] == "N4"){	$value = wf_number_format($A_DATA[$filed],4); 
}elseif($form_step["WFS_INPUT_FORMAT"] == "N5"){	$value = wf_number_format($A_DATA[$filed],5); 
}elseif($form_step["WFS_INPUT_FORMAT"] == "N6"){	$value = wf_number_format($A_DATA[$filed],6); 
}elseif($form_step["WFS_INPUT_FORMAT"] == "TU"){	$value = mb_strtoupper($A_DATA[$filed],'UTF-8'); 
}elseif($form_step["WFS_INPUT_FORMAT"] == "TL"){	$value = mb_strtolower($A_DATA[$filed],'UTF-8'); 
}elseif($form_step["WFS_INPUT_FORMAT"] == "TC"){	$value = ucfirst(mb_strtolower($A_DATA[$filed],'UTF-8'));
}elseif($form_step["WFS_INPUT_FORMAT"] == "ED"){	
	/* $pk_field = $rec_main["WF_FIELD_PK"];
	if(file_exists($editor_folder2.'/e_'.$form_step["WFS_FIELD_NAME"].'_'.$A_DATA[$pk_field].'.tmp')){
	$fp = @fopen($editor_folder2.'/e_'.$form_step["WFS_FIELD_NAME"].'_'.$A_DATA[$pk_field].'.tmp','r');
	$value = @fread($fp, @filesize($editor_folder2.'/e_'.$form_step["WFS_FIELD_NAME"].'_'.$A_DATA[$pk_field].'.tmp'));
	@fclose($fp);
	} */
    $value = wf_convert_var($A_DATA[$filed],'Y');
}else{ $value = $A_DATA[$filed];  }
}
		
		if($label == "Y"){
			if($value == ''){ $value = $mtext_default_null; }
			if($form_step["FORM_MAIN_ID"]=="2" AND $form_step["WFS_INPUT_FORMAT"] != "ED"){ $value = nl2br($value); } $value = $mtext_label_s.$value.$mtext_label_e; 
		}
		break;
	case '4': 	//radio  
	case '7': 	//hidden
	case '9': 	//select
	if($A_DATA[$filed] != ""){
		$pk_field = $rec_main["WF_FIELD_PK"];
		$result_relation = array();
		$result_relation = wf_call_irelation($form_step,$rec_main,$A_DATA[$pk_field],$A_DATA,'',$A_DATA[$filed]);
		if(count($result_relation)>0){ 
			foreach($result_relation as $rel_val){
				if($rel_val["selected"] == "selected" OR $rel_val["selected"] == "checked"){
					$value = $rel_val["text"];
				}
			}
		}
	}
	if($label == "Y"){ if($value == ''){ $value = $mtext_default_null; } $value = $mtext_label_s.$value.$mtext_label_e; }
		break;
	case '11': 	//Province
if($A_DATA[$filed] != ""){
	$aflag = "";
	if($A_DATA[$filed] != "10"){ $aflag = ""; }
if($_SESSION['WF_LANGUAGE'] == ""){ $pr_name = "PROVINCE_NAME"; }else{ $pr_name = "PROVINCE_NAME_EN"; }
$sql_option = db::query("select ".$pr_name." from G_PROVINCE where PROVINCE_CODE = '".$A_DATA[$filed]."'  ");
$rec_option = db::fetch_array($sql_option);
$value = $aflag.$rec_option[$pr_name];
}
if($label == "Y"){ if($value == ''){ $value = $mtext_default_null; } $value = $mtext_label_s.$value.$mtext_label_e; }
		break;
	case '12': 	//Amphur
if($A_DATA[$filed] != ""){
	$aflag = "";
//	if(substr($A_DATA[$filed],0,2) != "10"){ $aflag = "อ."; }else{ $aflag = "เขต"; }
if($_SESSION['WF_LANGUAGE'] == ""){ $amp_name = "AMPHUR_NAME"; }else{ $amp_name = "AMPHUR_NAME_EN"; }
$sql_option = db::query("select ".$amp_name." from G_AMPHUR where PROVINCE_CODE = '".substr($A_DATA[$filed],0,2)."' AND AMPHUR_CODE = '".substr($A_DATA[$filed],2,2)."' ");
$rec_option = db::fetch_array($sql_option);
$value = $aflag.str_replace("*","",$rec_option[$amp_name]);
}
if($label == "Y"){ if($value == ''){ $value = $mtext_default_null; } $value = $mtext_label_s.$value.$mtext_label_e; }
		break;
	case '13': 	//Tambon
if($A_DATA[$filed] != ""){
	$aflag = "";
//	if(substr($A_DATA[$filed],0,2) != "10"){ $aflag = "ต."; }else{ $aflag = "แขวง"; }
if($_SESSION['WF_LANGUAGE'] == ""){ $tam_name = "TAMBON_NAME"; }else{ $tam_name = "TAMBON_NAME_EN"; }
$sql_option = db::query("select ".$tam_name." from G_TAMBON where PROVINCE_CODE = '".substr($A_DATA[$filed],0,2)."' AND AMPHUR_CODE = '".substr($A_DATA[$filed],2,2)."' AND TAMBON_CODE = '".substr($A_DATA[$filed],4,2)."'");
$rec_option = db::fetch_array($sql_option);
$value = $aflag.str_replace("*","",$rec_option[$tam_name]);
}
if($label == "Y"){ if($value == ''){ $value = $mtext_default_null; } $value = $mtext_label_s.$value.$mtext_label_e; }
		break;
	case '14': 	//Zipcode
		$value = $A_DATA[$filed];
		if($label == "Y"){ $value = $mtext_label_s.$value.$mtext_label_e; }
		break;
	case '15': 	//View 
		if($form_step["WFS_OPTION_SELECT_DATA"] != ''){
			$sql_detail = db::query("select WF_MAIN_ID from WF_DETAIL where WFD_ID = '".$form_step["WFS_OPTION_SELECT_DATA"]."'");
			$rec_detail = db::fetch_array($sql_detail); 
			$value = bsf_show_form($rec_detail['WF_MAIN_ID'], $form_step["WFS_OPTION_SELECT_DATA"], $A_DATA, 'W', '', '', 'Y');
			if($label == "Y"){ $value = $mtext_label_s.$value.$mtext_label_e; }
		}
		break;
	case '17': 	//Year 
		$value = $A_DATA[$filed];
		if($label == "Y"){ $value = $mtext_label_s.$value.$mtext_label_e; }
		break;
	case '3': //date  
		if($form_step["WFS_CALENDAR_EN"] == "Y"){
		$value = db2date_en($A_DATA[$val[2]]);
		}else{
		$value = db2date($A_DATA[$filed]);
		}
		if($label == "Y"){ 
		if($value == ''){ $value = $mtext_default_null; }
		$value = $mtext_label_s.$value.$mtext_label_e; }
		break;
	case '5': //checkbox
		if($form_step["WFS_INPUT_FORMAT"] == "M"){
			$pk_field = $rec_main["WF_FIELD_PK"];
			$data_list = array();
			$data_list = wf_call_irelation($form_step,$rec_main,$A_DATA[$pk_field],$A_DATA);
			if(count($data_list)>0){ 
				foreach($data_list as $wf_v){
					if($wf_v['checked'] == 'checked'){
						$value .= $mtext_label_s.'<i class="fas fa-check-square f-16 text-success"></i> '.$wf_v['text'].$mtext_label_e.' ';
					}
				} 
			}
		}else{ 
		
			if($WF_LIST_DATA=="Y" AND $form_step["WFS_OPTION_SHORT_SELECT"] == "Y"){
				if($A_DATA[$filed] != ''){
					$value = '<i class="fas fa-check-square f-16 text-success"></i>';
				}else{
					$value = '<i class="fas fa-minus-square f-16 text-secondary"></i>';
				}
			}else{ 
				if($A_DATA[$filed] != ''){
					$value = '<i class="fas fa-check-square f-16 text-success"></i> '.$form_step["WFS_NAME"];
				}else{
					$value = '<i class="fas fa-minus-square f-16 text-secondary"></i> '.$form_step["WFS_NAME"];
				}
				
			}
			if($label == "Y"){ $value = $mtext_label_s.$value.$mtext_label_e; }
		}
		break;
	case '6': //browsefile 
		$pk_field = $rec_main["WF_FIELD_PK"]; 
		//$value = bsa_show($form_step["WFS_FIELD_NAME"],$A_DATA[$pk_field],$W,$form_step["WFS_FILE_ORDER"],$form_step["WFS_FILE_LIGHTBOX"],'N');
		$value = bsa_ishow($form_step,$A_DATA,$rec_main,'N');
		break;
	case '8': //ข้อความ
		if($form_step["WFS_TXT_C_RIGHT"] != ""){ 
		if($label == "Y"){
		$form_step["WFS_TXT_C_RIGHT_HIGHLIGHT"] = 'N';
		}
		$value = bsf_form_itextshow($form_step,$A_DATA,$rec_main,'R');
		}
		if($label == "Y"){ $value = $mtext_label_s.$value.$mtext_label_e; }
		break;	
	case '16': //form
		if($label == "Y"){
			$pk_field = $rec_main["WF_FIELD_PK"]; 
			if($form_step["WFS_INPUT_FORMAT"]=="O" OR $form_step["WFS_INPUT_FORMAT"]=="T"){
					if($form_step["WFS_FORM_SELECT"]!=''){
					echo '</div><div class="form-group row">';
					$FRM = array();
					$sql_form_O = db::query("select WF_MAIN_SHORTNAME,WF_TYPE from WF_MAIN where WF_MAIN_ID = '".$form_step["WFS_FORM_SELECT"]."'");
					$rec_main_form_O = db::fetch_array($sql_form_O);
					if($WF[$pk_field] != ''){
						$wfs_fcon = '';
						if($form_step["WFS_INPUT_FORMAT"]=="T"){
							$wfs_fcon = " AND WFS_ID = '".$form_step["WFS_ID"]."' ";
						}
						$sql_show_form = "select * from ".$rec_main_form_O['WF_MAIN_SHORTNAME']." where WF_MAIN_ID = '".$W."' AND WFR_ID = '".$A_DATA[$pk_field]."' ".$wfs_fcon;
						$query_frm = db::query($sql_show_form);
						$FRM=db::fetch_array($query_frm);
					}
					bsf_show_form($form_step["WFS_FORM_SELECT"],'0',$FRM,$rec_main_form_O['WF_TYPE'],'','','Y');
					
					}
				}else{ 
				$rand = bsf_random(10);
				echo '<span id="WFS_FORM_'.$form_step["WFS_ID"].'_'.$rand.'"></span>'; 
				echo '<script type="text/javascript">get_wfs_show(\'WFS_FORM_'.$form_step["WFS_ID"].'_'.$rand.'\',\'';
						if($_SESSION["WF_USER_ID"] != ""){ echo '../workflow/'; } 
						echo 'form_main.php\',\'W='.$form_step["WFS_FORM_SELECT"].'&WFD='.$form_step["WFD_ID"].'&WFS='.$form_step["WFS_ID"].'&WFR='.$A_DATA[$pk_field].'&F_TEMP_ID='.$A_DATA[$pk_field].'&WF_VIEW=VIEW&WFR_ID='.$A_DATA[$pk_field].'&wfp='.conText($_GET['wfp']).'&rand='.$rand.'\',\'GET\',\'\');</script>';
				echo '<div class="modal bg-dark bg-opacity-25" id="bizModal'.$form_step["WFS_ID"].'V'.$rand.'" data-bs-backdrop="static" aria-hidden="true">
						<div class="modal-dialog">
						  <div class="modal-content">
							<div class="modal-header">
							  <h1 class="modal-title fs-5"></h1><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
							</div>
							<div class="modal-body py-2"></div> 
						  </div>
						</div>
					  </div>';
				}
		}
		break;
		}
return $value; 
}
}
function wf_call_irelation($BSF_DET,$rec_main,$WFR,$WF=array(),$ONCHANGE='',$show_value='',$show_all='',$wftype=''){ 
	global $system_conf;
	if($BSF_DET["WFS_FIELD_NAME_ORI"] == ""){ $BSF_DET["WFS_FIELD_NAME_ORI"] = $BSF_DET["WFS_FIELD_NAME"]; }
	$PARENT='';
	$data_list=array(); 
	$wf_i = "0"; 
	$level = "0";
	$W = $BSF_DET['WF_MAIN_ID'];
	$WF_TYPE = $BSF_DET['WF_TYPE'];
	$txt_cjava = '';
	$NO_QUERY='';
	if($BSF_DET["FORM_MAIN_ID"]=="9"){ $data_use = "selected"; }else{ $data_use = "checked"; }
 
	if($BSF_DET["WFS_OPTION_SELECT_DATA"] != "" OR ($BSF_DET["WFS_OPTION_FULL_SQL"] == "Y" AND trim((string)$BSF_DET["WFS_OPTION_COND"]) != "")){ //ดึงข้อมูลจากตารางอื่น  WF_VIEW_COL_DATA
		if(is_numeric($BSF_DET["WFS_OPTION_SELECT_DATA"]) OR ($BSF_DET["WFS_OPTION_FULL_SQL"] == "Y" AND trim((string)$BSF_DET["WFS_OPTION_COND"]) != "")){ //Workflow,Form,Master
			if($show_value != ''){
					if(trim((string)$BSF_DET["WFS_OPTION_SHOW_VALUE"]) == ""){

						if(!is_numeric($show_value)){  $NO_QUERY='Y'; $rec_opt = array(); }
					} 
			}
			if($BSF_DET["WFS_OPTION_SELECT_DATA"] == ""){
				$NO_QUERY='Y'; $rec_opt = array();
				$BSF_DET['WFS_OPTION_SQL_VALUE'] = "Y";
			}
			if($show_all == "Y" AND $BSF_DET["WFS_OPTION_FULL_SQL"] == ""){
				$BSF_DET["WFS_OPTION_COND"] = "";
			}
			if($NO_QUERY==''){
			$sql_opt_main = db::query("select WF_MAIN_ID,WF_MAIN_SHORTNAME,WF_FIELD_PK,WF_VIEW_COL_DATA,WF_MAIN_DEFAULT_ORDER,WF_TYPE,WF_PARENT_USE,WF_PARENT_FIELD,WF_PARENT_FIELD_ORDER from WF_MAIN where WF_MAIN_ID = '".$BSF_DET["WFS_OPTION_SELECT_DATA"]."'");
			$rec_opt = db::fetch_array($sql_opt_main);
			}
			$opt_where = "";
			/*if($show_value == ''){
				if($rec_opt['WF_PARENT_USE'] != '' AND $rec_opt['WF_PARENT_FIELD'] != ''){	
					if($PARENT == ""){
						$opt_where .= "WHERE (".$rec_opt['WF_PARENT_FIELD']." IS NULL OR ".$rec_opt['WF_PARENT_FIELD']." = '' OR ".$rec_opt['WF_PARENT_FIELD']." = '0') ";
					}else{
						$opt_where .= "WHERE (".$rec_opt['WF_PARENT_FIELD']." = '".$PARENT."') ";
					}
				}
			}*/
			
			if($BSF_DET["WFS_OPTION_FULL_SQL"] == "Y" AND trim((string)$BSF_DET["WFS_OPTION_COND"]) != ""){
				$opt_where = bsf_show_field($W,$WF,trim((string)$BSF_DET["WFS_OPTION_COND"]),$WF_TYPE); 
				preg_match_all("/(#@)([a-zA-Z0-9_]+)(!!)/", $opt_where, $new_sql2, PREG_SET_ORDER);
				foreach ($new_sql2 as $val_new) {
					if($ONCHANGE == ''){ 
						$val = $WF[$val_new[2]];
					}else{
						$val = $_GET[$val_new[2]];
						//$val = $ONCHANGE;
					} 
					$opt_where = str_replace("#@".$val_new[2]."!!",$val,$opt_where);
				}
				preg_match_all("/(#!)([a-zA-Z0-9_]+)(!!)/", $opt_where, $new_sql2, PREG_SET_ORDER);
				foreach ($new_sql2 as $val_new) { 
					if($ONCHANGE == ''){ 
						$val = $WF[$val_new[2]];
					}else{
						//$val = $ONCHANGE;
						$val = $_GET[$val_new[2]];
					} 
					$opt_where = str_replace("#@".$val_new[2]."!!",$val,$opt_where);
				}
				$sql_opt_list_q = $opt_where;
			}else{
				if($show_value != ''){
					if($opt_where == ""){
						$opt_where = " WHERE 1=1 ";
					}
					if(trim((string)$BSF_DET["WFS_OPTION_SHOW_VALUE"]) != ""){
						$opt_where .= " AND ".bsf_gen_select(trim($BSF_DET["WFS_OPTION_SHOW_VALUE"]))." = '".$show_value."'";
					}else{
						if(!is_numeric($show_value)){ $show_value = ''; $NO_QUERY='Y'; }
						$opt_where .= " AND ".$rec_opt["WF_FIELD_PK"]." = '".$show_value."'";
					} 
				}else{
				if(trim((string)$BSF_DET["WFS_OPTION_COND"]) != ""){
					if($opt_where == ""){
						$opt_where = " WHERE 1=1 ";
					} 
					$opt_where .= " AND ".bsf_show_field($W,$WF,trim((string)$BSF_DET["WFS_OPTION_COND"]),$WF_TYPE);
					preg_match_all("/(#@)([a-zA-Z0-9_]+)(!!)/", $opt_where, $new_sql2, PREG_SET_ORDER);
					foreach ($new_sql2 as $val_new) { 
						if($ONCHANGE == ''){ 
							$val = $WF[$val_new[2]];
						}else{
							$val = $_GET[$val_new[2]];
							//$val = $ONCHANGE;
						} 
						$opt_where = str_replace("#@".$val_new[2]."!!",(string)$val,$opt_where);
					}
				}
				}
				$sql_opt_list_q = "select * from ".$rec_opt["WF_MAIN_SHORTNAME"]." ".$opt_where;
				if($rec_opt['WF_PARENT_USE'] != '' AND $rec_opt['WF_PARENT_FIELD'] != '' AND $rec_opt['WF_PARENT_FIELD_ORDER'] != ''){
					$has_order = bsf_check_orderby($sql_opt_list_q);
					if($has_order != "Y"){ 
					$sql_opt_list_q = $sql_opt_list_q . ' ORDER BY '.$rec_opt['WF_PARENT_FIELD_ORDER']; 
					}
				}
			}
			$sql_opt_list_q = htmlspecialchars_decode($sql_opt_list_q, ENT_QUOTES);
		if($NO_QUERY=='' OR $BSF_DET["WFS_OPTION_SELECT_DATA"] == ""){ // WFS_OPTION_SHOW_VALUE
			$sql_opt_list = db::query($sql_opt_list_q);
			$sql_opt_rows = db::num_rows($sql_opt_list);
			$pk_opt1 = $rec_opt["WF_FIELD_PK"];

			if($sql_opt_rows> 0){ 
			
				if(trim((string)$BSF_DET["WFS_OPTION_SHOW_FIELD"]) != ""){
					$txt_opt = $BSF_DET["WFS_OPTION_SHOW_FIELD"]; 
				}elseif($rec_opt["WF_VIEW_COL_DATA"] != ''){ 
					$txt_opt = str_replace("|"," ",$rec_opt["WF_VIEW_COL_DATA"]);
				}else{
					$txt_opt1 = '';
					$sql_step_f = db::query("SELECT WFS_FIELD_NAME FROM WF_STEP_FORM WHERE WF_MAIN_ID='".$rec_opt['WF_MAIN_ID']."' AND WF_TYPE = '".$rec_opt["WF_TYPE"]."' ORDER BY WFS_ORDER,WFS_OFFSET");
					while($rec_sf = db::fetch_array($sql_step_f)){
						$txt_opt1 .= '##'.$rec_sf["WFS_FIELD_NAME"].'!! ';
					}
					$txt_opt = trim((string)$txt_opt1);
				}
			
				$arr_b_fetch = array(); 
				if($BSF_DET['WFS_OPTION_SQL_VALUE'] == "" AND $BSF_DET["WFS_OPTION_SHOW_VALUE"] != "" AND str_contains($BSF_DET["WFS_OPTION_SHOW_VALUE"], '##')){
					preg_match_all("/(##)([a-zA-Z0-9_]+)(!!)/", $BSF_DET["WFS_OPTION_SHOW_VALUE"], $matches, PREG_SET_ORDER);
					foreach ($matches as $val){
						$form_step = db::query_first("SELECT WSF.* FROM WF_STEP_FORM WSF WHERE WSF.WF_MAIN_ID = '".$rec_opt['WF_MAIN_ID']."' AND WSF.WF_TYPE = '".$rec_opt["WF_TYPE"]."' AND WSF.WFS_FIELD_NAME ='".$val[2]."' ORDER BY WFS_MAIN_SHOW ASC");
						if($form_step['WFS_ID'] != ''){
						$arr_b_fetch[$val[2]] = $form_step;
						}
					}
				}
				if($BSF_DET['WFS_OPTION_SQL_VALUE'] == "" AND $txt_opt != "" AND str_contains($txt_opt, '##')){
					preg_match_all("/(##)([a-zA-Z0-9_]+)(!!)/", $txt_opt, $matches, PREG_SET_ORDER);
					foreach ($matches as $val){
						$form_step = db::query_first("SELECT WSF.* FROM WF_STEP_FORM WSF WHERE WSF.WF_MAIN_ID = '".$rec_opt['WF_MAIN_ID']."' AND WSF.WF_TYPE = '".$rec_opt["WF_TYPE"]."' AND WSF.WFS_FIELD_NAME ='".$val[2]."' ORDER BY WFS_MAIN_SHOW ASC");
						if($form_step['WFS_ID'] != ''){
						$arr_b_fetch[$val[2]] = $form_step;
						}
					}
				}
				
				
				
				
			while($rec_o = db::fetch_array($sql_opt_list)){ 
				if(trim((string)$BSF_DET["WFS_OPTION_SHOW_VALUE"]) != ""){ 
					if(trim((string)$BSF_DET["WFS_OPTION_SQL_VALUE"]) != ""){
						$pk_opt = bsf_show_field($BSF_DET["WFS_OPTION_SELECT_DATA"],$rec_o,$BSF_DET["WFS_OPTION_SHOW_VALUE"],$rec_opt["WF_TYPE"]);
					}else{
						
					//	$pk_opt = bsf_show_text($BSF_DET["WFS_OPTION_SELECT_DATA"],$rec_o,$BSF_DET["WFS_OPTION_SHOW_VALUE"],$rec_opt["WF_TYPE"]);
						$data = ""; 
						if($BSF_DET["WFS_OPTION_SHOW_VALUE"] !="" AND str_contains($BSF_DET["WFS_OPTION_SHOW_VALUE"], '##')){
							$search  = array();
							$replace = array(); 
							preg_match_all("/(##)([a-zA-Z0-9_]+)(!!)/", $BSF_DET["WFS_OPTION_SHOW_VALUE"], $matches, PREG_SET_ORDER);
							foreach ($matches as $val){ 
								if(isset($arr_b_fetch[$val[2]])){
								$value = bsf_show_itext($val[2],$rec_opt,$rec_o,$arr_b_fetch[$val[2]]); 
								}else{
								$value = bsf_show_field($BSF_DET["WFS_OPTION_SELECT_DATA"],$rec_o,"##".$val[2]."!!",$rec_opt["WF_TYPE"]);
								}
								array_push($search,"##".$val[2]."!!"); 
								array_push($replace,$value); 
							}
							$contents = str_replace($search, $replace, $BSF_DET["WFS_OPTION_SHOW_VALUE"]);
							$data = str_replace("##!!", "", $contents); 
						} 
						$pk_opt = $data;
					
					}
				}else{ 
					$pk_opt = $rec_o[$pk_opt1];
				}

				if(trim((string)$BSF_DET["WFS_OPTION_SQL_VALUE"]) != ""){
					$txt_label = bsf_show_field($BSF_DET["WFS_OPTION_SELECT_DATA"],$rec_o,$txt_opt,$rec_opt["WF_TYPE"]);
					$txt_label = bsf_show_field($W,$WF,$txt_label,$WF_TYPE);
				}else{
					$data = ""; 
					if($txt_opt !="" AND str_contains($txt_opt, '##')){
						$search  = array();
						$replace = array(); 
						preg_match_all("/(##)([a-zA-Z0-9_]+)(!!)/", $txt_opt, $matches, PREG_SET_ORDER);
						foreach ($matches as $val){  
							if(isset($arr_b_fetch[$val[2]])){
							$value = bsf_show_itext($val[2],$rec_opt,$rec_o,$arr_b_fetch[$val[2]]); 
							}else{
							$value = bsf_show_field($BSF_DET["WFS_OPTION_SELECT_DATA"],$rec_o,"##".$val[2]."!!",$rec_opt["WF_TYPE"]);
							}
							array_push($search,"##".$val[2]."!!"); 
							array_push($replace,$value); 
						}
						$contents = str_replace($search, $replace, $txt_opt);
						$data = str_replace("##!!", "", $contents); 
					} 
					$txt_label = $data;
					//$txt_label = bsf_show_text($BSF_DET["WFS_OPTION_SELECT_DATA"],$rec_o,$txt_opt,$rec_opt["WF_TYPE"]);
					//$txt_label = bsf_show_text($W,$WF,$txt_label,$WF_TYPE);
				}
				$txt_label = htmlspecialchars_decode($txt_label,ENT_QUOTES);
				if($BSF_DET["FORM_MAIN_ID"]=="5"){//checkbox
				
					$data_list[$wf_i]["id"] = $rec_o[$pk_opt1]; //value in checkbox
					$data_list[$wf_i]["name"] = $BSF_DET["WFS_FIELD_NAME_ORI"].'_'.$wf_i; //name checkbox
					$data_list[$wf_i]["text"] = $txt_label;  //label
					if($rec_opt['WF_PARENT_USE'] != '' AND $rec_opt['WF_PARENT_FIELD'] != ''){
						if($rec_o[$rec_opt['WF_PARENT_FIELD']] == ""){
						$data_list[$wf_i]["parent"] = '0'; 	
						}else{
						$data_list[$wf_i]["parent"] = $rec_o[$rec_opt['WF_PARENT_FIELD']]; 
						}
					}
					$data_list[$wf_i]["opt"] = "M_".$rec_o[$pk_opt1];  //option hidden
				
					if($wftype == "S"){ 
						if($WF[$data_list[$wf_i]["name"]] == $rec_o[$pk_opt1]){
							$data_list[$wf_i]["checked"] = $data_use;	
						}else{
							$data_list[$wf_i]["checked"] = "";
						}	
					}else{
					$sql_opt = db::query("SELECT COUNT(CHECKBOX_ID) AS CHECKBOX_ID FROM WF_CHECKBOX WHERE WFS_FIELD_NAME = '".$BSF_DET["WFS_FIELD_NAME_ORI"]."' AND WFR_ID = '".$WFR."' AND CHECKBOX_TYPE = 'M' AND CHECKBOX_REF = '".$rec_o[$pk_opt1]."' AND W_ID = '".$W."'");
					$num_opt=db::fetch_array($sql_opt);
					if($num_opt["CHECKBOX_ID"] > 0 AND $WFR != ''){
						$data_list[$wf_i]["checked"] = $data_use;	
					}else{
						$data_list[$wf_i]["checked"] = "";
					}
					}
					$wf_i++;
				}else{ 
					$data_list[$wf_i]["id"] = $pk_opt;
					$data_list[$wf_i]["text"] = $txt_label; 
					if($rec_opt['WF_PARENT_USE'] != '' AND $rec_opt['WF_PARENT_FIELD'] != ''){
						if($rec_o[$rec_opt['WF_PARENT_FIELD']] == ""){
						$data_list[$wf_i]["parent"] = '0'; 	
						}else{
						$data_list[$wf_i]["parent"] = $rec_o[$rec_opt['WF_PARENT_FIELD']]; 
						}
					}
					$data_list[$wf_i]["selected"] = ''; 
					if($WF[$BSF_DET["WFS_FIELD_NAME_ORI"]] == $pk_opt AND $WF[$BSF_DET["WFS_FIELD_NAME_ORI"]] != ''){
						$data_list[$wf_i]["selected"] = $data_use; 
					}else{
						$data_list[$wf_i]["selected"] = '';
					}
					$wf_i++;
				}
				/*if($show_value == ''){
					if($rec_opt['WF_PARENT_USE'] != '' AND $rec_opt['WF_PARENT_FIELD'] != '' AND $pk_opt != ""){
						$level++;
						wf_call_relation($WFS,$WFR,$WF,$ONCHANGE,$show_value,$pk_opt);
						$level--;
					}
				}*/
			}
			}
		}
			
		}elseif($BSF_DET["WFS_OPTION_SELECT_DATA"] == "S_U" OR $BSF_DET["WFS_OPTION_SELECT_DATA"] == "S_P" OR $BSF_DET["WFS_OPTION_SELECT_DATA"] == "S_D"){
			$opt_where = "";
			if(trim((string)$BSF_DET["WFS_OPTION_COND"]) != ""){
				$opt_where = " AND ".bsf_show_text($W,$WF,trim((string)$BSF_DET["WFS_OPTION_COND"]),$WF_TYPE);
				preg_match_all("/(#@)([a-zA-Z0-9_]+)(!!)/", $opt_where, $new_sql2, PREG_SET_ORDER);
				foreach ($new_sql2 as $val_new) { 
					if($ONCHANGE == ''){ 
						$val = $WF[$val_new[2]];
					}else{
						$val = $_GET[$val_new[2]];
						//$val = $ONCHANGE;
					} 
					$opt_where = str_replace("#@".$val_new[2]."!!",$val,$opt_where);
				}
			}
			
			if($show_value != ''){
				if($BSF_DET["WFS_OPTION_SELECT_DATA"] == "S_U"){
					$pk_o = "USR_ID";
				}elseif($BSF_DET["WFS_OPTION_SELECT_DATA"] == "S_P"){
					$pk_o = "POS_ID";
				}elseif($BSF_DET["WFS_OPTION_SELECT_DATA"] == "S_D"){
					$pk_o = "DEP_ID";
				}
				$opt_where = " AND ".$pk_o." = '".$show_value."' ".$opt_where;
			}
			if($BSF_DET["WFS_OPTION_SELECT_DATA"] == "S_U"){
				$sql_opt_list = db::query("select * from USR_MAIN WHERE USR_STATUS= 'Y' ".$opt_where);
			}elseif($BSF_DET["WFS_OPTION_SELECT_DATA"] == "S_P"){
				$sql_opt_list = db::query("select * from USR_POSITION WHERE POS_STATUS = 'Y' ".$opt_where);
			}elseif($BSF_DET["WFS_OPTION_SELECT_DATA"] == "S_D"){
				$sql_opt_list_q = "select * from USR_DEPARTMENT WHERE DEP_STATUS = 'Y' ".$opt_where;
				$has_order = bsf_check_orderby($sql_opt_list_q);
				if($has_order != "Y"){ 
				$sql_opt_list_q = $sql_opt_list_q . ' ORDER BY DEP_ORDER ASC'; 
				}
				$sql_opt_list = db::query($sql_opt_list_q);
				
			}
			
			while($rec_o = db::fetch_array($sql_opt_list)){
				if($BSF_DET["WFS_OPTION_SELECT_DATA"] == "S_U"){
					$pk_opt = $rec_o["USR_ID"];
					if($BSF_DET["WFS_OPTION_SHOW_FIELD"] !=""){
						$txt_label = bsf_show_field($W,$rec_o,$BSF_DET["WFS_OPTION_SHOW_FIELD"],'W');
					}else{
						$txt_label = $rec_o["USR_PREFIX"].$rec_o["USR_FNAME"].' '.$rec_o["USR_LNAME"];
					}
				}elseif($BSF_DET["WFS_OPTION_SELECT_DATA"] == "S_P"){
					$pk_opt = $rec_o["POS_ID"];
					if($BSF_DET["WFS_OPTION_SHOW_FIELD"] !=""){
						$txt_label = bsf_show_field($W,$rec_o,$BSF_DET["WFS_OPTION_SHOW_FIELD"],'W');
					}else{
						$txt_label = $rec_o["POS_NAME"];
					}
				}elseif($BSF_DET["WFS_OPTION_SELECT_DATA"] == "S_D"){
					$pk_opt = $rec_o["DEP_ID"];
					if($BSF_DET["WFS_OPTION_SHOW_FIELD"] !=""){
						$txt_label = bsf_show_field($W,$rec_o,$BSF_DET["WFS_OPTION_SHOW_FIELD"],'W');
					}else{
						$txt_label = $rec_o["DEP_NAME"];
					}
				}
				 
				
				if($BSF_DET["FORM_MAIN_ID"]=="5"){//checkbox
				
					$data_list[$wf_i]["id"] = $pk_opt; //value in checkbox
					$data_list[$wf_i]["name"] = $BSF_DET["WFS_FIELD_NAME_ORI"].'_'.$wf_i; //name checkbox
					if($system_conf['wf_display_department']=="parent-child" AND $BSF_DET["WFS_OPTION_SELECT_DATA"] == "S_D"){
						if($rec_o['DEPT_PARENT_ID'] == ""){
						$data_list[$wf_i]["parent"] = '0'; 	
						}else{
						$data_list[$wf_i]["parent"] = $rec_o['DEPT_PARENT_ID']; 
						}	
					}
					$data_list[$wf_i]["text"] = $txt_label;  //label 
					$data_list[$wf_i]["opt"] = "M_".$pk_opt;  //option hidden
				
					if($wftype == "S"){ 
						if($WF[$data_list[$wf_i]["name"]] == $pk_opt){
							$data_list[$wf_i]["checked"] = $data_use;	
						}else{
							$data_list[$wf_i]["checked"] = "";
						}	
					}else{
						$sql_opt = db::query("SELECT COUNT(CHECKBOX_ID) AS CHECKBOX_ID FROM WF_CHECKBOX WHERE WFS_FIELD_NAME = '".$BSF_DET["WFS_FIELD_NAME_ORI"]."' AND WFR_ID = '".$WFR."' AND CHECKBOX_TYPE = 'M' AND CHECKBOX_REF = '".$pk_opt."' AND W_ID = '".$W."'");
						$num_opt=db::fetch_array($sql_opt);
						if($num_opt["CHECKBOX_ID"] > 0 AND $WFR != ''){
							$data_list[$wf_i]["checked"] = $data_use;	
						}else{
							$data_list[$wf_i]["checked"] = "";
						}
					}
					$wf_i++;
				}else{ 
					$data_list[$wf_i]["id"] = $pk_opt;
					$data_list[$wf_i]["text"] = $txt_label; 
					if($system_conf['wf_display_department']=="parent-child" AND $BSF_DET["WFS_OPTION_SELECT_DATA"] == "S_D"){
						if($rec_o['DEPT_PARENT_ID'] == ""){
						$data_list[$wf_i]["parent"] = '0'; 	
						}else{
						$data_list[$wf_i]["parent"] = $rec_o['DEPT_PARENT_ID']; 
						}	
					}
					$data_list[$wf_i]["selected"] = ''; 
					if($WF[$BSF_DET["WFS_FIELD_NAME_ORI"]] == $pk_opt AND $WF[$BSF_DET["WFS_FIELD_NAME_ORI"]] != ''){
						$data_list[$wf_i]["selected"] = $data_use; 
					}else{
						$data_list[$wf_i]["selected"] = '';
					}
					$wf_i++;
				}
			}
		}
	} 
	if($BSF_DET["WFS_NUM_STEP_OPTION"] > 0){
		
		if($WF[$BSF_DET["WFS_FIELD_NAME_ORI"]] != "" AND $show_value != ''){
			$con = " AND WFSO_VALUE = '".$WF[$BSF_DET["WFS_FIELD_NAME_ORI"]]."'";
		}else{ 
			$con = " order by WFSO_ORDER";
		}
		$sql_option = db::query("select * from WF_STEP_OPTION where WFS_ID = '".$BSF_DET["WFS_ID"]."' ".$con);
		while($rec_option = db::fetch_array($sql_option)){ 
			if($rec_option['WFSO_NAME']!="" AND str_contains($rec_option['WFSO_NAME'], '##')){
				$search  = array();
				$replace = array(); 
				preg_match_all("/(##)([a-zA-Z0-9_]+)(!!)/", $rec_option['WFSO_NAME'], $matches, PREG_SET_ORDER);
				foreach ($matches as $val){ 
					$value = bsf_show_itext($val[2],$rec_main,$WF,$BSF_DET); 
					array_push($search,"##".$val[2]."!!"); 
					array_push($replace,$value); 
				}
				$contents = str_replace($search, $replace, $rec_option['WFSO_NAME']);
				$rec_option['WFSO_NAME'] = str_replace("##!!", "", $contents);  
			}
			
			if($BSF_DET["FORM_MAIN_ID"]=="5"){//checkbox 
					
				$data_list[$wf_i]["id"] = $rec_option['WFSO_VALUE']; //value in checkbox
				$data_list[$wf_i]["name"] = $BSF_DET["WFS_FIELD_NAME_ORI"].'_'.$wf_i; //name checkbox
				$data_list[$wf_i]["text"] = htmlspecialchars_decode($rec_option['WFSO_NAME'],ENT_QUOTES);  //label
				if($rec_opt['WF_PARENT_USE'] != '' AND $rec_opt['WF_PARENT_FIELD'] != ''){ 
					$data_list[$wf_i]["parent"] = '0'; 
				}
				$data_list[$wf_i]["opt"] = "O_".$rec_option['WFSO_ID'];  //option hidden
				if($wftype == "S"){ 
					if($WF[$data_list[$wf_i]["name"]] == $rec_option['WFSO_ID']){
						$data_list[$wf_i]["checked"] = $data_use;	
					}else{
						$data_list[$wf_i]["checked"] = "";
					}	
				}else{
						if($WFR != ''){
							$sql_opt = db::query("SELECT COUNT(CHECKBOX_ID) AS CHECKBOX_ID FROM WF_CHECKBOX WHERE WFS_FIELD_NAME = '".$BSF_DET["WFS_FIELD_NAME_ORI"]."' AND WFR_ID = '".$WFR."' AND CHECKBOX_TYPE = 'O' AND CHECKBOX_REF = '".$rec_option['WFSO_ID']."' AND W_ID = '".$W."'");
							$num_opt=db::fetch_array($sql_opt);
							if($num_opt["CHECKBOX_ID"] > 0){
								$data_list[$wf_i]["checked"] = $data_use;
							}else{
								$data_list[$wf_i]["checked"] = "";
							}
						}else{
							$data_list[$wf_i]["checked"] = "";
						}
				}
				$wf_i++;	
			}else{
				$data_list[$wf_i]["id"] = $rec_option['WFSO_VALUE'];
				$data_list[$wf_i]["text"] = htmlspecialchars_decode($rec_option['WFSO_NAME'],ENT_QUOTES); 
				if($rec_opt['WF_PARENT_USE'] != '' AND $rec_opt['WF_PARENT_FIELD'] != ''){ 
					$data_list[$wf_i]["parent"] = '0'; 
				}
				if($WF[$BSF_DET["WFS_FIELD_NAME_ORI"]] == $rec_option['WFSO_VALUE'] AND $WF[$BSF_DET["WFS_FIELD_NAME_ORI"]] != ''){
					$data_list[$wf_i]["selected"] = $data_use; 
				}else{
					$data_list[$wf_i]["selected"] = '';
				}
				$wf_i++;	
			}			
		}
	}		
	return $data_list;
}
function bsf_load_iform($BSF_DET,$rec_main,$WFR,$F_TEMP_ID,$WFD,$Flag=''){ // WFS_FORM_TABLE_SHOW
global $WF_TEXT_MAIN_EDIT,$WF_TEXT_MAIN_VIEW,$WF_TEXT_MAIN_DEL,$WF_LIST_DATA;

$W = $BSF_DET['WFS_FORM_SELECT'];
$WFS = $BSF_DET['WFS_ID'];
$WF_TEXT_MAIN_COPY = "คัดลอก";
$wf_table = $rec_main["WF_MAIN_SHORTNAME"];

if($BSF_DET["WFS_OPTION_COND"] != ""){
	$con = " AND ".$BSF_DET["WFS_OPTION_COND"];
}
$tb_class = array("C"=>"text-center","L"=>"text-start","R"=>"text-end");
/////////////////////////////////////////////////////////////////////////////
$wf_where = "";
$wf_where .= " AND ".$wf_table.".WFR_ID = '".$WFR."'";
$wf_where .= " AND ".$wf_table.".F_TEMP_ID = '".$F_TEMP_ID."' ";
$wf_where .= " AND ".$wf_table.".WF_MAIN_ID = '".$BSF_DET['WF_MAIN_ID']."' ";
//echo $wf_where;
if($wf_where != ""){
	$wf_where_custom = $wf_where;
	$wf_where = " WHERE 1=1 ".$wf_where;
} 
///////////////////////////////////////////////////////////////////////////// 
if($BSF_DET['WF_VIEW_COL_HEADER'] != ''){
$tb_head = explode("|",(string)$BSF_DET['WF_VIEW_COL_HEADER']);
$tb_data = explode("|",(string)$BSF_DET['WF_VIEW_COL_DATA']);
$tb_raw = explode("|",(string)$BSF_DET['WF_VIEW_COL_RAW']);
$tb_align = explode("|",(string)$BSF_DET['WF_VIEW_COL_ALIGN']);
$tb_size = explode("|",(string)$BSF_DET['WF_VIEW_COL_SIZE']);
$column_n = count($tb_head);

}elseif($rec_main['WF_VIEW_COL_HEADER'] != ''){
$tb_head = explode("|",(string)$rec_main['WF_VIEW_COL_HEADER']);
$tb_data = explode("|",(string)$rec_main['WF_VIEW_COL_DATA']);
$tb_raw = explode("|",(string)$rec_main['WF_VIEW_COL_RAW']);
$tb_align = explode("|",(string)$rec_main['WF_VIEW_COL_ALIGN']);
$tb_size = explode("|",(string)$rec_main['WF_VIEW_COL_SIZE']);  
$column_n = count($tb_head);

}else{
	global $tb_head,$tb_data,$tb_raw,$tb_align,$tb_size,$column_n;
}

if($BSF_DET["WFS_FORM_EDIT_LABEL"] != ''){ $WF_TEXT_MAIN_EDIT_F = $BSF_DET["WFS_FORM_EDIT_LABEL"];}else{ $WF_TEXT_MAIN_EDIT_F = $WF_TEXT_MAIN_EDIT; }
if($BSF_DET["WFS_FORM_DEL_LABEL"] != ''){ $WF_TEXT_MAIN_DEL_F = $BSF_DET["WFS_FORM_DEL_LABEL"];}else{ $WF_TEXT_MAIN_DEL_F = $WF_TEXT_MAIN_DEL; }
if($BSF_DET["WFS_FORM_VIEW_LABEL"] != ''){ $WF_TEXT_MAIN_VIEW_F = $BSF_DET["WFS_FORM_VIEW_LABEL"];}else{ $WF_TEXT_MAIN_VIEW_F = $WF_TEXT_MAIN_VIEW; }
if($BSF_DET["WFS_FORM_COPY_LABEL"] != ''){ $WF_TEXT_MAIN_COPY_F = $BSF_DET["WFS_FORM_COPY_LABEL"];}else{ $WF_TEXT_MAIN_COPY_F = $WF_TEXT_MAIN_COPY; }

if($rec_main["WF_MAIN_DEFAULT_ORDER"] != ""){
	$wf_order = " ORDER BY ".$rec_main["WF_MAIN_DEFAULT_ORDER"];
}else{
	$wf_order = "  ORDER BY ".$rec_main["WF_FIELD_PK"]." ASC";
}

$DET = "";
if($BSF_DET["WFS_INPUT_FORMAT"]=="D"){
	$DET = " AND WFD_ID = '".$WFD."'";
}
if($BSF_DET["WFS_FORM_TABLE_SHOW"] == "M" AND $BSF_DET["WFS_OPTION_COND"] != ""){
	
	if(str_contains($BSF_DET["WFS_OPTION_COND"], '##') AND $BSF_DET['WF_MAIN_ID'] != ''){ 
		$sql_source = db::query("select WF_MAIN_SHORTNAME,WF_FIELD_PK from WF_MAIN where WF_MAIN_ID = '".$BSF_DET['WF_MAIN_ID']."'");
		$rec_s = db::fetch_array($sql_source); 
		$sql_data_s = db::query("select * from ".$rec_s['WF_MAIN_SHORTNAME']." where ".$rec_s['WF_FIELD_PK']." = '".$WFR."'");
		$data_s = db::fetch_array($sql_data_s);
		preg_match_all("/(##)([a-zA-Z0-9_]+)(!!)/", $BSF_DET["WFS_OPTION_COND"], $new_sql2, PREG_SET_ORDER);
			foreach ($new_sql2 as $val_new) {  
				$val = $data_s[$val_new[2]]; 
				$BSF_DET["WFS_OPTION_COND"] = str_replace("##".$val_new[2]."!!",(string)$val,$BSF_DET["WFS_OPTION_COND"]);
			}
	} 
}
if($Flag == 'VIEW'){
 
	//$sql_workflow = "select * from ".$wf_table." ".$wf_where.$DET.$wf_order;
	if($BSF_DET["WFS_FORM_TABLE_SHOW"] == "M" AND $BSF_DET["WFS_OPTION_FULL_SQL"] == "Y" AND $BSF_DET["WFS_OPTION_COND"] != ""){
		$sql_workflow1 = wf_convert_var($BSF_DET["WFS_OPTION_COND"],'Y');
		if (preg_match("/#CONDITION#/i",$sql_workflow1)) { 
			$sql_workflow = str_replace("#CONDITION#",$wf_where_custom,$sql_workflow1);
		}else{
			$sql_workflow = $sql_workflow1.$wf_where_custom;
		}
		
	}else{
		$wh_custom = "";
		if($BSF_DET["WFS_FORM_TABLE_SHOW"] == "M" AND $BSF_DET["WFS_OPTION_COND"] != ""){
			if (preg_match("/order by/i",$BSF_DET["WFS_OPTION_COND"])) { 
				$wf_order = "";
			}
			$wh_custom = " AND ".wf_convert_var($BSF_DET["WFS_OPTION_COND"],'Y');
		}
		$sql_workflow = "select * from ".$wf_table." ".$wf_where.$DET.$wh_custom.$wf_order;
	}
$query_workflow = db::query($sql_workflow);
$no = 1;
$rows_frm = db::num_rows($query_workflow);
if($rows_frm > 0){
$arr_b_fetch = array();
for($c=0;$c<$column_n;$c++){
	if($tb_raw[$c]=="" AND $tb_data[$c]!="" AND str_contains($tb_data[$c], '##')){
		preg_match_all("/(##)([a-zA-Z0-9_]+)(!!)/", $tb_data[$c], $matches, PREG_SET_ORDER);
        foreach ($matches as $val){
			$form_step = db::query_first("SELECT WSF.* FROM WF_STEP_FORM WSF WHERE WSF.WF_MAIN_ID = '".$W."' AND WSF.WF_TYPE = '".$rec_main["WF_TYPE"]."' AND WSF.WFS_FIELD_NAME ='".$val[2]."' ORDER BY WFS_MAIN_SHOW ASC");
			$arr_b_fetch[$val[2]] = $form_step;
		}
	}
}
}
while($WF=db::fetch_array($query_workflow)){
	?><tr id="bsf_f_id_<?php echo $rec_main["WF_MAIN_ID"]; ?>_<?php echo $WF[$rec_main["WF_FIELD_PK"]]; ?>"><?php
	if($BSF_DET["WF_VIEW_COL_SHOW_NO"]=="Y"){
			?><td class="text-center"><?php echo $no; ?></td><?php	
	}
			for($c=0;$c<$column_n;$c++){
				
			?><td class="<?php echo $tb_class[$tb_align[$c]]; ?>"><?php
			$data = "";
			$text = $tb_data[$c];
			if ((stripos($text, "@&lt;") !== false) and $text != ""){
			preg_match_all("/(@&lt;)([a-zA-Z0-9_.]+)(&gt;)/", $text, $matches, PREG_SET_ORDER);
				foreach ($matches as $val) {
				if(file_exists("../plugin/" . $val[2])){
					include("../plugin/" . $val[2]);
					$text = str_replace("@&lt;" . $val[2] . "&gt;", '', $text);
					}
				}
			}
			$text = htmlspecialchars_decode((string)$text);
			if($tb_raw[$c]==""){
					if($tb_raw[$c]=="" AND $text!="" AND str_contains($text, '##')){
						$search  = array();
						$replace = array(); 
						preg_match_all("/(##)([a-zA-Z0-9_]+)(!!)/", $text, $matches, PREG_SET_ORDER);
						foreach ($matches as $val){ 
							$value = bsf_show_itext($val[2],$rec_main,$WF,$arr_b_fetch[$val[2]]); 
							if($arr_b_fetch[$val[2]]['FORM_MAIN_ID'] == '1' OR $arr_b_fetch[$val[2]]['FORM_MAIN_ID'] == '2'){
								$value = nl2br($value);
							}elseif($arr_b_fetch[$val[2]]['FORM_MAIN_ID'] == '10'){
								if(file_exists("../view/" .$arr_b_fetch[$val[2]]['WFS_CODING_VIEW']) AND $arr_b_fetch[$val[2]]['WFS_CODING_VIEW'] != ''){
									include("../view/" . $arr_b_fetch[$val[2]]['WFS_CODING_VIEW']); 
								}else{
									if($WF[$val[2]] != ''){
										$value = $WF[$val[2]];
									}
								}
							}
							array_push($search,"##".$val[2]."!!"); 
							array_push($replace,$value); 
						}
						$contents = str_replace($search, $replace, $text);
						$data = str_replace("##!!", "", $contents); 
					}
			}else{
				$data = bsf_show_field($W,$WF,$text,$rec_main["WF_TYPE"]);	
			} 
			echo $data;
			?></td><?php
			}
			if($BSF_DET["WFS_FORM_VIEW_STATUS"] == 'Y'){
			echo "<td class=\"tools_area text-end\">";
					if($BSF_DET["WFS_FORM_POPUP"] == "P"){
						$WFS_ONCLICK_V = " onclick=\"PopupCenter('";
						if($_SESSION["WF_USER_ID"] != ""){ $WFS_ONCLICK_V .= '../workflow/'; } 
						$WFS_ONCLICK_V .="form_mgt.php?W=".$W."&WFS=".$WFS."&WFD=".$WFD."&WFR_ID=".$WFR."&WFR_ID=".$WFR."&F_TEMP_ID=".$F_TEMP_ID."&WFR=".$WF[$rec_main["WF_FIELD_PK"]]."&WF_POP=P&WF_VIEW=Y&wfp=".conText($_REQUEST['wfp'])."', '','980','640')\"";
					}else{
						$WFS_ONCLICK_V = " onclick=\"open_modal('";
						if($_SESSION["WF_USER_ID"] != ""){ $WFS_ONCLICK_V .= '../workflow/'; } 
						$WFS_ONCLICK_V .="form_mgt.php?W=".$W."&WFS=".$WFS."&WFD=".$WFD."&WFR_ID=".$WFR."&WFR_ID=".$WFR."&F_TEMP_ID=".$F_TEMP_ID."&WFR=".$WF[$rec_main["WF_FIELD_PK"]]."&WF_VIEW=Y&wfp=".conText($_REQUEST['wfp'])."', '','".$BSF_DET["WFS_ID"]."V".conText((string)$_GET['rand'])."','".$BSF_DET["WFS_MODAL_SIZE"]."')\"";
					}
					?>
					<a href="#!" class="btn btn-light-info btn-mini" <?php echo $tootip;?>  title="<?php if($BSF_DET["WFS_FORM_VIEW_RESIZE"] == 'Y'){ echo $WF_TEXT_MAIN_VIEW_F;}?>" <?php echo $WFS_ONCLICK_V; ?>>
						<i class="ti ti-search"></i> <?php if($BSF_DET["WFS_FORM_VIEW_RESIZE"] != 'Y'){ echo $WF_TEXT_MAIN_VIEW_F;}?> </a>
				<?php 
				echo "</td>";
				}
			?>
		</tr>
	<?php $no++; } 
	
}else{
if($BSF_DET["WFS_FORM_ADD_POPUP"]=="Y"){
	if($BSF_DET["WFS_FORM_TABLE_SHOW"] == "M" AND $BSF_DET["WFS_OPTION_FULL_SQL"] == "Y" AND $BSF_DET["WFS_OPTION_COND"] != ""){
		$sql_workflow1 = wf_convert_var($BSF_DET["WFS_OPTION_COND"],'Y');
		if (preg_match("/#CONDITION#/i",$sql_workflow1)) { 
			$sql_workflow = str_replace("#CONDITION#",$wf_where_custom,$sql_workflow1);
		}else{
			$sql_workflow = $sql_workflow1.$wf_where_custom;
		}
		
	}else{
		$wh_custom = "";
		if($BSF_DET["WFS_FORM_TABLE_SHOW"] == "M" AND $BSF_DET["WFS_OPTION_COND"] != ""){
			if (preg_match("/order by/i",$BSF_DET["WFS_OPTION_COND"])) { 
				$wf_order = "";
			}
			$wh_custom = " AND ".wf_convert_var($BSF_DET["WFS_OPTION_COND"],'Y');
		}
		$sql_workflow = "select * from ".$wf_table." ".$wf_where.$DET.$wh_custom.$wf_order;
	}

$query_workflow = db::query($sql_workflow);
$no = 1;
$rows_frm = db::num_rows($query_workflow);
if($rows_frm > 0){
$arr_b_fetch = array();
for($c=0;$c<$column_n;$c++){
	if($tb_raw[$c]=="" AND $tb_data[$c]!="" AND str_contains($tb_data[$c], '##')){
		preg_match_all("/(##)([a-zA-Z0-9_]+)(!!)/", $tb_data[$c], $matches, PREG_SET_ORDER);
        foreach ($matches as $val){
			$form_step = db::query_first("SELECT WSF.* FROM WF_STEP_FORM WSF WHERE WSF.WF_MAIN_ID = '".$W."' AND WSF.WF_TYPE = '".$rec_main["WF_TYPE"]."' AND WSF.WFS_FIELD_NAME ='".$val[2]."' ORDER BY WFS_MAIN_SHOW ASC");
			$arr_b_fetch[$val[2]] = $form_step;
		}
	}
}

while($WF=db::fetch_array($query_workflow)){
	?><tr id="bsf_f_id_<?php echo $rec_main["WF_MAIN_ID"]; ?>_<?php echo $WF[$rec_main["WF_FIELD_PK"]]; ?>"><?php
	 if($BSF_DET["WF_VIEW_COL_SHOW_NO"]=="Y"){
			?><td class="text-center"><?php echo $no; ?></td><?php	
			}
			for($c=0;$c<$column_n;$c++){
				
			?><td class="<?php echo $tb_class[$tb_align[$c]]; ?>"><?php
			$data = "";
			$text = $tb_data[$c];
			if ((stripos($text, "@&lt;") !== false) and $text != ""){
			preg_match_all("/(@&lt;)([a-zA-Z0-9_.]+)(&gt;)/", $text, $matches, PREG_SET_ORDER);
				foreach ($matches as $val) {
				if(file_exists("../plugin/" . $val[2])){
					include("../plugin/" . $val[2]);
					$text = str_replace("@&lt;" . $val[2] . "&gt;", '', $text);
					}
				}
			}
			$text = htmlspecialchars_decode((string)$text);
			if($tb_raw[$c]==""){
					if($tb_raw[$c]=="" AND $text!="" AND str_contains($text, '##')){
						$search  = array();
						$replace = array(); 
						preg_match_all("/(##)([a-zA-Z0-9_]+)(!!)/", $text, $matches, PREG_SET_ORDER);
						foreach ($matches as $val){ 
							$value = bsf_show_itext($val[2],$rec_main,$WF,$arr_b_fetch[$val[2]]); 
							if($arr_b_fetch[$val[2]]['FORM_MAIN_ID'] == '1' OR $arr_b_fetch[$val[2]]['FORM_MAIN_ID'] == '2'){
								$value = nl2br($value);
							}elseif($arr_b_fetch[$val[2]]['FORM_MAIN_ID'] == '10'){
								if(file_exists("../view/" .$arr_b_fetch[$val[2]]['WFS_CODING_VIEW']) AND $arr_b_fetch[$val[2]]['WFS_CODING_VIEW'] != ''){
									include("../view/" . $arr_b_fetch[$val[2]]['WFS_CODING_VIEW']); 
								}else{
									if($WF[$val[2]] != ''){
										$value = $WF[$val[2]];
									}
								}
							}
							array_push($search,"##".$val[2]."!!"); 
							array_push($replace,$value); 
						}
						$contents = str_replace($search, $replace, $text);
						$data = str_replace("##!!", "", $contents); 
					}
			}else{
				$data = bsf_show_field($W,$WF,$text,$rec_main["WF_TYPE"]);	
			} 
			echo $data;
			?><input type="hidden" value="<?php echo conText($data); ?>"></td><?php
			}
			if($BSF_DET["WFS_FORM_EDIT_STATUS"] == "Y" OR $BSF_DET["WFS_FORM_VIEW_STATUS"] == "Y" OR $BSF_DET["WFS_FORM_DEL_STATUS"] == "Y"){ ?>
			<td class="tools_area text-end">
				<?php if($rec_main["WF_MAIN_LIST_INCLUDE"] != "" AND file_exists("../plugin/".$rec_main["WF_MAIN_LIST_INCLUDE"])){ include("../plugin/".$rec_main["WF_MAIN_LIST_INCLUDE"]); }
				if($BSF_DET["WFS_FORM_EDIT_STATUS"] == 'Y'){
					if($BSF_DET["WFS_FORM_POPUP"] == "P"){
						$WFS_ONCLICK_E = " onclick=\"PopupCenter('";
						if($_SESSION["WF_USER_ID"] != ""){ $WFS_ONCLICK_E .= '../workflow/'; } 
						$WFS_ONCLICK_E .="form_mgt.php?W=".$W."&WFS=".$WFS."&WFD=".$WFD."&WFR_ID=".$WFR."&WFR_ID=".$WFR."&F_TEMP_ID=".$F_TEMP_ID."&WFR=".$WF[$rec_main["WF_FIELD_PK"]]."&WF_POP=P&wfp=".conText($_REQUEST['wfp'])."', '','980','640')\"";
					}else{
						$WFS_ONCLICK_E = "onclick=\"open_modal('";
						if($_SESSION["WF_USER_ID"] != ""){ $WFS_ONCLICK_E .= '../workflow/'; } 
						$WFS_ONCLICK_E .="form_mgt.php?W=".$W."&WFS=".$WFS."&WFD=".$WFD."&WFR_ID=".$WFR."&WFR_ID=".$WFR."&F_TEMP_ID=".$F_TEMP_ID."&WFR=".$WF[$rec_main["WF_FIELD_PK"]]."&wfp=".conText($_REQUEST['wfp'])."', '','".$WFS."','".$BSF_DET["WFS_MODAL_SIZE"]."');\"";
					}
					?>
					<a href="#!" class="btn btn-mini btn-light-success" <?php echo $tootip;?> title="<?php if($BSF_DET["WFS_FORM_EDIT_RESIZE"] == 'Y'){ echo $WF_TEXT_MAIN_EDIT_F;}?>" <?php echo $WFS_ONCLICK_E; ?>>
						<i class="ti ti-edit"></i> <?php if($BSF_DET["WFS_FORM_EDIT_RESIZE"] != 'Y'){ echo $WF_TEXT_MAIN_EDIT_F;}?>
					</a> 
				<?php  
				}
				if($BSF_DET["WFS_FORM_VIEW_STATUS"] == 'Y'){
					if($BSF_DET["WFS_FORM_POPUP"] == "P"){
						$WFS_ONCLICK_V = " onclick=\"PopupCenter('";
						if($_SESSION["WF_USER_ID"] != ""){ $WFS_ONCLICK_V .= '../workflow/'; } 
						$WFS_ONCLICK_V .="form_mgt.php?W=".$W."&WFS=".$WFS."&WFD=".$WFD."&WFR_ID=".$WFR."&WFR_ID=".$WFR."&F_TEMP_ID=".$F_TEMP_ID."&WFR=".$WF[$rec_main["WF_FIELD_PK"]]."&WF_POP=P&WF_VIEW=Y&wfp=".conText($_REQUEST['wfp'])."', '','980','640')\"";
					}else{
						$WFS_ONCLICK_V = "onclick=\"open_modal('";
						if($_SESSION["WF_USER_ID"] != ""){ $WFS_ONCLICK_V .= '../workflow/'; } 
						$WFS_ONCLICK_V .="form_mgt.php?W=".$W."&WFS=".$WFS."&WFD=".$WFD."&WFR_ID=".$WFR."&WFR_ID=".$WFR."&F_TEMP_ID=".$F_TEMP_ID."&WFR=".$WF[$rec_main["WF_FIELD_PK"]]."&WF_VIEW=Y&wfp=".conText($_REQUEST['wfp'])."', '','".$WFS."','".$BSF_DET["WFS_MODAL_SIZE"]."');\"";
					}
					?>
					<a href="#!" class="btn btn-mini btn-light-info" <?php echo $tootip;?>  title="<?php if($BSF_DET["WFS_FORM_VIEW_RESIZE"] == 'Y'){ echo $WF_TEXT_MAIN_VIEW_F;}?>" <?php echo $WFS_ONCLICK_V; ?>>
						<i class="ti ti-search"></i> <?php if($BSF_DET["WFS_FORM_VIEW_RESIZE"] != 'Y'){ echo $WF_TEXT_MAIN_VIEW_F;}?>
					</a> 
				<?php  
				}
				if($BSF_DET["WFS_FORM_COPY_STATUS"] == 'Y'){
					if($BSF_DET["WFS_FORM_POPUP"] == "P"){ 
						$WFS_ONCLICK_C = " onclick=\"PopupCenter('";
						if($_SESSION["WF_USER_ID"] != ""){ $WFS_ONCLICK_C .= '../workflow/'; } 
						$WFS_ONCLICK_C .="form_mgt_copy.php?W=".$W."&WFS=".$WFS."&WFD=".$WFD."&WFR_ID=".$WFR."&WFR_ID=".$WFR."&F_TEMP_ID=".$F_TEMP_ID."&WFR=".$WF[$rec_main["WF_FIELD_PK"]]."&WF_POP=P&wfp=".conText($_REQUEST['wfp'])."', '','980','640')\"";
					}else{
						$WFS_ONCLICK_C = "onclick=\"open_modal('";
						if($_SESSION["WF_USER_ID"] != ""){ $WFS_ONCLICK_C .= '../workflow/'; } 
						$WFS_ONCLICK_C .="form_mgt_copy.php?W=".$W."&WFS=".$WFS."&WFD=".$WFD."&WFR_ID=".$WFR."&WFR_ID=".$WFR."&F_TEMP_ID=".$F_TEMP_ID."&WFR=".$WF[$rec_main["WF_FIELD_PK"]]."&wfp=".conText($_REQUEST['wfp'])."', '','".$WFS."','".$BSF_DET["WFS_MODAL_SIZE"]."');\"";
					}
					?>
					<a href="#!" class="btn btn-mini btn-light-primary" <?php echo $tootip;?>  title="<?php if($BSF_DET["WFS_FORM_COPY_RESIZE"] == 'Y'){ echo $WF_TEXT_MAIN_COPY_F;}?>" <?php echo $WFS_ONCLICK_C; ?>>
						<i class="ti ti-copy"></i> <?php if($BSF_DET["WFS_FORM_COPY_RESIZE"] != 'Y'){ echo $WF_TEXT_MAIN_COPY_F;}?>
					</a> 
				<?php  
				}
				if($BSF_DET["WFS_FORM_DEL_STATUS"] == 'Y'){?>
					<a href="#!" class="btn btn-mini btn-light-danger" <?php echo $tootip_del;?> title="<?php if($BSF_DET["WFS_FORM_DEL_RESIZE"] == 'Y'){ echo $WF_TEXT_MAIN_DEL_F;}?>" onClick="bsf_del_form('<?php echo $W; ?>','<?php echo $WFS; ?>','<?php echo $WFR; ?>','<?php echo $F_TEMP_ID; ?>','<?php echo $WFD; ?>','<?php echo $WF[$rec_main["WF_FIELD_PK"]]; ?>');">
						<i class="ti ti-trash"></i> <?php if($BSF_DET["WFS_FORM_DEL_RESIZE"] != 'Y'){ echo $WF_TEXT_MAIN_DEL_F;}?>
					</a>
				<?php }?>
			</td><?php } ?>
		</tr>
	<?php $no++; } 
		}
	}
	if($BSF_DET["WFS_FORM_ADD_POPUP"]=="M"){ //Cross Master 
		$sql_form = db::query("select WFS_ID,WFS_FIELD_NAME from WF_STEP_FORM WHERE WF_MAIN_ID = '".$W."' AND WF_TYPE = '".$rec_main["WF_TYPE"]."' AND WFS_MASTER_CROSS = 'Y'");
		$C = db::fetch_array($sql_form);
		if($C["WFS_ID"] != ""){ 
		$R = wf_call_relation($C["WFS_ID"],'',array());
		$txt_compare = '##'.$C["WFS_FIELD_NAME"].'!!'; 
	$no = 1;
	foreach($R as $val){
	$array_input = array();
	$sql_form_data = "select * from ".$wf_table." ".$wf_where." AND ".$C["WFS_FIELD_NAME"]." = '".$val['id']."' ".$DET;
	$query_form = db::query($sql_form_data); 
	$FRM = db::fetch_array($query_form);
	$FRM[$C["WFS_FIELD_NAME"]] = $val['id'];
	?><tr id="bsf_f_id_<?php echo $rec_main["WF_MAIN_ID"]; ?>_<?php echo $R[$rec_main["WF_FIELD_PK"]]; ?>"><?php
			if($BSF_DET["WF_VIEW_COL_SHOW_NO"]=="Y"){
			?><td class="text-center"><?php echo $no; ?></td><?php
			}
			for($c=0;$c<$column_n;$c++){
			?><td class="<?php echo $tb_class[$tb_align[$c]]; ?>"><?php
			if(strpos($tb_data[$c],$txt_compare)===false){
				$array_input = bsf_show_input($W,$FRM,$tb_data[$c],'F',$array_input,'_'.$WFS.'_'.$no);
				
			}else{
				echo $val['text'];
				$array_input[] = $C["WFS_FIELD_NAME"];
				echo '<input type="hidden" name="'.$C["WFS_FIELD_NAME"].'_'.$WFS.'_'.$no.'" id="'.$C["WFS_FIELD_NAME"].'_'.$WFS.'_'.$no.'" value="'.$val['id'].'">';
			}
			?></td><?php
			} ?>
		</tr>
	<?php $no++; } 
	echo '<input type="hidden" name="WF_NUMFRM_'.$WFS.'" id="WF_NUMFRM_'.$WFS.'" value="'.$no.'">';
	echo '<input type="hidden" name="WF_INPFRM_'.$WFS.'" id="WF_INPFRM_'.$WFS.'" value="'.implode(',',$array_input).'">';
		}
	}
	if($BSF_DET["WFS_FORM_ADD_POPUP"]=="N"){ //Inline 
	$rand_id = bsf_random(10);
	if($Flag == 'ADD'){ 
	$array_input = array();
	
	if($BSF_DET["WFS_INLINE_FORM"]=="Y"){
		?><blockquote class="card my-1 p-1" id="bsf_f_id_<?php echo $rec_main["WF_MAIN_ID"]; ?>_<?php echo $rand_id; ?>">
		<?php
		if($BSF_DET["WFS_FORM_DEL_STATUS"] == "Y"){
			?><div class="text-end"><?php
			 if($rec_main["WF_MAIN_LIST_INCLUDE"] != "" AND file_exists("../plugin/".$rec_main["WF_MAIN_LIST_INCLUDE"])){ include("../plugin/".$rec_main["WF_MAIN_LIST_INCLUDE"]); }
			 if($BSF_DET["WFS_FORM_DEL_STATUS"] == 'Y'){
				?>
						<a href="#!" class="btn btn-light-danger btn-mini" <?php echo $tootip_del;?> title="<?php if($BSF_DET["WFS_FORM_DEL_RESIZE"] == 'Y'){ echo $WF_TEXT_MAIN_DEL_F;}?>" onClick="bsf_del_form('<?php echo $W; ?>','<?php echo $WFS; ?>','<?php echo $WFR; ?>','<?php echo $F_TEMP_ID; ?>','<?php echo $WFD; ?>','<?php echo $rand_id; ?>');">
							<i class="ti ti-trash"></i> <?php if($BSF_DET["WFS_FORM_DEL_RESIZE"] != 'Y'){ echo $WF_TEXT_MAIN_DEL_F;} ?></a>
			 <?php } echo "</div>"; }
		echo '<input type="hidden" name="FID_'.$WFS.'[]" id="FID_'.$WFS.'" value="'.$rand_id.'">';
		bsf_show_form($BSF_DET["WFS_FORM_SELECT"],'0',array(),'F','','_'.$rand_id,'','','Y');
		$sql_step_f = db::query("SELECT * FROM WF_STEP_FORM WHERE WF_MAIN_ID='".$W."' AND WF_TYPE = '".$rec_main["WF_TYPE"]."' AND FORM_MAIN_ID != '16'  AND FORM_MAIN_ID != '10' AND (WFS_NAME != '' OR WFS_NAME IS NOT NULL) ORDER BY WFS_ORDER,WFS_OFFSET");
		while($rec_sf = db::fetch_array($sql_step_f)){
			$array_input[] = $rec_sf["WFS_FIELD_NAME"];
		} 
		?>
		</blockquote><?php
	}else{ /////Inline
	$arr_view_wf =array();
	if($BSF_DET['WFS_FORM_FIELD_VIEW'] != "" AND $BSF_DET['WFS_FORM_INPUT_SHOW'] == "M"){ //custom view
		$arr_view_wf = bsf_show_input_view($BSF_DET['WFS_FORM_FIELD_VIEW']);
	}
		?><tr id="bsf_f_id_<?php echo $rec_main["WF_MAIN_ID"]; ?>_<?php echo $rand_id; ?>"><?php
		echo '<input type="hidden" name="FID_'.$WFS.'[]" id="FID_'.$WFS.'" value="'.$rand_id.'">';
		 if($BSF_DET["WF_VIEW_COL_SHOW_NO"]=="Y"){
				?><td class="text-center"><?php echo $no; ?></td><?php
				}
				for($c=0;$c<$column_n;$c++){
				?><td class="<?php if(isset($tb_align[$c])){ echo $tb_class[$tb_align[$c]];} ?>"><?php  
				if(!in_array($tb_data[$c], $arr_view_wf)){
				if($tb_data[$c]!="" AND str_contains($tb_data[$c], '##')){
					preg_match_all("/(##)([a-zA-Z0-9_]+)(!!)/", $tb_data[$c], $matches, PREG_SET_ORDER);
					foreach ($matches as $val){ 
						$sql_form = db::query("SELECT WSF.*,WFS_FIELD_NAME AS WFS_FIELD_NAME_ORI FROM WF_STEP_FORM WSF WHERE WSF.WF_MAIN_ID = '".$W."' AND WSF.WF_TYPE = '".$rec_main["WF_TYPE"]."' AND WSF.WFS_FIELD_NAME ='".$val[2]."'");
						$form_step = db::fetch_array($sql_form);
						$arr = array(); 
						$arr[0] = $form_step;
						bsf_show_form_area($arr,$rec_main,'0',array(),$rec_main["WF_TYPE"],$form_step['WFS_ID'],'_'.$rand_id,'','','');
						
						$array_input[] = $val[2];
					}
				}
				}else{
					if($tb_data[$c]!="" AND str_contains($tb_data[$c], '##')){
						preg_match_all("/(##)([a-zA-Z0-9_]+)(!!)/", $tb_data[$c], $matches, PREG_SET_ORDER);
						foreach ($matches as $val){   
							$array_input[] = $val[2];
						}
					}
				}
				?></td><?php
				}
				if($BSF_DET["WFS_FORM_DEL_STATUS"] == "Y"){
				?><td class="tools_area text-end">
					<nobr>
					<?php if($rec_main["WF_MAIN_LIST_INCLUDE"] != "" AND file_exists("../plugin/".$rec_main["WF_MAIN_LIST_INCLUDE"])){ include("../plugin/".$rec_main["WF_MAIN_LIST_INCLUDE"]); }
					if($BSF_DET["WFS_FORM_DEL_STATUS"] == 'Y'){?>
						<a href="#!" class="btn btn-light-danger btn-mini" <?php echo $tootip_del;?> title="<?php if($BSF_DET["WFS_FORM_DEL_RESIZE"] == 'Y'){ echo $WF_TEXT_MAIN_DEL_F;}?>" onClick="bsf_del_form('<?php echo $W; ?>','<?php echo $WFS; ?>','<?php echo $WFR; ?>','<?php echo $F_TEMP_ID; ?>','<?php echo $WFD; ?>','<?php echo $rand_id; ?>');">
							<i class="ti ti-trash"></i> <?php if($BSF_DET["WFS_FORM_DEL_RESIZE"] != 'Y'){ echo $WF_TEXT_MAIN_DEL_F;} ?></a>
					<?php }?>
					</nobr>
				</td><?php } ?>
			</tr>
			<?php
			}
			echo '<input type="hidden" name="WF_INPFRM_'.$WFS.'" id="WF_INPFRM_'.$WFS.'" value="'.implode(',',$array_input).'">'; ?>
			<script>
			WFS_UPDATE<?php echo $WFS; ?>();
			$('#wfs_show<?php echo $WFS; ?> input').blur(function (){
				WFS_UPDATE<?php echo $WFS; ?>();
			});
			</script>
		<?php
	
	}else{
	//$sql_workflow = "select * from ".$wf_table." ".$wf_where.$DET.$wf_order;
	if($BSF_DET["WFS_FORM_TABLE_SHOW"] == "M" AND $BSF_DET["WFS_OPTION_FULL_SQL"] == "Y" AND $BSF_DET["WFS_OPTION_COND"] != ""){
		$sql_workflow1 = wf_convert_var($BSF_DET["WFS_OPTION_COND"],'Y');
		if (preg_match("/#CONDITION#/i",$sql_workflow1)) { 
			$sql_workflow = str_replace("#CONDITION#",$wf_where_custom,$sql_workflow1);
		}else{
			$sql_workflow = $sql_workflow1.$wf_where_custom;
		}
		
	}else{
		$wh_custom = "";
		if($BSF_DET["WFS_FORM_TABLE_SHOW"] == "M" AND $BSF_DET["WFS_OPTION_COND"] != ""){
			if (preg_match("/order by/i",$BSF_DET["WFS_OPTION_COND"])) { 
				$wf_order = "";
			}
			$wh_custom = " AND ".wf_convert_var($BSF_DET["WFS_OPTION_COND"],'Y');
		}
		$sql_workflow = "select * from ".$wf_table." ".$wf_where.$DET.$wh_custom.$wf_order;
	}
	$query_workflow = db::query($sql_workflow);
	$no = 1;
	$arr_view_wf =array();
	if($BSF_DET['WFS_FORM_FIELD_VIEW'] != "" AND $BSF_DET['WFS_FORM_INPUT_SHOW'] == "M"){ //custom view
		$arr_view_wf = bsf_show_input_view($BSF_DET['WFS_FORM_FIELD_VIEW']);
	}
$rows_frm = db::num_rows($query_workflow);
if($rows_frm > 0){
$arr_b_fetch = array();
for($c=0;$c<$column_n;$c++){
	if($tb_data[$c]!="" AND str_contains($tb_data[$c], '##')){
		preg_match_all("/(##)([a-zA-Z0-9_]+)(!!)/", $tb_data[$c], $matches, PREG_SET_ORDER);
        foreach ($matches as $val){
			$form_step = db::query_first("SELECT WSF.*,WFS_FIELD_NAME AS WFS_FIELD_NAME_ORI FROM WF_STEP_FORM WSF WHERE WSF.WF_MAIN_ID = '".$W."' AND WSF.WF_TYPE = '".$rec_main["WF_TYPE"]."' AND WSF.WFS_FIELD_NAME ='".$val[2]."' ORDER BY WFS_MAIN_SHOW ASC");
			$arr_b_fetch[$val[2]] = $form_step;
		}
	}
}	
	while($WF=db::fetch_array($query_workflow)){	
	if($BSF_DET["WFS_INLINE_FORM"]=="Y"){
		$array_input = array();
		?><blockquote class="card my-1 p-1" id="bsf_f_id_<?php echo $rec_main["WF_MAIN_ID"]; ?>_<?php echo $WF[$rec_main["WF_FIELD_PK"]]; ?>">
		<?php
		if($BSF_DET["WFS_FORM_DEL_STATUS"] == "Y"){ ?>
			<div class="text-end">
			<?php if($rec_main["WF_MAIN_LIST_INCLUDE"] != "" AND file_exists("../plugin/".$rec_main["WF_MAIN_LIST_INCLUDE"])){ include("../plugin/".$rec_main["WF_MAIN_LIST_INCLUDE"]); }
			if($BSF_DET["WFS_FORM_DEL_STATUS"] == 'Y'){ ?>
				<a href="#!" class="btn btn-light-danger btn-mini" <?php echo $tootip_del;?> title="<?php if($BSF_DET["WFS_FORM_DEL_RESIZE"] == 'Y'){ echo $WF_TEXT_MAIN_DEL_F;}?>" onClick="bsf_del_form('<?php echo $W; ?>','<?php echo $WFS; ?>','<?php echo $WFR; ?>','<?php echo $F_TEMP_ID; ?>','<?php echo $WFD; ?>','<?php echo $WF[$rec_main["WF_FIELD_PK"]]; ?>');">
					<i class="ti ti-trash"></i> <?php if($BSF_DET["WFS_FORM_DEL_RESIZE"] != 'Y'){ echo $WF_TEXT_MAIN_DEL_F;}?>
				</a>
			<?php } ?></div><?php }
		echo '<input type="hidden" name="FID_'.$WFS.'[]" id="FID_'.$WFS.'" value="'.$WF[$rec_main["WF_FIELD_PK"]].'" />';
		bsf_show_form($BSF_DET["WFS_FORM_SELECT"],'0',$WF,'F','','_'.$WF[$rec_main["WF_FIELD_PK"]],'','','Y');
		$sql_step_f = db::query("SELECT * FROM WF_STEP_FORM WHERE WF_MAIN_ID='".$W."' AND WF_TYPE = '".$rec_main["WF_TYPE"]."' AND FORM_MAIN_ID != '16'  AND FORM_MAIN_ID != '10' AND (WFS_NAME != '' OR WFS_NAME IS NOT NULL) ORDER BY WFS_ORDER,WFS_OFFSET");
		while($rec_sf = db::fetch_array($sql_step_f)){
			$array_input[] = $rec_sf["WFS_FIELD_NAME"];
		}
		?>
		</blockquote>
		<?php
	}else{   //4444
	$array_input = array();
		?><tr id="bsf_f_id_<?php echo $rec_main["WF_MAIN_ID"]; ?>_<?php echo $WF[$rec_main["WF_FIELD_PK"]]; ?>"><?php
		echo '<input type="hidden" name="FID_'.$WFS.'[]" id="FID_'.$WFS.'" value="'.$WF[$rec_main["WF_FIELD_PK"]].'" />';
		 if($BSF_DET["WF_VIEW_COL_SHOW_NO"]=="Y"){
				?><td class="text-center"><?php echo $no; ?></td><?php
				}
				for($c=0;$c<$column_n;$c++){
				?><td class="<?php if(isset($tb_align[$c])){ echo $tb_class[$tb_align[$c]];} ?>"><?php 
				if(!in_array($tb_data[$c], $arr_view_wf)){
					if($tb_data[$c]!="" AND str_contains($tb_data[$c], '##')){
						preg_match_all("/(##)([a-zA-Z0-9_]+)(!!)/", $tb_data[$c], $matches, PREG_SET_ORDER);
						foreach ($matches as $val){ 

							$arr = array();
							$arr[0] = $arr_b_fetch[$val[2]]; 
							$rec_main['WF_MAIN_ID'] = $W;
							bsf_show_form_area($arr,$rec_main,'0',$WF,$rec_main["WF_TYPE"],$arr_b_fetch[$val[2]]['WFS_ID'],'_'.$WF[$rec_main["WF_FIELD_PK"]],'','','');
							
							$array_input[] = $val[2];
						}
					}
				
				}else{ 
				if($tb_data[$c]!="" AND str_contains($tb_data[$c], '##')){
					preg_match_all("/(##)([a-zA-Z0-9_]+)(!!)/", $tb_data[$c], $matches, PREG_SET_ORDER);
					foreach ($matches as $val){
						if($tb_raw[$c]==""){
							if($arr_b_fetch[$val[2]]['FORM_MAIN_ID'] == "3" AND str_contains($WF[$val[2]], '/')){
										if($arr_b_fetch[$val[2]]['WFS_CALENDAR_EN'] == "Y"){
										$WF[$val[2]] = db2date_en($WF[$val[2]]);
										}else{
										$WF[$val[2]] = db2date($WF[$val[2]]);
										}
							}
						}
						echo "<input type=\"hidden\" name=\"".$val[2].'_'.$WF[$rec_main["WF_FIELD_PK"]]."\" value=\"".$WF[$val[2]]."\">";
						$array_input[] = $val[2];
					}
				}
				$data = "";
				if($tb_raw[$c]==""){
						if($tb_raw[$c]=="" AND $tb_data[$c]!="" AND str_contains($tb_data[$c], '##')){
							$search  = array();
							$replace = array(); 
							preg_match_all("/(##)([a-zA-Z0-9_]+)(!!)/", $tb_data[$c], $matches, PREG_SET_ORDER);
							foreach ($matches as $val){ 
								$value = bsf_show_itext($val[2],$rec_main,$WF,$arr_b_fetch[$val[2]]); 
								array_push($search,"##".$val[2]."!!"); 
								array_push($replace,$value); 
							}
							$contents = str_replace($search, $replace, $tb_data[$c]);
							$data = str_replace("##!!", "", $contents); 
						}
				}else{
					$data = bsf_show_field($W,$WF,$tb_data[$c],$rec_main["WF_TYPE"]);	
				} 
				echo nl2br($data);
				}
				?></td><?php
				}
				if($BSF_DET["WFS_FORM_DEL_STATUS"] == "Y"){ ?>
				<td class="tools_area text-end">
					<nobr>
					<?php if($rec_main["WF_MAIN_LIST_INCLUDE"] != "" AND file_exists("../plugin/".$rec_main["WF_MAIN_LIST_INCLUDE"])){ include("../plugin/".$rec_main["WF_MAIN_LIST_INCLUDE"]); }
					if($BSF_DET["WFS_FORM_DEL_STATUS"] == 'Y'){?>
						<a href="#!" class="btn btn-mini btn-light-danger" <?php echo $tootip_del;?> title="<?php if($BSF_DET["WFS_FORM_DEL_RESIZE"] == 'Y'){ echo $WF_TEXT_MAIN_DEL_F;}?>" onClick="bsf_del_form('<?php echo $W; ?>','<?php echo $WFS; ?>','<?php echo $WFR; ?>','<?php echo $F_TEMP_ID; ?>','<?php echo $WFD; ?>','<?php echo $WF[$rec_main["WF_FIELD_PK"]]; ?>');">
							<i class="ti ti-trash"></i> <?php if($BSF_DET["WFS_FORM_DEL_RESIZE"] != 'Y'){ echo $WF_TEXT_MAIN_DEL_F;}?>
						</a>
					<?php }?>
					</nobr>
				</td><?php } ?>
			</tr>
		<?php
		}		echo '<input type="hidden" name="WF_INPFRM_'.$WFS.'" id="WF_INPFRM_'.$WFS.'" value="'.implode(',',$array_input).'">'; 
	$no++; }} 
		if($no == "1" AND $BSF_DET["WFS_FORM_PRELOAD"]>0){
			?><script>get_wfs_show('wfs_show<?php echo $WFS; ?>','<?php if($_SESSION["WF_USER_ID"] != ""){ echo '../workflow/'; } ?>form_add.php','W=<?php echo $W; ?>&WFD=<?php echo $WFD; ?>&WFS=<?php echo $WFS; ?>&WFR=<?php echo $WFR; ?>&F_TEMP_ID=<?php echo $WFR; ?>&ROUND=<?php echo $BSF_DET["WFS_FORM_PRELOAD"]; ?>&wfp=<?php echo conText($_REQUEST['wfp']); ?>','GET','A');</script><?php
		}
	}
	}
	}
}

function bsf_show_form_area_o($sql_form,$rec_main,$WFD,$WF,$WF_TYPE,$WFS,$SHOW,$VIEW,$WFS_CONF,$INLINE_USE){

$innline_form_use = $INLINE_USE;
$WFS_U = $WFS;
$bsf_script = "";

$align_pos = array('L'=>'left','C'=>'center','R'=>'end');
$oper_arr = array("0"=>"==","1"=>">","2"=>">=","3"=>"<","4"=>"<=","5"=>"!=",""=>"=="); 

$W = $rec_main['WF_MAIN_ID'];
$pk_field = $rec_main["WF_FIELD_PK"];

$g_pos = 0;
$tab_flag = '0';
foreach($sql_form as $BSF_DET){

		$default = $WF[$BSF_DET["WFS_FIELD_NAME"]];
		if($default == ""){
			$default = $BSF_DET["WFS_DEFAULT_DATA"];
		}
		if($default != ""){
		$default = bsf_show_text($W,$WF,$default,$WF_TYPE);
		}
		$WF[$BSF_DET["WFS_FIELD_NAME"]] = $default;
		$WFS_FIELD_NAME = $BSF_DET["WFS_FIELD_NAME"];
		
		$class_offset = "";
		$class_align_left = "";
		$class_align_right = "";
		$class_required = "";
		$class_tooltip = "";
		$class_extra = "";	
		$class_input = "";	
		$right_data_val = "";
		$left_data_val = "";
		$data_list = array(); 
		$chk = 0;
		$style_display = "";
		$WFS = $BSF_DET["WFS_ID"];
		$NUM_SCRIPT = $BSF_DET["WFS_NUM_STEP_JS"]; 
		$NUM_THROW = $BSF_DET["WFS_NUM_STEP_THROW"];
		$NUM_ONCHANGE = $BSF_DET["WFS_NUM_ONCHANGE"];
		$NUM_ONCHANGE_SEND = $BSF_DET["WFS_NUM_ONCHANGE_SEND"];
		
		if($SHOW != ''){ $BSF_DET["WFS_FIELD_NAME"] = $BSF_DET["WFS_FIELD_NAME"].$SHOW; }
	/*  End ตัวแปร */
	/* Start Check Script  */	
	
	/* End Check Script  */
	if($BSF_DET["WFS_NO_BR"] == 'Y'){
		$class_nobr1 = '<nobr>';
		$class_nobr2 = '</nobr>';
	}else{
		$class_nobr1 = '';
		$class_nobr2 = '';
	}
	/* Start Set id and hidden  */	
	if($BSF_DET["WFS_HIDDEN_FORM"] == "Y"){
		$style_display = ' id="'.$BSF_DET["WFS_FIELD_NAME"].'_BSF_AREA" style="display:none" ';
	}elseif($BSF_DET["WFS_FIELD_NAME"] != ""){
		$style_display = ' id="'.$BSF_DET["WFS_FIELD_NAME"].'_BSF_AREA" ';
	}
	/* End Set id and hidden  */
	/* Start Set align  */	
	if($BSF_DET["WFS_COLUMN_LEFT_ALIGN"] != ""){
		$class_align_left = " text-".$align_pos[$BSF_DET["WFS_COLUMN_LEFT_ALIGN"]];
	}
	if($BSF_DET["WFS_COLUMN_RIGHT_ALIGN"] != ""){
		$class_align_right = " text-".$align_pos[$BSF_DET["WFS_COLUMN_RIGHT_ALIGN"]];
	}
	/* End Set align  */
	/* Start ขึ้นบรรทัดใหม่  */	
	if($g_pos != $BSF_DET["WFS_ORDER"] AND ($SHOW=='' OR $innline_form_use == "Y")){ 
		$offset_cal = 0;
		echo "</div><div class=\"form-group row\">";
		$g_pos = $BSF_DET["WFS_ORDER"];
	}
	/* End ขึ้นบรรทัดใหม่  */	
	/* Start คำนวณ offset  */	
	if($BSF_DET["WFS_OFFSET"] > 0  AND ($SHOW=='' OR $innline_form_use == "Y")){
		if($BSF_DET["WFS_OFFSET"] > $offset_cal){
			$offset_x = $BSF_DET["WFS_OFFSET"] - $offset_cal;
			$class_offset = " offset-md-".$offset_x;
			$offset_cal += $offset_x;
		}
	}
	/* End คำนวณ offset  */
	/* Start required  */	
	if($BSF_DET["WFS_REQUIRED"] == "Y"){
		$class_required = '<span class="text-danger">*</span>';
		$class_extra .= ' required aria-required="true"';

		$form_main_validate = array(1,2,3,4,5,6,9,10,11,12,13,14);
		if(in_array($BSF_DET["FORM_MAIN_ID"], $form_main_validate))
		{
			if($BSF_DET['WFS_VALIDATE_TEXT'] == "")
			{
				$BSF_VALIDATE[$BSF_DET["WFS_FIELD_NAME"]] = "กรุณาระบุ ".$BSF_DET["WFS_NAME"];
			}
			else
			{
				$BSF_VALIDATE[$BSF_DET["WFS_FIELD_NAME"]] = $BSF_DET['WFS_VALIDATE_TEXT'];
			}
		}
	}
	/* End required  */
	/* Start จัดข้อความ  */
	if($BSF_DET["FORM_MAIN_ID"] == '8' OR $BSF_DET["FORM_MAIN_ID"] == '15'){
		if($BSF_DET['WFS_TXT_C_LEFT_EDITOR']=="Y"){
			$editor_folder = '../wysiwyg_p'; 
			if(file_exists($editor_folder.'/tl_'.$BSF_DET['WFS_ID'].'.tmp')){
				$fp = @fopen($editor_folder.'/tl_'.$BSF_DET['WFS_ID'].'.tmp','r');
				$BSF_DET['WFS_TXT_C_LEFT'] = @fread($fp, filesize($editor_folder.'/tl_'.$BSF_DET['WFS_ID'].'.tmp'));
				@fclose($fp);
			}
			$div_ltxt_first = '';
			$div_ltxt_last = ''; 
		}else{
			if($BSF_DET["WFS_TXT_C_LEFT_HIGHLIGHT"] == "Y"){
			$div_ltxt_first = '<label class="label bg-primary'.$class_align_left.'">';
			}else{
			$div_ltxt_first = '<label class="f-bold '.$class_align_left.'">';
			}
			$div_ltxt_last = '</label>';
		}
		$left_data_val = $div_ltxt_first.bsf_show_text($W,$WF,$BSF_DET["WFS_TXT_C_LEFT"],$WF_TYPE).$div_ltxt_last;
		if($BSF_DET['WFS_TXT_C_RIGHT_EDITOR']=="Y"){
			$editor_folder = '../wysiwyg_p'; 
			if(file_exists($editor_folder.'/tr_'.$BSF_DET['WFS_ID'].'.tmp')){
				$fp = @fopen($editor_folder.'/tr_'.$BSF_DET['WFS_ID'].'.tmp','r');
				$BSF_DET['WFS_TXT_C_RIGHT'] = @fread($fp, filesize($editor_folder.'/tr_'.$BSF_DET['WFS_ID'].'.tmp'));
				@fclose($fp);
			}
			$div_rtxt_first = '';
			$div_rtxt_last = '';
		}else{
			if($BSF_DET["WFS_TXT_C_RIGHT_HIGHLIGHT"] == "Y"){
			$div_rtxt_first = '<label class="label bg-primary">';
			}else{
			$div_rtxt_first = '<label>';
			}
			$div_rtxt_last = '</label>'; 
			
		}
		$right_data_val = $div_rtxt_first.$BSF_DET["WFS_TXT_C_RIGHT"].$div_rtxt_last;
	}elseif($BSF_DET["FORM_MAIN_ID"] == "5" AND $BSF_DET["WFS_INPUT_FORMAT"] == "O"){ //checkbox 1-1
		$left_data_val = '';
		$div_rtxt_last = '</label>';
		if($BSF_DET["WFS_TXT_C_RIGHT_HIGHLIGHT"] == "Y"){
			$div_rtxt_first = '<label class="label bg-primary">';
		}else{
			$div_rtxt_first = '<label>';
		}
		$right_data_val = $class_nobr1.$div_rtxt_first.nl2br($BSF_DET["WFS_NAME"]).$div_rtxt_last.$class_nobr2;

		$data_list[$chk]["id"] = $BSF_DET["WFS_OPTION_VALUE"]; //value in checkbox
		$data_list[$chk]["name"] = $BSF_DET["WFS_FIELD_NAME"]; //name checkbox
		$data_list[$chk]["text"] = $BSF_DET["WFS_NAME"];  //label
			if($default == $BSF_DET["WFS_OPTION_VALUE"]){
				$data_list[$chk]["checked"] = "checked";
			}else{
				$data_list[$chk]["checked"] = "";		
			}
			$chk++;
		
	}else{
		if(($SHOW=='' OR $innline_form_use == "Y")){
		$left_data_val = $class_nobr1.'<label for="'.$BSF_DET["WFS_FIELD_NAME"].'" class="form-label'.$class_align_left.'">'.bsf_language('WFS',$BSF_DET["WFS_ID"],$BSF_DET["WFS_NAME"],'').$class_required.'</label>'.$class_nobr2;
		}
	}
	/* End จัดข้อความ  */
	/* Start จัด Column  */
	if($BSF_DET["WFS_COLUMN_TYPE"] == "2" AND $BSF_DET["FORM_MAIN_ID"] != "10"){ //2 Column
		if(($SHOW=='' OR $innline_form_use == "Y")){
		echo '<div '.$style_display.' class="col-md-'.$BSF_DET["WFS_COLUMN_LEFT"].$class_offset.' ">'.$left_data_val.'</div><div '.$style_display.' class="wf-space-i col-md-'.$BSF_DET["WFS_COLUMN_RIGHT"].$class_align_right.'" >';
		$offset_cal += $BSF_DET["WFS_COLUMN_LEFT"]+$BSF_DET["WFS_COLUMN_RIGHT"];
		}
	}elseif($BSF_DET["WFS_COLUMN_TYPE"] == "1" OR $BSF_DET["FORM_MAIN_ID"] == "10"){ //1 Column
		if(($SHOW=='' OR $innline_form_use == "Y")){
		echo '<div '.$style_display.' class="wf-space-i col-md-'.($BSF_DET["WFS_COLUMN_LEFT"]+$BSF_DET["WFS_COLUMN_RIGHT"]).$class_offset.$class_align_left.' ">';
		}
		if($BSF_DET["FORM_MAIN_ID"] == '10'){ //coding
			if($VIEW == "Y"){
				if($BSF_DET["WFS_CODING_VIEW"] != '' AND file_exists('../view/'.$BSF_DET["WFS_CODING_VIEW"])){
					include('../view/'.$BSF_DET["WFS_CODING_VIEW"]);
				}
			}else{
				if($BSF_DET["WFS_CODING_FORM"] != '' AND file_exists('../form/'.$BSF_DET["WFS_CODING_FORM"])){
					include('../form/'.$BSF_DET["WFS_CODING_FORM"]);
				}
				if(trim($BSF_DET["WFS_CODING_AJAX"]) != ''){
					echo '<span id="show_ajax_'.$BSF_DET["WFS_ID"].'"></span>';
					$ajax = explode('?',trim($BSF_DET["WFS_CODING_AJAX"]));
					echo '<script type="text/javascript">var dataString = "'.bsf_show_text($W,$WF,$ajax[1],$WF_TYPE).'";'.PHP_EOL;
					echo '$.ajax({ type: "GET",url: "'.$ajax[0].'",data: dataString,cache: false,success: function(html){ $("#show_ajax_'.$BSF_DET["WFS_ID"].'").html(html); } }); </script>';
				}	
			}				
		}else{
			echo $left_data_val;
		}
		$offset_cal += ($BSF_DET["WFS_COLUMN_LEFT"]+$BSF_DET["WFS_COLUMN_RIGHT"]);
	}
	/* End จัด Column  */
	/* Start จัด Group Input  */
	if(trim((string)$BSF_DET["WFS_TXT_BEFORE_INPUT"]) != "" OR trim((string)$BSF_DET["WFS_TXT_AFTER_INPUT"]) != ""OR $BSF_DET["FORM_MAIN_ID"] == '3'){ echo '<label class="input-group">'; }  
	//ข้อความก่อน input
	if(trim((string)$BSF_DET["WFS_TXT_BEFORE_INPUT"]) != ""){ echo '<span class="input-group-addon">'.bsf_show_text($W,$WF,trim($BSF_DET["WFS_TXT_BEFORE_INPUT"]),$WF_TYPE).'</span>'; } 
	/* End จัด Group Input  */
	/* Start tooltip  */
	if(trim((string)$BSF_DET["WFS_TOOLTIP"]) != ""){ 
	$class_extra .= ' data-toggle="tooltip" data-placement="top" title="'.bsf_show_text($W,$WF,trim($BSF_DET["WFS_TOOLTIP"]),$WF_TYPE).'"';
	} 
	/* End tooltip  */
	/* Start placeholder  */
	if(trim((string)$BSF_DET["WFS_PLACEHOLDER"]) != ""){ 
	$class_extra .= ' placeholder ="'.bsf_show_text($W,$WF,trim($BSF_DET["WFS_PLACEHOLDER"]),$WF_TYPE).'" '; 
	} 
	/* End placeholder  */
	/* Start Duplicate  */
	if($BSF_DET["WFS_CHECK_DUP"] == "Y"){ 
	$class_input .= ' wf_check_dup';
	}
	/* End Duplicate  */
	/* Start เฉพาะ text / textarea */
	$input_itype = 'text';
	if($BSF_DET["FORM_MAIN_ID"] == "1" OR $BSF_DET["FORM_MAIN_ID"] == "2"){
		if($BSF_DET['WFS_MAX_LENGTH'] != 0){ $class_extra .= ' maxlength="'.$BSF_DET['WFS_MAX_LENGTH'].'"';  $class_input .= '  max-textarea'; }
		if($BSF_DET['WFS_OPTION_TXT_HEIGHT'] != 0){ $class_extra .= ' style="height: '.$BSF_DET['WFS_OPTION_TXT_HEIGHT'].'px"';}
		if($BSF_DET['WFS_INPUT_FORMAT'] == "C"){ $class_input .= ' idcard'; }
		if($BSF_DET['WFS_INPUT_FORMAT'] == "E"){ $class_input .= ' email'; }
		if($BSF_DET['WFS_INPUT_FORMAT'] == "N"){ $class_input .= ' autonumber'; $class_extra .= ' data-v-max="9999999999999999999" data-v-min="-9999999999999999999"'; }
		if($BSF_DET['WFS_INPUT_FORMAT'] == "N1"){ $class_input .= ' autonumber'; $class_extra .= ' data-v-max="9999999999999999999.0" data-v-min="-9999999999999999999.0"'; }
		if($BSF_DET['WFS_INPUT_FORMAT'] == "N2"){ $class_input .= ' autonumber'; $class_extra .= ' data-v-max="9999999999999999999.00" data-v-min="-9999999999999999999.00"'; }
		if($BSF_DET['WFS_INPUT_FORMAT'] == "N3"){ $class_input .= ' autonumber'; $class_extra .= ' data-v-max="9999999999999999999.000" data-v-min="-9999999999999999999.000"'; }
		if($BSF_DET['WFS_INPUT_FORMAT'] == "N4"){ $class_input .= ' autonumber'; $class_extra .= ' data-v-max="9999999999999999999.0000" data-v-min="-9999999999999999999.0000"'; }
		if($BSF_DET['WFS_INPUT_FORMAT'] == "N5"){ $class_input .= ' autonumber'; $class_extra .= ' data-v-max="9999999999999999999.00000" data-v-min="-9999999999999999999.00000"'; }
		if($BSF_DET['WFS_INPUT_FORMAT'] == "N6"){ $class_input .= ' autonumber'; $class_extra .= ' data-v-max="9999999999999999999.000000" data-v-min="-9999999999999999999.000000"'; }
		if($BSF_DET['WFS_INPUT_FORMAT'] == "TU"){ $class_input .= ' text-uppercase'; }
		if($BSF_DET['WFS_INPUT_FORMAT'] == "TL"){ $class_input .= ' text-lowercase'; }
		if($BSF_DET['WFS_INPUT_FORMAT'] == "TC"){ $class_input .= ' text-capitalize'; }
		if($BSF_DET['WFS_INPUT_FORMAT'] == "C"){ $class_input .= ' idcard'; }
		if($BSF_DET['WFS_INPUT_FORMAT'] == "P"){ $input_itype = 'password'; }
	}
	/* End เฉพาะ text / textarea */
	
	//check form edit or view
	if($WF_TYPE=='F' AND $WFS_CONF != '' AND $WFSCONF['WFS_FORM_INPUT_SHOW'] == "M"){
		if(count($WFS_FORM_FIELD_EDIT)> 0 AND in_array($BSF_DET["WFS_ID"],$WFS_FORM_FIELD_EDIT)){
			$VIEW_F = "";
		}
		if(count($WFS_FORM_FIELD_VIEW)> 0 AND in_array($BSF_DET["WFS_ID"],$WFS_FORM_FIELD_VIEW)){
			$VIEW_F = "Y";
		}
	}
	
	
	/* Start เฉพาะ radio , checkbox , selectbox*/
	if($VIEW != "Y" AND $VIEW_F != "Y"){
	if($BSF_DET["FORM_MAIN_ID"]=="4" OR $BSF_DET["FORM_MAIN_ID"]=="5" OR $BSF_DET["FORM_MAIN_ID"]=="9"){
		if($BSF_DET["FORM_MAIN_ID"] != "5" OR $BSF_DET["WFS_INPUT_FORMAT"] != "O"){

			if($WF[$BSF_DET["WFS_FIELD_NAME"]] == "" AND $default != ''){
				$WF[$BSF_DET["WFS_FIELD_NAME"]] = $default;
			}
		$data_list = array();
		$data_list = wf_call_relation($BSF_DET["WFS_ID"],$WF[$pk_field],$WF);
			if($WF_TYPE == "S"){
				foreach($data_list as $chkb => $chkv){
					if($chkv['id'] == $WF[$chkv['name']]){
						$data_list[$chkb]['checked'] = "checked";
					}
				}
			}
		}
	 
	}
	}
	if($BSF_DET["FORM_MAIN_ID"]=="11" ){
	if($_SESSION['WF_LANGUAGE'] == ""){ $pr_name = "PROVINCE_NAME"; }else{ $pr_name = "PROVINCE_NAME_EN"; }
	$sql_option = db::query("select PROVINCE_CODE,".$pr_name." from G_PROVINCE  order by ".$pr_name);
	while($rec_option = db::fetch_array($sql_option)){ $data_list[$rec_option['PROVINCE_CODE']] = $rec_option[$pr_name]; }
	}
	if($BSF_DET["FORM_MAIN_ID"]=="12" AND $default != ""){
	if($_SESSION['WF_LANGUAGE'] == ""){ $amp_name = "AMPHUR_NAME"; }else{ $amp_name = "AMPHUR_NAME_EN"; }
	$sql_option = db::query("select PROVINCE_CODE,AMPHUR_CODE,".$amp_name." from G_AMPHUR WHERE PROVINCE_CODE = '".substr($default,0,2)."' order by AMPHUR_CODE");
	while($rec_option = db::fetch_array($sql_option)){ $data_list[$rec_option['PROVINCE_CODE'].$rec_option['AMPHUR_CODE']] = str_replace("*","",$rec_option[$amp_name]); }
	}
	if($BSF_DET["FORM_MAIN_ID"]=="13" AND $default != ""){
	if($_SESSION['WF_LANGUAGE'] == ""){ $tam_name = "TAMBON_NAME"; }else{ $tam_name = "TAMBON_NAME_EN"; }
	$sql_option = db::query("select PROVINCE_CODE,AMPHUR_CODE,TAMBON_CODE,".$tam_name." from G_TAMBON WHERE PROVINCE_CODE = '".substr($default,0,2)."' AND AMPHUR_CODE = '".substr($default,2,2)."' order by TAMBON_CODE");
	while($rec_option = db::fetch_array($sql_option)){ $data_list[$rec_option['PROVINCE_CODE'].$rec_option['AMPHUR_CODE'].$rec_option['TAMBON_CODE']] = str_replace("*","",$rec_option[$tam_name]); }
	}
	/* End เฉพาะ text / textarea */
	/* Custom Class */
	if($BSF_DET["WFS_DEFINE_CLASS"] != ''){
	$class_input .= ' '.$BSF_DET["WFS_DEFINE_CLASS"];
	}
	/* Custom Class */

	if($VIEW == "Y" OR $VIEW_F == "Y"){
		if($BSF_DET["FORM_MAIN_ID"] == "16"){
			if($BSF_DET["WFS_INPUT_FORMAT"]=="O" OR $BSF_DET["WFS_INPUT_FORMAT"]=="T"){
					if($BSF_DET["WFS_FORM_SELECT"]!=''){
					echo '</div><div class="form-group row">';
					$FRM = array();
					$sql_form_O = db::query("select WF_MAIN_SHORTNAME,WF_TYPE from WF_MAIN where WF_MAIN_ID = '".$BSF_DET["WFS_FORM_SELECT"]."'");
					$rec_main_form_O = db::fetch_array($sql_form_O);
					if($WF[$pk_field] != ''){ 
						$wfs_fcon = '';
						if($BSF_DET["WFS_INPUT_FORMAT"]=="T"){
							$wfs_fcon = " AND WFS_ID = '".$BSF_DET["WFS_ID"]."' ";
						}
						$sql_show_form = "select * from ".$rec_main_form_O['WF_MAIN_SHORTNAME']." where WF_MAIN_ID = '".$W."' AND WFR_ID = '".$WF[$pk_field]."' ".$wfs_fcon;
						$query_frm = db::query($sql_show_form);
						$FRM=db::fetch_array($query_frm);
					}
					bsf_show_form($BSF_DET["WFS_FORM_SELECT"],'0',$FRM,$rec_main_form_O['WF_TYPE'],'','','Y');
					
					}
				}else{
				echo '<span id="WFS_FORM_'.$BSF_DET["WFS_ID"].'"></span>';
				echo '<script type="text/javascript">get_wfs_show(\'WFS_FORM_'.$BSF_DET["WFS_ID"].'\',\'';
						if($_SESSION["WF_USER_ID"] != ""){ echo '../workflow/'; } 
						echo 'form_main.php\',\'W='.$BSF_DET["WFS_FORM_SELECT"].'&WFD='.$WFD.'&WFS='.$BSF_DET["WFS_ID"].'&WFR='.$WF[$pk_field].'&F_TEMP_ID='.$F_TEMP_ID.'&WF_VIEW=VIEW&WFR_ID='.$WF[$pk_field].'&wfp='.conText($_GET['wfp']).'\',\'GET\',\'\');</script>';
				}
		}elseif($BSF_DET["FORM_MAIN_ID"] == "8"){
			echo bsf_show_text($W,$WF,$right_data_val,$WF_TYPE);
		}else{
			if($BSF_DET["WFS_FIELD_NAME"] != ''){
			echo bsf_show_text($W,$WF,'##'.$BSF_DET["WFS_FIELD_NAME"].'!!',$WF_TYPE);
				if($NUM_SCRIPT > 0){
				$bsf_script .= 'bsf_change_obj'.$WF_TYPE.'_'.$WFS."('".$default."');";
				}
			}
		}
	}else{
		if($BSF_DET["WFS_READONLY"] == 'Y'){
		$class_extra .= ' readonly="true" ';
		}
		if($BSF_DET["WFS_DISABLE"] == 'Y'){
		$class_extra .= ' disabled="true" ';
		}
		switch($BSF_DET["FORM_MAIN_ID"]){
			case '1': //textbox
				if($NUM_SCRIPT > 0){ //change
				$class_extra .= ' onBlur="bsf_change_obj'.$WF_TYPE.'_'.$WFS.'(this.value);" ';
				$bsf_script .= 'bsf_change_obj'.$WF_TYPE.'_'.$WFS."('".$default."');";
				}
				if($BSF_DET['WFS_INPUT_FORMAT'] == "N" OR $BSF_DET['WFS_INPUT_FORMAT'] == "N1" OR $BSF_DET['WFS_INPUT_FORMAT'] == "N2" OR $BSF_DET['WFS_INPUT_FORMAT'] == "N3" OR $BSF_DET['WFS_INPUT_FORMAT'] == "N4" OR $BSF_DET['WFS_INPUT_FORMAT'] == "N5" OR $BSF_DET['WFS_INPUT_FORMAT'] == "N6"){ 
				 $default = str_replace(",","",$default);
				}
				$class_input = 'form-control'.$class_input;
				echo form_itext($BSF_DET["WFS_FIELD_NAME"],$default,$class_input,$class_extra,$input_itype);
				break;
			case '2': //textarea 
				if($BSF_DET['WFS_INPUT_FORMAT'] == "N" OR $BSF_DET['WFS_INPUT_FORMAT'] == "N1" OR $BSF_DET['WFS_INPUT_FORMAT'] == "N2" OR $BSF_DET['WFS_INPUT_FORMAT'] == "N3" OR $BSF_DET['WFS_INPUT_FORMAT'] == "N4" OR $BSF_DET['WFS_INPUT_FORMAT'] == "N5" OR $BSF_DET['WFS_INPUT_FORMAT'] == "N6"){ 
				 $default = str_replace(",","",$default);
				}
				if($BSF_DET['WFS_INPUT_FORMAT'] == "ED"){ 
					$editor_folder2 = '../wysiwyg/w'.$W;
					$fp = @fopen($editor_folder2.'/e_'.$BSF_DET["WFS_FIELD_NAME"].'_'.$WF[$pk_field].'.tmp','r');
					$default2 = @fread($fp, filesize($editor_folder2.'/e_'.$BSF_DET["WFS_FIELD_NAME"].'_'.$WF[$pk_field].'.tmp'));
					@fclose($fp);
					if($default == ""){ $default = $default2; }
				}
				echo form_iarea($BSF_DET["WFS_FIELD_NAME"],$default,$class_input,$class_extra);
				if($BSF_DET['WFS_INPUT_FORMAT'] == "ED"){ 
				if($BSF_DET['WFS_OPTION_TXT_HEIGHT'] != ""){
				$editor_h = $BSF_DET['WFS_OPTION_TXT_HEIGHT'];
				}else{
				$editor_h = "300";	
				}
				
				 echo "<script> $('#".$BSF_DET["WFS_FIELD_NAME"]."').summernote({ height: ".$editor_h.", lang: 'th-TH' }); </script>";
				}
				break;
			case '3': //date 
				$pos_slash = strpos($default, '/');
				if($pos_slash === false) {
					if($BSF_DET["WFS_CALENDAR_EN"]=="Y"){
					$default = db2date_en($default);
					}else{
						$default = db2date($default);
					}
				}
				if($BSF_DET["WFS_CALENDAR_EN"]=="Y"){
					$class_input = "_en ".$class_input;
				}
				echo form_idate($BSF_DET["WFS_FIELD_NAME"],$default,$class_input,$class_extra);
				break;
			case '4': //radio 
				if($BSF_DET["WFS_OPTION_NEW_LINE"] == "" OR $BSF_DET["WFS_OPTION_NEW_LINE"] == "N"){
					$class_input .= ' radio-inline';
				}

				if($NUM_SCRIPT > 0 OR $NUM_ONCHANGE_SEND > 0){ //change
				$class_extra .= ' onChange="';
				if($NUM_SCRIPT > 0){
				$class_extra .= 'bsf_change_obj'.$WF_TYPE.'_'.$WFS.'(this.value);';
				$bsf_script .= 'bsf_change_obj'.$WF_TYPE.'_'.$WFS."('".$default."');";
				}
				if($NUM_ONCHANGE_SEND > 0){
				$class_extra .= 'bsf_change_process'.$WF_TYPE.'_'.$WFS.'(this.value);';
				$bsf_script .= 'bsf_change_process'.$WF_TYPE.'_'.$WFS."('".$default."');";
				}
				$class_extra .= '"';
				}
				echo "<div id=\"WF_RADIO".$BSF_DET["WFS_ID"]."\">";
				echo form_iradio($BSF_DET["WFS_FIELD_NAME"],$data_list,$class_input,$class_extra);
				echo "</div>";
				break;
			case '5': //checkbox
				if($NUM_SCRIPT > 0){ //change
				$class_extra .= ' onClick="bsf_chk_obj'.$WF_TYPE.'_'.$WFS.'(this);" ';
				$bsf_script .= 'bsf_chk_obj'.$WF_TYPE.'_'.$WFS."(document.getElementById('".$BSF_DET["WFS_FIELD_NAME"]."'));";
				}
				if($BSF_DET["WFS_OPTION_NEW_LINE"] == "Y"){
					$class_input .= ' col-xs-5 ';
				}else{
					$class_input .= ' m-l-15';
				}
				echo form_icheck($BSF_DET["WFS_FIELD_NAME"],$data_list,$class_input,$class_extra);
				break;
			case '6': //browsefile 
				echo bsa_show($WFS_FIELD_NAME,$WF[$pk_field],$W,$BSF_DET["WFS_FILE_ORDER"],$BSF_DET["WFS_FILE_LIGHTBOX"]);
				$sql_attach_check = db::query("select COUNT(FILE_ID) AS FILE_ID from WF_FILE where WFS_FIELD_NAME ='".$WFS_FIELD_NAME."' AND WFR_ID='".$WF[$pk_field]."' AND WF_MAIN_ID = '".$W."' AND FILE_STATUS = 'Y' ");
				
				$NUM_FCHECK = db::fetch_array($sql_attach_check);
				if($NUM_FCHECK['FILE_ID'] > 0){
					$class_extra = str_replace('required aria-required="true"','',$class_extra);
				}
				
				$ext_comment= "";
				$multi= "";
				if(trim($BSF_DET["WFS_PLACEHOLDER"]) != ""){ 
				$class_input = trim($BSF_DET["WFS_PLACEHOLDER"]);
				}
				$size_ex_name = "";
				if($BSF_DET["WFS_FILE_LIMIT"] == "Y" AND $BSF_DET["WFS_FILE_LIMIT_SIZE"] > 0){
					if($_SESSION['WF_LANGUAGE'] == ""){ $size_ex_name = "ขนาดไฟล์ไม่เกิน "; }else{ $size_ex_name = "File size not over "; }
					$size_ex_name = $size_ex_name.bsf_show_size($BSF_DET["WFS_FILE_LIMIT_SIZE"]);
				}
				$l_ex_name = "";
				$l_ex_name_db = "";
				if(trim($BSF_DET["WFS_FILE_EXTEND_ALLOW"]) != ""){
					$extension = array();
					$extension_db = array();
					$ext = explode(',',trim($BSF_DET["WFS_FILE_EXTEND_ALLOW"]));
					foreach($ext as $val){
						$sql_option = db::query("select FILE_MIME from G_MIME_TYPES WHERE FIEL_TYPE = '".$val."'");
						$rec_option = db::fetch_array($sql_option);	
						$extension[] = $rec_option["FILE_MIME"];
						$extension_db[] = "'.".$val."'";
					}
					$class_extra .= ' accept="'.implode(',',$extension).'"';
					
					if($_SESSION['WF_LANGUAGE'] == ""){ $l_ex_name = "เฉพาะนามสกุล"; }else{ $l_ex_name = "Specific file extensions "; }
					$l_ex_name = $l_ex_name.' '.trim($BSF_DET["WFS_FILE_EXTEND_ALLOW"]).' ';
				}
				$ext_comment = '<small class="form-text text-muted">'.$l_ex_name.$size_ex_name.'</small>';
				if($BSF_DET["WFS_DROPBOX_USE"] == "Y"){ 
				echo "<div id=\"db_result_".$WFS_FIELD_NAME."\"></div>";
				if(trim($BSF_DET["WFS_INPUT_FORMAT"]) == "M"){ 
				$dropbox_clear = "";
				$multi_d = "true";
				}else{
				$dropbox_clear = "$('#db_result_".$WFS_FIELD_NAME."').html('');";
				$multi_d = "false";
				} 
				if(count($extension_db) > 0){ $l_ex_name_db = 'extensions: ['.implode(',',$extension_db).'],'; }
				echo '<script>
				var options_'.$BSF_DET["WFS_ID"].' = {
  success: function (files) {
	'.$dropbox_clear.'
    files.map(file => {
      let html = `<div class="dropbox_card row">
                    <div class="col-md-10"><a href="${file.link}" target="_blank"><i class="icofont icofont-social-dropbox"></i> ${file.name}</a><div class="f-right"><a href="javascript:;"><i class="icofont icofont-close dropbox-close"></i></a></div><input type="hidden" name="DB_N_'.$WFS_FIELD_NAME.'[]" value="${file.name}"><input type="hidden" name="DB_ID_'.$WFS_FIELD_NAME.'[]" value="${file.id}"><input type="hidden" name="DB_L_'.$WFS_FIELD_NAME.'[]" value="${file.link}"></div>
                  </div>`

      $("#db_result_'.$WFS_FIELD_NAME.'").append(html)
    })
  },
  cancel: function () {},linkType: "direct",multiselect: '.$multi_d.','.$l_ex_name_db.'folderselect: false,
}	 
				</script>';
				echo '<a href="#123"  onclick="Dropbox.choose(options_'.$BSF_DET["WFS_ID"].');" class="btn text-white btn-primary" title="dropbox"><i class="icofont icofont-social-dropbox"></i> Dropbox</a>';
				}
				if(trim($BSF_DET["WFS_INPUT_FORMAT"]) == "M"){ 
				$multi .= "multiple";
				}else{
				$multi .= "single";
				} 
				echo form_ifile($BSF_DET["WFS_FIELD_NAME"],$default,$class_input,$multi,$class_extra,$ext_comment);
				
				if($BSF_DET["WFS_SCAN_USE"] == "Y"){
				$bizSmartDoc = new BizSmartDoc();
				echo $bizSmartDoc->formInjection($BSF_DET["WFS_FIELD_NAME"]);
				}
				 
				if(trim($BSF_DET["WFS_INPUT_FORMAT"]) == "O" AND $BSF_DET["WFS_FILE_LIMIT"] == "Y" AND $BSF_DET["WFS_FILE_LIMIT_SIZE"] > 0){
				?><script>
				$('#<?php echo $BSF_DET["WFS_FIELD_NAME"]; ?>').bind('change', function() {
				  if(this.files[0].size > <?php echo $BSF_DET["WFS_FILE_LIMIT_SIZE"]; ?>){
					  alert('ขนาดไฟล์เกินกำหนด (ไม่เกิน <?php echo bsf_show_size($BSF_DET["WFS_FILE_LIMIT_SIZE"]); ?>)');
					  this.value='';
				  }
				});
				</script><?php
				}
				if(trim($BSF_DET["WFS_INPUT_FORMAT"]) == "M" AND $BSF_DET["WFS_FILE_LIMIT"] == "Y" AND $BSF_DET["WFS_FILE_LIMIT_SIZE"] > 0){
				?><script>
				$('#<?php echo $BSF_DET["WFS_FIELD_NAME"]; ?>').bind('change', function() {
					var input = document.getElementById(this.id);
					var all_size = 0;
					for (var x = 0; x < input.files.length; x++) {
						all_size += input.files[x].size;
					}
				  if(all_size > <?php echo $BSF_DET["WFS_FILE_LIMIT_SIZE"]; ?>){
					  alert('ขนาดไฟล์เกินกำหนด (ไม่เกิน <?php echo bsf_show_size($BSF_DET["WFS_FILE_LIMIT_SIZE"]); ?>)');
					  this.value='';
				  }
				});
				</script><?php
				}
				break;
			case '7': //hidden
				if($NUM_SCRIPT > 0){ //change
				$class_extra .= ' onBlur="bsf_change_obj'.$WF_TYPE.'_'.$WFS.'(this.value);" ';
				$bsf_script .= 'bsf_change_obj'.$WF_TYPE.'_'.$WFS."('".$default."');";
				}
				$class_input = ' hidden';
				echo form_itext($BSF_DET["WFS_FIELD_NAME"],$default,$class_input,$class_extra,$input_itype);
				if($BSF_DET["WFS_OPTION_SELECT_DATA"] != ""){
				echo "<div id=\"WF_HIDDEN".$BSF_DET["WFS_ID"]."\">";
				echo '<span id="WFH_'.$BSF_DET["WFS_FIELD_NAME"].'_SHOW">'.bsf_show_text($W,$WF,'##'.$BSF_DET["WFS_FIELD_NAME"].'!!',$WF_TYPE).'</span> <a href="#!" class="btn btn-primary btn-mini" data-toggle="modal" data-target="#bizModal3" onclick="open_modal(\'';
						if($_SESSION["WF_USER_ID"] != ""){ echo '../workflow/'; } 
						echo 'wf_load.php?W='.$BSF_DET["WFS_OPTION_SELECT_DATA"].'&WFS='.$BSF_DET["WFS_ID"].'&WFR='.$WF[$pk_field].'&wfp='.conText($_GET['wfp']).'\', \'\',\'3\')"> <i class="fa fa-search"></i> '.trim($BSF_DET["WFS_PLACEHOLDER"]).'</a>';
				echo "</div>";
				}
				
				break;
				break;
			case '8': //text
				echo bsf_show_text($W,$WF,$right_data_val,$WF_TYPE);
				break;
			case '9': //select
				$use_select2 = "";
				if(trim($BSF_DET["WFS_OPTION_SELECT2"]) == "Y"){
					if(trim($BSF_DET["WFS_OPTION_SELECT2COM"]) == "Y"){
						$class_input .= ' select2com'; 
					}else{
						$class_input .= ' select2';
					}
				
				$use_select2 = "Y";
				}
				
				if($NUM_THROW > 0){ //throw
				$class_input .= ' wf_onchange_th';
				$class_extra .= ' wfs-id="'.$WFS.'" '; 
				}
				if($NUM_SCRIPT > 0){ //change
				$class_extra .= ' onChange="bsf_change_obj'.$WF_TYPE.'_'.$WFS.'(this.value);" ';
				$bsf_script .= 'bsf_change_obj'.$WF_TYPE.'_'.$WFS."('".$default."');";
				}
				if(trim($BSF_DET["WFS_PLACEHOLDER"]) != ""){ 
				$class_extra .= '><option value="" ';
					if(trim($BSF_DET["WFS_OPTION_SELECT2"]) == "Y"){ $class_extra .= 'disabled'; }
				$class_extra .= ' selected>'.trim($BSF_DET["WFS_PLACEHOLDER"]).'</option';
				}
				if($BSF_DET["WFS_OPTION_ADD_MAIN"]=="Y"){ echo '<label class="input-group">'; }
				echo form_iselect($BSF_DET["WFS_FIELD_NAME"],$data_list,$class_input,$class_extra);
				if($BSF_DET["WFS_OPTION_ADD_MAIN"]=="Y"){
				echo '<span class="input-group-addon bg-primary" data-toggle="modal" data-target="#bizModal2" onclick="open_modal(\'';
				if($_SESSION["WF_USER_ID"] != ""){ echo '../workflow/'; } 
				echo 'master_mgt.php?W='.$BSF_DET["WFS_OPTION_SELECT_DATA"].'&TARGET_NAME='.$BSF_DET["WFS_FIELD_NAME"].'&TARGET='.$BSF_DET["WFS_ID"].'&use_select2='.$use_select2.'\', \'\',\'2\')" ><i class="typcn typcn-document-add"></i></span>';
				}
				break;
			case '11': //Province
				if($_SESSION['WF_LANGUAGE'] == ""){ $pr_name_choose = "เลือกจังหวัด"; }else{ $pr_name_choose = "Select Province"; }
				if($BSF_DET["WFS_SHOW_AMPHUR"] != ''){ $BSF_DET["WFS_SHOW_AMPHUR"].$SHOW; }
				if($BSF_DET["WFS_SHOW_TAMBON"] != ''){ $BSF_DET["WFS_SHOW_TAMBON"].$SHOW; }
				if($BSF_DET["WFS_SHOW_ZIPCODE"] != ''){ $BSF_DET["WFS_SHOW_ZIPCODE"].$SHOW; }
				$class_input .= ' select2-province';
				if($BSF_DET["WFS_SHOW_AMPHUR"] !=""){
				$class_extra .= ' onChange="get_amphur(\''.$BSF_DET["WFS_FIELD_NAME"].'\',\''.$BSF_DET["WFS_SHOW_AMPHUR"].$SHOW.'\',\''.$BSF_DET["WFS_SHOW_TAMBON"].$SHOW.'\',\''.$BSF_DET["WFS_SHOW_ZIPCODE"].$SHOW.'\');"><option value="" disabled selected>'.$pr_name_choose.'</option';
				}else{
				$class_extra .= '><option value="" disabled selected>'.$pr_name_choose.'</option';	
				}
				echo form_iselect2($BSF_DET["WFS_FIELD_NAME"],$data_list,$default,$class_input,$class_extra);
				break;
			case '12': //Amphur
					if($BSF_DET["WFS_SHOW_PROVINCE"] == ""){
						$class_input .= ' wf_select2-single-amphur';
					}else{
						$class_input .= ' select2-amphur';
					} 
				if($_SESSION['WF_LANGUAGE'] == ""){ $am_name_choose = "เลือกอำเภอ"; }else{ $am_name_choose = "Select District"; }
				if($BSF_DET["WFS_SHOW_PROVINCE"] != ''){ $BSF_DET["WFS_SHOW_PROVINCE"].$SHOW; }
				if($BSF_DET["WFS_SHOW_TAMBON"] != ''){ $BSF_DET["WFS_SHOW_TAMBON"].$SHOW; }
				if($BSF_DET["WFS_SHOW_ZIPCODE"] != ''){ $BSF_DET["WFS_SHOW_ZIPCODE"].$SHOW; }
				if($BSF_DET["WFS_SHOW_TAMBON"] !=""){
				$class_extra .= ' onChange="get_tambon(\''.$BSF_DET["WFS_SHOW_PROVINCE"].$SHOW.'\',\''.$BSF_DET["WFS_FIELD_NAME"].'\',\''.$BSF_DET["WFS_SHOW_TAMBON"].$SHOW.'\',\''.$BSF_DET["WFS_SHOW_ZIPCODE"].$SHOW.'\');"><option value="" disabled selected>'.$am_name_choose.'</option';
				}else{
				$class_extra .= '><option value="" selected="selected">'.$am_name_choose.'</option';
				}
				echo form_iselect2($BSF_DET["WFS_FIELD_NAME"],$data_list,$default,$class_input,$class_extra);
				break;
			case '13': //Tambon
				if($_SESSION['WF_LANGUAGE'] == ""){ $ta_name_choose = "เลือกตำบล"; }else{ $am_name_choose = "Select Sub-District"; }
				if($BSF_DET["WFS_SHOW_PROVINCE"] != ''){ $BSF_DET["WFS_SHOW_PROVINCE"].$SHOW; }
				if($BSF_DET["WFS_SHOW_AMPHUR"] != ''){ $BSF_DET["WFS_SHOW_AMPHUR"].$SHOW; }
				if($BSF_DET["WFS_SHOW_ZIPCODE"] != ''){ $BSF_DET["WFS_SHOW_ZIPCODE"].$SHOW; }
					if($BSF_DET["WFS_SHOW_AMPHUR"] == ""){
						$class_input .= ' wf_select2-single-tambon';
					}else{
						$class_input .= ' select2-tambon';
					}
				if($BSF_DET["WFS_SHOW_ZIPCODE"] !=""){
				$class_extra .= ' onChange="get_zipcode(\''.$BSF_DET["WFS_SHOW_PROVINCE"].$SHOW.'\',\''.$BSF_DET["WFS_SHOW_AMPHUR"].$SHOW.'\',\''.$BSF_DET["WFS_FIELD_NAME"].'\',\''.$BSF_DET["WFS_SHOW_ZIPCODE"].$SHOW.'\');"><option value="" disabled selected>'.$ta_name_choose.'</option';
				}else{
				$class_extra .= '><option value="" disabled selected>'.$ta_name_choose.'</option';		
				}
				echo form_iselect2($BSF_DET["WFS_FIELD_NAME"],$data_list,$default,$class_input,$class_extra);
				break;
			case '14': //Zipcode
				$class_input = 'form-control'.$class_input;
				echo form_itext($BSF_DET["WFS_FIELD_NAME"],$default,$class_input,$class_extra);
				break;
			case '15': //View
				if($BSF_DET["WFS_OPTION_SELECT_DATA"] != ''){
				echo "<span id=\"WFS_VIEW_".$BSF_DET["WFS_ID"]."\"></span><script type=\"text/javascript\">get_wfs_show('WFS_VIEW_".$BSF_DET["WFS_ID"]."','../process/prototype_preview.php','WFD=".$BSF_DET["WFS_OPTION_SELECT_DATA"]."','','');</script>";
				}
				break;
			case '16': //Form
				if($BSF_DET["WFS_INPUT_FORMAT"]=="O" OR $BSF_DET["WFS_INPUT_FORMAT"]=="T"){
					if($BSF_DET["WFS_FORM_SELECT"]!=''){
					echo '</div><div class="form-group row">';
					$FRM = array();
					$sql_form_O = db::query("select WF_MAIN_SHORTNAME,WF_TYPE from WF_MAIN where WF_MAIN_ID = '".$BSF_DET["WFS_FORM_SELECT"]."'");
					$rec_main_form_O = db::fetch_array($sql_form_O);
					if($WF[$pk_field] != ''){ 
						$wfs_fcon = '';
						if($BSF_DET["WFS_INPUT_FORMAT"]=="T"){
							$wfs_fcon = " AND WFS_ID = '".$BSF_DET["WFS_ID"]."' ";
						}
						$sql_show_form = "select * from ".$rec_main_form_O['WF_MAIN_SHORTNAME']." where WF_MAIN_ID = '".$W."' AND WFR_ID = '".$WF[$pk_field]."' ".$wfs_fcon;
						$query_frm = db::query($sql_show_form);
						$FRM=db::fetch_array($query_frm);
					}
					bsf_show_form($BSF_DET["WFS_FORM_SELECT"],'0',$FRM,$rec_main_form_O['WF_TYPE'],'','');
					
					}
				}else{
				echo '<span id="WFS_FORM_'.$BSF_DET["WFS_ID"].'"></span>';
				echo '<script type="text/javascript">$(document).ready(function() { get_wfs_show(\'WFS_FORM_'.$BSF_DET["WFS_ID"].'\',\'';
						if($_SESSION["WF_USER_ID"] != ""){ echo '../workflow/'; } 
						echo 'form_main.php\',\'W='.$BSF_DET["WFS_FORM_SELECT"].'&WFD='.$WFD.'&WFS='.$BSF_DET["WFS_ID"].'&WFR='.$WF[$pk_field].'&F_TEMP_ID='.$F_TEMP_ID.'&WFR_ID='.$WF[$pk_field].'&wfp='.conText($_GET['wfp']).'\',\'GET\',\'\'); });$(document).ready(function(){ $(\'button.close-modal\').click(function(){ var modal_number = $(this).attr(\'data-number\'); var modal_id = $(this).parents(\':eq(3)\').attr(\'id\'); $(\'#\'+modal_number).modal(\'hide\'); $(\'#\'+modal_id+\' .modal-title, #\'+modal_id+\' .modal-body\').html(\'\'); });}); </script>';
				}
				if($BSF_DET["WFS_FORM_POPUP"] == "" OR $BSF_DET["WFS_FORM_POPUP"] == "Y"){
				echo '<div class="modal fade modal-flex" id="bizModal_'.$BSF_DET["WFS_ID"].'" role="dialog" aria-labelledby="myModalLabel" data-backdrop="static"><div class="modal-dialog modal-lg " role="document"><div class="modal-content"><div class="modal-header"><button type="button" class="close close-modal" data-number="bizModal_'.$BSF_DET["WFS_ID"].'" aria-label="Close"><span aria-hidden="true">&times;</span></button><h4 class="modal-title" id="myModalLabel"></h4></div><div class="modal-body"></div><div class="modal-footer"><button type="button" class="btn btn-danger close-modal" data-number="bizModal_'.$BSF_DET["WFS_ID"].'">ปิด</button></div></div></div></div>';
				}
				break;
		}
	}
	//ข้อความหลัง input 
	if(trim((string)$BSF_DET["WFS_TXT_AFTER_INPUT"]) != ""){ echo '<span class="input-group-addon">'.bsf_show_text($W,$WF,trim((string)$BSF_DET["WFS_TXT_AFTER_INPUT"]),$WF_TYPE).'</span>'; }
	if(trim((string)$BSF_DET["WFS_TXT_BEFORE_INPUT"]) != "" OR trim((string)$BSF_DET["WFS_TXT_AFTER_INPUT"]) != "" OR $BSF_DET["FORM_MAIN_ID"] == '3'){ echo '</label>'; } 
	//หมายเหตุ
	if(trim((string)$BSF_DET["WFS_COMMENT"]) != ""){ 
	echo '<small class="form-text text-muted">'.bsf_show_text($W,$WF,trim($BSF_DET["WFS_COMMENT"]),$WF_TYPE).'</small>';
	}
	//Date change function
	if($BSF_DET["WFS_ONCHANGE"] != "" AND $BSF_DET["WFS_FIELD_NAME"] != "" AND ($BSF_DET["FORM_MAIN_ID"]=="1" OR $BSF_DET["FORM_MAIN_ID"]=="2" OR $BSF_DET["FORM_MAIN_ID"]=="3")){
		$arr = explode("@",$BSF_DET["WFS_ONCHANGE"]);
		$txt_java = "";
		$txt_java1 = "";
		foreach ($arr as &$value){
		$string = "@".$value;
		$matches = array();
		$pattern = '/(@[a-zA-Z_][a-zA-Z0-9\$_.]*)?/';
		preg_match($pattern,$string,$matches);
			if($matches[0] != ""){
			$obj_orginal = substr($matches[0], 1);
			$obj = $obj_orginal.$SHOW;
			$txt_java .= "$('#".$obj."').blur(function(){ get_".$BSF_DET["WFS_FIELD_NAME"]."(); });\n";
			$txt_java1 .= "+'&".$obj_orginal."='+$('#".$obj."').val()";
			}
		}
	?>
	<script type="text/javascript">
		$(document).ready(function(){
			<?php echo $txt_java; ?>
			function get_<?php echo $BSF_DET["WFS_FIELD_NAME"]; ?>(){
				var dataString = 'WF_ONCTYPE=<?php echo $BSF_DET["FORM_MAIN_ID"]; ?>&WFS_INPUT_FORMAT=<?php echo $BSF_DET['WFS_INPUT_FORMAT']; ?>&CFlag=<?php echo rawurlencode($BSF_DET["WFS_ONCHANGE"]); ?>'<?php echo $txt_java1; ?>;
				$.ajax({
					type: "GET",
					url: "<?php if($_SESSION["WF_USER_ID"] != ""){ echo '../workflow/'; } ?>date_function.php",
					data: dataString,
					cache: false,
					success: function(html){
						$('#<?php echo $BSF_DET["WFS_FIELD_NAME"]; ?>').val(html);
						$('#<?php echo $BSF_DET["WFS_FIELD_NAME"]; ?>').trigger('blur');
					}
				 });
			}
			<?php if($txt_java != ""){ echo "$('#".$obj."').trigger('blur');"; }; ?>
		})
	</script>
	<?php
	}
	if($BSF_DET["WFS_INPUT_EVENT"] != "" AND $BSF_DET["WFS_JAVASCRIPT_EVENT"] != "" AND $BSF_DET["WFS_FIELD_NAME"] !=''){
	?>
	<script type="text/javascript">
	$(document).ready(function(){
		$("#<?php echo $BSF_DET["WFS_FIELD_NAME"]; ?>").<?php echo $BSF_DET["WFS_INPUT_EVENT"]; ?>(function (){
			<?php echo wf_convert_var($BSF_DET["WFS_JAVASCRIPT_EVENT"],'Y'); ?>
		});
	})
	</script>
	<?php
	}
	if($BSF_DET["WFS_FIELD_NAME"] !='' AND $NUM_ONCHANGE_SEND > 0){ 
		$sql_change = db::query("SELECT WFS_ID FROM WF_ONCHANGE WHERE WFS_FIELD_SEND = '".$WFS_FIELD_NAME."' AND WF_MAIN_ID = '".$W."' AND WF_TYPE = '".$WF_TYPE."'");
		while($ONC = db::fetch_array($sql_change)){
		if($ONC['WFS_ID'] != ''){
			$sql_change_obj = db::query("select WFS_ID,WFS_NAME,WFS_FIELD_NAME,WFS_OPTION_SELECT2,WFS_OPTION_SELECT_DATA,FORM_MAIN_ID from WF_STEP_FORM where WFS_ID = '".$ONC['WFS_ID']."' ");
			$ONC_O = db::fetch_array($sql_change_obj);
			if($BSF_DET["FORM_MAIN_ID"] == "4"){
				
				?><script>function bsf_change_process<?php echo $WF_TYPE; ?>_<?php echo $WFS; ?>(val){
					<?php
			}else{
				?><script>$('#<?php echo $BSF_DET["WFS_FIELD_NAME"]; ?>').change(function(){
					var val = $('#<?php echo $BSF_DET["WFS_FIELD_NAME"]; ?>').val();
					<?php
			}
			 
				$onchtxt = "";
				$sql_change2 = db::query("SELECT WFS_FIELD_SEND FROM WF_ONCHANGE WHERE WFS_ID = '".$ONC['WFS_ID']."' AND WF_MAIN_ID = '".$W."' AND WF_TYPE = '".$WF_TYPE."'");
				while($ONC2 = db::fetch_array($sql_change2)){
					if($ONC2['WFS_FIELD_SEND'] == $BSF_DET["WFS_FIELD_NAME"]){
					echo "var ".$ONC2['WFS_FIELD_SEND']." = val;\n";
					}else{
					echo "var ".$ONC2['WFS_FIELD_SEND']." = $('#".$ONC2['WFS_FIELD_SEND'].$SHOW."').val();\n";	
					}
					echo "if(".$ONC2['WFS_FIELD_SEND']."==null){ ".$ONC2['WFS_FIELD_SEND']." = ''; }\n"; 
					$onchtxt .= "+'&".$ONC2['WFS_FIELD_SEND']."='+".$ONC2['WFS_FIELD_SEND'];
				}
				?>
				var url = "<?php if($_SESSION["WF_USER_ID"] != ""){ echo '../workflow/'; } ?>wf_onchange.php";
				var dataString = 'TARGET=<?php echo $ONC['WFS_ID']; ?>&WFR_ID=<?php echo conText($_GET['WFR_ID']); ?>&VAL='+val+'&FORM_MAIN_ID=<?php echo $ONC_O['FORM_MAIN_ID']; ?>&W=<?php echo $ONC_O['WFS_OPTION_SELECT_DATA']; ?>'<?php echo $onchtxt; ?>;
				$.ajax({
					   type: "GET",
					   url: url,
					   data: dataString, // serializes the form's elements.
					   cache: false,
					   success: function(html)
					   {
							<?php if($ONC_O['FORM_MAIN_ID']=="4"){ ?>
							$('#WF_RADIO<?php echo $ONC_O['WFS_ID']; ?>').html(html); 
							<?php }elseif($ONC_O['FORM_MAIN_ID']=="5"){ ?>
							$('#WF_CHKBOX<?php echo $ONC_O['WFS_ID']; ?>').html(html); 
							<?php }elseif($ONC_O['FORM_MAIN_ID']=="7"){ ?>
							$('#WF_HIDDEN<?php echo $ONC_O['WFS_ID']; ?>').html(html);
							<?php }else{ ?>
							$('select#<?php echo $ONC_O['WFS_FIELD_NAME'].$SHOW; ?>').html('<option value="" disabled="" selected="">เลือก<?php echo $ONC_O['WFS_NAME']; ?></option>').select2();
							$('select#<?php echo $ONC_O['WFS_FIELD_NAME'].$SHOW; ?>').select2({
								allowClear: true,
								data: html
							});
							$('select#<?php echo $ONC_O['WFS_FIELD_NAME'].$SHOW; ?>').select2("open"); 
							$('select#<?php echo $ONC_O['WFS_FIELD_NAME'].$SHOW; ?>').select2("close");

							<?php if($ONC_O['WFS_OPTION_SELECT2'] != "Y"){ ?>
							$('#<?php echo $ONC_O['WFS_FIELD_NAME'].$SHOW; ?>').select2("destroy");
							<?php } ?>
							<?php } ?>
					   }
					 });
			<?php 
			if($BSF_DET["FORM_MAIN_ID"] == "4"){ echo "}"; }else{ echo "});"; } ?>
			</script><?php
		}
		}
	}
	if(($BSF_DET["FORM_MAIN_ID"]=="1" OR $BSF_DET["FORM_MAIN_ID"]=="2") AND $BSF_DET["WFS_MASKING"] != ""){ echo '<script>$(function() { $("#'.$BSF_DET["WFS_FIELD_NAME"].'").inputmask({ mask: "'.$BSF_DET["WFS_MASKING"].'"}); });</script>'; } //input mask
	echo '</div>';
	
		
		if($NUM_SCRIPT > 0){ 
		$sql_js = db::query("select * from WF_STEP_JS where WFS_ID = '".$WFS."' order by WFSJ_ID"); 
		?> 
	  <script type="text/javascript">
	  <?php if($BSF_DET["FORM_MAIN_ID"]!="5"){ ?>
		function bsf_change_obj<?php echo $WF_TYPE; ?>_<?php echo $WFS; ?>(vals){
			<?php 
			while($CH = db::fetch_array($sql_js)){
			$WFSJ_VAR = bsf_show_field($W,$WF,$CH["WFSJ_VAR"],$WF_TYPE);
			if($CH["WFSJ_OPERATE"]=='1' OR $CH["WFSJ_OPERATE"]=='2' OR $CH["WFSJ_OPERATE"]=='3' OR $CH["WFSJ_OPERATE"]=='4'){
				?>
				var vals_txt = vals.replace(/,/g , "");
				<?php
			}else{
				$WFSJ_VAR = "'".$WFSJ_VAR."'";
				?>
				var vals_txt = vals; 
				<?php
			}
			?>
			if(vals_txt <?php echo $oper_arr[$CH["WFSJ_OPERATE"]];?> <?php echo $WFSJ_VAR; ?>){ 
			<?php 
			if($CH["WFSJ_SHOW"] != ""){ 
				$e = explode(",",$CH["WFSJ_SHOW"]); 
				foreach($e as $vals){ 
					echo "$(\"[id^=".$vals."_BSF_AREA]\").show();"; 
				} 
			} 
			if($CH["WFSJ_HIDE"] != ""){ 
				$e = explode(",",$CH["WFSJ_HIDE"]); 
				foreach($e as $vals){ 
					echo "$(\"[id^=".$vals."_BSF_AREA]\").hide();"; 
				} 
			} 
			if($CH["WFSJ_JAVASCRIPT"] != ""){ 
				echo bsf_show_text($W,$WF,$CH["WFSJ_JAVASCRIPT"],$WF_TYPE); 
			} 
			?> 
			}
			<?php } ?> 
		}
		<?php }else{ ?> 
		function bsf_chk_obj<?php echo $WF_TYPE; ?>_<?php echo $WFS; ?>(obj){
		<?php 
			while($CH = db::fetch_array($sql_js)){
			$WFSJ_VAR = bsf_show_field($W,$WF,$CH["WFSJ_VAR"],$WF_TYPE);
			if($CH["WFSJ_OPERATE"]=='0' OR $CH["WFSJ_OPERATE"]=='5'){
				?>
			if(obj.value=='<?php echo $WFSJ_VAR; ?>'){
			if(obj.checked==<?php if($CH["WFSJ_OPERATE"]=='0' OR $CH["WFSJ_OPERATE"]==''){ echo 'true'; }else{ echo 'false'; } ?>){
			<?php 
			if($CH["WFSJ_SHOW"] != ""){ 
				$e = explode(",",$CH["WFSJ_SHOW"]); 
				foreach($e as $vals){ 
					echo "$(\"[id^=".$vals."_BSF_AREA]\").show();"; 
				} 
			} 
			if($CH["WFSJ_HIDE"] != ""){ 
				$e = explode(",",$CH["WFSJ_HIDE"]); 
				foreach($e as $vals){ 
					echo "$(\"[id^=".$vals."_BSF_AREA]\").hide();"; 
				} 
			} 
			if($CH["WFSJ_JAVASCRIPT"] != ""){ 
				echo bsf_show_text($W,$WF,$CH["WFSJ_JAVASCRIPT"],$WF_TYPE); 
			} 
			?> 
			}}
			<?php }} ?> 
		}
		<?php } ?>
		</script>
	<?php
		}
	}
	if($tab_flag != '0'){
	echo '</div>';
	}
	if($bsf_script != ''){
		echo "<script>".$bsf_script."</script>";
	}

	if($rec_main['WF_JQUERY_VALIDATE'] == 'Y' && count($BSF_VALIDATE) > 0)
	{
		?>
		<script>
			$(document).ready(function()
			{
				$('#form_wf').validate({
					rules: {
						<?php
						$v=0;
						foreach($BSF_VALIDATE as $_key => $_val)
						{
							$validate_rule .= '"'.$_key.'": "required"';
							if($v < (count($BSF_VALIDATE) - 1))
							{
								$validate_rule .= ',';
							}
							$v++;
						}
						echo $validate_rule;
						?>
					},
					messages: {
						<?php
						$v=0;
						foreach($BSF_VALIDATE as $_key => $_val)
						{
							$validate_message .= '"'.$_key.'": "'.$_val.'"';
							if($v < (count($BSF_VALIDATE) - 1))
							{
								$validate_message .= ',';
							}
							$v++;
						}
						echo $validate_message;
						?>
					}
				});
			});
		</script>

		<?php
	}
}
function convert_order($o){
	//if(mb_strtoupper($o,'UTF-8') == "DESC"){
	if($o == "DESC"){
		return "ASC";
	}else{
		return "DESC";
	}
}
function order_ico($o){
	//if(mb_strtoupper($o,'UTF-8') == "DESC"){
	if($o == "DESC"){
		return 'dt-ordering-desc';
	}else{
		return 'dt-ordering-asc';
	}
}
function bsf_show_size($data){
	if($data > 1024000){ $size = number_format($data/1024000,2)." MB."; }
	elseif($data > 1024){ $size = number_format($data/1024,2)." KB."; }
	elseif($data > 1){ $size = number_format($data)." bytes."; }
	elseif($data >= 0){ $size = number_format($data)." byte."; }
	return $size;
	}
function create_link($link,$source,$replace=array(),$remove=array()){
	$text = '';
	foreach($source as $key => $val){
		if(!in_array($key,$remove)){
			if(in_array($key,$replace)){
				$text .= '&'.$key.'='.$replace[$key];
			}else{
				$text .= '&'.$key.'='.conText($val); 
			}
		}
	}
	return $link.'?'.$text;
}
function gen_ipermission($WHERE,$W,$WF_TYPE,$WF_ARR_STEP=array()){
if($WHERE != ""){
	$WHERE = wf_convert_var($WHERE,'Y');
}
if($WF_TYPE == "W"){  
	foreach($WF_ARR_STEP as $rec_detail){
		if($rec_detail['WFD_PERMISS_VIEW'] != ""){
			if($WHERE != ""){
				$WHERE .= ' OR ';
			}
		$WHERE .= "(WF_DET_NEXT = '".$rec_detail['WFD_ID']."' AND ".wf_convert_var($rec_detail['WFD_PERMISS_VIEW'],'Y').")";
		}
	}	 
}
if($WHERE != ""){
	$WHERE = "(".$WHERE.")";
}
return $WHERE;
}
function gen_permission($WHERE,$W,$WF_TYPE){
if($WHERE != ""){
	$WHERE = wf_convert_var($WHERE,'Y');
}
if($WF_TYPE == "W"){
	$sql_detail = db::query("select WFD_ID,WFD_PERMISS_VIEW from WF_DETAIL where WF_MAIN_ID = '".$W."' AND (WFD_PERMISS_VIEW IS NOT NULL OR WFD_PERMISS_VIEW != '' ) ");
	while($rec_detail = db::fetch_array($sql_detail)){
		if(trim($rec_detail['WFD_PERMISS_VIEW']) != ""){
			if($WHERE != ""){
				$WHERE .= ' OR ';
			}
		$WHERE .= "(WF_DET_NEXT = '".$rec_detail['WFD_ID']."' AND ".wf_convert_var($rec_detail['WFD_PERMISS_VIEW'],'Y').")";
		}
	}
}
if($WHERE != ""){
	$WHERE = "(".$WHERE.")";
}
return $WHERE;
}

function delete_wf_main($wf_main_id)
{
	$a_cond['WF_MAIN_ID'] = $wf_main_id;
	$b_cond['ACCESS_TYPE'] = "WFM";
	$b_cond['ACCESS_REF_ID'] = $wf_main_id;

	db::db_delete('USR_ACCESS', $b_cond);
	//db::db_delete('WF_DETAIL_GROUP', $a_cond);
	db::db_delete('WF_STEP', $a_cond);
	db::db_delete('WF_MAIN', $a_cond);
	db::db_delete('WF_FIELD_GROUP', $a_cond);

}

function delete_wf_detail($wfd_id)
{
	$a_cond['WFD_ID'] = $wfd_id;
	$b_cond['ACCESS_TYPE'] = "DET";
	$b_cond['ACCESS_REF_ID'] = $wfd_id;

	$sql_doc_main = db::query("SELECT * FROM DOC_MAIN WHERE WFD_ID = '".$wfd_id."'");
	while($rec_doc_main = db::fetch_array($sql_doc_main))
	{
		db::db_delete('DOC_VAR', array('DOC_ID' => $rec_doc_main['DOC_ID']));
		db::db_delete('DOC_LABEL', array('DOC_ID' => $rec_doc_main['DOC_ID']));
		db::db_delete('DOC_USER', array('DOC_ID' => $rec_doc_main['DOC_ID']));
	}
	db::db_delete('DOC_MAIN', $a_cond);
	db::db_delete('WF_FIELD_GROUP', $a_cond);
	/* db::db_delete('WF_REQUIREMENT', $a_cond); */
	db::db_delete('WF_STEP_CON', $a_cond);

	$sql_detail = db::query("SELECT WFS_ID FROM WF_STEP_FORM WHERE WFD_ID = '".$wfd_id."'");
	while($rec_detail = db::fetch_array($sql_detail))
	{
		delete_wf_step_form($rec_detail['WFS_ID']);
	}

	db::db_delete('WF_DETAIL', $a_cond);
	db::db_delete('USR_ACCESS', $b_cond);
}

function delete_wf_step_form($wfs_id)
{
	$a_cond['WFS_ID'] = $wfs_id;
	
	db::db_delete('WF_STEP_OPTION', $a_cond);
	db::db_delete('WF_STEP_JS', $a_cond);
	db::db_delete('WF_STEP_THROW', $a_cond);
	db::db_delete('WF_ONCHANGE', $a_cond);
	db::db_delete('WF_STEP_FORM', $a_cond);
}
function wf_convert_user_var($txt,$USR){
if($txt != ""){
	$txt = htmlspecialchars_decode(trim($txt), ENT_QUOTES);
	$txt = str_replace("@@WF_USER_ID!!",$USR["USR_ID"],$txt);
	$txt = str_replace("@@WF_USERNAME!!",$USR["USR_USERNAME"],$txt);
	
	 preg_match_all("/(@@)([a-zA-Z0-9_]+)(!!)/", $txt, $new_sql1, PREG_SET_ORDER); 
	foreach ($new_sql1 as $val_new) 
	{ 
		$txt = str_replace("@@".$val_new[2]."!!",$USR[$val_new[2]],$txt); 
	}
	
	return $txt;
	}	
}
function bsf_alert($W,$WFR,$WFD,$line,$WF_TYPE,$CURRENT){

if($W != "" AND $WFR != '' AND $WFD != '' AND $WFD != '0'){
if($line=="Y"){
$sql_conf1 = db::query("SELECT CONFIG_VALUE FROM WF_CONFIG WHERE CONFIG_NAME = 'wf_line_token_access'");
$rec_conf1 = db::fetch_array($sql_conf1);
	if($rec_conf1['CONFIG_VALUE'] == ""){
		$line = "N";
	}
}
$arr_usr = array();
$usr_con = " 1=2 ";
$sql_user_u = db::query("SELECT USR_REF_ID AS USR_ID FROM USR_ACCESS WHERE (USR_TYPE = 'U' AND ACCESS_TYPE = 'WFM' AND ACCESS_REF_ID = '".$W."') OR (USR_TYPE = 'U' AND ACCESS_TYPE = 'DET' AND ACCESS_REF_ID = '".$WFD."') GROUP BY USR_REF_ID");
while($rec_u = db::fetch_array($sql_user_u)){
	$usr_con .= " OR USR_ID = '".$rec_u['USR_ID']."'";
	array_push($arr_usr,$rec_u['USR_ID']);
}
$sql_user_d = db::query("SELECT USR_REF_ID AS DEP_ID FROM USR_ACCESS WHERE (USR_TYPE = 'D' AND ACCESS_TYPE = 'WFM' AND ACCESS_REF_ID = '".$W."') OR (USR_TYPE = 'D' AND ACCESS_TYPE = 'DET' AND ACCESS_REF_ID = '".$WFD."') GROUP BY USR_REF_ID");
while($rec_d = db::fetch_array($sql_user_d)){
	$usr_con .= " OR DEP_ID = '".$rec_d['DEP_ID']."'";
}
$sql_user_p = db::query("SELECT USR_REF_ID AS POS_ID FROM USR_ACCESS WHERE (USR_TYPE = 'P' AND ACCESS_TYPE = 'WFM' AND ACCESS_REF_ID = '".$W."') OR (USR_TYPE = 'P' AND ACCESS_TYPE = 'DET' AND ACCESS_REF_ID = '".$WFD."') GROUP BY USR_REF_ID");
while($rec_p = db::fetch_array($sql_user_p)){
	$usr_con .= " OR POS_ID = '".$rec_p['POS_ID']."'";
}
$sql_user_g = db::query("SELECT USR_ID FROM USR_ACCESS INNER JOIN USR_GROUP_SETTING ON USR_ACCESS.USR_REF_ID = USR_GROUP_SETTING.GROUP_ID WHERE (USR_TYPE = 'G' AND ACCESS_TYPE = 'WFM' AND ACCESS_REF_ID = '".$W."') OR (USR_TYPE = 'G' AND ACCESS_TYPE = 'DET' AND ACCESS_REF_ID = '".$WFD."') GROUP BY USR_ID");
while($rec_g = db::fetch_array($sql_user_g)){
	if(!in_array($rec_g['USR_ID'], $arr_usr)) {
	$usr_con .= " OR USR_ID = '".$rec_g['USR_ID']."'";
	}
} 
$sql = db::query("select WF_FIELD_PK,WF_MAIN_ID,WF_TYPE,WF_MAIN_TYPE,WF_MAIN_SHORTNAME,WF_PERMISS_VIEW,WF_MAIN_SEARCH,WF_MAIN_SEARCH_SQL,WF_R_SQL from WF_MAIN where WF_MAIN_ID = '".$W."'");
	$rec_main = db::fetch_array($sql);
	$wf_table = $rec_main["WF_MAIN_SHORTNAME"];
	$pk_name = $rec_main["WF_FIELD_PK"];
		
	$cond = "";
	if($rec_main["WF_PERMISS_VIEW"] != ''){ 
	$cond .= " AND ( ".$rec_main["WF_PERMISS_VIEW"]." )";
	} 
	if($rec_main["WF_PERMISS_ACTION"] != ''){ 
	$cond .= " AND ( ".$rec_main["WF_PERMISS_ACTION"]." )";
	}
$sql_detail = db::query("SELECT WFD_PERMISS_VIEW,WFD_PERMISS_ACTION FROM WF_DETAIL WHERE WFD_ID = '".$WFD."'");
$rec_detail = db::fetch_array($sql_detail);
	if($rec_detail["WFD_PERMISS_VIEW"] != ''){ 
	$cond .= " AND ( ".$rec_detail["WFD_PERMISS_VIEW"]." )";
	}
	if($rec_detail["WFD_PERMISS_ACTION"] != ''){ 
	$cond .= " AND ( ".$rec_detail["WFD_PERMISS_ACTION"]." )";
	}
	
	if($rec_main["WF_MAIN_SEARCH"] == '2' AND $rec_main["WF_MAIN_SEARCH_SQL"] != ''){
		$sql_form = $rec_main["WF_MAIN_SEARCH_SQL"];
		$sql_form = str_replace("#SEARCH#",$cond." AND ".$wf_table.".".$pk_name." = '".$WFR."'",$sql_form);
	}else{
		if($rec_main["WF_MAIN_SEARCH"] == '1' AND $rec_main["WF_R_SQL"] != ''){
			$cond .= " AND ".$rec_main["WF_R_SQL"];
		}
		$sql_form = "SELECT ".$pk_name." FROM ".$wf_table." WHERE ".$wf_table.".".$pk_name." = '".$WFR."' AND (WF_DET_NEXT IS NOT NULL OR WF_DET_NEXT != '')  AND WF_DET_NEXT > 0 ".$cond;
	}
		

 $sql_check1 = db::query("SELECT * FROM usr_main WHERE (USR_ID != '".$_SESSION["WF_USER_ID"]."' AND USR_STATUS = 'Y') AND (".$usr_con.")");
 
 while($U = db::fetch_array($sql_check1)){
	 $use_line = $line;
	 if($U['USR_LINE_API_KEY'] == "" AND $U['USR_EMAIL'] == ""){
		$use_line = "N";  
	 }
		$sql_form_gen = wf_convert_user_var($sql_form,$U); 
		$sql_wfr_form = db::query($sql_form_gen);
		$rows = db::num_rows($sql_wfr_form); 
			if($rows > 0){
			$insert_wf = array();
			$insert_wf['A_SEND_USER'] = $_SESSION['WF_USER_ID'];
			$insert_wf['WF_TYPE'] = $WF_TYPE;
			$insert_wf['WF_MAIN_ID'] = $W;
			$insert_wf['WFD_ID'] = $WFD;
			$insert_wf['WFR_ID'] = $WFR;
			$insert_wf['A_SEND_DATE'] = date2db(date("d/m/").(date("Y")+543));
			$insert_wf['A_SEND_TIME'] = date("H:i:s");
			$insert_wf['A_REC_USER'] = $U["USR_ID"];
			$insert_wf['A_STATUS'] = "Y";
			$insert_wf['A_LINE_USE'] = $use_line;
			$insert_wf['A_LINE_SEND'] = "N";
			$insert_wf['A_LAST_STEP'] = $CURRENT; 
			db::db_insert("WF_ALERT", $insert_wf, "A_ID");
			unset($insert_wf);
			}  
	}
}
}
function bsf_check_orderby($sql){
	$sql = strtoupper($sql);
	$sql = str_replace("\n",' ',$sql);
	$sql = str_replace("\r",' ',$sql);
	$sql = str_replace("\t",' ',$sql);
	$sql = str_replace("  "," ",$sql);
	$sql = str_replace("  "," ",$sql);
	$last_sql = '';
	$has_order = "";
	if(str_contains($sql, 'ORDER BY')){
		if(str_contains($sql, 'WHERE')){
			$wh = explode('WHERE',$sql);
			$last_sql = end($wh);
			if(str_contains($last_sql, 'ORDER BY')){
				$has_order = "Y";
			}else{
				$has_order = "N";
			}
		}else{
			$has_order = "Y";
		}
		
	}else{
		$has_order = "N";
	}
	return $has_order;
}


/**
 * Send a line notification.
 * @param int $usrId USR_MAIN.USR_ID
 * @param array $detail รายละเอียดของการแจ้งเตือน :
 *               - 'txt_preview' : ข้อความที่แสดงตัวอย่าง (ถ้าไม่มีจะแสดง txt_message).
 *               - 'txt_title' : ชื่อการแจ้งเตือน เช่น ระบบBizSmartFlow (ถ้าไม่มีจะไม่แสดง).
 *               - 'txt_from' : ผู้ส่ง เช่น จากนายAdmin Biz (ถ้าไม่มีจะไม่แสดง).
 *               - 'txt_type' : ประเภทของข้อความ เช่น ประชุม (ภายใน) (ถ้าไม่มีจะไม่แสดง).
 *               - 'txt_message' : รายละเอียดการแจ้งเตือน .
 *               - 'txt_btn' : ข้อความที่ปุ่ม เช่น ดูรายละเอียด (ถ้าไม่มีจะ Default ดูรายละเอียด).
 *               - 'url_btn' : URL ที่ต้องการ redirect หลังกดปุ่ม (ถ้าไม่มีจะไม่แสดง).
 */function bsf_send_line(int $usrId, array $detail = [])
 {
	global $system_conf;
	
    if (!empty($system_conf['wf_line_token_access']) && !empty($usrId) && !empty($detail)) {
        $qUsr = db::query("SELECT USR_ID,USR_LINE_NOTI_ID FROM USR_MAIN WHERE USR_ID = '{$usrId}' AND USR_STATUS = 'Y'");
        $rUsr = db::fetch_array($qUsr);
        $lineNotiId = $rUsr['USR_LINE_NOTI_ID'];

        if (!empty($lineNotiId)) {

            $url = "https://api.line.me/v2/bot/message/push";

            $arrData = ["to" => $lineNotiId, "messages" => [["type" => "flex", "altText" => !empty($detail['txt_preview']) ? $detail['txt_preview'] : $detail['txt_message'], "contents" => ["type" => "bubble", "body" => ["type" => "box", "layout" => "vertical", "contents" => [["type" => "text", "text" => "แจ้งเตือน", "weight" => "bold", "color" => "#1DB446", "size" => "sm", "align" => "center", "margin" => "none"], ["type" => "separator", "margin" => "sm"], ["type" => "box", "layout" => "baseline", "contents" => [["type" => "text", "text" => !empty($detail['txt_from']) ? $detail['txt_from'] : " ", "color" => "#aaaaaa", "wrap" => false, "size" => "xs", "align" => "start", "decoration" => "none", "margin" => "none"], ["type" => "text", "text" => !empty($detail['txt_type']) ? $detail['txt_type'] : " ", "align" => "end", "size" => "xs", "color" => "#EF752F", "wrap" => true]], "margin" => "md", "spacing" => "none", "borderWidth" => "none", "cornerRadius" => "none"], ["type" => "text", "text" => !empty($detail['txt_message']) ? $detail['txt_message'] : " ", "margin" => "lg", "wrap" => true, "size" => "sm", "weight" => "regular", "style" => "normal", "decoration" => "none"], ["type" => "separator", "margin" => "xxl"]]]]]]];

            if (!empty($detail['txt_title'])) {
                $title = ["type" => "text", "text" => !empty($detail['txt_title']) ? $detail['txt_title'] : " ", "weight" => "bold", "size" => "md", "margin" => "sm","wrap"=>true];
                array_splice($arrData["messages"][0]["contents"]["body"]["contents"], 1, 0, [$title]);
            }
            if (!empty($detail['url_btn'])) {
                $button = ["type" => "button", "action" => ["type" => "uri", "label" => !empty($detail['txt_btn']) ? $detail['txt_btn'] : "ดูรายละเอียด", "uri" => $detail['url_btn']], "style" => "primary", "gravity" => "center", "height" => "sm", "margin" => "lg"];
                $arrData["messages"][0]["contents"]["body"]["contents"][] = $button;
            }
            $curl = curl_init();
            curl_setopt_array($curl, [
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => json_encode($arrData),
                CURLOPT_HTTPHEADER => [
                    "Content-Type: application/json",
                    "Authorization: Bearer {".$system_conf['wf_line_token_access']."}"
                ],
            ]);

            $response = curl_exec($curl);
            $curl_error = curl_error($curl);
            $http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            curl_close($curl);
            $response = json_decode($response, true);

            $pathLog =  create_ifolder("../bizsmartnoti/log_send/@year@month/", "");
            if (is_dir($pathLog) && is_writable($pathLog)) {
                $textwrite = "[" . $_SESSION['WF_USER_ID'] . "][" . date("H:i:s") . "][" . $rUsr['USR_ID'] . "][" . $lineNotiId . "][" . json_encode($detail) . "][" . $http_code . "][".json_encode($response)."]";
                $filePath = $pathLog . "/" . date("Ymd") . '.txt';
                file_put_contents($filePath, $textwrite . PHP_EOL, FILE_APPEND);
            }
        }
    }
}

?>