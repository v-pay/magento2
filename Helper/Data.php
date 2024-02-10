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

namespace VirtualPay\Payment\Helper;

use Magento\Framework\Encryption\EncryptorInterface;
use VirtualPay\Payment\Logger\Logger;
use VirtualPay\Payment\Api\RequestRepositoryInterface;
use VirtualPay\Payment\Model\RequestFactory;
use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Directory\Helper\Data as DirectoryData;
use Magento\Framework\App\Config\Initial;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Component\ComponentRegistrar;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\LayoutFactory;
use Magento\Payment\Model\Config;
use Magento\Payment\Model\Method\Factory;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Model\Order;
use Magento\Store\Model\App\Emulation;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\HTTP\PhpEnvironment\RemoteAddress;
use Magento\Customer\Model\Session as CustomerSession;
use Psr\Log\LoggerInterface;

/**
 * Class Data
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Data extends \Magento\Payment\Helper\Data
{
    /** @var \VirtualPay\Payment\Logger\Logger */
    protected $logger;

    /** @var OrderInterface  */
    protected $order;

    /** @var RequestRepositoryInterface  */
    protected $requestRepository;

    /** @var RequestFactory  */
    protected $requestFactory;

    /** @var WriterInterface */
    private $configWriter;

    /** @var Json */
    private $json;

    /** @var StoreManagerInterface */
    private $storeManager;

    /** @var RemoteAddress */
    private $remoteAddress;

    /** @var CategoryRepositoryInterface  */
    protected $categoryRepository;

    /** @var CustomerSession  */
    protected $customerSession;

    /**
     * @var DirectoryData
     */
    protected $helperDirectory;

    /**
     * @var ComponentRegistrar
     */
    protected $componentRegistrar;

    /**
     * @var DateTime
     */
    protected $dateTime;

    /**
     * @var EncryptorInterface
     */
    protected $encryptor;

    public function __construct(
        Context $context,
        LayoutFactory $layoutFactory,
        Factory $paymentMethodFactory,
        Emulation $appEmulation,
        Config $paymentConfig,
        Initial $initialConfig,
        Logger $logger,
        WriterInterface $configWriter,
        Json $json,
        StoreManagerInterface $storeManager,
        RemoteAddress $remoteAddress,
        CustomerSession $customerSession,
        CategoryRepositoryInterface $categoryRepository,
        RequestRepositoryInterface $requestRepository,
        RequestFactory $requestFactory,
        OrderInterface $order,
        ComponentRegistrar $componentRegistrar,
        DateTime $dateTime,
        DirectoryData $helperDirectory,
        EncryptorInterface $encryptor
    ) {
        parent::__construct($context, $layoutFactory, $paymentMethodFactory, $appEmulation, $paymentConfig, $initialConfig);

        $this->logger = $logger;
        $this->configWriter = $configWriter;
        $this->json = $json;
        $this->storeManager = $storeManager;
        $this->remoteAddress = $remoteAddress;
        $this->customerSession = $customerSession;
        $this->categoryRepository = $categoryRepository;
        $this->requestRepository = $requestRepository;
        $this->requestFactory = $requestFactory;
        $this->order = $order;
        $this->componentRegistrar = $componentRegistrar;
        $this->dateTime = $dateTime;
        $this->helperDirectory = $helperDirectory;
        $this->encryptor = $encryptor;
    }

    public function getAllowedMethods(): array
    {
        return [
            \VirtualPay\Payment\Model\Ui\Pix\ConfigProvider::CODE,
        ];
    }

    /**
     * Log custom message using VirtualPay logger instance
     *
     * @param $message
     * @param string $name
     * @param void
     */
    public function log($message, string $name = 'virtualpay'): void
    {
        if ($this->getGeneralConfig('debug')) {
            try {
                if (!is_string($message)) {
                    $message = $this->json->serialize($message);
                }

                $this->logger->setName($name);
                $this->logger->debug($this->mask($message));
            } catch (\Exception $e) {
                $this->logger->error($e->getMessage());
            }
        }
    }

    public function getPlatform(): string
    {
        $configPath = 'platform_id';
        if ($this->getGeneralConfig('use_sandbox')) {
            $configPath = 'sandbox_' . $configPath;
        }
        return $this->getGeneralConfig($configPath);
    }

    public function getToken($storeId = null): string
    {
        $token = $this->encryptor->decrypt($this->getGeneralConfig('token', $storeId));
        if (empty($token)) {
            $this->log('Token is empty');
        }
        return $token;
    }

    /**
     * @throws \Exception
     */
    public function getWebhookToken($storeId = null): string
    {
        $token = $this->encryptor->decrypt($this->getGeneralConfig('webhook_token', $storeId));
        if (empty($token)) {
            throw new \Exception('Webhook token is empty');
        }
        return $token;
    }

    /**
     * @param string $message
     * @return string
     */
    public function mask(string $message): string
    {
        return preg_replace('/"hash":\s?"([^"]+)"/', '"hash":"*********"', $message);
    }

    /**
     * @param $message
     * @return bool|string
     */
    public function jsonEncode($message): string
    {
        try {
            return $this->json->serialize($message);
        } catch (\Exception $e) {
            $this->log($e->getMessage());
        }
        return $message;
    }

    /**
     * @param $message
     * @return bool|string
     */
    public function jsonDecode($message): string
    {
        try {
            return $this->json->unserialize($message);
        } catch (\Exception $e) {
            $this->log($e->getMessage());
        }
        return $message;
    }

    /**
    * @param $request
    * @param $response
    * @param $statusCode
    * @param $method
    * @return void
     */
    public function saveRequest($request, $response, $statusCode, string $method = 'virtualpay'): void
    {
        if ($this->getGeneralConfig('debug')) {
            try {
                if (!is_string($request)) {
                    $request = $this->json->serialize($request);
                }
                if (!is_string($response)) {
                    $response = $this->json->serialize($response);
                }
                $request = $this->mask($request);
                $response = $this->mask($response);

                $requestModel = $this->requestFactory->create();
                $requestModel->setRequest($request);
                $requestModel->setResponse($response);
                $requestModel->setMethod($method);
                $requestModel->setStatusCode($statusCode);

                $this->requestRepository->save($requestModel);
            } catch (\Exception $e) {
                $this->log($e->getMessage());
            }
        }
    }

    public function getConfig(
        string $config,
        string $group = 'virtualpay_pix',
        string $section = 'payment',
        $scopeCode = null
    ): string {
        return (string) $this->scopeConfig->getValue(
            $section . '/' . $group . '/' . $config,
            ScopeInterface::SCOPE_STORE,
            $scopeCode
        );
    }

    public function saveConfig(
        string $value,
        string $config,
        string $group = 'general',
        string $section = 'virtualpay'
    ): void {
        $this->configWriter->save(
            $section . '/' . $group . '/' . $config,
            $value
        );
    }

    public function getGeneralConfig(string $config, $scopeCode = null): string
    {
        return $this->getConfig($config, 'general', 'virtualpay', $scopeCode);
    }

    public function getNotificationUrl(Order $order): string
    {
        $orderId = $order->getStoreId() ?: $this->storeManager->getDefaultStoreView()->getId();
        return $this->storeManager->getStore($orderId)->getUrl(
            'virtualpay/webhook',
            [
                '_secure' => true
            ]
        );
    }

    /**
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getMediaUrl(): string
    {
        return $this->storeManager->getStore()->getBaseUrl(UrlInterface::URL_TYPE_MEDIA);
    }

    public function getEndpointConfig(string $config, $scopeCode = null): string
    {
        return $this->getConfig($config, 'endpoints', 'virtualpay', $scopeCode);
    }

    public function getStoreName(): string
    {
        return $this->getConfig('name', 'store_information', 'general');
    }

    public function getUrl(string $route, array $params = []): string
    {
        return $this->_getUrl($route, $params);
    }

    public function getLogger(): LoggerInterface
    {
        return $this->_logger;
    }

    public function digits(string $string): string
    {
        return preg_replace('/\D/', '', (string) $string);
    }

    public function formatPhoneNumber(string $phoneNumber): string
    {
        $phoneNumber = $this->clearNumber($phoneNumber);
        if (strlen($phoneNumber) == 10) {
            $phoneNumber = preg_replace("/(\d{2})(\d{4})(\d{4})/", "($1) $2-$3", $phoneNumber);
        } elseif (strlen($phoneNumber) == 11) {
            $phoneNumber = preg_replace("/(\d{2})(\d{5})(\d{4})/", "($1) $2-$3", $phoneNumber);
        }
        return $phoneNumber;
    }

    public function clearNumber(string $string): string
    {
        return preg_replace('/\D/', '', (string) $string);
    }

    public function formatDate(string $date): string
    {
        return date('Y-m-d', strtotime($date));
    }
}
