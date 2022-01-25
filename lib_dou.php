<?php // $Id: lib_culture.php,v 1.21 2012/04/06 07:16:29 shtifanov Exp $


function listbox_status_dou($scriptname, $statusid)
{
    global $CFG;
    

    $menu = array();
    $menu[0] = 'Выберите статус ...';
    $menu[2] = 'В работе';
    $menu[3] = 'Доработать';
    $menu[4] = 'На согласовании';
    
    echo '<tr><td>Статус:</td><td>';
    popup_form($scriptname, $menu, 'switchstatus', $statusid, '', '', '', false);
    echo '</td></tr>';
    return 1;
}


function listbox_razdel_dou($scriptname, $rzid)
{
    global $CFG;
    
    $menu = get_records_select_menu('monit_razdel', "shortname in ('rating_dou_op', 'rating_dou_ku', 'rating_dou_ed')", 'id', 'id, name');
    $menu[-1] = 'ВСЕ ТАБЛИЦЫ';
    $menu[0] = 'Выберите кластер ...';
    

    echo '<tr><td>Таблица данных:</td><td>';
    popup_form($scriptname, $menu, 'switchtbldata', $rzid, '', '', '', false);
    echo '</td></tr>';
    return 1;
}




function listbox_cluster_dou($scriptname, $kid)
{
    global $CFG;
    
    $menu = get_records_menu('monit_rating_dou_cluster');
    $menu[-1] = 'Все ДОУ';
    $menu[0] = 'Выберите кластер ...';
    
    /*
    $menu = array();
    $menu[1] = '1..3-групповые (малокомплектные) ДОУ';
    $menu[2] = '4..6-групповые ДОУ';
    $menu[3] = '7 групп и более в ДОУ';
    $menu[4] = 'Негосударственные ДОО: 1-2 группы';
    $menu[5] = 'Негосударственные ДОО: 3 и более групп';
    */
    
    echo '<tr><td>Кластер:</td><td>';
    popup_form($scriptname, $menu, 'switchklaster', $kid, '', '', '', false);
    echo '</td></tr>';
    return 1;
}


// Print tabs years with auto generation link to dou
function print_tabs_years_rating_dou($link, $rid, $oid, $yid, $outype)
{
	$toprow1 = array();
    $ouids = array();
    
	$uniqueconstcode = 0;
   	if ($rid != 0 && $oid != 0)	{
   		if ($ou = get_record_select($outype->tblname, "rayonid = $rid AND id = $oid AND yearid = $yid", 'id, uniqueconstcode'))		{
			$uniqueconstcode = $ou->uniqueconstcode;   			
   		}
   	} 

    if ($years = get_records_select('monit_years', 'id>=7', '', 'id, name'))  {
    	foreach ($years as $year)	{
    		$fulllink = $link . "&typeou={$outype->cod}&rid=$rid&oid=$oid&yid=" . $year->id;
	    	if ($uniqueconstcode != 0)	{
				if ($ou = get_record_select($outype->tblname, "uniqueconstcode=$uniqueconstcode AND yearid = {$year->id}", 'id, rayonid'))	{
					$fulllink = $link . "&typeou={$outype->cod}&rid={$ou->rayonid}&oid={$ou->id}&yid={$year->id}";
                    $ouids[$year->id] = $ou->id;
				}	
	    	}
            
  			$ayears = explode("/", $year->name);
   			$toprow1[] = new tabobject($year->id, $fulllink, get_string('civilyear', 'block_monitoring', $ayears[0]));    			
	    }
  	}
    $tabs1 = array($toprow1);

   //  print_heading(get_string('terms','block_dean'), 'center', 4);
   
	print_tabs($tabs1, $yid, NULL, NULL);
    
    return $ouids;
}


// $formula = func_fku_3#fku_3
// $indicator = null
function func_fku_3($formula, $indicator, &$arr_df, $ordering = 0)	
{
	global $itogmark;
	
	$itogmark = 0;

    $o1 = 'fku_3';

	$color = 'red';
	$strmark = '-';
    

	if (!empty($arr_df[$o1]))	{
	    if ($arr_df[$o1] == 100)   {
	       $itogmark = 2;
	    } else if ($arr_df[$o1] >= 70 && $arr_df[$o1] <= 99)   {
	        $itogmark = 1;
        }  

        $color = 'green';// get_string('status7color', 'block_monitoring');
                	
        $strmark = "<b><font color=\"$color\">$itogmark</font></b>";
        $strmark .= "<br><small>если ($arr_df[$o1] = 100) , то 2</small>";
        $strmark .= "<br><small>если (70 <= $arr_df[$o1] <= 99), то 1</small>";
        $strmark .= "<br><small>иначе 0</small>";

	}
	return 	$strmark;
}


// $formula = func_fku_21#fku_21
// $indicator = null
function func_fku_21($formula, $indicator, &$arr_df, $ordering = 0)
{
    global $itogmark;

    $itogmark = 0;

    $o1 = 'fku_21';

    $color = 'red';
    $strmark = '-';


    if (!empty($arr_df[$o1]))	{
        if ($arr_df[$o1] >= 90)   {
            $itogmark = 2;
        } else if ($arr_df[$o1] >= 70 && $arr_df[$o1] <= 89)   {
            $itogmark = 1;
        }

        $color = 'green';// get_string('status7color', 'block_monitoring');

        $strmark = "<b><font color=\"$color\">$itogmark</font></b>";
        $strmark .= "<br><small>если ($arr_df[$o1] >= 90) , то 2</small>";
        $strmark .= "<br><small>если (70 <= $arr_df[$o1] <= 89), то 1</small>";
        $strmark .= "<br><small>иначе 0</small>";
    }
    return 	$strmark;
}

// $formula = func_P13#(fku_12/fku_8/12)/avgzpregion*100%
function func_P13($formula, $indicator, &$arr_df, $ordering = 0)	
{
	global $REGIONCRITERIA, $itogmark;
	
    // print_object($REGIONCRITERIA);
	$itogmark = 0;

    $o1 = 'fku_12';
    $o2 = 'fku_8';
    
   	$color = 'red';
	$strmark = '-';
    
   	if (!empty($arr_df[$o1]) && !empty($arr_df[$o2])  && !empty($REGIONCRITERIA->avgzpregion))	{
		$drob =  ((double)$arr_df[$o1]/(double)$arr_df[$o2]/12.0)/$REGIONCRITERIA->avgzpregion;
        $rez_proc =  $drob*100.0;
        $itogmark = round ($drob, 4);

        $color = 'green';// get_string('status7color', 'block_monitoring');
                
		$dolja = number_format($rez_proc, 2, ',', '');
		$dolja .= '%';
	
		$strmark = "<b><font color=\"$color\">$itogmark</font></b>";
		$strmark .= "<br><small>($arr_df[$o1]/$arr_df[$o2]/12/{$REGIONCRITERIA->avgzpregion}=$dolja)</small>";		
		
	}
	return 	$strmark;
}


// $formula = func_P14#fku_15
// $indicator = null
function func_P14($formula, $indicator, &$arr_df, $ordering = 0)	
{
	global $itogmark;
	
	$itogmark = 0;

    $o1 = 'fku_15';

	$color = 'red';
	$strmark = '-';
    

	if (!empty($arr_df[$o1]))	{
	    if ($arr_df[$o1] > 1)   {
	       $itogmark = 2;
	    } else if ($arr_df[$o1] == 1)   {
	        $itogmark = 1;
        }  

        $color = 'green';// get_string('status7color', 'block_monitoring');
                	
		$strmark = "<b><font color=\"$color\">$itogmark</font></b>";		
		
	}
	return 	$strmark;
}


// $formula = func_P15#fku_15
// $indicator = null
function func_P15($formula, $indicator, &$arr_df, $ordering = 0)	
{
	global $itogmark;
	
	$itogmark = 0;

    $o1 = 'fku_16';

	$color = 'red';
	$strmark = '-';
    

	if (!empty($arr_df[$o1]))	{
	    if ($arr_df[$o1] > 2)   {
	       $itogmark = 2;
	    } else if ($arr_df[$o1] == 1)   {
	        $itogmark = 1;
        }  

        $color = 'green';// get_string('status7color', 'block_monitoring');
                	
		$strmark = "<b><font color=\"$color\">$itogmark</font></b>";		
		
	}
	return 	$strmark;
}


// $formula = func_P17#fku_18
// $indicator = null
function func_P17($formula, $indicator, &$arr_df, $ordering = 0)	
{
	global $itogmark;
	
	$itogmark = 0;

    $o1 = 'fku_18';

	$color = 'red';
	$strmark = '-';
    
    /*
	if (!empty($arr_df[$o1]))	{
	    if ($arr_df[$o1] >= 3)   {
	       $itogmark = 2;
	    } else if ($arr_df[$o1] >= 1)   {
	        $itogmark = 1;
        }  

        $color = 'green';// get_string('status7color', 'block_monitoring');
                	
		$strmark = "<b><font color=\"$color\">$itogmark</font></b>";		
		
	}
    */
    $color = 'green';
    $itogmark = $arr_df[$o1];
    $strmark = "<b><font color=\"$color\">$itogmark</font></b>";
    
	return 	$strmark;
}

// $formula = func_P18#fku_19
// $indicator = null
function func_P18($formula, $indicator, &$arr_df, $ordering = 0)	
{
	global $itogmark;
	
	$itogmark = 0;

    $o1 = 'fku_19';

	$color = 'red';
	$strmark = '-';
    
    /*
	if (!empty($arr_df[$o1]))	{
	    if ($arr_df[$o1] > 3)   {
	       $itogmark = 2;
	    } else if ($arr_df[$o1] == 3)   {
	        $itogmark = 1;
        }  

        $color = 'green';// get_string('status7color', 'block_monitoring');
                	
		$strmark = "<b><font color=\"$color\">$itogmark</font></b>";		
		
	}
    */
    
    $color = 'green';
    $itogmark = $arr_df[$o1];
    $strmark = "<b><font color=\"$color\">$itogmark</font></b>";
    
	return 	$strmark;
}


//  $formula  this is for example: fku_21
//  func_one_proc#fku_21
function func_one_proc($formula, $indicator, &$arr_df, $ordering = 0)	
{
	global  $itogmark;

	$itogmark = 0;	
	if (empty($arr_df[$formula])) return '-';

	$curr_indicator = $arr_df[$formula]; // example value  $arr_df[f_r1_07] 

    $itogmark = $curr_indicator / 100.0;
    $color = 'green';
	$strmark = "<b><font color=\"$color\">$itogmark</font></b>" ;

	return 	$strmark;
 
}

// $formula =  func_if#fku_31=fku_32
function func_if($formula, $indicator, &$arr_df, $ordering = 0)	
{
	global $itogmark;
	
	$itogmark = 0;
	
    // print_object($arr_df);
	// $operands = explode('=', $formula);
	// $o1 = $operands[0];
	/// $o2 = $operands[1];

    switch ($formula)   {
        case 'algorithm 30 (fku_31, fku_32)': $o1 = 'fku_31'; $o2 = 'fku_32';
        break; 
        case 'algorithm 31 (fku_33, fku_35)': $o1 = 'fku_33'; $o2 = 'fku_35';
        break; 
        case 'algorithm 32 (fku_34, fku_36)': $o1 = 'fku_34'; $o2 = 'fku_36';
        break; 
    }

	$color = 'red';
	$strmark = '-';
    
    // if (!empty($arr_df[$o1]) && !empty($arr_df[$o2]))	{
        if ($arr_df[$o1] == 0 && $arr_df[$o2] == 0)  {
            $itogmark = 2;
        } else if ($arr_df[$o1] >= 1 && $arr_df[$o2] == 0)  {
            $itogmark = 1;
        } else if ($arr_df[$o2] >= 1)  {
            $itogmark = 1;
        } 

        $color = 'green';
	
		$strmark = "<b><font color=\"$color\">$itogmark</font></b>";
        // $strmark .= "<br><small>если {$arr_df[$o1]} = 0, то 2; <br />если {$arr_df[$o2]} >= 1 - 1; <br />иначе 0</small>";
	// } else {
	   // $strmark .= "<br><small>если ? = 0, то 2; если ? >= 1, то 1; если ? > 0, то - 0 </small>";
	// }
	// print $strmark . '<br />';
	return 	$strmark;
}

// $formula = func_P13#(fku_12/fku_8/12)/avgzpregion*100%
function func_P33($formula, $indicator, &$arr_df, $ordering = 0)	
{
	global $itogmark;
	
	$itogmark = 0;

    $o1 = 'fed_1';
    $o2 = 'fed_2';
    $o3 = 'fed_3';
    
   	$color = 'red';
	$strmark = '-';
    
   	if (!empty($arr_df[$o1]) && !empty($arr_df[$o2])  && !empty($arr_df[$o3]))	{
		$drob =  (double)$arr_df[$o1]/(double)$arr_df[$o2]/$arr_df[$o3];
        $rez_proc =  $drob*100.0;
        // $itogmark = round ($drob, 4);
        $itogmark = round ($rez_proc, 4);

        $color = 'green';// get_string('status7color', 'block_monitoring');
                
		$dolja = number_format($rez_proc, 2, ',', '');
		$dolja .= '%';
	
		$strmark = "<b><font color=\"$color\">$itogmark</font></b>";
		$strmark .= "<br><small>($arr_df[$o1]/$arr_df[$o2]/$arr_df[$o3]/=$dolja)</small>";		
		
	}
	return 	$strmark;
}

// $formula = func_P13#(fku_12/fku_8/12)/avgzpregion*100%
function func_P34($formula, $indicator, &$arr_df, $ordering = 0)	
{
	global $REGIONCRITERIA, $itogmark;
	
	$itogmark = 0;

    $o1 = 'fed_4';
    $o2 = 'fed_2';
    
   	$color = 'red';
	$strmark = '-';
    
   	if (!empty($arr_df[$o1]) && !empty($arr_df[$o2]))	{ // && !empty($REGIONCRITERIA->healthlevel
		$drob =  (double)$arr_df[$o1]/(double)$arr_df[$o2];
        $P34 = round ($drob, 2);
        if ($P34 < $REGIONCRITERIA->healthlevel)    {
            $itogmark = 2;
        } else if ($P34 == $REGIONCRITERIA->healthlevel) {
            $itogmark = 1;
        }
        $color = 'green';// get_string('status7color', 'block_monitoring');
		$strmark = "<b><font color=\"$color\">$itogmark</font></b>";
        $strmark .= "<br><small>если ($arr_df[$o1]/$arr_df[$o2]) < $REGIONCRITERIA->healthlevel, то 2</small>";			
        $strmark .= "<br><small>если ($arr_df[$o1]/$arr_df[$o2]) = $REGIONCRITERIA->healthlevel, то 1</small>";
        $strmark .= "<br><small>иначе 0</small>";
	}
	return 	$strmark;
}


// $formula = func_P35#fed_6
// $indicator = null
function func_P35($formula, $indicator, &$arr_df, $ordering = 0)	
{
	global $itogmark;
	
	$itogmark = 0;

    $o1 = 'fed_6';

	$color = 'red';
	$strmark = '-';
    

	if (!empty($arr_df[$o1]))	{
	    if ($arr_df[$o1] > 90)   {
	       $itogmark = 2;
	    } else if ($arr_df[$o1] > 80 && $arr_df[$o1] <= 90)   {
	        $itogmark = 1;
        }  

        $color = 'green';// get_string('status7color', 'block_monitoring');
                	
		$strmark = "<b><font color=\"$color\">$itogmark</font></b>";
        $strmark .= "<br><small>если ($arr_df[$o1] > 90) , то 2</small>";			
        $strmark .= "<br><small>если (80 < $arr_df[$o1] <= 90), то 1</small>";
        $strmark .= "<br><small>иначе 0</small>";
		
		
	}
	return 	$strmark;
}


function func_P12($formula, $indicator, &$arr_df, $ordering = 0)	
{
	global $itogmark;
	
	$itogmark = 0;
    
    // echo $formula . '<hr>';
    // print_object($arr_df);

	$operands = explode('/', $formula);
	$o1 = $operands[0]; // fk_25
	$o2 = $operands[1]; // fk_07    

	$strmark = '-';
	if (!empty($arr_df[$o1]) && !empty($arr_df[$o2]))	{
	    $itogmark = round ($arr_df[$o1]/$arr_df[$o2]/10.0, 4);

     	$color = 'green';
    	$strmark = "<b><font color=\"$color\">$itogmark</font></b>";
        $strmark .= "<br><small>($arr_df[$o1]/$arr_df[$o2]/10 = $itogmark)</small>";
	}
    	
	return 	$strmark;
}


// $formula = func_div_mul_100#fku_12/fku_8
function func_div_mul_100($formula, $indicator, &$arr_df, $ordering = 0)	
{
	global $itogmark;
	
	$itogmark = 0;
    
	$operands = explode('/', $formula);
	$o1 = $operands[0];
	$formula2 = $operands[1];
	$operands2 = explode('*', $formula2);
	$o2 = $operands2[0];
     
	$strmark = '-';
	if (!empty($arr_df[$o1]) && !empty($arr_df[$o2]))	{
	   $itogmark = round ($arr_df[$o1]/$arr_df[$o2]*100, 4);

     	$color = 'green';
    	$strmark = "<b><font color=\"$color\">$itogmark</font></b>";
        $strmark .= "<br><small>($arr_df[$o1]/$arr_df[$o2]*100 = $itogmark)</small>";
	}
    	
	return 	$strmark;
}


function calculate_dou_mark($yid, $rid, $oid, $id, $shortname, $exclude='', $outype)
{
	global $db, $CFG;

   if ($exclude == '') {
        $table = 'monit_rating_dou';
    } else {
        $table = 'monit_rating_dou_ex';
    }	 
 	// echo $yid . '<hr>';
 
	$arr_df = array();
    $sql = "SELECT * FROM {$CFG->prefix}monit_form_$shortname WHERE id=$id";
    // echo  $sql . '<br />';
	if ($df = get_record_sql($sql))	{
		$arr_df = (array)$df;
		// print_r($arr_df); echo '<hr>';   	
	}
    //print_object($arr_df); echo '<hr>';
   	switch ($shortname)	{
		case 'rating_dou_op': $gradelevel = 20;
		break;
		case 'rating_dou_ku': $gradelevel = 21;
		break;
		case 'rating_dou_ed': $gradelevel = 22;
		break;
    }
    
    $totalmark = 0;
    // init_rating_parameters($yid, $shortname, $select, $order);
    $shortname = optional_param('sn', 'rating_dou');    
    $select = "yearid = $yid AND gradelevel = $gradelevel";
    $select .=  " AND edizm <> 'null'";    
    $order = 'number'; 

    $strsql = "SELECT id, number, formula, edizm, indicator, ordering FROM {$CFG->prefix}monit_rating_criteria
    		   WHERE $select $exclude
			   ORDER BY $order";
    // echo $strsql .' <br />';           
	if ($criterias = get_records_sql($strsql)) 	{
	    // print_object ($criterias);
		
		$criteriaids = array();
   		foreach($criterias as $criteria)	{
   			$criteriaids[] = $criteria->id;
	  	}
   		$criterialist = implode(',', $criteriaids);

		$strsql = "UPDATE {$CFG->prefix}{$table} mark=0 WHERE (yearid=$yid) AND ({$outype->idfieldname}=$oid) AND (criteriaid in ($criterialist))";
		$db->Execute($strsql);
    		
		// delete_records('monit_rating_school', 'schoolid', $sid);
		// set_field('monit_rating_school', 'mark', 0, 'schoolid', $sid);
		// print_object($criterias); exit();
        
        $totalmark = calculating_rating_dou($yid, $rid, $oid, $shortname, $arr_df, $criterias, $exclude, $outype);
	}

    // update_rating_total($yid, $rid, $sid, $shortname, $totalmark);
	
	return $totalmark;
}



function calculating_rating_dou($yid, $rid, $oid, $shortname, $arr_df, $criterias, $exclude, $outype)
{
    global $db, $CFG, $itogmark;
    
    if ($exclude == '') {
        $table = 'monit_rating_dou';
    } else {
        $table = 'monit_rating_dou_ex';
    }
    
	$totalmark = 0;    
    // print_object($criterias);
	foreach($criterias as $criteria)	{
		$itogmark = 0;
		if ($criteria->formula == 'null')	continue;
		$operands = explode('#', $criteria->formula);
		$o1 = trim($operands[0]);
		$o2 = trim($operands[1]);
       	if (function_exists($o1))   {
			if (!empty($arr_df))	{
                if (function_exists($o1)) {
               		$namefunc = $o1;
               		$strmark = $namefunc($o2, $criteria->indicator, $arr_df, $criteria->ordering);
				} 
			} else {
				$strmark = '-';
			}	
            /*
            if ($criteria->ordering == 1)   {
                $itogmark *= -1;    
            }
            */
            
       		$totalmark += $itogmark;
            // echo "$totalsum += $itogmark;<br>" . $strmark . '<br>';
            // echo "$totalsum<hr>";
            // if ($criteria->id == 411) {
            // echo $namefunc . ' = ' . $criteria->id . ' = ' . $itogmark . '<hr>';
            // }     
			if ($markschool = get_record_sql("SELECT id, mark 
											  FROM {$CFG->prefix}{$table}
			 								  WHERE yearid=$yid AND {$outype->idfieldname}=$oid AND criteriaid=$criteria->id")) {
			 	set_field('monit_rating_dou', 'mark', $itogmark, 'id', $markschool->id);							  	
		   } else {
		        $markschool = new stdClass();
		   		$markschool->yearid = $yid;
		   		$markschool->rayonid = $rid;
		        $markschool->{$outype->idfieldname} = $oid;
				$markschool->ratingcategoryid = 1;
				$markschool->criteriaid = $criteria->id;
				$markschool->mark = $itogmark;
				$markschool->rationum = 0;
                // print_object($markschool);
                
				if (!insert_record($table, $markschool))	{
					error('Not insert rating school.', "listforms.php?rid=$rid&amp;yid=$yid&amp;oid=$oid");
				}
                
		   }      
		}  else {
		    notify ("Function $o1 not found.");
		}
	} // foreach criterias	   
    return 	$totalmark;
}
?>