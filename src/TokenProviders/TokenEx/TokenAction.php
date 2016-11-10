<?php
/**
 * Class TokenAction
 *
 * @date   11/10/16
 * @author dennis
 */

namespace DennisLindsey\Tokenize\TokenProviders\TokenEx;

class TokenAction
{
    const Tokenize = [
        'Name' => 'TokenServices.svc/REST/Tokenize',
        'Key'  => 'Token'
    ];

    const TokenizeFromEncryptedValue = [
        'Name' => 'TokenServices.svc/REST/TokenizeFromEncryptedValue',
        'Key'  => 'Token'
    ];

    const ValidateToken = [
        'Name' => 'TokenServices.svc/REST/ValidateToken',
        'Key'  => 'Valid'
    ];

    const Detokenize = [
        'Name' => 'TokenServices.svc/REST/Detokenize',
        'Key'  => 'Value'
    ];

    const DeleteToken = [
        'Name' => 'TokenServices.svc/REST/DeleteToken',
        'Key'  => 'Success'
    ];

    const GetUsageStats = [
        'Name' => 'ReportingServices.svc/REST/GetUsageStats',
        'Key'  => 'UsageStats'
    ];

    const GetTokenCount = [
        'Name' => 'ReportingServices.svc/REST/GetUsageStats',
        'Key'  => 'TokenCount'
    ];
}
