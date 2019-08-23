<?php

class mikran_ControllerCatalogCategory extends ControllerCatalogCategory {
    public function preRender($template_buffer,$template_name,&$data) {
        if(isset($this->session->data['translate_message'])) {
            if(isset($data['success'])) {
                $data['success'] .= $this->session->data['translate_message'];
            }
            else {
                $data['success'] = $this->session->data['translate_message'];
            }
            unset($this->session->data['translate_message']);
        }
        return parent::preRender($template_buffer,$template_name,$data);
    }

    public function edit() {
        $this->load->language('catalog/category');
        
        if ($this->request->server['REQUEST_METHOD'] == 'POST') {
            $this->session->data['translate_message'] = '<hr/>'.$this->language->get('text_translation');
        }
        return parent::edit();
    }
}