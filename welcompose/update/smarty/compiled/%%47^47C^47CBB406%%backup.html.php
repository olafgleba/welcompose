<?php /* Smarty version 2.6.14, created on 2007-02-11 16:36:55
         compiled from backup.html */ ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="de" lang="de">
 <head>
  <meta http-equiv="content-type" content="text/html; charset=iso-8859-1" />
  <meta name="language" content="de" />
  <meta name="MSSmartTagsPreventParsing" content="true" />
  <meta http-equiv="imagetoolbar" content="no" /> 
  <title>Welcompose Update</title>
  <link rel="stylesheet" type="text/css" href="static/styles/setup.css" media="screen, projection" />

<script type="text/javascript" src="static/libs/thirdparty/prototype.js"></script>
<script type="text/javascript" src="static/libs/thirdparty/scriptaculous.js"></script>
<script type="text/javascript" src="static/libs/thirdparty/behaviours.js"></script>
<script type="text/javascript" src="parse/parse.js.php?file=wcom.setup.strings.js"></script>
<script type="text/javascript" src="static/libs/wcom.setup.core.js"></script>
<script type="text/javascript" src="static/libs/wcom.setup.helper.js"></script>
<script type="text/javascript" src="static/libs/wcom.setup.events.js"></script>
<script type="text/javascript" src="static/libs/wcom.setup.validation.js"></script>

</head>

<body>
<div id="container">

<div id="header"> 

<div id="logo">
<p>Welcompose Update</p>
<!-- logo --></div>

<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "_inc_navigation.html", 'smarty_include_vars' => array('on' => 'backup')));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>

<!-- header --></div>

<div id="content">

<h1>Backup</h1>

<p>Before continuing the update process, we <strong>strongly</strong>
recommend to perform a <strong>backup of your database</strong>. This can be
easily done with third-party applications like phpMyAdmin. If you don't know
how to perform a backup, the upgrading guide in the Welcompose manual will
help you.</p>

<p>Second, we recommend to take a <strong>copy of the whole Welcompose
installation</strong>. That may help you doing restores if something goes
wrong during the update process (like a server crash).</p>

<div class="linkcon">
<a href="database.php" class="submit200">I created a backup.</a>
</div>

<!-- content --></div>

<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "_inc_footer.html", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
<!-- container --></div>
</body>
</html>