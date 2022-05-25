<?php

namespace App\Interfaces\Payment;

interface PaymentMethodInterface {

    public function beforeBuy();

    public function afterBuy();

    public function success();

    public function cancel();

    public function home();

    public function admin_home();

}