<?php
class ControllerCheckoutMikranShippingAddress extends Controller {
	public function index() {
		$this->load->language('checkout/checkout');
        $data = array();

        //if (isset($this->session->data['shipping_address']['address_id'])) {
            //$data['address_id'] = $this->session->data['shipping_address']['address_id'];
            //} else {
            //$data['address_id'] = $this->customer->getAddressId();
            //}
        
        //$data['addresses'] = $this->model_account_address->getAddresses();
        
        // Validate if shipping is required. If not the customer should not have reached this page.
		if (!$this->cart->hasShipping()) {
            return $this->response->redirect($this->url->link('checkout/mikran/cart'));
		}
        
		// Validate cart has products and has stock.
		if ((!$this->cart->hasProducts() && empty($this->session->data['vouchers'])) || (!$this->cart->hasStock() && !$this->config->get('config_stock_checkout'))) {
            return $this->response->redirect($this->url->link('checkout/mikran/cart'));
		}

		// Validate minimum quantity requirements.
		$products = $this->cart->getProducts();
        
		foreach ($products as $product) {
			$product_total = 0;

			foreach ($products as $product_2) {
				if ($product_2['product_id'] == $product['product_id']) {
					$product_total += $product_2['quantity'];
				}
			}

			if ($product['minimum'] > $product_total) {
                return $this->response->redirect($this->url->link('checkout/mikran/cart'));
			}
		}

        if ($this->customer->isLogged()) {
            $this->load->model('account/address');
            $data['address_id'] = $this->customer->getAddressId();
            $data['addresses'] = $this->model_account_address->getAddresses();
            $data['use_address_route'] = $this->url->link('checkout/mikran/shipping_address/use_address');
        }

        if (isset($this->session->data['delivery_address']['checkbox'])) {
			$data['delivery_address_checkbox'] = $this->session->data['delivery_address']['checkbox'];
		} else {
			$data['delivery_address_checkbox'] = '';
		}

        if (isset($this->session->data['shipping_address']['firstname'])) {
			$data['firstname'] = $this->session->data['shipping_address']['firstname'];
		} else {
			$data['firstname'] = '';
		}

        if (isset($this->session->data['shipping_address']['lastname'])) {
			$data['lastname'] = $this->session->data['shipping_address']['lastname'];
		} else {
			$data['lastname'] = '';
		}

        if (isset($this->session->data['shipping_address']['email'])) {
			$data['email'] = $this->session->data['shipping_address']['email'];
		} else {
			$data['email'] = '';
		}

        if (isset($this->session->data['shipping_address']['telephone'])) {
			$data['telephone'] = $this->session->data['shipping_address']['telephone'];
		} else {
			$data['telephone'] = '';
		}

        if (isset($this->session->data['shipping_address']['comment'])) {
			$data['comment'] = $this->session->data['shipping_address']['comment'];
		} else {
			$data['comment'] = '';
		}

        if (isset($this->session->data['shipping_address']['company'])) {
			$data['company'] = $this->session->data['shipping_address']['company'];
		} else {
			$data['company'] = '';
		}

        if (isset($this->session->data['shipping_address']['address_1'])) {
			$data['address_1'] = $this->session->data['shipping_address']['address_1'];
		} else {
			$data['addresss_1'] = '';
		}

        if (isset($this->session->data['shipping_address']['address_2'])) {
			$data['address_2'] = $this->session->data['shipping_address']['address_2'];
		} else {
			$data['addresss_2'] = '';
		}

        if (isset($this->session->data['shipping_address']['city'])) {
			$data['city'] = $this->session->data['shipping_address']['city'];
		} else {
			$data['city'] = '';
		}

		if (isset($this->session->data['shipping_address']['postcode'])) {
			$data['postcode'] = $this->session->data['shipping_address']['postcode'];
		} else {
			$data['postcode'] = '';
		}

		if (isset($this->session->data['shipping_address']['country_id'])) {
			$data['country_id'] = $this->session->data['shipping_address']['country_id'];
		} elseif(isset($this->session_data['country_id'])) {
            $data['country_id'] = $this->session->data['country_id'];
        } else {
			$data['country_id'] = $this->config->get('config_country_id');
		}

        //Country info
        $this->load->model('localisation/country');
        $country_info = $this->model_localisation_country->getCountry($data['country_id']);
        
        $this->load->model('localisation/country');
		$data['countries'] = $this->model_localisation_country->getCountriesDict();
        if(isset($data['countries'][$data['country_id']])) {
            $iso_code_2 = $data['countries'][$data['country_id']]['iso_code_2'];
            $countries = new Ibericode\Vat\Countries();
            if($countries->isCountryCodeInEU($iso_code_2)) {
                $data['iso_code_2'] = $iso_code_2;
            }
        }

		//if (isset($this->session->data['shipping_address']['zone_id'])) {
        //		$data['zone_id'] = $this->session->data['shipping_address']['zone_id'];
		//} else {
        //		$data['zone_id'] = '';
		//}

		// Custom Fields
		$this->load->model('account/custom_field');

        //VAT
        $data['vat_field'] = $this->model_account_custom_field->getVATField();

        if (isset($this->session->data['shipping_address']['vat_field_value'])) {
			$data['vat_field']['value'] = $this->session->data['shipping_address']['vat_field_value'];
		}
        
        $data['shipping_required'] = $this->cart->hasShipping();
        $data['cart_url'] = $this->url->link('checkout/mikran/cart');

        //delivery address
        if(isset($this->session->data['delivery_address']['address_1'])) {
            $data['delivery_address_1'] = $this->session->data['delivery_address']['address_1'];
        }
        if(isset($this->session->data['delivery_address']['address_2'])) {
            $data['delivery_address_2'] = $this->session->data['delivery_address']['address_2'];
        }
        if(isset($this->session->data['delivery_address']['city'])) {
            $data['delivery_city'] = $this->session->data['delivery_address']['city'];
        }
        if(isset($this->session->data['delivery_address']['postcode'])) {
            $data['delivery_postcode'] = $this->session->data['delivery_address']['postcode'];
        }

        //POST CALLED
        if (isset($this->request->post['shipping-address'])) {
            if ((utf8_strlen(trim($this->request->post['shipping-address']['firstname'])) < 1) || (utf8_strlen(trim($this->request->post['shipping-address']['firstname'])) > 32)) {
                $data['error']['firstname'] = $this->language->get('error_firstname');
            } else {
                $this->session->data['shipping_address']['firstname'] = $this->request->post['shipping-address']['firstname'];
            }            
            $data['firstname'] = $this->request->post['shipping-address']['firstname'];
            
            if ((utf8_strlen(trim($this->request->post['shipping-address']['lastname'])) < 1) || (utf8_strlen(trim($this->request->post['shipping-address']['lastname'])) > 32)) {
                $data['error']['lastname'] = $this->language->get('error_lastname');
            } else {
                $this->session->data['shipping_address']['lastname'] = $this->request->post['shipping-address']['lastname'];
            }
            $data['lastname'] = $this->request->post['shipping-address']['lastname'];
            
            if ((utf8_strlen($this->request->post['shipping-address']['email']) > 96) || (!filter_var($this->request->post['shipping-address']['email'], FILTER_VALIDATE_EMAIL))) {
                $data['error']['email'] = $this->language->get('error_email');
            } else {
                $this->session->data['shipping_address']['email'] = $this->request->post['shipping-address']['email'];
            }
            
            $data['email'] = $this->request->post['shipping-address']['email'];
            
            if ((utf8_strlen($this->request->post['shipping-address']['telephone']) < 3) || (utf8_strlen($this->request->post['shipping-address']['telephone']) > 32)) {
                $data['error']['telephone'] = $this->language->get('error_telephone');
            } else {
                $this->session->data['shipping_address']['telephone'] = $this->request->post['shipping-address']['telephone'];
            }
            
            $data['telephone'] = $this->request->post['shipping-address']['telephone'];
            
            if (utf8_strlen($this->request->post['shipping-address']['company']) > 128) {
                $data['error']['company'] = $this->language->get('error_company');
            } else {
                $this->session->data['shipping_address']['company'] = $this->request->post['shipping-address']['company'];
            }

            $data['comment'] = $this->request->post['shipping-address']['comment'];
            
            if (utf8_strlen($this->request->post['shipping-address']['comment']) > 512) {
                $data['error']['comment'] = $this->language->get('error_comment');
            } else {
                $this->session->data['shipping_address']['comment'] = $this->request->post['shipping-address']['comment'];
            }

            $data['company'] = $this->request->post['shipping-address']['company'];

            if ((utf8_strlen(trim($this->request->post['shipping-address']['address_1'])) < 3) || (utf8_strlen(trim($this->request->post['shipping-address']['address_1'])) > 128)) {
                $data['error']['address_1'] = $this->language->get('error_address_1');
            } else {
                $this->session->data['shipping_address']['address_1'] = $this->request->post['shipping-address']['address_1'];
            }

            $data['address_1'] = $this->request->post['shipping-address']['address_1'];

            //delivery address
            //address 1
            if (isset($this->request->post['delivery-address']['address_1'])) {
                
                if ((utf8_strlen(trim($this->request->post['delivery-address']['address_1'])) < 3) || (utf8_strlen(trim($this->request->post['delivery-address']['address_1'])) > 128)) {
                    $data['error']['delivery_address_1'] = $this->language->get('error_address_1');
                } else {
                    $this->session->data['delivery_address']['address_1'] = $this->request->post['delivery-address']['address_1'];
                }
                
                $data['delivery_address_1'] = $this->request->post['delivery-address']['address_1'];
            } 

            //address 2
            if (isset($this->request->post['delivery-address']['address_2'])) {
                if (utf8_strlen($this->request->post['delivery-address']['address_2']) > 128) {
                    $data['error']['delivery_address_2'] = $this->language->get('error_address_2');
                } else {
                    $this->session->data['delivery_address']['address_2'] = $this->request->post['delivery-address']['address_2'];
                }
                $data['delivery_address_2'] = $this->request->post['delivery-address']['address_2'];
            }
            
            //city
            if (isset($this->request->post['delivery-address']['city'])) {
                if ((utf8_strlen(trim($this->request->post['delivery-address']['city'])) < 2) || (utf8_strlen(trim($this->request->post['delivery-address']['city'])) > 128)) {
                    $data['error']['delivery_city'] = $this->language->get('error_city');
                } else {
                    $this->session->data['delivery_address']['city'] = $this->request->post['delivery-address']['city'];
                }
                $data['delivery_city'] = $this->request->post['delivery-address']['city'];
            }

            //postcode
            if (isset($this->request->post['delivery-address']['postcode'])) {
                if ($country_info && $country_info['postcode_required'] && (utf8_strlen(trim($this->request->post['delivery-address']['postcode'])) < 2 || utf8_strlen(trim($this->request->post['delivery-address']['postcode'])) > 10)) {                
                    $data['error']['delivery_postcode'] = $this->language->get('error_postcode');
                } else {
                    $this->session->data['delivery_address']['postcode'] = $this->request->post['delivery-address']['postcode'];
                }
                $data['delivery_postcode'] = $this->request->post['delivery-address']['postcode'];
            }

            if (utf8_strlen($this->request->post['shipping-address']['address_2']) > 128) {
                $data['error']['address_2'] = $this->language->get('error_address_2');
            } else {
                $this->session->data['shipping_address']['address_2'] = $this->request->post['shipping-address']['address_2'];
            }
            $data['address_2'] = $this->request->post['shipping-address']['address_2'];
            
            if ((utf8_strlen(trim($this->request->post['shipping-address']['city'])) < 2) || (utf8_strlen(trim($this->request->post['shipping-address']['city'])) > 128)) {
               $data['error']['city'] = $this->language->get('error_city');
            } else {
                $this->session->data['shipping_address']['city'] = $this->request->post['shipping-address']['city'];
            }

            $data['city'] = $this->request->post['shipping-address']['city'];
            
            if ($country_info && $country_info['postcode_required'] && (utf8_strlen(trim($this->request->post['shipping-address']['postcode'])) < 2 || utf8_strlen(trim($this->request->post['shipping-address']['postcode'])) > 10)) {                
                $data['error']['postcode'] = $this->language->get('error_postcode');
            } else {
                $this->session->data['shipping_address']['postcode'] = $this->request->post['shipping-address']['postcode'];
                $this->session->data['shipping_address']['country'] = $country_info['name'];
                $this->session->data['shipping_address']['country_id'] = $country_info['country_id'];
                $this->session->data['shipping_address']['address_format'] = $country_info['address_format'];
            }

            $data['postcode'] = $this->request->post['shipping-address']['postcode'];
            
            //Custom field - VAT
            if ((utf8_strlen(trim($this->request->post['shipping-address']['vat'])) > 0)) {
                $validator = new Ibericode\Vat\Validator();
                try {
                    $is_valid = $validator->validateVatNumber($data['iso_code_2'].$this->request->post['shipping-address']['vat']); // true (checks format)
                    if($is_valid == false) {
                        $data['error']['vat'] = sprintf($this->language->get('error_vat'),$data['iso_code_2'],$this->request->post['shipping-address']['vat']);
                    } else {
                        $this->session->data['shipping_address']['vat_field_value'] = $this->request->post['shipping-address']['vat'];
                    }
                } catch (Ibericode\Vat\Vies\ViesException $e) {
                    $data['warning']['vat'] = sprintf($this->language->get('error_vat_vies'),$e->getMessage());
                    $this->session->data['shipping_address']['vat_field_value'] = $this->request->post['shipping-address']['vat'];
                }
            }

            //Zone ID. 
            if (isset($this->request->post['shipping_address']['zone_id'])) {
                $this->session->data['shipping_address']['zone_id'] = $this->request->post['shipping-address']['zone_id'];
            } else {
                $this->session->data['shipping_address']['zone_id'] = '';
            }
            $this->session->data['shipping_address']['zone'] = '';

            //comment
            if(!isset($this->session->data['comment'])) {
                $this->session->data['comment'] = '';
            }
            
            $data['vat_field']['value'] = $this->request->post['shipping-address']['vat'];

            if(isset($data['error'])) {
                $data['error']['message'] = $this->language->get('error_form');
            } else {
                $data['success']['message'] = $this->language->get('text_success_form');
                return $this->response->redirect($this->url->link('checkout/mikran/confirm'));
            }

            $data['method'] = 'post';
        }
		
        return $this->load->view('checkout/mikran/shipping_address', $data);
	}

    public function change_delivery() {
        if($this->request->is_post()) {
            if(isset($this->request->post['delivery_address_checkbox'])) {
                $this->session->data['delivery_address']['checkbox'] = "checked";
            } else {
                unset($this->session->data['delivery_address']['checkbox']);
            }
        }
    }

    public function gusapi() {
        $json = array();
        $this->load->language('checkout/checkout');
        if (isset($this->request->post['shipping-address']['vat'])) {
            $gus = new GusApi\GusApi('c94fe10440614e2a9414');
            try {
                $nipToCheck = $this->request->post['shipping-address']['vat'];
                $gus->login();
                
                $gusReports = $gus->getByNip($nipToCheck);
                $json = reset($gusReports)->jsonSerialize();
            } catch (GusApi\Exception\InvalidUserKeyException $e) {
                $json['error'] = $this->language->get('error_bad_user_key');
            } catch (GusApi\Exception\NotFoundException $e) {
                //$json['error'] =  $gus->getResultSearchMessage();
                $json['error'] = $this->language->get('error_company_not_found');
            }
        }
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    public function use_address() {
        if ($this->customer->isLogged()) {
            $this->load->language('checkout/shipping');
            $this->load->model('account/address');
            $this->load->model('account/custom_field');
            $address = $this->model_account_address->updateAddress($this->request->get['id']);
            if($address) {
                $this->session->data['shipping_address'] = $address;
                //VAT field
                $vat_field = $this->model_account_custom_field->getVATField();
                if(isset(($address['custom_field'][$vat_field['custom_field_id']]))) {
                    $this->session->data['shipping_address']['vat_field_value'] = $address['custom_field'][$vat_field['custom_field_id']];
                }
                //email and telephone
                $this->session->data['shipping_address']['email'] = $this->customer->getEmail();
                $this->session->data['shipping_address']['telephone'] = $this->customer->getTelephone();
                $this->session->data['header_messages'][] = array('message'=>$this->language->get('text_address_success'),'severity'=>'success');
            }            
        } 

        return $this->response->redirect($this->url->link('checkout/mikran/checkout'));            
    }
}