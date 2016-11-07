{default $object_parameters=array()}
{def
	$default_alias = ezini( 'ImageSettings', 'DefaultEmbedAlias', 'content.ini' )
	$attribute_identifier = ezini( 'ImageClass', 'ImageAttributeIdentifier', 'mugo_dam.ini' )
}
{if is_set($object_parameters.size)}
    {set $default_alias=$object_parameters.size}
{/if}

{attribute_view_gui
	attribute=$object.data_map[ $attribute_identifier ]
	image_alias=$default_alias
}

<p>
	<b>{$object.name|wash()}</b> -
	{if $object.data_map.caption.has_content}
		{attribute_view_gui attribute=$object.data_map.caption}
	{/if}
</p>
