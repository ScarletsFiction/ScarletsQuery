<?php
/* 
	ScarletsFiction Markup Language Query (ScarletsQuery)
	This library is under MIT License.

	You can freely use this library anywhere.
	But the only rule is, don't ever scrape a
	website made with ScarletsFramework.

	Because this library is brought to you by
	ScarletsFiction.
*/
namespace Scarlets\Library;

class MarkupLanguage{
	private static $DocumentTree = [];
	private static $currentTreeIndex = [];
	private static $lockTreeIndex = false;

	public static function selector($selector, $customTree){
		// Create copy
		self::$DocumentTree = $customTree;

		$selector = explode(' ', $selector);
		foreach($selector as $value){
			if($value[0] === '>'){
				self::$lockTreeIndex = count(self::$currentTreeIndex) + 1;
				continue;
			}

			if($value[0]==='.')
				self::findClass(substr($value, 1));

			elseif($value[0]==='#')
				self::findID(substr($value, 1));

			elseif($value[0]==='['){
				$value = substr($value, 1, -1);
				$value = explode('=', $value);
				if(count($value)===1)
					$value[1] = null;
				else 
					$value[1] = str_replace(['"', "'"], '', $value[1]);
				self::findAttribute($value[0], $value[1]);
			}

			else
				self::findTag($value);

			if(self::$lockTreeIndex)
				self::$lockTreeIndex = false;
		}
		return self::$DocumentTree;
	}

	private static function currentElementQuery($selector){
		self::$lockTreeIndex = false;

		preg_match_all('/[.#[][\w=\"\']+/', $selector, $matches);
		foreach($matches[0] as $value) {
			if($value[0] === '.')
				self::findClass(substr($value, 1), true);

			elseif($value[0] === '#')
				self::findID(substr($value, 1), true);

			elseif($value[0] === '['){
				$value = substr($value, 1);
				$value = explode('=', $value);
				if(count($value)===1)
					$value[1] = null;
				else 
					$value[1] = str_replace(['"', "'"], '', $value[1]);
				self::findAttribute($value[0], $value[1], true);
			}
		}
	}

	private static function findClass($select, $currentElement = false){
		if($currentElement){
			$collection = &self::$DocumentTree;
			for ($i=0; $i < count($collection); $i++) {
				foreach ($collection[$i] as $tag => $value) {
					if(!isset($value['class']) || !in_array($select, $value['class']))
						unset($collection[$i]);
					break;
				}
			}
			$collection = array_values($collection);
			return;
		}
		
		$selector = explode('%$@', str_replace(['.', '#', '['], '%$@', $select))[0];
		self::$DocumentTree = self::iterateFindClass($selector, self::$DocumentTree);

		if($selector !== $select)
			$matched = self::currentElementQuery($select);
	}

	private static function &iterateFindClass($class, &$currentTree){
		$found = [];
		$count = 0;
		$treeLocked = self::$lockTreeIndex ? (self::$lockTreeIndex < count(self::$currentTreeIndex)) : false;
		self::$currentTreeIndex[] = &$count;
		foreach($currentTree as $childs){
			if(isset($childs['parent']))
				self::$currentTreeIndex = $childs['parent'];

			foreach ($childs as $tag => $value) {
				if(isset($value['class']) && in_array($class, $value['class'])){
					$childs['parent'] = json_decode(json_encode(self::$currentTreeIndex), true);
					$found[] = $childs;
				}

				if(isset($childs['child']) && !$treeLocked){
					$data = self::iterateFindClass($class, $childs['child']);
					if(!empty($data)) $found = array_merge($found, $data);
				}
				break;
			}
			$count++;
		}
		array_pop(self::$currentTreeIndex);
		return $found;
	}

	private static function findID($select, $currentElement = false){
		if($currentElement){
			$collection = &self::$DocumentTree;
			for ($i=0; $i < count($collection); $i++) {
				foreach ($collection[$i] as $tag => $value) {
					if(!$select == $value['id'])
						unset($collection[$i]);
					break;
				}
			}
			$collection = array_values($collection);
			return;
		}
		
		$selector = explode('%$@', str_replace(['.', '#', '['], '%$@', $select))[0];
		self::$DocumentTree = self::iterateFindID($selector, self::$DocumentTree);

		if($selector !== $select)
			$matched = self::currentElementQuery($select);
	}

	private static function &iterateFindID($id, &$currentTree){
		$found = [];
		$count = 0;
		$treeLocked = self::$lockTreeIndex ? (self::$lockTreeIndex < count(self::$currentTreeIndex)) : false;
		self::$currentTreeIndex[] = &$count;
		foreach($currentTree as $childs){
			if(isset($childs['parent']))
				self::$currentTreeIndex = $childs['parent'];

			foreach ($childs as $tag => $value) {
				if(isset($value['id']) && $id == $value['id']){
					$childs['parent'] = json_decode(json_encode(self::$currentTreeIndex), true);
					$found[] = $childs;
				}

				if(isset($childs['child']) && !$treeLocked){
					$data = self::iterateFindID($id, $childs['child']);
					if(!empty($data)) $found = array_merge($found, $data);
				}
				break;
			}
			$count++;
		}
		array_pop(self::$currentTreeIndex);
		return $found;
	}

	private static function findAttribute(&$select, $value, $currentElement = false){
		if($currentElement){
			$collection = &self::$DocumentTree;
			for ($i=0; $i < count($collection); $i++) {
				foreach ($collection[$i] as $tag => $attr) {
					if(!isset($attr[$select]))
						unset($collection[$i]);
					elseif($value !== null && $value !== $attr[$select])
						unset($collection[$i]);
					break;
				}
			}
			$collection = array_values($collection);
			return;
		}

		self::$DocumentTree = self::iterateFindAttr($select, $value, self::$DocumentTree);
		
		if(strpos($select, '.') !==false || strpos($select, '#') !==false || strpos($select, '[') !==false)
			$matched = self::currentElementQuery($select);
	}

	private static function &iterateFindAttr($key, $values, &$currentTree){
		$found = [];
		$count = 0;
		$treeLocked = self::$lockTreeIndex ? (self::$lockTreeIndex < count(self::$currentTreeIndex)) : false;
		self::$currentTreeIndex[] = &$count;
		foreach($currentTree as $childs){
			if(isset($childs['parent']))
				self::$currentTreeIndex = $childs['parent'];
			
			foreach ($childs as $tag => $value) {
				if(isset($value[$key])){
					if($values !== null){
						if($value[$key] === $values){
							$childs['parent'] = json_decode(json_encode(self::$currentTreeIndex), true);
							$found[] = $childs;
						}
					}
					else{
						$childs['parent'] = json_decode(json_encode(self::$currentTreeIndex), true);
						$found[] = $childs;
					}
				}
				
				if(isset($childs['child']) && !$treeLocked){
					$data = self::iterateFindAttr($key, $values, $childs['child']);
					if(!empty($data)) $found = array_merge($found, $data);
				}
				break;
			}
			$count++;
		}
		array_pop(self::$currentTreeIndex);
		return $found;
	}

	private static function findTag(&$select){
		$selector = explode('%$@', str_replace(['.', '#', '['], '%$@', $select))[0];
		self::$DocumentTree = self::iterateFindTag($selector, self::$DocumentTree);

		if($selector !== $select)
			$matched = self::currentElementQuery($select);
	}

	private static function &iterateFindTag($findTag, &$currentTree){
		$found = [];
		$count = 0;
		$treeLocked = self::$lockTreeIndex ? (self::$lockTreeIndex < count(self::$currentTreeIndex)) : false;
		self::$currentTreeIndex[] = &$count;
		foreach($currentTree as $childs){
			if(isset($childs['parent']))
				self::$currentTreeIndex = $childs['parent'];
			
			foreach ($childs as $tag => $value) {
				if($tag === $findTag){
					$childs['parent'] = json_decode(json_encode(self::$currentTreeIndex), true);
					$found[] = $childs;
				}

				if(isset($childs['child']) && !$treeLocked){
					$data = self::iterateFindTag($findTag, $childs['child']);
					if(!empty($data)) $found = array_merge($found, $data);
				}
				break;
			}
			$count++;
		}
		array_pop(self::$currentTreeIndex);
		return $found;
	}

	public static function parseText($source){
		self::$DocumentTree = [];
		// Remove all not important stuff
		$source = preg_replace('/<(style|script).*?<\/(style|script)>|\n+|\t+|\r/s', '', $source);
		$source = str_replace(['<br />', '<br/>', '<br>'], "\n", $source);
		$source = str_replace(['“', '”', "&#8220;", "&#8221;"], '"', $source);
		$source = str_replace(["&#8217;", "&#8216;"], "'", $source);
		$source = str_replace("&#8211;", "-", $source);
		$source = str_replace("&#8230;", "...", $source);
		$source = htmlspecialchars_decode($source, ENT_QUOTES);

		$matches = explode('<', $source);
		array_shift($matches);
		self::parseTree(self::$DocumentTree, 0, $matches, count($matches));
		return (new MarkupLanguageElementCollection(self::$DocumentTree, self::$DocumentTree));
	}

	private static function parseTree(&$lastTree, $lastIndex, &$elements, $elementCount){
		for ($i = $lastIndex; $i < $elementCount; $i++) {
			$element = trim($elements[$i]);

			// Skip comments
			if($element[0][0] === '!')
				continue;

			// Check for a closing
			if($element[0][0] === '/')
				return $i;
			
			// For checking later
			$ends = false;

			// Extract attributes and content
			$attributes = explode('>', $element);
			if(count($attributes) === 1){
				$element = trim($attributes[0]);
				$attributes = false;
			} else {
				$element = trim($attributes[1]);
				$attributes = explode(' ', trim($attributes[0]), 2);
				$tag = $attributes[0];
				if(count($attributes) === 1)
					$attributes = [];
				else{
					$attributes[1] = str_replace("'", '"', $attributes[1]);
					$attributes_ = explode('="', $attributes[1]);
					$attributes = [];

					$attr = trim($attributes_[0]);
					if(strpos($attr, ' ') !== false){
						$attr = explode(' ', $attr);
						foreach ($attr as $value) {
							$attributes[$value] = '';
						}
						$attr = $attr[count($attr) - 1];
					}

					$data = '';

					// Will error if the string has escaped \"
					for($a=1; $a < count($attributes_); $a++){
						$attributes_[$a] = explode('"', $attributes_[$a]);
						$data .=  $attributes_[$a][0];

						if(count($attributes_[$a]) === 1){
							$data .= '="';
							continue;
						}

						$attributes[$attr] = $data;

						// Prepare next
						$attr = trim($attributes_[$a][count($attributes_[$a]) - 1]);
						if(strpos($attr, ' ') !== false){
							$attr = explode(' ', $attr);
							foreach ($attr as $value) {
								$attributes[$value] = '';
							}
							$attr = $attr[count($attr) - 1];
						}
						$data = '';
					}

					if($attr === '/')
						$ends = true;
					elseif($attr !== '' && isset($attributes[$attr])) {
						// Check if this element not have a child element
						$ends = substr($attributes[$attr], -1) === '/';

						// Remove last backslash
						if($ends){
							$ref = &$attributes[$attr];
							$ref = substr($ref, 0, -1);
							$attributes = array_filter($attributes);
						}
					}

					

					$attributes_ = NULL;
				}
			}


			// Check if this element not have a child element
			if($ends
				|| $tag === 'meta'
				|| $tag === 'link'){

				// Explode classes
				if(isset($attributes['class']))
					$attributes['class'] = explode(' ', $attributes['class']);

				// Add to the tree
				$lastTree[] = [
					$tag => $attributes
				];
				continue;
			}

			// Create the tree by re-parse the child element
			$childTree = [];
			$i = self::parseTree($childTree, $i + 1, $elements, $elementCount);

			// Explode classes
			if(isset($attributes['class']))
				$attributes['class'] = explode(' ', $attributes['class']);

			$currentTree = [
				$tag => $attributes
			];

			if(!empty($childTree))
				$currentTree['child'] = $childTree;

			if(strlen($element)!==0)
				$currentTree['content'] = $element;

			$lastTree[] = $currentTree;
		}
		return $elementCount;
	}
}

class MarkupLanguageElement{
	private $DocumentTree = null;
	private $element = null;
	public function __construct($element, &$DocumentTree){
		$this->element = $element;
		$this->DocumentTree = $DocumentTree;
	}

	public function selector($selector){
		return new MarkupLanguageElementCollection(MarkupLanguage::selector($selector, [$this->element]), $this->DocumentTree);
	}

	public function parent(){
		$copy = $this->DocumentTree;
		$parent = $this->element['parent'];
		array_pop($parent);
		$parent = explode(' ', implode(' child ', $parent));
		foreach($parent as $key){
			$copy = $copy[$key];
		}
		return $copy;
	}
	
	public function hasClass($class){
		foreach($this->element as $value){
			if(isset($value['class']) && in_array($class, $value['class']))
				return true;
			break;
		}
		return false;
	}
	
	public function next($jump=1){
		$copy = $this->DocumentTree;
		$parent = $this->element['parent'];
		$ref = &$parent[count($parent)-1];

		if($jump==0) $jump++;
		$ref = $ref + $jump;

		$parent = explode(' ', implode(' child ', $parent));
		foreach($parent as $key){
			if(!isset($copy[$key])) return null;
			$copy = $copy[$key];
		}
		return new MarkupLanguageElement($copy, $this->DocumentTree);
	}
	
	public function content(){
		if(isset($this->element['content']))
			return $this->element['content'];
		return '';
	}
	
	public function attr($attr){
		foreach($this->element as $value){
			if(isset($value[$attr]))
				return $value[$attr];
			break;
		}
		return null;
	}

	public function __get($prop){
		if($prop==='view'){
			$copy = $this->element;
			if(isset($copy['parent']))
				unset($copy['parent']);
			return $copy;
		}
		return null;
	}
}

class MarkupLanguageElementCollection implements \ArrayAccess{
	private $DocumentTree = [];
	private $collection = [];
	public function __construct($collection, &$DocumentTree){
		$this->collection = $collection;
		$this->DocumentTree = $DocumentTree;
	}

	public function selector($selector){
		return new MarkupLanguageElementCollection(MarkupLanguage::selector($selector, $this->collection), $this->DocumentTree);
	}

	public function parent(){
		$copy = $this->DocumentTree;
		foreach ($this->collection as $value) {
			$parent = $value['parent'];
			array_pop($parent);
			$parent = explode(' ', implode(' child ', $parent));
			foreach($parent as $key){
				$copy = $copy[$key];
			}
		}
		$this->collection = $copy;
		return $this;
	}
	
	public function &content($index = false){
		if($index !== false){
			if(isset($this->collection[$index]) && isset($this->collection[$index]['content']))
				return $this->collection[$index]['content'];
			else{
				$n = '';
				return $n;
			}
		}

		$content = [];
		for($i=0; $i < count($this->collection); $i++){
			if(isset($this->collection[$i]['content']))
		 		$content[] = $this->collection[$i]['content'];
		}

		return $content;
	}

	public function offsetSet($index, $value){
		if(!$value instanceof Element)
			throw new \Exception("Value must be the instance of MarkupLanguageElement");
		
        if(is_null($index))
            $this->collection[] = $value;
        else
            $this->collection[$index] = $value;
    }

    public function offsetExists($index){
        return isset($this->collection[$index]);
    }

    public function offsetUnset($index){
        unset($this->collection[$index]);
    }

    public function offsetGet($index){
    	if(!isset($this->collection[$index]))
    		return null;

        return new MarkupLanguageElement($this->collection[$index], $this->DocumentTree);
    }

    public function __get($prop){
    	if($prop==='length')
    		return count($this->collection);
    	
		else if($prop==='view'){
			$copy = $this->collection;
			foreach ($copy as &$value) {
				if(isset($value['parent']))
					unset($value['parent']);
			}
			return $copy;
		}

    	return null;
    }
}