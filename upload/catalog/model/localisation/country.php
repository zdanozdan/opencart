<?php
class ModelLocalisationCountry extends Model {
	public function getCountry($country_id) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "country WHERE country_id = '" . (int)$country_id . "' AND status = '1'");

		return $query->row;
	}

	public function getCountries() {
        $lang = $this->session->data['language'];
        $country_data = $this->cache->get('country.catalog.'.$lang);
        
		if (!$country_data) {
			$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "country WHERE status = '1' ORDER BY name ASC");

			$country_data = $query->rows;
            foreach($country_data as $key=>$country) {
                $country_data[$key]['name'] = Locale::getDisplayRegion('-'.$country['iso_code_2'], $lang);
            }

			$this->cache->set('country.catalog.'.$lang, $country_data);
        }

        return $country_data;
	}

    public function getCountriesDict() {
        $countries = $this->getCountries();
        $result = array();
        foreach($countries as $country) {
            $result[$country['country_id']] = $country;
        }

        return $result;
    }

    public function is_eu_member($iso_code_2) {
        $eu_countrycodes = array(
            'AT', 'BE', 'BG', 'HR', 'CY', 'CZ', 'DE', 'DK', 'EE', 'GR',
            'ES', 'FI', 'FR', 'GB', 'HU', 'IE', 'IT', 'LT', 'LU', 'LV',
            'MT', 'NL', 'PL', 'PT', 'RO', 'SE', 'SI', 'SK' ,
        );
        return(in_array($iso_code_2, $eu_countrycodes));
    }
}