<a href="https://www.patreon.com/stefansarya"><img src="http://anisics.stream/assets/img/support-badge.png" height="20"></a>

[![Written by](https://img.shields.io/badge/Written%20by-ScarletsFiction-%231e87ff.svg)](https://github.com/ScarletsFiction/)
[![Software License](https://img.shields.io/badge/License-MIT-brightgreen.svg)](LICENSE)
[![Build Status](https://api.travis-ci.org/ScarletsFiction/ScarletsQuery.svg?branch=master)](https://travis-ci.org/ScarletsFiction/ScarletsQuery)
[![Tweet](https://img.shields.io/twitter/url/http/shields.io.svg?style=social)](https://twitter.com/intent/tweet?text=ScarletsQuery%20is%20a%20high%20performance%20HTML%20or%20XML%20query%20selector/parser%20for%20PHP%20that%20almost%20like%20jQuery&url=https://github.com/ScarletsFiction/ScarletsQuery&via=github&hashtags=scarlets,dom,parser,query,php)

# ScarletsQuery
ScarletsQuery is a high performance HTML or XML query selector/parser for PHP that almost like jQuery.

## Installation instruction

Clone/download this repository and include `ScarletsQuery.php`

### Install with composer
> composer require scarletsfiction/scarletsquery

## Available Methods
This library query selector is designed like jQuery's queryselector.<br>
But there are different method to obtain the data.

### MarkupLanguage
This class will handle every selector query and parse Markup Language.

> MarkupLanguage::parseText($string);

Parse HTML/XML to structured array as `MarkupLanguageElementCollection`

### MarkupLanguageElementCollection
This class have a collection of Element Tree, you can also obtain the value just like an array `$collection[0]` and it will return `MarkupLanguageElement`.

> $dom->length;

Return length of Element Collection

> $dom->view;

Return raw array of the Element Collection

> $dom->selector($query);

Return the result as `MarkupLanguageElementCollection`

> $dom->parent();

Return parent of all result as `MarkupLanguageElementCollection`

> $dom->content($index = false);

Return content of all result as `Array`
If `$index` specified, it will return the content as `String`

### MarkupLanguageElement
This class have a collection of Element Tree, you can also obtain the value just like an array `$collection[0]` and it will return `MarkupLanguageElement`.

> $dom->view;

Return raw array of the Element

> $dom->selector($query);

Return the result as `MarkupLanguageElement`

> $dom->parent();

Return the element's parent `MarkupLanguageElement`

> $dom->hasClass($class);

Return `true` if found and vice-versa

> $dom->next($jump = 1);

Return the next sibling element as `MarkupLanguageElement`

> $dom->content();

Return the content as `String`

> $dom->attr($property);

Return element property as `String` or `null` if not found

## Example Usage

```html
<ul>
  <li class='account'>
    <span active id="name">Alex</span>
    <span active id="1">1a</span>
  </li>
  <li class="account">
    <span active id='nickname'>Steven</span>
    <span active id="2">2b</span>
  </li>
  <li class="profile">
    <span active id="name">Elisabeth</span>
    <span active id="3">3c</span>
  </li>
  <li class="account">
    <span active id="name">Luffy</span>
    <span active id="4">4d</span>
  </li>
</ul>
```

And below is the PHP script

```php
$html = file_get_contents('sample.html');
$dom = Scarlets\Library\MarkupLanguage::parseText($html);

// Select element that have 'name' id and get these content
$dom->selector('#name')->content();
/* Output
    Array
    (
        [0] => Alex
        [1] => Elisabeth
        [2] => Luffy
    )
*/

/*
  1. Select element that have 'ul' tag
  2. Select the child with '3' id
  3. Select the element before it { next(-1) }
  4. Get the content
*/
$dom->selector('ul #3')[0]->next(-1)->content();
/* Output
    Elisabeth
*/

// Select 'ul' tag and the child with 'li' tag
$contents = $dom->selector('ul li');

// Iterate over the result
for ($i=0; $i < $contents->length; $i++) {

    // Check if the element has 'profile' class
    if($contents[$i]->hasClass('profile')){

        // Select the child with 'span' tag
        // And get the second content
        print_r($contents[$i]->selector('span')->content(1));
    }
}
/* Output
    3c
*/
```

## Contribution

If you want to help in ScarletsQuery, please fork this project and edit on your repository, then make a pull request to here.

Keep the code simple and clear.

## License

ScarletsQuery is under the MIT license.

Help improve this framework by support the author ＼(≧▽≦)／