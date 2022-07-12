<?php
/**
 * Order pay page for Robokassa split
 */

defined('ABSPATH') or die('silence is golden');

use Rbsp\Client;
use Rbsp\RobokassaConfiguration;

$orderKey = $_REQUEST['key'] ?? '';

if ('' === $orderKey) {
    wp_redirect(home_url());
    exit();
}

$orderId = wc_get_order_id_by_order_key($orderKey);
$order = wc_get_order($orderId);

$config = new RobokassaConfiguration(
    \get_option('robokassa_payment_MerchantLogin'),
    \get_option('robokassa_payment_shoppass1'),
    \get_option('robokassa_payment_shoppass2'),
    $orderId
);
$client = new Client($config);
$formData = $client->getFormData();

if (!empty($formData)) {
    ?>

    <div class="preloader">
        <svg class="preloader__image" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">
            <path fill="currentColor"
                  d="M304 48c0 26.51-21.49 48-48 48s-48-21.49-48-48 21.49-48 48-48 48 21.49 48 48zm-48 368c-26.51 0-48 21.49-48 48s21.49 48 48 48 48-21.49 48-48-21.49-48-48-48zm208-208c-26.51 0-48 21.49-48 48s21.49 48 48 48 48-21.49 48-48-21.49-48-48-48zM96 256c0-26.51-21.49-48-48-48S0 229.49 0 256s21.49 48 48 48 48-21.49 48-48zm12.922 99.078c-26.51 0-48 21.49-48 48s21.49 48 48 48 48-21.49 48-48c0-26.509-21.491-48-48-48zm294.156 0c-26.51 0-48 21.49-48 48s21.49 48 48 48 48-21.49 48-48c0-26.509-21.49-48-48-48zM108.922 60.922c-26.51 0-48 21.49-48 48s21.49 48 48 48 48-21.49 48-48-21.491-48-48-48z">
            </path>
        </svg>
    </div>
    <style>
        .preloader {
            position: fixed;
            left: 0;
            top: 0;
            right: 0;
            bottom: 0;
            overflow: hidden;
            background: #e0e0e0;
            z-index: 1001;
        }

        .preloader__image {
            position: relative;
            top: 50%;
            left: 50%;
            width: 70px;
            height: 70px;
            margin-top: -35px;
            margin-left: -35px;
            text-align: center;
            animation: preloader-rotate 2s infinite linear;
        }

        @keyframes preloader-rotate {
            100% {
                transform: rotate(360deg);
            }
        }

        .loaded_hiding .preloader {
            transition: 0.3s opacity;
            opacity: 0;
        }

        .loaded .preloader {
            display: none;
        }
    </style>
    <script>
        window.onload = function () {
            document.body.classList.add("loaded_hiding");
            window.setTimeout(function () {
                document.body.classList.add("loaded");
                document.body.classList.remove("loaded_hiding");
            }, 1000);
        }
    </script>
    <form method="POST" action="<?php echo RBSP_ROBOKASSA_URL; ?>">
        <input type="hidden" name="invoice"
               value="<?php echo $formData['invoice']; ?>">
        <input type="hidden" name="signature"
               value="<?php echo $formData['signature']; ?>">
        <input type="submit" id="rbsp-form" value="Pay" class="submit">
        <script type="text/javascript"> document.getElementById('rbsp-form').click(); </script>
    </form>
    <?php
}