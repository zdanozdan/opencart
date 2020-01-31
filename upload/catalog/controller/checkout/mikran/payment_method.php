<?php
class ControllerCheckoutMikranPaymentMethod extends Controller {
    public function index() {
        $this->response->setOutput($this->index_view());
    }
	public function index_view($data=array()) {
		$this->load->language('checkout/checkout');
        
        // Totals
        $totals = array();
        $taxes = $this->cart->getTaxes();
        $total = 0;


        // Because __call can not keep var references so we put them into an array.
        $total_data = array(
            'totals' => &$totals,
            'taxes'  => &$taxes,
            'total'  => &$total
        );
			
        $this->load->model('setting/extension');
        
        $sort_order = array();
        
        $results = $this->model_setting_extension->getExtensions('total');

        foreach ($results as $key => $value) {
            $sort_order[$key] = $this->config->get('total_' . $value['code'] . '_sort_order');
        }
        
        array_multisort($sort_order, SORT_ASC, $results);
        
        foreach ($results as $result) {
            if ($this->config->get('total_' . $result['code'] . '_status')) {
                $this->load->model('extension/total/' . $result['code']);
				
                // We have to put the totals in an array so that they pass by reference.
                $this->{'model_extension_total_' . $result['code']}->getTotal($total_data);
            }
        }

        // Payment Methods
        $method_data = array();
        $this->load->model('setting/extension');        
        $results = $this->model_setting_extension->getExtensions('payment');
        
        $recurring = $this->cart->hasRecurringProducts();
        $country_id = isset($this->session->data['shipping_address']['country_id']) ? $this->session->data['shipping_address']['country_id'] : $this->session->data['country_id'];

        foreach ($results as $result) {
            if ($this->config->get('payment_' . $result['code'] . '_status')) {
                $this->load->model('extension/payment/' . $result['code']);
                $address = array('country_id'=>$country_id,'zone_id'=>0);
                $method = $this->{'model_extension_payment_' . $result['code']}->getMethod($address, $total);

                if ($method) {
                    $image = "";
                    $extra_data = isset($method['render_data']) ? $method['render_data'] : array();

                    if (is_file(DIR_TEMPLATE.$this->config->get('config_theme')."/image/" . $result['code'].'.png')) {
                        $image = 'catalog/view/theme/'.$this->config->get('config_theme')."/image/" . $result['code'].'.png';
                    }
                    
                    try {
                        $extra_data['image'] = $image;
                        $method['html'] = $this->load->view('extension/payment/'.$result['code'].'_extra',$extra_data);
                    }
                    catch (Exception $e) {
                    }
                    
                    if ($recurring) {
                        if (property_exists($this->{'model_extension_payment_' . $result['code']}, 'recurringPayments') && $this->{'model_extension_payment_' . $result['code']}->recurringPayments()) {
                            $method_data[$result['code']] = $method;
                        }
                    } else {
                        $method_data[$result['code']] = $method;
                    }
                }
            }
        }

        $sort_order = array();
        
        foreach ($method_data as $key => $value) {
            $sort_order[$key] = $value['sort_order'];
        }
        
        array_multisort($sort_order, SORT_ASC, $method_data);

        if($this->request->is_post()) {
            if (isset($this->request->post['payment_method'])) {
                if(isset($method_data[$this->request->post['payment_method']])) {
                    $pm = $method_data[$this->request->post['payment_method']];
                    $this->session->data['payment_method'] = $pm;
                    $data['messages'][] = array('message'=>sprintf($pm['title'],$this->language->get('text_payment_success')),'severity'=>'success','module'=>'payment');
                } else {
                    $data['messages'][] = array('message'=>$this->language->get('error_payment'),'severity'=>'danger','module'=>'payment');
                }
            } else {
                $data['messages'][] = array('message'=>$this->language->get('error_payment'),'severity'=>'danger','module'=>'payment');
            }
        }
        
        $this->session->data['payment_methods'] = $method_data;
        
        if (empty($this->session->data['payment_methods'])) {
            $data['messages'][] = array('message'=>sprintf($this->language->get('error_no_payment'), $this->url->link('information/contact')),'severity'=>'error','module'=>'payment');
        } 
        
        if (isset($this->session->data['payment_methods'])) {
            $data['payment_methods'] = $this->session->data['payment_methods'];
        } else {
            $data['payment_methods'] = array();
        }
        
        if (isset($this->session->data['payment_method']['code'])) {
            $data['code'] = $this->session->data['payment_method']['code'];
        } else {
            $data['code'] = '';
        }
        
        if (isset($this->session->data['comment'])) {
            $data['comment'] = $this->session->data['comment'];
        } else {
            $data['comment'] = '';
        }
        
        $data['scripts'] = $this->document->getScripts();
        
        if ($this->config->get('config_checkout_id')) {
            $this->load->model('catalog/information');
            
            $information_info = $this->model_catalog_information->getInformation($this->config->get('config_checkout_id'));
            
            if ($information_info) {
                $data['text_agree'] = sprintf($this->language->get('text_agree'), $this->url->link('information/information/agree', 'information_id=' . $this->config->get('config_checkout_id'), true), $information_info['title'], $information_info['title']);
            } else {
				$data['text_agree'] = '';
            }
        } else {
            $data['text_agree'] = '';
        }
        
        if (isset($this->session->data['agree'])) {
            $data['agree'] = $this->session->data['agree'];
        } else {
            $data['agree'] = '';
        }
        
        return $this->load->view('checkout/mikran/payment_method', $data);
    }
}

