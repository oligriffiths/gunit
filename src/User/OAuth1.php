<?php

namespace OliGriffiths\GUnit\User;

use OliGriffiths\GUnit\AUthenticator;

class OAuth1 implements UserInterface
{
    /**
     * @var string
     */
    private $access_key;

    /**
     * @var string
     */
    private $access_secret;
    
    private $authenticator;

    /**
     * Oauth1 constructor.
     * @param string $access_key
     * @param string $access_secret
     */
    public function __construct($access_key, $access_secret, $authenticator = 'oauth1')
    {
        if (!is_string($access_key) || !is_string($access_secret) || !is_string($authenticator)) {
            throw new \InvalidArgumentException('$access_key, $access_secret and $authenticator must be strings');
        }
        
        $this->access_key = $access_key;
        $this->access_secret = $access_secret;
        $this->authenticator = $authenticator;
    }

    /**
     * @return string
     */
    public function getAccessKey()
    {
        return $this->access_key;
    }

    /**
     * @return string
     */
    public function getAccessSecret()
    {
        return $this->access_secret;
    }
    
    public function getAuthenticator()
    {
        return $this->authenticator;
    }
}
