<?php
class mikran_ControllerProductProduct extends ControllerProductProduct {

    public function preRender($template_buffer,$template_name,&$data) {
        $this->load->language('product/product');
        $this->load->model('catalog/product');

        $product_id = $data['product_id'];
        

        //preRender is called on all controller actions, like review and so on, so lets filter one we need
        if($this->request->get['route'] == 'product/product') {
            #not path info on product page - lets inject it and create missing breadcrumbs
            if(!isset($this->request->get['path'])) {

                $product_id = $data['product_id'];

                //we need last modification date to display on theme bottom
                $data['date_modified'] = $this->model_catalog_product->getDateModified($product_id);
                $data['text_date_modified'] = $this->language->get('text_date_modified');
                
                $breadcrumbs[] = array_shift($data['breadcrumbs']);
                
                $product_path = $this->model_catalog_product->getCategoryPath($product_id);
                $parts = explode('_', $product_path);
                array_reverse($parts);
                $path = '';
                
                foreach ($parts as $path_id) {
                    if (!$path) {
                        $path = $path_id;
                    } else {
                        $path .= '_' . $path_id;
                    }
                    
                    $category_info = $this->model_catalog_category->getCategory($path_id);
                    
                    if ($category_info) {
                        $breadcrumbs[] = array(
                            'text' => $category_info['name'],
                            'href' => $this->url->link('product/category', 'path=' . $path)
                        );
                    }
                }
                
                for($i=0;$i<sizeof($data['breadcrumbs']);$i++) {
                    $breadcrumbs[] = array_pop($data['breadcrumbs']);
                }
                
                $data['breadcrumbs'] = $breadcrumbs;
            }
        }

        return parent::preRender($template_buffer,$template_name,$data);
    }

    #TODO remove event from DB and this function call
	public function addProductBreadcrumbsHandlerBefore(&$route, &$data, &$output) {
    }
}