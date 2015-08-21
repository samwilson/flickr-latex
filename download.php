<?php

if (php_sapi_name() !== 'cli') {
    echo "This file should be run from the command line.\n";
    exit(1);
}

require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/config.php';

$flickrLatex = new \Samwilson\FlickrLatex\FlickrLatex($dataDir, $apiKey, $apiSecret);

if (!$flickrLatex->authorized()) {
    echo "You must authorise this application.\n";
    exit(1);
}

// Download each group's photos.
foreach ($groups as $groupId) {
    $group = new Samwilson\FlickrLatex\Group($flickrLatex, $dataDir, __DIR__.'/templates');
    $photoData = $group->download($groupId);
}
