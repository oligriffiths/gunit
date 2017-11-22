<?php

namespace OliGriffiths\GUnit\PHPUnit\Constraint;

use OliGriffiths\GUnit\Guzzle;
use Psr\Http\Message;

/**
 * Body constraint is used to validate a HTTP response body
 */
abstract class Body extends AbstractConstraint
{
    /**
     * @var mixed
     */
    private $decoded;

    /**
     * @var bool
     */
    private $auto_decode;

    public function __construct($expected, $auto_decode = true, $verbose = false)
    {
        parent::__construct($expected, $verbose);

        $this->auto_decode = $auto_decode;
    }

    /**
     * @param Message\ResponseInterface $response
     * @return bool
     */
    protected function matches($response)
    {
        $value = $response;
        if ($this->auto_decode) {
            try {
                $value = $this->decodeBody($response->getHeaderLine('Content-Type'), (string) $response->getBody());
            } catch (\UnexpectedValueException $e) {

            }
        }

        return $value == $this->getExpected();
    }

    /**
     * @inheritDoc
     */
    protected function getValueFromResult(Guzzle\Result $result)
    {
        return $result->getResponse();
    }

    /**
     * @param $value
     * @return string
     */
    public function failureText($value)
    {
        return 'body' . $this->toString();
    }

    /**
     * Decode the response body into an associative array
     *
     * @param string $content_type The response content type
     * @param string $body The response body
     * @return array
     * @throws \UnexpectedValueException If the content type is unsupported or the response is empty
     */
    protected function decodeBody($content_type, $body)
    {
        $body = trim($body);
        if (empty($body)) {
            throw new \UnexpectedValueException('The response is empty');
        }

        if ($this->decoded !== null) {
            return $this->decoded;
        }

        switch ($this->normalizeContentType($content_type)) {
            case 'application/json':
                return $this->decoded = $this->decodeJson($body);

            case 'application/x-www-form-urlencoded':
                return $this->decoded = $this->decodeFormUrlEncoded($body);

            default:
                throw new \UnexpectedValueException(sprintf(
                    'Unable to decode body, content type %s not supported',
                    $content_type
                ));
        }
    }

    /**
     * Decodes a JSON string to an assoc array
     *
     * @param string $body The response body
     * @return array
     * @throws \UnexpectedValueException If JSON decoding failed
     */
    protected function decodeJson($body)
    {
        $response = json_decode($body, true);

        if ($error = json_last_error()) {
            throw new \UnexpectedValueException(sprintf('Unable to deserialize json: %s', json_last_error_msg()));
        }

        return $response;
    }

    /**
     * Decodes a form-url-encoded string to an assoc array
     *
     * @param string $body The response body
     * @return array
     * @throws \UnexpectedValueException If the response was not form-url-encoded
     */
    protected function decodeFormUrlEncoded($body)
    {
        if (strpos($body, '=') === false) {
            throw new \UnexpectedValueException('Unable to form-url-decode response');
        }

        parse_str($body, $result);
        return $result;
    }

    /**
     * Normalize a content type, removes vendor specific content types
     *
     * @param string $content_type The content type header to normalize
     * @return string
     * @throws \InvalidArgumentException If the content type is not in the format type/subtype
     */
    protected function normalizeContentType($content_type)
    {
        list($content_type) = explode(';', $content_type, 2);
        $parts = explode('/',  trim($content_type), 2);

        if (empty($parts[1])) {
            throw new \InvalidArgumentException(sprintf('Content type is malformed: %s', $content_type));
        }

        list($type, $subtype) = $parts;
        $subtype_parts = explode('+', $subtype, 2);
        $subtype = array_pop($subtype_parts);

        return $type . '/' . $subtype;
    }
}
