<?php

class ModelExtensionPaymentTpay extends Model
{
    public function getMethod($address, $total)
    {
        $this->load->language('extension/payment/tpay');

        $this->config->get('payment_tpay_status') && $total >= 0.00
        && $this->session->data['currency'] == $this->config->get('payment_tpay_currency')
            ? $status = true : $status = false;

        $method_data = array();

        if ($status) {
            $method_data = array(
                'code'       => 'tpay',
                'title'      => $this->language->get('text_title'),
                'sort_order' => $this->config->get('payment_tpay_sort_order'),
                'terms'      => '',
                'render_data' => array('banks'=>array('agricole.png','blik.png','getin.png','ing.png','mbank.png','neobank.png','paribas.png','pko.png','pocztowy.png','sepa.png','tmobile.png','alior.png','city.png','idea.png','inteligo.png','millenium.png','nest.png','pkobp.png','plusbank.png','santandeer.png','spoldzielcze.png')),
            );
        }

        return $method_data;
    }
}

?>
