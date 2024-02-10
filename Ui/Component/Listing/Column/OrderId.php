<?php

/**
 * @package VirtualPay\Payment
 * @copyright Copyright (c) 2021 VirtualPay
 * @license https://opensource.org/licenses/OSL-3.0.php Open Software License 3.0
 */

namespace VirtualPay\Payment\Ui\Component\Listing\Column;

use VirtualPay\Payment\Helper\Data;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Backend\Model\UrlInterface;
use VirtualPay\Payment\Helper\Order as OrderHelper;

class OrderId extends Column
{
    /**
     * @var UrlInterface
     */
    protected $urlBuilder;

    /**
     * @var Data
     */
    protected $helper;

    /**
     * @var OrderHelper
     */
    protected $helperOrder;

    /**
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param UrlInterface $urlBuilder
     * @param Data $helper
     * @param OrderHelper $helperOrder
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        UrlInterface $urlBuilder,
        Data $helper,
        OrderHelper $helperOrder,
        array $components = [],
        array $data = []
    ) {
        $this->urlBuilder = $urlBuilder;
        $this->helper = $helper;
        $this->helperOrder = $helperOrder;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            $fieldName = $this->getData('name');
            foreach ($dataSource['data']['items'] as &$item) {
                if ($item['increment_id']) {
                    $order = $this->helperOrder->loadOrder($item['increment_id']);
                    $orderId = $order->getId();
                    $item[$fieldName] = sprintf(
                        '<a href="%s">%s</a>',
                        $this->getViewLink($orderId),
                        $item['increment_id']
                    );
                } else {
                    $item[$fieldName] = __('Not Available');
                }
            }
        }

        return $dataSource;
    }

    /**
     * @param $entityId
     * @return string
     */
    protected function getViewLink($entityId)
    {
        return $this->urlBuilder->getUrl(
            'sales/order/view',
            ['order_id' => $entityId]
        );
    }
}
