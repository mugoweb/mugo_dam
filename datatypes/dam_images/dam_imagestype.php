<?php

/**
 * @author pek
 *
 */
class dam_imagesType extends eZDataType
{
	/** @var bool */
	protected static $backwardsCompatibleMode = false; //older version did not store the image alt text

	const DATA_TYPE_STRING = 'dam_images';

	function __construct()
	{
		parent::__construct( self::DATA_TYPE_STRING, ezpI18n::tr( 'mugo_dam_images/datatype', 'DAM Images', 'Datatype name' ),
			array( 'serialize_supported' => true ) );
	}

	/**
	 * @param $contentObjectAttribute
	 * @param $contentObject
	 * @param $publishedNodes
	 * @return bool
	 */
	function onPublish( $contentObjectAttribute, $contentObject, $publishedNodes )
	{
		// only execute on published versions
		if( $contentObjectAttribute->attribute( 'object_version' )->attribute( 'status' ) == 1 )
		{
			$obj_name = $contentObjectAttribute->attribute( 'object' )->attribute( 'name' );

			if( $obj_name )
			{
				// Check if we should rename image file names
				$ratios = $contentObjectAttribute->attribute( 'content' );

				if( !empty( $ratios ) )
				{
					$dirty = false;
					foreach( $ratios as $key => $ratio )
					{
						if( strpos( $ratio[ 'url' ], '{{unnamed}}' ) )
						{
							$parts = pathinfo( $ratio[ 'url' ] );
							$new_name = str_replace( '{{unnamed}}', $obj_name, $parts[ 'basename' ] );

							$new_path = MugoDamFunctionCollection::rename( $ratio[ 'url' ], $new_name );

							if( $new_path )
							{
								$dirty = true;
								$ratios[ $key ][ 'url' ] = $new_path;
							} else
							{
								eZDebugSetting::writeWarning( 'extension-mugo_dam', 'Failed to rename image.', __METHOD__ );
							}
						}
					}

					if( $dirty )
					{
						$contentObjectAttribute->setAttribute( 'data_text', serialize( $ratios ) );
						$contentObjectAttribute->store();
					}
				}
			} else
			{
				// hmm, is that possible?
			}
		}

		return true;
	}

	/**
	 * returning 'true', nothing else to do
	 *
	 * @param $contentObjectAttribute
	 * @return bool
	 */
	function storeObjectAttribute( $contentObjectAttribute )
	{
		return true;
	}

	/* (non-PHPdoc)
	 * @see eZDataType::validateClassAttributeHTTPInput()
	 */
	function validateClassAttributeHTTPInput( $http, $base, $classAttribute )
	{
		return eZInputValidator::STATE_ACCEPTED;
	}


	/* (non-PHPdoc)
	 * @see eZDataType::objectAttributeContent()
	 */
	function objectAttributeContent( $objectAttribute )
	{
		$return = unserialize( $objectAttribute->attribute( 'data_text' ) );

		if( self::$backwardsCompatibleMode )
		{
			foreach( $return as $ratio => $data )
			{
				if( !is_array( $data ) )
				{
					$return[ $ratio ] = array( 'url' => $data );
				}
			}
		}

		return $return;
	}

	/* (non-PHPdoc)
	 * @see eZDataType::fetchObjectAttributeHTTPInput()
	 */
	function fetchObjectAttributeHTTPInput( $http, $base, $contentObjectAttribute )
	{
		$return = false;

		$tagName = $base . '_dam_images_' . $contentObjectAttribute->attribute( 'id' );

		if( $http->hasPostVariable( $tagName ) )
		{
			$ratios = $http->postVariable( $tagName );

			if( !empty( $ratios ) )
			{
				// clean out empty values - maybe the POST variable should be cleaner
				foreach( $ratios as $ratio => $data )
				{
					if( !trim( $data[ 'url' ] ) )
					{
						unset( $ratios[ $ratio ] );
					}
				}

				// Making sure we don't store an empty serialized array
				$dbString = '';
				if( !empty( $ratios ) )
				{
					$dbString = serialize( $ratios );
				}

				$contentObjectAttribute->setAttribute( 'data_text', $dbString );
				$return = true;
			}
		}

		return $return;
	}


	/**
	 * @param $http
	 * @param $base
	 * @param $classAttribute
	 * @return bool
	 */
	function fetchClassAttributeHTTPInput( $http, $base, $classAttribute )
	{
		// PEK: it's kinda strange: this function gets called when a new content class
		// version get initialized (even before the user hits apply/ok).
		$hasPost = false;

		$options = array(
			'max_filesize' => 0,
			'allow_multiple' => false,
			'auto_upload' => false,
			'required_options' => 0,
		);

		foreach( $options as $key => $value )
		{
			$name = $base . '_dam_images_' . $key . '_' . $classAttribute->attribute( 'id' );
			if( $http->hasPostVariable( $name ) )
			{
				$hasPost = true;
				$options[ $key ] = $http->postVariable( $name );
			}
		}

		if( $hasPost )
		{
			$classAttribute->setAttribute( 'data_text4', implode( '-', $options ) );
		}

		return true;
	}

	/* (non-PHPdoc)
	 * @see eZDataType::hasObjectAttributeContent()
	 */
	function hasObjectAttributeContent( $contentObjectAttribute )
	{
		return trim( $contentObjectAttribute->attribute( 'data_text' ) ) != '';
	}

	/* (non-PHPdoc)
	 * @see eZDataType::validateClassAttributeHTTPInput()
	 */
	public function validateObjectAttributeHTTPInput( $http, $base, $contentObjectAttribute )
	{
		if( $this->validateForRequiredContent( $http, $base, $contentObjectAttribute ) )
		{
			return eZInputValidator::STATE_ACCEPTED;
		}
		else
		{
			return eZInputValidator::STATE_INVALID;
		}
	}

	/**
	 * Verifies a given contentObjectAttribute - so it's not expecting REQUEST parameters
	 * from a http object but uses the current content of contentObjectAttribute to check
	 * if it's valid
	 *
	 * @param eZContentObjectAttribute $contentObjectAttribute
	 * @return boolean
	 */
	public function validateAttribute( $contentObjectAttribute )
	{
		$return = true;

		if( $contentObjectAttribute->validateIsRequired() )
		{
			$return = $this->validateContent(
				$this->objectAttributeContent( $contentObjectAttribute ),
				$contentObjectAttribute
			);
		}

		return $return;
	}

	/**
	 * Stores validation error messages in the $contentObjectAttribute attribute
	 *
	 * @param array $attributeContent
	 * @param eZContentObjectAttribute $contentObjectAttribute
	 * @return bool
	 */
	protected function validateContent( $attributeContent, $contentObjectAttribute )
	{
		$return = false;

		if( !empty( $attributeContent ) )
		{
			$imageFound = false;

			// check if we got at least one image
			foreach( $attributeContent as $ratio )
			{
				if( trim( $ratio[ 'url' ] ) )
				{
					$imageFound = true;

					$requiredOptions = $this->getClassAttributeOptions(
						$contentObjectAttribute->attribute( 'contentclass_attribute' )
					);

					$requireAltText = $requiredOptions[ 3 ] > 0;

					// alt text is required
					if( $requireAltText )
					{
						if( trim( $ratio[ 'alt' ] ) )
						{
							$return = true;
							break;
						}
					}
					else
					{
						$return = true;
						break;
					}
				}
			}

			// Validation failed at this point.
			if( !$imageFound )
			{
				$contentObjectAttribute->setValidationError(
					ezpI18n::tr( 'kernel/classes/datatypes',
						'Image missing.' )
				);
			}
			else
			{
				$contentObjectAttribute->setValidationError(
					ezpI18n::tr( 'kernel/classes/datatypes',
						'Image alternative text missing' )
				);
			}
		}
		else
		{
			$contentObjectAttribute->setValidationError(
				ezpI18n::tr( 'kernel/classes/datatypes',
					'Input required.' )
			);
		}

		return $return;
	}

	/**
	 * Check if attribute is required and has content
	 * 
	 * @param type $http
	 * @param type $base
	 * @param type $contentObjectAttribute
	 */
	protected function validateForRequiredContent( $http, $base, $contentObjectAttribute )
	{
		$return = false;

		if( $contentObjectAttribute->validateIsRequired() )
		{
			$tagName = $base . '_dam_images_' . $contentObjectAttribute->attribute( 'id' );

			if( $http->hasPostVariable( $tagName ) )
			{
				$ratios = $http->postVariable( $tagName );

				$return = $this->validateContent( $ratios, $contentObjectAttribute );
			}
			else
			{
				$contentObjectAttribute->setValidationError(
					ezpI18n::tr( 'kernel/classes/datatypes',
						'Input required.' )
				);
			}
		}
		else
		{
			$return = true;
		}

		return $return;
	}
	
	/**
	 * 
	 * @param type $objectAttribute
	 * @return string
	 */
	public function toString( $objectAttribute )
	{
		return $objectAttribute->attribute( 'data_text' );
	}
	
	/**
	 * Function is a bit overloaded: you can specify the keys 'upload_url' and 'upload_name' which will then upload
	 * the image to the image server.
	 * 
	 * @param type $objectAttribute
	 * @param string $string
	 * @return boolean
	 */
	public function fromString( $objectAttribute, $string )
	{
		$ratios = unserialize( $string );

		if( !empty( $ratios ) )
		{
			foreach( $ratios as $ratio => $data )
			{
				if( self::$backwardsCompatibleMode )
				{
					if( !is_array( $data ) )
					{
						$data = array( 'upload_url' => $data );
					}
				}

				// Upload new image
				if( $data[ 'upload_url' ] )
				{
					if( ! $this->dam_has_image( $data[ 'upload_url' ] ) )
					{
						$url = MugoDamFunctionCollection::uploadToDam( $data[ 'upload_url' ], $data[ 'upload_name' ] );

						if( !$url )
						{
							//Critical issue - failed to upload
							return false;
						}
					}
					else
					{
						$url = $data[ 'upload_url' ];
					}

					// Cleanup overload data
					unset( $data[ 'upload_url' ] );
					unset( $data[ 'upload_name' ] );

					$data[ 'url' ] = $url;
				}

				$ratios[ $ratio ] = $data;
			}
		}
		
		$objectAttribute->setAttribute( 'data_text', serialize( $ratios ) );
		
		return true;
	}

	/**
	 * Not sure if that's really working in all usecases
	 *
	 * @param string $image
	 * @return bool
	 */
	public function dam_has_image( $image )
	{
		$url = self::getImageUrl( $image, null, 'http' );

		$curl = curl_init();
		curl_setopt( $curl, CURLOPT_URL, $url );
		curl_setopt( $curl, CURLOPT_NOBODY, true );
		curl_setopt( $curl, CURLOPT_RETURNTRANSFER, true );
		curl_exec( $curl );
		$info = curl_getinfo( $curl );
		curl_close( $curl );
		
		return $info[ 'http_code' ] == 200;
	}

	/**
	 * Untested
	 * 
	 * @param type $contentObjectAttribute
	 * @return type
	 */
	public function metaData( $contentObjectAttribute )
	{
		return unserialize( $contentObjectAttribute->attribute( 'data_text' ) );
	}
	
	public function isIndexable()
	{
		return true;
	}
	
	/**
	 * Returns an image URL based on the given eZ Attribute, alias, ratio identifier and protocol.
	 *
	 * $protocol can be 'http', 'https', 'none', 'auto'
	 * 
	 * @param eZContentObjectAttribute $contentObjectAttribute
	 * @param string $alias
	 * @param string $image_ratio_identifier
	 * @param string $protocol
	 * @return string
	 */
	public static function getImageUrlByAttribute( $contentObjectAttribute, $alias = null, $image_ratio_identifier = 'standard', $protocol = 'none' )
	{
		$return = false;
		
		if( $contentObjectAttribute instanceof eZContentObjectAttribute )
		{
			if( $contentObjectAttribute->attribute( 'has_content' ) )
			{
				$content = $contentObjectAttribute->attribute( 'content' );

				$image_ratio_identifier = $image_ratio_identifier ? $image_ratio_identifier : 'standard';
				$ratio = isset( $content[ $image_ratio_identifier ] ) ? $content[ $image_ratio_identifier ] : reset( $content );

				if( self::$backwardsCompatibleMode )
				{
					if( !is_array( $ratio ) )
					{
						$ratio = array( 'url' => $ratio );
					}
				}

				if( $ratio[ 'url' ] )
				{
					$return = self::getImageUrl( $ratio[ 'url' ], $alias, $protocol );
				}
			}
		}
		
		return $return;
	}

	/**
	 * $protocol can be 'http', 'https', 'none', 'auto'
	 * 
	 * @param string $image_path
	 * @param string $alias
	 * @param string $protocol
	 * @return string
	 */
	public static function getImageUrl( $image_path, $alias = null, $protocol = 'none' )
	{
		$return = false;
		
		if( $image_path )
		{
			$image_path_encoded = implode( '/', array_map( function($v) { return rawurlencode($v); }, explode( '/', $image_path ) ) );
			// add domain part to url
			$ini = eZINI::instance( 'mugo_dam.ini' );
			$baseDamUrl = $ini->variable( 'Base', 'DamBaseUrl' );
			$image_path = $baseDamUrl . $image_path_encoded;

			// add alias part to url
			$alias_part = $alias ? '?alias=' . $alias : '';
			$image_path .= $alias_part;

			// add protocol to url
			switch( $protocol )
			{
				case 'auto':
				{
					$protocol = eZSys::serverProtocol();
				}
				// do not break

				case 'http':
				case 'https':
				{
					$image_path = $protocol . ':' . $image_path;
				}
				break;
			}

			$return = $image_path;
		}

		return $return;
	}

	/**
	 * @param eZContentClassAttribute $classAttribute
	 * @return array
	 */
	public function getClassAttributeOptions( eZContentClassAttribute $classAttribute )
	{
		return explode( '-', $classAttribute->attribute( 'data_text4' ) );
	}
}

eZDataType::register( dam_imagesType::DATA_TYPE_STRING, 'dam_imagesType' );
