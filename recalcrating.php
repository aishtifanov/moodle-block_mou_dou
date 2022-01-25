<?php // $Id: recalcrating.php,v 1.8 2012/12/06 12:30:26 shtifanov Exp $

    require_once("../../config.php");
    require_once('../monitoring/lib.php');
    require_once('../mou_att2/lib_att2.php');
    require_once('../monitoring/rating/lib_rating.php');
    require_once('lib_dou.php');

    $rid = required_param('rid', PARAM_INT);       // Rayon id
    $yid = required_param('yid', PARAM_INT);       // Year id
    $trunc = optional_param('trunc', '');       // Make truncate table
    $del = optional_param('del', '');       // Make delete records in thid year
    $action = optional_param('action', '');       // Action
    
    // $rid=2;
    
    get_edit_capability_region_rayon($rid, $edit_capability_region, $edit_capability_rayon);
    
    $scriptname = 'recalcrating.php';
    
	$strlistrayons =  listbox_rayons_att("$scriptname?oid=0&amp;yid=$yid&amp;rid=", $rid);
	$strlisttypeou =  listbox_typeou_att("$scriptname?rid=$rid&amp;yid=$yid&amp;oid=0&amp;typeou=", $rid, $typeou, false, true);

	if (!$strlistrayons && !$strlisttypeou)   { 
		error(get_string('permission', 'block_mou_school'), '../index.php');
	}	

	$strtitle = get_string('title','block_mou_dou');
    $strscript = get_string('recalcrating', 'block_monitoring');

    $navlinks = array();
    $navlinks[] = array('name' => $strtitle, 'link' => "$CFG->wwwroot/blocks/mou_dou/index.php?rid=$rid&amp;yid=$yid", 'type' => 'misc');
    $navlinks[] = array('name' => $strscript, 'link' => null, 'type' => 'misc');
    $navigation = build_navigation($navlinks);
    print_header($SITE->shortname . ': '. $strtitle, $SITE->fullname, $navigation, "", "", true, "&nbsp;"); // , navmenu($course)

    ignore_user_abort(false); // see bug report 5352. This should kill this thread as soon as user aborts.
    @set_time_limit(0);
    @ob_implicit_flush(true);
    @ob_end_flush();
	@raise_memory_limit("512M");
 	if (function_exists('apache_child_terminate')) {
	    @apache_child_terminate();
	}    

	if ($rid<=25)	{
		$rayon = get_record_select('monit_rayon', "id = $rid", 'id, name');
		print_heading($rayon->name, 'center', 3);
	} else {
		print_heading(get_string('finishrecalcrating', 'block_monitoring'), 'center', 3);
		print_footer();
		exit(1);
	}	
	
    $itogmark = 0;
    $REGIONCRITERIA = new stdClass();
    init_region_criteria($yid);


    if ($action == 'exclude')   {
        $exclude = "AND number not in ('П12')";
    } else {
        $exclude =  '';
    }    
    recalculate_rating_update($rid, $yid, $exclude);
    // exit();

    $rid++;
    if ($rid <= 26)	{
    	redirect("recalcrating.php?yid=$yid&rid=$rid&action=$action", '', 5);
    }
    
    print_footer();


function recalculate_rating_update($rid, $yid, $exclude='')
{
    global $CFG;

    $school_type = get_records_select_menu('monit_school_type', "", 'id', 'id, cod');
        
	$nm = 9;
	$datemodified = get_date_from_month_year($nm, $yid);
    $shortnames = array('rating_dou_op', 'rating_dou_ku', 'rating_dou_ed');
    
    $strsql =  "create temporary table mdl_temp_ou
        SELECT @i:=@i+1 as num, id, name, 18 as edutypeid  
        FROM mdl_monit_education, (select @i:=0) z
        WHERE  rayonid = $rid AND isclosing=0 AND yearid=$yid
        union
        SELECT @i:=@i+1 as num, id, name, 1 as edutypeid  
        FROM mdl_monit_school
        WHERE  rayonid = $rid AND isclosing=0 AND yearid=$yid and clusterdou=6";
    execute_sql($strsql, false);        

	$color = 'red';
	if ($schools  = get_records('temp_ou'))	{
	   foreach ($schools as $school)  {
	        $outype = get_constants_ou($school_type[$school->edutypeid], $CONTEXT_OU, $tablename, $strtitle, $strselect, $where);
            $oid = $school->id;
            foreach ($shortnames as $i => $shortname) {
               /* 
               $strsql = "SELECT id, rayonid, schoolid, shortname, datemodified FROM {$CFG->prefix}monit_rating_listforms
               		      WHERE (douid=$oid) and (shortname='$shortname') and (datemodified=$datemodified)";
               */
               $strsql = "SELECT f.id FROM mdl_monit_rating_listforms l 
                           inner join mdl_monit_form_{$shortname} f on l.id=f.listformid
                           WHERE ({$outype->idfieldname}=$oid) and (shortname='$shortname') and (datemodified=$datemodified)";
                           
               // echo  $strsql . '<br />';
               if ($idform = get_field_sql($strsql)) 	{
                    $totalmark = calculate_dou_mark($yid, $rid, $oid, $idform, $shortname, $exclude, $outype);
                    echo "$oid. {$school->name}: $shortname = $totalmark<br />";  
               }
            }
       }
    }                

    
/*
    $strsql =  "SELECT id, name FROM {$CFG->prefix}monit_education
				WHERE rayonid = $rid AND isclosing=0 AND yearid=$yid";	
	if ($schools = get_records_sql($strsql))	{
	   foreach ($schools as $school)  {
	        $outype = get_constants_ou('20', $CONTEXT_OU, $tablename, $strtitle, $strselect, $where);
            $oid = $school->id;
            foreach ($shortnames as $i => $shortname) {
                
               // $strsql = "SELECT id, rayonid, schoolid, shortname, datemodified FROM {$CFG->prefix}monit_rating_listforms
               //	         WHERE (douid=$oid) and (shortname='$shortname') and (datemodified=$datemodified)";
               
               $strsql = "SELECT f.id FROM mdl_monit_rating_listforms l 
                           inner join mdl_monit_form_{$shortname} f on l.id=f.listformid
                           WHERE (douid=$oid) and (shortname='$shortname') and (datemodified=$datemodified)";
               // echo  $strsql . '<br />';
               if ($idform = get_field_sql($strsql)) 	{
                    $totalmark = calculate_dou_mark($yid, $rid, $oid, $idform, $shortname, $exclude, $outype);
                    echo "$oid: $shortname = $totalmark<br />";  
               }
            }
       }
    }
*/                    
}    	 


?>