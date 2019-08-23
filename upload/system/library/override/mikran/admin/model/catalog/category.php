<?php
require_once("translate.php");

class mikran_ModelCatalogCategory extends ModelCatalogCategory {
    use Translate;

    public function editCategory($category_id, $data) {
        $this->load->model('catalog/category');
        $this->load->model('localisation/language');

        $lang_pl = $this->model_localisation_language->getLanguageByCode('pl-pl');
        $cat_name_pl = $data['category_description'][$lang_pl['language_id']]['name'];
        
        foreach ($data['category_description'] as $language_id => $value) {
            $lang = $this->model_localisation_language->getLanguage($language_id);
            #translate category name

            $code = explode("-",$lang['code'])[0];

            $data['category_description'][$language_id]['name'] = $this->translate($cat_name_pl,$code);

            #meta_title
            $path = $this->model_catalog_category->getCategoryPath($category_id);
            $level = array_column($path, 'level');
            array_multisort($level, SORT_ASC, $path);

            $category_names = array();
            foreach($path as $p) {
                $category_names[] = $this->model_catalog_category->getCategoryDescriptions($p['path_id'])[$language_id]['name'];
            }

            array_pop($category_names);
            $category_names[] = $this->translate($cat_name_pl,$code);
            
            $c = $this->translate("Kategoria",explode('-',$code)[0]);
            $data['category_description'][$language_id]['meta_title'] = $this->config->get('config_name') . ' - '.$c.': ';
            $data['category_description'][$language_id]['meta_description'] = $this->config->get('config_name') . ' - '.$c.': ';

            $data['category_description'][$language_id]['meta_title'] .= implode(" > ",$category_names);
            $data['category_description'][$language_id]['meta_description'] .= implode(" > ",$category_names);
            $data['category_description'][$language_id]['meta_keyword'] = implode(",",$category_names);
        }

        parent::editCategory($category_id,$data);
    }
}