<?php
/**
 * Class Environment
 *
 * @date   11/10/16
 * @author dennis
 */

namespace DennisLindsey\Tokenize\TokenProviders\TokenEx;

use Log;
use GuzzleHttp\Client;
use GuzzleHttp\GuzzleClient;

class Environment
{
    protected $apiBaseUrl;
    protected $id;
    protected $apiKey;

    public $error;
    public $reference_number;

    /**
     * Environment constructor.
     *
     * @param bool   $sandbox
     * @param string $id
     * @param string $apiKey
     * @throws \Exception
     */
    public function __construct($sandbox = true, $id = '', $apiKey = '')
    {
        $this->apiBaseUrl = ($sandbox ? RequestParams::SandboxURL : RequestParams::LiveURL);
        $this->id         = $id;
        $this->apiKey     = $apiKey;

        if (!class_exists("GuzzleHttp\\Client")) {
            throw new \Exception("dennislindsey/tokenize requires \"guzzlehttp/guzzle v5\"");
        }
    }

    /**
     * Build the request data
     *
     * @param $data
     * @return array
     */
    private function getRequestArray($data)
    {
        return array_merge(
            [
                RequestParams::APIKey    => $this->apiKey,
                RequestParams::TokenExID => $this->id
            ],
            $data
        );
    }

    /**
     * Send request to the API
     *
     * @param       $action
     * @param array $data
     * @return mixed
     */
    protected function sendRequest($action, $data = [])
    {
        // CURL_SSLVERSION_TLSv1_2 is defined in libcurl version 7.34 or later
        // but unless PHP has been compiled with the correct libcurl headers it
        // won't be defined in your PHP instance.  PHP > 5.5.19 or > 5.6.3
        if (!defined('CURL_SSLVERSION_TLSv1_2')) {
            define('CURL_SSLVERSION_TLSv1_2', 6);
        }

        $url        = rtrim($this->apiBaseUrl, '/') . '/' . $action['Name'];
        $httpClient = new Client();
        $response   = $httpClient->post($url, [
            'json'   => $this->getRequestArray($data),
            'config' => [
                'curl' => [
                    CURLOPT_SSLVERSION => CURL_SSLVERSION_TLSv1_2
                ]
            ]
        ]);

        $result = $response->getBody(true);

        $this->logData($result);
        //decode result
        $jsonResult = json_decode($result, true);
        $this->logData($jsonResult);
        $this->isValidResponse($jsonResult);

        return $jsonResult[$action['Key']];
    }

    /**
     * Check if response is correctly formatted
     *
     * @param $response
     * @return bool
     */
    private function isValidResponse($response)
    {
        $this->error            = empty($response[ResponseParams::Error]) ? [] : $this->errorFromResponse($response[ResponseParams::Error]);
        $this->reference_number = empty($response[ResponseParams::ReferenceNumber]) ? '' : $response[ResponseParams::ReferenceNumber];

        return isset($response[ResponseParams::Success]) &&
        $response[ResponseParams::Success] === true;
    }

    /**
     * Get any errors from the response body
     *
     * @param $response
     * @return array
     */
    private function errorFromResponse($response)
    {
        $responseArray = explode(' : ', $response);

        return [
            'code'    => intval($responseArray[0]),
            'message' => $responseArray[1]
        ];
    }

    /**
     * Info logging
     *
     * @param $data
     */
    private function logData($data)
    {
        if (class_exists('Log') && method_exists('Log', 'info')) {
            Log::info(print_r($data, true));
        }
    }
}
