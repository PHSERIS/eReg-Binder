<?php

##
# © 2015 Partners HealthCare System, Inc. All Rights Reserved. 
##

/**
 * PLUGIN NAME:
 * DESCRIPTION:
 * VERSION: 1.0
 * AUTHOR: Dimitar Dimitrov
 */

// Call the REDCap Connect file in the main "redcap" directory
require_once "../../redcap_connect.php";

// Display the project header
require_once APP_PATH_DOCROOT . 'ProjectGeneral/header.php';
require_once "common_functions.php";

// Enter your project ID HERE
$target_proj_ids = array();

// If the Project ID is not setup, then we can't continue
if(!isset($_GET['pid']) || !is_numeric($_GET['pid'])) {    
    exit('Project ID is missing! Cannot continue!');
}

// If the project ID is not in the allowed array, then return 
//if(!in_array($_GET['pid'], $target_proj_ids)) {
  //  exit('This project is not allowed to view this report!');
//}

/**
 * Display the drop-down list of the available records - completed records
 */
$all_forms = array_keys($Proj->forms);
$first_form_name = $all_forms[0];
$completed_records_query = "select record,field_name,value from redcap_data where project_id=".$_GET['pid']." and field_name='".$first_form_name."_complete' and value=2";
$q = db_query($completed_records_query);
$select_record = "<form method='GET' action='{$_SERVER['PHP_SELF']}' >\n";
if($_GET['rid'] == -2) $heading = "All Records";
else $heading = "record ".$_GET['rid'];
$select_record .= "<div style='width: 100%; text-align:center; font-weight: bold; font-size: 22px;'>Delegation Log ".(isset($_GET['rid']) ? " for ".$heading : "") ."</div></p>";
$select_record .= "<input type='hidden' id='pid' name='pid' value='".$_GET['pid']."'/>";
$select_record .= "<select name='rid' id='rid' onchange='submit();'><option value=-1>-- select record --</option>\n";
$select_record .= "<option value=-2 ".($_GET['rid'] == -2 ? "selected" : "") .">ALL RECORDS</option>\n";
$records_list = "";
$records_list_array = array();
while ($row = db_fetch_assoc($q)) {
	$records_list .= ",'".$row['record']."'";
	$records_list_array[$row['record']] = $row['record'];
	if(isset($_GET['rid']) && is_numeric($_GET['rid']) && $_GET['rid'] == $row['record']) 
		$select_record .= "<option value=".$row['record']." selected>Record ".$row['record']."</option>\n";
	else 
		$select_record .= "<option value=".$row['record'].">Record ".$row['record']."</option>\n";
}
unset($q);
$select_record .= "</select></form></p>\n";
print $select_record;

/**
 * This will display a table based on the currently selected record
 */

if(isset($_GET['rid']) && is_numeric($_GET['rid'])) {
    // Select the label for HRC Review
    // Then select the Document Type
    // Then select the Document Number
	$selected_rids = "";
	if($_GET['rid'] == -1) $selected_rids = "";
	else if($_GET['rid'] == -2) $selected_rids = substr($records_list,1);
	else {
		$selected_rids = $_GET['rid'];
		unset($records_list_array); // we want only one record here
		$records_list_array[$_GET['rid']] = $_GET['rid'];
	}
		
    $sql_labels = "select record,field_name,value from redcap_data where project_id=".$_GET['pid']." and record in (".$selected_rids.")".
	    " and field_name in ('reviewid','doctype','docnumber')";
    $q = db_query($sql_labels);
    $titles = array();
    while($row = db_fetch_assoc($q)) {
	$titles[$row['field_name']."_".$row['record']] = parse_element_enum($row['value'],$Proj->metadata[$row['field_name']]['element_enum']);
    }
    unset($q);
    
    // Loop through the answers for the stafftask1 question and save them in a array with the KEY being the key and the answer being the payload
    //$staff_tasks_raw = explode('\\n',$Proj->metadata['stafftask1']['element_enum']);
    //$staff_tasks_raw = str_replace('-','\\n',$Proj->metadata['taskstaff1']['element_preceding_header']);
    //$staff_tasks_raw = str_replace('\r','\\n',$staff_tasks_raw);
    //$staff_tasks_raw = trim(str_replace('Delegation of Responsibility','',$staff_tasks_raw));
    //$staff_tasks_raw = explode('\\n', $staff_tasks_raw);
    $staff_tasks = array();
    $staff_tasks[1] = '1'; //'Obtain informed consent';
    $staff_tasks[2] = '2'; //'Obtain medical history';
    $staff_tasks[3] = '3'; //'Perform physical exam';
    $staff_tasks[4] = '4'; //'Assess eligibility criteria';
    $staff_tasks[5] = '5'; //'Administer study drug/device';
    $staff_tasks[6] = '6'; //'CRF completion';
    $staff_tasks[7] = '7'; //'CRF queries';
    $staff_tasks[8] = '8'; //'Query completion';
    $staff_tasks[9] = '9'; //'Maintain Regulatory Docs';
    $staff_tasks[10] = '10'; //'Maintain IRB documents';
    $staff_tasks[11] = '11'; //'Data monitoring';
    $staff_tasks[12] = '12'; //'Safety monitoring';
    $staff_tasks[13] = '13'; //'Other';	
    $staff_tasks[14] = 'E-Signature';
$image_view_page	= APP_PATH_WEBROOT . "DataEntry/image_view.php?pid=".$_GET['pid']; 
$image_id = $Proj->metadata['imgtasks']['edoc_id'];
$img_src = "$image_view_page&id=$image_id";
$record_entry           = APP_PATH_WEBROOT . "DataEntry/index.php?pid=".$_GET['pid']."&page=".urlencode($first_form_name);
print "<img src='$image_view_page&id=$image_id'>";
	/**foreach($staff_tasks_raw as $row) {
		$answer_value = trim(substr($row, 0, strpos($row,'.')));
		$answer_label = trim(substr($row, strpos($row,'.')+1));
		$staff_tasks[$answer_value] = $answer_label;
	}*/

	// form the table header
	$headers = RCView::th(array('class'=>'header', 'style'=>'text-align:center;color:#800000;padding:5px 10px;vertical-align:bottom;'),"Record");
	//$headers .= RCView::th(array('class'=>'header', 'style'=>'text-align:center;color:#800000;padding:5px 10px;vertical-align:bottom;'),$Proj->metadata['doctype']['element_preceding_header']);
	//$headers .= RCView::th(array('class'=>'header', 'style'=>'text-align:center;color:#800000;padding:5px 10px;vertical-align:bottom;'),$Proj->metadata['doctype']['element_label']);
	//$headers .= RCView::th(array('class'=>'header', 'style'=>'text-align:center;color:#800000;padding:5px 10px;vertical-align:bottom;'),$Proj->metadata['docnumber']['element_label']);
	$headers .= RCView::th(array('class'=>'header', 'style'=>'text-align:center;color:#800000;padding:5px 10px;vertical-align:bottom;'),'Staff Name');
	$headers .= RCView::th(array('class'=>'header', 'style'=>'text-align:center;color:#800000;padding:5px 10px;vertical-align:bottom;'),'Staff Title');
	$headers .= RCView::th(array('class'=>'header', 'style'=>'text-align:center;color:#800000;padding:5px 10px;vertical-align:bottom;'),'Start Date');
	$headers .= RCView::th(array('class'=>'header', 'style'=>'text-align:center;color:#800000;padding:5px 10px;vertical-align:bottom;'),'End Date');
	$protocolnumber = '';
	$protocoltitle = '';
	foreach($staff_tasks as $row) {
		$headers .= RCView::th(array('class'=>'header', 'style'=>'text-align:center;color:#800000;padding:5px 10px;vertical-align:bottom;'),$row);
	}

	$rpt_table_rows = RCView::thead('', RCView::tr('', $headers));
	$doc_rows = '';

	// eSigs
	$eSigs = array(); // Record => Signer
        $sig_sql = "select e.record, e.project_id, e.event_id, e.username, e.timestamp, u.user_firstname, u.user_lastname from redcap_esignatures e, redcap_user_information u
                                        where e.project_id = " . $_GET['pid'] . " and e.username = u.username";
        $sig_q = db_query($sig_sql);
	while ( $row = db_fetch_assoc($sig_q) ) {
		$eSigs[$row['record']] = "<img src='" . APP_PATH_IMAGES . "tick_shield.png' class='imgfix'><br />E-Signed by ".$row['username'].
				" (".$row['user_firstname']." ".$row['user_lastname'].")<br /> on ".$row['timestamp'];
	}
	unset($sig_q);

	foreach(array_keys($records_list_array) as $rec_id_row) { // BIGGEST LOOP
	
	// Then select all of the staff names for the "Staff Documents" form
	$sql_numstaff = "select record,field_name,value from redcap_data where project_id=".$_GET['pid']." and record in (".$rec_id_row.")".
                " and field_name in ('numstaff')";
	$result_numstaff = db_query($sql_numstaff);
	$number_of_staff = 0;
	while($ns_row = db_fetch_assoc($result_numstaff)) {
		$number_of_staff = $ns_row['value'];
	}
	//$doc_rows = '';
	for($staff_n = 1; $staff_n<=$number_of_staff; $staff_n++) {
	// Loop through all of the staff members and the defined documents matrix and put check markes for the ones that have been selected
	$sql_docs = "select record,field_name,value from redcap_data where project_id=".$_GET['pid']." and record in (".$rec_id_row.")".
		" and field_name in ('namestaff".$staff_n."')";
	$q = db_query($sql_docs);
	
	while($row = db_fetch_assoc($q)) {
		if(!isset($row['value']) || strlen($row['value'])<1) continue; // we don't want to show empty names
		if(strlen($protocolnumber)<=1 || strlen($protocoltitle)<=1) {
		    $sql_title_number = "select record,field_name,value from redcap_data where project_id=".$_GET['pid']." and field_name in ('protocolnumber','protocoltitle')";
		    $title_number_q = db_query($sql_title_number);
		    while ( $tq = db_fetch_assoc($title_number_q) ) {
			if($tq['field_name'] == 'protocolnumber') {
                                if(strlen($protocolnumber)<=1) $protocolnumber = $tq['value'];
                        }
                        if($tq['field_name'] == 'protocoltitle') {
                                if(strlen($protocoltitle)<=1) $protocoltitle = $tq['value'];
                        }
		    }
		}
		$doc_r = '';
		$doc_r .= RCView::td(array('class'=>'data', 'style'=>'text-align:center;padding:5px 10px;vertical-align:center;'),
			RCView::a(array('href'=>$record_entry.'&id='.$row['record'], 'style'=>'color:#800000;vertical-align:middle;text-decoration:underline;font-weight:bold;'), $row['record']));
		//$doc_r .= RCView::td(array('class'=>'data', 'style'=>'text-align:center;padding:5px 10px;vertical-align:center;'),$Proj->metadata['doctype']['element_preceding_header']);
		//$doc_r .= RCView::td(array('class'=>'data', 'style'=>'text-align:center;padding:5px 10px;vertical-align:center;'),$titles['doctype_'.$rec_id_row]);
		//$doc_r .= RCView::td(array('class'=>'data', 'style'=>'text-align:center;padding:5px 10px;vertical-align:center;'),$titles['docnumber_'.$rec_id_row]);
		$doc_r .= RCView::td(array('class'=>'data', 'style'=>'text-align:center;padding:5px 10px;vertical-align:center;'),$row['value']);
		
		$sql_title = "select record,field_name,value from redcap_data where project_id=".$_GET['pid']." and record in (".$rec_id_row.")".
                		" and field_name in ('titlestaff".$staff_n."_rev')";
		$q_title = db_query($sql_title);
		$staff_title = '';
		while ( $t_row = db_fetch_assoc($q_title)) {
			$staff_title = $t_row['value'];
		}
		$doc_r .= RCView::td(array('class'=>'data', 'style'=>'text-align:center;padding:5px 10px;vertical-align:center;'),parse_element_enum ($staff_title, $Proj->metadata['titlestaff1_rev']['element_enum']));
		unset($q_title);

		$sql_start_date = "select record,field_name,value from redcap_data where project_id=".$_GET['pid']." and record in (".$rec_id_row.")".
                                " and field_name in ('startdatestaff".$staff_n."')";
		$q_start_date = db_query($sql_start_date);
		$staff_start_date = '';
                while ( $t_row = db_fetch_assoc($q_start_date)) {
                        $staff_start_date = $t_row['value'];
                }
		if(strlen($staff_start_date)>0)
		    $staff_start_date = date ( 'm/d/Y', strtotime($staff_start_date));
		$doc_r .= RCView::td(array('class'=>'data', 'style'=>'text-align:center;padding:5px 10px;vertical-align:center;'),$staff_start_date);
                unset($q_start_date);

		$sql_end_date = "select record,field_name,value from redcap_data where project_id=".$_GET['pid']." and record in (".$rec_id_row.")".
                                " and field_name in ('enddatestaff".$staff_n."')";
                $q_end_date = db_query($sql_end_date);
                while ( $t_row = db_fetch_assoc($q_end_date)) {
                        $staff_end_date = $t_row['value'];
                }
		$doc_r .= RCView::td(array('class'=>'data', 'style'=>'text-align:center;padding:5px 10px;vertical-align:center;'),$staff_end_date);
                unset($q_end_date);
		
		//$doc_r .= RCView::td(array('class'=>'data', 'style'=>'text-align:center;padding:5px 10px;vertical-align:center;'),'start date');
		//$doc_r .= RCView::td(array('class'=>'data', 'style'=>'text-align:center;padding:5px 10px;vertical-align:center;'),'end date');


		$st = array();
		// get the staff tasks for this employee
		$check_sql = "select record,field_name,value from redcap_data where project_id=".$_GET['pid']." and record in (".$rec_id_row.")".
                                        " and field_name in ('taskstaff".$staff_n."')";
                $check_result = db_query($check_sql);
                while($check_row = db_fetch_assoc($check_result)) {
			$st[$check_row['value']] = $check_row['value'];
                }
		unset($check_result);

		// Loop through the staff tasks
		foreach(array_keys($staff_tasks) as $answer_key) {
			if($staff_tasks[$answer_key] === 'E-Signature') {
				if(isset($eSigs[$row['record']])) {
					$doc_r .= RCView::td(array('class'=>'data', 'style'=>'text-align:center;padding:5px 10px;vertical-align:center;'),
                                  	      $eSigs[$row['record']]
	                                );
				}
				else {
					$doc_r .= RCView::td(array('class'=>'data', 'style'=>'text-align:center;padding:5px 10px;vertical-align:center;'),
                                  		""
                                	);
				}
			}
			else {
			if(isset($st[$answer_key])) {			
				$doc_r .= RCView::td(array('class'=>'data', 'style'=>'text-align:center;padding:5px 10px;vertical-align:center;'),
					"<img src='".APP_PATH_IMAGES."tick.png'/>"
				);	
			}
			else {
				$doc_r .= RCView::td(array('class'=>'data', 'style'=>'text-align:center;padding:5px 10px;vertical-align:center;'),
                                        ""
                                );
			}
			}
		} // end small loop
		$doc_rows .= RCView::tr('',$doc_r);
	} // end medium loop
	} // end big loop
	} // end BIGGEST LOOP
	$rpt_table_rows .= $doc_rows;

	$prt_table_complete = RCView::table(array('id'=>'record_status_table', 'class'=>'form_border'), $rpt_table_rows);
		
	$subtitle = RCView::h2(array('class'=>'subtitle', 'style'=>'text-align:left; font-size:15px;color:#800000'), 
		'Protocol Title: '.$protocoltitle);
	$subtitle .= RCView::h2(array('class'=>'subtitle', 'style'=>'text-align:left; font-size:15px;color:#800000'), 
		'Protocol Number: '.$protocolnumber);
	$subtitle .= RCView::img(array('src'=>APP_PATH_IMAGES.'printer.png','class'=>'imgfix')) . 
		"<a href='javascript:;' style='font-size:11px;' onclick=\"window.print();\">{$lang['graphical_view_15']}</a>";	

	print $subtitle;
	print '<p/>';
	
	print $prt_table_complete;
}

// Display the project footer
require_once APP_PATH_DOCROOT . 'ProjectGeneral/footer.php';
