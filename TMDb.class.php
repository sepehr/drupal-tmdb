<?php

/**
 * TMDb is a PHP wrapper for TMDb API which is mainly written 
 * to be used in Drupal modules, Also to be respectful 
 * to the coding standards of Drupal community.
 *
 * @version 0.1
 * @author Sepehr Lajevardi - me@sepehr.ws
 * 
 * @see http://api.themoviedb.org/2.1/
 *
 * @license GNU General Public License 3.0
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * TMDb API base class.
 * Provides basic functionality to interact with http://api.themoviedb.org/2.1/.
 */
class TMDb {
  // TODO: Add properties docs.
  
  // API call default params.
  const XML = 'xml';
  const JSON = 'json';
  const YAML = 'yaml';
  const LANG = 'en';
  const VERSION = 2.1;
  const SERVER = 'http://api.themoviedb.org/';
  
  // API call HTTP methods.
  const GET = 'get';
  const POST = 'post';
  
  // API method types.
  const AUTH = 'Auth';
  const MEDIA = 'Media';
  const MOVIE = 'Movie';
  const PERSON = 'Person';
  const GENRES = 'Generes';
  
  // Props.
  protected $key;
  protected $format;
  protected $server;
  protected $version;
  protected $language;
  
  /**
   * Class constructor.
   *
   * @param $key
   *   API key.
   * @param $server
   *   API server address.
   * @param $version
   *   API version.
   * @param $format
   *   API call response format.
   * @param $language
   *   API call response language.
   */
  public function __construct($key, $server = TMDb::SERVER, $version = TMDb::VERSION, $format = TMDb::JSON, $language = TMDb::LANG) {
    $this->setKey($key);
    $this->setServer($server);
    $this->setVersion($version);
    $this->setFormat($format);
    $this->setLanguage($language);
  }
  
  /**
   * Set API key.
   *
   * @param
   *   $key New API key to be set.
   */
  public function setKey($key) {
    $this->key = $key;
  }
  
  /**
   * Set API server address.
   *
   * @param
   *   $key New API server to be set.
   */
  public function setServer($server) {
    $this->server = $server;
  }
  
  /**
   * Set API version.
   *
   * @param
   *   $version New API version to be set.
   */
  public function setVersion($version) {
    $this->version = $version;
  }
  
  /**
   * Set API response format.
   *
   * @param
   *   $format New API response format to be set.
   */
  public function setFormat($format) {
    $this->format = $format;
  }
  
  /**
   * Set API response language.
   *
   * @param
   *   $language New API response language to be set.
   */
  public function setLanguage($language) {
    $this->language = $language;
  }
  
  /**
   * Get API key.
   *
   * @return
   *   $key New API key to be set.
   */
  public function getKey() {
    return $this->key;
  }
  
  /**
   * Get API server address.
   *
   * @return
   *   $key New API server to be set.
   */
  public function getServer() {
    return $this->server;
  }
  
  /**
   * Get API version.
   *
   * @return
   *   $version New API version to be set.
   */
  public function getVersion() {
    return $this->version;
  }
  
  /**
   * Get API response format.
   *
   * @return
   *   $format New API response format to be set.
   */
  public function getFormat() {
    return $this->format;
  }
  
  /**
   * Get API response language.
   *
   * @return
   *   $language New API response language to be set.
   */
  public function getLanguage() {
    return $this->language;
  }
  
  /**
   * Builds API call URL for lazies to cheer!
   */
  protected function getBaseUrl() {
    $server = $this->getServer();
    if (substr(trim($server), -1) != '/') {
      $server .= '/';
    }
    
    return $server . $this->getVersion() . '/';
  }
  
  /**
   * Extracts and returns an API method's type.
   *
   * @param
   *   $method The name of the API method.
   *
   * @return
   *   The API method's type.
   *
   * @see http://api.themoviedb.org/2.1/
   */
  protected function getMethodType($method) {
    return substr($method, 0, strpos($method, '.'));
  }

  /**
   * Builds API call GET URL for a specific method.
   *
   * @param $method
   *   API call method name.
   * @param $params
   *   API method parameteres to be passed.
   *
   * @return
   *   Built API call URL.
   *
   * @see buildBaseUrl()
   * @see http://api.themoviedb.org/2.1/
   */
  protected function buildCallUrl($method, $params, $format, $language) {
    $parts = array($method);
    if (!is_null($language))
      $parts[] = $language;
    $parts[] = $format;
    $parts[] = $this->getKey();
    
    $call_url = $this->getBaseUrl() . implode('/', $parts);
    if (!is_null($params)) {
      switch ($params[0]) {
        case '?':
          $call_url .= $params;
          break;
          
        default:
          $call_url .= '/' . $params;
      }
    }
    return $call_url;
  }
  
  /**
	 * Call a TMDb API method.
	 *
	 * @param $method
	 *   API method name to be included in the API Call URL.
	 * @param $params
	 *   Parameters to be included in the API Call URL. Either an array or a string.
	 * @param $format
	 *   Specific API response format for $method call.
	 * @param $http_method
	 *   The HTTP method to be used for the call.
	 *   If set to POST, the $params parameter "should" be an array.
   * @param $language
   *   Specific API response language for $method call.
	 *
	 * @return
	 *   API response in $format.
	 */
  public function call($method, $params = NULL, $format = NULL, $language = NULL, $http_method = TMDb::GET) {
    $call_url = $response = NULL;
    $format = (is_null($format)) ? $this->getFormat() : $format;
    $language = (is_null($language)) ? $this->getLanguage() : $language;
    
    switch (strtolower($http_method)) {
      case TMDb::GET:
        // Check parameters.
        if (!is_null($params)) {
          $params = (is_array($params)) ? '?' . http_build_query($params) : urlencode($params);
        }
        
        // Check language and set the API call URL.
        $language = ($this->getMethodType($method) == TMDb::AUTH) ? NULL : $this->getLanguage();
        $call_url = $this->buildCallUrl($method, $params, $format, $language);
        $response = $this->fetch($call_url);
        break;
        
      case TMDb::POST:
        if (!is_array($params)) {
          throw new TMDbException('The $params parameter passed to call() method should be an array when using HTTP POST method.');
        }
        $params['type'] = $format;
        $params['api_key'] = $this->getKey();
        $call_url = $this->getBaseUrl() . $method;  
        $response = $this->fetch($call_url, TMDb::POST, $params);
        break;
    }
    
    return $response;
  }
  
  
}
