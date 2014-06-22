{if is_unset( $image_class )}
	{def $image_class = ''}
{/if}

{if $attribute.has_content}
	{foreach $attribute.content as $entry}
		<img src="{$entry}?alias={$image_class}" title="" />
	{/foreach}
{/if}
