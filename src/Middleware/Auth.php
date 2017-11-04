<?php

namespace OliGriffiths\GUnit\Middleware;

use Psr\Http\Message;

class Auth
{
    /**
     * @var array
     */
    private $auth;
    private $mode;
    private $user;

    /**
     * Auth constructor.
     *
     * @param array $auth
     */
    public function __construct(array $auth)
    {
        $this->auth = $auth;
    }


    public function __invoke(callable $handler)
    {
        return function(Message\RequestInterface $request, array $options) use ($handler) {

            $options['auth'] = $this->mode;

            // Execute next handler
            return $handler($request, $options);
        };
    }

    public function getAuth()
    {

    }

    public function setAuth($mode, $user)
    {
        $this->mode = $mode;
        $this->user = $user;
    }
}
