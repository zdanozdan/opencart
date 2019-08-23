<?php
class mikran_ControllerCommonHome extends ControllerCommonHome {
    #1.check session language - if set redirect
    #2.if no session language, get code from nginx, if not go for pl
    public function forcelang() {
        $lang_code = explode("-",$this->session->data['language']);
        #1
        if(isset($lang_code[0])) {
            $lang_code = $lang_code[0];
            $redirect_url = ($this->request->server['HTTPS'] ? 'https://' : 'http://') . $this->request->server['HTTP_HOST'] . '/'.$lang_code .'/';
            return $this->response->redirect($redirect_url);
        }

        #2
        if(isset($this->request->get['lang'])) {
            $lang_code = $this->request->get['lang'];
        }
        else {
            $lang_code = 'pl';
        }
        $redirect_url = ($this->request->server['HTTPS'] ? 'https://' : 'http://') . $this->request->server['HTTP_HOST'] . '/'.$lang_code .'/';
        return $this->response->redirect($redirect_url);
    }

    public function index() {
        parent::index();
    }
}