<?php

declare(strict_types=1);

namespace Rbsp;

class OrderData
{
    private $orderId;
    private $WCorder;
    private $WCorderData;

    public function __construct( $orderId )
    {
        $this->orderId = (int)$orderId;
        $this->WCorder = wc_get_order($this->orderId);
        $this->WCorderData = $this->WCorder->get_data();

    }

    public function getOrderId()
    {
        return $this->orderId;
    }

    public function getUserMail()
    {
        return $this->WCorderData['billing']['email'];
    }

    public function getSum()
    {
        return number_format((float)$this->WCorderData['total'], 2, '.', '');
    }

    public function getSumFloat()
    {
        return (float)$this->WCorderData['total'];
    }

    public function getVendorId()
    {
        return get_post_meta($this->orderId, 'hp_vendor', true);
    }

    public function getVendorShopId()
    {
        return get_post_meta($this->getVendorId(), 'rbsp_vendor_robokassa_id', true);
    }

    public function getVendorPaymentPart()
    {
        return get_post_meta($this->getVendorId(), 'rbsp_vendor_payment_part', true);
    }

}