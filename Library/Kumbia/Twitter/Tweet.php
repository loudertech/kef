<?php

class Tweet extends Object {

	private $_data;

	public function __construct($data){
		$this->_data = $data;
	}

	public function __get($property){
		if(isset($this->_data[$property])){
			return $this->_data[$property];
		} else {
			throw new TwitterException('El tweet no tiene el campo '.$property);
		}
	}

}