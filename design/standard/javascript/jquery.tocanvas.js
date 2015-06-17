;(function ( $, window, document, undefined )
{

	var pluginName = 'toCanvas',
	    pluginElement = null,
		defaults = {
			afterUpdate : function()
			{
			},
			preview_size        : { w : 300, h : 200 },
			upload_service      : '',
			from_remote_service : '',
			base_url            : '',
			repository          : '',
			api_key             : 'topsecret',
			object_id           : '',
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

	/**
	 *
	 * @param sourceWidth
	 * @param sourceHeight
	 * @param targetWidth
	 * @param targetHeight
	 * @returns {*}
	 */
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
		formData : null,
		uploadImg : null,
		fileReader : null,

		init : function()
		{
			var self = this;

			// FormData for uploads to image server
			self.formData = new FormData();

			self.initUploadImage();
			self.initFileReader();
			self.initTriggers();
			self.initDragAndDrop();
			
			// start clean
			self.resetUpload();
		},
		
		initTriggers : function()
		{
			var self = this;
						
			// Upload image event
			$(self.element).find( '.upload' ).click( function( event )
			{
				var uploadButton = this;

				$(self.element).find( '.upload-image' ).hide();
				$(self.element).find( '.uploading' ).show();
				
				self.formData.append( 'apikey',     self.options.api_key );
				self.formData.append( 'repository', self.options.repository );

				var serviceUrl = self.getServiceUrl();

				if( serviceUrl )
				{
					$.ajax(
					{
						url: serviceUrl,
						type: 'POST',
						// Form data
						data: self.formData,
						headers: { 'Content-Disposition' : 'filename=' +  encodeURIComponent( self.getFileName( uploadButton ) ) + ';' },
						dataType: 'json',
						//Options to tell jQuery not to process data or worry about content-type.
						cache: false,
						contentType: false,
						processData: false,
						success: function( data )
						{
							self.afterUpload( self, data );
						},
						error: function( data )
						{
							alert( 'Failed to upload image.' );
							self.resetUpload();
						},
					});
				}
				else
				{
					alert( 'No service URL configured' );
				}
				
				event.preventDefault();  
				event.stopPropagation();
			});

			// Event on file selection
			$(self.element).find( '.fromdisk input' ).change( function(e)
			{
				self.fileReader.readAsDataURL( e.target.files[0] );
				self.formData.append( 'files[]', $(self.element).find( '.fromdisk input' ).get(0).files[0] );
			});

			// Event on fromUrl input
			$(self.element).find( '.fromurl input' ).on( 'change', function(e)
			{
				var srcUrl = $(self.element).find( '.fromurl input' ).val();
				self.uploadImg.src = srcUrl;
			});

			// Cancel upload trigger
			$(self.element).find( '.cancel-upload' ).click( function( event )
			{
				self.resetUpload();

				event.preventDefault();  
				event.stopPropagation();
			});
			
			// File select dialog trigger
			$(self.element).find( '.select-image-trigger' ).click( function( event)
			{
				$(self.element).find( '.select-image .fromdisk input' ).click();

				event.preventDefault();  
				event.stopPropagation();
			});
			
			// Remove current image
			$(self.element).find( '.remove-image-trigger' ).click( function( event )
			{
				$(self.element).find( '.current-image img' )
					.attr( 'src', '' )
					.attr( 'data-base', '' );

				$(self.element).find( '.storage' ).val( '' );
				
				event.preventDefault();  
				event.stopPropagation();
				
				self.options.afterUpdate();
			});

		},

		getFileName : function( context )
		{
			var self = this;

			var ratio = $(context ).closest( 'div.tocanvas' ).attr( 'data-ratio' );

			return self.options.object_id + '_'+ self.options.version + '_{{unnamed}}_' + ratio + self.addFileExtension();
		},

		addFileExtension : function()
		{
			var self = this;

			var map =
			{
				'image/png'  : '.png',
				'image/jpg'  : '.jpg',
				'image/jpeg' : '.jpg',
				'image/bmp'  : '.bmp',
				'image/gif'  : '.gif',
			};

			var re = /^data:(.*);/;
			var matches = re.exec( self.fileReader.result );

			if( matches !== null )
			{
				return map[ matches[1] ] || '';
			}
		},

		initDragAndDrop : function()
		{
			var self = this;

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
	
			// Start dragging current-image
			$(self.element).find( '.current-image img' ).on( 'dragstart', function(e)
			{
				e.originalEvent.dataTransfer.setData( 'Text', $( e.originalEvent.target ).attr( 'data-base' ) );
			});
			
			$(self.element).find( '.dropbox' ).on( 'drop', function( event )
			{
				event.preventDefault();  
				event.stopPropagation();
				
				// Check if we already have an image in the upload canvas
				if( $(self.element).find( '.upload-image:hidden' ).length )
				{
					// Drop from FS or another image
					if( event.originalEvent.dataTransfer.getData( 'Text' ) )
					{
						$(self.element).find( '.fromurl input' ).val( event.originalEvent.dataTransfer.getData( 'Text' ) );
						$(self.element).find( '.fromurl input' ).trigger( 'change' );

						self.formData.append( 'remotefile', event.originalEvent.dataTransfer.getData( 'Text' ) + '?alias=original' );
					}
					// From FS
					else
					{
						var files = event.originalEvent.dataTransfer.files;
						if( files.length > 0 )
						{
							var i = files.length;
							while( i-- )
							{
								var file = files[ i ];

								// Only handle image files
								if( file.type.indexOf( 'image' ) === -1 ) { continue; }
								self.fileReader.readAsDataURL( file );

								self.formData.append( 'files[]', file );
							}
						}
					}
				}
			});
		},
		
		/**
		 * The file reader is needed to get the content form the HTML file input.
		 * File reader onloads sets the uploadImg src and triggers its onload function.
		 */
		initFileReader : function()
		{
			var self = this;
			// JS file reader
			self.fileReader = new FileReader();
			self.fileReader.onload = function( event )
			{
				self.uploadImg.src = event.target.result;
			};
		},
		
		initUploadImage : function()
		{
			var self = this;
			
			// JS image that gets loaded to the canvas
			self.uploadImg = new Image();

			// get canvas context
			var canvasTag = $( self.element ).find( 'canvas' )[0];
			var ctx = canvasTag.getContext( '2d' );
			
			self.uploadImg.onload = function()
			{
				self.enableUpload();

				// calc dimensions if images are big
				var dimensions = { w : self.uploadImg.width, h : self.uploadImg.height };
				if( self.uploadImg.width > self.options.preview_size.w || self.uploadImg.height > self.options.preview_size.h )
				{
					dimensions = resize( self.uploadImg.width, self.uploadImg.height, self.options.preview_size.w, self.options.preview_size.h );
				}
				
				canvasTag.width  = dimensions.w;
				canvasTag.height = dimensions.h;
				$( canvasTag ).attr( 'data-width', self.uploadImg.width );
				$( canvasTag ).attr( 'data-height', self.uploadImg.height );
	
				ctx.drawImage( self.uploadImg, 0, 0, dimensions.w, dimensions.h );
			};
		},
		
		afterUpload : function( self, data )
		{
			if( data.files.length )
			{
				// there is only one upload file in the array
				var fileData = data.files[ 0 ];
				
				if( fileData.error )
				{
					alert( 'Error: ' +  fileData.error );
				}
				else
				{
					// sets the string we store in the DB
					$(self.element).find( '.storage' ).val( fileData.urlPath );

					// sets the src and data-base attribute, using the default alias to display the image
					var imgTag = $(self.element).find( '.current-image img' );
					var alias = imgTag.attr( 'data-alias' ) ? '?alias=' + imgTag.attr( 'data-alias' ) : '';

					var imgUrl = this.options.base_url + fileData.urlPath;
					var imgPreviewAliasUrl = imgUrl + alias;
					var imgOriginalAliasUrl = imgUrl;

					imgTag
						.attr( 'src', imgPreviewAliasUrl )
						.attr( 'data-base', imgOriginalAliasUrl );

					// callback afterUpdate function
					self.options.afterUpdate();
				}
				
				self.resetUpload();
			}
		},
		
		resetUpload : function()
		{
			var self = this;

			// disable upload button
			$( self.element ).find( '.select-image' ).show();
			$( self.element ).find( '.upload-image' ).hide();
			$( self.element ).find( '.uploading' ).hide();

			// clear canvas
			var canvasTag = $( self.element ).find( 'canvas' )[0];
			var ctx = canvasTag.getContext( '2d' );
			ctx.clearRect( 0, 0, canvasTag.width, canvasTag.height );
			
			ctx.font="28px Verdana";
			ctx.fillStyle = "#999999";
			ctx.textAlign = "center";
			ctx.fillText( 'Drop image here', 135, 50 );
			ctx.fillText( 'to upload', 135, 90 );
			
			// clear file upload input
			var fileInput = $(self.element).find( '.fromdisk input' );
			fileInput.val('');
			//fileInput.replaceWith( fileInput.val('').clone( true ) );
			
			// clear remote file input
			$(self.element).find( '.fromurl input' ).val( '' );
			
			// reset preview select
			$(self.element).find( '.preview' ).val( '' );
			
			// reset dropbox
			$(self.element).find( '.dropbox' ).removeClass( 'dragging' );
			
			// reset objects
			self.formData = new FormData();
			
			// rest imgObj
			self.uploadImg.src = '';
		},
		
		enableUpload : function()
		{
			var self = this;
			
			$( self.element ).find( '.select-image' ).hide();
			$( self.element ).find( '.upload-image' ).show();
		},
		
		getServiceUrl : function()
		{
			var self = this;
			var serviceUrl = false;

			// File upload or re-use remote image
			if( $(self.element).find( '.fromurl input' ).val() )
			{
				serviceUrl = self.options.from_remote_service;
			}
			else
			{
				serviceUrl = self.options.upload_service;
			}
			
			return serviceUrl;
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