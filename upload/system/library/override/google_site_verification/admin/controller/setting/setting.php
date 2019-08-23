<?php
class google_site_verification_ControllerSettingSetting extends ControllerSettingSetting {
	public function preRender( $template_buffer, $template_name, &$data ) {
		if ($template_name != 'setting/setting.twig') {
			return parent::preRender( $template_buffer, $template_name, $data );
		}

		// add support for the Google site verification code field
		$language = $this->load->language('setting/setting');
		$data['entry_google_site_verification'] = $this->language->get('entry_google_site_verification');
		if (isset($this->request->post['config_google_site_verification'])) {
			$data['config_google_site_verification'] = $this->request->post['config_google_site_verification'];
		} else {
			$data['config_google_site_verification'] = $this->config->get('config_google_site_verification');
		}
		
		// add the Google site verification field to the template file
		$search = '<label class="col-sm-2 control-label" for="input-theme">{{ entry_theme }}</label>';
		$add  = '              <div class="form-group">'."\n";
		$add .= '                <label class="col-sm-2 control-label" for="input-google-site-verification">{{ entry_google_site_verification }}</label>'."\n";
		$add .= '                <div class="col-sm-10">'."\n";
		$add .= '                  <input type="text" name="config_google_site_verification" value="{{ config_google_site_verification }}" placeholder="{{ entry_google_site_verification }}" id="input-google-site-verification" class="form-control" />'."\n";
		$add .= '                </div>'."\n";
		$add .= '              </div>'."\n";
		include_once(DIR_SYSTEM.'library/override/modifier.php');
		$template_buffer = Modifier::modifyStringBuffer( $template_buffer,$search,$add,'before',1 );
		return parent::preRender( $template_buffer, $template_name, $data );
	}
}
?>