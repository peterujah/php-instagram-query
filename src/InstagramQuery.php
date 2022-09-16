<?php 
/**
 * OBCompress - Reusable php Functions
 * @author      Peter Chigozie(NG) peterujah
 * @copyright   Copyright (c), 2021 Peter(NG) peterujah
 * @license     MIT public license
 */
namespace Peterujah\NanoBlock;
use \Serps\Core\Browser\Browser;
use \Serps\HttpClient\CurlClient;
use \Serps\Core\Url;
class InstagramQuery{
    /** 
     * Holds the default user agent string
     * @var string $userAgent
    */
    protected static $userAgent = "Mozilla/5.0 (Windows NT 10.0) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/40.0.2214.93 Safari/537.36";
    
    /** 
     * Holds the browser instance
     * @var Browser $browser
    */
    protected $browser = null;

    /** 
     * Holds the instagram website url
     * @var string $instagram
    */
    protected static $instagram = "https://www.instagram.com/";

    /** 
     * Holds the regex options
     * @var array $regex
    */
    protected static $regex = array(
        "picture" => '/"profile_pic_url":"(.*?)"/', //'/"profile_pic_url":"(.+?)"/';
        "page" => '/"page_id":"(.*?)"/' ,
        "profile" => '/"profile_id":"(.*?)"/',
        "name" => '/"title":"(.*?)"/' 
    );
    
    /**
	* Class constructor
    * @param string $browserLanguage browser language
    * @param string $userAgent browser user-agent string
	*/
    public function __construct($browserLanguage = "en-US", $userAgent = null) {
        $this->browser = new Browser(
            new CurlClient(),
            (!empty($userAgent) ? $userAgent : self::$userAgent),
            $browserLanguage
        );
    }

    /** 
	* Extracts json from script tags
	* @param string $dom instagram html dom element
	* @return mixed|array|null
	*/
    private function extractQuery($dom){
        $document = new \DOMDocument();
        $document->loadHTML($dom);
        $xpath = new \DOMXPath($document);
        return $xpath->query("//body/script");
    }

     /** 
	* Extracts profile picture url
	* @param string $username user instagram profile username
	* @return object|null
	*/
    public function findProfilePic(string $username){
        if(empty($username)){
            return new stdClass();
        }
        return $this->find($username, "picture");
    }

    /** 
	* Extracts profile page id
	* @param string $username user instagram profile username
	* @return object|null
	*/
    public function findPageId(string $username){
        if(empty($username)){
            return new stdClass();
        }
        return $this->findArray($username, "page");
    }

    /** 
	* Extracts profile id
	* @param string $username user instagram profile username
	* @return object|null
	*/
    public function findProfileId(string $username){
        if(empty($username)){
            return new \stdClass();
        }
        return $this->find($username, "profile");
    }

    /** 
	* Extracts profile name
	* @param string $username user instagram profile username
	* @return object|null
	*/
    public function findProfileName(string $username){
        if(empty($username)){
            return new \stdClass();
        }
        return $this->find($username, "name");
    }

    /** 
	* Extracts item
	* @param string $username user instagram profile username
    * @param string $path extraction path 
	* @return object|null
	*/
    public function find(string $username, string $path){
        $responseObject = (object)[];
        if(empty($username)){
            return $responseObject;
        }
        foreach ($this->open($username) as $element) {
            if($element->nodeName == "script"){
                foreach ($element->childNodes as $node) {
                    if (preg_match(self::$regex[$path], $node->nodeValue, $match)){
                        return (object) array(
                            $path => $this->formatMatch($match[1], $path),
                            "matches" => $match
                        );
                    }
                }
            }
        }
        return $responseObject;
    }

    /** 
	* Extracts list of array object
	* @param string $username user instagram profile username
    * @param string $path extraction path 
	* @return object|null
	*/
    public function findArray(string $username, string $path){
        $responseObject = array();
        if(empty($username)){
            return (object) $responseObject;
        }
        foreach ($this->open($username) as $element) {
            if($element->nodeName == "script"){
                foreach ($element->childNodes as $node) {
                    if (preg_match(self::$regex[$path], $node->nodeValue, $match)){
                        $responseObject[] = (object) array(
                            $path => $this->formatMatch($match[1], $path),
                            "matches" => $match
                        );
                    }
                }
            }
        }
        return (object) $responseObject;
    }

    /** 
	* clean string by type
	* @param string $string 
    * @param string $path extraction path 
	* @return string
	*/
    private function formatMatch(string $string, string $path){
        switch($path){
            case "picture":
                return str_replace('\/', '/', $string);
            case "name":
                return explode(" (", $string)[0];
            default:
                return $string;
        }
    }

    /** 
	* Run instagram in browser
	* @param string $username user instagram profile username
	* @return mixed|array|null response from instagram
	*/
    public function open(string $username){
        if( $this->browser == null){
            return null;
        }
        if($response = $this->browser->navigateToUrl(Url::fromString(self::$instagram . "{$username}/"))){
            if($response->getHttpResponseStatus() == 200){
                return $this->extractQuery($response->getPageContent());
            }
        }
        return null;
    }
}