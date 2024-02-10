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
 *
 */

namespace VirtualPay\Payment\Gateway\Http\Client;

use VirtualPay\Payment\Gateway\Http\Client\Api\Create;
use VirtualPay\Payment\Gateway\Http\Client\Api\Query;
use VirtualPay\Payment\Helper\Data;

class Api
{
    /**
     * @var Data
     */
    private $helper;

    /**
     * @var Create
     */
    private $create;

    /**
     * @var Query
     */
    private $query;

    public function __construct(
        Data $helper,
        Create $create,
        Query $query
    ) {
        $this->helper = $helper;
        $this->create = $create;
        $this->query = $query;
    }

    public function create(): Create
    {
        return $this->create;
    }

    public function query(): Query
    {
        return $this->query;
    }

    /**
     * @param $request
     * @param string $name
     */
    public function logRequest($request, $name = 'virtualpay'): void
    {
        $this->helper->log('Request', $name);
        $this->helper->log($request, $name);
    }

    /**
     * @param $response
     * @param string $name
     */
    public function logResponse($response, $name = 'virtualpay'): void
    {
        $this->helper->log('RESPONSE', $name);
        $this->helper->log($response, $name);
    }

    /**
     * @param $request
     * @param $response
     * @param $statusCode
     * @return void
     */
    public function saveRequest(
        $request,
        $response,
        $statusCode,
        $method = \VirtualPay\Payment\Model\Ui\CreditCard\ConfigProvider::CODE
    ): void {
        $this->helper->saveRequest($request, $response, $statusCode, $method);
    }
}
