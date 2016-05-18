<?php

/*
 * Move images from old attribute to the mugo_dam images attribute
 */


class MugoMigrateImages extends MugoTask
{
	protected $imageBasePath = 'http://arrowhead.quinstreet.net/';

	protected $mapping = array(
		'image' => array(
			'image' => array( 'standard', 'images' )
		),
		'author' => array(
			'image' => array( 'standard', 'images' )
		),
		'resource' => array(
			'image' => array( 'standard', 'images' )
		),
		'slideshow_item' => array(
			'image' => array( 'standard', 'images' )
		),
		'custom_panelist' => array(
			'avatar' => array( 'standard', 'avatars' )
		),
		'sponsor' => array(
			'logo_large' => array( 'standard', 'logo_larges' ),
			'logo_medium' => array( 'standard', 'logo_mediums' ),
			'logo_small' => array( 'standard', 'logo_smalls' ),
		)
	);

	public function create( $parameters, $limit )
	{
		$return = array();
		
		$classes = array(
			'image',
			'author',
			'custom_panelist',
			'resource',
			'sponsor',
			'slideshow_item'
		);
		
		$allContent = eZFunctionHandler::execute( 'content', 'tree', array(
			'parent_node_id' => 1,
			'class_filter_type'  => 'include',
			'class_filter_array' => $classes,
			'limitation'         => array(),
			'as_object'          => false,
			'main_node_only'     => true,
			'offset'             => 0,
			'limit'              => $limit
		) );
		
		//$allContent = array( array( 'id' => 51573 ) );
		
		foreach( $allContent as $row )
		{
			$return[] = $row[ 'id' ];
		}

		return $return;
	}
	
	public function execute( $task_id, $parameters )
	{
		$ezObj = eZContentObject::fetch( $task_id );

		if( isset( $this->mapping[ $ezObj->attribute( 'class_identifier' ) ] ) )
		{
			$attributeMapping = $this->mapping[ $ezObj->attribute( 'class_identifier' ) ];

			$dataMap = $ezObj->attribute( 'data_map' );

			foreach( $attributeMapping as $attributeIdentifier => $attributeMap )
			{
				if( isset( $dataMap[ $attributeIdentifier ] ) )
				{
					$attribute = $dataMap[ $attributeIdentifier ];

					$attrContent = array();
					if( $attribute->attribute( 'has_content' ) )
					{
						$fileName = $attribute->attribute( 'content' )->attribute( 'original_filename' );

						// Get URL
						$data = $attribute->toString();
						$parts = explode( '|', $data );
						$imagePath = $this->imageBasePath . $this->urlEscape( $parts[0] );

						$url = $this->uploadImage( $imagePath, $fileName, $ezObj->attribute( 'published' ) );

						if( $url )
						{
							$attrContent[ $attributeMap[0] ] = $url;

							$dataMap[ $attributeMap[1] ]->setAttribute( 'data_text', serialize( $attrContent ) );
							$dataMap[ $attributeMap[1] ]->store();
						}
						else
						{
							return false;
						}
					}

				}
			}
		}

		return true;
	}
	
	protected function uploadImage( $imagePath, $fileName, $creationTime )
	{		
		return MugoDamFunctionCollection::uploadToDam( $imagePath, $fileName, $creationTime );
	}

	protected function urlEscape( $url )
	{
		return str_replace( array( ' ' ), array( '%20' ), $url );
	}
}
