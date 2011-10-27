<?php

require 'Library/Kumbia/Twitter/Tweet.php';

class Twitter extends Object {

	private $_apiHost = 'http://api.twitter.com';

	private $_username;

	private $_password;

	private $_dom;

	public function __construct($username, $password=''){
		$this->_username = $username;
		$this->_password = $password;
	}

	private function _executeApi($uri, $authenticate, $options=array()){
		$parameters = '';
		if(count($options)){
			$queryString = array();
			foreach($options as $key => $value){
				$queryString[] = $key.'='.$value;
			}
			$parameters = '?'.join('&', $queryString);
		}
		if($authenticate==true){
			$context = stream_context_create(array(
			    'http' => array(
			        'header'  => "Authorization: Basic ".base64_encode($this->_username.':'.$this->_password)
			    )
			));
			$restResource = @file_get_contents($this->_apiHost.$uri.$parameters, false, $context);
		} else {
			$restResource = @file_get_contents($this->_apiHost.$uri.$parameters);
		}
		if($restResource==false){
			throw new TwitterException($php_errormsg);
		} else {
			return $restResource;
		}
	}

	public function getRecentTweets($options=array()){
		if(isset($options['username'])){
			$username = $this->_username;
		} else {
			$username = $options['username'];
		}
		$restResource = $this->_executeApi('/1/statuses/user_timeline/'.$username.'.json', $options);
		$statuses = json_decode($restResource, true);
		$tweets = array();
		foreach($statuses as $status){
			$tweets[] = new Tweet($status);
		}
		return $tweets;
	}

	public function getFollowers($options=array()){
		if(isset($options['username'])){
			$username = $this->_username;
		} else {
			$username = $options['username'];
		}
		echo $restResource = $this->_executeApi('/1/statuses/followers/'.$username.'.json', $options);
	}

}