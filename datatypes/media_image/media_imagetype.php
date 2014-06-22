<?php

/**
 * @author pek
 *
 */
class media_imageType extends eZDataType
{
	const DATA_TYPE_STRING = 'media_image';

	function __construct()
	{
		parent::__construct( self::DATA_TYPE_STRING, ezpI18n::tr( 'mugo_media_lib/datatype', 'Media Image', 'Datatype name'),
							 array( 'serialize_supported' => true ) );
	}

	/*
	 * Nothing additional to store - so just return true (parent class should do that)
	 * 
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
		
		$tagName = $base . '_media_image_' . $contentObjectAttribute->attribute( 'id' );

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
			$name = $base . '_media_image_'. $key .'_' . $classAttribute->attribute( 'id' );
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
		$return = false;
		
		$tagName = $base . '_media_image_' . $contentObjectAttribute->attribute( 'id' );
		
		if( $http->hasPostVariable( $tagName ) )
		{
			$data = $http->postVariable( $tagName );
			if( !empty( $data ) )
			{
				// clean out empty values
				foreach( $data as $index => $entry )
				{
					if( trim( $entry ) )
					{
						$return = true;
						break;
					}
				}
			}
		}

		if( $return )
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
	
	/* (non-PHPdoc)
	 * @see eZDataType::isIndexable()
	 */
	function isIndexable()
	{
		return true;
	}

	/* (non-PHPdoc)
	 * @see eZDataType::sortKeyType()
	 */
	function sortKeyType()
	{
		return '';
	}
	
	function toString( $objectAttribute )
	{
		return $objectAttribute->attribute( 'data_text' );
	}
	
	function fromString( $objectAttribute, $string )
	{
		$images = unserialize( $string );
		$uploadedImages = array();
		
		if( !empty( $images ) )
		{
			foreach( $images as $image )
			{
				if( ! $this->dam_has_image( $image ) )
				{
					$url = MugoDamFunctionCollection::uploadToDam( $image );
					
					if( $url )
					{
						$uploadedImages[] = $url;
					}
					else
					{
						//Failed to upload
						return false;
					}
				}
				else
				{
					$uploadedImages[] = $url;
				}
			}
		}
		
		$objectAttribute->setAttribute( 'data_text', serialize( $uploadedImages ) );
		
		return true;
	}
	
	public function dam_has_image( $image )
	{
		$ini = eZINI::instance( 'mugo_dam.ini' );
		$baseDamUrl = $ini->variable( 'Base', 'DamBaseUrl' );

		// check if image is hosted on DAM
		if( strpos( $image, $baseDamUrl ) !== false )
		{
			// check if exists on DAM
			return file_exists( $image );
		}
		else
		{
			return false;
		}
	}
}

eZDataType::register( media_imageType::DATA_TYPE_STRING, 'media_imageType' );
