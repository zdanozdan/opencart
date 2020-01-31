<?php


use tpayLibs\examples\TpayBasicExample;
use tpayLibs\src\_class_tpay\Utilities\Lang;

require_once DIR_SYSTEM . 'library/tpayLibs/examples/BasicPaymentForm.php';
require_once DIR_SYSTEM . 'library/tpayLibs/examples/TransactionNotification.php';

class ControllerExtensionPaymentTpay extends Controller {

    public function index() {

        $this->language->load('extension/payment/tpay');

        $data['button_confirm'] = $this->language->get('button_confirm');
        $data['button_back'] = $this->language->get('button_back');
        $data['text_lang'] = $this->language->get('text_lang');
        $data['text_bank_choice'] = $this->language->get('text_bank_choice');
        $data['text_accept_terms'] = $this->language->get('text_accept_terms');
        $data['text_title']  = $this->language->get('text_title');

        $this->load->model('checkout/order');

        $this->load->language('extension/payment/tpay');

        $order_id = isset($this->request->get['order_id']) ? $this->request->get['order_id'] : $this->session->data['order_id'];
        $this->session->data['order_id'] = $order_id;

        $order_data = $this->model_checkout_order->getOrder($order_id);

        $data['action'] = HTTPS_SERVER . 'index.php?route=extension/payment/tpay/pay';
        $data['back'] = HTTPS_SERVER . 'index.php?route=checkout/payment';

        $amount = $this->currency->format($order_data['total'], $order_data['currency_code'], $order_data['currency_value']);

        $data['text_order_info']  = sprintf($this->language->get('text_order_info'),$order_id,$amount);
        
        $this->id = 'payment';

        if ($order_data['language_code'] == 'pl' || $order_data['language_code'] == 'pl-pl') {
            Lang::setLang('pl');
        }
        $data['tpay_payment_place'] = $this->config->get('payment_tpay_payment_place');
        $data['tpay_payment_view'] = $this->config->get('payment_tpay_payment_view');
        $data['seller_id']= $this->config->get('payment_tpay_seller_id');
        $data['form'] = '';
        $data['show_regulations_checkbox'] = false;
        $data['merchant_id'] = (int)$data['seller_id'];
        $data['regulation_url'] = '';

        $data['cards_and_transfers'] = Lang::get('cards_and_transfers');
        $data['other_methods'] = Lang::get('other_methods');
        $data['accept'] = Lang::get('accept');
        $data['regulations_url'] = Lang::get('regulations_url');
        $data['regulations'] = Lang::get('regulations');
        $data['acceptance_is_required'] = Lang::get('acceptance_is_required');
        //ob_start();
        //require_once DIR_SYSTEM . 'library/tpayLibs/src/common/_tpl/bankSelection.phtml';
        //$form = ob_get_clean();
        //$data['tpay_form'] = $form;
        return $this->load->view('extension/payment/tpay', $data);
    }

    public function pay() {

        $this->load->model('checkout/order');
        $this->load->language('extension/payment/tpay');
        
        $order_data = $this->model_checkout_order->getOrder($this->session->data['order_id']);
        $this->id = 'payment';

        $note = $this->language->get('text_new_order');
        $this->model_checkout_order->addOrderHistory($order_data['order_id'], $this->config->get('payment_tpay_order_status_new'), $note);

        $tpay_currency = $this->config->get('payment_tpay_currency');
        $tpay_seller_id = $this->config->get('payment_tpay_seller_id');
        $tpay_conf_code = $this->config->get('payment_tpay_conf_code');
        $formHandler = new TpayBasicExample ($tpay_conf_code, (int)$tpay_seller_id);
        $crc = base64_encode($order_data['order_id']);

        $amount = number_format($this->currency->format($order_data['total'], $tpay_currency, $order_data['currency_value'], FALSE), 2, '.', '');
        $from = $this->session->data['currency'];
        $amount = $this->currency->convert($amount, $from, $tpay_currency);

        $data['kwota'] = $amount;
        $data['opis'] = $this->language->get('text_order') . $order_data['order_id'];
        $data['email'] = $order_data['email'];
        $data['nazwisko'] = $order_data['payment_lastname'];
        $data['imie'] = $order_data['payment_firstname'];
        $data['adres'] = $order_data['payment_address_1'] . $order_data['payment_address_2'];
        $data['miasto'] = $order_data['payment_city'];
        $data['kraj'] = $order_data['payment_country'];
        $data['kod'] = $order_data['payment_postcode'];
        $data['crc'] = $crc;
        $data['telefon'] = $order_data['telephone'];
        $data['pow_url'] = HTTPS_SERVER . 'index.php?route=checkout/mikran/success';
        $data['pow_url_blad'] = HTTPS_SERVER . 'index.php?route=checkout/mikran/checkout';
        $data['wyn_url'] = HTTPS_SERVER . 'index.php?route=extension/payment/tpay/validate';
        if(isset($this->request->post['kanal'])){
            $data['kanal'] = (int)$this->request->post['kanal'];}
        $data['akceptuje_regulamin'] = isset($this->request->post['akceptuje_regulamin']) ? 1 : 0;
        $formHandler->getDataForTpay($data);
        if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . 'default/template/payment/tpay_redirect')) {
            print_r($this->load->view( $this->config->get('config_template') . 'extension/payment/tpay_redirect', $data));
        }

        print_r($this->load->view('extension/payment/tpay_redirect', $data));
    }

    public function validate() {

        $tpay_seller_id = $this->config->get('payment_tpay_seller_id');
        $tpay_conf_code = $this->config->get('payment_tpay_conf_code');
        $handler = new \tpayLibs\examples\TransactionNotification($tpay_conf_code, (int)$tpay_seller_id);
        $data = $handler->checkPayment();

        $note='';
        $order_id = (int)base64_decode($data['tr_crc']);
        $tr_status = $data['tr_status'];
        $tr_id = $data['tr_id'];

        $this->load->model('checkout/order');
        $this->load->language('payment/tpay');
        $order_data = $this->model_checkout_order->getOrder($order_id);
        $completed_status = $this->config->get('payment_tpay_order_status_completed');

        $current_status = $order_data['order_status_id'];

        $note.=((int)$data['test_mode']== 1) ? '<b>Test mode: </b> ' : '';

        if ($current_status === $completed_status) {
            return false;
        }

        $note .= date('H:i:s ') . " Tpay ". $this->language->get('transaction id: ') . $tr_id;

        if ($tr_status == 'TRUE') {
            $this->model_checkout_order->addOrderHistory($order_data['order_id'], $this->config->get('payment_tpay_order_status_completed'), $note, TRUE);
            //Add transcation history
            $this->load->model('account/transaction');
            $this->model_account_transaction->addPaymentHistory($order_data['customer_id'],array('order_id'=>$order_data['order_id'],'amount'=>$data['tr_amount'],'description'=>$this->config->get('payment_tpay_order_status_completed')." " .$note));

        } elseif ($tr_status == 'FALSE') {

            $this->model_checkout_order->addOrderHistory($order_data['order_id'], $this->config->get('payment_tpay_order_status_error'), $note, TRUE);
        }
        return true;
    }

}

?>
