<?php
class mikran_ModelCatalogProduct extends ModelCatalogProduct {

    public function getCategoryPath($product_id) {
        $this->load->model('catalog/category');
        
        $categories = $this->getCategories($product_id);
        $first_cat = $categories[0]['category_id'];
        $retval = array();

        $path = function($catid) use (&$path,&$retval) {
            $category_info = $this->model_catalog_category->getCategory($catid);
            if($category_info) {
                $retval[] = $category_info['category_id'];
                if ($category_info['parent_id'] > 0) {
                    $path($category_info['parent_id']);
                }
                return implode('_',array_reverse($retval));
            }
        };

        return $path($first_cat);
    }

    //lets display all subcategory products by default
    public function getProducts($data = array()) {
        if(!isset($data['filter_sub_category'])) {
            $data['filter_sub_category'] = true;
        }
        
        return parent::getProducts($data);
    }

    public function getProductMeta($product_id) {
        $query = $this->db->query("SELECT p.product_id, pd.meta_title, pd.name from " . DB_PREFIX . "product p LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id) WHERE p.product_id = '" . (int)$product_id . "' AND pd.language_id='" . (int)$this->config->get('config_language_id') . "'");
        if ($query->num_rows) {
            return array(
                'product_id'       => $query->row['product_id'],
                'meta_title'       => $query->row['meta_title'],
                'name'             => $query->row['name'],
            );
        }
    }

    public function getDateModified($product_id) {
        $query = $this->db->query("SELECT date_modified  from " . DB_PREFIX . "product WHERE product_id = '" . (int)$product_id . "'");
        return $query->row['date_modified'];
    }
}