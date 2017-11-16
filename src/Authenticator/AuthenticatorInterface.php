<?php

namespace OliGriffiths\GUnit\Authenticator;

use OliGriffiths\GUnit\User;
use Psr\Http\Message;

interface AuthenticatorInterface
{
    public function authenticate(Message\RequestInterface $request, array $options, User\UserInterface $user = null);
}
