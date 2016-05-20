<?php

/**
 * This class expect to have access to 'ContentClass_Handler' which is not delivered with
 * this extension.
 */
class MugoDamUploadHandler extends eZContentUploadHandler
{
	public function __construct()
	{
		parent::__construct( 'Mugo DAM file handling', 'mugodam' );
	}

	public function handleFile( &$upload, &$result,
						 $filePath, $originalFilename, $mimeInfo,
						 $location, $existingNode )
	{
		$return = false;

		$ini = eZINI::instance( 'mugo_dam.ini' );
		$ratio = $ini->variable( 'ImageRatios', 'DefaultRatio' );
		$classIdentifier = $ini->variable( 'ImageClass', 'ClassIdentifier' );
		$imageAttributeIdentifier = $ini->variable( 'ImageClass', 'ImageAttributeIdentifier' );
		$nameAttributeIdentifier = $ini->variable( 'ImageClass', 'NameAttributeIdentifier' );

		$path_parts = pathinfo( $originalFilename );

		$attributes = array();
		$attributes[ $nameAttributeIdentifier ] = $path_parts[ 'filename' ];
		$attributes[ $imageAttributeIdentifier ] = serialize( array( $ratio => array(
			'upload_name' => $originalFilename,
			'upload_url' => $filePath
		) ) );

		$meta = array(
			'remote_id' => 'webdav_' . md5( mt_rand() )
		);

		//TODO: resolve dependency here!!
		$image_id = ContentClass_Handler::create( $attributes, $location, $classIdentifier, $meta );
		
		if( $image_id )
		{
			$image = eZContentObjectTreeNode::fetch( $image_id );
			
			// Set by reference - a bit confusing
			$result[ 'contentobject' ] = $image->attribute( 'object' );
			$result[ 'contentobject_id' ] = $image->attribute( 'contentobject_id' );
			$return = true;
		}
		
		return $return;
	}
}
