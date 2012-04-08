<?php
class EpiCurl
{
  const timeout = 3;
  static $inst = null;
  static $singleton = 0;
  private $mc;
  private $msgs;
  private $running;
  private $execStatus;
  private $selectStatus;
  private $sleepIncrement = 1.1;
  private $requests = array();
  private $responses = array();
  private $properties = array();
  private static $timers = array();

  function __construct()
  {
    if(self::$singleton == 0)
    {
      throw new Exception('This class cannot be instantiated by the new keyword.  You must instantiate it using: $obj = EpiCurl::getInstance();');
    }

    $this->mc = curl_multi_init();
    $this->properties = array(
      'code'  => CURLINFO_HTTP_CODE,
      'time'  => CURLINFO_TOTAL_TIME,
      'length'=> CURLINFO_CONTENT_LENGTH_DOWNLOAD,
      'type'  => CURLINFO_CONTENT_TYPE,
      'url'   => CURLINFO_EFFECTIVE_URL
      );
  }

  public function addEasyCurl($ch)
  {
    $key = $this->getKey($ch);
    $this->requests[$key] = $ch;
    curl_setopt($ch, CURLOPT_HEADERFUNCTION, array($this, 'headerCallback'));
    $done = array('handle' => $ch);
    $this->storeResponse($done, false);
    $this->startTimer($key);
    return new EpiCurlManager($key);
  }

  public function addCurl($ch)
  {
    $key = $this->getKey($ch);
    $this->requests[$key] = $ch;
    curl_setopt($ch, CURLOPT_HEADERFUNCTION, array($this, 'headerCallback'));

    $code = curl_multi_add_handle($this->mc, $ch);
    $this->startTimer($key);
    
    // (1)
    if($code === CURLM_OK || $code === CURLM_CALL_MULTI_PERFORM)
    {
      do {
          $code = $this->execStatus = curl_multi_exec($this->mc, $this->running);
      } while ($this->execStatus === CURLM_CALL_MULTI_PERFORM);

      return new EpiCurlManager($key);
    }
    else
    {
      return $code;
    }
  }

  public function getResult($key = null)
  {
    if($key != null)
    {
      if(isset($this->responses[$key]))
      {
        return $this->responses[$key];
      }

      $innerSleepInt = $outerSleepInt = 1;
      while($this->running && ($this->execStatus == CURLM_OK || $this->execStatus == CURLM_CALL_MULTI_PERFORM))
      {
        usleep(intval($outerSleepInt));
        $outerSleepInt = intval(max(1, ($outerSleepInt*$this->sleepIncrement)));
        $ms=curl_multi_select($this->mc, 0);
        if($ms > 0)
        {
          do{
            $this->execStatus = curl_multi_exec($this->mc, $this->running);
            usleep(intval($innerSleepInt));
            $innerSleepInt = intval(max(1, ($innerSleepInt*$this->sleepIncrement)));
          }while($this->execStatus==CURLM_CALL_MULTI_PERFORM);
          $innerSleepInt = 1;
        }
        $this->storeResponses();
        if(isset($this->responses[$key]['data']))
        {
          return $this->responses[$key];
        }
        $runningCurrent = $this->running;
      }
      return null;
    }
    return false;
  }

  public static function getSequence()
  {
    return new EpiSequence(self::$timers);
  }

  public static function getTimers()
  {
    return self::$timers;
  }

  private function getKey($ch)
  {
    return (string)$ch;
  }

  private function headerCallback($ch, $header)
  {
    $_header = trim($header);
    $colonPos= strpos($_header, ':');
    if($colonPos > 0)
    {
      $key = substr($_header, 0, $colonPos);
      $val = preg_replace('/^\W+/','',substr($_header, $colonPos));
      $this->responses[$this->getKey($ch)]['headers'][$key] = $val;
    }
    return strlen($header);
  }

  private function storeResponses()
  {
    while($done = curl_multi_info_read($this->mc))
    {
      $this->storeResponse($done);
    }
  }

  private function storeResponse($done, $isAsynchronous = true)
  {
    $key = $this->getKey($done['handle']);
    $this->stopTimer($key, $done);
    if($isAsynchronous)
      $this->responses[$key]['data'] = curl_multi_getcontent($done['handle']);
    else
      $this->responses[$key]['data'] = curl_exec($done['handle']);

    foreach($this->properties as $name => $const)
    {
      $this->responses[$key][$name] = curl_getinfo($done['handle'], $const);
    }
    if($isAsynchronous)
      curl_multi_remove_handle($this->mc, $done['handle']);
    curl_close($done['handle']);
  }

  private function startTimer($key)
  {
    self::$timers[$key]['start'] = microtime(true);
  }

  private function stopTimer($key, $done)
  {
      self::$timers[$key]['end'] = microtime(true);
      self::$timers[$key]['api'] = curl_getinfo($done['handle'], CURLINFO_EFFECTIVE_URL);
      self::$timers[$key]['time'] = curl_getinfo($done['handle'], CURLINFO_TOTAL_TIME);
      self::$timers[$key]['code'] = curl_getinfo($done['handle'], CURLINFO_HTTP_CODE);
  }

  static function getInstance()
  {
    if(self::$inst == null)
    {
      self::$singleton = 1;
      self::$inst = new EpiCurl();
    }

    return self::$inst;
  }
}

class EpiCurlManager
{
  private $key;
  private $epiCurl;

  public function __construct($key)
  {
    $this->key = $key;
    $this->epiCurl = EpiCurl::getInstance();
  }

  public function __get($name)
  {
    $responses = $this->epiCurl->getResult($this->key);
    return isset($responses[$name]) ? $responses[$name] : null;
  }

  public function __isset($name)
  {
    $val = self::__get($name);
    return empty($val);
  }
}

/*
 * Credits:
 *  - (1) Alistair pointed out that curl_multi_add_handle can return CURLM_CALL_MULTI_PERFORM on success.
 */


class EpiOAuth
{
  public $version = '1.0';

  protected $requestTokenUrl;
  protected $accessTokenUrl;
  protected $authenticateUrl;
  protected $authorizeUrl;
  protected $consumerKey;
  protected $consumerSecret;
  protected $token;
  protected $tokenSecret;
  protected $callback;
  protected $signatureMethod;
  protected $debug = false;
  protected $useSSL = false;
  protected $followLocation = false;
  protected $headers = array();
  protected $userAgent = 'EpiOAuth (http://github.com/jmathai/twitter-async/tree/)';
  protected $connectionTimeout = 5;
  protected $requestTimeout = 30;

  public function addHeader($header)
  {
    if(is_array($header) && !empty($header))
      $this->headers = array_merge($this->headers, $header);
    elseif(!empty($header))
      $this->headers[] = $header;
  }

  public function getAccessToken($params = null)
  {
    if (isset($_GET['oauth_verifier']) && !isset($params['oauth_verifier']))
    {
      $params['oauth_verifier'] = $_GET['oauth_verifier'];
    }
    $resp = $this->httpRequest('POST', $this->getUrl($this->accessTokenUrl), $params);
    return new EpiOAuthResponse($resp);
  }

  public function getAuthenticateUrl($token = null, $params = null)
  { 
    $token = $token ? $token : $this->getRequestToken($params);
    if (is_object($token)) $token = $token->oauth_token;
    $addlParams = empty($params) ? '' : '&'.http_build_query($params, '', '&');
    return $this->getUrl($this->authenticateUrl) . '?oauth_token=' . $token . $addlParams;
  }

  public function getAuthorizeUrl($token = null, $params = null)
  {
    $token = $token ? $token : $this->getRequestToken($params);
    if (is_object($token)) $token = $token->oauth_token;
    return $this->getUrl($this->authorizeUrl) . '?oauth_token=' . $token;
  }

  // DEPRECATED in favor of getAuthorizeUrl()
  public function getAuthorizationUrl($token = null)
  { 
    return $this->getAuthorizeUrl($token);
  }

  public function getRequestToken($params = null)
  {
    if (isset($this->callback) && !isset($params['oauth_callback']))
    {
      $params['oauth_callback'] = $this->callback;
    }
    $resp = $this->httpRequest('POST', $this->getUrl($this->requestTokenUrl), $params);
    return new EpiOAuthResponse($resp);
  }

  public function getUrl($url)
  {
    if($this->useSSL === true)
      return preg_replace('/^http:/', 'https:', $url);

    return $url;
  }

  public function httpRequest($method = null, $url = null, $params = null, $isMultipart = false)
  {
    if(empty($method) || empty($url))
      return false;

    if(empty($params['oauth_signature']))
      $params = $this->prepareParameters($method, $url, $params);

    switch($method)
    {
      case 'GET':
        return $this->httpGet($url, $params);
        break;
      case 'POST':
        return $this->httpPost($url, $params, $isMultipart);
        break;
      case 'DELETE':
        return $this->httpDelete($url, $params);
        break;

    }
  }

  public function setDebug($bool=false)
  {
    $this->debug = (bool)$bool;
  }

  public function setFollowLocation($bool)
  {
    $this->followLocation = (bool)$bool;
  }

  public function setTimeout($requestTimeout = null, $connectionTimeout = null)
  {
    if($requestTimeout !== null)
      $this->requestTimeout = floatval($requestTimeout);
    if($connectionTimeout !== null)
      $this->connectionTimeout = floatval($connectionTimeout);
  }

  public function setToken($token = null, $secret = null)
  {
    $this->token = $token;
    $this->tokenSecret = $secret;
  }

  public function setCallback($callback = null)
  {
    $this->callback = $callback;
  }

  public function useSSL($use = false)
  {
    $this->useSSL = (bool)$use;
  }

  protected function addDefaultHeaders($url, $oauthHeaders)
  {
    $_h = array('Expect:');
    $urlParts = parse_url($url);
    $oauth = 'Authorization: OAuth realm="' . $urlParts['scheme'] . '://' . $urlParts['host'] . $urlParts['path'] . '",';
    foreach($oauthHeaders as $name => $value)
    {
      $oauth .= "{$name}=\"{$value}\",";
    }
    $_h[] = substr($oauth, 0, -1);
    $_h[] = "User-Agent: {$this->userAgent}";
    $this->addHeader($_h);
  }

  protected function buildHttpQueryRaw($params)
  {
    $retval = '';
    foreach((array)$params as $key => $value)
      $retval .= "{$key}={$value}&";
    $retval = substr($retval, 0, -1);
    return $retval;
  }

  protected function curlInit($url)
  {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $this->headers); 
    curl_setopt($ch, CURLOPT_TIMEOUT, $this->requestTimeout);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $this->connectionTimeout);
    curl_setopt($ch, CURLOPT_ENCODING, '');
    if($this->followLocation)
      curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    //if(isset($_SERVER ['SERVER_ADDR']) && !empty($_SERVER['SERVER_ADDR']) && $_SERVER['SERVER_ADDR'] != '127.0.0.1')
    //  curl_setopt($ch, CURLOPT_INTERFACE, $_SERVER ['SERVER_ADDR']);

    // if the certificate exists then use it, else bypass ssl checks
    if(file_exists($cert = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'ca-bundle.crt'))
    {
      curl_setopt($ch, CURLOPT_CAINFO, $cert);
      curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
      curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
    }
    else
    {
      curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
      curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    }
    return $ch;
  }

  protected function emptyHeaders()
  {
    $this->headers = array();
  }

  protected function encode_rfc3986($string)
  {
    return str_replace('+', ' ', str_replace('%7E', '~', rawurlencode(($string))));
  }

  protected function generateNonce()
  {
    if(isset($this->nonce)) // for unit testing
      return $this->nonce;

    return md5(uniqid(rand(), true));
  }

  // parameters should already have been passed through prepareParameters
  // no need to double encode
  protected function generateSignature($method = null, $url = null, $params = null)
  {
    if(empty($method) || empty($url))
      return false;

    // concatenating and encode
    $concatenatedParams = $this->encode_rfc3986($this->buildHttpQueryRaw($params));

    // normalize url
    $normalizedUrl = $this->encode_rfc3986($this->normalizeUrl($url));
    $method = $this->encode_rfc3986($method); // don't need this but why not?

    $signatureBaseString = "{$method}&{$normalizedUrl}&{$concatenatedParams}";
    return $this->signString($signatureBaseString);
  }

  protected function executeCurl($ch)
  {
    if($this->isAsynchronous)
      return $this->curl->addCurl($ch);
    else
      return $this->curl->addEasyCurl($ch);
  }

  protected function httpDelete($url, $params) {
      $this->addDefaultHeaders($url, $params['oauth']);
      $ch = $this->curlInit($url);
      curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
      curl_setopt($ch, CURLOPT_POSTFIELDS, $this->buildHttpQueryRaw($params['request']));
      $resp = $this->executeCurl($ch);
      $this->emptyHeaders();
      return $resp;
  }

  protected function httpGet($url, $params = null)
  {
    if(count($params['request']) > 0)
    {
      $url .= '?';
      foreach($params['request'] as $k => $v)
      {
        $url .= "{$k}={$v}&";
      }
      $url = substr($url, 0, -1);
    }
    $this->addDefaultHeaders($url, $params['oauth']);
    $ch = $this->curlInit($url);
    $resp = $this->executeCurl($ch);
    $this->emptyHeaders();

    return $resp;
  }

  protected function httpPost($url, $params = null, $isMultipart)
  {
    $this->addDefaultHeaders($url, $params['oauth']);
    $ch = $this->curlInit($url);
    curl_setopt($ch, CURLOPT_POST, 1);
    // php's curl extension automatically sets the content type
    // based on whether the params are in string or array form
    if($isMultipart)
      curl_setopt($ch, CURLOPT_POSTFIELDS, $params['request']);
    else
      curl_setopt($ch, CURLOPT_POSTFIELDS, $this->buildHttpQueryRaw($params['request']));
    $resp = $this->executeCurl($ch);
    $this->emptyHeaders();

    return $resp;
  }

  protected function normalizeUrl($url = null)
  {
    $urlParts = parse_url($url);
    $scheme = strtolower($urlParts['scheme']);
    $host   = strtolower($urlParts['host']);
    $port = isset($urlParts['port']) ? intval($urlParts['port']) : 0;

    $retval = strtolower($scheme) . '://' . strtolower($host);

    if(!empty($port) && (($scheme === 'http' && $port != 80) || ($scheme === 'https' && $port != 443)))
      $retval .= ":{$port}";

    $retval .= $urlParts['path'];
    if(!empty($urlParts['query']))
    {
      $retval .= "?{$urlParts['query']}";
    }

    return $retval;
  }

  protected function isMultipart($params = null)
  {
    if($params)
    {
      foreach($params as $k => $v)
      {
        if(strncmp('@',$k,1) === 0)
          return true;
      }
    }
    return false;
  }

  protected function prepareParameters($method = null, $url = null, $params = null)
  {
    if(empty($method) || empty($url))
      return false;

    $oauth['oauth_consumer_key'] = $this->consumerKey;
    $oauth['oauth_token'] = $this->token;
    $oauth['oauth_nonce'] = $this->generateNonce();
    $oauth['oauth_timestamp'] = !isset($this->timestamp) ? time() : $this->timestamp; // for unit test
    $oauth['oauth_signature_method'] = $this->signatureMethod;
    if(isset($params['oauth_verifier']))
    {
      $oauth['oauth_verifier'] = $params['oauth_verifier'];
      unset($params['oauth_verifier']);
    }
    $oauth['oauth_version'] = $this->version;
    // encode all oauth values
    foreach($oauth as $k => $v)
      $oauth[$k] = $this->encode_rfc3986($v);
    // encode all non '@' params
    // keep sigParams for signature generation (exclude '@' params)
    // rename '@key' to 'key'
    $sigParams = array();
    $hasFile = false;
    if(is_array($params))
    {
      foreach($params as $k => $v)
      {
        if(strncmp('@',$k,1) !== 0)
        {
          $sigParams[$k] = $this->encode_rfc3986($v);
          $params[$k] = $this->encode_rfc3986($v);
        }
        else
        {
          $params[substr($k, 1)] = $v;
          unset($params[$k]);
          $hasFile = true;
        }
      }
      
      if($hasFile === true)
        $sigParams = array();
    }

    $sigParams = array_merge($oauth, (array)$sigParams);

    // sorting
    ksort($sigParams);

    // signing
    $oauth['oauth_signature'] = $this->encode_rfc3986($this->generateSignature($method, $url, $sigParams));
    return array('request' => $params, 'oauth' => $oauth);
  }

  protected function signString($string = null)
  {
    $retval = false;
    switch($this->signatureMethod)
    {
      case 'HMAC-SHA1':
        $key = $this->encode_rfc3986($this->consumerSecret) . '&' . $this->encode_rfc3986($this->tokenSecret);
        $retval = base64_encode(hash_hmac('sha1', $string, $key, true));
        break;
    }

    return $retval;
  }

  public function __construct($consumerKey, $consumerSecret, $signatureMethod='HMAC-SHA1')
  {
    $this->consumerKey = $consumerKey;
    $this->consumerSecret = $consumerSecret;
    $this->signatureMethod = $signatureMethod;
    $this->curl = EpiCurl::getInstance();
  }
}

class EpiOAuthResponse
{
  private $__resp;
  protected $debug = false;

  public function __construct($resp)
  {
    $this->__resp = $resp;
  }

  public function __get($name)
  {
    if($this->__resp->code != 200)
      EpiOAuthException::raise($this->__resp, $this->debug);

    parse_str($this->__resp->data, $result);
    foreach($result as $k => $v)
    {
      $this->$k = $v;
    }

    return isset($result[$name]) ? $result[$name] : null;
  }

  public function __toString()
  {
    return $this->__resp->data;
  }
}

class EpiOAuthException extends Exception
{
  public static function raise($response, $debug)
  {
    $message = $response->responseText;

    switch($response->code)
    {
      case 400:
        throw new EpiOAuthBadRequestException($message, $response->code);
      case 401:
        throw new EpiOAuthUnauthorizedException($message, $response->code);
      default:
        throw new EpiOAuthException($message, $response->code);
    }
  }
}
class EpiOAuthBadRequestException extends EpiOAuthException{}
class EpiOAuthUnauthorizedException extends EpiOAuthException{}


/*
 *  Class to integrate with Twitter's API.
 *    Authenticated calls are done using OAuth and require access tokens for a user.
 *    API calls which do not require authentication do not require tokens (i.e. search/trends)
 * 
 *  Full documentation available on github
 *    http://wiki.github.com/jmathai/twitter-async
 * 
 *  @author Jaisen Mathai <jaisen@jmathai.com>
 */
class EpiTwitter extends EpiOAuth
{
  const EPITWITTER_SIGNATURE_METHOD = 'HMAC-SHA1';
  const EPITWITTER_AUTH_OAUTH = 'oauth';
  const EPITWITTER_AUTH_BASIC = 'basic';
  protected $requestTokenUrl= 'https://api.twitter.com/oauth/request_token';
  protected $accessTokenUrl = 'https://api.twitter.com/oauth/access_token';
  protected $authorizeUrl   = 'https://api.twitter.com/oauth/authorize';
  protected $authenticateUrl= 'https://api.twitter.com/oauth/authenticate';
  protected $apiUrl         = 'http://api.twitter.com';
  protected $apiVersionedUrl= 'http://api.twitter.com';
  protected $searchUrl      = 'http://search.twitter.com';
  protected $userAgent      = 'EpiTwitter (http://github.com/jmathai/twitter-async/tree/)';
  protected $apiVersion     = '1';
  protected $isAsynchronous = false;

  /* OAuth methods */
  public function delete($endpoint, $params = null)
  {
    return $this->request('DELETE', $endpoint, $params);
  }

  public function get($endpoint, $params = null)
  {
    return $this->request('GET', $endpoint, $params);
  }

  public function post($endpoint, $params = null)
  {
    return $this->request('POST', $endpoint, $params);
  }

  /* Basic auth methods */
  public function delete_basic($endpoint, $params = null, $username = null, $password = null)
  {
    return $this->request_basic('DELETE', $endpoint, $params, $username, $password);
  }

  public function get_basic($endpoint, $params = null, $username = null, $password = null)
  {
    return $this->request_basic('GET', $endpoint, $params, $username, $password);
  }

  public function post_basic($endpoint, $params = null, $username = null, $password = null)
  {
    return $this->request_basic('POST', $endpoint, $params, $username, $password);
  }

  public function useApiVersion($version = null)
  {
    $this->apiVersion = $version;
  }

  public function useAsynchronous($async = true)
  {
    $this->isAsynchronous = (bool)$async;
  }

  public function __construct($consumerKey = null, $consumerSecret = null, $oauthToken = null, $oauthTokenSecret = null)
  {
    parent::__construct($consumerKey, $consumerSecret, self::EPITWITTER_SIGNATURE_METHOD);
    $this->setToken($oauthToken, $oauthTokenSecret);
  }

  public function __call($name, $params = null/*, $username, $password*/)
  {
    $parts  = explode('_', $name);
    $method = strtoupper(array_shift($parts));
    $parts  = implode('_', $parts);
    $endpoint   = '/' . preg_replace('/[A-Z]|[0-9]+/e', "'/'.strtolower('\\0')", $parts) . '.json';
    /* HACK: this is required for list support that starts with a user id */
    $endpoint = str_replace('//','/',$endpoint);
    $args = !empty($params) ? array_shift($params) : null;

    // calls which do not have a consumerKey are assumed to not require authentication
    if($this->consumerKey === null)
    {
      $username = null;
      $password = null;

      if(!empty($params))
      {
        $username = array_shift($params);
        $password = !empty($params) ? array_shift($params) : null;
      }

      return $this->request_basic($method, $endpoint, $args, $username, $password);
    }

    return $this->request($method, $endpoint, $args);
  }

  private function getApiUrl($endpoint)
  {
    if(preg_match('@^/search[./]?(?=(json|daily|current|weekly))@', $endpoint))
      return "{$this->searchUrl}{$endpoint}";
    elseif(!empty($this->apiVersion))
      return "{$this->apiVersionedUrl}/{$this->apiVersion}{$endpoint}";
    else
      return "{$this->apiUrl}{$endpoint}";
  }

  private function request($method, $endpoint, $params = null)
  {
    $url = $this->getUrl($this->getApiUrl($endpoint));
    $resp= new EpiTwitterJson(call_user_func(array($this, 'httpRequest'), $method, $url, $params, $this->isMultipart($params)), $this->debug);
    if(!$this->isAsynchronous)
      $resp->response;

    return $resp;
  }

  private function request_basic($method, $endpoint, $params = null, $username = null, $password = null)
  {
    $url = $this->getApiUrl($endpoint);
    if($method === 'GET')
      $url .= is_null($params) ? '' : '?'.http_build_query($params, '', '&');
    $ch  = curl_init($url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Expect:'));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, $this->requestTimeout);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
    if($method === 'POST' && $params !== null)
    {
      if($this->isMultipart($params))
        curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
      else
        curl_setopt($ch, CURLOPT_POSTFIELDS, $this->buildHttpQueryRaw($params));
    }
    if(!empty($username) && !empty($password))
      curl_setopt($ch, CURLOPT_USERPWD, "{$username}:{$password}");

    $resp = new EpiTwitterJson(EpiCurl::getInstance()->addCurl($ch), $this->debug);
    if(!$this->isAsynchronous)
      $resp->response;

    return $resp;
  }
}

class EpiTwitterJson implements ArrayAccess, Countable, IteratorAggregate
{
  private $debug;
  private $__resp;
  public function __construct($response, $debug = false)
  {
    $this->__resp = $response;
    $this->debug  = $debug;
  }

  // ensure that calls complete by blocking for results, NOOP if already returned
  public function __destruct()
  {
    $this->responseText;
  }

  // Implementation of the IteratorAggregate::getIterator() to support foreach ($this as $...)
  public function getIterator ()
  {
    if ($this->__obj) {
      return new ArrayIterator($this->__obj);
    } else {
      return new ArrayIterator($this->response);
    }
  }

  // Implementation of Countable::count() to support count($this)
  public function count ()
  {
    return count($this->response);
  }
  
  // Next four functions are to support ArrayAccess interface
  // 1
  public function offsetSet($offset, $value) 
  {
    $this->response[$offset] = $value;
  }

  // 2
  public function offsetExists($offset) 
  {
    return isset($this->response[$offset]);
  }
  
  // 3
  public function offsetUnset($offset) 
  {
    unset($this->response[$offset]);
  }

  // 4
  public function offsetGet($offset) 
  {
    return isset($this->response[$offset]) ? $this->response[$offset] : null;
  }

  public function __get($name)
  {
    $accessible = array('responseText'=>1,'headers'=>1,'code'=>1);
    $this->responseText = $this->__resp->data;
    $this->headers      = $this->__resp->headers;
    $this->code         = $this->__resp->code;
    if(isset($accessible[$name]) && $accessible[$name])
      return $this->$name;
    elseif(($this->code < 200 || $this->code >= 400) && !isset($accessible[$name]))
      EpiTwitterException::raise($this->__resp, $this->debug);

    // Call appears ok so we can fill in the response
    $this->response     = json_decode($this->responseText, 1);
    $this->__obj        = json_decode($this->responseText);

    if(gettype($this->__obj) === 'object')
    {
      foreach($this->__obj as $k => $v)
      {
        $this->$k = $v;
      }
    }

    if (property_exists($this, $name)) {
      return $this->$name;
    }
    return null;
  }

  public function __isset($name)
  {
    $value = self::__get($name);
    return !empty($name);
  }
}

class EpiTwitterException extends Exception 
{
  public static function raise($response, $debug)
  {
    $message = $response->data;
    switch($response->code)
    {
      case 400:
        throw new EpiTwitterBadRequestException($message, $response->code);
      case 401:
        throw new EpiTwitterNotAuthorizedException($message, $response->code);
      case 403:
        throw new EpiTwitterForbiddenException($message, $response->code);
      case 404:
        throw new EpiTwitterNotFoundException($message, $response->code);
      case 406:
        throw new EpiTwitterNotAcceptableException($message, $response->code);
      case 420:
        throw new EpiTwitterEnhanceYourCalmException($message, $response->code);
      case 500:
        throw new EpiTwitterInternalServerException($message, $response->code);
      case 502:
        throw new EpiTwitterBadGatewayException($message, $response->code);
      case 503:
        throw new EpiTwitterServiceUnavailableException($message, $response->code);
      default:
        throw new EpiTwitterException($message, $response->code);
    }
  }
}
class EpiTwitterBadRequestException extends EpiTwitterException{}
class EpiTwitterNotAuthorizedException extends EpiTwitterException{}
class EpiTwitterForbiddenException extends EpiTwitterException{}
class EpiTwitterNotFoundException extends EpiTwitterException{}
class EpiTwitterNotAcceptableException extends EpiTwitterException{}
class EpiTwitterEnhanceYourCalmException extends EpiTwitterException{}
class EpiTwitterInternalServerException extends EpiTwitterException{}
class EpiTwitterBadGatewayException extends EpiTwitterException{}
class EpiTwitterServiceUnavailableException extends EpiTwitterException{}
