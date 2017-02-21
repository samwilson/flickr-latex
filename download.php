#!/usr/bin/env php
<?php

use Samwilson\FlickrLatex\FlickrLatex;
use Samwilson\FlickrLatex\Group;

echo "Please report bugs at https://github.com/samwilson/flickr-latex\n";

if (php_sapi_name() !== 'cli') {
    echo "This file should be run from the command line.\n";
    exit(1);
}

require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/config.php';

// Check config variables.
if (!is_array($groups)) {
    echo "You must set the '\$groups' variable in config.php\n";
    exit(1);
}

$flickrLatex = new FlickrLatex(__DIR__.'/data', $apiKey, $apiSecret);

// See if we need to authorize.
if (!$flickrLatex->authorized()) {
    $flickrService = $flickrLatex->getService();
    // Fetch the request-token.
    $requestToken = $flickrService->requestRequestToken();
    $url = $flickrService->getAuthorizationUri([
        'oauth_token' => $requestToken->getRequestToken(),
        'perms' => 'read'
    ]);
    echo "Please go to this URL to authorize this applicaiton:\n$url\n";
    // Flickr says, at this point:
    // "You have successfully authorized the application Flickr Latex to use your credentials.
    // You should now type this code into the application:"
    echo "Paste the 9-digit code (with or without hyphens) here: ";
    $verifier = preg_replace('/[^0-9]/', '', fgets(fopen('php://stdin', 'r')));

    // Fetch the access-token, for saving to data/token.json
    $accessToken = $flickrService->requestAccessToken(
        $requestToken,
        $verifier,
        $requestToken->getAccessTokenSecret()
    );
    $flickrLatex->setStoredCredentials($accessToken);
}

if (!$flickrLatex->authorized()) {
    echo "Unable to authorize. :-(";
    exit(1);
}

//$groups = $flickrLatex->request('flickr.people.getGroups', ['user_id'=>$flickrLatex->getUserId()]);
//print_r($groups);
//exit();

// Download each group's photos.
foreach ($groups as $groupId) {
    $group = new Group($flickrLatex, $dataDir, __DIR__.'/templates');
    $photoData = $group->download($groupId);
}
