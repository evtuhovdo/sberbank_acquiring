<?php

// Документация
// https://securepayments.sberbank.ru/wiki/doku.php/integration:api:start
// https://github.com/voronkovich/sberbank-acquiring-client

use Voronkovich\SberbankAcquiring\Client;
use Voronkovich\SberbankAcquiring\Currency;
use Voronkovich\SberbankAcquiring\OrderStatus;

require_once __DIR__ . '/vendor/autoload.php';

if (!file_exists(__DIR__ . '/config.php')) {
    echo 'Скопируйте файл config.php.dist в config.php и укажиие в нем логин и пароль';
}

$config = require __DIR__ . '/config.php';

$client = new Client(['userName' => $config['username'], 'password' => $config['password'], 'apiUri' => $config['apiUri']]);

$orderId = 111111121;
$orderAmount = 1000; // сумма в копейках !!!!
$returnUrl = 'http://mycoolshop.local/payment-success';

$params['currency'] = Currency::RUB;
$params['failUrl'] = 'http://mycoolshop.local/payment-failure';

$result = $client->registerOrder($orderId, $orderAmount, $returnUrl, $params);

$paymentOrderId = $result['orderId'];
$paymentFormUrl = $result['formUrl'];

echo 'Номер заказа что сгенерил сбер ' . $paymentOrderId . "\n";
echo 'Ссылка на оплату куда надо кинуть пользователя ' . $paymentFormUrl . "\n";

// Узнаем статус заказа

$result = $client->getOrderStatus($paymentOrderId);

echo "Order status - " . $result['orderStatus'] . "\n";

//// Возможные статусы
//// An order was successfully registered, but is'nt paid yet
//const CREATED = 0;
//// An order's amount was successfully holded (for two-stage payments only)
//const APPROVED = 1;
//// An order was deposited
//// If you want to check whether payment was successfully paid - use this constant
//const DEPOSITED = 2;
//// An order was reversed
//const REVERSED = 3;
//// An order was refunded
//const REFUNDED = 4;
//// An order authorization was initialized by card emitter's ACS
//const AUTHORIZATION_INITIALIZED = 5;
//// An order was declined
//const DECLINED = 6;

// оплачен
if (OrderStatus::isDeposited($result['orderStatus'])) {
    echo "Order #$orderId is deposited!\n";
}

// отклонен
if (OrderStatus::isDeclined($result['orderStatus'])) {
    echo "Order #$orderId was declined!\n";
}

// Или можно получать статус заказа по своим идентификаторам, а не смотреть идентификаторы сбера.
$result = $client->getOrderStatusByOwnId($orderId);