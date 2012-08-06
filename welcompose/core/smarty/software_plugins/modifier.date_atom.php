<?php

// Reference to origin smarty plugin to include
require_once SMARTY_DIR.'plugins/shared.make_timestamp.php';

function smarty_modifier_date_atom($string)
{
	// get current locale
	$current_locale = setlocale(LC_ALL, 0);
	
	// set locale to C to get english dates
	setlocale(LC_ALL, 'C');
	
	$timestamp = smarty_make_timestamp($string);
	
	$date = date('Y-m-d', $timestamp);
	$time = date('H:i:s', $timestamp);
	$utc_offset = date('O', $timestamp);
	
	if (substr($utc_offset, 1) == '0000') {
		$utc_offset = 'Z';
	} else {
		$utc_offset = sprintf("%s:%s", substr($utc_offset, 0, 3), substr($utc_offset, 3));
	}
	
	// reset locale
	setlocale(LC_ALL, $current_locale);
	
	return sprintf("%sT%s%s", $date, $time, $utc_offset);
}

?>
