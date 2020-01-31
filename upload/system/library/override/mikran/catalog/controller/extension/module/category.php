<?php
class mikran_ControllerExtensionModuleCategory extends ControllerExtensionModuleCategory {

	public function index() {
		$this->load->language('extension/module/category');
        $this->load->model('catalog/product');

        $parts = array();
        
		if (isset($this->request->get['path'])) {
			$parts = explode('_', (string)$this->request->get['path']);
		} else {
            if (isset($this->request->get['product_id'])) {
                $product_path = $this->model_catalog_product->getCategoryPath($this->request->get['product_id']);
                $parts = explode('_', $product_path);
            }
		}

		if (isset($parts[0])) {
			$data['category_id'] = $parts[0];
		} else {
			$data['category_id'] = 0;
		}

		if (isset($parts[1])) {
			$data['child_id'] = $parts[1];
		} else {
			$data['child_id'] = 0;
		}

        if (isset($parts[2])) {
			$data['child_lv3_id'] = $parts[2];
		} else {
			$data['child_lv3_id'] = 0;
		}

        if (isset($parts[3])) {
			$data['child_lv4_id'] = $parts[3];
		} else {
			$data['child_lv4_id'] = 0;
		}

		$this->load->model('catalog/category');

		#$this->load->model('catalog/product');

		$data['categories'] = array();

		$categories = $this->model_catalog_category->getCategories(0);

		foreach ($categories as $category) {
			$children_data = array();

			if ($category['category_id'] == $data['category_id']) {
				$children = $this->model_catalog_category->getCategories($category['category_id']);

				foreach($children as $child) {
// CategoriesMenu3rdLevel>>>
					$children_lv3_data = array();
                    $children_lv4_data = array();
					
					if ($child['category_id'] == $data['child_id']) {
						$children_lv3 = $this->model_catalog_category->getCategories($child['category_id']);
					
						foreach ($children_lv3 as $child_lv3) {

                            if ($child_lv3['category_id'] == $data['child_lv3_id']) {
                                $children_lv4 = $this->model_catalog_category->getCategories($child_lv3['category_id']);

                                foreach ($children_lv4 as $child_lv4) {
                                    $filter_data_lv4 = array(
                                        'filter_category_id'  => $child_lv4['category_id'],
                                        'filter_sub_category' => true
                                    );
                                    $children_lv4_data[] = array(
                                        'category_id' => $child_lv4['category_id'],
                                        'name'  => $child_lv4['name'] . ($this->config->get('config_product_count') ? ' (' . $this->model_catalog_product->getTotalProducts($filter_data_lv4) . ')' : ''),
                                        'href'  => $this->url->link('product/category', 'path=' . $category['category_id'] . '_' . $child['category_id'] . '_' . $child_lv3['category_id'] . '_' . $child_lv4['category_id']));
                                }
                            }
                            
							$filter_data_lv3 = array(
								'filter_category_id'  => $child_lv3['category_id'],
								'filter_sub_category' => true
							);                            
						
							$children_lv3_data[] = array(
								'category_id' => $child_lv3['category_id'],
								'name'  => $child_lv3['name'] . ($this->config->get('config_product_count') ? ' (' . $this->model_catalog_product->getTotalProducts($filter_data_lv3) . ')' : ''),
								'href'  => $this->url->link('product/category', 'path=' . $category['category_id'] . '_' . $child['category_id'] . '_' . $child_lv3['category_id'])
							);
						}
					
					}
// <<<CategoriesMenu3rdLevel

                    
					$filter_data = array('filter_category_id' => $child['category_id'], 'filter_sub_category' => true);

					$children_data[] = array(
						'category_id' => $child['category_id'],
						'name' => $child['name'] . ($this->config->get('config_product_count') ? ' (' . $this->model_catalog_product->getTotalProducts($filter_data) . ')' : ''),
                        'children_lv3' => $children_lv3_data,
                        'children_lv4' => $children_lv4_data,
						'href' => $this->url->link('product/category', 'path=' . $category['category_id'] . '_' . $child['category_id']),
                        
					);
				}
			}

			$filter_data = array(
				'filter_category_id'  => $category['category_id'],
				'filter_sub_category' => true
			);

			$data['categories'][] = array(
				'category_id' => $category['category_id'],
				'name'        => $category['name'] . ($this->config->get('config_product_count') ? ' (' . $this->model_catalog_product->getTotalProducts($filter_data) . ')' : ''),
				'children'    => $children_data,
				'href'        => $this->url->link('product/category', 'path=' . $category['category_id']),
			);
		}

 		return $this->load->view('extension/module/category', $data);
	}
}