<?php
class ControllerCheckoutShippingMethod extends Controller {
    public function index() {
        return $this->response->redirect($this->url->link('checkout/mikran/shipping_method'));
    }

	public function save() {
	}
}