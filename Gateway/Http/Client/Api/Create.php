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

class Create extends Client
{
    /**
     * @param $data
     * @return array
     */
    public function execute($data, $storeId = null): array
    {
        $path = $this->getEndpointPath('payments/create');
        $method = Request::METHOD_POST;
        return $this->makeRequest($path, $method, $data, $storeId);
    }
}
