<?php
/**
 * connector.php
 *
 * LICENSE: THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 *
 * @author David Wilcock <dave.wilcock@gmail.com> - original file author. See https://github.com/10REM/php-garmin-connect
 * @author Karen Attfield <karenlattfield@gmail.com> - modifications and stripped down file
 * @package
 */


class Connector
{
   /**
    * @var null|resource
    */
    private $objCurl = null;
    private $arrCurlInfo = array();
    private $strCookieDirectory = '';

   /**
    * @var array
    */
    private $arrCurlOptions = array(
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_SSL_VERIFYHOST => false,
      CURLOPT_SSL_VERIFYPEER => false,
      CURLOPT_COOKIESESSION => false,
      CURLOPT_AUTOREFERER => true,
      CURLOPT_VERBOSE => false,
      CURLOPT_FRESH_CONNECT => true,
      CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:61.0) Gecko/20100101 Firefox/61.0',
      CURLOPT_ENCODING => 'gzip',
    );

	private $arrCookieValues = array(); // Define the property to store cookies

    private $strCookieOptionKey = 'garmin_connector_cookies'; // Replace with your desired key

   /**
    * @var int
    */
    private $intLastResponseCode = -1;

   /**
    * @var string
    */
    private $strCookieFile = '';

   /**
    * @param string $strUniqueIdentifier
    * @throws Exception
    */
    public function __construct($strUniqueIdentifier)
    {
         //$this->strCookieDirectory = sys_get_temp_dir();
        // if (strlen(trim($strUniqueIdentifier)) == 0) {
        //     throw new Exception("Identifier isn't valid");
        // }
        // $this->strCookieFile = $this->strCookieDirectory . DIRECTORY_SEPARATOR . "GarminCookie_" . $strUniqueIdentifier;
        // $this->refreshSession();
		//$this->arrCookieValues = $this->loadCookies(); // Load cookies from storage

		if (strlen(trim($strUniqueIdentifier)) == 0) {
			throw new Exception("Identifier isn't valid");
		}
	
		$this->strCookieOptionKey = "garmin_cookie_" . $strUniqueIdentifier;
		$this->refreshSession();
    }

	private function loadCookies() {
        $cookie_json = get_option($this->strCookieOptionKey);
        return !empty($cookie_json) ? json_decode($cookie_json, true) : array();
    }

   /**
    * Create a new curl instance
    */
    public function refreshSession()
    {
        // $this->objCurl = curl_init();
        // $this->arrCurlOptions[CURLOPT_COOKIEJAR] = $this->strCookieFile;
        // $this->arrCurlOptions[CURLOPT_COOKIEFILE] = $this->strCookieFile;
        // curl_setopt_array($this->objCurl, $this->arrCurlOptions);
		//$saved_cookies = get_option($this->strCookieOptionKey);

		//$this->arrCookieValues = !empty($saved_cookies) ? json_decode($saved_cookies, true) : array();
	}

   /**
    * @param string $strUrl
    * @param array $arrParams
    * @param bool $bolAllowRedirects
    * @return mixed
    */
    // public function get($strUrl, $arrParams = array(), $bolAllowRedirects = true)
    // {
    //     if (null !== $arrParams && count($arrParams)) {
    //         $strUrl .= '?' . http_build_query($arrParams);
    //     }

	// 	print_r($arrParams);
	// 	print_r('ola');
    //     curl_setopt($this->objCurl, CURLOPT_HTTPHEADER, array(
    //         'NK: NT'
    //     ));
    //     curl_setopt($this->objCurl, CURLOPT_URL, $strUrl);
    //     curl_setopt($this->objCurl, CURLOPT_FOLLOWLOCATION, (bool)$bolAllowRedirects);
    //     curl_setopt($this->objCurl, CURLOPT_CUSTOMREQUEST, 'GET');

    //     $strResponse = curl_exec($this->objCurl);
    //     $arrCurlInfo = curl_getinfo($this->objCurl);
	// 	print_r($arrCurlInfo);
    //     $this->intLastResponseCode = $arrCurlInfo['http_code'];
	// 	print_r('here we go');
	// 	echo 'Response:<pre>';

	// 	print_r($arrCurlInfo);
	// 	echo '</pre>';

    //     return $strResponse;
    // }

	//add_action('the_content','get');


	// public function __set_curl_to_follow( &$handle ) {
	// 	curl_setopt( $handle, CURLOPT_FOLLOWLOCATION, true );  
	// }
	

	public function get($strUrl, $arrParams = array(), $bolAllowRedirects = true)
	{
		if (!empty($arrParams)) {
			$strUrl .= '?' . http_build_query($arrParams);
		}
	

		$nonce = wp_create_nonce( 'my-action' );
foreach ( $_COOKIE as $name => $value ) {
    $cookies[] = "$name=" . urlencode( is_array( $value ) ? serialize( $value ) : $value );
}

// if ($bolAllowRedirects) {
// 	add_action( 'http_api_curl', '__set_curl_to_follow', $handle );

// }
$request_headers = array(
	'NK' => 'NT',
	//'Cookie' => http_build_query($this->arrCookieValues, '', '; ')
	//'User-Agent' => 'GarminConnector'
	//'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:61.0) Gecko/20100101 Firefox/61.0',
//	'cookie' => implode( '; ', $cookies ),
);

		// Disable SSL verification
//add_filter('https_ssl_verify', '__return_false');
//add_filter('https_local_ssl_verify', '__return_false');
	
		$response = wp_remote_get($strUrl, array(
			'headers' => $request_headers,
			//'NK' => 'NT',
		//	'user-agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:61.0) Gecko/20100101 Firefox/61.0',
		//	'user-agent' => 'curl/7.47.0',
			'user-agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/99.0.4844.83 Safari/537.36',

			'timeout' => 10,
			'redirection' => $bolAllowRedirects ? 5 : 0,
			'httpversion' => '1.0',
			'blocking' => true,
			'sslverify' => false,
			'body'        => [
		//		'my_nonce' => $nonce
			],

			//'user-agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:61.0) Gecko/20100101 Firefox/61.0',
		//	'user-agent' => 'GarminConnector',
			//'cookies' => array(), //$this->arrCookieValues, // Include cookies here		));

			// 'referer' => $strReferer,  // Referer might not be needed in this context
		));


// After the request, re-enable SSL verification
//remove_filter('https_ssl_verify', '__return_false');
//remove_filter('https_local_ssl_verify', '__return_false');

		if (is_wp_error($response)) {
			// Handle error
			echo 'Ooopsies';
		} else {
			// Get the final response code, headers, and body
			$response_code = wp_remote_retrieve_response_code($response);
			$response_headers = wp_remote_retrieve_headers($response);
			$response_body = wp_remote_retrieve_body($response);
			print_r('<pre>');
print_r($response_headers);
			print_r('</pre>');


			// Process the response as needed
				// Update and store cookies from the response
			// $this->arrCookieValues = array_merge($this->arrCookieValues, wp_remote_retrieve_cookies($response));
			// update_option($this->strCookieOptionKey, json_encode($this->arrCookieValues));
		
			// $this->storeCookies(); // Store cookies after updating

			$this->intLastResponseCode = $response_code;

			return $response_body;
		}

	
	}

    /**
     * @param string $strUrl
     * @param array $arrParams
     * @param array $arrData
     * @param bool $bolAllowRedirects
     * @param string|null $strReferer
     * @param array $headers
     * @param string|null $rawPayload
     * @return mixed
     */
    public function post($strUrl, $arrParams = array(), $arrData = array(), $bolAllowRedirects = true, $strReferer = null, $headers = array(), $rawPayload = null)
    {

		// if ($bolAllowRedirects) {
		// 	add_action( 'http_api_curl', '__set_curl_to_follow', $handle );
		
		// }
		if (!empty($arrParams)) {
			$strUrl .= '?' . http_build_query($arrParams);
		}

		$request_headers = array();
    
		if (!empty($headers)) {
			foreach ($headers as $key => $value) {
				$request_headers[$key] = $value;
			}
		}
	
		if (!empty($rawPayload)) {
			$request_headers['Content-Type'] = 'application/x-www-form-urlencoded';
			$request_headers['Cookie'] = http_build_query($this->arrCookieValues, '', '; ');
		}

		// $headers[] = "Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8";
		// $headers[] = "Cache-Control: no-cache";
		// $headers[] = "Connection: keep-alive";
		// $headers[] = "Keep-Alive: 300";
		// $headers[] = "Accept-Charset: ISO-8859-1,utf-8;q=0.7,*;q=0.7";
		// $headers[] = "Accept-Language: en-us,en;q=0.5";
		// $headers[] = "Pragma: no-cache"; 



		// $response = wp_remote_post($strUrl, array(
		// 	'headers' => $request_headers,
		// 	'body' => $arrData,
		// 	'timeout' => 45,
		// 	'redirection' => $bolAllowRedirects ? 5 : 0,
		// 	'httpversion' => '1.0',
		// 	'blocking' => true,
		// 	'sslverify' => false,
		// 	//'user-agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:61.0) Gecko/20100101 Firefox/61.0',
		// 	'user-agent' => 'GarminConnector',
		// 	'referer' => $strReferer,
		// ));
		$nonce = wp_create_nonce( 'my-action' );
foreach ( $_COOKIE as $name => $value ) {
    $cookies[] = "$name=" . urlencode( is_array( $value ) ? serialize( $value ) : $value );
}
$arrData['my-nonce'] =  $nonce;


		// Disable SSL verification
//add_filter('https_ssl_verify', '__return_false');
//add_filter('https_local_ssl_verify', '__return_false');
		$response = wp_remote_post( $strUrl, array(
			//'method' => 'POST',
			'timeout' => 30,
			'redirection' => 5,
			'httpversion' => '1.0',
			'blocking' => false,
			'sslverify' => false,
			//'user-agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:61.0) Gecko/20100101 Firefox/61.0',
		//	'user-agent' => 'curl/7.47.0',
		'user-agent' => 'Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:89.0) Gecko/20100101 Firefox/89.0',
		//'user-agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/109.0.0.0 Safari/537.36 ',
		//'user-agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/99.0.4844.83 Safari/537.36',
		//'user-agent' => 'Mozilla/5.0 (iPad; CPU OS 12_2 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Mobile/15E148',
		//'Connection' => 'keep-alive',
		//'Cache-Control' => 'max-age=0',
		// 'content-type' => 'application/x-www-form-urlencoded', // Content-Type header if needed
			'headers' => array(
				//'user-agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:61.0) Gecko/20100101 Firefox/61.0',
			//	'cookie' => implode( '; ', $cookies ),
			//    'cookie' => '__cfruid=e7f40231e2946a1a645f6fa0eb19af969527087e-1624781498; _gcl_au=1.1.279416294.1624782732; _gid=GA1.2.518227313.1624782732; _scid=64860a19-28e4-4e83-9f65-252b26c70796; _fbp=fb.1.1624782732733.795190273; __adal_ca=so%3Ddirect%26me%3Dnone%26ca%3Ddirect%26co%3D%28not%2520set%29%26ke%3D%28not%2520set%29; __adal_cw=1624782733150; _sctr=1|1624732200000; _gaexp=GAX1.2.eSuc0QBTRhKbpaD4vT_-oA.18880.x331; _hjTLDTest=1; _hjid=bb69919f-e61b-4a94-a03b-db7b1f4ec4e4; hp_preferences=%7B%22locale%22%3A%22en-gb%22%7D; funnelFromId=38; eToroLocale=en-gb; G_ENABLED_IDPS=google; marketing_visitor_regulation_id=10; marketing_visitor_country=96; __cflb=0KaS4BfEHptJdJv5nwPFxhdSsqV6GxaSK8BuVNBmVkuj6hYxsLDisSwNTSmCwpbFxkL3LDuPyToV1fUsaeNLoSNtWLVGmBErMgEeYAyzW4uVUEoJHMzTirQMGVAqNKRnL; __cf_bm=6ef9d6f250ee71d99f439672839b52ac168f7c89-1624785170-1800-ASu4E7yXfb+ci0NsW8VuCgeJiCE72Jm9uD7KkGJdy1XyNwmPvvg388mcSP+hTCYUJvtdLyY2Vl/ekoQMAkXDATn0gyFR0LbMLl0b7sCd1Fz/Uwb3TlvfpswY1pv2NvCdqJBy5sYzSznxEsZkLznM+IGjMbvSzQffBIg6k3LDbNGPjWwv7jWq/EbDd++xriLziA==; _uetsid=2ba841e0d72211eb9b5cc3bdcf56041f; _uetvid=2babee20d72211eb97efddb582c3c625; _ga=GA1.2.1277719802.1624782732; _gat_UA-2056847-65=1; __adal_ses=*; __adal_id=47f4f887-c22b-4ce0-8298-37d6a0630bdd.1624782733.2.1624785174.1624782818.770dd6b7-1517-45c9-9554-fc8d210f1d7a; _gat=1; TS01047baf=01d53e5818a8d6dc983e2c3d0e6ada224b4742910600ba921ea33920c60ab80b88c8c57ec50101b4aeeb020479ccfac6c3c567431f; outbrain_cid_fetch=true; _ga_B0NS054E7V=GS1.1.1624785164.2.1.1624785189.35; TMIS2=9a74f8b353780f2fbe59d8dc1d9cd901437be0b823f8ee60d0ab36264e2503993c5e999eaf455068baf761d067e3a4cf92d9327aaa1db627113c6c3ae3b39cd5e8ea5ce755fb8858d673749c5c919fe250d6297ac50c5b7f738927b62732627c5171a8d3a86cdc883c43ce0e24df35f8fe9b6f60a5c9148f0a762e765c11d99d; mp_dbbd7bd9566da85f012f7ca5d8c6c944_mixpanel=%7B%22distinct_id%22%3A%20%2217a4c99388faa1-0317c936b045a4-34647600-13c680-17a4c993890d70%22%2C%22%24device_id%22%3A%20%2217a4c99388faa1-0317c936b045a4-34647600-13c680-17a4c993890d70%22%2C%22%24initial_referrer%22%3A%20%22%24direct%22%2C%22%24initial_referring_domain%22%3A%20%22%24direct%22%7D',
				'content-type' => 'application/x-www-form-urlencoded\r\n',
				// 'content-type' => 'application/json',
			),
			'body' => $arrData, //json_encode($arrData),
			//'cookies' => array(), //$this->arrCookieValues, // Include cookies here		));
		));


// After the request, re-enable SSL verification
//remove_filter('https_ssl_verify', '__return_false');
//remove_filter('https_local_ssl_verify', '__return_false');

		if (is_wp_error($response)) {
			// Handle error
			echo 'Ooops';
		} else {
			// Get the final response code, headers, and body
			$response_code = wp_remote_retrieve_response_code($response);
			$response_headers = wp_remote_retrieve_headers($response);
			$response_body = wp_remote_retrieve_body($response);
		
			// Update and store cookies from the response
			// $this->arrCookieValues = array_merge($this->arrCookieValues, wp_remote_retrieve_cookies($response));
			// update_option($this->strCookieOptionKey, json_encode($this->arrCookieValues));
			// 	$this->storeCookies(); // Store cookies after updating
			// 	$received_cookies = wp_remote_retrieve_cookies( $response );
print_r($response_code);
print_r('code is above');
			$this->intLastResponseCode = $response_code;

			return $response_body;
		}



	}

	private function storeCookies() {
        update_option($this->strCookieOptionKey, json_encode($this->arrCookieValues));
    }

	// public function post($strUrl, $arrParams = array(), $arrData = array(), $bolAllowRedirects = true, $strReferer = null, $headers = array(), $rawPayload = null)
    // {
    //     if (empty($headers)) {
    //         $headers[] = 'Content-Type: application/x-www-form-urlencoded';
    //     }

    //     if (!empty($rawPayload)) {
    //         curl_setopt($this->objCurl, CURLOPT_POSTFIELDS, $rawPayload);
    //         $headers[] = 'Content-Length: ' . strlen($rawPayload);
    //     }

    //     if ($arrData !== null && count($arrData)) {
    //         curl_setopt($this->objCurl, CURLOPT_POSTFIELDS, http_build_query($arrData));
    //     }

    //     if (null !== $strReferer) {
    //         curl_setopt($this->objCurl, CURLOPT_REFERER, $strReferer);
    //     }

    //     if (! empty($arrParams)) {
    //         $strUrl .= '?' . http_build_query($arrParams);
    //     }
    //     curl_setopt($this->objCurl, CURLOPT_HTTPHEADER, $headers);
    //     curl_setopt($this->objCurl, CURLOPT_HEADER, false);
    //     curl_setopt($this->objCurl, CURLOPT_FRESH_CONNECT, true);
    //     curl_setopt($this->objCurl, CURLOPT_FOLLOWLOCATION, (bool)$bolAllowRedirects);
    //     curl_setopt($this->objCurl, CURLOPT_CUSTOMREQUEST, "POST");
    //     curl_setopt($this->objCurl, CURLOPT_VERBOSE, false);

    //     curl_setopt($this->objCurl, CURLOPT_URL, $strUrl);

    //     $strResponse = curl_exec($this->objCurl);
    //     $this->arrCurlInfo = curl_getinfo($this->objCurl);
    //     $this->intLastResponseCode = (int)$this->arrCurlInfo['http_code'];
	// 	print_r('are we posting?');
    //     return $strResponse;
    // }

   /**
    * @return array
    */
    public function getCurlInfo()
    {
        return $this->arrCurlInfo;
    }

   /**
    * @return int
    */
    public function getLastResponseCode()
    {
        return $this->intLastResponseCode;
    }

//    /**
//     * Removes the cookie
//     */
//     public function clearCookie()
//     {
//         if (file_exists($this->strCookieFile)) {
//             unlink($this->strCookieFile);
//         }
//     }

  /**
     * Clears stored cookies from options
     */
    public function clearStoredCookies()
    {
        delete_option($this->strCookieOptionKey);
    }

//    /**
//     * Closes curl and then clears the cookie.
//     */
//     public function cleanupSession()
//     {
//         curl_close($this->objCurl);
//         $this->clearCookie();
//     }
}