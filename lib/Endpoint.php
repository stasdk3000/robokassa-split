<?php

declare(strict_types=1);

namespace Rbsp;

class Endpoint
{
    public function __construct()
    {
        add_action( 'init', array( $this, 'rewriteEndpoint' ) );
        add_action( 'template_redirect', array( $this, 'redirectLink' ) );
        add_filter( 'woocommerce_get_checkout_payment_url', [$this, 'filterUrl'], 99999, 1 );
        add_action( 'parse_request', [$this, 'checkPayment'], 1);
    }

    public function checkPayment()
    {

        if (isset($_REQUEST['robokassa']) &&
            isset($_REQUEST['IncCurrLabel']) &&
            'SplitR' === $_REQUEST['IncCurrLabel']
        ) {

            $returner = '';

            if ($_REQUEST['robokassa'] === 'result') {

                if (isset($_REQUEST['SignatureValue']) &&
                    isset($_REQUEST['orderid'])
                ) {

                    $order = new \WC_Order($_REQUEST['orderid']);
                    $order->add_order_note('Заказ успешно оплачен!');
                    $order->payment_complete();

                    global $woocommerce;
                    $woocommerce->cart->empty_cart();

                    $returner = 'SPLIT-OK' . $_REQUEST['InvId'];

                } else {

                    $order = new \WC_Order($_REQUEST['orderid']);
                    $order->add_order_note('Bad CRC');
                    $order->update_status('failed');

                    $returner = 'SPLIT BAD SIGN';
                }
            }

            if ($_REQUEST['robokassa'] == 'success') {
                header('Location:' . robokassa_payment_get_success_fail_url(get_option('robokassa_payment_SuccessURL'), $_REQUEST['orderid']));
                die;
            }

            if ($_REQUEST['robokassa'] == 'fail') {
                header('Location:' . robokassa_payment_get_success_fail_url(get_option('robokassa_payment_FailURL'), $_REQUEST['orderid']));
                die;
            }

            if ($_REQUEST['robokassa'] == 'registration') {

                $postData = file_get_contents('php://input');
                $data = json_decode($postData, true);

                $filename = 'registration_data.json';
                $save = json_encode($data);
                file_put_contents($_SERVER['DOCUMENT_ROOT']."/wp-content/plugins/robokassa/data/{$filename}", $save);

                echo json_encode($data);
            }

            echo $returner;
            die;
        } else if (isset($_REQUEST['robokassa'])) {
            $order = wc_get_order( $_REQUEST['InvId'] );
            if (!$order) {
                wp_redirect(home_url());
                die();
            }

        }
    }

    public function rewriteEndpoint()
    {

        add_rewrite_endpoint( 'wc-split', EP_ALL );
    }

    public function redirectLink()
    {

        global $wp_query;
        if(stristr($_SERVER['REQUEST_URI'], 'wc-split') !== false) {
            include __DIR__ . '/template/split-page.php';
            die();
        }

        if ( $wp_query->query_vars['name'] === 'wc-split' ) {
            include __DIR__ . '/template/split-page.php';
            die();
        }

    }

    public function filterUrl( $urlString ): string
    {
        $urlArray = explode('/', $urlString);
        $key = end($urlArray);
        if ( empty($key) ) {
            array_pop($urlArray);
            $key = end($urlArray);
        }

        if (self::isDeposit()) {
            return $urlString;
        } else {
            return '/wc-split/' . $key;
        }
    }

    function isDeposit(): bool
    {
        if (is_admin()) {
            return false;
        }

        $cart = WC()->cart->get_cart();
        $flag = true;

        if (!isset($cart)) {
            return true;
        }

        foreach ($cart as $item) {
            $deposit = get_post_meta($item['product_id'], '_awcdp_deposit_enabled', true);
            $flag = 'no' !== $deposit;
        }

        return $flag;
    }
}