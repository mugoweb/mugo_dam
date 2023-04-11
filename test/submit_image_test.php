<?php

$server = 'http://www.example.com';
$key = 'topsecret';
$filename = 'download.png';

$data = array(
	'files[0]' => new CurlFile( 'download.png', 'image/png', 'download.png'),
	'creationtime' => 1461769869,
	'repository' => $repo,
	'apikey' => $key,
);

$headers = array(
	'Content-Disposition: attachment; filename='. rawurlencode( 'download.png' ) .';',
);

$ch = curl_init( $server . '/upload' );

curl_setopt( $ch, CURLOPT_POST, true );
curl_setopt( $ch, CURLOPT_POSTFIELDS, $data );
curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false );

if( !empty( $headers ) )
{
	curl_setopt( $ch, CURLOPT_HTTPHEADER, $headers );
}

$jsonResponse = curl_exec( $ch );
$responseData = json_decode( $jsonResponse );

if( $responseData )
{
	echo 'Result from upload:' . "\n";
	print_r( $responseData );
}
else
{
	echo 'No proper json response:' . "\n";
	var_dump( $jsonResponse );
}

$uploadFile = $responseData->files[0]->urlPath;

if( $uploadFile )
{
	$data = array(
		'source' => $uploadFile,
		'target_name' => 'renamed.png',
		'repository' => $repo,
		'apikey' => $key,
	);

	$ch = curl_init( $server . '/rename' );
	curl_setopt( $ch, CURLOPT_POST, true );
	curl_setopt( $ch, CURLOPT_POSTFIELDS, $data );
	curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
	curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false );

	$jsonResponse = curl_exec( $ch );
	$responseData = json_decode( $jsonResponse );

	echo 'Result of rename:' . "\n";
	var_dump( $responseData );
}
