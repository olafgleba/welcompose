<?php

if (isset($renderer)) {
	$renderer->setRequiredTemplate('
	{if $error}
		{$label}<span style="color:red;">*</span>
	{else}
		{if $required}
			{$label}<span class="required">*</span>
		{else}
			{$label}
		{/if}      
	{/if}
	');
} elseif (isset($RENDERER)) {
	$RENDERER->setRequiredTemplate('
	{if $error}
		{$label}<span style="color:red;">*</span>
	{else}
		{if $required}
			{$label}<span class="required">*</span>
		{else}
			{$label}
		{/if}      
	{/if}
	');	
}
?>