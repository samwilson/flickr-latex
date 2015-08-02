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
    downloadGroup($flickrLatex, $dataDir, $groupId);
}

exit();

// End of main execution block. Functions only, below here.
// -----------------------------------------------------------------------------

function downloadGroup($flickrLatex, $dataDir, $groupId) {

    $maxPages = 2;
    $info = $flickrLatex->request('flickr.groups.getInfo', ['group_id' => $groupId]);
    $title = $info['group']['name']['_content'];
    echo "====== Downloading photos for group: $title ======\n";

    // Loop through all pages of photos for this group.
    $photoData = array();
    $page = 1;
    while ($page) {
        $photos = $flickrLatex->request('flickr.groups.pools.getPhotos', ['group_id' => $groupId, 'page' => $page, 'per_page' => 500]);
        echo "Getting page $page of " . $photos['photos']['pages'] . "\n";
        // Get all these photos.
        foreach ($photos['photos']['photo'] as $photo) {
            $photoDatum = $flickrLatex->singlePhoto($photo['id']);
            $photoData[uniqid($photoDatum['date_taken'])] = $photoDatum;
        }
        if ($page < min($photos['photos']['pages'], $maxPages)) {
            $page++;
        } else {
            $page = false;
        }
    }
    ksort($photoData);

    // Output LaTeX file.
    ob_start();
    require __DIR__ . '/templates/album.php';
    $latex = ob_get_clean();
    $albumDir = $dataDir . '/albums/' . $groupId;
    if (!is_dir($albumDir)) {
        mkdir($albumDir, 0755, true);
    }
    file_put_contents($albumDir . '/album.tex', $latex);
}

function texEsc($str) {
    $in = strip_tags($str);
    $pat = array('/\\\(\s)/', '/\\\(\S)/', '/&/', '/%/', '/\$/', '/>>/', '/_/', '/\^/', '/#/', '/"(\s)/', '/"(\S)/');
    $rep = array('\textbackslash\ $1', '\textbackslash $1', '\&', '\%', '\textdollar ', '\textgreater\textgreater ', '\_', '\^', '\#', '\textquotedbl\ $1', '\textquotedbl $1');
    return preg_replace($pat, $rep, $in);
}

function flickrDate($time, $granularity) {
    $granularities = array(
        '0' => 'Y-m-d H:i:s',
        '4' => 'Y-m',
        '6' => 'Y',
        '8' => '\c. Y',
    );
    return date($granularities[$granularity], strtotime($time));
}
