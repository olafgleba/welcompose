<?php /* Smarty version 2.6.14, created on 2007-02-12 16:00:33
         compiled from database.html */ ?>
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
$this->_smarty_include(array('smarty_include_tpl_file' => "_inc_navigation.html", 'smarty_include_vars' => array('on' => 'database')));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>

<!-- header --></div>

<div id="content">

<h1>Database</h1>

<p>Now, it's time to update your database structure. The update script has
determined which modifications have to be done. As soon as you press on
<em>Start</em>, the database update process will begin. Please note that the
update scripts may ask you some questions. Answer them to complete the update
process.</p>

<h2>Update tasks</h2>
<table>
<tr>
<td class="extension">Task</td>
<td class="status">Status</td>
</tr>
<?php $_from = $this->_tpl_vars['tasks']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['task'] => $this->_tpl_vars['file']):
?>
<tr>
<td><?php echo $this->_tpl_vars['task']; ?>
</td>
<td>pending</td>
</tr>
<?php endforeach; endif; unset($_from); ?>
</table>

<!-- content --></div>

<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "_inc_footer.html", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
<!-- container --></div>
</body>
</html>