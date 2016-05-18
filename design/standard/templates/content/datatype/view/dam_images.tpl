{if is_unset( $image_alias )}
	{def $image_alias = ''}
{/if}
{if is_unset( $image_ratio_identifier )}
	{def $image_ratio_identifier = 'standard'}
{/if}

{def $image_path = $attribute|image_url( $image_alias, $image_ratio_identifier )}

{if $attribute.has_content}
	{foreach $attribute.content as $entry}
		<img src="{$image_path}" alt="" />
	{/foreach}
{/if}
