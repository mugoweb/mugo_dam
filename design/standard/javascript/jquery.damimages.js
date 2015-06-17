;(function ( $, window, document, undefined )
{

	var pluginName = 'damimages',
	    pluginElement = null,
		defaults = {
		};

	function Plugin( element, options )
	{
		pluginElement = element;
		this.element = element;
		this.options = $.extend( {}, defaults, options) ;
		this._defaults = defaults;
		this._name = pluginName;

		this.init();
	}
			
	Plugin.prototype =
	{
		init : function()
		{
			var self = this;

			self.updatePreview();
		},
		
		updatePreview : function()
		{
			var self = this;
			
			var uploadAliasTopPreviewAliasMap =
			{
				standard_300x200 : 'standard_218x145',
				cinema_288x154   : 'cinema_288x154',
				square_50x50     : 'square_50x50',
			};

			var previewFieldset = $(self.element).find( 'fieldset.preview' );

			// clear previews
			previewFieldset.find( 'img' ).attr( 'src', '' );
			previewFieldset.find( 'span' ).html( '' );
			
			// fill previews
			var uploadImages = $(self.element).find( '.tocanvas img' );
			$.each( uploadImages, function()
			{
				var uploadAlias = $(this).attr( 'data-alias' );
				var targetAlias = uploadAliasTopPreviewAliasMap[ uploadAlias ];

				var target = previewFieldset.find( 'li[data-alias="'+ targetAlias +'"]' );

				// Check if preview is mapped
				if( target.length )
				{
					// Check if upload image is there
					if( $(this).attr( 'data-base' ) )
					{
						// load image
						var previewUrl = $(this).attr( 'data-base' ) + '?alias=' + targetAlias;
						target.find( 'img' ).attr( 'src', previewUrl );

						// add custom crop info
						target.find( 'span' ).html( '(custom crop)' );
					}
				}
			});

			// fill remaining preview - auto crops
			var standardImg = $(self.element).find( '.tocanvas img[data-alias="standard_300x200"]' );
			
			if( standardImg.attr( 'data-base' ) )
			{
				$.each( previewFieldset.find( 'img' ), function()
				{
					// Check if it's empty
					if( $(this).attr( 'src' ) === '' )
					{
						// load image
						var previewUrl = standardImg.attr( 'data-base' ) + '?alias=' + $(this).closest( 'li' ).attr( 'data-alias' );
						$(this).attr( 'src', previewUrl );

						// add auto crop info
						$(this).parent().find( 'span' ).html( '(auto crop)' );

					}
				});
			}
		},
	};

	$.fn[pluginName] = function ( options ) {
		var args = arguments;

		if (options === undefined || typeof options === 'object') {
			return this.each(function ()
			{
				if (!$.data(this, 'plugin_' + pluginName)) {

					$.data(this, 'plugin_' + pluginName, new Plugin( this, options ));
				}
			});

		} else if (typeof options === 'string' && options[0] !== '_' && options !== 'init') {

			var returns;

			this.each(function () {
				var instance = $.data(this, 'plugin_' + pluginName);

				if (instance instanceof Plugin && typeof instance[options] === 'function') {

					returns = instance[options].apply( instance, Array.prototype.slice.call( args, 1 ) );
				}

				// Allow instances to be destroyed via the 'destroy' method
				if (options === 'destroy') {
					$.data(this, 'plugin_' + pluginName, null);
				}
			});

			return returns !== undefined ? returns : this;
		}
	};

}(jQuery, window, document));