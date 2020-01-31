<?php
class ControllerCheckoutMikranCart extends Controller {
	public function products() {
		$this->load->language('checkout/cart');

//		$this->document->setTitle($this->language->get('heading_title'));

//		$data['breadcrumbs'] = array();

//		$data['breadcrumbs'][] = array(
///			'href' => $this->url->link('common/home'),
//			'text' => $this->language->get('text_home')
//		);

//		$data['breadcrumbs'][] = array(
//			'href' => $this->url->link('checkout/mikran/cart'),
//			'text' => $this->language->get('heading_title')
//		);

        if (!$this->cart->hasStock() && (!$this->config->get('config_stock_checkout') || $this->config->get('config_stock_warning'))) {
            $data['messages'][] = array('message'=>$this->language->get('error_stock'),'severity'=>'danger');
        } elseif (isset($this->session->data['error'])) {
            $data['messages'][] = array('message'=>$this->session->data['error'],'severity'=>'danger');
            unset($this->session->data['error']);
        }

        if ($this->config->get('config_customer_price') && !$this->customer->isLogged()) {
            $data['messages'][] = array('message'=>sprintf($this->language->get('text_login'), $this->url->link('account/login'), $this->url->link('account/register')),'severity'=>'warning');
        }        
        if (isset($this->session->data['success'])) {
            $data['messages'][] = array('message'=>$this->session->data['success'],'severity'=>'success');
            unset($this->session->data['success']);
        } 
        
        $data['action'] = $this->url->link('checkout/mikran/cart/edit', '', true);
        $data['checkout_action'] = $this->url->link('checkout/mikran/cart/post', '', true);
        $data['remove_action'] = $this->url->link('checkout/mikran/cart/remove');
        
        if ($this->config->get('config_cart_weight')) {
            $data['weight'] = $this->weight->format($this->cart->getWeight(), $this->config->get('config_weight_class_id'), $this->language->get('decimal_point'), $this->language->get('thousand_point'));
        } else {
            $data['weight'] = '';
        }
        
        $this->load->model('tool/image');
        $this->load->model('tool/upload');

        $data['products'] = array();

        $products = $this->cart->getProducts();

        foreach ($products as $product) {
            $product_total = 0;

            foreach ($products as $product_2) {
                if ($product_2['product_id'] == $product['product_id']) {
                    $product_total += $product_2['quantity'];
                }
            }

            if ($product['minimum'] > $product_total) {
                $data['messages'][] = array('message'=>sprintf($this->language->get('error_minimum'), $product['name'], $product['minimum']),'severity'=>'danger');
            }

            if ($product['image']) {
                $image = $this->model_tool_image->resize($product['image'], $this->config->get('theme_' . $this->config->get('config_theme') . '_image_cart_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_cart_height'));
            } else {
                $image = '';
            }

            $option_data = array();

            foreach ($product['option'] as $option) {
                if ($option['type'] != 'file') {
                    $value = $option['value'];
                } else {
                    $upload_info = $this->model_tool_upload->getUploadByCode($option['value']);

                    if ($upload_info) {
                        $value = $upload_info['name'];
                    } else {
                        $value = '';
                    }
                }

                $option_data[] = array(
                    'name'  => $option['name'],
                    'value' => (utf8_strlen($value) > 20 ? utf8_substr($value, 0, 20) . '..' : $value)
                );
            }

            // Display prices
            if ($this->customer->isLogged() || !$this->config->get('config_customer_price')) {
                $unit_price = $this->tax->calculate($product['price'], $product['tax_class_id'], $this->config->get('config_tax'));
					
                $price = $this->currency->format($unit_price, $this->session->data['currency']);
                $total = $this->currency->format($unit_price * $product['quantity'], $this->session->data['currency']);
            } else {
                $price = false;
                $total = false;
            }

            $recurring = '';

            if ($product['recurring']) {
                $frequencies = array(
                    'day'        => $this->language->get('text_day'),
                    'week'       => $this->language->get('text_week'),
                    'semi_month' => $this->language->get('text_semi_month'),
                    'month'      => $this->language->get('text_month'),
                    'year'       => $this->language->get('text_year')
                );

                if ($product['recurring']['trial']) {
                    $recurring = sprintf($this->language->get('text_trial_description'), $this->currency->format($this->tax->calculate($product['recurring']['trial_price'] * $product['quantity'], $product['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']), $product['recurring']['trial_cycle'], $frequencies[$product['recurring']['trial_frequency']], $product['recurring']['trial_duration']) . ' ';
                }

                if ($product['recurring']['duration']) {
                    $recurring .= sprintf($this->language->get('text_payment_description'), $this->currency->format($this->tax->calculate($product['recurring']['price'] * $product['quantity'], $product['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']), $product['recurring']['cycle'], $frequencies[$product['recurring']['frequency']], $product['recurring']['duration']);
                } else {
                    $recurring .= sprintf($this->language->get('text_payment_cancel'), $this->currency->format($this->tax->calculate($product['recurring']['price'] * $product['quantity'], $product['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']), $product['recurring']['cycle'], $frequencies[$product['recurring']['frequency']], $product['recurring']['duration']);
                }
            }

            $rates = $this->tax->getUnitAvgTaxRates($product['price'],$product['tax_class_id']);

            $data['products'][] = array(
                'cart_id'   => $product['cart_id'],
                'thumb'     => $image,
                'name'      => $product['name'],
                'model'     => $product['model'],
                'option'    => $option_data,
                'recurring' => $recurring,
                'quantity'  => $product['quantity'],
                'stock'     => $product['stock'] ? true : !(!$this->config->get('config_stock_checkout') || $this->config->get('config_stock_warning')),
                'reward'    => ($product['reward'] ? sprintf($this->language->get('text_points'), $product['reward']) : ''),
                //'price'     => $price,
                'price'     => $this->currency->format($product['price'],$this->session->data['currency']),
                'price_total_netto' => $this->currency->format($product['price']*$product['quantity'],$this->session->data['currency']),
                //Added extra fields for rendering in template
                'tax_amount'=> $this->currency->format($rates['avg_tax_amount']*$product['quantity'], $this->session->data['currency']),
                'tax_rate'  => $rates['avg_tax_rate'],
                'price_total'=>$this->currency->format(($rates['avg_tax_amount']*$product['quantity'])+($product['price']*$product['quantity']), $this->session->data['currency']),
                'total'     => $total,
                'href'      => $this->url->link('product/product', 'product_id=' . $product['product_id'])
            );
        }

        // Gift Voucher
        $data['vouchers'] = array();

        if (!empty($this->session->data['vouchers'])) {
            foreach ($this->session->data['vouchers'] as $key => $voucher) {
                $data['vouchers'][] = array(
                    'key'         => $key,
                    'description' => $voucher['description'],
                    'amount'      => $this->currency->format($voucher['amount'], $this->session->data['currency']),
                    'remove'      => $this->url->link('checkout/mikran/cart', 'remove=' . $key)
                );
            }
        }

        $data['totals'] = $this->getTotals();

        $data['continue'] = $this->url->link('common/home');

        $data['checkout'] = $this->url->link('checkout/mikran/checkout', '', true);

        $this->load->model('setting/extension');

        $data['modules'] = array();
			
        $files = glob(DIR_APPLICATION . '/controller/extension/total/*.php');

        if ($files) {
            foreach ($files as $file) {
                $result = $this->load->controller('extension/total/' . basename($file, '.php'));
					
                if ($result) {
                    $data['modules'][basename($file,'.php')] = $result;
                }
            }
        }
        return $data;
    }

    public function index($data=array()) {
        $this->load->language('checkout/cart');
        $this->document->setTitle($this->language->get('heading_title'));

        if(isset($this->session->data['messages'])) {
            $data = array_merge(array('messages'=>$this->session->data['messages']),$data);
            unset($this->session->data['messages']);
        }
        
        $data['breadcrumbs'] = array();
        
        $data['breadcrumbs'][] = array(
            'href' => $this->url->link('common/home'),
            'text' => $this->language->get('text_home')
        );
        
        $data['breadcrumbs'][] = array(
            'href' => $this->url->link('checkout/mikran/cart'),
            'text' => $this->language->get('heading_title')
        );

        if ($this->cart->hasProducts() || !empty($this->session->data['vouchers'])) {

            $data = array_merge($data,$this->products());

            //So we dont want this popup method for shipping method
            $data['modules']['shipping'] = $this->load->controller('checkout/mikran/shipping_method/index_view',$data);
            $data['column_left'] = $this->load->controller('common/column_left');
            $data['column_right'] = $this->load->controller('common/column_right');
            $data['content_top'] = $this->load->controller('common/content_top');
            $data['content_bottom'] = $this->load->controller('common/content_bottom');
            $data['footer'] = $this->load->controller('common/footer');
            $data['header'] = $this->load->controller('common/header');
            $data['modules']['payment'] = $this->load->controller('checkout/mikran/payment_method/index_view',$data);
            $data['modules']['country'] = $this->load->controller('checkout/mikran/shipping_country/index_view',$data);
            $this->response->setOutput($this->load->view('checkout/mikran/cart', $data));
        } else {
            $data['messages'][] = array('message'=>$this->language->get('text_empty'),'severity'=>'warning');
			
            $data['continue'] = $this->url->link('common/home');

            unset($this->session->data['success']);

            $data['column_left'] = $this->load->controller('common/column_left');
            $data['column_right'] = $this->load->controller('common/column_right');
            $data['content_top'] = $this->load->controller('common/content_top');
            $data['content_bottom'] = $this->load->controller('common/content_bottom');
            $data['footer'] = $this->load->controller('common/footer');
            $data['header'] = $this->load->controller('common/header');

            $this->response->setOutput($this->load->view('error/not_found', $data));
        }
    }

    public function cart() {
        $data = array();        

        if ($this->cart->hasShipping()) {
			// Validate if shipping address has been set.
			//if (!isset($this->session->data['shipping_address'])) {
			//	$data['redirect'] = $this->url->link('checkout/mikran/checkout', '', true);
			//}

			// Validate if shipping method has been set.
			if (!isset($this->session->data['shipping_method'])) {
				//$data['redirect'] = $this->url->link('checkout/mikran/checkout', '', true);
			}
		}
                
        if ($this->cart->hasProducts() || !empty($this->session->data['vouchers'])) {
            $data = array_merge($data,$this->products());
        } else {
            $data['messages'][] = array('message'=>$this->language->get('text_empty'),'severity'=>'error');
        }        

        return $this->load->view('checkout/mikran/frozen', $data);
    }

    public function add() {
        $this->load->language('checkout/cart');

        $json = array();

        if (isset($this->request->post['product_id'])) {
            $product_id = (int)$this->request->post['product_id'];
        } else {
            $product_id = 0;
        }

        $this->load->model('catalog/product');

        $product_info = $this->model_catalog_product->getProduct($product_id);

        if ($product_info) {
            if (isset($this->request->post['quantity'])) {
                $quantity = (int)$this->request->post['quantity'];
            } else {
                $quantity = 1;
            }

            if (isset($this->request->post['option'])) {
                $option = array_filter($this->request->post['option']);
            } else {
                $option = array();
            }

            $product_options = $this->model_catalog_product->getProductOptions($this->request->post['product_id']);

            foreach ($product_options as $product_option) {
                if ($product_option['required'] && empty($option[$product_option['product_option_id']])) {
                    $json['error']['option'][$product_option['product_option_id']] = sprintf($this->language->get('error_required'), $product_option['name']);
                }
            }

            if (isset($this->request->post['recurring_id'])) {
                $recurring_id = $this->request->post['recurring_id'];
            } else {
                $recurring_id = 0;
            }

            $recurrings = $this->model_catalog_product->getProfiles($product_info['product_id']);

            if ($recurrings) {
                $recurring_ids = array();

                foreach ($recurrings as $recurring) {
                    $recurring_ids[] = $recurring['recurring_id'];
                }

                if (!in_array($recurring_id, $recurring_ids)) {
                    $json['error']['recurring'] = $this->language->get('error_recurring_required');
                }
            }

            if (!$json) {
                $this->cart->add($this->request->post['product_id'], $quantity, $option, $recurring_id);

                $json['success'] = sprintf($this->language->get('text_success'), $this->url->link('product/product', 'product_id=' . $this->request->post['product_id']), $product_info['name'], $this->url->link('checkout/mikran/cart'));

                // Unset all shipping and payment methods
                //unset($this->session->data['shipping_method']);
                unset($this->session->data['shipping_methods']);
                //unset($this->session->data['payment_method']);
                unset($this->session->data['payment_methods']);

                // Totals
                $this->load->model('setting/extension');

                $totals = array();
                $taxes = $this->cart->getTaxes();
                $total = 0;
		
                // Because __call can not keep var references so we put them into an array. 			
                $total_data = array(
                    'totals' => &$totals,
                    'taxes'  => &$taxes,
                    'total'  => &$total
                );

                // Display prices
                if ($this->customer->isLogged() || !$this->config->get('config_customer_price')) {
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

                    $sort_order = array();

                    foreach ($totals as $key => $value) {
                        $sort_order[$key] = $value['sort_order'];
                    }

                    array_multisort($sort_order, SORT_ASC, $totals);
                }

                $json['total'] = sprintf($this->language->get('text_items'), $this->cart->countProducts() + (isset($this->session->data['vouchers']) ? count($this->session->data['vouchers']) : 0), $this->currency->format($total, $this->session->data['currency']));
            } else {
                $json['redirect'] = str_replace('&amp;', '&', $this->url->link('product/product', 'product_id=' . $this->request->post['product_id']));
            }
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    public function edit() {
        $this->load->language('checkout/cart');

        $json = array();

        // Update
        if (!empty($this->request->post['quantity'])) {
            foreach ($this->request->post['quantity'] as $key => $value) {
                $this->cart->update($key, $value);
            }

            $this->session->data['success'] = $this->language->get('text_remove');

            //unset($this->session->data['shipping_method']);
            //unset($this->session->data['shipping_methods']);
            //unset($this->session->data['payment_method']);
            //unset($this->session->data['payment_methods']);
            unset($this->session->data['reward']);

            $this->response->redirect($this->url->link('checkout/mikran/cart'));
        }
        $this->response->redirect($this->url->link('checkout/mikran/cart'));

        //$this->response->addHeader('Content-Type: application/json');
        //$this->response->setOutput(json_encode($json));
    }

    public function remove() {
        $this->load->language('checkout/cart');

        // Remove
        if (isset($this->request->get['key'])) {
            $this->cart->remove($this->request->get['key']);
            unset($this->session->data['vouchers'][$this->request->get['key']]);
            $this->session->data['success'] = $this->language->get('text_remove');
                        
            //unset($this->session->data['shipping_method']);
            //unset($this->session->data['shipping_methods']);
            //unset($this->session->data['payment_method']);
            //unset($this->session->data['payment_methods']);
            unset($this->session->data['reward']);
        }

        $this->response->redirect($this->url->link('checkout/mikran/cart'));
    }

    public function totals() {
        $data['totals'] = $this->getTotals();
        $this->response->setOutput($this->load->view('checkout/mikran/_totals', $data));
    }

    private function getTotals() {
        // Totals
        $this->load->model('setting/extension');
        
        $totals = array();
        $taxes = $this->cart->getTaxes();
        $total = 0;

			
        // Because __call can not keep var references so we put them into an array. 			
        $total_data = array(
            'totals' => &$totals,
            'taxes'  => &$taxes,
            'total'  => &$total
        );

        // Display prices
        if ($this->customer->isLogged() || !$this->config->get('config_customer_price')) {
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

            $sort_order = array();

            foreach ($totals as $key => $value) {
                $sort_order[$key] = $value['sort_order'];
            }

            array_multisort($sort_order, SORT_ASC, $totals);
        }

        $retval = array();

        foreach ($totals as $total) {
            $tax = 0;
            $t = 0;
            if(isset($total['tax'])) {
                $tax = $this->currency->format($total['tax'], $this->session->data['currency']);
                $t = $this->currency->format($total['value']+$total['tax'], $this->session->data['currency']); 
            }
            $retval[] = array(
                'code'  => $total['code'],
                'title' => $total['title'],
                'text'  => $this->currency->format($total['value'], $this->session->data['currency']),
                'tax'   => $tax,
                'total' => $t,
            );
        }

        return $retval;
    }

    public function post() {
        $this->load->language('checkout/checkout');
        $this->load->model('localisation/country');
        $country_ids = $this->model_localisation_country->getCountriesDict();

        if($this->request->is_post()) {
            $redirect = True;
            $country_data = array();

            if (isset($this->request->post['shipping_method'])) {
                $shipping = explode('.', $this->request->post['shipping_method']);                
                if (!isset($shipping[0]) or !isset($shipping[1])) {
                    $redirect=False;
                } else {
                    $data['messages'][] = array('message'=>$this->language->get('text_shipping_set'),'severity'=>'success','module'=>'shipping');
                }
            } else {
                $data['messages'][] = array('message'=>$this->language->get('error_shipping'),'severity'=>'danger','module'=>'shipping');
                $redirect = False;
            }

            if (isset($this->request->post['country_id'])) {
                if(!array_key_exists($this->request->post['country_id'],$country_ids)) {
                    $data['messages'][] = array('message'=>$this->language->get('error_wrong_country'),'severity'=>'danger','module'=>'country');
                    $reditect = False;
                }
            } else {
                $data['messages'][] = array('message'=>$this->language->get('error_wrong_country'),'severity'=>'danger','module'=>'country');
                $redirect = False;
                var_dump('2');
            }

            if (isset($this->request->post['payment_method'])) {
                $payment = $this->request->post['payment_method'];
                $this->load->model('extension/payment/' . $payment);
                $address = array('country_id'=>$this->request->post['country_id'],'zone_id'=>0);

                $method = $this->{'model_extension_payment_' . $payment}->getMethod($address, array());
                if(!is_array($method) || $payment!=$method['code']) {
                    $data['messages'][] = array('severity'=>'danger','message'=>$this->language->get("error_payment_method"),'module'=>'payment');
                    $redirect=False;
                }
            } else {
                $data['messages'][] = array('message'=>$this->language->get('error_payment'),'severity'=>'danger','module'=>'payment');
                $redirect = False;
            }                       
        } else {
            $data['messages'][] = array('message'=>$this->language->get("error_checkout"),'severity'=>'danger');
            $redirect = False;
        }

        //All fine, go to next step - redirect to checkout
        if($redirect == True) {                    
            return $this->response->redirect($this->url->link('checkout/mikran/checkout', '', true));
        }                
        $this->session->data['messages'] = $data['messages'];
        $this->response->redirect($this->url->link('checkout/mikran/cart', '', true));
    }
}
