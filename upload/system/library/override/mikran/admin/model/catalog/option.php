<?php
use Google\Cloud\Translate\TranslateClient;

class mikran_ModelCatalogOption extends ModelCatalogOption {

    public function getProductOptionValues($option_value_id) {
        $sql = "SELECT pd.product_id,pd.name FROM " . DB_PREFIX . "product_option_value pov left join ". DB_PREFIX ."product_description pd on (pov.product_id = pd.product_id)  WHERE pd.language_id = '". (int)$this->config->get('config_language_id') ."' and pov.option_value_id = '" . (int)$option_value_id . "'";

        $query = $this->db->query($sql);

        $retval = array();

        foreach($query->rows as $value) {
            $retval[] = $value['name'].'=>'.$value['product_id'];
        }
        return $retval;
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

    public function editOption($option_id,$data) {
        //die();
        //Source string is always pl-pl, this one can be modified only
        $this->load->model('localisation/language');
        $pl = $this->model_localisation_language->getLanguageByCode('pl-pl');
        $language_id = $pl['language_id'];
        
        $source_name = $data['option_description'][$language_id]['name'];
        $languages = $this->model_localisation_language->getLanguages();
        unset($languages['pl-pl']);

        foreach($languages as $language) {
            $code = explode("-",$language['code'])[0];
            $name = $this->translate($source_name,$code);
            $data['option_description'][$language['language_id']]['name'] = $name;
        }

        $targets = array();
        foreach($languages as $language) {
            $targets[$language['language_id']] = explode("-",$language['code'])[0];
        }

        foreach($data['option_value'] as $count=>$option_value) {
            $source_option_desc = $option_value['option_value_description'][$language_id]['name'];
            foreach($option_value['option_value_description'] as $lang_id=>$description) {
                if($lang_id != $language_id) {
                    $data['option_value'][$count]['option_value_description'][$lang_id]['name'] = $this->translate($source_option_desc,$targets[$lang_id]);
                }
            }
        }

        parent::editOption($option_id,$data);
    }
}