{if is_unset( $image_class )}
	{def $image_class = ''}
{/if}

{def $base_url = ezini( 'Base', 'DamBaseUrl', 'mugo_dam.ini' )}
{if $attribute.has_content}
	{foreach $attribute.content as $entry}
		<img src="{$base_url}{$entry}{if $image_class}?alias={$image_class}{/if}" alt="" />
	{/foreach}
{/if}
