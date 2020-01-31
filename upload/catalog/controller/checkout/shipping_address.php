<?php
class ControllerCheckoutShippingAddress extends Controller {
	public function index() {
        return $this->response->redirect($this->url->link('checkout/mikran/shipping_address'));
	}

	public function save() {
	}
}