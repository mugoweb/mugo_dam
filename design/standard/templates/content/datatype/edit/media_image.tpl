{ezcss_require( 'media_image.css' )}
{ezscript_require( 'jquery.tocanvas.js' )}

{if is_unset( $attribute_base )}
	{def $attribute_base='ContentObjectAttribute'}
{/if}

{* options are
    0 : max file size in MB
    1 : allow multiple files
    2 : auto upload
*}
{def $options        = $attribute.contentclass_attribute.data_text4|explode('-')
     $max_size       = $options.0
     $allow_multiple = $options.1
     $auto_upload    = $options.2
}

{def $image_url = false()
     $ratios = ezini( 'Base', 'ImageRatios', 'mugo_dam.ini' )}


<div id="ezp-attribute-id-{$attribute.contentclassattribute_id}">

	{foreach $ratios as $id => $label}
		{set $image_url = first_set( $attribute.content[ $id ], false() )}


		<div class="tocanvas">

			<input 
				class="storage"
				type="hidden"
				name="{$attribute_base}_media_image_{$attribute.id}[{$id}]"
				value="{$image_url}" />

			<h1>{$label}</h1>

			<div class="current-image">
				{if $image_url}
					<img data-original="{$image_url}?alias=original"
						 src="{$image_url}?alias=standard_300x200"
						 alt=""
						 data-alias="standard_300x200" />
				{else}
					<img src="data:image/gif;base64,R0lGODlhAQABAAD/ACwAAAAAAQABAAACADs%3D" alt="" />
				{/if}
			</div>

			<div class="dropbox">
				<canvas width="300" height="200"></canvas>

				<div class="select-image">
					<h2>Select new image</h2>
					<div class="fromurl">
						<input type="hidden" value="" />
					</div>

					<div class="fromdisk">
						<input type="file" name="files[]" />
					</div>

				</div>
				
				<div class="upload-image">
					<h2>Upload image</h2>
					<button class="upload">Upload</button>
					<button class="cancel-upload">Cancel</button>
				</div>
			</div>

			<div style="clear: both"></div>
		</div>
	{/foreach}
</div>

<script>
	$(function()
	{ldelim}
		$( '#ezp-attribute-id-{$attribute.contentclassattribute_id} .tocanvas' ).toCanvas(
		{ldelim}
			upload_service      : '{ezini( 'Base', 'UploadServiceUrl', 'mugo_dam.ini' )}',
			from_remote_service : '{ezini( 'Base', 'FromRemoteServiceUrl', 'mugo_dam.ini' )}',
		{rdelim});
	{rdelim});
</script>
