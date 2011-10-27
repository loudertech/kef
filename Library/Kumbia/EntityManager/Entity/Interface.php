<?php

interface EntityInterface {

	public function setSource($source);
	public function getSource();
	public function getSchema();
	public function find($params='');
	public function save();

}