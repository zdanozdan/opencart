<?php
class ControllerCheckoutMikranShippingCountry extends Controller {
    public function index() {
        $this->response->setOutput($this->index_view());
    }
	public function index_view($data=array()) {
		$this->load->language('checkout/checkout');

        $this->load->model('localisation/country');
        $country_ids = $this->model_localisation_country->getCountriesDict();

        if($this->request->is_post()) {
            //countries dropdown
            if(isset($this->request->post['country_id'])) {
                $country_id = $this->request->post['country_id'];
                if(array_key_exists($country_id,$country_ids)) {
                    unset($this->session->data['shipping_method']);
                    unset($this->session->data['payment_method']);
                    $this->session->data['shipping_address']['country_id'] = $country_id;
                    $this->session->data['shipping_address']['country'] = $country_ids[$country_id]['name'];
                    $this->session->data['shipping_address']['iso_code_2'] = $country_ids[$country_id]['iso_code_2'];
                    $this->session->data['shipping_address']['iso_code_3'] = $country_ids[$country_id]['iso_code_3'];
                    $this->session->data['country_id'] = $country_id;
                    $data['messages'][] = array('message'=>$this->language->get('text_country_set'),'severity'=>'success','module'=>'country');
                } else {
                    $data['messages'][] = array('message'=>$this->language->get('error_wrong_country'),'severity'=>'error','module'=>'country');
                }
            }
        }

        if (isset($this->session->data['shipping_address']['country_id'])) {
            $data['country_id'] = $this->session->data['shipping_address']['country_id'];
        } else {
            $data['country_id'] = $this->config->get('config_country_id');
        }

        $countries = $this->model_localisation_country->getCountries();
        foreach($countries as $country) {
            if($data['country_id'] == $country['country_id']) {
                $data['country_code'] = $country['iso_code_2'];
                $data['eu_member'] = '';
                if($this->model_localisation_country->is_eu_member($country['iso_code_2'])) {
                    $data['eu_member'] = $country['iso_code_2'];
                }
            }
        }

        $data['countries'] = $countries;
		
		return $this->load->view('checkout/mikran/shipping_country', $data);
	}

    public function set_eu() {
        if (isset($this->request->get['eu']) && $this->request->get['eu'] == 'true') {
            $this->session->data['shipping_address']['is_vat_eu'] = 'true';
        } else {
            unset($this->session->data['shipping_address']['is_vat_eu']);
        }
    }
}