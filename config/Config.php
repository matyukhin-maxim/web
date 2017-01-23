<?php

class Configuration {

	public static $scriptList = [
		'lib/jquery.min',
		'lib/jquery-ui.min',
		'lib/jquery.form.min',
		'lib/jquery.cookie',
		'lib/bootstrap.min',
		'lib/bootstrap-slider.min',
		'lib/moment.min',
		'lib/i18n/moment-ru',               // rus moment.js
		'lib/bootstrap-select.min',
		'lib/bootstrap-datetimepicker.min', // date & time picker
		'lib/jquery.bootstrap-growl',
		'lib/i18n/defaults-ru_RU',          // rus selectpicker
		'lib/ie10-viewport-bug-workaround', // IE10 viewport hack for Surface/desktop Windows 8 bug
		'common',
	];

	public static $connection = [
		'host' => 'localhost',
		'user' => 'root',
		'pass' => 'fell1x',
		//'user' => 'matyukhin',
		//'pass' => 'ksTg3276sm@',
		'base' => 'dummy',
	];

	public static $siteName  = 'Разгадай-ка';
	public static $brandName = 'OneMoreTry :)';
	public static $fuckIT = 'WhoAreYou';

}