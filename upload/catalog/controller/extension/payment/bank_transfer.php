<?php
class ControllerExtensionPaymentBankTransfer extends Controller {
	public function index($totals=array()) {
        $this->load->language('checkout/success');
		$this->load->language('extension/payment/bank_transfer');
        $this->load->model('checkout/order');

		$data['bank'] = nl2br($this->config->get('payment_bank_transfer_bank' . $this->config->get('config_language_id')));

        $comment = $this->language->get('text_thank_you') . "\n\n";
        $comment .= $this->language->get('text_instruction') . "\n\n";
        $comment .= $this->config->get('payment_bank_transfer_bank' . $this->config->get('config_language_id')) . "\n\n";
        $comment .= $this->language->get('text_payment');
            
        //$this->model_checkout_order->addOrderHistory($this->session->data['order_id'], $this->config->get('payment_bank_transfer_order_status_id'), $comment, true);

        $order_id = isset($this->request->get['order_id']) ? $this->request->get['order_id'] : $this->session->data['order_id'];
        $this->session->data['order_id'] = $order_id;
        $order_data = $this->model_checkout_order->getOrder($order_id);

        $amount = $this->currency->format($order_data['total'], $order_data['currency_code'], $order_data['currency_value']);
        $data['text_order_info']  = sprintf($this->language->get('text_order_info'),$order_id,$amount);
        $data['account_number'] = $this->language->get('text_account_number_'.$order_data['currency_code']);

		return $this->load->view('extension/payment/bank_transfer', $data);
	}

	public function confirm() {
	}
}