{def $default_alias = ezini( 'ImageSettings', 'DefaultEmbedAlias', 'content.ini' )}

{attribute_view_gui attribute=$object.data_map.images image_alias=$default_alias}

<p>
     <b>{$object.name|wash()}</b> -
     {if $object.data_map.caption.has_content}
          {attribute_view_gui attribute=$object.data_map.caption}
     {/if}
</p>