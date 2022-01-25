<?php // $Id: listforms.php,v 1.16 2012/11/14 10:58:53 shtifanov Exp $

    require_once("../../config.php");
    require_once('../monitoring/lib.php');
	require_once('../monitoring/lib_excel.php');
    require_once('../mou_att2/lib_att2.php');
    require_once('../monitoring/rating/lib_rating.php');
    require_once('../monitoring/queue/lib_queue.php');
    require_once('lib_dou.php');
    
    $rid = required_param('rid', PARAM_INT);            // Rayon id
    // $sid = optional_param('sid', 0, PARAM_INT);            // School id
    $oid = optional_param('oid', 0, PARAM_INT);       // OU id
    $yid = optional_param('yid', 0, PARAM_INT);       		// Year id
    $fid = optional_param('fid', 0, PARAM_INT);       // Form id
    $typeou = optional_param('typeou', '20');       // Type OU
    // $nm  = optional_param('nm', 9, PARAM_INT);  // Month number
    $action   = optional_param('action', '');
    $rzid = optional_param('rzid', -1, PARAM_INT);       
    $statusid = optional_param('statusid', 0, PARAM_INT);       
    $nm = 9;
    
    $scriptname = 'groupoperations.php';

    $curryearid = get_current_edu_year_id();
    
    if ($yid != 0)	{
    	$eduyear = get_record('monit_years', 'id', $yid);
    } else {
    	$yid = $curryearid;
    	$eduyear = get_record('monit_years', 'id', $yid);
    }

    get_edit_capability_region_rayon($rid, $edit_capability_region, $edit_capability_rayon);
    
	$strlistrayons =  listbox_rayons_att("$scriptname?yid=$yid&statusid=$statusid&rzid=$rzid&rid=", $rid);
	// $strlisttypeou =  listbox_typeou_att("$scriptname?rid=$rid&amp;yid=$yid&amp;oid=0&amp;typeou=", $rid, $typeou, false, true);

	if (!$strlistrayons or !$edit_capability_region)   {  // && !$strlisttypeou 
		error(get_string('permission', 'block_mou_school'), '../index.php');
	}	
	
   	$strtitle = get_string('title','block_mou_dou');
	$strscript = 'Групповые операции';

    $navlinks = array();
    $navlinks[] = array('name' => $strtitle, 'link' => "$CFG->wwwroot/blocks/mou_dou/index.php?rid=$rid&amp;yid=$yid", 'type' => 'misc');
    $navlinks[] = array('name' => $strscript, 'link' => null, 'type' => 'misc');
    $navigation = build_navigation($navlinks);

    print_header($SITE->shortname . ': '. $strscript, $SITE->fullname, $navigation, "", "", true, "&nbsp;"); // , navmenu($course)

	echo '<table cellspacing="0" cellpadding="10" align="center" class="generaltable generalbox">';
	echo $strlistrayons;
    listbox_razdel_dou("$scriptname?yid=$yid&rid=$rid&statusid=$statusid&rzid=", $rzid);
    listbox_status_dou("$scriptname?yid=$yid&rid=$rid&rzid=$rzid&statusid=", $statusid);
	echo '</table>';

    if ($action == 'change' && $statusid > 0 && $rid > 0) 	{
        $selectstatusid = '';
        if ($statusid > 2) {
            // $selectstatusid = ' status <> 2 AND ';
        }
        
        $select = " shortname in ('rating_dou_op', 'rating_dou_ku', 'rating_dou_ed') AND ";
        if ($rzid > 0)  {
            $shortname = get_field_select('monit_razdel', 'shortname', "id=$rzid");
            $select = " shortname = '$shortname' AND "; 
        }
        $sql = "update mdl_monit_rating_listforms
                set status=$statusid
                where $select $selectstatusid douid in (SELECT id FROM mdl_monit_education where yearid=$yid and rayonid=$rid)";
        // print $sql . '<br>';        
        execute_sql($sql, false);        
        $sql = "update mdl_monit_rating_listforms
                set status=$statusid
                where $select $selectstatusid schoolid in (SELECT id FROM mdl_monit_school where yearid=$yid and rayonid=$rid)";
        execute_sql($sql, false);
        // print $sql . '<br>';
        notify('Изменение статуса выполнено.', 'green');
	} else 	if ($rid != 0 && $statusid != 0)   {
       
        	$options = array('action'=> 'change', 'rid' => $rid, 'oid' => $oid, 'yid' => $yid, 
        					 'rzid' => $rzid, 'statusid' => $statusid,  'sesskey' => $USER->sesskey);
           	echo '<center>';
            print_single_button($scriptname, $options, 'Установить статус у выбранной таблиц(ы) для всех ДОУ выбранного района');
            echo '</center>';
    }
    print_footer();

?>