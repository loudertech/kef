<?php

class UuidCassieGenerator {

	public static function generate(){
		$randomBits = false;
		if(PHP_OS=='Linux'){
			$fp = @fopen('/dev/urandom', 'rb');
			if($fp!==false){
				$randomBits.=@fread($fp,16);
				@fclose($fp);
			}
		} else {
			$randomBits = "";
			for($count=0;$count<16;++$count){
				$randomBits.=chr(mt_rand(0, 255));
			}
		}
		$timeLow = bin2hex(substr($randomBits, 0, 4));
		$timeMid = bin2hex(substr($randomBits, 4, 2));
		$timeHiAndVersion = bin2hex(substr($randomBits, 6, 2));
		$clockSeqHiAndReserved = bin2hex(substr($randomBits, 8, 2));
		$node = bin2hex(substr($randomBits, 10, 6));
		$timeHiAndVersion = hexdec($timeHiAndVersion);
		$timeHiAndVersion = $timeHiAndVersion >> 4;
		$timeHiAndVersion = $timeHiAndVersion | 0x4000;
		$clockSeqHiAndReserved = hexdec($clockSeqHiAndReserved);
		$clockSeqHiAndReserved = $clockSeqHiAndReserved >> 2;
		$clockSeqHiAndReserved = $clockSeqHiAndReserved | 0x8000;
		return sprintf('%08s-%04s-%04x-%04x-%012s', $timeLow, $timeMid, $timeHiAndVersion, $clockSeqHiAndReserved, $node);
	}

}