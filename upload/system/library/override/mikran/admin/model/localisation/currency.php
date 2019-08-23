<?php
class mikran_ModelLocalisationCurrency extends ModelLocalisationCurrency {
    public function refresh($force = false) {
         $c = new Celery(
            'localhost', /* Server */
            '', /* Login */
            '', /* Password */
            0, /* vhost */
            'celery', /* exchange */
            'celery', /* binding */
            6379, /* port */
            'redis' /* connector */
        );
        $c->PostTask('pull_rates',array());
    }
}