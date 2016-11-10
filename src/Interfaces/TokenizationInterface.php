<?php
/**
 * Interface TokenizationInterface
 *
 * @date   11/10/16
 * @author dennis
 */

namespace DennisLindsey\Tokenize\Interfaces;

/**
 * Interface TokenizationInterface
 *
 * All tokenization APIs should implement this interface to be properly consumed by the application
 */
interface TokenizationInterface
{
    /**
     * TokenizationInterface constructor.
     *
     * Call the init() method here
     */
    public function __construct($provider);

    /**
     * Initialize the API connection
     *
     * @param $provider
     * @param $connection
     */
    public function init($provider, $connection);

    /**
     * Waterfall the API connection to the next available endpoint for the provider.
     *
     * The API connections should be defined in `config/tokenization.php`
     *
     * @return mixed
     */
    public function reinitialize();

    /**
     * Tokenize some data, return a token in the format defined by $scheme
     *
     * @param $data
     * @param $scheme
     * @return mixed
     */
    public function store($data, $scheme);

    /**
     * Get tokenized data
     *
     * @param $token
     * @return mixed
     */
    public function get($token);

    /**
     * Ensure that a token exists in the database
     *
     * @param $token
     * @return mixed
     */
    public function validate($token);

    /**
     * Delete the token and tokenized data
     *
     * @param $token
     * @return mixed
     */
    public function delete($token);

    /**
     * Get errors from the most recent API action
     *
     * @return mixed
     */
    public function getErrors();

    /**
     * Get a reference number for the most recent API action
     *
     * @return mixed
     */
    public function getReferenceNumber();
}