<?php

/**
 * File: ./example.bcapifinder.php
 * 
 * @package library.1.0
 * Alexander Donichenko (adonichenko@gmail.com)
 */
require_once dirname(dirname(__DIR__)) . '/runner.php';

//$argv[1] = 'todoitems';
//$argv[1] = 'sql';
//$argv[1] = 'creacomm';
$argv[1] = 'creamsg';
try {
	$st = microtime(true);
	$stmem = memory_get_usage();
	echo '<pre>';

	$bcapifinder = new Bcapifinder();
	if ($argv[1] == 'todoitems') {
		$arprojs = $bcapifinder->getProjects();
		print_r($arprojs);
		// todo items for all projects ( <= 25)
		$aritems = $bcapifinder->getAllTodoItems();
		print_r($aritems);
		
		print_r($bcapifinder->getMessage(76717967));
		
	} elseif ($argv[1] == 'creacomm') {
		$idcomm = $bcapifinder->createComment('posts', 76717967);
		print_r($bcapifinder->getMessage(76717967));		
		print_r($bcapifinder->deleteComment($idcomm));
		print_r($bcapifinder->getMessage(76717967));
		
	} elseif ($argv[1] == 'creamsg') {
		$idmsg = $bcapifinder->createMessageForProject(10557188);
		print_r($bcapifinder->getMessage($idmsg));		
		print_r($bcapifinder->deleteMessage($idmsg));
		$aret = $bcapifinder->getMessage($idmsg);
		if (isset($aret)) {
			print_r($aret);
		} else {
			echo "Сообщение {$idmsg} не обнаружено \n";
		}
		
	} elseif ($argv[1] == 'sql') {
		$res = $bcapifinder->prepQuery('select * from todoitems', array());
		print_r($res);
	}

	echo 'Run times: ' . round((microtime(true) - $st), 1) . "s <br /> \n";
	echo 'Used memory: ' . round((memory_get_usage() - $stmem) / (1024 * 1024), 1) . 'Mb | All '
	. round(memory_get_peak_usage(true) / (1024 * 1024), 1) . "Mb <br /> \n";
} catch (Exception $exc) {
	echo $exc . "\n<br>";
}
?>
