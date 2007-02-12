<?php /* Smarty version 2.6.14, created on 2007-02-11 16:21:26
         compiled from requirements.html */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'escape', 'requirements.html', 73, false),)), $this); ?>
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
$this->_smarty_include(array('smarty_include_tpl_file' => "_inc_navigation.html", 'smarty_include_vars' => array('on' => 'requirements')));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>

<!-- header --></div>

<div id="content">

<h1>Requirements</h1>
<p>Please make sure that your webspace meets all the requirements. If any errors or warnings occur,
please consult the install manual how to fix it. There are instructions how to tell you webspace
provider about it too.</p>

<p><strong>Attention</strong>: Please note that we're only checking the capabilities of PHP here. This test does not
check if your database is new enough.</p> 

<?php if (! empty ( $this->_tpl_vars['form']['errors'] )): ?>
<div id="error">
<ul class="req">
<?php $_from = $this->_tpl_vars['form']['errors']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['error']):
?>
	<li><?php echo $this->_tpl_vars['error']; ?>
</li>
<?php endforeach; endif; unset($_from); ?>
</ul>
<!-- error --></div>
<?php endif; ?>

<form class="botbg"<?php echo $this->_tpl_vars['form']['attributes']; ?>
>
<?php echo $this->_tpl_vars['form']['javascript']; ?>


<fieldset class="topbg">

<?php echo $this->_tpl_vars['form']['hidden']; ?>


<h2>Checking for available extensions</h2>
<table>
<tr>
<td class="extension">Extension</td>
<td class="status">Status</td>
</tr>
<?php $_from = $this->_tpl_vars['extension_statuses']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['_extension'] => $this->_tpl_vars['_status']):
?>
<tr>
<td>
<?php echo ((is_array($_tmp=$this->_tpl_vars['_extension'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'htmlall') : smarty_modifier_escape($_tmp, 'htmlall')); ?>

</td>
<td class="marker_<?php echo ((is_array($_tmp=$this->_tpl_vars['_status']['marker'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'htmlall') : smarty_modifier_escape($_tmp, 'htmlall')); ?>
">
<?php echo ((is_array($_tmp=$this->_tpl_vars['_status']['text'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'htmlall') : smarty_modifier_escape($_tmp, 'htmlall')); ?>

</td>
</tr>
<?php endforeach; endif; unset($_from); ?>
</table>

<h2>Checking for up-to-date versions</h2>
<table>
<tr>
<td class="software">Software</td>
<td class="status">Status</td>
</tr>
<?php $_from = $this->_tpl_vars['software_statuses']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['_software'] => $this->_tpl_vars['_status']):
?>
<tr>
<td>
<?php echo ((is_array($_tmp=$this->_tpl_vars['_software'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'htmlall') : smarty_modifier_escape($_tmp, 'htmlall')); ?>

</td>
<td class="marker_<?php echo ((is_array($_tmp=$this->_tpl_vars['_status']['marker'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'htmlall') : smarty_modifier_escape($_tmp, 'htmlall')); ?>
">
<?php echo ((is_array($_tmp=$this->_tpl_vars['_status']['text'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'htmlall') : smarty_modifier_escape($_tmp, 'htmlall')); ?>

</td>
</tr>
<?php endforeach; endif; unset($_from); ?>
</table>

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

<?php if ($this->_tpl_vars['error_counter'] == 0): ?>
<div class="linkcon">
<a href="backup.php" class="submit200">Go to next step</a>
</div>
<?php endif; ?>

</fieldset>
</form>

<!-- content --></div>

<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "_inc_footer.html", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
<!-- container --></div>
</body>
</html>