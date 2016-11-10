<?php
/**
 * Class Tokenizer
 *
 * @date   11/10/16
 * @author dennis
 */

namespace DennisLindsey\Tokenize\TokenProviders\TokenEx;

/**
 * Class Tokenizer
 *
 * Provides the methods to handle all tokenization actions for the Test provider.
 *
 * This class should only be used through DennisLindsey\Tokenize\Repositories\TokenizeRepository
 */
class Tokenizer extends Environment
{
    /**
     * Tokenize a piece of data
     *
     * @param     $data
     * @param int $tokenScheme
     * @return mixed
     */
    public function tokenize($data, $tokenScheme = TokenScheme::GUID)
    {
        $requestParams = [
            RequestParams::Data        => $data,
            RequestParams::TokenScheme => $tokenScheme
        ];

        return $this->sendRequest(TokenAction::Tokenize, $requestParams);
    }

    /**
     * Tokenize a piece of encrypted data
     *
     * @param $encryptedData
     * @param $tokenScheme
     * @return mixed
     */
    public function tokenizeFromEncryptedData($encryptedData, $tokenScheme)
    {
        $requestParams = [
            RequestParams::EncryptedData => $encryptedData,
            RequestParams::TokenScheme   => $tokenScheme
        ];

        return $this->sendRequest(TokenAction::TokenizeFromEncryptedValue, $requestParams);
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
        $requestParams = [
            RequestParams::Token => $token
        ];

        return $this->sendRequest(TokenAction::ValidateToken, $requestParams);
    }

    /**
     * Get the previously tokenized data
     *
     * @param $token
     * @return bool|mixed
     */
    public function detokenize($token)
    {
        $requestParams = [
            RequestParams::Token => $token
        ];

        return $this->sendRequest(TokenAction::Detokenize, $requestParams);
    }

    /**
     * Delete the tokenized data from the data store
     *
     * @param $token
     * @return bool
     */
    public function deleteToken($token)
    {
        $requestParams = [
            RequestParams::Token => $token
        ];

        return $this->sendRequest(TokenAction::DeleteToken, $requestParams);
    }

    /**
     * Get usage statistics from your TokenEx account
     *
     * @return mixed
     */
    public function getUsageStats()
    {
        return $this->sendRequest(TokenAction::GetUsageStats);
    }

    /**
     * Get the total number of tokens stored in your TokenEx account
     *
     * @return mixed
     */
    public function getTokenCount()
    {
        return $this->sendRequest(TokenAction::GetTokenCount);
    }
}
