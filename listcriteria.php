<?php // $Id: listcriteria.php,v 1.18 2012/12/06 12:30:25 shtifanov Exp $

    require_once("../../config.php");
    require_once('../monitoring/lib.php');
    require_once('../mou_att2/lib_att2.php');
    require_once('../mou_ege/lib_ege.php');
    require_once('../monitoring/rating/lib_rating.php');
    require_once('../monitoring/queue/lib_queue.php');
            
    require_once('lib_dou.php');

    $rid = required_param('rid', PARAM_INT);            // Rayon id
    // $sid = optional_param('sid', 0, PARAM_INT);            // School id
    $oid = optional_param('oid', 0, PARAM_INT);       // OU id
    $yid = optional_param('yid', 0, PARAM_INT);       		// Year id
    $fid = optional_param('fid', 0, PARAM_INT);       // Form id
    $level = optional_param('level', 'school');       // Form id
    // $nm  = optional_param('nm', 9, PARAM_INT);  // Month number
    $typeou = optional_param('typeou', '20');       // Type OU    
    $nm = 9;
	$itogmark = 0;
	
    if ($yid == 0)	{
    	$yid = get_current_edu_year_id();
    }

    $scriptname = 'listcriteria.php';

    get_edit_capability_region_rayon($rid, $edit_capability_region, $edit_capability_rayon);
    
	$strlistrayons =  listbox_rayons_att("$scriptname?oid=0&amp;yid=$yid&amp;rid=", $rid);
	// $strlisttypeou =  listbox_typeou_att("$scriptname?rid=$rid&amp;yid=$yid&amp;oid=0&amp;typeou=", $rid, $typeou, false, true);

	if (!$strlistrayons) { //  && !$strlisttypeou)   { 
		error(get_string('permission', 'block_mou_school'), '../index.php');
	}	
    
    // закрываем доступ для Белгорода
    if (!$edit_capability_region && ($edit_capability_rayon && $rid == 21)) {
        error(get_string('permission', 'block_mou_school'), '../index.php');
    }
    
    $outype = get_constants_ou($typeou, $CONTEXT_OU, $tablename, $strtitle, $strselect, $where);
        
    if ($oid > 0) {
    	$context = get_context_instance($CONTEXT_OU, $oid);
        $view_capability = has_capability('block/mou_att2:viewou', $context);
        $edit_capability = has_capability('block/mou_att2:editou', $context);
    }        

    init_rating_parameters($yid, $shortname, $select, $order);
    
    $REGIONCRITERIA = new stdClass();
    init_region_criteria($yid);
    
    if ($oid != 0)	{
        if ($typeou == '20')    {
   	       $strschool = get_field_select('monit_education', 'name', "id = $oid");
        } else {
   	       $strschool = get_field_select('monit_school', 'name', "id = $oid");            
        }    
    }	else  {
   	    $strschool = get_string('dou', 'block_mou_att');
    }

		
	$action   = optional_param('action', '');
    if ($action == 'excel') 	{
    	// init_region_criteria($yid);
	    $table = table_listcriteria($rid, $oid, $yid, $nm, $shortname, $action, $outype);
  		print_table_to_excel($table);
        exit();
	}



   	$strtitle = get_string('title','block_mou_dou');    
    $strscript = get_string('listcriteria', 'block_monitoring');
    $strrayon = get_string('rayon', 'block_monitoring');
    $strrayons = get_string('rayons', 'block_monitoring');
    $strschools = get_string('college', 'block_monitoring');
    $strreports = get_string('reportschool', 'block_monitoring');

   
    $navlinks = array();
    $navlinks[] = array('name' => $strtitle, 'link' => "$CFG->wwwroot/blocks/mou_dou/index.php?rid=$rid&amp;yid=$yid", 'type' => 'misc');
    $navlinks[] = array('name' => $strscript, 'link' => null, 'type' => 'misc');
    $navlinks[] = array('name' => $strschool, 'link' => null, 'type' => 'misc');
    $navigation = build_navigation($navlinks);
    print_header($SITE->shortname . ': '. $strtitle, $SITE->fullname, $navigation, "", "", true, "&nbsp;"); // , navmenu($course)
    

    // $currenttab = 'listcriteria';
    // include('tabs.php');

    /*
    $toprow2  = array();
    $toprow2[] = new tabobject('school', "listcriteria.php?level=school&rid=$rid&oid=$oid&nm=$nm&yid=$yid", 'По ДОУ');
    $toprow2[] = new tabobject('rayon', "listcriteria.php?level=rayon&rid=$rid&nm=$nm&yid=$yid", 'По району');
    $tabs2 = array($toprow2);
    print_tabs($tabs2, $level, NULL, NULL);
    */
    
	echo '<table cellspacing="0" cellpadding="10" align="center" class="generaltable generalbox">';
	echo $strlistrayons;

    listbox_typeou("$scriptname?yid=$yid&rid=$rid&typeou=", $rid, $typeou);
    if ($typeou != '-')	{
       	if ($strlistou = listbox_ou_att("$scriptname?rid=$rid&amp;yid=$yid&amp;typeou=$typeou&amp;oid=", $rid, $typeou, $oid, $yid, 6))	{ 
    		echo $strlistou;
    	} else {
    		echo '</table>';
    		notice(get_string('ounotfound', 'block_mou_att'), "../index.php?rid=$rid&amp;yid=$yid");
    	}	
    }   
	echo '</table>';
	
	if ($rid != 0 && $oid != 0)   {
        // init_region_criteria($yid);
        
               	
        print_tabs_years_rating_dou("$scriptname?nm=$nm", $rid, $oid, $yid, $outype);  
  
       /*
        echo '<table cellspacing="0" cellpadding="10" align="center" class="generaltable generalbox">';
        listbox_rating_level("listcriteria.php?level=$level&rid=$rid&amp;sid=$sid&amp;nm=$nm&amp;yid=$yid&amp;sn=", $shortname, $yid, false, $level);
        echo '</table>';
        echo '<p>';
        */
        $shortname = optional_param('sn', 'rating_dou');
	
	    $totalsum = $itogmark = 0;

    	$options = array('action'=> 'excel', 'rid' => $rid, 'oid' => $oid, 'yid' => $yid, 'typeou'  => $typeou, 
    					 'fid' => $fid,  'nm' => $nm,  'sn' => $shortname,  'sesskey' => $USER->sesskey);
    	
       	echo '<center>';
        print_single_button("listcriteria.php", $options, get_string('downloadexcel'));
        echo '</center><br />';
 
        $table = table_listcriteria($rid, $oid, $yid, $nm, $shortname, '', $outype);    
      	$strtotlamark = get_string('total_mark', 'block_monitoring') . ': ' . $totalsum;
        print_heading($strscript, 'center', 3);
       	print_heading($strtotlamark, 'center', 4);
      	// print_table($table);

       	print_color_table($table);
	    print_heading($strtotlamark, 'center', 4);

   
       	echo '<center>';
        print_single_button("listcriteria.php", $options, get_string('downloadexcel'));
        echo '</center>';
    }
    
	// print_string('remarkyear', 'block_monitoring');
    print_footer();



function table_listcriteria($rid, $oid, $yid, $nm, $shortname, $action, $outype)	
{
	global $CFG, $edit_capability_region, $edit_capability_rayon, $totalsum, $itogmark, $level, $strschool;

    $symbolnumber = get_string('symbolnumber', 'block_monitoring'); 
    $nameofpokazatel = get_string('nameofpokazatel', 'block_monitoring');
    $valueofpokazatel = get_string('mark', 'block_monitoring');
	$formula = get_string('formula','block_monitoring');
	$straction = get_string("action","block_monitoring");

    $table = new stdClass();
    if ($edit_capability_region || $edit_capability_rayon)   {
        $table->head  = array ($symbolnumber, $nameofpokazatel, $formula, $valueofpokazatel);
        $table->align = array ("left", "left", "center", "center");
    	$table->width = '90%';
        $table->size = array ('5%', '65%', '15%', '15%');
        $table->columnwidth = array (7, 100, 15, 15);
    	$table->class = 'moutable';
    } else {
        $table->head  = array ($symbolnumber, $nameofpokazatel, $valueofpokazatel);
        $table->align = array ("left", "left", "center");
    	$table->width = '90%';
        $table->size = array ('5%', '65%', '15%');
        $table->columnwidth = array (7, 100, 15);
    	$table->class = 'moutable';
    }

    $yearname = get_field_select('monit_years', 'name', "id = $yid");
    $godi = explode('/', $yearname); 
   	$table->titlesrows = array(30);
    $table->titles = array();
	// $table->titles[] = get_string('name_'.$shortname, 'block_monitoring');
    $table->titles[] = get_string('listcriteria', 'block_monitoring') . ' ' . $strschool . ' (' . $godi[0] . ' год)';
    $table->downloadfilename = "{$strschool}_{$rid}_{$oid}_{$shortname}";
    $table->worksheetname = 'criteria';
	
	get_name_otchet_year ($yid, $a, $b);
	// echo $a . $b;	

    // init_rating_parameters($yid, $shortname, $select, $order, $level);	
 
    $datefrom = get_date_from_month_year($nm, $yid);

    $strsql = "SELECT * FROM {$CFG->prefix}monit_rating_listforms
    	   		   WHERE ({$outype->idfieldname}=$oid) and (datemodified=$datefrom)"; // and (shortname='$shortname') 
  	
	$arr_df = array();
	if ($recs = get_records_sql($strsql))	{
	    foreach ($recs as $rec)    {
            $shortname = $rec->shortname;
 		    $fid = $rec->id;
            if ($df = get_record_sql("SELECT * FROM {$CFG->prefix}monit_form_$shortname WHERE listformid=$fid"))	{
   			  $arr_df += (array)$df;
			  // print_object($arr_df);
            }    	
   		}
   	}	

    $select = "yearid=$yid AND gradelevel in (20, 21, 22)";
    // $select .=  " AND edizm <> 'null'";    
    $order = 'gradelevel, sortnumber'; 

    $strsql = "SELECT id, number, name, formula, edizm, indicator, ordering 
			   FROM {$CFG->prefix}monit_rating_criteria
    		   WHERE  $select 
			   ORDER BY $order";
    // echo $strsql .' <br />';
               
	if ($criterias = get_records_sql($strsql)) 	{
	
   		foreach($criterias as $criteria)	{
			$color = 'red';// get_string('status1color', 'block_monitoring');
			$strmark = "<b><font color=\"$color\">0</font></b>";

   			if ($criteria->formula == 'null')	{
				$criterianumber = '<b>'. $criteria->number . '</b>';
				eval("\$criterianame = \"$criteria->name\";");
   				$criterianame = '<b>'.$criterianame.'</b>';   			
   				$criteriaformula = '';
				$strmark = ''; 
   			} else {
   				// $criterianame = $criteria->name;
   				eval("\$criterianame = \"$criteria->name\";");
   				$criterianumber = $criteria->number;
				$operands = explode('#', $criteria->formula);
				// echo $criteria->formula . '<br>';
				// print_r($operands); echo '<br>';
				$o1 = trim($operands[0]);
				$o2 = trim($operands[1]);
				$criteriaformula = '<i>'.translitfield('f'.$operands[1]) . '</i>';

                // echo $o1 . '<br />';
				if (!empty($arr_df))	{
	               if (function_exists($o1)) {
	               		$namefunc = $o1;
	               		$strmark = $namefunc($o2, $criteria->indicator, $arr_df, $criteria->ordering);
	               		// echo "$totalsum += $itogmark;<br>" . $strmark . '<br>';
	               		$totalsum += $itogmark;
	               		// echo "$totalsum<hr>";
					}
				} else {
					$strmark = '-';
				}	
				
			}	   
    		if ($action == 'excel') 	{
    			$criterianumber = " " . $criterianumber; 
    		}	
            
           if ($edit_capability_region || $edit_capability_rayon)   {
                $table->data[] = array ($criterianumber, $criterianame, $criteriaformula, $strmark); //
            } else {
                $table->data[] = array ($criterianumber, $criterianame, $strmark); //                
            }  
		}    
	}
	
	return $table;
}


?>