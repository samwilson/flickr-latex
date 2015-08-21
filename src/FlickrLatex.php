<?php

namespace Samwilson\FlickrLatex;

class FlickrLatex {

    private $apiKey;
    private $apiSecret;
    private $dataDir;

    public function __construct($dataDir, $apiKey, $apiSecret) {
        $this->dataDir = realpath($dataDir);
        if (!is_dir($this->dataDir)) {
            throw new Exception("Data directory not found: $this->dataDir");
        }
        $this->apiKey = $apiKey;
        $this->apiSecret = $apiSecret;
    }

    /**
     * 
     * @return FlickrServer
     */
    public function getServer() {
        $this->server = new Server(
                array(
            'identifier' => $this->apiKey,
            'secret' => $this->apiSecret,
                //'callback_uri' => $this->callbackUri,
                )
        );
        return $this->server;
    }

    public function getTokenFilename() {
        return $this->dataDir . '/token.txt';
    }

    /**
     * Whether we're authorized yet.
     * @return boolean
     */
    public function authorized() {
        if (!file_exists($this->getTokenFilename())) {
            return false;
        }
        $result = $this->request('flickr.test.login');
        return ($result['stat'] == 'ok');
    }

    public function request($method, $parameters = array()) {
        $client = $this->getServer()->createHttpClient();
        $url = 'https://api.flickr.com/services/rest';
        $params = [ 'method' => $method, 'format' => 'php_serial'];
        $url = $url . '?' . http_build_query(array_merge($params, $parameters));
        $tokenCreds = unserialize(file_get_contents($this->getTokenFilename()));
        $headers = $this->getServer()->getHeaders($tokenCreds, 'GET', $url);
        $response = $client->get($url, $headers)->send();
        return unserialize($response->getBody(TRUE));
    }

    public function getPhotosets() {
        $result = $this->request('flickr.photosets.getList');
        //print_r(array_keys($result));
        return $result['photosets']['photoset'];
    }

    public function photosGetNotInSet() {
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

    public function singlePhoto($id) {
        $photoInfo = $this->request('flickr.photos.getInfo', array('photo_id' => $id));
        //print_r($photoInfo);
        echo "  " . $photoInfo['photo']['id'] . " -- " . $photoInfo['photo']['title']['_content'] . "\n";

        // Compile required info.
        $dateTaken = $photoInfo['photo']['dates']['taken'];
        $description = '';
        if ($photoInfo['photo']['description']['_content']) {
            $description = trim($photoInfo['photo']['description']['_content'], ' .') . '.';
        }

        $photoDatum = array(
            'id' => $id,
            'user_id' => $photoInfo['photo']['owner']['nsid'],
            'date_taken_value' => $dateTaken,
            'granularity' => $photoInfo['photo']['dates']['takengranularity'],
            'date_taken' => Latex::flickrDate($dateTaken, $photoInfo['photo']['dates']['takengranularity']),
            'title' => trim($photoInfo['photo']['title']['_content'], '.') . '.', // Ensure has trailing full stop.
            'description' => $description,
            'tags' => array(),
        );
        foreach ($photoInfo['photo']['tags']['tag'] as $tag) {
            $photoDatum['tags'][] = $tag['raw'];
        }

        $localDir = $this->dataDir . "/photos/$id";
        if (!is_dir($localDir)) {
            mkdir($localDir, 0755, true);
        }

        // Download files.
        $farm = $photoInfo['photo']['farm'];
        $server = $photoInfo['photo']['server'];
        $scrt = $photoInfo['photo']['secret'];

        // Original?
        if (isset($photoInfo['photo']['originalsecret'])) {
            $origScrt = $photoInfo['photo']['originalsecret'];
            $origFmt = $photoInfo['photo']['originalformat'];
            $origUrl = 'https://farm' . $farm . '.staticflickr.com/' . $server . '/' . $id . '_' . $origScrt . '_o.' . $origFmt;
            if (!file_exists($localDir . '/original.' . $origFmt)) {
                file_put_contents($localDir . '/original.' . $origFmt, file_get_contents($origUrl));
            }
        }
        // Medium. https://farm{farm-id}.staticflickr.com/{server-id}/{id}_{secret}_[mstzb].jpg
        $medUrl = 'https://farm' . $farm . '.staticflickr.com/' . $server . '/' . $id . '_' . $scrt . '_c.jpg';
        if (!file_exists($localDir . '/medium.jpg')) {
            file_put_contents($localDir . '/medium.jpg', file_get_contents($medUrl));
        }
        // Metadata.
        $metadata = \Symfony\Component\Yaml\Yaml::dump($photoDatum);
        file_put_contents($localDir . '/metadata.yml', $metadata);

        return $photoDatum;
    }

}
