<?php

declare(strict_types=1);

namespace Rbsp;

class Client
{
    private RobokassaConfiguration $configuration;
    private $order;
    private $receipt = [];
    private bool $isDeposit = false;

    public function __construct(RobokassaConfiguration $configuration)
    {
        $this->configuration = $configuration;
        $this->order = new OrderData($this->configuration->getOrderId());

        // Take items by WC cart
        $this->cartItems();

        return true;
    }

    /**
     * Prepare data structure
     * {@link https://docs.robokassa.ru/split/}
     * @return object
     */
    public function prepareData(): object
    {
        return (object)[
            'outAmount' => $this->order->getSum(),
            'email' => $this->order->getUserMail(),
            "incCurr" => "BankCard",
            "language" => "ru",
            "isTest" => false,
            'merchant' => (object)[
                'id' => $this->configuration->getShopID1()
            ],
            'splitMerchants' => self::getMerchants(),
        ];
    }

    public function getFormatSum($sum)
    {
        return number_format((float)$sum, 2, '.', '');
    }

    public function getMerchant($shopId, $sum): object
    {

        $object = (object)[
            'id' => $shopId,
            'amount' => $this->getFormatSum($sum),
            'receipt' => (object)[
                'InvoiceId' => $this->order->getOrderId(),
                'items' => []
            ]
        ];

        if (!empty($this->receipt['items'])) {
            $items = [];
            foreach ($this->receipt['items'] as $item) {
                $item['sum'] = $this->getFormatSum($sum);
                $items[] = (object)$item;
            }
            $object->receipt->items = $items;
        }

        return $object;

    }

    public function checkControlSum($x, $y, $summa): bool
    {
        return $x + $y === $summa;
    }

    public function getTotalSum(): array
    {
        $vendorPercent = $this->order->getVendorPaymentPart();

        if (1 <= $vendorPercent && $vendorPercent <= 100) {

            $sum = $this->order->getSumFloat();

            $vendor = $vendorPercent / 100 * $sum;
            $master = (100 - $vendorPercent) / 100 * $sum;

            // checksum
            if (self::checkControlSum(
                $master,
                $vendor,
                $sum
            )) {
                return [
                    'master' => $master,
                    'vendor' => $vendor,
                ];
            }

        }

        return [];
    }

    public function getMerchants(): array
    {
        $splitMerchants = [];
        $totalSum = self::getTotalSum();

        if (empty($totalSum)) {
            return $splitMerchants;
        }

        // Master shop
        $splitMerchants[] = $this->getMerchant(
            $this->configuration->getShopID1(),
            $totalSum['master']
        );

        // Vendor shop
        $splitMerchants[] = $this->getMerchant(
            $this->order->getVendorShopId(),
            $totalSum['vendor']
        );

        return $splitMerchants;

    }

    private function cartItems(): void
    {
        $cart = WC()->cart->get_cart();

        if (!isset($cart)) {
            return;
        }

        foreach ($cart as $item) {
            // Check product deposit option
            $this->isProductDeposit($item['product_id']);

            $product = wc_get_product($item['product_id']);

            $current['name'] = $product->get_title();
            $current['quantity'] = (float)$item['quantity'];
            $current['sum'] = $item['line_total'];
            $current['payment_object'] = \get_option('robokassa_payment_paymentObject');
            $current['payment_method'] = \get_option('robokassa_payment_paymentMethod');
            $current['tax'] = 'none';

            $this->receipt['items'][] = $current;
        }

    }

    public function isProductDeposit($productId)
    {
        $deposit = get_post_meta($productId, '_awcdp_deposit_enabled', true);
        if ('no' !== $deposit) {
            $this->isDeposit = true;
        }
    }

    public function getSignature(): string
    {
        $invoice = json_encode($this->prepareData());
        return md5(
            $invoice . $this->configuration->getPass1()
        );
    }

    public function getFormData(): array
    {

        if ($this->isDeposit) {
            return [];
        }

        return [
            'invoice' => urlencode(json_encode($this->prepareData())),
            'signature' => $this->getSignature(),
        ];
    }

}