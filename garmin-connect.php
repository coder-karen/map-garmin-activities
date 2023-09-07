<?php
/**
 * garmin-connect.php
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
 * @author Karen Attfield <karenlattfield@gmail.com> - stripped down file and minor modifications
 * @package
 */


require_once('garmin-connect/connector.php');
require 'garmin-connect/exceptions/unexpected-response-code-exception.php';

class Garmin_Connect
{

    /**
     * @var string
     */
    private $strUsername = '';

    /**
     * @var string
     */
    private $strPassword = '';

    /**
     * @var Garmin_Connect\Connector|null
     */
    private $objConnector = null;

    /**
     * Performs some essential setup
     *
     * @param array $arrCredentials
     * @throws Exception
     */
    public function __construct(array $arrCredentials = array())
    {
        if (!isset($arrCredentials['username'])) {
            throw new Exception("Username credential missing");
        }

        $this->strUsername = $arrCredentials['username'];
        unset($arrCredentials['username']);

        $intIdentifier = md5($this->strUsername);

        $this->objConnector = new \Connector($intIdentifier);

       // If we can validate the cached auth, we don't need to do anything else
        if ($this->checkCookieAuth()) {
            return;
        }

        if (!isset($arrCredentials['password'])) {
            throw new Exception("Password credential missing");
        }

        $this->strPassword = $arrCredentials['password'];
        unset($arrCredentials['password']);
        $this->authorize($this->strUsername, $this->strPassword);
    }

    /**
     * Try to read the username from the API - if successful, it means we have a valid cookie, and we don't need to auth
     *
     * @return bool
     * @throws Unexpected_Response_Code_Exception
     */
    private function checkCookieAuth()
    {
        if (strlen(trim($this->getUsername())) == 0) {
            $this->objConnector->cleanupSession();
            $this->objConnector->refreshSession();
            return false;
        }
        return true;
    }

    /**
     * Because there doesn't appear to be a nice "API" way to authenticate with Garmin Connect, we have to effectively spoof
     * a browser session using some pretty high-level scraping techniques. The connector object does all of the HTTP
     * work, and is effectively a wrapper for CURL-based session handler (via CURLs in-built cookie storage).
     *
     * @param string $strUsername
     * @param string $strPassword
     * @throws Authentication_Exception
     * @throws Unexpected_Response_Code_Exception
     */
    private function authorize($strUsername, $strPassword)
    {
        $arrParams = array(
            'service' => 'https://connect.garmin.com/modern/',
            'webhost' => 'https://connect.garmin.com',
            'source' => 'https://connect.garmin.com/en-UK/signin',
            'clientId' => 'GarminConnect',
            'gauthHost' => 'https://sso.garmin.com/sso',
            'consumeServiceTicket' => 'false'
        );
        $strResponse = $this->objConnector->get("https://sso.garmin.com/sso/login", $arrParams, true);
        $strSigninUrl = "https://sso.garmin.com/sso/login?" . http_build_query($arrParams);

        if ($this->objConnector->getLastResponseCode() != 200) {
            throw new Exception(sprintf(
                "SSO prestart error (code: %d, message: %s)",
                $this->objConnector->getLastResponseCode(),
                $strResponse
            ));
        }

        preg_match("/name=\"_csrf\" value=\"(.*)\"/", $strResponse, $arrCsrfMatches);

        if (!isset($arrCsrfMatches[1])) {
            throw new Exception("Unable to find CSRF input in login form");
        }

        $arrData = array(
            "username" => $strUsername,
            "password" => $strPassword,
            "_eventId" => "submit",
            "embed" => "true",
            "displayNameRequired" => "false",
            "_csrf" => $arrCsrfMatches[1],
        );

        $strResponse = $this->objConnector->post("https://sso.garmin.com/sso/login", $arrParams, $arrData, true, $strSigninUrl);


        preg_match("/ticket=([^\"]+)\"/", $strResponse, $arrMatches);
        if (!isset($arrMatches[1])) {
            $strMessage = "Garmin authentication failed - please check your credentials";

			preg_match("/\blocked\b/", $strResponse, $arrLocked);

            if (isset($arrLocked[0])) {
                $strMessage = "Authentication failed, and it looks like your account has been locked. Please access https://connect.garmin.com to unlock";
            }

            $this->objConnector->cleanupSession();
            throw new Exception($strMessage);
        }

        $strTicket = rtrim($arrMatches[0], '"');
		print_r($arrMatches);
		print_r($arrMatches[0]);

        $arrParams = array(
            'ticket' => $strTicket
        );

        $this->objConnector->post('https://connect.garmin.com/modern/', $arrParams, null, false);
        if ($this->objConnector->getLastResponseCode() != 302) {
            throw new Exception($this->objConnector->getLastResponseCode());
        }

        // should only exist if the above response WAS a 302 ;)
        $arrCurlInfo = $this->objConnector->getCurlInfo();
        $strRedirectUrl = $arrCurlInfo['redirect_url'];

        $this->objConnector->get($strRedirectUrl, null, true);
        if (!in_array($this->objConnector->getLastResponseCode(), array(200, 302))) {
            throw new Exception($this->objConnector->getLastResponseCode());
        }

        // Fires up a fresh CuRL instance, because of our reliance on Cookies requiring "a new page load" as it were ...
        $this->objConnector->refreshSession();
    }


	/**
     * Gets a list of activities within a date range
     *
     * @param string $startDate
     * @param string $endDate
     * @param string $strActivityType
     * @return mixed
     * @throws Exception
     */
    public function getActivityListByDate($startDate = '', $endDate = '', $strActivityType = null)
    {

		$arrParams = array();
		if ('' !== $startDate) {
            $arrParams['startDate'] = $startDate;
        }
		else {
			return;
		}
		if ('' !== $endDate) {
			$arrParams['endDate'] = $endDate;
		}

        if (null !== $strActivityType) {
            $arrParams['activityType'] = $strActivityType;
        }

        $strResponse = $this->objConnector->get(
            'https://connect.garmin.com/modern/proxy/activitylist-service/activities/search/activities',
            $arrParams
        );

        if ($this->objConnector->getLastResponseCode() != 200) {
            throw new Exception($this->objConnector->getLastResponseCode());
        }
        $objResponse = json_decode($strResponse);
        return $objResponse;
    }

	/**
     * @return mixed
     * @throws Unexpected_Response_Code_Exception
     */
    public function getUser()
    {
        $strResponse = $this->objConnector->get('https://connect.garmin.com/modern/currentuser-service/user/info');
        if ($this->objConnector->getLastResponseCode() != 200) {
            throw new MapGarminActivities\Garmin_Connect\exceptions\Unexpected_Response_Code_Exception($this->objConnector->getLastResponseCode());
        }

        $objResponse = json_decode($strResponse);
        return $objResponse;
    }

	/**
     * @return mixed
     * @throws Unexpected_Response_Code_Exception
     */
    public function getUsername()
    {
        $objUser = $this->getUser();
        if (!$objUser) {
            return null;
        }
        return $objUser->username;
    }

}