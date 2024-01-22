# php-instagram-query

A simple php class to extract user profile pictures, page_ids, profile_id followers, following, posts & name from instagram

Installation is super-easy via Composer:
```md
composer require peterujah/php-instagram-query
```

# USAGES

Initialize InstagramQuery with the necessary parameters and register your custom classes.

```php 
use \Peterujah\NanoBlock\InstagramQuery;
$lookup = new InstagramQuery();
$username = "peterchig";
```

OR with options 
List of supported os for userAgent string `[ 'chrome', 'firefox', 'explorer', 'iphone', 'android', 'mobile', 'windows', 'mac', 'linux' ]`

```php 
use \Peterujah\NanoBlock\InstagramQuery;
$lookup = new InstagramQuery($browserLanguage, $os);
$username = "peterchig";
```

Fine user profile picture
```php
$ig = $lookup->findProfilePic($username);
echo $ig->picture;
var_dump($ig);
```

Fine user page ids
```php
$ig = $lookup->findPageId($username);
echo $ig->page;
var_dump($ig);
```

Fine user profile id
```php
$ig = $lookup->findProfileId($username);
echo $ig->profile;
var_dump($ig);
```

Fine user Instagram name
```php
$ig = $lookup->findProfileName($username);
echo $ig->name;
var_dump($ig);
```

Fine user followers
```php
$ig = $lookup->findFollowers($username);
echo $ig->followers;
var_dump($ig);
```

Fine user following
```php
$ig = $lookup->findFollowing($username);
echo $ig->following;
var_dump($ig);
```

Fine user posts
```php
$ig = $lookup->findPosts($username);
echo $ig->posts;
var_dump($ig);
```
