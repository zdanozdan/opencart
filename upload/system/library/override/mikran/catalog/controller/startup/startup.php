<?php
class mikran_ControllerStartupStartup extends ControllerStartupStartup {
	public function index() {
        parent::index();

        if(isset($this->request->get['lang'])) {
            $languages = $this->model_localisation_language->getLanguages();
            foreach($languages as $lang) {
                $code = explode("-",$lang['code']);
                if($code[0] == $this->request->get['lang']) {
                    $code = $lang['code'];
                    
                    $this->session->data['language'] = $code;
                    setcookie('language', $code, time() + 60 * 60 * 24 * 30, '/', $this->request->server['HTTP_HOST']);

                    // Overwrite the default language object
                    $language = new Language($code);
                    $language->load($code);
		
                    $this->registry->set('language', $language);
		
                    // Set the config language_id
                    $this->config->set('config_language_id', $languages[$code]['language_id']);
                }
            }
        }
    }
}