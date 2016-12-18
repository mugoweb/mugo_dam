<?php

$server = 'http://www.example.com';
$key = 'topsecret';
$filename = 'download.png';

$data = array(
        'files[0]'      => '@' . $filename,
        'creationtime'  => 1461769869,
);

$headers = array(
        'Content-Disposition: attachment; filename='. rawurlencode( $filename ) .';',
);

$access_details = array(
        'repository'    => 'client_test',
        'apikey'                => $key,
);

$data += $access_details;

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

echo 'Result from upload:' . "\n";
print_r( $responseData );

$uploadFile = $responseData->files[0]->urlPath;

if( $uploadFile )
{
        $data = array(
                'source' => $uploadFile,
                'target_name' => 'renamed.png'
        );

        $data += $access_details;

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
