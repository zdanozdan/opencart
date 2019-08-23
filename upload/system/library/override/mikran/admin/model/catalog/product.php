<?php
use Google\Cloud\Translate\TranslateClient;

class mikran_ModelCatalogProduct extends ModelCatalogProduct {

    public function addProduct($data) {
        if(isset($data['product_id']) && (int)$data['product_id'] > 0) {
            $this->db->query("INSERT INTO " . DB_PREFIX . "product SET model = '" . $this->db->escape($data['model']) . "', sku = '" . $this->db->escape($data['sku']) . "', upc = '" . $this->db->escape($data['upc']) . "', ean = '" . $this->db->escape($data['ean']) . "', jan = '" . $this->db->escape($data['jan']) . "', isbn = '" . $this->db->escape($data['isbn']) . "', mpn = '" . $this->db->escape($data['mpn']) . "', location = '" . $this->db->escape($data['location']) . "', quantity = '" . (int)$data['quantity'] . "', minimum = '" . (int)$data['minimum'] . "', subtract = '" . (int)$data['subtract'] . "', stock_status_id = '" . (int)$data['stock_status_id'] . "', date_available = '" . $this->db->escape($data['date_available']) . "', manufacturer_id = '" . (int)$data['manufacturer_id'] . "', shipping = '" . (int)$data['shipping'] . "', price = '" . (float)$data['price'] . "', points = '" . (int)$data['points'] . "', weight = '" . (float)$data['weight'] . "', weight_class_id = '" . (int)$data['weight_class_id'] . "', length = '" . (float)$data['length'] . "', width = '" . (float)$data['width'] . "', height = '" . (float)$data['height'] . "', length_class_id = '" . (int)$data['length_class_id'] . "', status = '" . (int)$data['status'] . "', tax_class_id = '" . (int)$data['tax_class_id'] . "', sort_order = '" . (int)$data['sort_order'] . "', date_added = NOW(), date_modified = NOW()" . ", product_id = '" .(int)$data['product_id'] ."'");

            $this->load->model('localisation/language');
            $languages = $this->model_localisation_language->getLanguages();
            foreach ($languages as $language) {
                $this->db->query("INSERT INTO " . DB_PREFIX . "product_description SET product_id = '" . (int)$data['product_id'] . "', language_id = '" . (int)$language['language_id'] . "', name = '" . $this->db->escape($data['model']) . "'");
            }
        } else {
            parent::addProduct($data);
        }
    }

    public function translate($source,$target,$default_source='pl') {
        $client = new Predis\Client();
        $translate = new TranslateClient([
            'key' => GCS_KEY,
        ]);
        
        $name = $client->get($target.'/'.$source);
        if(!$name) {
            $trans = $translate->translate($source,['target'=>$target,'source'=>$default_source]);
            $name = $trans['text'];
            $client->set($target.'/'.$source, $name);
        }
        
        return $name;
    }


    /* Mikran model field is unique PLU code. We just select this one and skip other filters */
    public function getProducts($data = array()) {

        if (!empty($data['filter_model'])) {
            $sql = "SELECT * FROM " . DB_PREFIX . "product p LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id) WHERE pd.language_id = '" . (int)$this->config->get('config_language_id') . "'";
			$sql .= " AND p.model = '" . $this->db->escape($data['filter_model']) . "'";;

            $query = $this->db->query($sql);

            return $query->rows;
		}

        //Filter fixed !
        if (!empty($data['filter_name'])) {
            $data['filter_name'] = "%".$data['filter_name'];
		}

        return parent::getProducts($data);
	}


    /* PLU pagination */
    public function getTotalProducts($data = array()) {

		if (!empty($data['filter_model'])) {
            $sql = "SELECT COUNT(DISTINCT p.product_id) AS total FROM " . DB_PREFIX . "product p LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id)";
            $sql .= " WHERE pd.language_id = '" . (int)$this->config->get('config_language_id') . "'";
			$sql .= " AND p.model = '" . $this->db->escape($data['filter_model']) . "'";
            $query = $this->db->query($sql);
            return $query->row['total'];
		}

        return parent::getTotalProducts($data);
	}
    
    public function editProduct($product_id, $data) {

        $translate = new TranslateClient([
            'key' => GCS_KEY,
        ]);

        $client = new Predis\Client();

        //Source string is always pl-pl, this one can be modified only
        $this->load->model('localisation/language');
        $pl = $this->model_localisation_language->getLanguageByCode('pl-pl');
        if (!isset($pl['language_id'])) {
            return parent::editProduct($product_id,$data);
        }

        $pl_tags = explode(',',$data['product_description'][$pl['language_id']]['tag']);
        
        $source_name = $data['product_description'][$language_id]['name'];
        $languages = $this->model_localisation_language->getLanguages();
        unset($languages['pl-pl']);

        //Translate from pl to other available languages
        foreach($languages as $language) {
            $code = explode("-",$language['code'])[0];
            $name = $this->translate($source_name,$code);

            //Automatically generate title tag
            $shop_title = $this->translate('sklep',$code);
            
            $data['product_description'][$language['language_id']]['name'] = $name;
            $data['product_description'][$language['language_id']]['meta_keyword'] = $name;
            $data['product_description'][$language['language_id']]['meta_title'] = $this->config->get('config_name').' - '.$shop_title.' - '.$name;

            //translate tags
            if(is_array($pl_tags)) {
                $tags = array();
                foreach($pl_tags as $tag) {
                    $tags[] = $this->translate($tag,$code);
                }
                $data['product_description'][$language['language_id']]['tag'] = implode(",",$tags);
            }
        }

        //Automatically generate title tag for PL - overwrite whatever is there
        $data['product_description'][$pl['language_id']]['meta_title'] = $this->config->get('config_name').' - sklep - '.$data['product_description'][$pl['language_id']]['name'];
        $data['product_description'][$pl['language_id']]['meta_keyword'] = $data['product_description'][$pl['language_id']]['name'];
        $data['product_description'][$pl['language_id']]['meta_description'] = strip_tags(html_entity_decode($data['product_description'][$pl['language_id']]['description']));

        //Autogenerate title keywords in format: cateogories,name
        $this->load->model('catalog/product');
        $this->load->model('catalog/category');
        $product_cat = $this->model_catalog_product->getProductCategories($this->request->get['product_id']);

        if(isset($product_cat[0])) {
            $product_cat = $product_cat[0];
            $path = $this->model_catalog_category->getCategoryPath($product_cat);
            $level = array_column($path, 'level');
            array_multisort($level, SORT_ASC, $path);
            foreach($path as $p) {
                $descriptions = $this->model_catalog_category->getCategoryDescriptions($p['path_id']);
                foreach($descriptions as $lang_id => $description) {
                    $data['product_description'][$lang_id]['meta_keyword'] .= ','. $description['name'];
                }
            }
        }

        parent::editProduct($product_id,$data);
    }
}
