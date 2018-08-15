<?php
include "../ScarletsQuery.php";

$dom = Scarlets\Library\MarkupLanguage::parseText(file_get_contents('sample.html'));

echo('<pre>');

print_r($dom->selector('#name')->content());
print_r($dom->selector('ul #3')[0]->next(-1)->content()."\n\n");

$contents = $dom->selector('ul li');
for ($i=0; $i < $contents->length; $i++) {
	if($contents[$i]->hasClass('profile')){
		print_r($contents[$i]->selector('span')->content(1));
	}
}

echo('</pre>');