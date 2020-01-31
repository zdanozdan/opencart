<?php
class ControllerCheckoutRegister extends Controller {
	public function index() {
        return $this->response->redirect($this->url->link('checkout/mikran/register'));
	}

	public function save() {
        return $this->response->redirect($this->url->link('checkout/mikran/register/save'));
	}
}
