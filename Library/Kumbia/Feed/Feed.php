<?php

/**
 * Kumbia Enterprise Framework
 *
 * LICENSE
 *
 * This source file is subject to the New BSD License that is bundled
 * with this package in the file docs/LICENSE.txt.
 *
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@loudertechnology.com so we can send you a copy immediately.
 *
 * @category	Kumbia
 * @package		Feed
 * @copyright	Copyright (c) 2008-2009 Louder Technology COL. (http://www.loudertechnology.com)
 * @copyright	Copyright (c) 2005-2009 Andres Felipe Gutierrez (gutierezandresfelipe at gmail.com)
 * @license		New BSD License
 * @version 	$Id: Feed.php 29 2009-05-01 02:19:38Z gutierrezandresfelipe $
 */

/**
 * Feed
 *
 * Permite la creación/lectura de feeds
 *
 * @category	Kumbia
 * @package		Feed
 * @copyright	Copyright (c) 2008-2009 Louder Technology COL. (http://www.loudertechnology.com)
 * @copyright	Copyright (c) 2005-2009 Andres Felipe Gutierrez (gutierezandresfelipe at gmail.com)
 * @license		New BSD License
 * @abstract
 */
class Feed {

	/**
	 * Objeto DOMDocument
	 *
	 * @var DOMDocument
	 */
	private $_dom;

	/**
	 * Objeto XPath
	 *
	 * @var DOMXPath
	 */
	private $_xpath;

	/**
	 * Constructor de Feed
	 *
	 * @param string $version
	 * @param string $encoding
	 */
	public function __construct($version='1.0', $encoding='UTF-8'){
		$this->_dom = new DOMDocument($version, $encoding);
	}

	/**
	 * Crea u obtiene el objeto Xpath
	 *
	 * @return DOMXpath
	 */
	private function _getXpath(){
		if($this->_xpath==null){
			$this->_xpath = new DOMXPath($this->_dom);
		}
		return $this->_xpath;
	}

	/**
	 * Inicializa el documento RSS
	 *
	 * @return DOMDocument
	 */
	private function _initializeRSS(){
		$rss = $this->_dom->getElementsByTagName('rss');
		if($rss->length==0){
			$rss = $this->_dom->createElement('rss');
			$rss->setAttribute('version', '2.0');
			$rss->setAttribute('xmlns:atom', 'http://www.w3.org/2005/Atom');
			$this->_dom->appendChild($rss);
			return $rss;
		} else {
			return $rss->item(0);
		}
	}

	/**
	 * Inicializa el canal (channel) en el Feed
	 *
	 * @return DOMDocument
	 */
	private function _initializeChannel(){
		$rss = $this->_initializeRSS();
		$channel = $this->_dom->getElementsByTagName('channel');
		if($channel->length==0){
			$channel = $this->_dom->createElement('channel');
			$rss->appendChild($channel);
			return $channel;
		} else {
			return $channel->item(0);
		}
	}

	/**
	 * Crea ó actualiza un valor de un nodo hijo del nodo raiz "rss"
	 *
	 * @param string $nodeName
	 * @param string $value
	 */
	private function _setRootNode($nodeName, $value){
		$rss = $this->_initializeRSS();
		$xpath = $this->_getXpath();
		$nodeList = $xpath->query('//rss/'.$nodeName);
		if($nodeList->length==0){
			$element = new DOMElement($nodeName, $value);
			$rss->appendChild($element);
		} else {
			$element = $nodeList->item(0);
			$element->nodeValue = $value;
		}
	}

	/**
	 * Establece el titulo del Feed
	 *
	 * @param string $title
	 */
	public function setTitle($title){
		$this->_setRootNode('title', $title);
	}

	/**
	 * Establece el descripción del Feed
	 *
	 * @param string $description
	 */
	public function setDescription($description){
		$this->_setRootNode('description', $description);
	}

	/**
	 * Establece el link del Feed
	 *
	 * @param string $link
	 */
	public function setLink($link){
		$this->_setRootNode('link', $link);
	}

	/**
	 * Establece el idioma del Feed
	 *
	 * @param string $language
	 */
	public function setLanguage($language){
		$this->_setRootNode('language', $language);
	}

	/**
	 * Establece el TTL del Feed
	 *
	 * @param string $ttl
	 */
	public function setTtl($ttl){
		$this->_setRootNode('ttl', $ttl);
	}

	/**
	 * Establece el software generador del Feed
	 *
	 * @param string $generator
	 */
	public function setGenerator($generator){
		$this->_setRootNode('generator', $generator);
	}

	/**
	 * Establece el la URL con detalles del formato usado
	 * para generar el Feed
	 *
	 * @param string $docs
	 */
	public function setDocs($docs){
		$this->_setRootNode('docs', $docs);
	}

	/**
	 * Agrega un item al Feed
	 *
	 * @param FeedItem $item
	 */
	public function addItem(FeedItem $item){
		$channel = $this->_initializeChannel();
		$elementItem = new DOMElement('item');
		$channel->appendChild($elementItem);
		$elements = $item->getElementsAsArray();
		foreach($elements as $elementName => $value){
			$element = new DOMElement($elementName, $value);
			$elementItem->appendChild($element);
		}
	}

	/**
	 * Obtiene el XML del Feed
	 *
	 * @return string
	 */
	public function getXMLFeed(){
		$this->_initializeRSS();
		return $this->_dom->saveXML();
	}

	/**
	 * Lee un recurso RSS apartir de su ubicación
	 *
	 * @param string $url
	 * @return boolean
	 */
	public function readRss($url){
		$rssContent = file_get_contents($url);
		if($rssContent!==false){
			$this->readRssString($rssContent);
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Lee un recurso RSS apartir de un string XML
	 *
	 * @param string $rssString
	 */
	public function readRssString($rssString){
		$this->_dom->loadXML($rssString);
	}

	/**
	 * Devuelve los items del RSS
	 *
	 * @return array
	 */
	public function getItems(){
		$items = $this->_dom->getElementsByTagName('item');
		$feedItems = array();
		foreach($items as $item){
			$feedItem = new FeedItem();
			foreach($item->childNodes as $child){
				switch($child->localName){
					case 'title':
						$feedItem->setTitle($child->nodeValue);
						break;
					case 'link':
						$feedItem->setLink($child->nodeValue);
						break;
					case 'pubDate':
						$feedItem->setPubDate($child->nodeValue);
						break;
				}
			}
			$feedItems[] = $feedItem;
		}
		return $feedItems;
	}

}
