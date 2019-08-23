<?php
class mikran_ControllerCommonLanguage extends ControllerCommonLanguage {
    public function language() {
        $new_lang = $this->request->post['code'];
        $redirect = $this->request->post['redirect'];

        if(isset($new_lang) && isset($redirect)) {
            $new_lang = explode('-',$new_lang)[0];
            $curr_lang = explode("-",$this->session->data['language'])[0];
            $redirect = htmlspecialchars_decode($this->request->post['redirect'], ENT_COMPAT);

            $r = str_replace($this->request->server['HTTP_HOST'].'/'.$curr_lang,$this->request->server['HTTP_HOST'].'/'.$new_lang, $redirect);
            $this->session->data['language'] = $this->request->post['code'];
            $this->response->redirect($r);
        }

        $code = $this->config->get('config_language');
        $lang_code = explode("-",$code)[0];

        $redirect_url = ($this->request->server['HTTPS'] ? 'https://' : 'http://') . $this->request->server['HTTP_HOST'] . '/'.$lang_code .'/';
        return $this->response->redirect($redirect_url);
    }
}