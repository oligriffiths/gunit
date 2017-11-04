<?php

namespace OliGriffiths\GUnit;

use Psr\Http\Message;

/**
 * GuzzleResult is a value object that wraps both the request & the response object of a guzzle call
 */
class GuzzleResult
{
    /**
     * @var Message\RequestInterface
     */
    private $request;

    /**
     * @var Message\ResponseInterface
     */
    private $response;

    /**
     * GuzzleResult constructor.
     *
     * @param Message\RequestInterface $request
     * @param Message\ResponseInterface $response
     */
    public function __construct(Message\RequestInterface $request, Message\ResponseInterface $response)
    {
        $this->request = $request;
        $this->response = $response;
    }

    /**
     * @return Message\RequestInterface
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * @return Message\ResponseInterface
     */
    public function getResponse()
    {
        return $this->response;
    }
}
