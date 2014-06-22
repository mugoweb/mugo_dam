<?php

class MugoDamFunctionCollection
{
	/**
	 * Uploads given imagePath to the DAM. Either returns URL of upload image in the DAM or
	 * false on failure.
	 * 
	 * @param string $imagePath
	 * @return boolean|string
	 */
	static function uploadToDam( $imagePath, $creationTime = null )
	{
		$ini = eZINI::instance( 'mugo_dam.ini' );
		$uploadService = $ini->variable( 'Base', 'UploadServiceUrl' );

		$creationTime = (int)$creationTime ? (int)$creationTime : time();
		
		$target = sys_get_temp_dir() . '/tmpImageUpload_' . getmypid();
		
		if( copy( $imagePath, $target ) )
		{
			$post = array(
				'repository'    => $ini->variable( 'Base', 'Repository' ),
				'apikey'        => $ini->variable( 'Base', 'ApiKey' ),
				'files[0]'      => '@'.$target,
				'creationtime'  => $creationTime,
			);
			
			$ch = curl_init();
			curl_setopt( $ch, CURLOPT_URL, $uploadService );
			curl_setopt( $ch, CURLOPT_POST, true );
			curl_setopt( $ch, CURLOPT_POSTFIELDS, $post );
			curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
			curl_setopt( $ch, CURLOPT_HTTPHEADER, array(
					'Content-Disposition: attachment; filename="'. basename( $imagePath ) .'"',
			));

			$jsonText = curl_exec ($ch);
			curl_close( $ch );
			
			$jsonReturn = json_decode( $jsonText );
			
			if( !empty( $jsonReturn ) )
			{
				$files = $jsonReturn->files;

				return $files[ 0 ]->url;
			}			
		}
		
		return false;
	}
}