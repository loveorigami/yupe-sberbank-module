<?php

/**
 * Class SberbankPaymentSystem
 * @link
 */

use yupe\widgets\YFlashMessages;

Yii::import('application.modules.sberbank.SberbankModule');
Yii::import('application.modules.sberbank.components.Sberbank');
/**
 * Class SberbankPaymentSystem
 */
class SberbankPaymentSystem extends PaymentSystem
{
    /**
     * @param Payment $payment
     * @param Order $order
     * @param bool|false $return
     * @return mixed|string
     */
    public function renderCheckoutForm(Payment $payment, Order $order, $return = false)
    {
        $sbank = new Sberbank($payment);
        $action = $sbank->getFormUrl($order);

        if (!$action) {
            Yii::app()->getUser()->setFlash(
                YFlashMessages::ERROR_MESSAGE,
                Yii::t('SberbankModule.sberbank', 'Payment by "{name}" is impossible', ['{name}' => $payment->name])
            );

            return false;
        }

        return Yii::app()->getController()->renderPartial('application.modules.sberbank.views.form', [
            'action' => $action
        ], $return);
    }

    /**
     * @param Payment $payment
     * @param CHttpRequest $request
     * @return bool
     */
    public function processCheckout(Payment $payment, CHttpRequest $request)
    {
        $orderId = $request->getParam('orderId');
        echo $orderId;
        $sbank = new Sberbank($payment);
        $order = Order::model()->findByAttributes(['orderId'=>$orderId]);

        if ($order === null) {
            Yii::log(Yii::t('SberbankModule.sberbank', 'The order doesn\'t exist.'), CLogger::LEVEL_ERROR);
            return false;
        }

        if ($order->isPaid()) {
            Yii::log(
                Yii::t('SberbankModule.sberbank', 'The order #{n} is already payed.', $order->getPrimaryKey()),
                CLogger::LEVEL_ERROR
            );

            return $order;
        }

        if ($sbank->getPaymentStatus($request) && $order->pay($payment)) {
            Yii::log(
                Yii::t('SberbankModule.sberbank', 'The order #{n} has been payed successfully.', $order->getPrimaryKey()),
                CLogger::LEVEL_INFO
            );
            Yii::app()->getUser()->setFlash(
                YFlashMessages::SUCCESS_MESSAGE,
                Yii::t('SberbankModule.sberbank', 'The order #{n} has been payed successfully.', $order->getPrimaryKey())
            );
        } else {
            Yii::app()->getUser()->setFlash(
                YFlashMessages::ERROR_MESSAGE,
                Yii::t('SberbankModule.sberbank', 'Attempt to pay failed')
            );
            Yii::log(Yii::t('SberbankModule.sberbank', 'An error occurred when you pay the order #{n}.',
                $order->getPrimaryKey()), CLogger::LEVEL_ERROR);
        }

        return $order;

    }
}
