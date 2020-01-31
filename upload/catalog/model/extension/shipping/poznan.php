<?php
class ModelExtensionShippingPoznan extends Model {
	function getQuote($address) {
		$this->load->language('extension/shipping/poznan');

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "zone_to_geo_zone WHERE geo_zone_id = '" . (int)$this->config->get('shipping_poznan_geo_zone_id') . "' AND country_id = '" . (int)$address['country_id'] . "' AND (zone_id = '" . (int)$address['zone_id'] . "' OR zone_id = '0')");

		if (!$this->config->get('shipping_poznan_geo_zone_id')) {
			$status = true;
		} elseif ($query->num_rows) {
			$status = true;
		} else {
			$status = false;
		}

		$method_data = array();

		if ($status) {
			$quote_data = array();

            $sub_total = $this->cart->getSubTotal();

            $price = ($sub_total >= (float)$this->config->get('shipping_poznan_free_from')) ? 0 : (float)$this->config->get('shipping_poznan_price');
            $tax_class_id = (int)$this->config->get('shipping_poznan_tax_class_id');

			$quote_data['poznan'] = array(
				'code'         => 'poznan.poznan',
				'title'        => $this->language->get('text_description'),
				'cost'         => $price,
				'tax_class_id' => $tax_class_id,
				'text'         => $this->currency->format($this->tax->calculate($price, $tax_class_id, $this->config->get('config_tax')), $this->session->data['currency'])
			);

			$method_data = array(
				'code'       => 'poznan',
				'title'      => $this->language->get('text_title'),
				'quote'      => $quote_data,
				'sort_order' => $this->config->get('shipping_poznan_sort_order'),
				'error'      => false
			);
		}

		return $method_data;
	}
}