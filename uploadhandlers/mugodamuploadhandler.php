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
		
		$ini   = eZINI::instance( 'mugo_dam.ini' );
		$ratio = $ini->variable( 'ImageRatios', 'DefaultRatio' );

		$path_parts = pathinfo( $originalFilename );
		
		$attributes = array(
			'name'   => $path_parts[ 'filename' ],
			'images' => serialize( array( $ratio => $filePath ) )
		);
		
		$meta = array(
			'remote_id' => 'webdav_' . md5( mt_rand() )
		);
		
		$image_id = ContentClass_Handler::create( $attributes, $location, 'image', $meta );
		
		if( $image_id )
		{
			$image = eZContentObjectTreeNode::fetch( $image_id );
			
			// Set by reference - a bit confusing
			$result[ 'contentobject' ] = $image->attribute( 'object' );
			$return = true;
		}
		
		return $return;
	}
}
