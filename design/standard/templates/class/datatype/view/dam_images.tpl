{def
	$options = $class_attribute.data_text4|explode('-')
	$required_options = array( 'Image only', 'Image and alt text' )
}


<div class="block clearfix">
	<div style="float: left; margin-right: 30px;">
		<label>{'Required options'|i18n( 'design/standard/class/datatype ')}:</label>
		<p>{$required_options[ $options.3 ]}</p>
	</div>

	<div style="float: left; margin-right: 30px;">
		<label>{'Max file size'|i18n( 'design/standard/class/datatype ')}:</label>
		<p>{$options.0}</p>
	</div>

	<div style="float: left; margin-right: 30px;">
		<label>{'Allow multiple images'|i18n( 'design/standard/class/datatype ')}:</label>
		<p>{$options.1}</p>
	</div>

	<div style="float: left; margin-right: 30px;">
		<label>{'Auto upload'|i18n( 'design/standard/class/datatype ')}:</label>
		<p>{$options.2}</p>
	</div>
</div>
