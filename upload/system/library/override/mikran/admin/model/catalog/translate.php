<?php
use Google\Cloud\Translate\TranslateClient;

trait Translate {
    public function translate($source,$target,$default_source='pl') {
        $client = new Predis\Client();
        $translate = new TranslateClient([
            'key' => GCS_KEY,
        ]);

        if($default_source===$target) {
            return $source;
        }
        
        $name = $client->get($target.'/'.$source);
        if(!$name) {
            $trans = $translate->translate($source,['target'=>$target,'source'=>$default_source]);
            $name = $trans['text'];
            $client->set($target.'/'.$source, $name);
        }
        
        return $name;
    }
}