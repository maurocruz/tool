<?php
namespace Plinct\Tool;

class Locale
{
  /**
   * @return string
   */
  public static function getServerLanguage(): string {
    if (filter_input(INPUT_SERVER, 'HTTP_ACCEPT_LANGUAGE')) {
      return (new \Locale())->acceptFromHttp(filter_input(INPUT_SERVER, 'HTTP_ACCEPT_LANGUAGE'));
    }
    return false;
  }

  public static function translateByGettext($language, $domain, $directory) {
    setlocale(LC_MESSAGES, $language.".utf8");
    bindtextdomain($domain, $directory);
    bind_textdomain_codeset($domain, 'UTF-8');
    textdomain($domain);
  }
}
