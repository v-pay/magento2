<?php

/**
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    VirtualPay
 * @package     VirtualPay_Payment
 *
 */

namespace VirtualPay\Payment\Gateway\Http\Client\Api;

use VirtualPay\Payment\Gateway\Http\Client;
use Laminas\Http\Request;

class Query extends Client
{
    public function execute(string $transactionId, $storeId = null): array
    {
        $path = $this->getEndpointPath('payments/get', $transactionId);
        $method = Request::METHOD_GET;
        return $this->makeRequest($path, $method, [], $storeId);
    }
}
