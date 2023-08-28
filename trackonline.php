<?php
$origin=isset($_SERVER['HTTP_ORIGIN'])?$_SERVER['HTTP_ORIGIN']:$_SERVER['HTTP_HOST'];
header('Access-Control-Allow-Origin: '.$origin);
header('Access-Control-Allow-Methods: POST, OPTIONS, GET, PUT');
header('Access-Control-Allow-Credentials: true');

include_once './lib/class.common.php';
$Object = new Common();

$id = $_GET['id'];

$graphic_http = 'http://api.warrantyproject.com/photo/Blank.gif';

//Get the filesize of the image for headers
$photoPathTemp = $_SERVER["DOCUMENT_ROOT"].'/photo/Blank.gif';

$filesize = filesize( $photoPathTemp );

$Object->updateTrackEmail($id,'Sent',"Opened");

//Begin the header output
header( 'Content-Type: image/gif' );
//Now actually output the image requested (intentionally disregarding if the database was affected)
header( 'Pragma: public' );
header( 'Expires: 0' );
header( 'Cache-Control: must-revalidate, post-check=0, pre-check=0' );
header( 'Cache-Control: private',false );
header( 'Content-Disposition: attachment; filename="Blank.gif"' );
header( 'Content-Transfer-Encoding: binary' );
header( 'Content-Length: '.$filesize );
readfile( $graphic_http );

exit;
