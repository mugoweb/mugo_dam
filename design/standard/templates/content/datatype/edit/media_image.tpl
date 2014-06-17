{ezcss_require( 'jquery.fileupload-ui.css' )}
{ezscript_require( array(
		'jquery.ui.widget.js',
		'tmpl.min.js',
		'load-image.min.js',
		'canvas-to-blob.min.js',
		'jquery.iframe-transport.js',
		'jquery.fileupload.js',
		'jquery.fileupload-process.js',
		'jquery.fileupload-resize.js',
		'jquery.fileupload-validate.js',
		'jquery.fileupload-ui.js'
		 ) )}

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

{* show data *}
<div class="media-image">
	{* <button id="test">test</button> *}
	<div class="existing-files">
		{if $attribute.has_content}
			{foreach $attribute.content as $entry}
				<div class="file-entry">
					<input id="ezcoa-{if ne( $attribute_base, 'ContentObjectAttribute' )}{$attribute_base}-{/if}{$attribute.contentclassattribute_id}_{$attribute.contentclass_attribute_identifier}" class="ezcc-{$attribute.object.content_class.identifier} ezcca-{$attribute.object.content_class.identifier}_{$attribute.contentclass_attribute_identifier}" type="hidden" name="{$attribute_base}_media_image_{$attribute.id}[]" value="{$entry|wash( xhtml )}" />
					
					<div class="thumbnail">
						<img src="{$entry|wash()}?alias=300x300" />
					</div>
					
					<div class="overlay-top">
						<i class="icon-zoom-in icon-white"></i>
						<i class="icon-remove-sign icon-white pull-right"></i>
					</div>

					<div class="overlay-bottom">
						<small>
							<label>Name:</label>0104-handshake-newscom.jpg<br />
							<label>Size:</label>11.52 KB<br />
						</small>
					</div>
					
				</div>
			{/foreach}
		{/if}
	</div>

	<div style="clear: both"></div>
	
	<div id="fileupload">
		<!-- The fileupload-buttonbar contains buttons to add/delete files and start/cancel the upload -->
		<div class="fileupload-buttonbar">
			<div>
				<!-- The fileinput-button span is used to style the file input field as button -->
				<span class="fileinput-button">
					<span>Drag and drop or <a href="#">select image{if $allow_multiple}s{/if}</a></span>
					<input type="file" name="files[]" {if $allow_multiple}multiple="multiple"{/if} />
				</span>
				{if $auto_upload|not()}
					<button type="submit" class="btn btn-primary start">
						<i class="icon-upload icon-white"></i>
						<span>Start upload</span>
					</button>
					<button type="reset" class="btn btn-warning cancel">
						<i class="icon-ban-circle icon-white"></i>
						<span>Cancel upload</span>
					</button>
					{* we dont' want to delete images from the DAM
					<button type="button" class="btn btn-danger delete">
						<i class="icon-trash icon-white"></i>
						<span>Delete</span>
					</button>
					<input type="checkbox" class="toggle">
					*}
				{/if}
				<!-- The loading indicator is shown during file processing -->
				<span class="fileupload-loading"></span>
			</div>
	
			<!-- The global progress information -->
			<div class="span5 fileupload-progress fade">
				<!-- The global progress bar -->
				<div class="progress progress-success progress-striped active" role="progressbar" aria-valuemin="0" aria-valuemax="100">
					<div class="bar" style="width:0%;"></div>
				</div>
				<!-- The extended global progress information -->
				<div class="progress-extended">&nbsp;</div>
			</div>
		</div>
	
		<!-- The table listing the files available for upload/download -->
		<div class="files"></div>
		<div style="clear: both"></div>
	</div>
</div>

{literal}
<!-- The template to display files available for upload -->
<script id="template-upload" type="text/x-tmpl">
{% for (var i=0, file; file=o.files[i]; i++) { %}
	<div class="template-upload fade file-entry">

		<div class="thumbnail">
			<span class="preview"></span>
		</div>

		<div class="overlay-top">
			<small>
				<p class="name">{%=file.name%}</p>
				{% if (file.error) { %}
					<div><span class="label label-important">Error</span> {%=file.error%}</div>
				{% } %}
				
				<p class="size">{%=o.formatFileSize(file.size)%}</p>
			</small>
		</div>

		<div class="overlay-bottom">
			{% if (!o.files.error) { %}
				<div class="progress progress-success progress-striped active" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="0"><div class="bar" style="width:0%;"></div></div>
			{% } %}

			{% if (!o.files.error && !i && !o.options.autoUpload) { %}
				<button class="btn btn-primary start">
					<i class="icon-upload icon-white"></i>
					<span>Start</span>
				</button>
			{% } %}
			{% if (!i) { %}
				<button class="btn btn-warning cancel">
					<i class="icon-ban-circle icon-white"></i>
					<span>Cancel</span>
				</button>
			{% } %}
		</div>
	</div>
{% } %}
</script>

<!-- The template to display files available for download -->
<script id="template-download" type="text/x-tmpl">
{% for (var i=0, file; file=o.files[i]; i++) { %}
	<div class="template-download fade file-entry">
		<input id="{%=ezpInputFieldDetails.id%}" class="{%=ezpInputFieldDetails.class%}" type="hidden" name="{%=ezpInputFieldDetails.name%}" value="{%=file.url%}" />
		
		{% if (file.url) { %}
		<div class="thumbnail">
			<a href="{%=file.url%}" title="{%=file.name%}" download="{%=file.name%}"><img src="{%=file.url%}?alias=300x300"></a>
		</div>
		{% } %}
		
		<div class="overlay-top">
			<i class="icon-zoom-in icon-white"></i>
			<i class="icon-remove-sign icon-white pull-right">Remove</i>
		</div>

		<div class="overlay-bottom">
			<small>
				<label>Name:</label><a href="{%=file.url%}" title="{%=file.name%}" data-gallery="{%=file.thumbnail_url&&'gallery'%}" download="{%=file.name%}">{%=file.name%}</a><br />
				<label>Size:</label>{%=o.formatFileSize(file.size)%}<br />
			</small>
		</div>
		
		<div style="display: none;">
			<button class="btn btn-danger delete" data-type="{%=file.delete_type%}" data-url="{%=file.delete_url%}"{% if (file.delete_with_credentials) { %} data-xhr-fields='{"withCredentials":true}'{% } %}>
				<i class="icon-trash icon-white"></i>
				<span>Delete</span>
			</button>
			<input type="checkbox" name="delete" value="1" class="toggle">
		</div>
	</div>
{% } %}
</script>
{/literal}


<script type="text/javascript">

var ezpInputFieldDetails = 
{ldelim}
	id	: "ezcoa-{if ne( $attribute_base, 'ContentObjectAttribute' )}{$attribute_base}-{/if}{$attribute.contentclassattribute_id}_{$attribute.contentclass_attribute_identifier}",
	class : "ezcc-{$attribute.object.content_class.identifier} ezcca-{$attribute.object.content_class.identifier}_{$attribute.contentclass_attribute_identifier}",
	name  : "{$attribute_base}_media_image_{$attribute.id}[]"
{rdelim};

var fileUploadOptions =
{ldelim}
	url                   : '{ezini( 'Base', 'DamBaseUrl', 'mugo_dam.ini' )}',
	disableImageResize    : false,
	maxFileSize           : 5000000,
	acceptFileTypes       : /(\.|\/)(gif|jpe?g|png)$/i,
	autoUpload            : {if $auto_upload}true{else}false{/if},
	replaceDownloadedFile : {if $allow_multiple}false{else}true{/if},
	formData              : [ {ldelim} name : 'key', value : '{ezini( 'Base', 'Repository', 'mugo_dam.ini' )}' {rdelim} ],
	previewMaxWidth       : 300,
	previewMaxHeight      : 300
{rdelim}


{literal}

$(function ()
{
    'use strict';
    // Initialize the jQuery File Upload widget:
	$('#fileupload').fileupload(
    {
		// Uncomment the following to send cross-domain cookies:
		//xhrFields: {withCredentials: true},
        url: fileUploadOptions.url
    });

	$('#fileupload').fileupload( 'addEventsToExistingFiles', $( '.file-entry' ) );

	// Enable iframe cross-domain access via redirect option:
	$('#fileupload').fileupload(
		'option',
		'redirect',
		window.location.href.replace(
            /\/[^\/]*$/,
            '/cors/result.html?%s'
        )
    );

    $('#fileupload').fileupload( 'option', fileUploadOptions );
	
	// Upload server status check for browsers with CORS support:
	if ($.support.cors)
	{
		$.ajax(
		{
		    url  : fileUploadOptions.url,
		    type : 'HEAD'
		})
		.fail(function ()
		{
			$('<span class="alert alert-error"/>')
				.text( 'Upload server currently unavailable - ' + new Date() )
				.appendTo('#fileupload');
		});
	}
	
	// Trigger file dialog with JS
	$( '.fileupload-buttonbar a' ).click( function(e)
	{
		$(this).closest( '.fileupload-buttonbar' ).find( 'input[name="files[]"]' ).click();
		
		return false;
	});
	
	// BaseXMS example code
	//$( '#test' ).on( 'click', function(e)
	//{
	//	var data = '<content><name>fips</name><sort>fips</sort><raw>fips <b>test1</b></raw></content>';
	//	$.ajax(
	//	{
	//		type : 'POST',
	//		url  : 'http://localhost/basexms/:REST/node/addwithcontent/1',
	//		data : { data : data }
	//	});
	//	
	//	return false;
	//});
});
{/literal}
</script>
