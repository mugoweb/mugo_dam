{def $options = $class_attribute.data_text4|explode('-')}

<div class="block">
	<label>{'Max file size'|i18n( 'design/standard/class/datatype ')}:</label>
	<p>{$options.0}</p>

	<label>{'Allow multiple images'|i18n( 'design/standard/class/datatype ')}:</label>
	<p>{$options.1}</p>
	
	<label>{'Auto upload'|i18n( 'design/standard/class/datatype ')}:</label>
	<p>{$options.2}</p>
</div>
