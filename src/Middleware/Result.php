<?php

namespace OliGriffiths\GUnit\Middleware;

use OliGriffiths\GUnit;
use Psr\Http\Message;
use GuzzleHttp\Promise;

class Result
{
    public function __invoke(callable $handler)
    {
        return function(Message\RequestInterface $request, array $options) use ($handler) {

            // Execute next handler
            $promise = $handler($request, $options);

            // Get response
            $response = $promise->wait();

            return new Promise\FulfilledPromise(new GUnit\Guzzle\Result($request, $response));
        };
    }
}
