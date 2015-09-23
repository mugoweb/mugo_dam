<?php

/**
 * @author pek
 *
 */
class dam_imagesType extends eZDataType
{
	const DATA_TYPE_STRING = 'dam_images';

	function __construct()
	{
		parent::__construct( self::DATA_TYPE_STRING, ezpI18n::tr( 'mugo_dam_images/datatype', 'DAM Images', 'Datatype name'),
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
				$entries = $contentObjectAttribute->attribute( 'content' );

				if( !empty( $entries ) )
				{
					$dirty = false;
					foreach( $entries as $key => $entry )
					{
						if( strpos( $entry, '{{unnamed}}' ) )
						{
							$parts = pathinfo( $entry );
							$new_name = str_replace( '{{unnamed}}', $obj_name, $parts[ 'basename' ] );

							$new_path = MugoDamFunctionCollection::rename( $entry, $new_name );

							if( $new_path )
							{
								$dirty = true;
								$entries[ $key ] = $new_path;
							} else
							{
								// report
							}
						}
					}

					if( $dirty )
					{
						$contentObjectAttribute->setAttribute( 'data_text', serialize( $entries ) );
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
		return unserialize( $objectAttribute->attribute( 'data_text' ) );
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
			$data = $http->postVariable( $tagName );
			if( !empty( $data ) )
			{
				// clean out empty values
				foreach( $data as $index => $entry )
				{
					if( !trim( $entry ) )
					{
						unset( $data[ $index ] );
					}
				}
				
				$contentObjectAttribute->setAttribute( 'data_text', serialize( $data ) );
				$return = true;
			}
		}

		return $return;
	}
		
	/* 
	 * PEK: it's kinda strange: this function gets called when a new content class
	 * version get initialized (even before the user hits apply/ok).
	 */
	function fetchClassAttributeHTTPInput( $http, $base, $classAttribute )
	{
		$hasPost = false;
		
		$options = array(
				'max_filesize'   => 0,
				'allow_multiple' => false,
				'auto_upload'    => false
		);
		
		foreach( $options as $key => $value )
		{
			$name = $base . '_dam_images_'. $key .'_' . $classAttribute->attribute( 'id' );
			if ( $http->hasPostVariable( $name ) )
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
	function validateObjectAttributeHTTPInput( $http, $base, $contentObjectAttribute )
	{
		if( $this->validateForRequiredContent( $http, $base, $contentObjectAttribute ) )
		{
			return eZInputValidator::STATE_ACCEPTED;
		}
		else
		{
			$contentObjectAttribute->setValidationError(
					ezpI18n::tr( 'kernel/classes/datatypes',
							'Input required.' )
			);
			
			return eZInputValidator::STATE_INVALID;
		}
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
		// Set return value to 'true' if content is not required.
		$return = $contentObjectAttribute->validateIsRequired() ? false : true;
		
		$tagName = $base . '_dam_images_' . $contentObjectAttribute->attribute( 'id' );
		
		if( $http->hasPostVariable( $tagName ) )
		{
			$data = $http->postVariable( $tagName );
			if( !empty( $data ) )
			{
				// clean out empty values
				foreach( $data as $entry )
				{
					if( trim( $entry ) )
					{
						$return = true;
						break;
					}
				}
			}
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
	 * Function is a bit overloaded: it should just write the string back into the DB.
	 * But it also allows you to give an image file/url and it will upload it to the DAM.
	 * 
	 * @param type $objectAttribute
	 * @param string $string
	 * @return boolean
	 */
	public function fromString( $objectAttribute, $string )
	{
		$images = unserialize( $string );
		$uploadedImages = array();
		
		if( !empty( $images ) )
		{
			foreach( $images as $ratio => $image )
			{
				if( ! $this->dam_has_image( $image ) )
				{
					$url = MugoDamFunctionCollection::uploadToDam( $image );
					
					if( $url )
					{
						$uploadedImages[ $ratio ] = $url;
					}
					else
					{
						//Failed to upload
						return false;
					}
				}
				else
				{
					$uploadedImages[ $ratio ] = $image;
				}
			}
		}
		
		$objectAttribute->setAttribute( 'data_text', serialize( $uploadedImages ) );
		
		return true;
	}
	
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

				// Get image path
				$image_ratio_identifier = $image_ratio_identifier ? $image_ratio_identifier : 'standard';
				$image_path = isset( $content[ $image_ratio_identifier ] ) ? $content[ $image_ratio_identifier ] : reset( $content );

				if( $image_path )
				{
					$return = self::getImageUrl( $image_path, $alias, $protocol );
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
	
}

eZDataType::register( dam_imagesType::DATA_TYPE_STRING, 'dam_imagesType' );
