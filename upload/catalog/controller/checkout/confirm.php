<?php
class ControllerCheckoutConfirm extends Controller {
	public function index() {
        return $this->response->redirect($this->url->link('checkout/mikran/confirm'));
	}
}
