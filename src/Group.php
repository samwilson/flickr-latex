<?php

namespace Samwilson\FlickrLatex;

class Group
{

    /** @var \Samwilson\FlickrLatex\FlickrLatex */
    protected $flickrLatex;

    /** @var string */
    protected $dataDir;

    /** @var string */
    protected $templateDir;

    public function __construct($flickrLatex, $dataDir, $templateDir)
    {
        $this->flickrLatex = $flickrLatex;
        $this->dataDir = $dataDir;
        $this->templateDir = $templateDir;
    }

    function download($groupId)
    {
        $maxPages = 2;
        $info = $this->flickrLatex->request('flickr.groups.getInfo', ['group_id' => $groupId]);
        if (!isset($info->group)) {
            echo "Unable to get group info for group $groupId -- returned was:\n";
            print_r($info);
            exit(1);
        }
        $title = $info->group->name->_content;
        echo "====== Downloading photos for group: $title ======\n";

        // Loop through all pages of photos for this group.
        $photoData = array();
        $page = 1;
        while ($page) {
            $photos = $this->flickrLatex->request('flickr.groups.pools.getPhotos', ['group_id' => $groupId, 'page' => $page, 'per_page' => 500]);
            echo "Getting page $page of " . $photos->photos->pages . "\n";
            // Get all these photos.
            foreach ($photos->photos->photo as $photo) {
                $photoDatum = $this->flickrLatex->singlePhoto($photo->id);
                $photoData[uniqid($photoDatum['date_taken'])] = $photoDatum;
            }
            if ($page < min($photos->photos->pages, $maxPages)) {
                $page++;
            } else {
                $page = false;
            }
        }
        ksort($photoData);

        // Output LaTeX file.
        $dataDir = $this->dataDir;
        ob_start();
        require $this->templateDir . '/album.php';
        $latex = ob_get_clean();
            $albumDir = $this->dataDir . '/albums/' . $groupId;
        if (!is_dir($albumDir)) {
            mkdir($albumDir, 0755, true);
        }
        file_put_contents($albumDir . '/album.tex', $latex);

    }
}
