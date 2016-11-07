{* options are
    0 : max file size in MB
    1 : allow multiple files
    2 : auto upload
    3 : required options
*}
{def $options = $class_attribute.data_text4|explode('-')}

<div class="block clearfix" style="margin-top: 20px">

	<div style="float: left; margin-right: 30px;">
		<label>{'Required options'|i18n( 'design/standard/class/datatype ')}:</label>
		<select name="ContentClass_dam_images_required_options_{$class_attribute.id}">
			<option value="0">Image</option>
			<option value="1" {if eq( $options.3, 1 )}selected="selected"{/if}>Image and alt text</option>
		</select>
	</div>

	<div style="float: left; margin-right: 30px;">
		<label>{'Max file size'|i18n( 'design/standard/class/datatype ')}:</label>
		<input type="text" name="ContentClass_dam_images_max_filesize_{$class_attribute.id}" value="{$options.0}" size="5" maxlength="5" />&nbsp;MB
	</div>

	<div style="float: left; margin-right: 30px;">
		<label>{'Allow multiple images'|i18n( 'design/standard/class/datatype ')}:</label>
		<input type="checkbox" name="ContentClass_dam_images_allow_multiple_{$class_attribute.id}" value="1" {if $options.1}checked="checked"{/if} />
	</div>

	<div style="float: left; margin-right: 30px;">
		<label>{'Auto upload'|i18n( 'design/standard/class/datatype ')}:</label>
		<input type="checkbox" name="ContentClass_dam_images_auto_upload_{$class_attribute.id}" value="1" {if $options.2}checked="checked"{/if} />
	</div>
</div>
