<?php

class FluxProtocol {

	private $_options;

	public function __construct($consumer, $options){
		if(!is_array($options)){
			$options = array('location' => $options);
		}
		$this->_options = $options;
		$this->_transport = $consumer->getTransport($options['location']);
	}
	
	public function __getResponseBody(){
		return $this->_transport->getResponseBody();
	}

	public function __call($method, $arguments){
		$this->_transport->setHeaders(array(
			'FluxAction' => $method,
			'User-Agent' => 'FLUX/'.Core::FRAMEWORK_VERSION
		));
		$this->_transport->setUrl($this->_options['location'].'/'.$method);
		$this->_transport->setRawPostData(serialize($arguments));
		$this->_transport->send();

		$responseCode = $this->_transport->getResponseCode();
		$responseBody = $this->_transport->getResponseBody();
		return $this->_processResponse($responseCode, $responseBody);
	}

	private function _processResponse($responseCode, $responseBody){
		if($responseCode>=200&&$responseCode<300){
			$response = @unserialize($responseBody);
			if($response===false&&strlen($responseBody)!=4){
				throw new ServiceConsumerException('Remote unclassificable exception: '.$responseBody);
			}
			return $response;
		} else {
			if($responseCode>=400&&$responseCode<600){
				$exceptionInfo = @unserialize($responseBody);
				if(is_array($exceptionInfo)){
					if(isset($exceptionInfo['type'])){
						if(class_exists($exceptionInfo['type'])){
							$className = $exceptionInfo['type'];
						} else {
							$className = 'CoreException';
						}
						throw new $className($exceptionInfo['message'], $exceptionInfo['code']);
					}
				} else {
					throw new ServiceConsumerException('Remote unclassificable exception: '.$responseBody);
				}
			}
		}
	}

}