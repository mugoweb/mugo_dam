<?php

/**
 * TODO: stop building static functions!!!
 *
 * Class MugoDamFunctionCollection
 */
class MugoDamFunctionCollection
{
	/**
	 * Uploads given imagePath to the DAM. Either returns URL of upload image in the DAM or
	 * false on failure. The imagePath can be a local image or remote image specified as a URL
	 * 
	 * @param string $imagePath
	 * @return boolean|string
	 */
	static function uploadToDam( $imagePath, $fileName = null, $creationTime = null )
	{
		$return        = false;
		$ini           = eZINI::instance( 'mugo_dam.ini' );
		$uploadService = $ini->variable( 'Base', 'UploadServiceUrl' );

		$creationTime = (int)$creationTime ? (int)$creationTime : time();
		$fileName     = $fileName ? $fileName : basename( $imagePath );
		
		$target = sys_get_temp_dir() . '/tmpImageUpload_' . getmypid();
		
		if( copy( $imagePath, $target ) )
		{
			$post = array(
				'files[0]' => new CurlFile( $target ),
				'creationtime' => $creationTime,
			);

			//TODO: check if CurlFile can handle it
			$headers = array(
				'Content-Disposition: attachment; filename='. rawurlencode( $fileName ) .';',
			);

			$result = self::call_server( $uploadService, $post, $headers );

			if( $result )
			{
				$file = $result->files[0];

				if( !$file->error )
				{
					$return = $file->urlPath;
				}
				else
				{
					eZDebugSetting::writeWarning( 'extension-mugo_dam', 'Upload response error: ' . $file->error, __METHOD__ );
				}
			}
		}

		return $return;
	}

	/**
	 * Renames an image on the server
	 *
	 * @param string $source
	 * @param string $target_name
	 * @return bool|mixed
	 */
	static function rename( $source, $target_name )
	{
		$return = false;
		$ini = eZINI::instance( 'mugo_dam.ini' );
		$renameService = $ini->variable( 'Base', 'RenameServiceUrl' );

		if( $source && $target_name )
		{
			$post = array(
				'source' => $source,
				'target_name' => $target_name,
			);

			$return = self::call_server( $renameService, $post );
		}

		return $return;
	}

	/**
	 * Generic function to send request to image server
	 *
	 * @param string $endpoint
	 * @param array $data
	 * @param array $headers
	 * @return bool|mixed
	 */
	static protected function call_server( $endpoint, $data, $headers = null )
	{
		$return = false;

		$ini = eZINI::instance( 'mugo_dam.ini' );

		$access_details = array(
			'repository'	=> $ini->variable( 'Base', 'Repository' ),
			'apikey'		=> $ini->variable( 'Base', 'ApiKey' ),
		);

		$data += $access_details;

		$ch = curl_init();
		curl_setopt( $ch, CURLOPT_URL, $endpoint );
		curl_setopt( $ch, CURLOPT_POST, true );
		curl_setopt( $ch, CURLOPT_POSTFIELDS, $data );
		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
		curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false );

		if( !empty( $headers ) )
		{
			curl_setopt( $ch, CURLOPT_HTTPHEADER, $headers );
		}

		$jsonText = curl_exec( $ch );

		if( curl_errno( $ch ) )
		{
			eZDebugSetting::writeWarning( 'extension-mugo_dam', '"'. $endpoint .'" error: ' . curl_error( $ch ), __METHOD__ );
		}
		else
		{
			$jsonReturn = json_decode( $jsonText );

			if( !empty( $jsonReturn ) )
			{
				$return = $jsonReturn;
			}
		}

		curl_close( $ch );

		return $return;
	}
}
