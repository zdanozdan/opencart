<?php
class ModelExtensionShippmentGls extends Model {
    public $fixtures = array(
        "PL"=>array("Polska","15","1","1","1","1","1"),
        "AL"=>array("Albania","355.30","551.10","678.70","939.40","1156.10","1431.10"),
        "AT"=>array("Austria","55.00","57.20","61.60","67.10","70.40","83.60"),
        "BE"=>array("Belgia","48.40","51.70","53.90","60.50","62.70","83.60"),
        "BA"=>array("Bośnia i Hercegowina","355.30","551.10","678.70","939.40","1156.10","1431.10"),
        "BG"=>array("Bułgaria","71.50","73.70","75.90","81.40","81.40","89.10"),
        "HR"=>array("Chorwacja (kontynent, Krk)","96.80","99.00","103.40","107.80","112.20","121.00"),
        "CY"=>array("Cypr (EU)","152.90","268.40","385.00","617.10","733.70","965.80"),
        "ME"=>array("Czarnogóra","355.30","551.10","678.70","939.40","1156.10","1431.10"),
        "CZ"=>array("Czechy","44.00","47.30","48.40","51.70","53.90","82.50"),
        "DK"=>array("Dania","48.40","51.70","53.90","60.50","62.70","83.60"),
        "EE"=>array("Estonia","68.20","71.50","73.70","79.20","81.40","100.10"),
        "FI"=>array("Finlandia","95.70","102.30","110.00","115.50","126.50","183.70"),
        "FR"=>array("Francja I (kontynent)","70.40","73.70","73.70","88.00","96.80","114.40"),
        "FR_2"=>array("Francja II (Korsyka)","149.60","157.30","163.90","182.60","190.30","206.80"),
        "GR"=>array("Grecja I (Ateny. Pireus)","150.70","265.10","378.40","433.40","513.70","677.60"),
        "GR_2"=>array("Grecja II (reszta kraju)","169.40","284.90","400.40","449.90","532.40","697.40"),
        "ES"=>array("Hiszpania I (kontynent)","94.60","96.80","99.00","115.50","126.50","145.20"),
        "ES_1"=>array("Hiszpania II (Baleary)","244.20","270.60","298.10","379.50","394.90","460.90"),
        "ES_2"=>array("Hiszpania III Wyspy Kanaryjskie Melilla Ceuta Gibraltar Andora","393.80","402.60","447.70","590.70","667.70","790.90"),
        "NL"=>array("Holandia","48.40","51.70","51.70","57.20","60.50","83.60"),
        "IE"=>array("Irlandia","92.40","93.50","96.80","100.10","102.30","130.90"),
        "IS"=>array("Islandia","355.30","551.10","678.70","939.40","1156.10","1431.10"),
        "XK"=>array("Kosowo","355.30","551.10","678.70","939.40","1156.10","1431.10"),
        "LI"=>array("Lichtenstein","61.60","64.90","67.10","72.60","74.80","118.80"),
        "LT"=>array("Litwa","44.00","47.30","48.40","51.70","53.90","82.50"),
        "LU"=>array("Luxemburg","48.40","51.70","55.00","62.70","64.90","83.60"),
        "LV"=>array("Łotwa","57.20","60.50","62.70","68.20","70.40","88.00"),
        "XX"=>array("Macedonia","355.30","551.10","678.70","939.40","1156.10","1431.10"),
        "MT"=>array("Malta","173.80","266.20","349.80","441.10","484.00","601.70"),
        "DE"=>array("Niemcy","44.00","47.30","48.40","51.70","53.90","86.90"),
        "NO"=>array("Norwegia","149.60","151.80","154.00","157.30","159.50","159.50"),
        "PT"=>array("Portugalia I (kontynent)","107.80","111.10","115.50","123.20","126.50","145.20"),
        "PT_1"=>array("Portugalia II (Azory. Madera)","216.70","295.90","511.50","696.30","696.30","1119.80"),
        "RO"=>array("Rumunia","70.40","72.60","74.80","80.30","80.30","86.90"),
        "SM"=>array("San Marino","70.40","73.70","73.70","78.10","80.30","111.10"),
        "RS"=>array("Serbia","96.80","99.00","103.40","111.10","112.20","124.30"),
        "SK"=>array("Słowacja","44.00","47.30","50.60","53.90","55.00","82.50"),
        "SI"=>array("Słowenia","74.80","77.00","81.40","86.90","91.30","96.80"),
        "CH"=>array("Szwajcaria","58.30","61.60","63.80","69.30","71.50","116.60"),
        "SE"=>array("Szwecja","82.50","84.70","89.10","95.70","97.90","143.00"),
        "TR"=>array("Turcja","177.10","300.30","422.40","667.70","790.90","1036.20"),
        "HU"=>array("Węgry","49.50","51.70","52.80","56.10","58.30","82.50"),
        "GB"=>array("Wielka Brytania","70.40","73.70","74.80","79.20","81.40","114.40"),
        "IT"=>array("Włochy","70.40","73.70","73.70","78.10","80.30","114.40"),
        "FO"=>array("Wyspy Owcze","355.30","551.10","678.70","939.40","1156.10","1431.10"),
    );

    public function install() {
        // TBD if needed
        //`currency_code` CHAR(3) NOT NULL,
        $this->db->query("
			CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "gls_rates` (
			  `gls_rate_id` INT(11) NOT NULL AUTO_INCREMENT,
			  `country_id` INT(11) NOT NULL UNIQUE,
			  `tax_class_id` INT(11) NOT NULL,
              `price` decimal(15,4) NOT NULL,
              `free_from` decimal(15,4) NOT NULL,
			  `modifier` VARCHAR(255),
			  `date_added` DATETIME NOT NULL,
			  `date_modified` DATETIME NOT NULL,
			  PRIMARY KEY (`gls_rate_id`)
			) ENGINE=MyISAM DEFAULT COLLATE=utf8_general_ci;");
    }

    
    public function load_fixtures() {
        $this->load->model('localisation/tax_class');
        $tax_classes = $this->model_localisation_tax_class->getTaxClasses();
        $tax_class_id = $tax_classes[0]['tax_class_id'];
        $this->load->model('localisation/country');
        $countries = $this->model_localisation_country->getCountries();        
        foreach($countries as $country) {
            $code = $country['iso_code_2'];
            $country_id = $country['country_id'];
            if(isset($this->fixtures[$code])) {
                $row = $this->fixtures[$code];
                $price = $row[5];
                $modifier = sprintf("5:%s,10:%s,15:%s,25:%s,30:%s,40:%s",$row[1],$row[2],$row[3],$row[4],$row[5],$row[6]);
                $sql = sprintf("INSERT INTO " . DB_PREFIX . "gls_rates (country_id, tax_class_id, price, modifier, date_added,date_modified) values (%s,%s,%s,'%s',%s,%s)",$country_id,$tax_class_id,$price,$modifier,"NOW()","NOW()");
                $this->db->query($sql);
            }
        }
    }
    
    public function getRates($data = array()) {
        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "gls_rates");
        return $query->rows;
    }

    public function addRates($data) {
		if (isset($data['country_row']) && isset($data['shippment_gls_rate']) && isset($data['shipping_gls_tax_class_id'])) {
            $this->db->query("DELETE FROM " . DB_PREFIX . "gls_rates");
			foreach ($data['country_row'] as $key=>$value) {
				//$this->db->query("DELETE FROM " . DB_PREFIX . "gls_rates WHERE country_id = '" . (int)$value . "'");
                $rate = $data['shippment_gls_rate'][$key];
                $tax_class_id = $data['shipping_gls_tax_class_id'][$key];
                $modifier = $data['shippment_gls_modifier'][$key];
                $free_from = $data['shippment_gls_free_from'][$key];

				$this->db->query("INSERT INTO " . DB_PREFIX . "gls_rates SET country_id = '" . (int)$value . "', tax_class_id = '" . (int)$tax_class_id . "', price = '" . $rate . "',free_from = '".$free_from."', modifier = '".$modifier."', date_added = NOW() ON DUPLICATE KEY UPDATE country_id = '".(int)$value . "' , tax_class_id = '" . (int)$tax_class_id . "', price = '" . $rate . "', modifier = '" .$modifier ."'");
			}
		}
	}
}