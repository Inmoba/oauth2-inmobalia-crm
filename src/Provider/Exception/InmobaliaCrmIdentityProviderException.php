<?php

namespace Inmobalia\OAuth2\Client\Provider\Exception;

use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use Psr\Http\Message\ResponseInterface;

class InmobaliaCrmIdentityProviderException extends IdentityProviderException
{
    /**
     * @var ResponseInterface $response
     */
    protected $response;

    /**
     * @var string $responseBody
     */
    protected $responseBody;

    /**
     * @var array $data
     */
    protected $data;

    /**
     * @param string            $message
     * @param ResponseInterface $response The response
     * @param array             $data
     */
    public function __construct($message, $response, $data)
    {
        parent::__construct($message, $response->getStatusCode(), $this->responseBody);

        $this->response = $response;
        $this->responseBody = (string) $response->getBody();
        $this->data = $data;
    }

    /**
     * Returns the exception's response body.
     *
     * @return string
     */
    public function getResponseBody()
    {
        return $this->responseBody;
    }

    /**
     * Returns the exception's response message.
     *
     * @return \Psr\Http\Message\MessageInterface
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * Returns the exception's response message.
     *
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * Creates identity exception from response.
     *
     * @param ResponseInterface $response
     * @param array             $data
     * @param string            $message
     *
     * @return InmobaliaCrmIdentityProviderException
     */
    public static function fromResponse(ResponseInterface $response, $data)
    {
        if (isset($data['error_description'])) {
            $message = $data['error_description'];
        } elseif (isset($data['error'])) {
            $message = $data['error'];
        } else {
            $message = $response->getReasonPhrase();
        }

        return new InmobaliaCrmIdentityProviderException($message, $response, $data);
    }
}
