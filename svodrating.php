<?php // $Id: summaryrating.php,v 1.26 2013/02/25 06:17:19 shtifanov Exp $

    require_once("../../config.php");
    require_once('../monitoring/lib.php');
    require_once('../mou_att2/lib_att2.php');
    require_once('../monitoring/rating/lib_rating.php');
    require_once('../mou_ege/lib_ege.php');
    require_once('lib_dou.php');

    $rid = required_param('rid', PARAM_INT);            // Rayon id
    $oid = optional_param('oid', 0, PARAM_INT);            // School id
    $yid = optional_param('yid', 0, PARAM_INT);       		// Year id
    $kid = optional_param('kid', 0, PARAM_INT);       		// Kluster id    
    // $nm  = optional_param('nm', 9, PARAM_INT);  // Month number
    $criteriaid = optional_param('cid', 0);       // Shortname form
    $level = optional_param('level', 'ou');       // Form id
    $report = optional_param('r', 'rA');       //  Report
    $shortname = optional_param('sn', 'rating_dou');
	$action   = optional_param('action', '');
    $criteriaid = optional_param('cid', 0);        
    $nm = 9;

    if ($yid == 0)	{
    	$yid = get_current_edu_year_id();
    }

    $scriptname = 'svodrating.php';

    get_edit_capability_region_rayon($rid, $edit_capability_region, $edit_capability_rayon);
    
	$strlistrayons =  listbox_rayons_att("$scriptname?oid=0&amp;yid=$yid&amp;rid=", $rid);
	$strlisttypeou =  listbox_typeou_att("$scriptname?rid=$rid&amp;yid=$yid&amp;oid=0&amp;typeou=", $rid, $typeou, false, true);

	if (!$strlistrayons && !$strlisttypeou)   { 
		error(get_string('permission', 'block_mou_school'), '../index.php');
	}	

    if ($action == 'excel') 	{
        switch($level) {
            case 'ou':  $table = table_svodrating($rid, $oid, $yid, $nm, $shortname, $select, $order, $kid, $report);
            break;
                       
            case 'rayon':
            break;    
            
            case 'region':
                        $select = "yearid = $yid AND gradelevel in (20, 21, 22)";
                        $select .=  " AND edizm <> 'null'";
                        $order = 'id';

                        if ($report == 'rC')    {
                      		$table = table_ratingregion_criteriaid($rid, $oid, $yid, $nm, $shortname, $select, $criteriaid, $kid);
                        } else {    
                            $table = table_svodrating(-1, $oid, $yid, $nm, $shortname, $select, $order, $kid, $report);
                        }        
            break;                  
        }

  		print_table_to_excel($table);
        exit();
	}


	$strtitle = get_string('title','block_mou_dou');
    $strscript = get_string('svodrating', 'block_mou_dou');

    $navlinks = array();
    $navlinks[] = array('name' => $strtitle, 'link' => "$CFG->wwwroot/blocks/mou_dou/index.php?rid=$rid&amp;yid=$yid", 'type' => 'misc');
    $navlinks[] = array('name' => $strscript, 'link' => null, 'type' => 'misc');
    $navigation = build_navigation($navlinks);
    print_header($SITE->shortname . ': '. $strtitle, $SITE->fullname, $navigation, "", "", true, "&nbsp;"); // , navmenu($course)

    print_heading('Сводный рейтинг дошкольных образовательных учреждений', 'center');
    
    $outype = get_constants_ou(20, $CONTEXT_OU, $tablename, $strtitle, $strselect, $where);
    print_tabs_years_rating_dou("$scriptname?nm=$nm&sn=$shortname&cid=$criteriaid", $rid, $oid, $yid, $outype);  
    
    $toprow2  = array();
    $toprow2[] = new tabobject('ou', "$scriptname?level=ou&rid=$rid&oid=$oid&nm=$nm&yid=$yid&cid=$criteriaid", 'ДОУ района');
    // $toprow2[] = new tabobject('rayon',  "$scriptname?level=rayon&rid=$rid&nm=$nm&yid=$yid&cid=$criteriaid", 'Муниципалитетов');
    $toprow2[] = new tabobject('region', "$scriptname?level=region&rid=$rid&nm=$nm&yid=$yid&cid=$criteriaid", 'Областной');    
    $tabs2 = array($toprow2);
    print_tabs($tabs2, $level, NULL, NULL);
    
    $toprow3  = array();
    $toprow3[] = new tabobject('rA', "$scriptname?r=rA&level=$level&rid=$rid&oid=$oid&nm=$nm&yid=$yid&cid=$criteriaid&kid=$kid", 'Рейтинг А');
    $toprow3[] = new tabobject('rB', "$scriptname?r=rB&level=$level&rid=$rid&oid=$oid&nm=$nm&yid=$yid&cid=$criteriaid&kid=$kid", 'Рейтинг Б');
    if ($level == 'region') {    
        $toprow3[] = new tabobject('rC', "$scriptname?r=rC&level=$level&rid=$rid&oid=$oid&nm=$nm&yid=$yid&cid=$criteriaid&kid=$kid", 'Рейтинг C');
    }    
    $tabs3 = array($toprow3);
    print_tabs($tabs3, $report, NULL, NULL);
 
    $select = "yearid = $yid AND gradelevel in (20, 21, 22)";
    $select .=  " AND edizm <> 'null'";
    $order = 'id'; 
    
    $link = "$scriptname?r=$report&level=$level&rid=$rid&oid=$oid&nm=$nm&yid=$yid&kid=";
    switch($level) {
        case 'ou':  
       	            echo '<table cellspacing="0" cellpadding="10" align="center" class="generaltable generalbox">';
                	echo $strlistrayons;
                    listbox_cluster_dou($link, $kid);
                	echo '</table>';
                    
                    if ($kid != 0)   {
                        $table = table_svodrating($rid, $oid, $yid, $nm, $shortname, $select, $order, $kid, $report);
                    }    
        break;
                   
        case 'rayon':
        break;    
        
        case 'region':
       	            echo '<table cellspacing="0" cellpadding="10" align="center" class="generaltable generalbox">';
                    listbox_cluster_dou($link, $kid);
                	echo '</table>';

                    if ($report == 'rC')    {
                        $select = "yearid = $yid AND gradelevel in (20, 21, 22)";
                        $select .=  " AND edizm <> 'null'";
                        $order = 'id';
                        echo '<table cellspacing="0" cellpadding="10" align="center" class="generaltable generalbox">';
                    	listbox_rating_criteria("$scriptname?r=$report&level=$level&rid=$rid&oid=$oid&nm=$nm&yid=$yid&kid=$kid&sn=$shortname&cid=", $shortname, $select, $criteriaid, $order);
                        echo '</table><br />';
                       	if ($criteriaid <> 0)	{
              	    	   	$strsql = "SELECT concat (number, '. ', name) as nname FROM {$CFG->prefix}monit_rating_criteria
   			                           WHERE id=$criteriaid";
	                        $nname = get_field_sql($strsql);
                            print_heading($nname, 'center', 4);
                    		$table = table_ratingregion_criteriaid($rid, $oid, $yid, $nm, $shortname, $select, $criteriaid, $kid);
                        }
                    } else {    
                        if ($kid != 0)   {
                            $table = table_svodrating(-1, $oid, $yid, $nm, $shortname, $select, $order, $kid, $report);
                        }
                    }        
        break;                  
    }
            
 
    if (!empty($table)) {
   	    print_color_table($table);
		$options = array('action'=> 'excel', 'rid' => $rid, 'oid' => $oid, 'yid' => $yid,  'cid' => $criteriaid, 'kid' => $kid, 
						  'r' => $report, 'level' => $level, 'nm' => $nm,  'sn' => $shortname, 'sesskey' => $USER->sesskey);
	   	echo '<center>';
	    print_single_button('svodrating.php', $options, get_string('downloadexcel'));
   	    echo '</center>';
        echo '<br /><br />';
    }    

   	echo '<center><br /><br /><br /><br /><br /><br /><br /><hr>';
	$options = array('action'=> 'recalc', 'rid' => 1, 'yid' => $yid, 'sesskey' => $USER->sesskey);
    print_single_button("recalcrating.php", $options, get_string('recalcrating', 'block_monitoring'));
    /*
    echo '<br /><br />';
	$options = array('action'=> 'exclude', 'rid' => 1, 'yid' => $yid, 'sesskey' => $USER->sesskey);
    print_single_button("recalcrating.php", $options, get_string('recalcrating', 'block_monitoring') . ' (без учета показателя П12)');
    */
    echo '</center>';

	// print_string('remarkyear', 'block_monitoring');
    print_footer();
    


function table_svodrating($rid, $sid, $yid, $nm, $shortname, $select, $order, $kid, $report)	
{
	global $CFG, $edit_capability_region, $edit_capability_rayon;

    $strstatus = get_string('status', 'block_monitoring');
    $numberf = get_string('ratingnum', 'block_monitoring');
    $strname = get_string('dougr', 'block_mou_att');
    $valueofpokazatel = get_string('valueofpokazatel', 'block_monitoring');

    $table = new stdClass();
    $table->head  = array ($strstatus, $numberf, $strname, 'Всего');
    $table->align = array ("center", "center", "left", "center");
	$table->width = '90%';
    $table->size = array ('5%', '5%', '90%', '5%');
    $table->columnwidth = array (10, 7, 100, 15);
	$table->class = 'moutable';
	
   	$table->titlesrows = array(30, 30);
    $table->titles = array();
    $table->titles[] = 'Сводный рейтинг дошкольных образовательных учреждений'; 
	$table->titles[] = '';
    $table->downloadfilename = "report_{$rid}_{$shortname}";
    $table->worksheetname = $table->downloadfilename;
	
	$datefrom = get_date_from_month_year($nm, $yid);
	// $curryid = get_current_edu_year_id();
    $curryid = $yid;
    
    $whererid = '';
    if ($rid > 0) {
        $whererid =  "rayonid = $rid AND ";
    }
    
    if ($kid == -1)   {
        $kid = '0,1,2,3';
    }    
    
    if ($report == 'rA')    {
        $mdltable =  'monit_rating_dou';
        $exclude = "";
    } else {
        $mdltable =  'monit_rating_dou';
        $exclude = "AND number not in ('П12')";
    }
    
    if ($kid == 6)   { // Дошкольные группы в школах
    	$strsql =  "SELECT id, name FROM {$CFG->prefix}monit_school
    				WHERE $whererid isclosing=0 AND yearid=$curryid AND clusterdou=6
    				ORDER BY number";	
    } else {
    	$strsql =  "SELECT id, name FROM {$CFG->prefix}monit_education
    				WHERE $whererid isclosing=0 AND yearid=$curryid AND stateinstitution in ($kid)
    				ORDER BY number";	
    }
    
	$color = 'red';
	if ($schools = get_records_sql($strsql))	{
		
        $schoolsarray = array();
        $schoolsname = array();
        $schoolsmark = array();
	    foreach ($schools as $sa)  {
	        $schoolsarray[] = $sa->id;
	        $schoolsname[$sa->id] = $sa->name;
	        $schoolsmark[$sa->id] = 0;
	    }
	    // $schoolslist = implode(',', $schoolsarray);

		$strsql = "SELECT id, number, name FROM {$CFG->prefix}monit_rating_criteria
	   			   WHERE $select $exclude 
	 		   	   ORDER BY $order";
        // echo $strsql;                    
		if ($criterias = get_records_sql($strsql)) 	{
  			$criteriaids = array();
	   		foreach($criterias as $criteria)	{
	   			$criteriaids[] = $criteria->id;
		  	}
	    	// $criterialist = implode(',', $criteriaids);
		}  	


//		$strsql = "SELECT id, schoolid, mark FROM {$CFG->prefix}monit_rating_school
//		 		   WHERE (schoolid in ($schoolslist))  AND criteriaid=$criteriaid";

        $schoolslist = implode(',', $schoolsarray);

        if ($kid == 6)   { // Дошкольные группы в школах
    		$strsql = "SELECT id, schoolid, criteriaid, mark FROM {$CFG->prefix}{$mdltable}
    		 		   WHERE (schoolid in ($schoolslist))  AND yearid=$yid";
        } else {
    		$strsql = "SELECT id, douid as schoolid, criteriaid, mark FROM {$CFG->prefix}{$mdltable}
    		 		   WHERE (douid in ($schoolslist))  AND yearid=$yid";
        }
	    if ($ratschools = get_records_sql($strsql)) 	{
		    foreach ($ratschools as $rs)  {
		    	if (in_array($rs->criteriaid, $criteriaids))	{ 
		            $schoolsmark[$rs->schoolid] += $rs->mark;
		        }    
		    }
		}
		
        foreach ($schools as $sa)  { 
            $schoolsmark_i[$sa->id] = $schoolsmark[$sa->id]; //  + $schoolsmark_k[$sa->id]; 
        }    
                            
		arsort($schoolsmark_i);        
		reset($schoolsmark_i);
		$maxmark = current($schoolsmark_i);
		// echo $maxsm; 
		$placerating = array();
		$mesto = 1;
		foreach ($schoolsmark_i as $schoolid => $schoolmark) {
			// if ($schoolmark > 0) {
				if ($schoolmark == $maxmark)	{
					$placerating[$schoolid] = $mesto;
				} else {
					$placerating[$schoolid] = ++$mesto;
					$maxmark = $schoolmark; 
				}	 
			/* } else {
				$placerating[$schoolid] = '-';
			}*/
		}	
			
 	
		foreach ($schoolsmark_i as $schoolid => $schoolmark) {
			$schoolname = $schoolsname[$schoolid];
            
            if ($kid == 6)   { // Дошкольные группы в школах
                $thisrid = get_field_select('monit_school', 'rayonid', "id = $schoolid");
                $link = "listcriteria.php?rid=$thisrid&yid=$yid&typeou=03&oid=$schoolid";
            
            } else {
                $thisrid = get_field_select('monit_education', 'rayonid', "id = $schoolid");
                $link = "listcriteria.php?rid=$thisrid&yid=$yid&typeou=20&oid=$schoolid";        
            }
            
			$schoolname = "<strong><a href=\"$link\">$schoolname</a></strong>";
			$mesto = '<b><i>'.$placerating[$schoolid] . '</i></b>';
			// $mesto = $placerating[$schoolid];
			// if ($schoolmark >= 0)	{
			// $strmark = 	 "<b><font color=green>{$schoolsmark[$schoolid]}</font></b>";
            // $strmark_k = "<b><font color=green>{$schoolsmark_k[$schoolid]}</font></b>";
            $strmark_i = "<b><font color=green>$schoolmark</font></b>"; 
			/*} else {
			   $strmark = "<b><font color=red>-</font></b>";	
			}*/
			
	    	$strformrkpu_status = get_string("status1","block_monitoring");			
			$strcolor = get_string("status1color","block_monitoring");
		
            if ($kid == 6)   { // Дошкольные группы в школах
                $select = "SELECT min(`status`) as minstatus FROM mdl_monit_rating_listforms m
                        where (schoolid=$schoolid) and (datemodified=$datefrom)";
            } else {
                $select = "SELECT min(`status`) as minstatus FROM mdl_monit_rating_listforms m
                        where (douid=$schoolid) and (datemodified=$datefrom)";
            }
			if ($minstatus = get_field_sql($select))	{
				$strformrkpu_status = get_string('status'.$minstatus, "block_monitoring");
				$strcolor = get_string('status'.$minstatus.'color',"block_monitoring");	
			}
	 	
	
            $table->data[] = array ($strformrkpu_status, $mesto, $schoolname, $strmark_i);  
 
		    $table->bgcolor[] = array ($strcolor);
		}    
	}
	
	return $table;
}    




function table_ratingregion_criteriaid($rid, $oid, $yid, $nm, $shortname, $select, $criteriaid, $kid)	
{
	global $CFG, $admin_is, $region_operator_is, $rayon_operator_is;

    $numberf = get_string('ratingnum', 'block_monitoring');
    $strname = get_string('dougr', 'block_mou_att');
    $valueofpokazatel = get_string('valueofpokazatel', 'block_monitoring');
    
    $rayonname = get_field_select('monit_rayon', 'name', "id = $rid");
    $criterianame = get_field_select('monit_rating_criteria', 'name', "id = $criteriaid");
    
    $table = new stdClass();
    $table->head  = array ($numberf, $strname, $valueofpokazatel);
    $table->align = array ("center", "left", "center");
	$table->width = '90%';
    $table->size = array ('5%', '90%', '5%');
    $table->columnwidth = array (7, 100, 15);
	$table->class = 'moutable';

   	$table->titlesrows = array(30, 30, 30);
    $table->titles = array();
    $table->titles[] = 'Сводный рейтинг дошкольных образовательных учреждений'; 
    $table->titles[] = get_string('ratingcriteria', 'block_mou_dou');
    $table->titles[] = $criterianame;
    $table->downloadfilename = "criteria_{$rid}_{$shortname}";
    $table->worksheetname = 'criteria';

	$datefrom = get_date_from_month_year($nm, $yid);
	// $curryid = get_current_edu_year_id();
    $curryid = $yid;

    if ($kid == -1)   {
        $kid = '0,1,2,3';
    }    

    if ($kid == 6)   { // Дошкольные группы в школах
    	$strsql =  "SELECT id, name FROM {$CFG->prefix}monit_school
    				WHERE isclosing=0 AND yearid=$curryid AND clusterdou=6
    				ORDER BY number";	
    } else {
    	$strsql =  "SELECT id, name FROM {$CFG->prefix}monit_education
    				WHERE isclosing=0 AND yearid=$curryid AND stateinstitution in ($kid)
    				ORDER BY number";	
    }
	$color = 'red';
	if ($schools = get_records_sql($strsql))	{
		
        $schoolsarray = array();
        $schoolsname = array();
        $schoolsmark = array();
	    foreach ($schools as $sa)  {
	        $schoolsarray[] = $sa->id;
	        $schoolsname[$sa->id] = $sa->name;
	        $schoolsmark[$sa->id] = -1;
	    }
	    $schoolslist = implode(',', $schoolsarray);

        if ($kid == 6)   { // Дошкольные группы в школах
		   $strsql = "SELECT id, schoolid, mark FROM {$CFG->prefix}monit_rating_dou
		 		   WHERE (schoolid  in ($schoolslist)) AND criteriaid=$criteriaid AND yearid=$yid";
        } else {
		   $strsql = "SELECT id, douid as schoolid, mark FROM {$CFG->prefix}monit_rating_dou
		 		   WHERE (douid in ($schoolslist)) AND criteriaid=$criteriaid AND yearid=$yid";
        }
                   
	    if ($ratschools = get_records_sql($strsql)) 	{
		    foreach ($ratschools as $rs)  {
		        $schoolsmark[$rs->schoolid] = $rs->mark;
		    }
			arsort($schoolsmark);
		}
		
		reset($schoolsmark);
		$maxmark = current($schoolsmark);
		// echo $maxsm; 
		$placerating = array();
		$mesto = 1;
		foreach ($schoolsmark as $schoolid => $schoolmark) {
			if ($schoolmark > 0) {
				if ($schoolmark == $maxmark)	{
					$placerating[$schoolid] = $mesto;
				} else {
					$placerating[$schoolid] = ++$mesto;
					$maxmark = $schoolmark; 
				}	 
			} else {
				$placerating[$schoolid] = '-';
			}
		}	
			
 	
		foreach ($schoolsmark as $schoolid => $schoolmark) {
			$schoolname = $schoolsname[$schoolid];
			$schoolname = "<strong>$schoolname</strong></a>";
			$mesto = '<b><i>'.$placerating[$schoolid] . '</i></b>';
			if ($schoolmark >= 0)	{
			   $strmark = "<b><font color=green>$schoolmark</font></b>";	
			} else {
			   $strmark = "<b><font color=red>-</font></b>";	
			}
		 	
		    $table->data[] = array ($mesto, $schoolname, $strmark);
		}    
	}
	
	return $table;
}

?>