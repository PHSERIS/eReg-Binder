<?php

##
# © 2019 Partners HealthCare System, Inc. All Rights Reserved. 
##

/**
 * PLUGIN NAME: Delegation Log - eReg Binder Version 2
 * DESCRIPTION:
 * VERSION: 2.0
 * AUTHOR: Dimitar Dimitrov
 *
 * In version 2 of the eReg Binder, the staff is in ARM 2
 *
 */

// Call the REDCap Connect file in the main "redcap" directory
require_once "../../redcap_connect.php";

// Display the project header
require_once APP_PATH_DOCROOT . 'ProjectGeneral/header.php';
require_once APP_PATH_DOCROOT . 'Config/init_functions.php';
//require_once "common_functions.php";

// If the Project ID is not setup, then we can't continue
if(!isset($_GET['pid']) || !is_numeric($_GET['pid'])) {    
    exit('Project ID is missing! Cannot continue!');
}

//$target_proj_ids = array(XXXX,YYYY);
// If the project ID is not in the allowed array, then return 
//if(!in_array($_GET['pid'], $target_proj_ids)) {
  //  exit('This project is not allowed to view this report!');
//}

/**
 * Display the drop-down list of the available records - completed records
 */
// Get the Event IDs for Arm 2
$arm_2_details = $Proj->events[2];
$arm_2_event_ids = array_keys($Proj->events[2]['events']);

$record_entry     = APP_PATH_WEBROOT . "DataEntry/record_home.php?pid=".$Proj->project_id."&arm=2";
$image_view_page  = APP_PATH_WEBROOT . "DataEntry/image_view.php?pid=".$Proj->project_id;

// Get the forms in Arm 2
$targetted_hrc_id_form_menu_name = "HRC ID";
$targetted_hrc_id_form_name = "hrc_id"; // default
$targetted_hrc_id_form_details = array();
$targetted_staff_end_date_form_menu_name = "Staff End Date";
$targetted_staff_end_date_form_name = "staff_end_date"; // default
$targetted_staff_end_date_form_details = array();
$targetted_staff_form_menu_name = "Staff Documents";
$targetted_staff_form_name = "staff_documents"; // a default
$targetted_staff_form_details = array(); // will contain the fields and other things for this form
$arm_2_forms = array($Proj->eventsForms[$arm_2_event_ids[0]]);
foreach ( $Proj->forms as $form_name=>$form_details ) {
  if ( $targetted_staff_form_menu_name === $form_details['menu'] ) {
    $targetted_staff_form_name = $form_name;
    $targetted_staff_form_details = $form_details;
  }
  elseif ( $targetted_hrc_id_form_menu_name === $form_details['menu'] ) {
    $targetted_hrc_id_form_name = $form_name;
    $targetted_hrc_id_form_details = $form_details;
  }
  elseif ( $targetted_staff_end_date_form_menu_name === $form_details['menu'] ) {
    $targetted_staff_end_date_form_name = $form_name;
    $targetted_staff_end_date_form_details = $form_details;
  }
}

// Get all the records that have the staff form completed
$completed_records = REDCap::getData(
  'array',
  array(),
  array($targetted_staff_form_name.'_complete'),
  array($arm_2_event_ids[0]),
  array(), // group
  false, // combine checkboxes
  false, // output DAGs
  false, // output survey fields
  "[".$targetted_staff_form_name."_complete]=2"
);

$extra_record_labels = Records::getCustomRecordLabelsSecondaryFieldAllRecords(array(), true, 2); // for arm 2
if($extra_record_labels)
{
  foreach ($extra_record_labels as $this_record=>$this_label) {
    $dropdownid_disptext[removeDDEending($this_record)] .= " $this_label";
  }
}

$select_record = "<form method='GET' action='{$_SERVER['PHP_SELF']}' >\n";
if($_GET['rid'] == -2) $heading = "All Records";
else $heading = "record ".$_GET['rid'];
$select_record .= "<div style='width: 100%; text-align:center; font-weight: bold; font-size: 22px;'>Delegation Log ".(isset($_GET['rid']) ? " for ".$heading : "") ."</div></p>";
$select_record .= "<input type='hidden' id='pid' name='pid' value='".$Proj->project_id."'/>";
$select_record .= "<select name='rid' id='rid' onchange='submit();'><option value=-1>-- select record --</option>\n";
$select_record .= "<option value=-2 ".($_GET['rid'] == -2 ? "selected" : "") .">ALL RECORDS</option>\n";
foreach ( array_keys($completed_records) as $record_id ) {
  if(isset($_GET['rid']) && $_GET['rid'] == $record_id)
    $select_record .= "<option value=\"".$record_id."\" selected>".$record_id . ($extra_record_labels ? " ".$extra_record_labels[$record_id] : "") ."</option>\n";
  else
    $select_record .= "<option value=\"".$record_id."\">".$record_id . ($extra_record_labels ? " ".$extra_record_labels[$record_id] : "") ."</option>\n";
}
$select_record .= "</select></form></p>\n";
print $select_record;

/**
 * This will display a table based on the currently selected record
 */
if(isset($_GET['rid'])) {
	// form the table header
	$headers = RCView::th(array('class'=>'header', 'style'=>'text-align:center;color:#800000;padding:5px 10px;vertical-align:bottom;'),"Record");
	$headers .= RCView::th(array('class'=>'header', 'style'=>'text-align:center;color:#800000;padding:5px 10px;vertical-align:bottom;'),'Staff Name');
  $headers .= RCView::th(array('class'=>'header', 'style'=>'text-align:center;color:#800000;padding:5px 10px;vertical-align:bottom;'),'Staff Initials');
	$headers .= RCView::th(array('class'=>'header', 'style'=>'text-align:center;color:#800000;padding:5px 10px;vertical-align:bottom;'),'Staff Title');
	$headers .= RCView::th(array('class'=>'header', 'style'=>'text-align:center;color:#800000;padding:5px 10px;vertical-align:bottom;'),'Start Date');
	$headers .= RCView::th(array('class'=>'header', 'style'=>'text-align:center;color:#800000;padding:5px 10px;vertical-align:bottom;'),'End Date');
	$protocolnumber = '';
	$protocoltitle = '';

	// Get the Delegation answer options
  $delegation_field_name = array_search("Delegation",$targetted_staff_form_details['fields']);
  if ( $delegation_field_name == '' || !isset($delegation_field_name) ) {
    print "No Delegation Field found - cannot continue";
    exit(1);
  }
  // Get ome additional fields
  $targetted_fields = array ("Name", "Credentials", "Title", "Initials", "Start Date", "End Date", "Documentation of Training", "Email Address", "Staff signature");
  $targetted_fields_names = array();
  foreach ( $targetted_fields as $tf ) {
    $targetted_fields_names[$tf] = array_search($tf,$targetted_staff_form_details['fields'], true); // strict search
    if ( $targetted_fields_names[$tf] === false ) {
      $targetted_fields_names[$tf] = array_search($tf,$targetted_hrc_id_form_details['fields'], true); // strict search
      if ( $targetted_fields_names[$tf] === false ) {
        $targetted_fields_names[$tf] = array_search($tf,$targetted_staff_end_date_form_details['fields'], true); // strict search
      }
    }
  }

  $delegation_field_array = parseEnum($Proj->metadata[$delegation_field_name]['element_enum']);
  foreach($delegation_field_array as $key => $value ) {
		$headers .= RCView::th(array('class'=>'header', 'style'=>'text-align:center;color:#800000;padding:5px 10px;vertical-align:bottom;'),$value);
	}
	// Add the signature field
  $headers .= RCView::th(array('class'=>'header', 'style'=>'text-align:center;color:#800000;padding:5px 10px;vertical-align:bottom;'),"Staff Signature");

	$rpt_table_rows = RCView::thead('', RCView::tr('', $headers));
	$doc_rows = '';

  // Get the data for the selected records or for all records
  $all_data = array();
  if ( $_GET['rid'] == -2 ) {
    // ALl records
    $all_data = REDCap::getData(
      'array',
      array(),
      array(),
      array($arm_2_event_ids[0]),
      array(), // group
      false, // combine checkboxes
      false, // output DAGs
      false, // output survey fields
      "[".$targetted_staff_form_name."_complete]=2"
    );
  }
  else {
    // Just the selected record
    $all_data = REDCap::getData(
      'array',
      array(is_numeric($_GET['rid']) ? htmlspecialchars($_GET['rid']) : -1),
      array(),
      array($arm_2_event_ids[0]),
      array(), // group
      false, // combine checkboxes
      false, // output DAGs
      false, // output survey fields
      "[".$targetted_staff_form_name."_complete]=2"
    );
  }

  $all_record_rows = '';
  foreach ( $all_data as $record => $event ) {
    $record_row = '';
    foreach ( $event as $eid => $answer_data ) {
      // Record Link
      $record_row .= RCView::td(array('class'=>'data', 'style'=>'text-align:center;padding:5px 10px;vertical-align:center;'),
                      RCView::a(array('href'=>$record_entry.'&id='.$record, 'style'=>'color:#800000;vertical-align:middle;text-decoration:underline;font-weight:bold;'), $record));
      // Name
      $record_row .= RCView::td(array('class'=>'data', 'style'=>'text-align:center;padding:5px 10px;vertical-align:center;'),$answer_data[$targetted_fields_names['Name']]);
      // Initials
      $record_row .= RCView::td(array('class'=>'data', 'style'=>'text-align:center;padding:5px 10px;vertical-align:center;'),$answer_data[$targetted_fields_names['Initials']]);
      // Title
      $titles = parseEnum($Proj->metadata[$targetted_fields_names['Title']]['element_enum']);
      $record_row .= RCView::td(array('class'=>'data', 'style'=>'text-align:center;padding:5px 10px;vertical-align:center;'),$titles[$answer_data[$targetted_fields_names['Title']]]);
      // Start Date
      $record_row .= RCView::td(array('class'=>'data', 'style'=>'text-align:center;padding:5px 10px;vertical-align:center;'),$answer_data[$targetted_fields_names['Start Date']]);
      // End Date (if any)
      $record_row .= RCView::td(array('class'=>'data', 'style'=>'text-align:center;padding:5px 10px;vertical-align:center;'),$answer_data[$targetted_fields_names['End Date']]);
      // Delegation stuff
      foreach ( $answer_data[$delegation_field_name] as $answer_key => $answer_value ) {
        if ( $answer_value == 1 ) {
          $record_row .= RCView::td(array('class'=>'data', 'style'=>'text-align:center;padding:5px 10px;vertical-align:center;'),"<img src='".APP_PATH_IMAGES."tick.png'/>");
        }
        else {
          $record_row .= RCView::td(array('class'=>'data', 'style'=>'text-align:center;padding:5px 10px;vertical-align:center;'),"");
        }
      }
      // eSig
      if( isset($answer_data[$targetted_fields_names['Staff signature']]) && is_numeric($answer_data[$targetted_fields_names['Staff signature']]) && $answer_data[$targetted_fields_names['Staff signature']] >0 ) {
        $signature_img_src = $image_view_page.'&doc_id_hash='.Files::docIdHash($answer_data[$targetted_fields_names['Staff signature']]).'&id='.$answer_data[$targetted_fields_names['Staff signature']]."&page={$targetted_staff_form_name}&record={$record}&event_id={$eid}&field_name=".$targetted_fields_names['Staff signature']."&signature=1";
        $signature_img = "<img src='$signature_img_src' alt='[SIGNATURE]'>";
        $record_row .= RCView::td(array('class'=>'data', 'style'=>'text-align:center;padding:5px 10px;vertical-align:center;'),
            "<div id='$record-sigimg' class='sig-img' style='display:block'>$signature_img</div>"
        );
      }
      else {
        $record_row .= RCView::td(array('class'=>'data', 'style'=>'text-align:center;padding:5px 10px;vertical-align:center;'),
          ""
        );
      }
    }
    $all_record_rows .= RCView::tr('',$record_row);
  }
  $rpt_table_rows .= $all_record_rows;

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
