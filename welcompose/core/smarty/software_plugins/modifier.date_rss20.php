<?php

foreach ($smarty->plugins_dir as $value) { 
    $filepath = $value ."/shared.make_timestamp.php"; 
    if (file_exists($filepath)) { 
        require_once $filepath; 
    } 
}

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
