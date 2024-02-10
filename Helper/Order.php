<?php

/**
 * VirtualPay
 *
 * @category    VirtualPay
 * @package     VirtualPay_Payment
 */

namespace VirtualPay\Payment\Helper;

use BaconQrCode\Renderer\ImageRenderer as QrCodeImageRenderer;
use BaconQrCode\Renderer\Image\ImagickImageBackEnd as QrCodeImagickImageBackEnd;
use BaconQrCode\Renderer\RendererStyle\RendererStyle as QrCodeRendererStyle;
use BaconQrCode\Writer as QrCodeWritter;
use Magento\Framework\Exception\LocalizedException;
use VirtualPay\Payment\Helper\Data as HelperData;
use VirtualPay\Payment\Gateway\Http\Client;
use VirtualPay\Payment\Gateway\Http\Client\Api;
use VirtualPay\Payment\Model\Ui\CreditCard\ConfigProvider as CcConfigProvider;
use Magento\Framework\App\Config\Initial;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Filesystem;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\View\LayoutFactory;
use Magento\Payment\Model\Config;
use Magento\Payment\Model\Method\Factory;
use Magento\Sales\Model\Order as SalesOrder;
use Magento\Sales\Model\Order\CreditmemoFactory;
use Magento\Sales\Model\Order\Invoice;
use Magento\Sales\Model\Order\InvoiceRepository;
use Magento\Sales\Model\Order\Payment;
use Magento\Sales\Model\OrderFactory;
use Magento\Sales\Model\OrderRepository;
use Magento\Sales\Model\ResourceModel\Order\Payment as ResourcePayment;
use Magento\Sales\Model\ResourceModel\Order\Status\CollectionFactory;
use Magento\Sales\Model\Service\CreditmemoService;
use Magento\Store\Model\App\Emulation;

class Order extends \Magento\Payment\Helper\Data
{
    const STATUS_APPROVED = 6;

    const STATUS_PENDING = 4;

    const STATUS_DENIED = 7;

    const STATUS_REFUNDED = 89;

    const STATUS_CONTESTATION = 24;

    const STATUS_CHARGEBACK = 24;

    const STATUS_MONITORING = 87;

    const DEFAULT_QRCODE_WIDTH = 400;
    const DEFAULT_QRCODE_HEIGHT = 400;
    const DEFAULT_EXPIRATION_TIME = 30;

    /**
     * @var HelperData
     */
    protected $helperData;

    /**
     * @var OrderFactory
     */
    protected $orderFactory;

    /**
     * @var OrderFactory
     */
    protected $orderRepository;

    /**
     * @var InvoiceRepository
     */
    protected $invoiceRepository;

    /**
     * @var CreditmemoFactory
     */
    protected $creditmemoFactory;

    /**
     * @var CreditmemoService
     */
    protected $creditmemoService;

    /**
     * @var ResourcePayment
     */
    protected $resourcePayment;

    /**
     * @var CollectionFactory
     */
    protected $orderStatusCollectionFactory;

    /**
     * @var Filesystem
     */
    protected $filesystem;

    /** @var Client */
    protected $client;

    /** @var Api */
    protected $api;

    /**
     * @var DateTime
     */
    protected $dateTime;

    /**
     * Order constructor.
     * @param Context $context
     * @param LayoutFactory $layoutFactory
     * @param Factory $paymentMethodFactory
     * @param Emulation $appEmulation
     * @param Config $paymentConfig
     * @param Initial $initialConfig
     * @param OrderFactory $orderFactory
     * @param CreditmemoFactory $creditmemoFactory
     * @param OrderRepository $orderRepository
     * @param InvoiceRepository $invoiceRepository
     * @param CreditmemoService $creditmemoService
     * @param ResourcePayment $resourcePayment
     * @param CollectionFactory $orderStatusCollectionFactory
     * @param Filesystem $filesystem
     * @param Client $client
     * @param Api $api
     * @param DateTime $dateTime
     * @param Data $helperData
     */
    public function __construct(
        Context $context,
        LayoutFactory $layoutFactory,
        Factory $paymentMethodFactory,
        Emulation $appEmulation,
        Config $paymentConfig,
        Initial $initialConfig,
        OrderFactory $orderFactory,
        CreditmemoFactory $creditmemoFactory,
        OrderRepository $orderRepository,
        InvoiceRepository $invoiceRepository,
        CreditmemoService $creditmemoService,
        ResourcePayment $resourcePayment,
        CollectionFactory $orderStatusCollectionFactory,
        Filesystem $filesystem,
        Client $client,
        Api $api,
        DateTime $dateTime,
        HelperData $helperData
    ) {
        parent::__construct($context, $layoutFactory, $paymentMethodFactory, $appEmulation, $paymentConfig, $initialConfig);

        $this->helperData = $helperData;
        $this->orderFactory = $orderFactory;
        $this->orderRepository = $orderRepository;
        $this->invoiceRepository = $invoiceRepository;
        $this->creditmemoFactory = $creditmemoFactory;
        $this->creditmemoService = $creditmemoService;
        $this->resourcePayment = $resourcePayment;
        $this->filesystem = $filesystem;
        $this->dateTime = $dateTime;
        $this->client = $client;
        $this->api = $api;
        $this->orderStatusCollectionFactory = $orderStatusCollectionFactory;
    }

    /**
     * Update Order Status
     *
     * @param SalesOrder $order
     * @param string $virtualpayStatus
     * @param array $content
     * @param float $amount
     * @param bool $callback
     * @return bool
     */
    public function updateOrder(
        SalesOrder $order,
        string $virtualpayStatus,
        array $content,
        float $amount,
        bool $callback = false
    ): bool {
        try {
            /** @var Payment $payment */
            $payment = $order->getPayment();
            $orderStatus = $payment->getAdditionalInformation('status');
            $order->addCommentToStatusHistory(__('Callback received %1 -> %2', $orderStatus, $virtualpayStatus));

            if ($virtualpayStatus != $orderStatus) {
                if ($virtualpayStatus == self::STATUS_APPROVED) {
                    if ($order->canInvoice()) {
                        $this->invoiceOrder($order, $amount);
                    }

                    $updateStatus = $order->getIsVirtual()
                        ? $this->helperData->getConfig('paid_virtual_order_status')
                        : $this->helperData->getConfig('paid_order_status');

                    $message = __('Your payment for the order %1 was confirmed', $order->getIncrementId());
                    $order->addCommentToStatusHistory($message, $updateStatus, true);
                } elseif (
                    $this->helperData->getGeneralConfig('cancel_unapproved_orders', $order->getStoreId())
                ) {
                    if ($virtualpayStatus == self::STATUS_DENIED) {
                        $order = $this->cancelOrder($order, $amount, $callback);
                    } elseif ($virtualpayStatus == self::STATUS_REFUNDED) {
                        $order = $this->refundOrder($order, $amount, $callback);
                    }
                }

                $payment->setAdditionalInformation('status', $virtualpayStatus);
                if (isset($content['status_name'])) {
                    $payment->setAdditionalInformation('status_name', $content['status_name']);
                }
            }

            $this->orderRepository->save($order);
            $this->savePayment($payment);

            return true;
        } catch (\Exception $e) {
            $this->helperData->log($e->getMessage());
        }

        return false;
    }

    /**
     * @param Payment $payment
     * @return void
     * @throws \Magento\Framework\Exception\AlreadyExistsException
     */
    public function savePayment($payment)
    {
        $this->resourcePayment->save($payment);
    }

    /**
     * @param SalesOrder $order
     * @param float $amount
     */
    protected function invoiceOrder(SalesOrder $order, $amount): void
    {
        /** @var Payment $payment */
        $payment = $order->getPayment();
        $payment->setParentTransactionId($payment->getLastTransId());
        $payment->registerCaptureNotification($amount);
    }

    /**
     * @param SalesOrder $order
     * @param float $amount
     * @param boolean $callback
     * @return SalesOrder $order
     *@throws \Magento\Framework\Exception\LocalizedException
     */
    public function cancelOrder(SalesOrder $order, float $amount, bool $callback = false): SalesOrder
    {
        if ($order->canCreditmemo()) {
            $creditMemo = $this->creditmemoFactory->createByOrder($order);
            $this->creditmemoService->refund($creditMemo, true);
        } elseif ($order->canCancel()) {
            $order->cancel();
        }

        $cancelledStatus = $this->helperData->getConfig(
            'cancelled_order_status',
            $order->getPayment()->getMethod(),
            'payment',
            $order->getStoreId()
        ) ?: false;

        $order->addCommentToStatusHistory(__('The order %1 was cancelled. Amount of %2', $cancelledStatus, $amount));

        return $order;
    }

    /**
     * @param SalesOrder $order
     * @param float $amount
     * @param bool $callback
     * @return mixed
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function refundOrder(SalesOrder $order, float $amount, bool $callback = false): SalesOrder
    {
        if ($order->getBaseGrandTotal() == $amount) {
            return $this->cancelOrder($order, $amount, $callback);
        }

        $totalRefunded = (float) $order->getTotalRefunded() + $amount;
        $order->setTotalRefunded($totalRefunded);
        $order->addCommentToStatusHistory(__('The order had the amount refunded by VirtualPay. Amount of %1', $amount));

        return $order;
    }

    /**
     * @param $order
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function credimemoOrder(SalesOrder $order): void
    {
        $creditMemo = $this->creditmemoFactory->createByOrder($order);
        $this->creditmemoService->refund($creditMemo);
    }

    /**
     * @param $order
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function captureOrder(SalesOrder $order, $captureCase = 'online'): void
    {
        if ($order->canInvoice()) {
            /** @var Invoice $invoice */
            $invoice = $order->prepareInvoice();
            $invoice->setRequestedCaptureCase($captureCase);
            $invoice->register();
            $invoice->pay();

            $this->invoiceRepository->save($invoice);

            // Update the order
            $order->getPayment()->setAdditionalInformation('captured', true);
            $this->orderRepository->save($order);
        }
    }

    /**
     * @throws LocalizedException
     */
    protected function setTransactionInformation(Payment $payment, array $content, string $prefix = ''): Payment
    {
        foreach ($content as $key => $value) {
            if (!is_array($value)) {
                $payment->setAdditionalInformation($prefix . $key, $value);
            }
        }
        return $payment;
    }

    public function updateDefaultAdditionalInfo(Payment $payment, array $content): Payment
    {
        try {
            //transaction_id, token_transaction, status_name, status_id, max_days_to_keep_waiting_payment
            $payment = $this->setTransactionInformation($payment, $content);
            $tid = $content['id'];
            $payment->setTransactionId($tid);
            $payment->setLastTransId($tid);
            $payment->setAdditionalInformation('tid', $tid);
            $payment->setAdditionalInformation('transaction_id', $tid);

            if (isset($content['transaction_id'])) {
            }

            $payment->setAdditionalInformation('status', $content['status'] ?? '');
            $payment->setIsTransactionClosed(false);
        } catch (\Exception $e) {
            $this->_logger->warning($e->getMessage());
        }

        return $payment;
    }

    public function updatePixAdditionalInfo(Payment $payment, array $content): Payment
    {
        try {
            $payment->setAdditionalInformation('qr_code_emv', $content['qr_code']);
            $payment->setAdditionalInformation('qr_code_original_url', $content['qr_code_image'] ?? '');
            $QRCodeUrl = $this->generateQrCode($payment, $content['qr_code']);
            $payment->setAdditionalInformation('qr_code_url', $QRCodeUrl);

            $payment->setIsTransactionClosed(false);
        } catch (\Exception $e) {
            $this->_logger->warning($e->getMessage());
        }

        return $payment;
    }

    public function generateQrCode($payment, $qrCode): string
    {
        $pixUrl = '';
        if ($qrCode) {
            try {
                $renderer = new QrCodeImageRenderer(
                    new QrCodeRendererStyle(self::DEFAULT_QRCODE_WIDTH),
                    new QrCodeImagickImageBackEnd()
                );
                $writer = new QrCodeWritter($renderer);
                $pixQrCode = $writer->writeString($qrCode);

                $filename = 'virtualpay/pix-' . $payment->getOrder()->getIncrementId() . '.png';
                $media = $this->filesystem->getDirectoryWrite(DirectoryList::MEDIA);
                $media->writeFile($filename, $pixQrCode);

                $pixUrl = $this->helperData->getMediaUrl() . $filename;
            } catch (\Exception $e) {
                $this->helperData->log($e->getMessage());
            }
        }

        return $pixUrl;
    }

    public function loadOrder(string $incrementId): SalesOrder
    {
        $order = $this->orderFactory->create();
        if ($incrementId) {
            $order->loadByIncrementId($incrementId);
        }

        return $order;
    }

    public function getStatusState($status): string
    {
        if ($status) {
            $statuses = $this->orderStatusCollectionFactory
                ->create()
                ->joinStates()
                ->addFieldToFilter('main_table.status', $status);

            if ($statuses->getSize()) {
                return $statuses->getFirstItem()->getState();
            }
        }

        return '';
    }

    /**
     * @param $payment
     * @return string
     */
    public function getPaymentStatusState($payment): string
    {
        $defaultState = $payment->getOrder()->getState();
        $paymentMethod = $payment->getMethodInstance();
        if (!$paymentMethod) {
            return $defaultState;
        }

        $status = $paymentMethod->getConfigData('order_status');
        if (!$status) {
            return $defaultState;
        }

        $state = $this->getStatusState($status);
        if (!$state) {
            return $defaultState;
        }

        return $state;
    }


    /**
     * @param $state
     * @return bool
     */
    public function canSkipOrderProcessing($state): bool
    {
        return $state != SalesOrder::STATE_PROCESSING;
    }
}
