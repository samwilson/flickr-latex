<?php

namespace Samwilson\FlickrLatex;

use OAuth\Common\Consumer\Credentials;
use OAuth\Common\Storage\Memory;
use OAuth\OAuth1\Service\AbstractService;
use OAuth\OAuth1\Token\StdOAuth1Token;
use OAuth\OAuth1\Token\TokenInterface;
use OAuth\ServiceFactory;
use Symfony\Component\Yaml\Yaml;

class FlickrLatex
{

    /** @var string */
    protected $apiKey;

    /** @var string */
    protected $apiSecret;

    /** @var string */
    protected $dataDir;

    /** @var string The ID of the current user. */
    protected $userId;

    /** @var  AbstractService */
    protected $flickrService;

    public function __construct($dataDir, $apiKey, $apiSecret)
    {
        $this->dataDir = realpath($dataDir);
        if (!is_dir($this->dataDir)) {
            throw new Exception("Data directory not found: $dataDir");
        }
        $this->apiKey = $apiKey;
        $this->apiSecret = $apiSecret;
    }

    /**
     * Get the Flickr service.
     * @return AbstractService
     */
    public function getService()
    {
//      if ( $this->flickrService instanceof ServiceInterface ) {
//          return $this->flickrService;
//      }
        $credentials = new Credentials($this->apiKey, $this->apiSecret, 'oob');
        $storage = new Memory();
        $accessToken = $this->getStoredCredentials();
        if ($accessToken instanceof TokenInterface) {
            $storage->storeAccessToken('Flickr', $accessToken);
        }
        $serviceFactory = new ServiceFactory();
        $this->flickrService = $serviceFactory->createService('Flickr', $credentials, $storage);
        return $this->flickrService;
    }

    /**
     * Get the full path to the file that stores the user's credentials.
     * @return string
     */
    public function getTokenFilename()
    {
        return $this->dataDir . '/credentials.txt';
    }

    /**
     * Whether we're authorized yet.
     * @return boolean
     */
    public function authorized()
    {
        if (!file_exists($this->getTokenFilename())) {
            return false;
        }
        $result = $this->request('flickr.test.login');
        if (!isset($result->stat) || !$result->stat === 'ok') {
            return false;
        }
        $this->userId = $result->user->id;
        return true;
    }

    public function getUserId()
    {
        return $this->userId;
    }

    /**
     * Get the token credentials stored in data/token.json
     * @return StdOAuth1Token
     */
    public function getStoredCredentials()
    {
        if (!file_exists($this->getTokenFilename())) {
            return false;
        }
        $string = file_get_contents($this->getTokenFilename());
        return unserialize($string);
    }

    /**
     * Update the data/token.json file with new credentials.
     * @param StdOAuth1Token $credentials
     */
    public function setStoredCredentials(StdOAuth1Token $credentials)
    {
        file_put_contents($this->getTokenFilename(), serialize($credentials));
    }

    public function request($method, $parameters = array())
    {
        $flickrService = $this->getService();
        $response = $flickrService->requestJson($method, 'POST', $parameters);
        return json_decode($response);
    }

    public function getPhotosets()
    {
        $result = $this->request('flickr.photosets.getList');
        return $result['photosets']['photoset'];
    }

    public function photosGetNotInSet()
    {
        $page = 1;
        while ($page) {
            $notInSet = $this->request('flickr.photos.getNotInSet');
            //print_r($notInSet);
            if ($notInSet['photos']['pages'] == $page) {
                $page = false;
            }
            foreach ($notInSet['photos']['photo'] as $photo) {
                $this->singlePhoto($photo['id']);
            }
        }
    }

	/**
	 * @param string $id
	 * @return string[]
	 */
    public function singlePhoto($id)
    {
        $photoInfo = $this->request('flickr.photos.getInfo', array( 'photo_id' => $id ));
        if (!isset($photoInfo->photo)) {
            return [];
        }
        //print_r($photoInfo);
        echo "  " . $photoInfo->photo->id . " -- " . $photoInfo->photo->title->_content . "\n";

        // Compile required info.
        $dateTaken = $photoInfo->photo->dates->taken;
        $description = '';
        if ($photoInfo->photo->description->_content) {
            $description = trim($photoInfo->photo->description->_content, ' .') . '.';
        }

        $photoDatum = array(
            'id' => $id,
            'user_id' => $photoInfo->photo->owner->nsid,
            'date_taken_value' => $dateTaken,
            'granularity' => $photoInfo->photo->dates->takengranularity,
            'date_taken_raw' => $dateTaken,
            'date_taken' => Latex::flickrDate(
                $dateTaken,
                $photoInfo->photo->dates->takengranularity
            ),
            'title' => trim($photoInfo->photo->title->_content, '.') . '.',
            // Ensure has trailing full stop.
            'description' => $description,
            'tags' => array(),
        );
        foreach ($photoInfo->photo->tags->tag as $tag) {
            $photoDatum['tags'][] = $tag->raw;
        }

        $localDir = $this->dataDir . "/photos/$id";
        if (!is_dir($localDir)) {
            mkdir($localDir, 0750, true);
        }

        // Download files.
        $farm = $photoInfo->photo->farm;
        $server = $photoInfo->photo->server;
        $secret = $photoInfo->photo->secret;

        // Original?
        /*if (isset($photoInfo->photo->originalsecret)) {
            $origScrt = $photoInfo->photo->originalsecret;
            $origFmt = $photoInfo->photo->originalformat;
            $origUrl =
                'https://farm' . $farm . '.staticflickr.com/' . $server . '/' . $id . '_' .
                $origScrt . '_o.' . $origFmt;
            if (!file_exists($localDir . '/original.' . $origFmt)) {
                file_put_contents(
                    $localDir . '/original.' . $origFmt,
                    file_get_contents($origUrl)
                );
            }
        }*/
        // Download large size.
        // https://farm{farm-id}.staticflickr.com/{server-id}/{id}_{secret}_[mstzb].jpg
        $photoUrl = 'https://farm' . $farm . '.staticflickr.com/'
                    . $server . '/' . $id . '_' . $secret . '_b.jpg';
        if (!file_exists($localDir . '/photo.jpg')) {
            file_put_contents($localDir . '/photo.jpg', file_get_contents($photoUrl));
        }
        // Metadata.
        $metadata = Yaml::dump($photoDatum);
        file_put_contents($localDir . '/metadata.yml', $metadata);

        return $photoDatum;
    }
}
