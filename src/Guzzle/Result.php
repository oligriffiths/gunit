<?php

namespace OliGriffiths\GUnit\Guzzle;

use Psr\Http\Message;

/**
 * GuzzleResult is a value object that wraps both the request & the response object of a guzzle call
 */
class Result
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

    /**
     * @return string
     */
    public function toString()
    {
        $request = $this->getRequest();
        $response = $this->getResponse();

        $output = [''];

        $output[] = 'Request: ';
        $output[] = sprintf(
            '%s %s HTTP/%s',
            $request->getMethod(),
            $request->getUri(),
            $request->getProtocolVersion()
        );
        $output[] = $this->renderMessage($request);
        $output[] = PHP_EOL;

        $output[] = 'Response: ';
        $output[] = sprintf(
            'HTTP/%s %d %s',
            $response->getProtocolVersion(),
            $response->getStatusCode(),
            $response->getReasonPhrase()
        );
        $output[] = $this->renderMessage($response);

        return implode(PHP_EOL, $output);
    }

    public function __toString()
    {
        return $this->toString();
    }

    private function renderMessage(Message\MessageInterface $message)
    {
        $output = [];
        foreach ($message->getHeaders() as $header => $values) {
            foreach ($values as $value) {
                $output[] = $header . ': ' . $value;
            }
        }

        $body = trim($message->getBody());
        if (empty($body)) {
            return implode(PHP_EOL, $output);
        }

        $output[] = '';

        $content_type = explode(';', $message->getHeaderLine('Content-Type'));
        $content_type = $content_type[0];

        if (preg_match('#application\/.*\+json#', $content_type)) {
            $content_type = 'application/json';
        }

        switch ($content_type) {
            case 'application/json':
                $output[] = json_encode(json_decode($body, true), JSON_PRETTY_PRINT);
                break;
            default:
                $output[] = $body;
                break;
        }

        return implode(PHP_EOL, $output);
    }

}
