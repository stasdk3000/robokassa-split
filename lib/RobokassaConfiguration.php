<?php

declare(strict_types=1);

namespace Rbsp;

class RobokassaConfiguration
{
    private string $shopID1;
    private string $shopPass1;
    private string $shopPass2;
    private string $orderId;

    public function __construct(
        string $shopID1,
        string $shopPass1,
        string $shopPass2,
        string $orderId
    )
    {
        $this->shopID1 = $shopID1;
        $this->shopPass1 = $shopPass1;
        $this->shopPass2 = $shopPass2;
        $this->orderId = $orderId;

    }

    public function getShopID1(): string
    {
        return $this->shopID1;
    }

    public function getPass1(): string
    {
        return $this->shopPass1;
    }

    public function getPass2(): string
    {
        return $this->shopPass2;
    }

    public function getOrderId(): string
    {
        return $this->orderId;
    }
}