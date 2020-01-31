<?php
class ModelExtensionShippingGls extends Model {
	public function getQuote($address) {
		$this->load->language('extension/shipping/gls');

		$quote_data = array();

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "gls_rates left join " .DB_PREFIX."country on ".DB_PREFIX."country.country_id = ".DB_PREFIX."gls_rates.country_id where ".DB_PREFIX."gls_rates.country_id = '" . (int)$address['country_id'] ."'" );

        if ($query->num_rows) {
            $status = true;
        } else {
            $status = false;
        }

        $surcharge = $this->config->get('shipping_gls_surcharge');
        $viatoll = $this->config->get('shipping_gls_viatoll');
        $method_data = array();

		if ($status) {
			$quote_data = array();
            //"-100" means no extra fees added = 100 + vat
            $price = ($query->row['price'] > 0 ? abs($query->row['price']) + (abs($query->row['price']) * (float)$surcharge/100) + (float)$viatoll : abs($query->row['price']));

            if($query->row['free_from'] > 0) {
                $price = ($this->cart->getSubTotal() > $query->row['free_from']) ? 0 : $price;
            }
            
			$quote_data['gls'] = array(
				//'code'         => 'gls.gls_'.$query->row['country_id'],
                'code'         => 'gls.gls',
				'title'        => $this->language->get('text_description') ." (". $query->row['name'] .")",
				'cost'         => $price,
				'tax_class_id' => $query->row['tax_class_id'],
				'text'         => $this->currency->format($this->tax->calculate($price, $query->row['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']),
			);

			$method_data = array(
				'code'       => 'gls',
				'title'      => $this->language->get('text_title'),
				'quote'      => $quote_data,
				'sort_order' => $this->config->get('shipping_gls_sort_order'),
				'error'      => false,
			);
		}

		return $method_data;
	}
}
