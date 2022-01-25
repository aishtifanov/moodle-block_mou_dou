<?php // $Id: htmlforms.php,v 1.16 2012/11/14 10:58:53 shtifanov Exp $
    
    require_once("../../config.php");
    require_once($CFG->dirroot.'/lib/uploadlib.php');
    require_once($CFG->libdir.'/filelib.php');
    require_once('../monitoring/lib.php');
    require_once('../mou_att2/lib_att2.php');
    require_once('../monitoring/rating/lib_rating.php');
    require_once('lib_dou.php');
 

    $rid = required_param('rid', PARAM_INT);       // Rayon id
    $oid = optional_param('oid', 0, PARAM_INT);       // OU id
    $fid = required_param('fid', PARAM_INT);       // Form id
    $yid = required_param('yid', PARAM_INT);       		// Year id
    $nm = required_param('nm', PARAM_INT);         // Month
    $shortname = required_param('sn');       // Shortname form
	$action   = optional_param('action',   '-');
	$copynext = optional_param('copynext', '-');
	$copyprev = optional_param('copyprev', '-');
    $typeou = optional_param('typeou', '-');       // Type OU
    
    $scriptname = 'htmlforms.php';

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

	$itogmark = 0;
	
    // check security
    $datefrom = get_date_from_month_year($nm, $yid);
    $strsql = "SELECT * FROM {$CFG->prefix}monit_rating_listforms
	   		   WHERE ({$outype->idfieldname}=$oid) and (shortname='$shortname') and (datemodified=$datefrom)";
	
	$redirlink = "listforms.php?rid=$rid&oid=$oid&nm=$nm&yid=$yid&fid=$fid&typeou=$typeou";
   	// print $strsql; echo '<hr>';
    if ($rec = get_record_sql($strsql))	{
    	// print_r($rec); echo '<hr>';
    	$currstatus = $rec->status;
        if ($currstatus == 4 && $edit_capability && !$edit_capability_region  && !$edit_capability_rayon )  {
	        error(get_string('accessdenied','block_monitoring'), $redirlink);
        }

        if ($currstatus >= 5 && ($edit_capability_rayon || $edit_capability) && !$edit_capability_region)  {
	        error(get_string('accessdenied','block_monitoring'), $redirlink);
        }

        if ($rec->id != $fid)  {
	        error(get_string('accessdenied','block_monitoring'), $redirlink);
        }
    }

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
   	
		
	/// A form was submitted so process the input
	if ($rec = data_submitted())  {
       // print_object($rec);
       	   
        $REGIONCRITERIA = new stdClass();
        init_region_criteria($yid);
        // print_object($REGIONCRITERIA);
 
        foreach ($rec as $fld => $value)   {
            if (strpos($value, ','))    {
                $rec->{$fld} = str_replace(',', '.', $value); 
            }
        }    
        // print_object($rec);        
        
	    $errcount = find_form_errors($rec, $err, 'monit_form_'.$shortname);

		if ($errcount == 0)  {
		  
            // print_r($_FILES);  echo '<hr>';
            
            $name1 = 'doc_'.$oid.'_'.$shortname;
            if (!empty($_FILES[$name1]['name']))	{
                $dir = "1/school/$oid/$shortname";
                $um = new upload_manager($name1, true, false, 1, false, $CFG->maxbytes);
            
                if ($um->process_file_uploads($dir))  {
                      print_heading(get_string('uploadedfile'), 'center', 4);
                } else {
                      notify(get_string("uploaderror", "assignment")); 
                }
            }
			
			// print_r($REGIONCRITERIA). '<hr>'; exit();
			
            // print $fid. '<br>';
		    if ($fid == 0)  { // insert new records
               $rkp = new stdClass(); 
			   $rkp->rayonid = $rid;
		       $rkp->{$outype->idfieldname} = $oid;
		       $rkp->status = 2;
		       $rkp->shortname = $shortname;
		       // $rkp->shortrusname =  $strformname;
		       // $rkp->fullname = ??????????
		       $rkp->datemodified = get_date_from_month_year($nm, $yid);

   			   $strsql = "SELECT id, rayonid, schoolid, shortname, datemodified FROM {$CFG->prefix}monit_rating_listforms
	 		   		      WHERE ({$outype->idfieldname}=$oid) and (shortname='$shortname') and (datemodified={$rkp->datemodified})";

	 		   if ($recsss = get_record_sql($strsql)) 	{
	 		   	  error(get_string('errorinduplicatedformcreate','block_monitoring'), $redirlink);
	 		   }

		       if (!$idnew = insert_record('monit_rating_listforms', $rkp))	{
					error(get_string('errorincreatinglist','block_monitoring'), $redirlink);
			   }

		       $rec->listformid = $idnew;


		       if (!$idform = insert_record('monit_form_'.$shortname, $rec))	{
		            print_object($rec);
					error(get_string('errorincreatingform','block_monitoring'), $redirlink);
			   }
			   
			   $totalmark = calculate_dou_mark($yid, $rid, $oid, $idform, $shortname, '', $outype);
			   // echo $totalmark . '!!!';
		        // notice(get_string('succesavedata','block_monitoring'), );
			   redirect($redirlink, get_string('succesavedata','block_monitoring'), 30);

		    } else {  // update records

		       $rec->listformid = $fid;
		       $df = get_record_sql("SELECT id, listformid FROM {$CFG->prefix}monit_form_$shortname WHERE listformid=$fid");
		       $rec->id = $df->id;

               // print_object($rec);
		       if (!update_monit_record('monit_form_'.$shortname, $rec))	{
		       		print_object($rec);
					error(get_string('errorinupdatingform','block_monitoring'), $redirlink);
			   }
			   $totalmark = calculate_dou_mark($yid, $rid, $oid, $rec->id, $shortname, '', $outype);
			   // echo $totalmark; 
		       // notice(get_string('succesupdatedata','block_monitoring'), $redirlink);
		       redirect($redirlink, get_string('succesupdatedata','block_monitoring'), 30);
		    }
            
		}
	}

    if ($fid != 0)  {
    	$rec = get_record('monit_form_'.$shortname, 'listformid', $fid);
       // print_r($rec);
    }

    // print_r($rec);
   	$yearedu = get_record('monit_years', 'id', $yid);
    $namess = explode('/', $yearedu->name);
	$streduname = $namess[0];//$yearedu->name;	
    
    $strnamemonth = "Отчетный период $streduname год.";// get_string('periodreport', 'block_monitoring', $streduname);
    // $strnamemonth = 'За отчетный период.';
	print_heading($strrating.': '.$dou->name, "center", 3);
	print_heading($strformname.'.<br>'.$strnamemonth, "center", 4);

    print_simple_box_start("center");
    // include("$shortname.php");
    // include("end_of_forms.html");
    print_rating_htmlforms($rid, $oid, $yid, $fid, $nm, $shortname, $typeou);
    
  	print_simple_box_end();

    print_footer();




function print_rating_htmlforms($rid, $oid, $yid, $fid, $nm, $shortname, $typeou)
{
	global $CFG, $USER, $rec;
	
	?>	
    <form name="form_rating_dou" enctype="multipart/form-data" method="post" action="htmlforms.php">
	<input type="hidden" name="rid" value="<?php echo $rid ?>" />
	<input type="hidden" name="oid" value="<?php echo $oid ?>" />
	<input type="hidden" name="fid" value="<?php echo $fid ?>" />
	<input type="hidden" name="nm" value="<?php echo $nm ?>" />
	<input type="hidden" name="yid" value="<?php echo $yid ?>" />
	<input type="hidden" name="sn" value="<?php echo $shortname ?>" />
    <input type="hidden" name="typeou" value="<?php echo $typeou ?>" />
	<input type="hidden" name="sesskey" value="<?php echo $USER->sesskey ?>">
	<!-- <table class="formtable" cellpadding="5"> -->
	<table border="1" cellspacing="2" cellpadding="5" align="center" bordercolor=black>
	<tr>
		<th valign="top" nowrap="nowrap" ><?php print_string('symbolnumber', 'block_monitoring') ?></th>
		<th valign="top" nowrap="nowrap" ><?php print_string('nameofpokazatel', 'block_monitoring') ?></th>
		<th valign="top" nowrap="nowrap" ><?php print_string('valueofpokazatel', 'block_monitoring') ?></th>
	</tr>
	<?php

  get_name_otchet_year ($yid, $a, $b);
	
  $razdel = get_record ('monit_razdel', 'shortname', $shortname);
  
  if (isset($rec)) {
	  $arrec = (array)$rec;
  }
  // print_r($razdel);
  if ($razdel)	{
        $sql = "SELECT rf.* FROM mdl_monit_razdel_field rf
                inner join mdl_monit_razdel_field_year rfy on rf.id=rfy.razdelfieldid
                where rfy.yearid=$yid and rf.razdelid=$razdel->id";
  		$fields = get_records_sql($sql);
          // get_records ('monit_razdel_field', 'razdelid', $razdel->id);
 	    // print_r($fields);
  		if ($fields) {
  		    $num_I = 0;  $num_II = 1;
            foreach ($fields as $field)  {
            	eval("\$fieldname = \"$field->name\";");
            	switch ($field->edizm)	{
            		case 'null': $num_I++; $num_II = 1;
            					 echo '<tr valign="top">';
								 echo "<TD>$num_I</TD>";
   								 echo "<TD><B>$fieldname</B></TD>";
							     echo '<TD></TD></tr>';
            		break;

                    
                    case 'days':
                    case 'hour':
					case 'man': 
            		case 'item':
                    case 'unit':
            		case 'ball':
            		case 'proc':
                    case 'year':
								echo '<tr valign="top">';
								if (isset($field->name_field)) {
									$_num = translitfield('f'.$field->name_field);
								} else {
									$_num = $num_I.$num_II;
								}
								echo "<td align=left>$_num</td>";
                                /*
                                if ($field->name_field == 'fku_20') {
                                    $strudostovrdocs = get_string('udostovrdocs', 'block_mou_att');
                                    $ffurl = "$CFG->wwwroot/file.php/1/spravka_503.pdf";
                                    $fieldname .= ' &nbsp;<a href="'.$ffurl.'" >'.$strudostovrdocs.'</a>';
                                    
                                }
                                */
							    echo "<td align=left>$fieldname</td>";
							    echo "<td align=left> <input type=text name=$field->name_field size=5 maxlength=7 ";
								if (isset($err[$field->name_field])) {
									echo 'style="border-color:#FF0000"';
								}
								echo 'value=';
								if (isset($arrec[$field->name_field]) &&  $arrec[$field->name_field]> 0) {
									echo $arrec[$field->name_field];
								} else {
								    echo '0';
                                 /*   
                                    // DEBUGING
                                    if ($field->name_field == 'fn_1' || $field->name_field == 'fo_1')   {
                                        echo '100';
                                    } else {
                                        echo rand(2, 99);
                                    }
                                 */   
                                }
                                //////////////////////////
								$stredizm = get_string($field->edizm, 'block_monitoring');
								echo '>&nbsp;' . $stredizm;
								echo '</td></tr>';
								$num_II++;
            		break;
            		
            		case 'trub': case 'rub':
								echo '<tr valign="top">';
								$_num = translitfield('f'.$field->name_field);
								echo "<td align=left>$_num</td>";
							    echo "<td align=left>$fieldname</td>";
							    echo "<td align=left> <input type=text name=$field->name_field size=5 maxlength=12 ";
								if (isset($err[$field->name_field])) {
									echo 'style="border-color:#FF0000"';
								}
								echo 'value=';
								if (isset($arrec[$field->name_field]) &&  $arrec[$field->name_field]> 0) {
									echo $arrec[$field->name_field];
								}
								echo '>&nbsp;' . get_string($field->edizm, 'block_monitoring');
								echo '</td></tr>';
								$num_II++;
            		break;
            		
            		case 'bool': 
					  		    $yes = get_string('yes');
					  		    $no  = get_string('no');
								$_num = translitfield('f'.$field->name_field);
								echo '<tr valign="top">';
								echo "<td align=left>$_num</td>";
							    echo "<td align=left>$fieldname</td>";
							    echo "<td align=left><select size=1 name={$field->name_field}>";
								if (isset($arrec[$field->name_field]) &&  $arrec[$field->name_field] == 0) {
							 	   echo '<option selected value="0">--</option>';
							 	}  else {
							 	   echo '<option value="0">--</option>';
							 	}
								if (isset($arrec[$field->name_field]) &&  $arrec[$field->name_field] == 1) {
						   		   echo '<option selected value="1">'.$yes.'</option>';
						   		}  else {
						   		   echo '<option value="1">'.$yes.'</option>';
						   		}
								if (isset($arrec[$field->name_field]) &&  $arrec[$field->name_field] == -1) {
						   		   echo '<option selected value="-1">'.$no.'</option>';
						   		}  else {
						   		   echo '<option value="-1">'.$no.'</option>';
						   		}
								echo '</select>';
								echo '</td></tr>';
								$num_II++;
            		break;
                    
                    case 'three': 
								$_num = translitfield('f'.$field->name_field);
								echo '<tr valign="top">';
								echo "<td align=left>$_num</td>";
							    echo "<td align=left>$fieldname</td>";
							    echo "<td align=left><select size=1 name={$field->name_field}>";
								if (isset($arrec[$field->name_field]) &&  $arrec[$field->name_field] == 0) {
							 	   echo '<option selected value="0">0</option>';
							 	}  else {
							 	   echo '<option value="0">0</option>';
							 	}
								if (isset($arrec[$field->name_field]) &&  $arrec[$field->name_field] == 1) {
						   		   echo '<option selected value="1">1</option>';
						   		}  else {
						   		   echo '<option value="1">1</option>';
						   		}
								if (isset($arrec[$field->name_field]) &&  $arrec[$field->name_field] == 2) {
						   		   echo '<option selected value="2">2</option>';
						   		}  else {
						   		   echo '<option value="2">2</option>';
						   		}
								echo '</select>';
								echo '</td></tr>';
								$num_II++;
            		break;
            		
            		
            		default:   notify('Unknown edizm:'. $field->edizm);
            		
            	}
            }
  		}
  }
  echo '</table>';
  if (!isregionviewoperator() && !israyonviewoperator())  {
    /*
    	$filearea = "1/school/$oid/$shortname";
        if ($basedir = make_upload_directory($filearea))   {
            if ($files = get_directory_list($basedir)) {
                require_once($CFG->libdir.'/filelib.php');
                $output = '';
                foreach ($files as $key => $file) {
                    $icon = mimeinfo('icon', $file);
                    if ($CFG->slasharguments) {
                        $ffurl = "$CFG->wwwroot/file.php/$filearea/$file";
                    } else {
                        $ffurl = "$CFG->wwwroot/file.php?file=/$filearea/$file";
                    }
    
                    $output .= '<img align="middle" src="'.$CFG->pixpath.'/f/'.$icon.'" class="icon" alt="'.$icon.'" />'.
                            '<a href="'.$ffurl.'" >'.$file.'</a><br />';
                }
            } else {
            	$output = '(' . get_string('isabscent', 'block_mou_att') . ')';
            }
        }
    */
        $name1 = 'doc_'.$oid.'_'.$shortname;

    // $CFG->maxbytes = MAX_SCAN_COPY_SIZE; 

    $struploadafile = get_string('loadfiledocs', 'block_mou_att');
    $strmaxsize = get_string("maxsize", "", display_size($CFG->maxbytes));

    
 	 echo '<table border=0 align=center><tr valign="top">';
     echo '<tr><td align=center colspan=2><br>';
   	 echo "<p>$struploadafile ($strmaxsize):";
	 helpbutton('uploadmanual', $struploadafile, 'mou');
	 echo "</p>";

     echo '<input type="hidden" name="MAX_FILE_SIZE" value="'. $CFG->maxbytes .'" />'."\n";
     echo '<input type="file" size="70" name="'. $name1 .'" alt="'. $name1 .'" />'."\n";

   
	echo '</td></tr><tr><td align=center colspan=2>';

	print_string('loadedfiledocs', 'block_mou_att');
	$filearea = "1/school/$oid/$shortname";
    $delurlparam = "rid=$rid&oid=$oid&yid=$yid&typeou=$typeou&sn=$shortname&nm=$nm&fid=$fid";    
    
    $output = show_upload_files ($filearea, $delurlparam);
//    echo '<div class="files">'.$output.'</div>';
  	echo $output;
    
     echo '</td></tr><tr><td align=center><br><input type="submit" value="';     
	 print_string('savechanges');
	 echo '" /></td></form><td align="center"><br>';
	 $options = array('rid' => $rid, 'oid' => $oid, 'yid' => $yid, 'fid' => $fid,  'nm' => $nm,  'sesskey' => $USER->sesskey);
    print_single_button("listforms.php", $options, get_string("revert"));
    echo '</td></tr></table>';
  }
}


  

?>