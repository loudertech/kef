<?php

class TestBrowserException extends CoreException {

}

class TestBrowserMessage extends Object {

	private $_node;

	public function __construct($page, $node){
		$this->_node = $node;
	}

	public function isSuccess(){
		return strpos($this->_node->getAttribute('class'), 'successMessage')!==false;
	}

	public function getContent(){
		return $this->_node->nodeValue;
	}

}

/**
 * TestBrowserElement
 *
 */
class TestBrowserElement extends Object {

	public function __construct($page, $node){
		$this->_node = $node;
	}

	/**
	 * Devuelve el id del elemento
	 *
	 */
	public function getId(){
		return $this->_node->getAttribute('id');
	}

	/**
	 * Devuelve el valor del elemento
	 *
	 */
	public function getValue(){
		return $this->_node->getAttribute('value');
	}

}

class TestBrowserForm extends Object {

	/**
	 * Elemento DOMNode al formulario
	 *
	 * @var DOMNode
	 */
	private $_form;

	/**
	 * TestBrowserPage
	 *
	 * @var TestBrowser
	 */
	private $_page;

	public function __construct($page, $form){
		$this->_page = $page;
		$this->_form = $form;
	}

	/**
	 * Envia el formulario
	 *
	 * @param	array $formInput
	 * @return	TestBrowserPage
	 */
	public function submit($formInput=array()){
		$action = $this->_form->getAttribute('action');
		$method = $this->_form->getAttribute('method');
		foreach($formInput as $inputId => $value){
			$query = '//input[@id="'.$inputId.'"] | //select[@id="'.$inputId.'"]';
			$nodeList = $this->_page->getXPath()->query($query, $this->_form);
			if($nodeList->length==0){
				throw new TestBrowserException('El campo "'.$inputId.'" no está presente en la forma');
			}
		}
		return $this->_page->getBrowser()->go($action, $method, $formInput);
	}

}

class TestBrowserPage extends Object {

	/**
	 * DOMDocument
	 *
	 * @var DOMDocument
	 */
	private $_dom;

	/**
	 * DOMXPath
	 *
	 * @var DOMXPath
	 */
	private $_xpath;

	/**
	 * Cuerpo del documento sin procesar
	 *
	 * @var string
	 */
	private $_body;

	/**
	 * Instancia de TestBrowser
	 *
	 * @var TestBrowser
	 */
	private $_browser;

	public function __construct($browser, $body){
		$this->_browser = $browser;
		$this->_dom = new DOMDocument();
		$this->_body = $body;
		@$this->_dom->loadHTML($body);
		$this->_xpath = new DOMXPath($this->_dom);
	}

	/**
	 * Obtiene el primer elemento que coincida con un selector XPATH
	 *
	 * @param	string $selector
	 * @return	TestBrowserElement
	 */
	public function getElement($selector){
		$nodeList = $this->_xpath->query('//'.$selector);
		if($nodeList->length>0){
			return new TestBrowserElement($this, $nodeList->item(0));
		} else {
			return null;
		}
	}

	/**
	 * Obtiene los elementos que coincidan con un selector XPATH
	 *
	 * @param	string $selector
	 * @return	array
	 */
	public function getElements($selector){
		$elements = array();
		$nodeList = $this->_xpath->query('//'.$selector);
		foreach($nodeList as $node){
			$elements[] = new TestBrowserElement($this, $node);
		}
		return $elements;
	}

	/**
	 * Devuelve el primer formulario encontrado en la página
	 *
	 * @return TestBrowserForm
	 */
	public function getFirstForm(){
		$nodeList = $this->_xpath->query('//form');
		if($nodeList->length>0){
			return new TestBrowserForm($this, $nodeList->item(0));
		}
	}

	/**
	 * Obtiene la instancia de TestBrowser
	 *
	 * @return TestBrowser
	 */
	public function getBrowser(){
		return $this->_browser;
	}

	/**
	 * Obtiene el objeto XPATH de la página
	 *
	 */
	public function getXPath(){
		return $this->_xpath;
	}

	/**
	 * Devuelve el HTML de la pagina
	 *
	 * @return string
	 */
	public function getBody(){
		return $this->_dom->saveHTML();
	}

	/**
	 * Devuelve el cuerpo HTTP del documento
	 *
	 * @return string
	 */
	public function getRawBody(){
		return $this->_body;
	}

	public function hasMessage($message){
		$nodeList = $this->_xpath->query('//div');
		if($nodeList->length>0){
			foreach($nodeList as $node){
				$className = $node->getAttribute('class');
				if(strpos($className, 'kumbiaDisplay')!==false){
					if(strpos($node->nodeValue, $message)!==false){
						return true;
					}
				}
			}
			return false;
		} else {
			return false;
		}
	}

	public function getMessages(){
		$messages = array();
		$nodeList = $this->_xpath->query('//div');
		if($nodeList->length>0){
			foreach($nodeList as $node){
				$className = $node->getAttribute('class');
				if(strpos($className, 'kumbiaDisplay')!==false){
					$messages[] = new TestBrowserMessage($this, $node);
				}
			}
		}
		return $messages;
	}

	public function chooseFromSelect($selectId, $text){
		$query = '//select[@id="'.$selectId.'"]';
		$nodeList = $this->_xpath->query($query);
		if($nodeList->length>0){
			foreach($nodeList as $node){
				foreach($this->_xpath->query('//option', $node) as $option){
					if($option->nodeValue==$text){
						return $option->getAttribute('value');
					}
				}
			}
		} else {
			throw new TestBrowserException('No existe la lista "'.$selectId.'"');
		}
	}

	public function getOptionsFrom($selectId){
		$query = '//select[@id="'.$selectId.'"]';
		$nodeList = $this->_xpath->query($query);
		if($nodeList->length>0){
			$options = array();
			foreach($nodeList as $node){
				foreach($this->_xpath->query('//option', $node) as $option){
					$options[] = new TestBrowserElement($this, $option);
				}
			}
			return $options;
		} else {
			throw new TestBrowserException('No existe la lista "'.$selectId.'"');
		}
	}

}

class TestBrowser extends Object {

	private static $_instance = null;

	private $_http;

	private $_server;

	private $_lastRequestType;

	private function __construct(){
		$this->_http = new HttpRequest();
		$this->_http->enableCookies();
	}

	/**
	 * Establece el servidor donde se va a trabajar
	 *
	 * @param string $server
	 */
	public function setServer($server){
		$this->_server = $server;
	}

	/**
	 * Devuelve el singleton de TestBrowser
	 *
	 * @return TestBrowser
	 */
	public static function getInstance(){
		if(self::$_instance===null){
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	/**
	 * Realiza la petición HTTP
	 *
	 * @param	string $url
	 * @param 	string $type
	 * @param	string $method
	 * @param	array $input
	 */
	private function _prepareGo($url, $type, $method, $input){
		if($method=='get'){
			$this->_http->setMethod(HttpRequest::METH_GET);
		} else {
			$this->_http->setMethod(HttpRequest::METH_POST);
			$this->_http->setPostFields($input);
		}
		if($type!=$this->_lastRequestType){
			if($type=='normal'){
				$this->_http->setHeaders(array(
					'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
					'Accept-Language' => 'en,en-us;q=0.8,es-ar;q=0.5,es;q=0.3',
					'User-Agent' => 'Mozilla/5.0 (Macintosh; U; Intel Mac OS X 10.6; es-AR; rv:1.9.2.4) Gecko/20100611 Firefox/3.6.4'
				));
			} else {
				if($type=='ajax'){
					$this->_http->setHeaders(array(
						'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
						'Accept-Language' => 'en,en-us;q=0.8,es-ar;q=0.5,es;q=0.3',
						'User-Agent' => 'Mozilla/5.0 (Macintosh; U; Intel Mac OS X 10.6; es-AR; rv:1.9.2.4) Gecko/20100611 Firefox/3.6.4',
						'X-Requested-With' => 'XMLHttpRequest'
					));
				}
			}
			$this->_lastRequestType = $type;
		}
		$this->_http->setUrl('http://'.$this->_server.$url);
		$this->_http->send();
	}

	/**
	 * Realiza una petición sobre el servidor actual y devuelve un objeto de página
	 *
	 * @param	string $url
	 * @param	string $method
	 * @param	array $input
	 * @return	TestBrowserPage
	 */
	public function go($url, $method='get', $input=array()){
		try {
			$this->_prepareGo($url, 'normal', $method, $input);
			if($this->_http->getResponseCode()>=400){
				file_put_contents('console.log', $this->_http->getResponseBody(), FILE_APPEND);
				throw new TestBrowserException($this->_http->getResponseStatus());
			} else {
				return new TestBrowserPage($this, $this->_http->getResponseBody());
			}
		}
		catch(HttpException $e){

		}
	}

	/**
	 * Realiza una petición sobre el servidor actual y devuelve un valor procesado del XML resultante
	 *
	 * @param	string $url
	 * @param	string $method
	 * @param	array $input
	 * @return	TestBrowserPage
	 */
	public function goQuery($url, $method='get', $input=array()){
		try {
			$this->_prepareGo($url, 'ajax', $method, $input);
			if($this->_http->getResponseCode()>=400){
				file_put_contents('console.log', $this->_http->getResponseBody(), FILE_APPEND);
				throw new TestBrowserException($this->_http->getResponseStatus());
			} else {
				file_put_contents('b.txt', $this->_http->getResponseBody());
				$dom = new DOMDocument();
				$dom->loadXML($this->_http->getResponseBody());
				foreach($dom->getElementsByTagName('data') as $node){
					return $node->nodeValue;
				}
			}
		}
		catch(HttpException $e){

		}
	}

}