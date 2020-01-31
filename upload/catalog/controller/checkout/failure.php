<?php
class ControllerCheckoutFailure extends Controller {
	public function index() {
        return $this->response->redirect($this->url->link('checkout/mikran/failure'));
	}
}