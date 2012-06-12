{function name=qt}
{strip}
	{if $el["required"]}
		{if $el["error"]}
			<span class="req">*</span>
		{else}
			<span>*</span>
		{/if}
	{/if}
{/strip}
{/function}