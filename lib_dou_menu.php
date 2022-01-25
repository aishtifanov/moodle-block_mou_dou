<?php // $Id: lib_culture.php,v 1.21 2012/04/06 07:16:29 shtifanov Exp $


function get_items_menu_block_dou(&$items, &$icons)
{
    global $CFG, $USER, $rid, $sid, $yid;
    
	// $yid = 4;// $yearmonit;  !!!!!!!!!!!!!!!!!!!!1        

    $typeou = 20;
    
	$index_items = array();
	
	$admin_is = isadmin();
	// $region_operator_is = ismonitoperator('region');
	// if  ($admin_is || $region_operator_is) 	{
	if  ($admin_is) 	{  
		$index_items = array(1,2,3,4,5);
	}	

	$strsql = "SELECT a.id, roleid, contextid, contextlevel, userid, instanceid  
			   FROM mdl_role_assignments a	JOIN mdl_context ctx ON a.contextid=ctx.id
			   WHERE userid={$USER->id}";
	if ($ctxs = get_records_sql($strsql))	{
		// echo '<pre>'; print_r($ctxs); echo '<pre>';
		foreach($ctxs as $ctx1)	{
			switch ($ctx1->contextlevel)	{
				// case CONTEXT_REGION:
                case CONTEXT_REGION_ATT:
                                    if ($ctx1->roleid == 8 || $ctx1->roleid == 10)	{
									 	$idx_rayon = array(1,2,3,4,5); //  
    								 	$index_items = array_merge ($idx_rayon, $index_items);
    								 }	
       						         break;
				case CONTEXT_RAYON_DOU:  if ($ctx1->roleid == 8 || $ctx1->roleid == 10) {
				                        if ($ctx1->instanceid == 21)    { // закрываем доступ для Белгорода
				                            $idx_rayon = array(1); //    
				                        } else {
				                            $idx_rayon = array(1,2,3); 
				                        }
									 	 
      								 	$index_items = array_merge ($idx_rayon, $index_items);
									 }
							 		 break;
                
                case CONTEXT_SCHOOL:
                                    // print "id=$ctx1->instanceid and clusterdou=6<br>";
                                    if ($school1 = get_record_select('monit_school', "id=$ctx1->instanceid and clusterdou=6"))  {
                                      if ($ctx1->roleid < 13)	{
								 		$idx_school = array(1); //2, 3
                                         $index_items = array_merge ($idx_school, $index_items); 
                                         $sid = $ctx1->instanceid;
                                         $typeou = '03';
								 	  }   
                                    }
                                    
                                    break;
                                        
				case CONTEXT_DOU:   if ($ctx1->roleid < 13)	{
								 		$idx_school = array(1); //2, 3 
                                         $sid = $ctx1->instanceid;
								 	} else {
								 		$idx_school = array(1); //2, 3  
								 	}	
    								 $index_items = array_merge ($idx_school, $index_items);
				break;
			}
		}
		
		$index_items = array_unique($index_items);
		sort($index_items);
	}		 
    
    $link = "?rid=$rid&sid=$sid&yid=$yid&typeou=$typeou";
    
    $items[1] = '<a href="'.$CFG->wwwroot."/blocks/mou_dou/listforms.php{$link}\">".get_string('begindata', 'block_monitoring').'</a>';
    $icons[1] = '<img src="'.$CFG->pixpath.'/i/settings.gif" height="16" width="16" alt="" />';

    $items[2] = '<a href="'.$CFG->wwwroot."/blocks/mou_dou/listcriteria.php{$link}\">".get_string('listcriteria','block_monitoring').'</a>';
    $icons[2] = '<img src="'.$CFG->pixpath.'/i/report.gif" height="16" width="16" alt="" />';
    
    $items[3] = '<a href="'.$CFG->wwwroot."/blocks/mou_dou/ratingcriteria.php{$link}\">".get_string('ratingcriteria','block_mou_dou').'</a>';
    $icons[3] = '<img src="'.$CFG->wwwroot.'/blocks/mou_school/i/curric.gif" height="16" width="16" alt="" />';

    $items[4] = '<a href="'.$CFG->wwwroot."/blocks/mou_dou/svodrating.php{$link}\">".get_string('svodrating','block_mou_dou').'</a>';
    $icons[4] = '<img src="'.$CFG->wwwroot.'/blocks/mou_school/i/curric.gif" height="16" width="16" alt="" />';

    $items[5] = '<a href="'.$CFG->wwwroot."/blocks/mou_dou/groupoperations.php{$link}\">".'Групповые операции</a>';
    $icons[5] = '<img src="'.$CFG->pixpath.'/i/db.gif" height="16" width="16" alt="" />';
    
/*
	$items[3] = '<a href="'.$CFG->wwwroot."/blocks/mou_culture/otdeleniya/metodrabota.php?rid=$rid&amp;sid=$sid&amp;yid=$yid\">".get_string('metodrabota','block_mou_culture').'</a>';
    $icons[3] = '<img src="'.$CFG->wwwroot.'/blocks/mou_school/i/curric.gif" height="16" width="16" alt="" />';

	$items[4] = '<a href="'.$CFG->wwwroot."/blocks/mou_culture/otdeleniya/masterklass.php?rid=$rid&amp;sid=$sid&amp;yid=$yid\">".get_string('masterklass','block_mou_culture').'</a>';
	$icons[4] = '<img src="'.$CFG->pixpath.'/i/db.gif" height="16" width="16" alt="" />';

	$items[5] = '<a href="'.$CFG->wwwroot."/blocks/mou_culture/otdeleniya/contests.php?rid=$rid&amp;sid=$sid&amp;yid=$yid\">".get_string('contests','block_mou_culture').'</a>';
	$icons[5] = '<img src="'.$CFG->pixpath.'/i/settings.gif" height="16" width="16" alt="" />';

	$items[6] = '<a href="'.$CFG->wwwroot."/blocks/mou_culture/reports/reports.php?rid=$rid&amp;sid=$sid&amp;yid=$yid\">".get_string('reports','block_mou_culture').'</a>';
	$icons[6] = '<img src="'.$CFG->pixpath.'/i/report.gif" height="16" width="16" alt="" />';


    $icons[7] = '<img src="'.$CFG->pixpath.'/i/guest.gif" height="16" width="16" alt="" />';;
	$icons[8] = '<img src="'.$CFG->pixpath.'/i/info.gif" height="16" width="16" alt="" />';
    $icons[9] = '<img src="'.$CFG->pixpath.'/i/settings.gif" height="16" width="16" alt="" />';
*/

    return $index_items;    
}


?>