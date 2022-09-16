# php-instagram-query

A simple php class to extract user profile picture, page_ids, profile_id & name from instagram

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

```php 
use \Peterujah\NanoBlock\InstagramQuery;
$lookup = new InstagramQuery($browserLanguage, $userAgent);
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

Fine user instagram name
```php
$ig = $lookup->findProfileName($username);
echo $ig->name;
var_dump($ig);
```
