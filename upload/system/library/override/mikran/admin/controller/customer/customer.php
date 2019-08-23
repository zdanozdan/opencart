<?php
class mikran_ControllerCustomerCustomer extends ControllerCustomerCustomer {
    public function orders() {
        $this->load->language('sale/order');

        $this->load->model('sale/order');

        $data['text_no_results'] = $this->language->get('text_no_results');
        $data['text_see_order'] = $this->language->get('text_see_order');
        $data['text_loading'] = $this->language->get('text_loading');
        
        $data['column_order_id'] = $this->language->get('column_order_id');
        $data['column_status'] = $this->language->get('column_status');
        $data['column_date_added'] = $this->language->get('column_date_added');
        $data['column_date_modified'] = $this->language->get('column_date_modified');
        $data['column_total'] = $this->language->get('column_total');
        $data['column_action'] = $this->language->get('column_action');
        
        if (isset($this->request->get['page'])) {
            $page = $this->request->get['page'];
        } else {
            $page = 1;
        }

        $data['orders'] = array();

        $results = $this->model_sale_order->getOrdersByCustomerId($this->request->get['customer_id'], array('sort' => "o.date_added", 'order' => 'DESC','limit'=>10,'start'=>($page-1) * 10));

        foreach ($results as $result) {
            
            $data['orders'][] = array(
                'id'         => $result['order_id'],
                'status'      => $result['order_status'],
                'date_added' => date('d/m/y', strtotime($result['date_added'])),
                'date_modified' => date('d/m/y', strtotime($result['date_modified'])),
                'action_view'  => $this->url->link('sale/order/info', 'user_token=' . $this->session->data['user_token'] . '&order_id=' . $result['order_id'], 'SSL'),
                'action_edit'  => $this->url->link('sale/order/edit', 'user_token=' . $this->session->data['user_token'] . '&order_id=' . $result['order_id'], 'SSL'),
                'total'     => $this->currency->format($result['total'], $result['currency_code'], $result['currency_value'])
            );
        }

        $order_total = $this->model_sale_order->getTotalOrdersByCustomerId($this->request->get['customer_id']);

		$pagination = new Pagination();
		$pagination->total = $order_total;
		$pagination->page = $page;
		$pagination->limit = 10;
		$pagination->url = $this->url->link('customer/customer/orders', 'user_token=' . $this->session->data['user_token'] . '&customer_id=' . $this->request->get['customer_id'] . '&page={page}', true);

		$data['pagination'] = $pagination->render();

        $data['results'] = sprintf($this->language->get('text_pagination'), ($order_total) ? (($page - 1) * 10) + 1 : 0, ((($page - 1) * 10) > ($order_total - 10)) ? $order_total : ((($page - 1) * 10) + 10), $order_total, ceil($order_total / 10));
        
        $this->response->setOutput($this->load->view('customer/customer_orders', $data));
    }
}