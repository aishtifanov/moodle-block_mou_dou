<?php // $Id: ratingrayon.php,v 1.8 2012/10/18 10:40:41 shtifanov Exp $

    require_once("../../config.php");
    require_once('../monitoring/lib.php');
    require_once('../mou_att2/lib_att2.php');
    require_once('../monitoring/rating/lib_rating.php');
    require_once('../mou_ege/lib_ege.php');
    require_once('lib_dou.php');

    $rid = required_param('rid', PARAM_INT);            // Rayon id
    $oid = optional_param('oid', 0, PARAM_INT);            // School id
    $yid = optional_param('yid', 0, PARAM_INT);       		// Year id
    // $nm  = optional_param('nm', 9, PARAM_INT);  // Month number
    $criteriaid = optional_param('cid', 0);       // Shortname form
    $typeou = optional_param('typeou', '20');       // Type OU
    $shortname = optional_param('sn', 'rating_dou');
	$action   = optional_param('action', '');        
    $nm = 9;

    if ($yid == 0)	{
    	$yid = get_current_edu_year_id();
    }

    $scriptname = 'ratingcriteria.php';

    get_edit_capability_region_rayon($rid, $edit_capability_region, $edit_capability_rayon);
    
	$strlistrayons =  listbox_rayons_att("$scriptname?oid=0&amp;yid=$yid&amp;rid=", $rid);
	$strlisttypeou =  listbox_typeou_att("$scriptname?rid=$rid&amp;yid=$yid&amp;oid=0&amp;typeou=", $rid, $typeou, false, true);

	if (!$strlistrayons && !$strlisttypeou)   { 
		error(get_string('permission', 'block_mou_school'), '../index.php');
	}	

    if ($action == 'excel') 	{
    	// init_region_criteria($yid);
        $select = "yearid = $yid AND gradelevel in (20, 21, 22)";
        $select .=  " AND edizm <> 'null'";

	    $table = table_ratingrayon($rid, $oid, $yid, $nm, $shortname, $select, $criteriaid);
  		print_table_to_excel($table);
        exit();
	}

	$strtitle = get_string('title','block_mou_dou');
    $strscript = get_string('ratingcriteria', 'block_mou_dou');

    $navlinks = array();
    $navlinks[] = array('name' => $strtitle, 'link' => "$CFG->wwwroot/blocks/mou_dou/index.php?rid=$rid&amp;yid=$yid", 'type' => 'misc');
    $navlinks[] = array('name' => $strscript, 'link' => null, 'type' => 'misc');
    $navigation = build_navigation($navlinks);
    print_header($SITE->shortname . ': '. $strtitle, $SITE->fullname, $navigation, "", "", true, "&nbsp;"); // , navmenu($course)

 	echo '<table cellspacing="0" cellpadding="10" align="center" class="generaltable generalbox">';
	echo $strlistrayons;
	echo '</table>';
    
    if ($rid != 0)   {
        // print_tabs_years_rating_dou("$scriptname?nm=$nm", $rid, $oid, $yid);
        // init_rating_parameters($yid, $shortname, $select, $order);    
        $select = "yearid = $yid AND gradelevel in (20, 21, 22)";
        $select .=  " AND edizm <> 'null'";
        $order = 'id'; 
        print_heading($strscript, 'center', 4);

        echo '<table cellspacing="0" cellpadding="10" align="center" class="generaltable generalbox">';
    	listbox_rating_criteria("$scriptname?rid=$rid&oid=$oid&nm=$nm&yid=$yid&sn=$shortname&cid=", $shortname, $select, $criteriaid, $order);
        echo '</table><br />';
    
    	
    	if ($criteriaid <> 0)	{
    	   
            $outype = get_constants_ou($typeou, $CONTEXT_OU, $tablename, $strtitle, $strselect, $where);
               	
            print_tabs_years_rating_dou("$scriptname?nm=$nm&sn=$shortname&cid=$criteriaid", $rid, $oid, $yid, $outype);  

    	   	$strsql = "SELECT concat (number, '. ', name) as nname FROM {$CFG->prefix}monit_rating_criteria
                       WHERE id=$criteriaid";
            $nname = get_field_sql($strsql);
            print_heading($nname, 'center', 4);
           
    		$table = table_ratingrayon($rid, $oid, $yid, $nm, $shortname, $select, $criteriaid);
       	   	print_color_table($table);
    
    		$options = array('action'=> 'excel', 'rid' => $rid, 'oid' => $oid, 'yid' => $yid,  'cid' => $criteriaid,
    						  'nm' => $nm,  'sn' => $shortname, 'sesskey' => $USER->sesskey);
    	   	echo '<center>';
    	    print_single_button("ratingcriteria.php", $options, get_string('downloadexcel'));
    	    echo '</center>';
    	}
    }
	// print_string('remarkyear', 'block_monitoring');
    print_footer();


function table_ratingrayon($rid, $sid, $yid, $nm, $shortname, $select, $criteriaid)	
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
    $table->titles[] = get_string('ratingcriteria', 'block_mou_dou');
	$table->titles[] = $rayonname;
    $table->titles[] = $criterianame;
    $table->downloadfilename = "criteria_{$rid}_{$sid}_{$shortname}";
    $table->worksheetname = 'criteria';

	$datefrom = get_date_from_month_year($nm, $yid);
	// $curryid = get_current_edu_year_id();
    $curryid = $yid;


    $strsql =  "create temporary table mdl_temp_ou
        SELECT @i:=@i+1 as num, id, name, 18 as edutypeid  
        FROM mdl_monit_education, (select @i:=0) z
        WHERE  rayonid = $rid AND isclosing=0 AND yearid=$yid
        union
        SELECT @i:=@i+1 as num, id, name, 1 as edutypeid  
        FROM mdl_monit_school
        WHERE  rayonid = $rid AND isclosing=0 AND yearid=$yid and clusterdou=6";
    execute_sql($strsql, false);        
/*
	$strsql =  "SELECT id, name  FROM {$CFG->prefix}monit_education
				WHERE  rayonid = $rid AND isclosing=0 AND yearid=$curryid
				ORDER BY number";	
*/
	$color = 'red';
	if ($ous = get_records('temp_ou'))	{
	    // print_object($ous);
		
        $schoolsids = array(); // id-шники школ
        $douids = array(); // id-шники ДОУ
        $id_num_school = array(); // сопоставляем id-шнику порядковый номер ОУ
        $id_num_dou = array(); // сопоставляем id-шнику порядковый номер ОУ
         
        $ouname = array(); // имена ОУ
        $oumark = array(); // оценки ОУ
        
	    foreach ($ous as $ou)  {
	        
            if ($ou->edutypeid == 1)    {
                $schoolsids[] = $ou->id;
                $id_num_school[$ou->id] = $ou->num;    
            } else if ($ou->edutypeid == 18)    {
                $douids[] = $ou->id;
                $id_num_dou[$ou->id] = $ou->num;
            }
            
	        $ouname[$ou->num] = $ou->name;
	        $oumark[$ou->num] = -1;
	    }
        // print_object($id_num_school);
        
        // выбираем оценки школ
	    $schoolslist = implode(',', $schoolsids);
		$strsql = "SELECT id, schoolid, mark FROM {$CFG->prefix}monit_rating_dou
		 		   WHERE (schoolid in ($schoolslist)) AND criteriaid=$criteriaid AND yearid=$yid";
	    if ($ratschools = get_records_sql($strsql)) 	{
	        // print_object($ratschools);
		    foreach ($ratschools as $rs)  {
		        $oumark[$id_num_school[$rs->schoolid]] = $rs->mark;
		    }
		}
        
        // выбираем оценки ДОУ
	    $doulist = implode(',', $douids);
		$strsql = "SELECT id, douid, mark FROM {$CFG->prefix}monit_rating_dou
		 		   WHERE (douid in ($doulist)) AND criteriaid=$criteriaid AND yearid=$yid";
	    if ($ratdous = get_records_sql($strsql)) 	{
	        // print_object($ratdous);
		    foreach ($ratdous as $rs)  {
		        $oumark[$id_num_dou[$rs->douid]] = $rs->mark;
		    }
		}
        
        arsort($oumark);
        // print_object($oumark);
		
		reset($oumark);
		$maxmark = current($oumark);
		// echo $maxsm; 
		$placerating = array();
		$mesto = 1;
		foreach ($oumark as $numou => $oumark1) {
			if ($oumark1 > 0) {
				if ($oumark1 == $maxmark)	{
					$placerating[$numou] = $mesto;
				} else {
					$placerating[$numou] = ++$mesto;
					$maxmark = $oumark1; 
				}	 
			} else {
				$placerating[$numou] = '-';
			}
		}	
			
 	
		foreach ($oumark as $numou => $oumark1) {
			$ouname1 = $ouname[$numou];
			$ouname1 = "<strong>$ouname1</strong></a>";
			$mesto = '<b><i>'.$placerating[$numou] . '</i></b>';
			if ($oumark1 >= 0)	{
			   $strmark = "<b><font color=green>$oumark1</font></b>";	
			} else {
			   $strmark = "<b><font color=red>-</font></b>";	
			}
		 	
		    $table->data[] = array ($mesto, $ouname1, $strmark);
		}    
	}
	
	return $table;
}

?>