<?php  // $Id: delete.php,v 1.1 2009/11/19 11:33:42 Shtifanov Exp $

    require_once("../../config.php");
    require_once('../monitoring/lib.php');
    require_once('../mou_att2/lib_att2.php');
    
    
    $rid = required_param('rid', PARAM_INT);  // Rayon id
    $oid = required_param('oid', PARAM_INT);  // School id
    $yid = required_param('yid', PARAM_INT);       // Year id
    $fid = required_param('fid', PARAM_INT);       // Form id
    $typeou = required_param('typeou', PARAM_TEXT);       // Type OU
    $nm = required_param('nm', PARAM_INT);         // Month
    $shortname = required_param('sn');       // Shortname form
    $file     = required_param('file', PARAM_FILE);
	$confirm = optional_param('confirm', 0, PARAM_INT);
    
    // $redirlink = "htmlforms.php?rid=$rid&yid=$yid&oid=$oid&typeou=$typeou&stft=$stft&uid=$uid&cid=$cid";
    $returnurl = "htmlforms.php?rid=$rid&yid=$yid&oid=$oid&typeou=$typeou&nm=$nm&sn=$shortname&fid=$fid";
    $returnscript = 'htmlforms.php';

    $scriptname = 'deldoc.php';

    get_edit_capability_region_rayon($rid, $edit_capability_region, $edit_capability_rayon);
    
	$strlistrayons =  listbox_rayons_att("$scriptname?oid=0&yid=$yid&rid=", $rid);
	$strlisttypeou =  listbox_typeou_att("$scriptname?rid=$rid&yid=$yid&oid=0&typeou=", $rid, $typeou);

	if (!$strlistrayons && !$strlisttypeou)   { 
		error(get_string('permission', 'block_mou_school'), '../index.php');
	}	
    
    $outype = get_constants_ou($typeou, $CONTEXT_OU, $tablename, $strtitle, $strselect, $where);
    
	$context = get_context_instance($CONTEXT_OU, $oid);
    $view_capability = has_capability('block/mou_att2:viewou', $context);
    $edit_capability = has_capability('block/mou_att2:editou', $context);    
   
    $dou = get_record($tablename, 'id', $oid);
    
   	$strtitle = get_string('title','block_mou_dou');
	$strscript = get_string('begindata', 'block_monitoring');
    $strrating = get_string('rating', 'block_monitoring');
    $razdel = get_record_select('monit_razdel', "shortname = '$shortname'", 'id, name');
	// $strformname = get_string('name_'.$shortname,'block_monitoring');
    $strformname = $razdel->name;

    $navlinks = array();
    $navlinks[] = array('name' => $strtitle, 'link' => "$CFG->wwwroot/blocks/mou_dou/index.php?rid=$rid&amp;yid=$yid", 'type' => 'misc');
    $navlinks[] = array('name' => $strscript, 'link' => "listforms.php?rid=$rid&amp;oid=$oid&amp;yid=$yid", 'type' => 'misc');
    $navlinks[] = array('name' => $strformname, 'link' => null, 'type' => 'misc');
    $navigation = build_navigation($navlinks);

    print_header($SITE->shortname . ': '. $strtitle, $SITE->fullname, $navigation, "", "", true, "&nbsp;"); // , navmenu($course)

    if (!$edit_capability)  {
		error(get_string('permission', 'block_mou_school'), $redirlink);
    }    
    
    $optionsreturn = array('rid'=>$rid, 'oid'=>$oid, 'yid'=>$yid, 'fid' => $fid, 'typeou' => $typeou, 'nm' => $nm, 'sn' => $shortname);

    if (!$confirm) {
        $optionsyes = $optionsreturn;
		$optionsyes['file'] = $file;
		$optionsyes['confirm']=1;
		$optionsyes['sesskey'] = sesskey();
        print_heading(get_string('deletingcourse', '', $file), 'center', 3);
        notice_yesno(get_string('confirmdeletefile', 'assignment', $file), 'deldoc.php', $returnscript, $optionsyes, $optionsreturn, 'post', 'get');
        print_footer('none');
        die;
    }
    
    $filepath = $CFG->dataroot."/1/school/$oid/$shortname/$file";
    if (file_exists($filepath)) {
        if (@unlink($filepath)) {
            redirect($returnurl, get_string('clamdeletedfile') , 0);
        }
    }

    // print delete error
    print_header(get_string('delete'));
    notify(get_string('deletefilefailed', 'assignment'));
    print_continue($returnurl);
    print_footer('none');
?>