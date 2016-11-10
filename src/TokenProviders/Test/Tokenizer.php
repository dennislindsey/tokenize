<?php
/**
 * Class Tokenizer
 *
 * @date   11/10/16
 * @author dennis
 */

namespace DennisLindsey\Tokenize\TokenProviders\Test;

use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\Exception\UnsatisfiedDependencyException;

/**
 * Class Tokenizer
 *
 * Provides the methods to handle all tokenization actions for the Test provider.
 *
 * This class should only be used through DennisLindsey\Tokenize\Repositories\TokenizeRepository
 */
class Tokenizer
{
    protected $tokens = [];

    protected $apiBaseUrl;
    protected $id;
    protected $apiKey;

    public $error;
    public $reference_number;

    /**
     * Tokenizer constructor.
     *
     * @param string $apiBaseUrl
     * @param string $id
     * @param string $apiKey
     */
    public function __construct($apiBaseUrl = '', $id = '', $apiKey = '')
    {
        $this->apiBaseUrl = $apiBaseUrl;
        $this->id         = $id;
        $this->apiKey     = $apiKey;
    }

    /**
     * Tokenize a piece of data
     *
     * @param     $data
     * @param int $tokenScheme
     * @return mixed
     */
    public function tokenize($data, $tokenScheme = TokenScheme::GUID)
    {
        $uuid5                            = Uuid::uuid5(Uuid::NAMESPACE_DNS, $this->getServerURL());
        $this->tokens[$uuid5->toString()] = $data;

        // i.e. c4a760a8-dbcf-5254-a0d9-6a4474bd1b62
        return $uuid5->toString();
    }

    /**
     * Tokenize a piece of encrypted data
     *
     * @param $encryptedData
     * @param $token_scheme
     * @return mixed
     */
    public function tokenizeFromEncryptedData($encryptedData, $token_scheme)
    {
        return $this->tokenize($encryptedData, $token_scheme);
    }

    /**
     * Tokenize a credit card number
     *
     * @param $ccnum
     * @return mixed
     */
    public function tokenizeFromCreditCardNumber($ccnum)
    {
        return $this->tokenize($ccnum, TokenScheme::TOKENfour);
    }

    /**
     * Validate that the token exists in the data store
     *
     * @param $token
     * @return bool
     */
    public function validateToken($token)
    {
        if (array_key_exists($token, $this->tokens)) {
            return true;
        }

        return false;
    }

    /**
     * Get the previously tokenized data
     *
     * @param $token
     * @return bool|mixed
     */
    public function detokenize($token)
    {
        if (array_key_exists($token, $this->tokens)) {
            $data = $this->tokens[$token];

            return $data;
        }

        return false;
    }

    /**
     * Delete the tokenized data from the data store
     *
     * @param $token
     * @return bool
     */
    public function deleteToken($token)
    {
        if (array_key_exists($token, $this->tokens)) {
            unset($this->tokens[$token]);

            return true;
        }

        return false;
    }

    /**
     * Return the server's URL. Used for creating UUIDs.
     *
     * @return string
     */
    private function getServerURL()
    {
        if (array_key_exists('HTTP_HOST', $_SERVER)) {
            return $_SERVER['HTTP_HOST'];
        } elseif (array_key_exists('SERVER_NAME', $_SERVER)) {
            return $_SERVER['SERVER_NAME'];
        }

        return 'example.net';
    }
}
