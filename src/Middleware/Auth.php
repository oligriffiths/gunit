<?php

namespace OliGriffiths\GUnit\Middleware;

use OliGriffiths\GUnit;
use Psr\Http\Message;

class Auth
{
    /**
     * @var array
     */
    private $authenticators;
    private $users;
    
    private $test_auth_mode;
    private $test_auth_user;

    /**
     * Auth constructor.
     *
     * @param array $auth
     */
    public function __construct(array $authenticators, array $users)
    {
        $this->authenticators = $authenticators;
        $this->users = $users;
    }


    public function __invoke(callable $handler)
    {
        return function(Message\RequestInterface $request, array $options) use ($handler) {

            $auth_mode = $options['auth_mode'];
            $auth_user = $options['auth_user'];
            unset($options['auth_mode'], $options['auth_user']);
            
            if ($auth_mode || $auth_user) {
                $request = $this->getAuth($request, $options, $auth_mode, $auth_user);
            }

            // Execute next handler
            return $handler($request, $options);
        };
    }
    
    private function getAuth(Message\RequestInterface $request, array $options, $mode = null, $user = null)
    {
        if ($user) {
            $user = $this->getUser($user);
            
            if (!$mode) {
                $mode = $user->getAuthenticator();
            }
        }
        
        if (!$mode) {
            throw new \UnexpectedValueException('Auth set but no auth mode');
        }

        $authenticator  = $this->getAuthenticator($mode);
        
        if (!$authenticator) {
            throw new \UnexpectedValueException(sprintf(
                'Auth mode set to %s but no authenticator found',
                $mode
            ));
        }
        
        return $authenticator->authenticate($request, $options, $user);
    }

    /**
     * @param string $user
     * @return GUnit\User\UserInterface
     */
    private function getUser($user)
    {
        return isset($this->users[$user]) ? $this->users[$user] : null; 
    }

    /**
     * @param string $mode
     * @return GUnit\Authenticator\AuthenticatorInterface
     */
    private function getAuthenticator($mode)
    {
        return isset($this->authenticators[$mode]) ? $this->authenticators[$mode] : null;
    }
}
