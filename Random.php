<?php
/* fadhiilrachman@gmail.com made this (c) 2013 */
Class Random {

	protected static $reg_alphabet='ABCDEFGHIJKLMNOPQRSTUVWXYZ',

					 $reg_numeric='0123456789';

	private static $context='';

	public function __construct() {
	}

	private static function random($min, $max) {
		$range = $max - $min;
		if ($range < 0) return $min;
			$log = log($range, 2);
			$bytes = (int) ($log / 8) + 1;
			$bits = (int) $log + 1;
			$filter = (int) (1 << $bits) - 1;
			do {
				$rnd = hexdec(bin2hex(openssl_random_pseudo_bytes($bytes)));
				$rnd = $rnd & $filter;
			} while ($rnd >= $range);
		return $min + $rnd;
	}

	public static function substr($string) {
		Random::$context='';
		$panjang = strlen($string);
		for($i = 1; $i <= $panjang; $i++) {
			Random::$context .= substr($string, $panjang-$i, 1);
		}
		return Random::$context;
	}

	public static function alphabet($range) {
		Random::$context='';
		for($i=0; $i<$range; $i++){
			Random::$context .= Random::$reg_alphabet[Random::random(0,strlen(Random::$reg_alphabet))];
		}
		return Random::$context;
	}

	public static function numeric($range) {
		Random::$context='';
		for($i=0; $i<$range; $i++){
			Random::$context .= Random::$reg_numeric[Random::random(0,strlen(Random::$reg_numeric))];
		}
		return Random::$context;
	}

	public static function timestamp() {
		Random::$context='';
		$date = new DateTime();
		Random::$context = $date->getTimestamp();
		return Random::$context;
	}

}