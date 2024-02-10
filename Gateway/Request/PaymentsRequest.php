<?php

/**
 *
 *
 *
 *
 *
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

namespace VirtualPay\Payment\Gateway\Request;

use VirtualPay\Payment\Gateway\Http\Client\Api;
use VirtualPay\Payment\Helper\Data;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Payment\Gateway\ConfigInterface;
use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Sales\Model\Order;

class PaymentsRequest
{
    /**
     * @var ConfigInterface
     */
    protected $config;

    /**
     * @var DateTime
     */
    protected $date;

    /**
     * @var Data
     */
    protected $helper;

    /**
     * @var CustomerSession $customerSession
     */
    protected $customerSession;

    /**
     * @var ManagerInterface
     */
    protected $eventManager;

    /**
     * @var DateTime
     */
    protected $dateTime;

    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var CategoryRepositoryInterface
     */
    protected $categoryRepository;

    /**
     * @var string
     */
    protected $currencyCode;

    /**
     * @var Api
     */
    protected $api;

    public function __construct(
        ManagerInterface $eventManager,
        Data $helper,
        DateTime $date,
        ConfigInterface $config,
        CustomerSession $customerSession,
        DateTime $dateTime,
        ProductRepositoryInterface $productRepository,
        CategoryRepositoryInterface $categoryRepository,
        Api $api
    ) {
        $this->eventManager = $eventManager;
        $this->helper = $helper;
        $this->date = $date;
        $this->config = $config;
        $this->customerSession = $customerSession;
        $this->dateTime = $dateTime;
        $this->productRepository = $productRepository;
        $this->categoryRepository = $categoryRepository;
        $this->api = $api;
    }

    protected function getTransaction(Order $order, float $amount): array
    {
        return [
            'name' => $order->getCustomerName(),
            'email' => $order->getCustomerEmail(),
            'phone' => $this->getCustomerPhoneNumber($order),
            'description' => $this->getDescription($order),
            'document' => $this->getCustomerTaxvat($order),
            'amount' => $amount,
            'platform' => $this->helper->getPlatform(),
            'reference' => $order->getIncrementId()
        ];
    }

    protected function getDescription(Order $order): string
    {
        $storeName = $this->helper->getStoreName();
        $remoteIp = $order->getRemoteIp();
        return sprintf('Order %s from %s: IP: %s', $order->getIncrementId(), $storeName, $remoteIp);
    }

    protected function getCustomerPhoneNumber(Order $order): string
    {
        $phoneNumber = $order->getBillingAddress()->getTelephone() ?: '';
        return $this->helper->formatPhoneNumber($phoneNumber);
    }

    public function getCustomerTaxVat(Order $order): string
    {
        $address = $order->getBillingAddress();
        $customerTaxVat = $address->getVatId() ?: $order->getCustomerTaxvat();
        $virtualpayCustomerTaxVat = (string) $order->getPayment()->getAdditionalInformation('virtualpay_customer_taxvat');
        if ($virtualpayCustomerTaxVat) {
            $customerTaxVat = $virtualpayCustomerTaxVat;
        }

        return $customerTaxVat;
    }
}
