<?php
namespace Plinct\Tool;

class Locale {
    /**
     * @return string
     */
    public static function getServerLanguage(): string {
        return (new \Locale())->acceptFromHttp(filter_input(INPUT_SERVER, 'HTTP_ACCEPT_LANGUAGE'));
    }

    public static function translateByGettext($language, $name, $directory) {
        putenv("LC_ALL=$language");
        setlocale(LC_ALL, $language.".utf8");
        bindtextdomain($name, $directory);
        textdomain($name);
    }
}