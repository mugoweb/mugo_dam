<?php

$endpoint = 'http://imageserver.example.com/upload';
$filename = 'download.png';

$data = array(
	'files[0]'      => '@' . $filename,
	'creationtime'  => 1461769869,
);

$headers = array(
	'Content-Disposition: attachment; filename='. rawurlencode( $filename ) .';',
);

$access_details = array(
	'repository'	=> 'mugo_test',
	'apikey'		=> 'topsecret',
);

$data += $access_details;

$ch = curl_init( $endpoint );

curl_setopt( $ch, CURLOPT_POST, true );
curl_setopt( $ch, CURLOPT_POSTFIELDS, $data );
curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false );

if( !empty( $headers ) )
{
	curl_setopt( $ch, CURLOPT_HTTPHEADER, $headers );
}

$jsonText = curl_exec( $ch );

var_dump( $jsonText );
