<?php
use Cocur\Slugify\Slugify;

class mikran_ControllerStartupSeoUrl extends ControllerStartupSeoUrl {

	public function rewrite($link) {
        //match product
        //https://mikran.com.pl/index.php?route=product/product&amp;product_id=30
	    $slugify = new Slugify(['lowercase' => false]);
	    $slugify->addRule('amp', '');
	    
        $url_info = parse_url(str_replace('&amp;', '&', $link));
        parse_str($url_info['query'], $data);

        foreach ($data as $key => $value) {
            if (isset($data['route'])) {
                $lang_code = explode("-",$this->session->data['language'])[0];
                
                if ($data['route'] == 'product/manufacturer/info') {
                    $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "manufacturer WHERE manufacturer_id = '" . (int)$data['manufacturer_id'] . "'");
                    if($query->rows) {

                        $url_query = $this->build_query($data);

                        $url =  $url_info['scheme'] . '://' . $url_info['host'] . (isset($url_info['port']) ? ':' . $url_info['port'] : '') . "/".$lang_code."/idm".$data['manufacturer_id'].'/'.$slugify->slugify($query->row['name']).$url_query;

                        if(isset($data['meta_title'])) {
                            if(strcmp($data['meta_title'],$slugify->slugify($query->row['name'])) != 0 ) {
                                return $this->response->redirect($url);
                            }
                        }
                        return $url;
                    }
                }
                
                if ($data['route'] == 'common/home') {
                    $home_url =  $url_info['scheme'] . '://' . $url_info['host'] . (isset($url_info['port']) ? ':' . $url_info['port'] : '') . "/".$lang_code."/";
                    return $home_url;			    
                }

                if ($data['route'] == 'product/category' && $key == 'path') {
                    $path = implode(",",explode("_",$this->db->escape($data['path'])));
                    $query = $this->db->query("SELECT category_id,name FROM " . DB_PREFIX . "category_description WHERE category_id in (" . $path . ") AND language_id = '" . (int)$this->config->get('config_language_id') . "' ORDER BY FIND_IN_SET (category_id,'".$path."')");
                    
                    $url = "";
                    foreach($query->rows as $row) {
                        $url = $url.'/'.$slugify->slugify($row['name']);
                    }

                    $url_query = $this->build_query($data);

                    if(isset($data['meta_title'])) {
                        if(strcmp('/'.$data['meta_title'],$url) != 0 ) {
                            $redirect_url = $url_info['scheme'] . '://' . $url_info['host'] . (isset($url_info['port']) ? ':' . $url_info['port'] : '') . "/".$lang_code."/idc".$data['path'].'/'.$url.$url_query;
                            return $this->response->redirect($redirect_url);
                        }
                    }
                    
                    return $url_info['scheme'] . '://' . $url_info['host'] . (isset($url_info['port']) ? ':' . $url_info['port'] : '') . "/".$lang_code."/idc".$data['path'].$url.$url_query;
                }
                if ($data['route'] == 'product/product' && $key == 'product_id') {
                    $this->load->model('catalog/product');
                    $product_meta = $this->model_catalog_product->getProductMeta($data['product_id']);
                    $product_slug = $slugify->slugify($product_meta['name']);

                    $url_query = $this->build_query($data);
                    
                    if(isset($data['meta_title'])) {
                        if(strcmp($data['meta_title'],$product_slug) != 0 ) {
                            
                            $redirect_url = $url_info['scheme'] . '://' . $url_info['host'] . (isset($url_info['port']) ? ':' . $url_info['port'] : '') . "/".$lang_code."/id".$data['product_id'].'/'.$product_slug.$url_query;
                            return $this->response->redirect($redirect_url);
                        }
                    }
			
                    return $url_info['scheme'] . '://' . $url_info['host'] . (isset($url_info['port']) ? ':' . $url_info['port'] : '') . "/".$lang_code."/id".$data['product_id'].'/'.$product_slug.$url_query;
                }
            }
        }
        
	    #No mikran rewrite ? Try OC
	    return parent::rewrite($link);
	}

    public function build_query($data) {

        $url_query = '';
        $url_data = $data;
        
        unset($url_data['route']);
        unset($url_data['path']);
        unset($url_data['manufacturer_id']);
        unset($url_data['product_id']);
        unset($url_data['meta_title']);
        unset($url_data['lang']);
        
        //var_dump($url_data);
                    
        $url_query = '';
        
        foreach ($url_data as $key => $value) {
            $url_query .= '&' . rawurlencode((string)$key) . '=' . rawurlencode((is_array($value) ? http_build_query($value) : (string)$value));
        }
                    
        if ($url_query) {
            $url_query = '?' . str_replace('&', '&amp;', trim($url_query, '&'));
        }

        return $url_query;
    }
}
