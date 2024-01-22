<?php 
/**
 * OBCompress - Reusable php Functions
 * @author      Peter Chigozie(NG) peterujah
 * @copyright   Copyright (c), 2021 Peter(NG) peterujah
 * @license     MIT public license
 */
namespace Peterujah\NanoBlock;
use \Peterujah\NanoBlock\userAgent;
use \Serps\Core\Browser\Browser;
use \Serps\HttpClient\CurlClient;
use \Serps\Core\Url;
use \DOMDocument;
use \DOMXPath;
use \stdClass;

class InstagramQuery{

    /** 
     * Holds the browser instance
     * @var Browser $browser
    */
    private $browser = null;

    /** 
     * Holds the instagram website url
     * @var string $instagram
    */
    private static $instagram = "https://www.instagram.com/";

    /** 
     * Holds the instagram meta description
     * @var string $metadata
    */
    private static $metadata = '//head/meta[@name="description"]';


    /** 
     * Holds the regex options
     * @var array $regex
    */
    private static $regex = [
        "picture" => '/"profile_pic_url":"(.*?)"/',
        "page" => '/"page_id":"(.*?)"/' ,
        "profile" => '/"profile_id":"(.*?)"/',
        "name" => '/"title":"(.*?)"/',
        "followers" => '/(\d+) Followers/',
        "following" => '/(\d+) Following/',
        "posts" => '/(\d+) Posts/'
    ];

    /** 
     * Holds allowed operating systems
     * @var array $oss
    */
    private static $oss = [ 'chrome', 'firefox', 'explorer', 'iphone', 'android', 'mobile', 'windows', 'mac', 'linux' ];
    
    /**
	* Class constructor
    * @param string $language browser language
    * @param string $os browser operating system for user-agent string
	*/
    public function __construct(string $language = "en-US", string $os = '') {
        $os = (in_array($os, self::$oss) ? $os : '');
        $userAgent = new userAgent($os);
        $this->browser = new Browser(
            new CurlClient(),
            $userAgent->generate(),
            $language
        );
    }

    /** 
	* Extracts profile picture url
	* @param string $username user instagram profile username
    *
	* @return object|null
	*/
    public function findProfilePic(string $username): object 
    {
        if(empty($username)){
            return new stdClass();
        }
        return $this->find($username, "picture");
    }

    /** 
	* Extracts profile page id
	* @param string $username user instagram profile username
    *
	* @return object|null
	*/
    public function findPageId(string $username): object 
    {
        if(empty($username)){
            return new stdClass();
        }
        return $this->findArray($username, "page");
    }

    /** 
	* Extracts profile id
	* @param string $username user instagram profile username
    *
	* @return object
	*/
    public function findProfileId(string $username): object 
    {
        if(empty($username)){
            return new stdClass();
        }
        return $this->find($username, "profile");
    }

    /** 
	* Extracts profile name
	* @param string $username user instagram profile username
    *
	* @return object
	*/
    public function findProfileName(string $username): object 
    {
        if(empty($username)){
            return new stdClass();
        }
        return $this->find($username, "name");
    }

    /** 
	* Extracts profile infos
	* @param string $username user instagram profile username
    *
	* @return object
	*/
    public function findInfos(string $username): object 
    {
        if(empty($username)){
            return new stdClass();
        }
        return $this->find($username, '', self::$metadata);
    }

    /** 
	* Extracts profile followers
	* @param string $username user instagram profile username
    *
	* @return object
	*/
    public function findFollowers(string $username): object 
    {
        if(empty($username)){
            return new stdClass();
        }
        return $this->find($username, "followers", self::$metadata);
    }

    /** 
	* Extracts profile followings
	* @param string $username user instagram profile username
    *
	* @return object
	*/
    public function findFollowing(string $username): object
    {
        if(empty($username)){
            return new stdClass();
        }
        return $this->find($username, "following", self::$metadata);
    }

    /** 
	* Extracts profile posts
	* @param string $username user instagram profile username
    *
	* @return object
	*/
    public function findPosts(string $username): object
    {
        if(empty($username)){
            return new stdClass();
        }
        return $this->find($username, "posts", self::$metadata);
    }

    /** 
	* Extracts item
    *
	* @param string $username user instagram profile username
    * @param string $path extraction path 
    * @param string $pattern pattern to look for in document html 
    *
	* @return object|null
	*/
    public function find(string $username, string $path, string $pattern = '//body/script'){
        $result = (object)[];
        if(empty($username)){
            return $result;
        }
        $contents = $this->open($username, $pattern);
        if($contents === null){
            return $result;
        }
       
        foreach ($contents as $element) {
            if($element->nodeName === "script" || $element->nodeName === "meta"){
                if($element->nodeName === "meta"){
                    $content = $element->getAttribute('content');
                    if($path === ''){
                        $infos = [];
                        $infos[] = $this->extractMatches('followers', $content);
                        $infos[] = $this->extractMatches('following', $content);
                        $infos[] = $this->extractMatches('posts', $content);
                        return (object) $infos;
                    }
                    
                    return $this->extractMatches($path, $content);
                }

                foreach ($element->childNodes as $node) {
                    return $this->extractMatches($path, $node->nodeValue);
                }
            }
        }

        return $result;
    }

    /** 
	* Extracts list of array object
    *
	* @param string $username user instagram profile username
    * @param string $path extraction path 
    * @param string $pattern pattern to look for in document html 
    *
	* @return object
	*/
    public function findArray(string $username, string $path, string $pattern = '//body/script'): object
    {
        $results = [];
        if($username === ''){
            return (object) $results;
        }

        $contents = $this->open($username, $pattern);
        if($contents === null){
           return (object) $results;
        }

        foreach ($contents as $element) {
            if($element->nodeName === "script"  || $element->nodeName === "meta"){
                if($element->nodeName === "meta"){
                    $content = $element->getAttribute('content');
                    if($path === ''){
                        $results[] = $this->extractMatches('followers', $content);
                        $results[] = $this->extractMatches('following', $content);
                        $results[] = $this->extractMatches('posts', $content);
                    }else{
                        $results[] = $this->extractMatches($path, $content);
                    }
                }

                foreach ($element->childNodes as $node) {
                    $results[] = $this->extractMatches($path, $node->nodeValue);
                }
            }
        }
        return (object) $results;
    }

    /** 
	* Extracts matched contents
    *
    * @param string $path extraction path 
    * @param string $content content to extract
    *
	* @return object
	*/
    private function extractMatches(string $path, string $content): object
    {
        if (preg_match(self::$regex[$path], $content, $match)) {
            return (object) [
                $path => $this->formatMatch($match[1], $path),
                "matches" => $match
            ];
        }
        return (object) [];
    }

    /** 
	* clean string by type
    *
	* @param string $string 
    * @param string $path extraction path 
    *
	* @return string
	*/
    private function formatMatch(string $string, string $path): string 
    {
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
    public function open(string $username, string $pattern): mixed 
    {
        if( $this->browser === null){
            return null;
        }
        if($response = $this->browser->navigateToUrl(Url::fromString(self::$instagram . "{$username}/"))){
            if($response->getHttpResponseStatus() == 200){
                return $this->extractQuery($response->getPageContent(), $pattern);
            }
        }
        return null;
    }

    /** 
	* Extracts json from script tags
    *
	* @param string $dom instagram html dom element
    * @param string $pattern pattern to look for in document html 
    *
	* @return mixed
	*/
    private function extractQuery(string $dom, string $pattern): mixed 
    {
        $document = new DOMDocument();
        $document->loadHTML($dom);
        $xpath = new DOMXPath($document);

        $extract = $xpath->query($pattern);

        return $extract;
    }
}
