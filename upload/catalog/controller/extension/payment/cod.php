<?php
class ControllerExtensionPaymentCod extends Controller {
	public function index($totals=array()) {
        $this->load->language('checkout/success');
        $this->load->language('extension/payment/cod');

        $order_id = isset($this->request->get['order_id']) ? $this->request->get['order_id'] : $this->session->data['order_id'];
        $this->session->data['order_id'] = $order_id;
        $order_data = $this->model_checkout_order->getOrder($order_id);
        $amount = $this->currency->format($order_data['total'], $order_data['currency_code'], $order_data['currency_value']);
        $data['text_cod'] = sprintf($this->language->get('text_cod'),$amount);

        //$this->load->model('checkout/order');
            
        //$this->model_checkout_order->addOrderHistory($this->session->data['order_id'], $this->config->get('payment_cod_order_status_id'),sprintf($this->language->get('text_cod'),$totals['total']['text']),true);
        
		return $this->load->view('extension/payment/cod',$data);
	}

	public function confirm() {
	}
}
