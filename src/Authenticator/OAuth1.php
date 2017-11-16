<?php

namespace OliGriffiths\GUnit\Authenticator;

use OliGriffiths\GUnit\User;
use Psr\Http\Message;
use GuzzleHttp\Subscriber\Oauth;

class OAuth1 implements AuthenticatorInterface
{
    /**
     * @var string
     */
    private $consumer_key;

    /**
     * @var string
     */
    private $consumer_secret;
    
    private $config;
    
    /**
     * Oauth1 constructor.
     * @param string $consumer_key
     * @param string $consumer_secret
     */
    public function __construct($consumer_key, $consumer_secret, array $config = [])
    {
        if (!is_string($consumer_key) || !is_string($consumer_secret)) {
            throw new \InvalidArgumentException('$consumer_key and $consumer_secret must be strings');
        }

        $config['consumer_key'] = $consumer_key;
        $config['consumer_secret'] = $consumer_secret;
        $this->config = $config;
        $this->consumer_key = $consumer_key;
        $this->consumer_secret = $consumer_secret;
    }

    /**
     * @return string
     */
    public function getConsumerKey()
    {
        return $this->consumer_key;
    }

    /**
     * @return string
     */
    public function getConsumerSecret()
    {
        return $this->consumer_secret;
    }

    /**
     * @param Message\RequestInterface $request
     * @param User\OAuth1|null $user
     */
    public function authenticate(Message\RequestInterface $request, array $options, User\UserInterface $user = null)
    {
        if ($user) {
            $authenticator = $this->getOauthInstance($user->getAccessKey(), $user->getAccessSecret());
        } else {
            $authenticator = $this->getOauthInstance();
        }

        $options['auth'] = 'oauth';
        
        $handler = $authenticator->__invoke(function($request) {
            return $request;
        });
        
        return $handler($request, $options);
    }
    
    private function getOauthInstance($token = null, $secret = null)
    {
        $hash = $token . ':' . $secret;
        
        $config = $this->config;
        $config['token'] = $token;
        $config['token_secret'] = $secret;
        return new Oauth\Oauth1($config);;
    }
}
