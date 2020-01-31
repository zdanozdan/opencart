<?php
class ControllerCheckoutCheckout extends Controller {
	public function index() {
        return $this->response->redirect($this->url->link('checkout/mikran/checkout'));
	}

	public function country() {
        return $this->response->redirect($this->url->link('checkout/mikran/checkout/country'));
	}

	public function customfield() {
        return $this->response->redirect($this->url->link('checkout/mikran/checkout/customfield'));
	}
}