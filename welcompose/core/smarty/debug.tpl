{capture name='_smarty_debug' assign=debug_output}
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
<head>
    <title>Smarty Debug Console</title>
<style type="text/css">
{literal}
body, h1, h2, td, th, p {
    font-family: sans-serif;
    font-weight: normal;
    font-size: 1em;
    margin: 0;
    padding: 0;
}

h1, h2, td, th, p {
	    padding: 20px;
}

h1 {
    margin: 0;
		padding: 10px 20px;
		font-size: 100%!important;
    text-align: left;
		color: #333!important;
    background-color: #EFEFEF;
    color:  black;
    font-weight: bold;
    font-size: 1.2em;
 }

h2 {
    background-color: #fff;
		padding: 10px 20px 12px 20px;
		font-size: 80%;
    color: #333;
    text-align: left;
    font-weight: bold;
    border-top: 1px solid #fff;
}

body {
    background: #fff; 
}

p, table, div {
    background: #fff;
} 

p {
    margin: 0;
    font-style: italic;
    text-align: center;
}

table {
    width: 100%;
		background-color: #fff;
		border-collapse: collapse;
}

th, td {
    font-family: monospace;
    vertical-align: top;
    text-align: left;
    width: 70%;
}
th {
		width: 30%;
		border-right: 1px solid #fff;
}

td {
    color: #333;
}

.odd {
    background-color: #eeeeee;
}

.even {
    background-color: #F9F9F9;
}

.exectime {
    font-size: 0.8em;
    font-style: italic;
}

#table_assigned_vars th {
    color: #FF620D;
}
{/literal}
</style>
</head>
<body>

<h1>Welcompose Smarty Debug Console for :  {if isset($template_name)}{$template_name|debug_print_var nofilter}{else}Total Time {$execution_time|string_format:"%.5f"}{/if}</h1>

{if !empty($template_data)}
<h2>included templates &amp; config files (load time in seconds)</h2>

<div>
{foreach $template_data as $template}
  {$template.name}
  <span class="exectime">
   (compile {$template['compile_time']|string_format:"%.5f"}) (render {$template['render_time']|string_format:"%.5f"}) (cache {$template['cache_time']|string_format:"%.5f"})
  </span>
  <br>
{/foreach}
</div>
{/if}

<h2>assigned template variables</h2>

<table id="table_assigned_vars">
    {foreach $assigned_vars as $vars}
       <tr class="{if $vars@iteration % 2 eq 0}odd{else}even{/if}">   
       <th>${$vars@key|escape:'html'}</th>
       <td>{$vars|debug_print_var nofilter}</td></tr>
    {/foreach}
</table>

<h2>assigned config file variables (outer template scope)</h2>

<table id="table_config_vars">
    {foreach $config_vars as $vars}
       <tr class="{if $vars@iteration % 2 eq 0}odd{else}even{/if}">   
       <th>{$vars@key|escape:'html'}</th>
       <td>{$vars|debug_print_var nofilter}</td></tr>
    {/foreach}

</table>
</body>
</html>
{/capture}
<script type="text/javascript">
{$id = $template_name|default:''|md5}
    _smarty_console = window.open("","console{$id}","width=768,height=640,resizable,scrollbars=yes");
    _smarty_console.document.write("{$debug_output|escape:'javascript' nofilter}");
    _smarty_console.document.close();
</script>
