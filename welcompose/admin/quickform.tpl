{function name=qt}
{strip}
	{if $el["required"]}
		{if !empty($el["error"])}
			<span class="req">*</span>
		{else}
			<span>*</span>
		{/if}
	{/if}
{/strip}
{/function}