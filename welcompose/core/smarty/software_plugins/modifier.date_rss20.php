<?php

require_once $smarty->_get_plugin_filepath('shared','make_timestamp');

function smarty_modifier_date_rss20($string)
{
	// get current locale
	$current_locale = setlocale(LC_ALL, 0);
	
	// set locale to C to get english dates
	setlocale(LC_ALL, 'C');
	
	$timestamp = smarty_make_timestamp($string);
	
	$date_string = strftime("%a, %d %b %Y %H:%M:%S %z", $timestamp);
	
	// reset locale
	setlocale(LC_ALL, $current_locale);
	
	return $date_string;
}

?>
