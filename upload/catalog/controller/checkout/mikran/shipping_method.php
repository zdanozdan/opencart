<?php
class ControllerCheckoutMikranShippingMethod extends Controller {
    public function index() {
        $this->response->setOutput($this->index_view());
    }

	public function index_view($data=array()) {
		$this->load->language('checkout/checkout');
        $this->load->language('extension/total/shipping');

        $method_data = array();
        $country_data = array();
        $shipping_address = array();

        if($this->request->is_post()) {
            if (isset($this->request->post['shipping_method'])) {
                $shipping = explode('.', $this->request->post['shipping_method']);
                
                if (isset($shipping[0]) && isset($shipping[1])) {
                    $this->session->data['shipping_method'] = $this->session->data['shipping_methods'][$shipping[0]]['quote'][$shipping[1]];
                    $data['messages'][] = array('message'=>sprintf($this->language->get('text_shipping_success'),$this->session->data['shipping_method']['title']),'severity'=>'success','module'=>'shipping');
                } else {
                    $data['messages'][] = array('message'=>$this->language->get('error_shipping'),'severity'=>'warning','module'=>'shipping');
                }
            } else {
                $data['messages'][] = array('message'=>$this->language->get('error_shipping'),'severity'=>'danger','module'=>'shipping');
            }
        }
        

        if (!isset($this->session->data['shipping_address']['country_id'])) {
            $country_data['country_id'] = $this->session->data['country_id'];
        }

        if (!isset($this->session->data['shipping_address']['zone_id'])) {
            $country_data['zone_id'] = 0;
        }

        if (isset($this->session->data['shipping_address'])) {
            $shipping_address = $this->session->data['shipping_address'];
        }

        $this->load->model('setting/extension');
        $results = $this->model_setting_extension->getExtensions('shipping');          

        foreach ($results as $result) {
            if ($this->config->get('shipping_' . $result['code'] . '_status')) {
                $this->load->model('extension/shipping/' . $result['code']);
                
                $quote = $this->{'model_extension_shipping_' . $result['code']}->getQuote(array_merge($shipping_address,$country_data));
                
                if ($quote) {

                    $extra_html = "";
                    $extra_data = isset($quote['render_data']) ? $quote['render_data'] : array();
                    $image = "";

                    if (is_file(DIR_TEMPLATE.$this->config->get('config_theme')."/image/" . $result['code'].'.png')) {
                        $image = 'catalog/view/theme/'.$this->config->get('config_theme')."/image/" . $result['code'].'.png';
                    }

                    try {
                        $extra_data['image'] = $image;
                        $extra_html = $this->load->view('extension/shippment/'.$result['code'].'_extra',$extra_data);
                    }
                    catch (Exception $e) {
                    }
                    
                    $method_data[$result['code']] = array(
                        'title'      => $quote['title'],
                        'quote'      => $quote['quote'],
                        'sort_order' => $quote['sort_order'],
                        'error'      => $quote['error'],
                        'extra_html' => $extra_html,
                    );
                    if (is_file(DIR_TEMPLATE.$this->config->get('config_theme')."/image/" . $result['code'].'.png')) {
                        $method_data[$result['code']]['image'] = $image;
                    }
                }
            }

			$sort_order = array();

			foreach ($method_data as $key => $value) {
				$sort_order[$key] = $value['sort_order'];
			}

			array_multisort($sort_order, SORT_ASC, $method_data);

			$this->session->data['shipping_methods'] = $method_data;
		}

		if (empty($this->session->data['shipping_methods'])) {
			$data['messages'][] = array('message'=>sprintf($this->language->get('error_no_shipping'), $this->url->link('information/contact')),'severity'=>'danger');
		} 

		if (isset($this->session->data['shipping_methods'])) {
			$data['shipping_methods'] = $this->session->data['shipping_methods'];
		} else {
			$data['shipping_methods'] = array();
		}

		if (isset($this->session->data['shipping_method']['code'])) {
			$data['code'] = $this->session->data['shipping_method']['code'];
		} else {
			$data['code'] = '';
		}

		if (isset($this->session->data['comment'])) {
			$data['comment'] = $this->session->data['comment'];
		} else {
			$data['comment'] = '';
		}

		return $this->load->view('checkout/mikran/shipping_method', $data);
	}
}