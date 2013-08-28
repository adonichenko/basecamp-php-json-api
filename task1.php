#!/usr/bin/php5
<?php
/**
 * File: ./task1.php
 * 
 * @package library.1.0
 * Alexander Donichenko (adonichenko@gmail.com)
 */
require_once dirname(__FILE__) . '/library/runner.php';

//$argv[1] = 10557188;
if (!isset($argv[1])) {
	exit('Not exists ID project');
}
try {
	$bcapifinder = new Bcapifinder();
	//all current to do items for project
	$aritems = $bcapifinder->getAllTodoItems($argv[1]);
	// to do items in base
	$res = $bcapifinder->prepQuery('select * from todoitems', array());
	// prepare arrays for difference
	$arapi = array();
	$arbase = array();
	$aridmsg = array();
	foreach ($res as $row) {
		$arbase[$row['itemid']] = $row['itemhash'];
		$aridmsg[$row['itemid']] = $row['idmsg'];
	}
	foreach ($aritems as $iditem => $tdit) {
		$arapi[$iditem] = $tdit['fullhash'];
	}
	// change or add items
	$archange = array_diff_assoc($arapi, $arbase);
	// remove items
	$ardelete = array_diff_key($arbase, $arapi);

	print_r($archange);
	print_r($ardelete);

	foreach ($archange as $iditem => $hash) {
		$idmsg = null;
		// change
		if (isset($aridmsg[$iditem])) {
			$idmsg = $aridmsg[$iditem];
			$bcapifinder->createComment('posts', $idmsg, "To do item {$iditem} changed");
		} else {
			// add
			$idmsg = $bcapifinder->createMessageForProject($argv[1], "Msg for to do item {$iditem}");
			$bcapifinder->createComment('posts', $idmsg, "To do item {$iditem} added");
		}
		$res = $bcapifinder->prepQuery('
			INSERT INTO todoitems 
				SET itemid = :iditem, idmsg = :idmsg, itemhash = :hash 
				ON DUPLICATE KEY UPDATE  itemhash = :hash', array(
			 ':iditem' => $iditem,
			 'idmsg' => $idmsg,
			 ':hash' => $hash,
			 ));
	}

	foreach ($ardelete as $iditem => $hash) {
		// deleted
		$idmsg = $aridmsg[$iditem];
		if (isset($idmsg)) {
			$bcapifinder->createComment('posts', $idmsg, "To do item {$iditem} deleted");
		}

		$res = $bcapifinder->prepQuery('
			DELETE FROM todoitems 
				WHERE itemid = :iditem', array(
			 ':iditem' => $iditem,
			 ));
	}
} catch (Exception $exc) {
	echo $exc . "\n";
}
?>
