<?php
class mikran_ControllerCatalogProduct extends ControllerCatalogProduct {

    public function preRender( $template_buffer, $template_name, &$data ) {
        if($template_name == "catalog/product_form.twig") {
            if(isset($this->request->get['product_id'])) {
                $data['reload'] = $this->url->link('catalog/product/reload', 'user_token=' . $this->session->data['user_token'] . '&product_id=' . $this->request->get['product_id'], true);
            }
        }

        if($template_name == "catalog/product_list.twig") {
            $data['reload'] = $this->url->link('catalog/product/reload', 'user_token=' . $this->session->data['user_token'], true);
        }

        if(isset($this->session->data['error_message'])) {
            $data['error_warning'] = $this->session->data['error_message'];
            unset($this->session->data['error_message']);
        }

        if(isset($this->session->data['success_message'])) {
            $data['success_message'] = $this->session->data['success_message'];
            unset($this->session->data['success_message']);
        }
        return parent::preRender( $template_buffer, $template_name, $data );
    }

    public function index() {
        parent::index();
    }

    public function reload() {
        try {
            $c = new Celery(
                'localhost', /* Server */
                '', /* Login */
                '', /* Password */
                0, /* vhost */
                'celery', /* exchange */
                'celery', /* binding */
            6379, /* port */
                'redis' /* connector */
            );

            if(isset($this->request->get['product_id'])) {
                $c->PostTask('pull_prices',array($this->request->get['product_id']));
            } else {
                $c->PostTask('pull_prices',array());
            }
        
            $this->session->data['success_message'] = "Task scheduled for execution. Please reload product page and check 'last updated' date";
        } catch (Exception $e) {
            $this->session->data['error_message'] = $e->getMessage();
        }

        if(isset($this->request->get['product_id'])) {
            $this->response->redirect($this->url->link('catalog/product/edit', 'user_token=' . $this->session->data['user_token'] . '&product_id=' . $this->request->get['product_id'], true));
        } else {
            $this->response->redirect($this->url->link('catalog/product/index', 'user_token=' . $this->session->data['user_token'] , true));
        }
        
    }
    
    public function edit() {
        parent::edit();
    }

    public function add() {

        $this->load->language('catalog/product');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('catalog/product');

		if (($this->request->server['REQUEST_METHOD'] == 'POST')) {
            if ($this->request->post['model'] && $this->request->post['sku']) {
                if($this->request->post['model'] == $this->request->post['sku']) {                    
                    $this->request->post['product_id'] = $this->request->post['sku'];
                    $this->request->post['status'] = 0;
                    try {
                        $this->model_catalog_product->addProduct($this->request->post);
                        $this->session->data['success_message'] = sprintf("Product utworzony z kodem SKU (%s). Cena, stan zostaną pobrane automatycznie z GT",$this->request->post['sku']);

                        //Call celery worker to update price and quantity

                        //Success ? Redirect to edit page on this same product
                        $this->response->redirect($this->url->link('catalog/product/edit', 'user_token=' . $this->session->data['user_token'] . '&product_id=' . $this->request->post['product_id'], true));
                    } catch (Exception $e) {
                        $this->session->data['error_message'] = "Błąd zapisu: ".$e->getMessage();
                        unset($this->request->post['product_id']);
                        return $this->getForm();
                    }
                }
                else {
                    $this->session->data['error_message'] = "Pola 'Model' oraz 'SKU' muszą mieć tą samą wartość - kod SKU z subiekta";
                    return $this->getForm();
                }
            }
        }

        unset($this->request->post['product_id']);
        parent::add();
    }

    protected function validateForm() {
        parent::validateForm();
        
        //Private members are so so so STUPID as well as PHP is
        $r = new ReflectionObject($this);
        $p = $r->getParentClass()->getProperty('error');
        $p->setAccessible(true);
        $error = $p->getValue($this);
        //var_dump($error);

        $this->load->model('localisation/language');
        $languages = $this->model_localisation_language->getLanguages();
        //var_dump($languages);

        foreach($error as $error_field=>$lang_message) {
            if(is_array($lang_message)) {
                foreach($languages as $code=>$language) {
                    if(isset($lang_message[$language['language_id']])) {
                        if(strcmp($code,'pl-pl') != 0) {
                            unset($error[$error_field][$language['language_id']]);
                        }
                    }
                }
            }
        }

        if(empty($error['name'])) {
            unset($error['name']);
        }
        if(empty($error['meta_title'])) {
            unset($error['meta_title']);
        }
        if(!empty($error['warning']) && sizeof($error) == 1) {
            unset($error['warning']);
        }

        //Again thank you fucking PHP
        $p->setValue($this,$error);

        return !$error;
    }
}