<?php
class mikran_ControllerCatalogOption extends ControllerCatalogOption {
	protected function getForm() {
		$data['text_form'] = !isset($this->request->get['option_id']) ? $this->language->get('text_add') : $this->language->get('text_edit');

		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

		if (isset($this->error['name'])) {
			$data['error_name'] = $this->error['name'];
		} else {
			$data['error_name'] = array();
		}

		if (isset($this->error['option_value'])) {
			$data['error_option_value'] = $this->error['option_value'];
		} else {
			$data['error_option_value'] = array();
		}

		$url = '';

		if (isset($this->request->get['sort'])) {
			$url .= '&sort=' . $this->request->get['sort'];
		}

		if (isset($this->request->get['order'])) {
			$url .= '&order=' . $this->request->get['order'];
		}

		if (isset($this->request->get['page'])) {
			$url .= '&page=' . $this->request->get['page'];
		}

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('catalog/option', 'user_token=' . $this->session->data['user_token'] . $url, true)
		);

		if (!isset($this->request->get['option_id'])) {
			$data['action'] = $this->url->link('catalog/option/add', 'user_token=' . $this->session->data['user_token'] . $url, true);
		} else {
			$data['action'] = $this->url->link('catalog/option/edit', 'user_token=' . $this->session->data['user_token'] . '&option_id=' . $this->request->get['option_id'] . $url, true);
		}

		$data['cancel'] = $this->url->link('catalog/option', 'user_token=' . $this->session->data['user_token'] . $url, true);

		if (isset($this->request->get['option_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
			$option_info = $this->model_catalog_option->getOption($this->request->get['option_id']);
		}

		$data['user_token'] = $this->session->data['user_token'];

		$this->load->model('localisation/language');

		$data['languages'] = $this->model_localisation_language->getLanguages();

		if (isset($this->request->post['option_description'])) {
			$data['option_description'] = $this->request->post['option_description'];
		} elseif (isset($this->request->get['option_id'])) {
			$data['option_description'] = $this->model_catalog_option->getOptionDescriptions($this->request->get['option_id']);
		} else {
			$data['option_description'] = array();
		}

		if (isset($this->request->post['type'])) {
			$data['type'] = $this->request->post['type'];
		} elseif (!empty($option_info)) {
			$data['type'] = $option_info['type'];
		} else {
			$data['type'] = '';
		}

		if (isset($this->request->post['sort_order'])) {
			$data['sort_order'] = $this->request->post['sort_order'];
		} elseif (!empty($option_info)) {
			$data['sort_order'] = $option_info['sort_order'];
		} else {
			$data['sort_order'] = '';
		}

		if (isset($this->request->post['option_value'])) {
			$option_values = $this->request->post['option_value'];
		} elseif (isset($this->request->get['option_id'])) {
			$option_values = $this->model_catalog_option->getOptionValueDescriptions($this->request->get['option_id']);
		} else {
			$option_values = array();
		}

		$this->load->model('tool/image');

		foreach ($option_values as $option_value) {
			if (is_file(DIR_IMAGE . $option_value['image'])) {
				$image = $option_value['image'];
				$thumb = $option_value['image'];
			} else {
				$image = '';
				$thumb = 'no_image.png';
			}

            $option_related_products = $this->model_catalog_option->getProductOptionValues($option_value['option_value_id']);

			$data['option_values'][] = array(
				'option_value_id'          => $option_value['option_value_id'],
				'option_value_description' => $option_value['option_value_description'],
				'image'                    => $image,
				'thumb'                    => $this->model_tool_image->resize($thumb, 100, 100),
				'sort_order'               => $option_value['sort_order'],
                'option_products'          => $option_related_products,
			);            
		}

		$data['placeholder'] = $this->model_tool_image->resize('no_image.png', 100, 100);

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('catalog/option_form', $data));
	}

    protected function validateForm() {

        parent::validateForm();

        //Private members are so so so STUPID as well as PHP is
        $r = new ReflectionObject($this);
        $p = $r->getParentClass()->getProperty('error');
        $p->setAccessible(true);
        $error = $p->getValue($this);

        $this->load->model('localisation/language');
        $languages = $this->model_localisation_language->getLanguages();
        foreach($languages as $code=>$language) {
            if(strcmp($code,'pl-pl') != 0) {
                unset($error['name'][$language['language_id']]);
                //unset($error['option_value'][$language['language_id']]);
            }
        }

        $pl = $this->model_localisation_language->getLanguageByCode('pl-pl');
        $pl_id = $pl['language_id'];

        foreach($error['option_value'] as $option_id=>$option_value) {
            foreach($languages as $code=>$language) {
                if(isset($option_value[$language['language_id']])) {
                    if(strcmp($code,'pl-pl') != 0) {
                        unset($error['option_value'][$option_id][$language['language_id']]);
                    }
                }
            }
        }

        foreach($error['option_value'] as $option_id=>$option_value) {
            if(empty($option_value)) {
                unset($error['option_value'][$option_id]);
            }
        }
        
        if(empty($error['name'])) {
            unset($error['name']);
        }
        if(empty($error['option_value'])) {
            unset($error['option_value']);
        }

        //Again thank you fucking PHP
        $p->setValue($this,$error);

        return !$error;
    }
}