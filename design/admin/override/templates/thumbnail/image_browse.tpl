<div class="content-view-thumbnail">
{if $show_link}
    {attribute_view_gui attribute=$node.data_map.images image_alias='standard_132x88' href=concat( '/content/browse/', $node.node_id )|ezurl}
{else}
    {attribute_view_gui attribute=$node.data_map.images image_alias='standard_132x88'}
{/if}
</div>
