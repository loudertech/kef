<?php

class ProcessController extends ApplicationController {

	public function beforeFilter(){
		set_time_limit(0);
	}

}