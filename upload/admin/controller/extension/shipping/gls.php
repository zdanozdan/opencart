<?php
class ControllerExtensionShippingGls extends Controller {
	private $error = array();

    public function install() {
		$this->load->model('extension/shippment/gls');
        $this->model_extension_shippment_gls->install();
    }

	public function index() {
		$this->load->language('extension/shipping/gls');
		$this->document->setTitle($this->language->get('heading_title'));
		$this->load->model('setting/setting');
        $data = array();

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {

			$this->model_setting_setting->editSetting('shipping_gls', $this->request->post);
			//$this->session->data['success'] = $this->language->get('text_success');

            $data['text_success'] = $this->language->get('text_success');

            $this->load->model('extension/shippment/gls');
            $this->model_extension_shippment_gls->addRates($this->request->post);

			//$this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=shipping', true));
		}

        $this->getForm($data);
	}

    protected function getForm($data=array()) {        
		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

		if (isset($this->error['name'])) {
			$data['error_name'] = $this->error['name'];
		} else {
			$data['error_name'] = '';
		}

		if (isset($this->error['description'])) {
			$data['error_description'] = $this->error['description'];
		} else {
			$data['error_description'] = '';
		}

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('extension/shipping/gls', 'user_token=' . $this->session->data['user_token'], true)
		);

        $data['action'] = $this->url->link('extension/shipping/gls', 'user_token=' . $this->session->data['user_token'], true);

		$data['user_token'] = $this->session->data['user_token'];

		$this->load->model('localisation/country');
        $data['countries'] = $this->model_localisation_country->getCountries();
 
        $this->load->model('extension/shippment/gls');
        $data['country_shipping_rates'] = $this->model_extension_shippment_gls->getRates();

        $this->load->model('localisation/tax_class');
        $data['tax_classes'] = $this->model_localisation_tax_class->getTaxClasses();

        if (isset($this->request->post['shipping_gls_sort_order'])) {
			$data['shipping_gls_sort_order'] = $this->request->post['shipping_gls_sort_order'];
		} else {
			$data['shipping_gls_sort_order'] = $this->config->get('shipping_gls_sort_order');
		}

        if (isset($this->request->post['shipping_gls_surcharge'])) {
			$data['shipping_gls_surcharge'] = $this->request->post['shipping_gls_surcharge'];
		} else {
			$data['shipping_gls_surcharge'] = $this->config->get('shipping_gls_surcharge');
		}

        if (isset($this->request->post['shipping_gls_viatoll'])) {
			$data['shipping_gls_viatoll'] = $this->request->post['shipping_gls_viatoll'];
		} else {
			$data['shipping_gls_viatoll'] = $this->config->get('shipping_gls_viatoll');
		}

        if (isset($this->request->post['shipping_gls_status'])) {
			$data['shipping_gls_status'] = $this->request->post['shipping_gls_status'];
		} else {
			$data['shipping_gls_status'] = $this->config->get('shipping_gls_status');
		}
        
		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('extension/shipping/gls', $data));
	}

	protected function validate() {
		if (!$this->user->hasPermission('modify', 'extension/shipping/gls')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		return !$this->error;
	}
}