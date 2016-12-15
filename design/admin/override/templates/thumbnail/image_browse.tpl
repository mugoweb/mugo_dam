{def
	$preview_image_alias = ezini( 'Preview', 'PreviewImages', 'mugo_dam.ini' )
	$image_alias = $preview_image_alias[ 'Standard' ]
}

{* lookup dam_images datatype *}
{def $attribute_name=''}
{foreach $node.data_map as $attribute}
	{if eq( $attribute.data_type_string, 'dam_images' )}
		{set $attribute_name=$attribute.contentclass_attribute_identifier}
		{break}
	{/if}
{/foreach}

<div class="content-view-thumbnail">
	{if $show_link}
		{if $attribute_name}
			{attribute_view_gui
				attribute=$node.data_map[ $attribute_name ]
				image_alias=$image_alias
				href=concat( '/content/browse/', $node.node_id )|ezurl()
			}
		{/if}
	{else}
		{if $attribute_name}
			{attribute_view_gui
				attribute=$node.data_map[ $attribute_name ]
				image_alias=$image_alias
			}
		{/if}
	{/if}
</div>
