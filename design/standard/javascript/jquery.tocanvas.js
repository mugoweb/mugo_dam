;(function ( $, window, document, undefined )
{

	var pluginName = 'toCanvas',
	    pluginElement = null,
		defaults = {
			done : function()
			{
			},
			preview_size : { w : 300, h : 200 },
			upload_service : '',
			from_remote_service : '',
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
	
	function resize( sourceWidth, sourceHeight, targetWidth, targetHeight )
	{
		var ratio = sourceWidth / sourceHeight;
		
		if( ratio * targetHeight > targetWidth )
		{
			return { w : targetWidth, h : ( 1 / ratio ) * targetWidth };
		}
		else
		{
			return { w : ratio * targetHeight, h : targetHeight };
		}
	}
		
	Plugin.prototype =
	{
		init : function()
		{
			var self = this;
			
			// FormData for upload
			var formData = new FormData();
			
			// get canvas context
			var canvasTag = $( self.element ).find( 'canvas' )[0];
			var ctx = canvasTag.getContext( '2d' );
			
			// JS image that gets loaded to the canvas
			var uploadImg = new Image;
			uploadImg.onload = function()
			{
				self.enableUpload();
								
				// calc dimensions if images are big
				var dimensions = { w : uploadImg.width, h : uploadImg.height };
				if( uploadImg.width > self.options.preview_size.w || uploadImg.height > self.options.preview_size.h )
				{
					dimensions = resize( uploadImg.width, uploadImg.height, self.options.preview_size.w, self.options.preview_size.h );
				}
				
				canvasTag.width  = dimensions.w;
				canvasTag.height = dimensions.h;
				$( canvasTag ).attr( 'data-width', uploadImg.width );
				$( canvasTag ).attr( 'data-height', uploadImg.height );
	
				ctx.drawImage( uploadImg, 0, 0, dimensions.w, dimensions.h );
				
				// callback done function
				self.options.done( uploadImg, dimensions );
			};

			// JS file reader
			var fileReader = new FileReader();
			fileReader.onload = function( event )
			{
				uploadImg.src = event.target.result;
			}
			
			// Event on fromUrl input
			$(self.element).find( '.fromurl input' ).on( 'change', function(e)
			{
				var srcUrl = $(self.element).find( '.fromurl input' ).val();
				uploadImg.src = srcUrl;
			});
			
			// Event on file selection
			$(self.element).find( '.fromdisk input' ).change( function(e)
			{
				fileReader.readAsDataURL( e.target.files[0] );
				formData.append( 'files[]', $(self.element).find( '.fromdisk input' ).get(0).files[0] );
			});
			
			// Event on dropping a file
			$(self.element).find( '.dropbox' ).on( 'dragover', function( event )
			{
				event.preventDefault();  
				event.stopPropagation();
				$(this).addClass( 'dragging' );
			});

			$(self.element).find( '.dropbox' ).on( 'dragleave', function( event )
			{
				event.preventDefault();  
				event.stopPropagation();
				$(this).removeClass( 'dragging' );
			});
			
			$(self.element).find( '.dropbox' ).on( 'drop', function( event )
			{
				event.preventDefault();  
				event.stopPropagation();
				
				// Drop from FS or another image
				if( event.originalEvent.dataTransfer.getData( 'Text' ) )
				{
					$(self.element).find( '.fromurl input' ).val( event.originalEvent.dataTransfer.getData( 'Text' ) );
					$(self.element).find( '.fromurl input' ).trigger( 'change' );
					
					formData.append( 'remotefile', event.originalEvent.dataTransfer.getData( 'Text' ) );
				}
				else
				{
					// From FS
					var files = event.originalEvent.dataTransfer.files;
					if( files.length > 0 )
					{
						var i = files.length;
						while( i-- )
						{
							var file = files[ i ];

							// Only handle image files
							if( file.type.indexOf( 'image' ) === -1 ) { continue; }
							fileReader.readAsDataURL( file );

							formData.append( 'files[]', file );
						}
					}
				}
			});
			
			// Start dragging current-image
			$(self.element).find( '.current-image img' ).on( 'dragstart', function(e)
			{
				e.originalEvent.dataTransfer.setData( 'Text', $( e.originalEvent.target ).attr( 'data-original' ) );
			});
			
			// Upload image
			$(self.element).find( '.upload' ).click( function( event )
			{
				formData.append( 'apikey', 'topsecret' );

				var serviceUrl;
				
				// File upload or re-use remote image
				if( $(self.element).find( '.fromurl input' ).val() )
				{
					serviceUrl = self.options.from_remote_service;
				}
				else
				{
					serviceUrl = self.options.upload_service;
				}
				
				if( serviceUrl )
				{
					$.ajax({
						url: serviceUrl,
						type: 'POST',
						success: function( data )
						{
							self.afterUpload( self, data );
						},
						// Form data
						data: formData,
						dataType: 'json',
						//Options to tell jQuery not to process data or worry about content-type.
						cache: false,
						contentType: false,
						processData: false
					});
				}
				else
				{
					alert( 'No service URL configured' );
				}
				
				event.preventDefault();  
				event.stopPropagation();
			});
			
			$(self.element).find( '.cancel-upload' ).click( function( event )
			{
				self.resetUpload();

				event.preventDefault();  
				event.stopPropagation();
			});
		},
				
		afterUpload : function( self, data )
		{
			if( data.files.length )
			{
				var imgTag = $(self.element).find( '.current-image img' );
				
				var imgUrl = data.files[0].url;
				var imgPreviewAliasUrl = imgUrl + '?alias=' + imgTag.attr( 'data-alias' );
				var imgOriginalAliasUrl = imgUrl + '?alias=original';
				
				//console.log( $( pluginElement ) );
				imgTag
					.attr( 'src', imgPreviewAliasUrl )
					.attr( 'data-original', imgOriginalAliasUrl );

				$(self.element).find( '.storage' ).val( imgUrl );
				
				self.resetUpload();
			}
		},
		
		resetUpload : function()
		{
			var self = this;

			// disable upload button
			$( self.element ).find( '.select-image' ).show();
			$( self.element ).find( '.upload-image' ).hide();

			// clear canvas
			var canvasTag = $( self.element ).find( 'canvas' )[0];
			var ctx = canvasTag.getContext( '2d' );
			ctx.clearRect( 0, 0, canvasTag.width, canvasTag.height );
			
			// clear file upload input
			var fileInput = $(self.element).find( '.fromdisk input' );
			fileInput.val('');
			//fileInput.replaceWith( fileInput.val('').clone( true ) );
			
			// clear remote file input
			$(self.element).find( '.fromurl input' ).val( '' );
		},
		
		enableUpload : function()
		{
			var self = this;
			
			$( self.element ).find( '.select-image' ).hide();
			$( self.element ).find( '.upload-image' ).show();
		}
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