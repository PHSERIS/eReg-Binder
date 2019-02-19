<?php

##
# © 2019 Partners HealthCare System, Inc. All Rights Reserved. 
##

function parse_element_enum ( $field_name_value_needle, $element_enum_haystack ) {
	if(!isset($field_name_value_needle) || strlen($field_name_value_needle)<1) return "N/A"; // Default error is to return empty
	if(!isset($element_enum_haystack)) return "N/A"; // Default Error for missing answers string

	$tmp_explode = explode("~",$field_name_value_needle);
	$needles = array();
	foreach( array_keys($tmp_explode) as $key) {
		$needles[trim($tmp_explode[$key])] = trim($tmp_explode[$key]);
	}

	$first_explode = explode('\n', $element_enum_haystack);	
	$answer_choices = array();
	for($i=0; $i<count($first_explode); $i++) {
		$comma_index = strpos($first_explode[$i], ',',0);
		$answer_value = trim(substr($first_explode[$i], 0, $comma_index));
		$answer_human_readable = trim(substr($first_explode[$i], $comma_index + 1));
		
		$answer_choices[$answer_value] = $answer_human_readable;
	}

	$str_to_return = "";
	foreach( array_keys($needles) as $value ) {
		$str_to_return .= $answer_choices[$value]."</br>";
	}
	return $str_to_return;
}
