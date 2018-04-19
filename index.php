<?php
require 'vendor/autoload.php';
use Sunra\PhpSimple\HtmlDomParser;

class Scraper {

  // Page location.
  var $pageUrl = 'https://www.black-ink.org/';

  function __construct() {                                                       
    $this->scrape();    
  }

  /**
   * Scrape the list page, extract all data from the page and generate a data array.
   */
  function scrape() {
    // Getting lisitng contents 
    // Create a DOM object from the URL
    $html = HtmlDomParser::file_get_html($this->pageUrl);
    // Get the article elements under 'Digitalia' category
    // Get all article elements with class 'category-workblog'
    $articles = $html->find('article[class=category-workblog]');
    $totalFileSize = 0;
    if($articles) {
      foreach($articles as $article) {
        // Extract url, link, desc and keyword.
        $elements = $article->children(0)->childNodes();
        $url = $elements[0]->children(0)->first_child()->href;
        $link = $elements[0]->children(0)->first_child()->innertext;
        $desc = $elements[2]->children(0)->first_child()->text();
        $keyword = $elements[3]->children(0)->first_child()->first_child()->innertext;
        // Filesize of the linked HTML page
        $fileSize = $this->curl($url);
  
        if($fileSize){
          $totalFileSize += $fileSize;
        }
        $data['results'][] = array(
                              'url' => $url,
                              'link' => $link,
                              'meta description' => $desc, 
                              'keywords' => $keyword, 
                              'filesize' => $this->fileSizeFormatter($fileSize)
                            );
      }
      $data['total'] = $this->fileSizeFormatter($totalFileSize);
  
      print json_encode($data);
    }
    return;
  }

  /**
   * @ param string $url. Page url location which need to fetch
   * @ return filesize of the url
   */
  function curl($url) {
    // Create a cURL handle
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);

    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

    $result = curl_exec($ch);
    // Check if any error occurred
    if (!curl_errno($ch)) {
      $fileSize = curl_getinfo($ch, CURLINFO_SIZE_DOWNLOAD);
    }
    curl_close($ch);
    return $fileSize;
  }

  /**
   * Formats filesize in human readable way.
   *
   * @param filesize in bytes $size
   * @return string Formatted Filesize, e.g. "113.24 MB".
   */
  function fileSizeFormatter($size) {
    $units = array('KB', 'MB', 'GB', 'TB');
    $currUnit = '';
    while (count($units) > 0  &&  $size > 1024) {
      $currUnit = array_shift($units);
      $size /= 1024;
    }
    return ($size | 0) . $currUnit;
  }

}

$scraper = new Scraper();

?>