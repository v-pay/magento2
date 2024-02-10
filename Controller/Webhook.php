<?php

/**
 *
 *
 *
 * @category    VirtualPay
 * @package     VirtualPay_Payment
 */

namespace VirtualPay\Payment\Controller;

use VirtualPay\Payment\Helper\Data as HelperData;
use VirtualPay\Payment\Helper\Order as HelperOrder;
use VirtualPay\Payment\Model\CallbackFactory;
use VirtualPay\Payment\Model\ResourceModel\Callback as CallbackResourceModel;
use Magento\Framework\App\Action\Action;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Request\InvalidRequestException;

abstract class Webhook extends Action implements \Magento\Framework\App\CsrfAwareActionInterface
{
    const LOG_NAME = 'virtualpay-callback';

    /**
     * @var string
     */
    protected $eventName = 'callback';

    /**
     * @var HelperData
     */
    protected $helperData;

    /**
     * @var HelperOrder
     */
    protected $helperOrder;

    /**
     * @var CallbackResourceModel
     */
    protected $callbackResourceModel;

    /**
     * @var CallbackFactory
     */
    protected $callbackFactory;

    /**
     * @var Json
     */
    protected $json;

    /**
     * @var string
     */
    protected $requestContent;

    /**
     * @var ResultFactory
     */
    protected $resultFactory;

    /**
     * Event manager
     *
     * @var ManagerInterface
     */
    protected $eventManager;

    /**
     * PostBack constructor.
     * @param Context $context
     * @param Json $json
     * @param ResultFactory $resultFactory
     * @param HelperData $helperData
     * @param HelperOrder $helperOrder
     * @param CallbackResourceModel $callbackResourceModel
     * @param ManagerInterface $eventManager
     */
    public function __construct(
        Context $context,
        Json $json,
        ResultFactory $resultFactory,
        HelperData $helperData,
        HelperOrder $helperOrder,
        CallbackResourceModel $callbackResourceModel,
        CallbackFactory $callbackFactory,
        ManagerInterface $eventManager
    ) {
        $this->json = $json;
        $this->resultFactory = $resultFactory;
        $this->helperData = $helperData;
        $this->helperOrder = $helperOrder;
        $this->callbackResourceModel = $callbackResourceModel;
        $this->callbackFactory = $callbackFactory;
        $this->eventManager = $eventManager;

        return parent::__construct($context);
    }

    /**
     * https://api-docs.virtualpay.com.br/reference/webhook
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface
     */
    abstract public function execute();

    /**
     * @param $result
     * @param $content
     * @param $params
     * @return mixed
     */
    public function dispatchEvent($result, $content, $params)
    {
        $this->eventManager->dispatch(
            'virtualpay_webhook_' . $this->eventName,
            [
                'result' => $result,
                'content' => $content,
                'params' => $params
            ]
        );

        return $result;
    }

    /**
     * @inheritDoc
     */
    public function createCsrfValidationException(RequestInterface $request): ?InvalidRequestException
    {
        $result = $this->resultFactory->create(ResultFactory::TYPE_RAW);
        $result->setHttpResponseCode(403);
        return new InvalidRequestException(
            $result
        );
    }

    /**
     * @inheritDoc
     * @throws \Exception
     */
    public function validateForCsrf(RequestInterface $request): ?bool
    {
        try {
            $authorization = $request->getHeader('Authorization');
            $bearerToken = str_replace('Bearer ', '', $authorization);
            $storedHash = $this->helperData->getWebhookToken();
            return ($bearerToken == $storedHash);
        } catch (\Exception $e) {
            $this->helperData->getLogger()->critical($e->getMessage());
        }
        return false;
    }

    /**
     * @param RequestInterface $request
     * @return mixed|string
     */
    protected function getContent(RequestInterface $request)
    {
        if (!$this->requestContent) {
            $this->requestContent = '';
            try {
                $content = $request->getContent();
                $this->requestContent = ($content) ? $this->json->unserialize($content) : [];
            } catch (\Exception $e) {
                $this->helperData->getLogger()->critical($e->getMessage());
            }
        }
        return $this->requestContent;
    }

    /**
     * @param $content
     * @param $params
     */
    protected function logParams($content, $params)
    {
        $this->helperData->log(__('Content'), self::LOG_NAME);
        $this->helperData->log($content, self::LOG_NAME);

        $this->helperData->log(__('Params'), self::LOG_NAME);
        $this->helperData->log($params, self::LOG_NAME);
    }
}
