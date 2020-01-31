<?php
class ControllerCheckoutPaymentMethod extends Controller {
	public function index() {
        return $this->response->redirect($this->url->link('checkout/mikran/payment_method'));
	}

	public function save() {
	}
}
