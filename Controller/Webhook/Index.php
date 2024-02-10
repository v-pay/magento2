<?php

/**
 *
 * @category    VirtualPay
 * @package     VirtualPay_Payment
 */

namespace VirtualPay\Payment\Controller\Webhook;

use Magento\Framework\Controller\ResultFactory;
use VirtualPay\Payment\Controller\Webhook;

class Index extends Webhook
{
    /**
     * @var string
     */
    protected $eventName = 'pix';

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $this->helperData->log(__('Webhook %1', __CLASS__), self::LOG_NAME);

        $result = $this->resultFactory->create(ResultFactory::TYPE_RAW);
        $statusCode = 500;

        try {
            $content = $this->getContent($this->getRequest()) ?? '';
            $params = $this->getRequest()->getParams();
            $this->logParams($content, $params);

            if (isset($content['transaction'])) {
                $method = 'virtualpay-payments';
                $transaction = $params['transaction'];
                $orderIncrementId = $transaction['order_number'] ?? $transaction['free'];
                if (isset($transaction['status_id'])) {
                    $virtualpayStatus = $transaction['status_id'];
                    $order = $this->helperOrder->loadOrder($transaction['order_number']);
                    if ($order->getId()) {
                        $method = $order->getPayment()->getMethod();
                        $amount = $transaction['price_original'] ?? $order->getGrandTotal();
                        $this->helperOrder->updateOrder($order, $virtualpayStatus, $transaction, $amount, true);
                        $statusCode = 200;
                    }
                }

                /** @var \VirtualPay\Payment\Model\Callback $callBack */
                $callBack = $this->callbackFactory->create();
                $callBack->setStatus($transaction['status_name'] ?? '');
                $callBack->setMethod($method);
                $callBack->setIncrementId($orderIncrementId);
                $callBack->setPayload($this->json->serialize($params));
                $this->callbackResourceModel->save($callBack);
            }
        } catch (\Exception $e) {
            $statusCode = 500;
            $this->helperData->getLogger()->error($e->getMessage());
        }

        $result->setHttpResponseCode($statusCode);
        return $result;
    }
}
