<?php
class ModelExtensionTotalCodFee extends Model {
	public function getTotal($total) {
		if (isset($this->session->data['payment_method']) && $this->session->data['payment_method']['code'] == 'cod') {
			
			$this->load->language('extension/total/cod_fee');
			
			$fee_amount = 0;
			
			$sub_total = $this->cart->getSubTotal();
			
			if($this->config->get('total_cod_fee_type') == 'P') {
				$fee_amount = round((($sub_total * $this->config->get('total_cod_fee_fee')) / 100), 2);
			} else {
				$fee_amount = $this->config->get('total_cod_fee_fee');
			}

            $tax = 0;
            if ($this->config->get('total_cod_fee_tax_class_id')) {
            
                $tax_rates = $this->tax->getRates($fee_amount, $this->config->get('total_cod_fee_tax_class_id'));
                
                foreach ($tax_rates as $tax_rate) {
                    if (!isset($total['taxes'][$tax_rate['tax_rate_id']])) {
                        $total['taxes'][$tax_rate['tax_rate_id']] = $tax_rate['amount'];
                    } else {
                        $total['taxes'][$tax_rate['tax_rate_id']] += $tax_rate['amount'];
                    }
                    $tax += $tax_rate['amount'];
                }
            }

			$total['totals'][] = array(
				'code'       => 'cod_fee',
				'title'      => $this->language->get('text_cod_fee'),
				'value'      => $fee_amount,
				'sort_order' => $this->config->get('cod_fee_sort_order'),
                'tax'        => $tax,
			);
			
			$total['total'] += $fee_amount;
		}
	}
}