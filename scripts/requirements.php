<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="de" lang="de">
<head>
<meta http-equiv="content-type" content="text/html; charset=iso-8859-1" />
<meta name="language" content="de" />
<meta name="MSSmartTagsPreventParsing" content="true" />
<meta http-equiv="imagetoolbar" content="no" /> 
<title>Welcompose Requirements</title>

<style type="text/css">
* {
margin: 0;
padding: 0;
}
html, body {
border: 0 none;
}
body {
font: 95% Arial, Helvetica, sans-serif;
color: #333;
background: #d9d9d9;
padding: 12px 0 0 0;
text-align: center;
}
h1, h2 {
font-weight: normal;
}
h2 {
color: #666;
}
h1 {
font-size: 2em;
margin: 40px 0 15px 22px;
}
h2, p, a, li, th, td {
font-size: 0.80em;
}
p a, li a, th a, td a {
font-size: 100%;
}
a:link, a:visited {
color: #333;
text-decoration: none;
background: transparent;
}
a:hover {
background: transparent;
text-decoration: underline;
}
a:active, a:focus {
color: #fff;
background: #ff620d;
}
#container {
position: relative;
width: 768px;
min-height: 600px;
background: #fff;
margin: 0 auto;
text-align: left;
}
#container p.copyright {
width: 723px;
padding: 5px 5px 25px 5px;
margin: 50px 20px 0 20px;
border-top: 1px solid #ebebeb;
}
.fright {
float: right;
width: 200px;
text-align: right;
}
#header {
width: 768px;
height: 70px;
background: #cbcbcb;
}

/* LOGO START */
#logo {
position: absolute;
top: 30px;
left: 575px;
width: 200px; 
padding-top: 3px;
}
#logo p {
color: #fff;
background: #0c3;
padding-left: 7px;
padding-bottom: 3px;
font-size: 2em;
background: transparent;
}
/* LOGO STOP */

#content {
margin: 19px;
width: 730px;
}
#content p, #content h2, table {
margin: 0 120px 15px 22px;
line-height: 130%;
}
#content h2 {
margin: 30px 0 15px 22px;
line-height: 100%;
font-size: 1em;
}

/* LICENCE */
#licence {
width: 680px;
height: 350px;
margin: 10px 10px 10px 14px;
overflow: auto;
}
#licence p {
padding: 0 0 8px 0;
margin: 0;
font-size: 0.80em;
}
#licence ol li ol li {
font-size: 100%;
list-style-type: lower-latin;
padding: 8px 0 0 0;
}
#licence ol {
list-style-position: inside;
list-style-type: decimal;
padding: 0 0 5px 0;
}
#licence ol li {
padding: 0 0 8px 0;
}
#licence h2 {
padding: 0 0 8px 0;
margin: 0;
}
#licence span {
text-decoration: underline;
}
/* LICENCE EOF */

.marker_warning,
.marker_error,
.marker_fine {
text-align: right;
}
.marker_fine {
color: #0c3;
}
.marker_error {
color: #f00;
}
.marker_warning {
color: #f90;
}
.marker_fine {
color: #0c3;
}

table {
border-collapse: collapse;
}
table th {
padding: 0 0 5px 0;
}
table td {
padding: 4px 0 4px 0;
border-bottom: 1px solid #efefef;
}
th, td {
text-align: left;
}
table img {
vertical-align: bottom;
}
table td.extension,
table td.software {
width: 335px;
font-weight: bold;
}
table td.status {
width: 250px;
text-align: right;
font-weight: bold;
}
table td.status_indicator {
width: 80px;
text-align: left;
}
table td.status_indicator_text {
width: 300px;
}

/* CLEARFIX */
.clearfix:after {
content: "."; 
display: block; 
height: 0; 
clear: both; 
visibility: hidden;
}
/* Hides from IE-mac \*/
* html .clearfix { 
height: 1%; 
}
/* End hide from IE-mac */
</style>
</head>

<body>
<div id="container">
<div id="header"> 
<div id="logo">
<p>Welcompose</p>
<!-- logo --></div>
<!-- header --></div>

<div id="content">

<h1>Requirements</h1>
<p>Please make sure that your webspace meets all the requirements for running Welcompose. If any errors or
warnings occur, please consult the install manual how to fix it. There are instructions how to tell you
webspace provider about it too.</p>

<p><strong>Attention</strong>: Please note that we're only checking the capabilities of PHP here. This test does not
check if your database is new enough.</p>

<?php
// initialize error counter
$error_counter = 0;

// prepare array of required extensions
$extensions = array(
	'gettext',
	'pdo',
	'pdo_mysql',
	'gd',
	'xml',
	'simplexml',
	'dom',
	'session',
	'pcre'
);
sort($extensions);

// let's see if all required extensions are loaded
$extension_statuses = array();
foreach ($extensions as $_extension) {
	if (extension_loaded($_extension)) {
		$extension_statuses[$_extension] = array(
			'text' => 'OK',
			'marker' => 'fine'
		);
	} else {
		$extension_statuses[$_extension] = array(
			'text' => 'Not installed',
			'marker' => 'error'
		);
		
		// increment error counter
		$error_counter++;
	}
}

// let's see if up-to-date software is available
$software_statuses = array();

// php versions
if (version_compare(phpversion(), '5.0.3', '<')) {
	$software_statuses['PHP '.phpversion()] = array(
		'text' => 'Too old',
		'marker' => 'error'
	);
	
	// increment error counter
	$error_counter++;
} elseif (version_compare(phpversion(), '5.0.3', '>=') && version_compare(phpversion(), '5.1.3', '<')) {
	$software_statuses['PHP '.phpversion()] = array(
		'text' => 'May cause troubles',
		'marker' => 'warning'
	);
} elseif (version_compare(phpversion(), '5.1.3', '>=')) {
	$software_statuses['PHP '.phpversion()] = array(
		'text' => 'OK',
		'marker' => 'fine'
	);
}

// gd versions
$gd_info = gd_info();
if (!preg_match("=2\.[0-9]+=", $gd_info['GD Version'])) {
	$software_statuses['GD Version '.$gd_info['GD Version']] = array(
		'text' => 'Too old',
		'marker' => 'error'
	);
	
	// increment error counter
	$error_counter++;
} elseif (!preg_match("=bundled=i", $gd_info['GD Version'])) {
	$software_statuses['GD Version '.$gd_info['GD Version']] = array(
		'text' => 'May cause troubles, use the bundled one',
		'marker' => 'warning'
	);
} else {
	$software_statuses['GD Version '.$gd_info['GD Version']] = array(
		'text' => 'OK',
		'marker' => 'fine'
	);
}

// pdo versions
if (!defined("PDO::ATTR_EMULATE_PREPARES")) {
	$software_statuses['pdo'] = array(
		'text' => 'Update to PDO 1.0.3 and pdo_mysql 1.0.2; or install PHP 5.1.3 or higher',
		'marker' => 'error'
	);
	
	// increment error counter
	$error_counter++;
} else {
	$software_statuses['pdo'] = array(
		'text' => 'OK',
		'marker' => 'fine'
	);
}

?>

<h2>Checking for available extensions</h2>
<table>
<tr>
<td class="extension">Extension</td>
<td class="status">Status</td>
</tr>
<?php foreach ($extension_statuses as $_extension => $_status) { ?>
<tr>
<td>
<?php echo htmlspecialchars($_extension); ?>
</td>
<td class="marker_<?php echo htmlspecialchars($_status['marker']); ?>">
<?php echo htmlspecialchars($_status['text']); ?>
</td>
</tr>
<?php } ?>
</table>

<h2>Checking for up-to-date versions</h2>
<table>
<tr>
<td class="software">Software</td>
<td class="status">Status</td>
</tr>
<?php foreach ($software_statuses as $_software => $_status) { ?>
<tr>
<td>
<?php echo htmlspecialchars($_software); ?>
</td>
<td class="marker_<?php echo htmlspecialchars($_status['marker']); ?>">
<?php echo htmlspecialchars($_status['text']); ?>
</td>
</tr>
<?php } ?>
</table>

<h2>Legend</h2>
<table>
<tr>
<td class="status_indicator marker_error">Red</td>
<td class="status_indicator_text">Welcompose won't work</td>
</tr>
<tr>
<td class="status_indicator marker_warning">Orange</td>
<td class="status_indicator_text">Welcompose may work -- don't blame us for errors</td>
</tr>
<tr>

<td class="status_indicator marker_fine">Green</td>
<td class="status_indicator_text">Everything's fine</td>
</tr>
</table>

<!-- content --></div>

<p class="copyright">
<span class="fright">Licensed below the terms of the <a href="http://www.opensource.org/licenses/osl-3.0.php">Open 
Software License 3.0</a></span>
&copy; 2006 - 2007 <a href="http://www.welcompose.com/"><strong>Welcompose<sup>&reg;</sup></strong></a> Requirements Tester for 0.8 &ndash; powered by <a href="http://www.sopic.com/"><strong>Sopic<sup>&reg;</sup></strong></a>
</p><!-- container --></div>
</body>
</html>