<?php

/**
 * File: ./example.datafinder.php
 * 
 * @package library.1.0
 * Alexander Donichenko (adonichenko@gmail.com)
 */
require_once dirname(dirname(__DIR__)) . '/runner.php';

//$argv[1] = 'url';
$argv[1] = 'db';
try {
	$st = microtime(true);
	$stmem = memory_get_usage();
	echo '<pre>';

	$datafinder = new Datafinder();
	if ($argv[1] == 'url') {
		$datafinder->urlOpen(array('google.com'), null, array(
			 array(
				  'followlocation' => true,
			 )
		));
		print_r($datafinder->urlopen);
	} elseif ($argv[1] == 'db') {
		$res = $datafinder->prepQuery('select * from todoitems', array());
		print_r($res);
	}

	echo 'Run times: ' . round((microtime(true) - $st), 1) . "s <br /> \n";
	echo 'Used memory: ' . round((memory_get_usage() - $stmem) / (1024 * 1024), 1) . 'Mb | All '
	. round(memory_get_peak_usage(true) / (1024 * 1024), 1) . "Mb <br /> \n";
} catch (Exception $exc) {
	echo $exc . "\n<br>";
}
?>
