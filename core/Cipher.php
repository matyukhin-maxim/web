<?php

/**
 * Created by PhpStorm.
 * User: Матюхин_МП
 * Date: 25.05.2016
 * Time: 7:56
 */
class Cipher {

	public static $method = 'aes-256-ctr';

	public static function encode($data, $secret, $serialize = true) {

		if ($serialize) $data = serialize($data);

		return openssl_encrypt(
			$data,
			self::$method,
			$secret,
			OPENSSL_ZERO_PADDING,
			sha1('ngres')
		);
	}

	public static function decode($data, $secret, $serialize = true) {

		$plain = openssl_decrypt(
			$data,
			self::$method,
			$secret,
			OPENSSL_ZERO_PADDING,
			sha1('ngres')
		);

		return $serialize ? unserialize($plain) : $plain;
	}

	public static function generate_token($len = 32) {

		$chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
		//$chars = str_shuffle($chars);
		$nc = strlen($chars) - 1;

		$token = '';
		for ($idx = 0; $idx < $len; $idx++) $token .= $chars[mt_rand(0, $nc)];
		return $token;
	}
}