<?php

/**
 * Simple PHP User Agent string parser
 */

class phpUserAgentStringParser
{
  /**
   * Parse a user agent string.
   *
   * @param   string  $userAgentString  defaults to $_SERVER['HTTP_USER_AGENT'] if empty
   * @return  array   (                 the user agent informations
   *            'browser_name'      => 'firefox',
   *            'browser_version'   => '3.6',
   *            'operating_system'  => 'linux'
   *          )
   */
  public function parse($userAgentString = null)
  {
    // use current user agent string as default
    if(!$userAgentString)
    {
      $userAgentString = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : null;
    }

    // parse quickly (with medium accuracy)
    $informations = $this->doParse($userAgentString);


    // run some filters to increase accuracy
    foreach($this->getFilters() as $filter)
    {
      $this->$filter($informations);
    }

    return $informations;
  }

  /**
   * Detect quickly informations from the user agent string
   * 
   * @param   string $userAgentString   user agent string
   * @return  array                     user agent informations array
   */
  protected function doParse($userAgentString)
  {
    $userAgent = array(
      'string'            => $this->cleanUserAgentString($userAgentString),
      'browser_name'      => null,
      'browser_version'   => null,
      'operating_system'  => null,
      'engine'            => null
    );

    if(empty($userAgent['string']))
    {
      return $userAgent;
    }

    // build regex that matches phrases for known browsers
    // (e.g. "Firefox/2.0" or "MSIE 6.0" (This only matches the major and minor
    // version numbers.  E.g. "2.0.0.6" is parsed as simply "2.0"
    $pattern = '#('.join('|', $this->getKnownBrowsers()).')[/ ]+([0-9]+(?:\.[0-9]+)?)#';

    // Find all phrases (or return empty array if none found)
    if (preg_match_all($pattern, $userAgent['string'], $matches))
    {
      // Since some UAs have more than one phrase (e.g Firefox has a Gecko phrase,
      // Opera 7,8 have a MSIE phrase), use the last one found (the right-most one
      // in the UA).  That's usually the most correct.
      $i = count($matches[1])-1;

      if (isset($matches[1][$i]))
      {
        $userAgent['browser_name'] = $matches[1][$i];
      }
      if (isset($matches[2][$i]))
      {
        $userAgent['browser_version'] = $matches[2][$i];
      }
    }

    // Find operating system
    $pattern = '#'.join('|', $this->getKnownOperatingSystems()).'#';
    
    if (preg_match($pattern, $userAgent['string'], $match))
    {
      if (isset($match[0]))
      {
        $userAgent['operating_system'] = $match[0];
      }
    }

    // Find engine
    $pattern = '#'.join('|', $this->getKnownEngines()).'#';
    
    if (preg_match($pattern, $userAgent['string'], $match))
    {
      if (isset($match[0]))
      {
        $userAgent['engine'] = $match[0];
      }
    }

    return $userAgent;
  }

  /**
   * Make user agent string lowercase, and replace browser aliases
   *
   * @param   string $userAgentString the dirty user agent string
   * @return  string                  the clean user agent string
   */
  public function cleanUserAgentString($userAgentString)
  {
    // clean up the string
    $userAgentString = trim(strtolower($userAgentString));

    // replace browser names with their aliases
    $userAgentString = strtr($userAgentString, $this->getKnownBrowserAliases());

    // replace operating system names with their aliases
    $userAgentString = strtr($userAgentString, $this->getKnownOperatingSystemAliases());

    // replace engine names with their aliases
    $userAgentString = strtr($userAgentString, $this->getKnownEngineAliases());

    return $userAgentString;
  }

  /**
   * Get the list of filters that get called when parsing a user agent
   *
   * @return  array list of valid callables
   */
  public function getFilters()
  {
    return array(
      'filterAndroid',
      'filterGoogleChrome',
      'filterSafariVersion',
      'filterOperaVersion',
      'filterYahoo',
      'filterMsie',
    );
  }

  /**
   * Add a filter to be called when parsing a user agent
   * 
   * @param   string $filter name of the filter method
   */
  public function addFilter($filter)
  {
    $this->filters += $filter;
  }

  /**
   * Get known browsers
   *
   * @return  array the browsers
   */
  protected function getKnownBrowsers()
  {
    return array(
      'msie',
      'firefox',
      'safari',
      'webkit',
      'opera',
      'netscape',
      'konqueror',
      'gecko',
      'chrome',
      'googlebot',
      'iphone',
      'msnbot',
      'applewebkit'
    );
  }

  /**
   * Get known browser aliases
   *
   * @return  array the browser aliases
   */
  protected function getKnownBrowserAliases()
  {
    return array(
      'shiretoko'     => 'firefox',
      'namoroka'      => 'firefox',
      'shredder'      => 'firefox',
      'minefield'     => 'firefox',
      'granparadiso'  => 'firefox'
    );
  }

  /**
   * Get known operating system
   *
   * @return  array the operating systems
   */
  protected function getKnownOperatingSystems()
  {
    return array(
  
	  'Windows 8',
      'Windows 7',
      'Windows Vista',
      'Windows Server 2003/XP x64',
      'Windows XP',
      'Windows XP',
      'Windows 2000',
      'Windows ME',
      'Windows 98',
      'Windows 95',
      'Windows 3.11',
      'Mac OS X',
	  'Macintosh; Intel Mac OS X',
      'Mac OS 9',	  
      'Linux',
      'Ubuntu',
      'iPhone',
      'iPod',
      'iPad',
      'Android',
      'BlackBerry',
      'Mobile'
  
    );

  }

  /**
   * Get known operating system aliases
   *
   * @return  array the operating system aliases
   */
  protected function getKnownOperatingSystemAliases()
  {
        return  array(
                            'windows nt 6.2'            =>  'Windows 8',
                            'windows nt 6.1'            =>  'Windows 7',
                            'windows nt 6.0'            =>  'Windows Vista',
                            'windows nt 5.2'            =>  'Windows Server 2003/XP x64',
                            'windows nt 5.1'            =>  'Windows XP',
                            'windows xp'                =>  'Windows XP',
                            'windows nt 5.0'            =>  'Windows 2000',
                            'windows me'                =>  'Windows ME',
                            'win98'                     =>  'Windows 98',
                            'win95'                     =>  'Windows 95',
                            'win16'                     =>  'Windows 3.11',
                            'macintosh|mac os x'        =>  'Mac OS X',
                            'macintosh; intel mac os x' =>'Macintosh; Intel Mac OS X',    												
                            'mac_powerpc'               =>  'Mac OS 9',
                            'linux'                     =>  'Linux',
                            'ubuntu'                    =>  'Ubuntu',
                            'iphone'                    =>  'iPhone',
                            'ipod'                      =>  'iPod',
                            'ipad'                      =>  'iPad',
                            'android'                   =>  'Android',
                            'blackberry'                =>  'BlackBerry',
                            'webos'                     =>  'Mobile'
                        );
  }

  /**
   * Get known engines
   *
   * @return  array the engines
   */
  protected function getKnownEngines()
  {
    return array(
      'gecko',
      'webkit',
      'trident',
      'presto'
    );
  }

  /**
   * Get known engines aliases
   *
   * @return  array the engines aliases
   */
  protected function getKnownEngineAliases()
  {
    return array();
  }

  /**
   * Filters
   */

  /**
   * Google chrome has a safari like signature
   */
  protected function filterGoogleChrome(array &$userAgent)
  {
    if ('safari' === $userAgent['browser_name'] && strpos($userAgent['string'], 'chrome/'))
    {
      $userAgent['browser_name'] = 'chrome';
      $userAgent['browser_version'] = preg_replace('|.+chrome/([0-9]+(?:\.[0-9]+)?).+|', '$1', $userAgent['string']);
    }
  }

  /**
   * Safari version is not encoded "normally"
   */
  protected function filterSafariVersion(array &$userAgent)
  {
    if ('safari' === $userAgent['browser_name'] && strpos($userAgent['string'], ' version/'))
    {
      $userAgent['browser_version'] = preg_replace('|.+\sversion/([0-9]+(?:\.[0-9]+)?).+|', '$1', $userAgent['string']);
    }
  }

  /**
   * Opera 10.00 (and higher) version number is located at the end
   */
  protected function filterOperaVersion(array &$userAgent)
  {
    if('opera' === $userAgent['browser_name'] && strpos($userAgent['string'], ' version/'))
    {
      $userAgent['browser_version'] = preg_replace('|.+\sversion/([0-9]+\.[0-9]+)\s*.*|', '$1', $userAgent['string']);
    }
  }

  /**
   * Yahoo bot has a special user agent string
   */
  protected function filterYahoo(array &$userAgent)
  {
    if (null === $userAgent['browser_name'] && strpos($userAgent['string'], 'yahoo! slurp'))
    {
      $userAgent['browser_name'] = 'yahoobot';
    }
  }

  /**
   * MSIE does not always declare its engine
   */
  protected function filterMsie(array &$userAgent)
  {
    if ('msie' === $userAgent['browser_name'] && empty($userAgent['engine']))
    {
      $userAgent['engine'] = 'trident';
    }
  }

    /**
     * Android has a safari like signature
     */
    protected function filterAndroid(array &$userAgent) {
        if ('safari' === $userAgent['browser_name'] && strpos($userAgent['string'], 'android ')) {
            $userAgent['browser_name'] = 'android';
            $userAgent['operating_system'] = 'android';
            $userAgent['browser_version'] = preg_replace('|.+android ([0-9]+(?:\.[0-9]+)+).+|', '$1', $userAgent['string']);
        }
    }
}
