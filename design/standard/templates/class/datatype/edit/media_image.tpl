{* options are
    0 : max file size in MB
    1 : allow multiple files
    2 : auto upload
*}
{def $options = $class_attribute.data_text4|explode('-')}

<div class="block">
	<label>{'Max file size'|i18n( 'design/standard/class/datatype ')}:</label>
	<input type="text" name="ContentClass_media_image_max_filesize_{$class_attribute.id}" value="{$options.0}" size="5" maxlength="5" />&nbsp;MB

	<label>{'Allow multiple images'|i18n( 'design/standard/class/datatype ')}:</label>
	<input type="checkbox" name="ContentClass_media_image_allow_multiple_{$class_attribute.id}" value="1" {if $options.1}checked="checked"{/if} />

	<label>{'Auto upload'|i18n( 'design/standard/class/datatype ')}:</label>
	<input type="checkbox" name="ContentClass_media_image_auto_upload_{$class_attribute.id}" value="1" {if $options.2}checked="checked"{/if} />
</div>
