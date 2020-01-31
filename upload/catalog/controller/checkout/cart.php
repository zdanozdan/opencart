<?php
class ControllerCheckoutCart extends Controller {
	public function index() {
        return $this->response->redirect($this->url->link('checkout/mikran/cart'));
	}

	public function add() {
        return $this->response->redirect($this->url->link('checkout/mikran/cart/add'));
	}

	public function edit() {
        return $this->response->redirect($this->url->link('checkout/mikran/cart/edit'));
	}

	public function remove() {
        return $this->response->redirect($this->url->link('checkout/mikran/cart/remove'));
	}
}
