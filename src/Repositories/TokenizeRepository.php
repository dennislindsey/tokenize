<?php
/**
 * Class TokenizeRepository
 *
 * @date   11/10/16
 * @author dennis
 */

namespace DennisLindsey\Tokenize\Repositories;

use Log;

// Implement the interface
use DennisLindsey\Tokenize\Interfaces\TokenizationInterface;

// Use custom exception classes
use DennisLindsey\Tokenize\Exceptions\TokenizeException;
use DennisLindsey\Tokenize\Exceptions\TokenizeValidationException;
use DennisLindsey\Tokenize\Exceptions\TokenizeConnectionException;

/**
 * Class TokenizeRepository
 *
 * Deals with all tokenization related features
 *
 * Specifically, this is where we handle the CRUD and retrieval of credit card details stored
 * in a "vault" with our tokenization provider (TokenEx).
 *
 * <h4>Example</h4>
 *
 * <code>
 *
 * // Initialize the connection
 * $tokenize = new TokenizeRepository;
 *
 * // Store some data in the vault and retrieve a token
 * $new_token = $tokenize->store([
 *      'num'    => 4242424242424242,
 *      'exp_mo' => 08,
 *      'exp_yr' => 16,
 *      'name'   => 'Dennis Lindsey',
 *      ...
 * ]); // string
 *
 * // Check that the token exists in the token vault
 * $valid_token = $tokenize->validate($new_token); // true | false
 *
 * // Get the tokenized data from the vault
 * $tokenized_data = $tokenize->get($new_token); // mixed
 *
 * // Delete a token from the vault
 * $token_deleted = $tokenize->delete($new_token); // true | false
 *
 * // Get a reference number for the action that was just completed
 * $action_reference = $tokenize->getReferenceNumber(); // string
 *
 * // Get any errors that occurred during the action that was just attempted
 * $action_errors = $tokenize->getErrors(); // array
 *
 * </code>
 *
 * @link http://docs.tokenex.com/#tokenex-api-token-services
 * @link https://github.com/cliffom/tokenex-php
 */
class TokenizeRepository implements TokenizationInterface
{
    protected $tokenizer  = null;
    protected $provider   = 'TokenEx';
    protected $connection = 0;
    private   $crypt      = null;

    /**
     * TokenizeRepository constructor.
     *
     * Instantiates the Tokenizer object with our TokenEx API credentials
     *
     * @param string $provider
     */
    public function __construct($provider = 'TokenEx')
    {
        if (function_exists('app')) {
            $this->crypt = app()['encrypter'];
        }
        $this->init($provider);
    }

    /**
     * Initialize an API connection to the specified provider
     *
     * @param string $provider
     * @param int    $connection
     * @throws TokenizeConnectionException
     * @see http://docs.tokenex.com/#tokenex-api-authentication
     */
    public function init($provider = 'TokenEx', $connection = 0)
    {
        // TODO: provide some method of getting config options when this package is used in a non-Laravel installation
        $sandbox = config("tokenization.$provider.$connection.sandbox", true);
        $id      = config("tokenization.$provider.$connection.id");
        $apiKey  = config("tokenization.$provider.$connection.apiKey");

        if (is_null($sandbox) || is_null($id) || is_null($apiKey)) {
            throw new TokenizeConnectionException('No API connection available');
        }

        $this->provider   = $provider;
        $this->connection = $connection;

        $tokenizerClass = "DennisLindsey\\Tokenize\\TokenProviders\\$this->provider\\Tokenizer";

        $this->tokenizer = new $tokenizerClass($sandbox == true, $id, $apiKey);
    }

    /**
     * Waterfall the API connection to the next available server, if there is one
     */
    public function reinitialize()
    {
        try {
            $this->connection++;
            $this->init($this->provider, $this->connection);
        } catch (TokenizeConnectionException $e) {
            throw new TokenizeConnectionException('Could not create waterfall connection');
        }
    }

    /**
     * Tokenize is the method that you would call in order to tokenize a given data set.
     *
     * You will need to provide the data you wish to tokenize and your desired token scheme.
     *
     * @param mixed  $data
     * @param string $scheme
     * @return string
     * @see http://docs.tokenex.com/#tokenex-api-token-services-tokenize
     */
    public function store($data, $scheme = "GUID")
    {
        try {
            $schemeClass = "DennisLindsey\\Tokenize\\TokenProviders\\$this->provider\\TokenScheme";
            $schemeClass = new \ReflectionClass($schemeClass);
            $scheme       = $schemeClass->getConstant($scheme);

            $data  = $this->encodeData($data);
            $token = $this->tokenizer->tokenize($data, $scheme);

            $this->validate($token);

            return $token;
        } catch (\Exception $e) {
            // Log exception
            $this->logData($e);
            // Create connection to backup server
            $this->reinitialize();
            // Try again
            $this->store($data, $scheme);
        }
    }

    /**
     * Get the data from a token.
     *
     * @param string $token
     * @return mixed
     * @throws TokenizeValidationException
     * @see http://docs.tokenex.com/#tokenex-api-token-services-detokenize
     */
    public function get($token)
    {
        if (!$this->validate($token)) {
            throw new TokenizeValidationException('Token does not exist within the token vault');
        }

        $data = $this->tokenizer->detokenize($token);

        $data = $this->decodeData($data);

        return $data;
    }

    /**
     * Validates if the given token exists within the token vault.
     *
     * @param string $token
     * @return bool
     * @throws \Exception
     * @see http://docs.tokenex.com/#tokenex-api-token-services-validate-token
     */
    public function validate($token)
    {
        try {
            return ($this->tokenizer->validateToken($token) == true); // true or false
        } catch (\Exception $e) {
            // Log exception
            $this->logData($e);
            throw $e;
        }
    }

    /**
     * Deletes a token from the tokenization provider
     *
     * TokenEx currently charges us on a per-token basis, so we don't want to store tokens
     * if we have full confidence that they will never be used in the future.
     *
     * @param string $token
     * @return bool
     * @throws \Exception
     * @see http://docs.tokenex.com/#tokenex-api-token-services-delete-token
     */
    public function delete($token)
    {
        try {
            return ($this->tokenizer->deleteToken($token) == true); // true or false
        } catch (\Exception $e) {
            // Log exception
            $this->logData($e);
            throw $e;
        }
    }

    /**
     * Get errors from the most recent action, if there are any.
     *
     * Will return an empty array if there are no errors to report.
     *
     * @return array
     */
    public function getErrors()
    {
        return (!is_null($this->tokenizer) ? $this->tokenizer->error : []);
    }

    /**
     * Get the reference number from the most recent action, if one is available
     *
     * This can be used to do a lookup on the TokenEx dashboard for information about the action
     *
     * @return string|null
     */
    public function getReferenceNumber()
    {
        return (!is_null($this->tokenizer) ? $this->tokenizer->reference_number : null);
    }

    /**
     * Get usage stats for the given account
     *
     * @return mixed
     * @throws \Exception
     * @see http://docs.tokenex.com/#tokenex-api-reporting-services-get-usage-stats
     */
    public function getUsageStats()
    {
        try {
            return $this->tokenizer->getUsageStats();
        } catch (\Exception $e) {
            $this->logData($e);
            throw $e;
        }
    }

    /**
     * Get the total number of tokens for the given account
     *
     * @return mixed
     * @throws \Exception
     * @see http://docs.tokenex.com/#tokenex-api-reporting-services-get-usage-stats
     */
    public function getTokenCount()
    {
        try {
            return $this->tokenizer->getTokenCount();
        } catch (\Exception $e) {
            $this->logData($e);
            throw $e;
        }
    }

    /**
     * If the Laravel Log facade is available then log the data
     *
     * @param $data
     */
    private function logData($data)
    {
        if (class_exists('Log') && method_exists('Log', 'info')) {
            Log::info(print_r($data, true));
        }
    }

    /**
     * Encode and encrypt $data into a string to be stored in the tokenizer's vault
     *
     * @param      $data
     * @param bool $encrypt
     * @return string
     * @throws TokenizeValidationException
     */
    private function encodeData($data, $encrypt = true)
    {
        if (($data = json_encode($data)) === false) {
            throw new TokenizeValidationException('Could not encode data');
        }

        if ($encrypt) {
            $data = $this->crypt->encrypt($data);
        }

        return $data;
    }

    /**
     * Decrypt and decode the $data retrieved from the tokenizer's vault
     *
     * @param      $data
     * @param bool $decrypt
     * @return mixed
     * @throws TokenizeValidationException
     */
    private function decodeData($data, $decrypt = true)
    {
        if ($decrypt) {
            $data = $this->crypt->decrypt($data);
        }

        if (($data = json_decode($data, true)) === null) {
            throw new TokenizeValidationException('Could not decode data');
        }

        return $data;
    }
}