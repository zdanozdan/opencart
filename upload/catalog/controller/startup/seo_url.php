<?php
use Cocur\Slugify\Slugify;

class ControllerStartupSeoUrlBase extends Controller {
	public function index() {
		// Add rewrite to url class
		if ($this->config->get('config_seo_url')) {
			$this->url->addRewrite($this);
		}

		// Decode URL
		if (isset($this->request->get['_route_'])) {
			$parts = explode('/', $this->request->get['_route_']);

			// remove any empty arrays from trailing
			if (utf8_strlen(end($parts)) == 0) {
				array_pop($parts);
			}

			foreach ($parts as $part) {
				$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "seo_url WHERE keyword = '" . $this->db->escape($part) . "' AND store_id = '" . (int)$this->config->get('config_store_id') . "'");

				if ($query->num_rows) {
					$url = explode('=', $query->row['query']);

					if ($url[0] == 'product_id') {
						$this->request->get['product_id'] = $url[1];
					}

					if ($url[0] == 'category_id') {
						if (!isset($this->request->get['path'])) {
							$this->request->get['path'] = $url[1];
						} else {
							$this->request->get['path'] .= '_' . $url[1];
						}
					}

					if ($url[0] == 'manufacturer_id') {
						$this->request->get['manufacturer_id'] = $url[1];
					}

					if ($url[0] == 'information_id') {
						$this->request->get['information_id'] = $url[1];
					}

					if ($query->row['query'] && $url[0] != 'information_id' && $url[0] != 'manufacturer_id' && $url[0] != 'category_id' && $url[0] != 'product_id') {
						$this->request->get['route'] = $query->row['query'];
					}
				} else {
					$this->request->get['route'] = 'error/not_found';

					break;
				}
			}

			if (!isset($this->request->get['route'])) {
				if (isset($this->request->get['product_id'])) {
					$this->request->get['route'] = 'product/product';
				} elseif (isset($this->request->get['path'])) {
					$this->request->get['route'] = 'product/category';
				} elseif (isset($this->request->get['manufacturer_id'])) {
					$this->request->get['route'] = 'product/manufacturer/info';
				} elseif (isset($this->request->get['information_id'])) {
					$this->request->get['route'] = 'information/information';
				}
			}
		}
	}

	public function rewrite($link) {
		$url_info = parse_url(str_replace('&amp;', '&', $link));

		$url = '';

		$data = array();

		parse_str($url_info['query'], $data);

		foreach ($data as $key => $value) {
			if (isset($data['route'])) {
				if (($data['route'] == 'product/product' && $key == 'product_id') || (($data['route'] == 'product/manufacturer/info' || $data['route'] == 'product/product') && $key == 'manufacturer_id') || ($data['route'] == 'information/information' && $key == 'information_id')) {
					$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "seo_url WHERE `query` = '" . $this->db->escape($key . '=' . (int)$value) . "' AND store_id = '" . (int)$this->config->get('config_store_id') . "' AND language_id = '" . (int)$this->config->get('config_language_id') . "'");

					if ($query->num_rows && $query->row['keyword']) {
						$url .= '/' . $query->row['keyword'];

						unset($data[$key]);
					}
				} elseif ($key == 'path') {
					$categories = explode('_', $value);

					foreach ($categories as $category) {
						$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "seo_url WHERE `query` = 'category_id=" . (int)$category . "' AND store_id = '" . (int)$this->config->get('config_store_id') . "' AND language_id = '" . (int)$this->config->get('config_language_id') . "'");

						if ($query->num_rows && $query->row['keyword']) {
							$url .= '/' . $query->row['keyword'];
						} else {
							$url = '';

							break;
						}
					}

					unset($data[$key]);
				}
			}
		}

		if ($url) {
			unset($data['route']);

			$query = '';

			if ($data) {
				foreach ($data as $key => $value) {
					$query .= '&' . rawurlencode((string)$key) . '=' . rawurlencode((is_array($value) ? http_build_query($value) : (string)$value));
				}

				if ($query) {
					$query = '?' . str_replace('&', '&amp;', trim($query, '&'));
				}
			}

			return $url_info['scheme'] . '://' . $url_info['host'] . (isset($url_info['port']) ? ':' . $url_info['port'] : '') . str_replace('/index.php', '', $url_info['path']) . $url . $query;
		} else {
			return $link;
		}
	}
}

class ControllerStartupSeoUrl extends ControllerStartupSeoUrlBase {
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

                        $url =  $url_info['scheme'] . '://' . $url_info['host'] . (isset($url_info['port']) ? ':' . $url_info['port'] : '') . "/".$lang_code."/idm".$data['manufacturer_id'].'/'.$slugify->slugify($query->row['name']);
			    
                        if(isset($data['meta_title'])) {
                            if(strcmp($data['meta_title'],$query->row['name']) != 0 ) {
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
			
                    if(isset($data['meta_title'])) {
                        if(strcmp('/'.$data['meta_title'],$url) != 0 ) {
                            $redirect_url = $url_info['scheme'] . '://' . $url_info['host'] . (isset($url_info['port']) ? ':' . $url_info['port'] : '') . "/".$lang_code."/idc".$data['path'].'/'.$url;
                            return $this->response->redirect($redirect_url);
                        }
                    }
                        
                    return $url_info['scheme'] . '://' . $url_info['host'] . (isset($url_info['port']) ? ':' . $url_info['port'] : '') . "/".$lang_code."/idc".$data['path'].$url;
                }
                if ($data['route'] == 'product/product' && $key == 'product_id') {
                    $this->load->model('catalog/product');
                    $product_meta = $this->model_catalog_product->getProductMeta($data['product_id']);
                    $product_slug = $slugify->slugify($product_meta['name']);
                    
                    if(isset($data['meta_title'])) {
                        if(strcmp($data['meta_title'],$product_slug) != 0 ) {
				
                            $redirect_url = $url_info['scheme'] . '://' . $url_info['host'] . (isset($url_info['port']) ? ':' . $url_info['port'] : '') . "/".$lang_code."/id".$data['product_id'].'/'.$product_slug;
                            return $this->response->redirect($redirect_url);
                        }
                    }
			
                    return $url_info['scheme'] . '://' . $url_info['host'] . (isset($url_info['port']) ? ':' . $url_info['port'] : '') . "/".$lang_code."/id".$data['product_id'].'/'.$product_slug;
                }
            }
        }

	    #No mikran rewrite ? Try OC
	    return parent::rewrite($link);
	}
}
