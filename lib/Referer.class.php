<?php

/**
 * @author Tiago
 */
class Referer {
    
    /*
     * Obtem todos os dados referentes ao visitante atual
     */
    public static function getVisitante(){
        $visitante = new Visitante();
        $visitante->ip=$_SERVER['REMOTE_ADDR'];
        $visitante->referer=$_SERVER ['HTTP_REFERER'];
        $visitante->userAgent=strtolower($_SERVER['HTTP_USER_AGENT']);
        $visitante->bot=self::isBot();
        $visitante->mobile=self::isMobile();
        return $visitante;
    }
    public static function isMobile() {
        $op = strtolower($_SERVER['HTTP_X_OPERAMINI_PHONE']);
        $ua = strtolower($_SERVER['HTTP_USER_AGENT']);
        $ac = strtolower($_SERVER['HTTP_ACCEPT']);

        return strpos($ac, 'application/vnd.wap.xhtml+xml') !== false
                || $op != ''
                || strpos($ua, 'android') !== false
                || strpos($ua, 'mobile') !== false
                || strpos($ua, 'samsung') !== false
                || strpos($ua, 'opera mini') !== false
                || strpos($ua, 'up.browser') !== false
                || strpos($ua, 'up.link') !== false
                || strpos($ua, 'ericsson,') !== false
                || strpos($ua, 'smartphone') !== false
                || strpos($ua, 'ipaq') !== false
                || strpos($ua, 'mini') !== false
                || strpos($ua, 'mobi') !== false
                || strpos($ua, 'phone') !== false
                || strpos($ua, 'tablet') !== false
                || strpos($ua, 'sony') !== false
                || strpos($ua, 'symbian') !== false
                || strpos($ua, 'nokia') !== false
                || strpos($ua, 'windows ce') !== false
                || strpos($ua, 'epoc') !== false
                || strpos($ua, 'nitro') !== false
                || strpos($ua, 'j2me') !== false
                || strpos($ua, 'midp-') !== false
                || strpos($ua, 'cldc-') !== false
                || strpos($ua, 'netfront') !== false
                || strpos($ua, 'mot') !== false
                || strpos($ua, 'audiovox') !== false
                || strpos($ua, 'blackberry') !== false
                || strpos($ua, 'panasonic') !== false
                || strpos($ua, 'philips') !== false
                || strpos($ua, 'sanyo') !== false
                || strpos($ua, 'sharp') !== false
                || strpos($ua, 'sie-') !== false
                || strpos($ua, 'portalmmm') !== false
                || strpos($ua, 'blazer') !== false
                || strpos($ua, 'danger') !== false
                || strpos($ua, 'palm') !== false
                || strpos($ua, 'series60') !== false
                || strpos($ua, 'palmsource') !== false
                || strpos($ua, 'pocketpc') !== false
                || strpos($ua, 'rover') !== false
                || strpos($ua, 'au-mic,') !== false
                || strpos($ua, 'alcatel') !== false
                || strpos($ua, 'ericy') !== false
                || strpos($ua, 'up.link') !== false
                || strpos($ua, 'vodafone/') !== false
                || strpos($ua, 'avantgo') !== false
                || strpos($ua, 'blackberry') !== false
                || strpos($ua, 'bolt') !== false
                || strpos($ua, 'hiptop') !== false
                || strpos($ua, 'up.browser') !== false
                || strpos($ua, 'webos') !== false
                || strpos($ua, 'wos') !== false;
    }

    public static function isBot() {
        $op = strtolower($_SERVER['HTTP_X_OPERAMINI_PHONE']);
        $ua = strtolower($_SERVER['HTTP_USER_AGENT']);
        $ac = strtolower($_SERVER['HTTP_ACCEPT']);
        $ip = $_SERVER['REMOTE_ADDR'];

        return $ip == '66.249.65.39'
                || strpos($ua, 'googlebot') !== false
                || strpos($ua, 'mediapartners') !== false
                || strpos($ua, 'yahooysmcm') !== false
                || strpos($ua, 'baiduspider') !== false
                || strpos($ua, 'msnbot') !== false
                || strpos($ua, 'slurp') !== false
                || strpos($ua, 'ask') !== false
                || strpos($ua, 'teoma') !== false
                || strpos($ua, 'spider') !== false
                || strpos($ua, 'heritrix') !== false
                || strpos($ua, 'attentio') !== false
                || strpos($ua, 'twiceler') !== false
                || strpos($ua, 'irlbot') !== false
                || strpos($ua, 'fast crawler') !== false
                || strpos($ua, 'fastmobilecrawl') !== false
                || strpos($ua, 'jumpbot') !== false
                || strpos($ua, 'googlebot-mobile') !== false
                || strpos($ua, 'yahooseeker') !== false
                || strpos($ua, 'motionbot') !== false
                || strpos($ua, 'mediobot') !== false
                || strpos($ua, 'chtml generic') !== false
                || strpos($ua, 'nokia6230i/. fast crawler') !== false;
    }
    
    public static function registrarVisitaEvento($visitante,$idEvento) {
        global $wpdb;
        $campos = array();
        $campos['ip'] = $visitante->ip;
        $campos['referer'] = $visitante->referer;
        $campos['userAgent'] = $visitante->userAgent;
        $campos['mobile'] = $visitante->mobile;
        $campos['id_evento'] = $idEvento;
        $mascaras = array_fill(0, count($campos) + 1, '%s');
        // Se é nova, salvar em banco
        $ok = $wpdb->insert('ev_visitante_evento', $campos, $mascaras);
    }

}

/*
 * Refere-se a um único visitante
 */
class Visitante{
    public $ip;
    public $referer;
    public $userAgent;
    public $bot;
    public $mobile;
}