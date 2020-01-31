<?php
class ControllerCheckoutSuccess extends Controller {
	public function index() {
        return $this->response->redirect($this->url->link('checkout/mikran/success'));
	}
}