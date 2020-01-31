<?php
class ControllerCheckoutLogin extends Controller {
	public function index() {
        return $this->response->redirect($this->url->link('checkout/mikran/login'));
	}

	public function save() {
        return $this->response->redirect($this->url->link('checkout/mikran/login/save'));
    }
}
