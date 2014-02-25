<?php
require('../vendor/autoload.php');

use Amazon\InstantAccess\Signature\Credential;
use Amazon\InstantAccess\Signature\CredentialStore;
use Amazon\InstantAccess\Signature\Request;
use Amazon\InstantAccess\Signature\Signer;
use Amazon\InstantAccess\Utils\DateUtils;
use Amazon\InstantAccess\Utils\HttpUtils;
use Amazon\InstantAccess\Utils\IOUtils;

use Amazon\InstantAccess\Log\Logger;

$monolog = new Monolog\Logger('AmazonInstantAccessTest');
$monolog->pushHandler(new Monolog\Handler\StreamHandler('php://stdout'));
Logger::setLogger($monolog);

$server = array();
//$server['HTTP_USER_AGENT'] = 'Mozilla/5.0 (Windows NT 6.2; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/32.0.1667.0 Safari/537.36';
$server['HTTP_HOST'] = 'localhost';
$server['SERVER_PROTOCOL'] = 'HTTP/1.1';
$server['SERVER_PORT'] = '8000';
$server['REQUEST_URI'] = '/service/linking';
$server['REQUEST_METHOD'] = 'POST';
$server['CONTENT_TYPE'] = 'application/json';

$content = '{
                "operation":  "GetUserId",
                "infoField1": "nobody@amazon.com",
                "infoField2": "nobody"
            }';

$content = trim(preg_replace('/\s+/', ' ', $content));

$request = new Request($server, $content);

$credential = new Credential('e2c4905c-83ba-41e7-9c1b-af8014a334cb', '367caa91-cde5-48f2-91fe-bb95f546e9f0');

$signer = new Signer();

$signer->sign($request, $credential);

//echo $request;

$cmd  = 'curl -v';
$cmd .= ' --data \'' . $request->getBody() . '\'';
foreach ($request->getHeaders() as $key => $value) {
    $cmd .= ' -H "' . $key . ':' . $value . '"';
}
$cmd .= ' ' . $request->getUrl();

echo $cmd;

system($cmd);
