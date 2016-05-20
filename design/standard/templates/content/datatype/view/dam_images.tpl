{if is_unset( $image_alias )}
	{def $image_alias = ''}
{/if}
{if is_unset( $image_ratio_identifier )}
	{def $image_ratio_identifier = 'standard'}
{/if}

{if $attribute.has_content}
	{foreach $attribute.content as $ratio}
		<img src="{$ratio.url|image_url( $image_alias, $image_ratio_identifier )}" alt="{$ratio.alt|wash()}" />
	{/foreach}
{/if}
