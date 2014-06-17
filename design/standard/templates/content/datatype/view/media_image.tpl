{if is_unset( $alias )}
	{def $alias = ''}
{/if}

{if $attribute.has_content}
	{foreach $attribute.content as $entry}
		<img src="{$entry}?alias={$alias}" title="" />
	{/foreach}
{/if}
