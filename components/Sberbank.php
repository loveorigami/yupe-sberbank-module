<?php

/**
 * Class for working with Sberbank REST
 *
 * @package  yupe.modules.sberbank.components
 * @author   Lukyanov Andrey <loveorigami@mail.ru>
 * @license  BSD http://ru.wikipedia.org/wiki/%D0%9B%D0%B8%D1%86%D0%B5%D0%BD%D0%B7%D0%B8%D1%8F_BSD
 * @link     https://github.com/loveorigami/yupe-sberbank-module
 **/
class Sberbank
{
    // Payment key
    private $key;

    // Payment mode (secure or sandbox)
    private $userName;
    private $password;
    private $returnUrl;
    private $merchant;
    private $failUrl;
    private $sessionTimeoutSecs;
    private $language;


    public function __construct(Payment $payment)
    {
        $settings = $payment->getPaymentSystemSettings();

        $this->userName = $settings['userName'];
        $this->password = $settings['password'];
        $this->returnUrl = $settings['returnUrl'];
        $this->merchant = $settings['merchant'];

        $this->sessionTimeoutSecs = $settings['sessionTimeoutSecs'];
        $this->language = $settings['language'];
        $this->failUrl = $settings['failUrl'];
    }

    /**
     * Generate url
     *
     * @param string $method Payler API method
     * @return string
     * @link 12. Координаты подключения (см. guide)
     */
    public function getUrl($method)
    {
        return 'https://3dsec.sberbank.ru/payment/rest/' . $method;
    }

    /**
     * Starts a payment session and returns its ID
     *
     * @param Order $order
     * @return string|bool
     */
    public function getFormUrl(Order $order)
    {
        if ($order->orderId) {
            return $this->merchant.'?mdOrder='.$order->orderId;
        }

        $data = [
            'userName' => $this->userName,
            'password' => $this->password,
            'returnUrl' => $this->returnUrl,
            'failUrl' => $this->failUrl,
            'sessionTimeoutSecs' => $this->sessionTimeoutSecs,
            'language' => $this->language,

            'orderNumber' => $order->id,
            'amount' => $order->getTotalPriceWithDelivery() * 100,
            'description' =>  Yii::t('SberbankModule.sberbank', 'Payment order #{n} on "{site}" website', [
                '{n}' => $order->id,
                '{site}' => Yii::app()->getModule('yupe')->siteName
            ])
        ];

        $sessionData = $this->sendRequest($data, 'register.do');

        // {"orderId":"5a5e78b7-dd22-4062-b677-b87fc12cdcfa","formUrl":"https://3dsec.sberbank.ru/payment/merchants/nene/payment_ru.html?mdOrder=5a5e78b7-dd22-4062-b677-b87fc12cdcfa"}
        // returnUrl

        if (!isset($sessionData['formUrl'])) {
            Yii::log(Yii::t('SberbankModule.sberbank', 'Session ID is not defined.'), CLogger::LEVEL_ERROR);
            return false;
        }

        if (isset($sessionData['orderId'])) {
            $order->orderId = $sessionData['orderId'];
            $order->save();
        }

        return $sessionData['formUrl'];

    }

    /**
     * Gets the status of the current payment
     *
     * @param CHttpRequest $request
     * @return string|bool
     * Номер состояния Описание
            0 Заказ зарегистрирован, но не оплачен
            1 Предавторизованная сумма захолдирована (для двухстадийных платежей)
            2 Проведена полная авторизация суммы заказа
            3 Авторизация отменена
            4 По транзакции была проведена операция возврата
            5 Инициирована авторизация через ACS банка-эмитента
            6 Авторизация отклонена
     */
    public function getPaymentStatus(CHttpRequest $request)
    {
        $data = [
            'userName' => $this->userName,
            'password' => $this->password,
            'orderId' => $request->getParam('orderId'),
        ];

        $response = $this->sendRequest($data, 'getOrderStatus.do');

        if (!isset($response['OrderStatus'])) {
            return false;
        }

        if ($response['OrderStatus']==2) {
            return true;
        }

        return false;
    }

    /**
     * Sends a request to the server
     *
     * @param array $data API method parameters
     * @param string $method Payler API method
     * @return bool|mixed
     */
    private function sendRequest($data, $method)
    {
        $data = http_build_query($data, '', '&');

        $options = [
            CURLOPT_URL => $this->getUrl($method),
            CURLOPT_POST => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 45,
            CURLOPT_VERBOSE => false,
            CURLOPT_HTTPHEADER => [
                'Content-type: application/x-www-form-urlencoded',
                'Cache-Control: no-cache',
                'charset="utf-8"',
            ],
            CURLOPT_POSTFIELDS => $data,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_SSL_VERIFYPEER => false,
        ];

        $ch = curl_init();
        curl_setopt_array($ch, $options);
        $json = curl_exec($ch);
        if ($json === false) {
            Yii::log(Yii::t('SberbankModule.sberbank', 'Request error: {message}',
                ['{message}' => curl_error($ch)]), CLogger::LEVEL_ERROR);

            return false;
        }
        $result = json_decode($json, true);
        curl_close($ch);

        return $result;
    }
}