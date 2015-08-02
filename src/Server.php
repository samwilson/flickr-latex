<?php

namespace Samwilson\FlickrLatex;

class Server extends \League\OAuth1\Client\Server\Server {

    public function urlAuthorization() {
        return 'https://www.flickr.com/services/oauth/authorize';
    }

    public function urlTemporaryCredentials() {
        return 'https://www.flickr.com/services/oauth/request_token';
    }

    public function urlTokenCredentials() {
        return 'https://www.flickr.com/services/oauth/access_token';
    }

    public function urlUserDetails() {
        
    }

    public function userDetails($data, \League\OAuth1\Client\Credentials\TokenCredentials $tokenCredentials) {
        
    }

    public function userEmail($data, \League\OAuth1\Client\Credentials\TokenCredentials $tokenCredentials) {
        
    }

    public function userScreenName($data, \League\OAuth1\Client\Credentials\TokenCredentials $tokenCredentials) {
        
    }

    public function userUid($data, \League\OAuth1\Client\Credentials\TokenCredentials $tokenCredentials) {
        
    }

}
