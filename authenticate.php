<?php

require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/config.php';

session_start();

$callbackUri = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];

$server = new Samwilson\FlickrLatex\Server([
    'identifier' => $apiKey,
    'secret' => $apiSecret,
    'callback_uri' => $callbackUri,
]);

$tokenFile = $dataDir . '/token.txt';

if (!file_exists($tokenFile) && !isset($_GET['oauth_token'])) {
    $temporaryCredentials = $server->getTemporaryCredentials();
    $_SESSION['temporary_credentials'] = serialize($temporaryCredentials);
    session_write_close();
    echo $url = $server->getAuthorizationUrl($temporaryCredentials) . '&perms=read';
    echo "<p><a href='$url'>Authorise app</a><p>";
}

if (isset($_GET['oauth_token'])) {
    $temporaryCredentials = unserialize($_SESSION['temporary_credentials']);
    $tokenCredentials = $server->getTokenCredentials($temporaryCredentials, $_GET['oauth_token'], $_GET['oauth_verifier']);
    file_put_contents($tokenFile, serialize($tokenCredentials));
}

//if (file_exists($tokenFile)) {
//    $tokenCredentials = unserialize(file_get_contents($tokenFile));
//    $client = $server->createHttpClient();
//    $url = 'https://api.flickr.com/services/rest';
//    $params = [
//        'method' => 'flickr.photosets.getList',
//        'format' => 'php_serial',
//    ];
//    $url = $url . '?' . http_build_query($params);
//    $headers = $server->getHeaders($tokenCredentials, 'GET', $url);
//    $response = $client->get($url, $headers)->send();
//}

