<?php

require_once('auth_digest.php');

$bucket1 = "phpsdk";
$key1 = 'demo';

$bucket2 = "phpsdk";
$key2 = "demo1";

$rs = new RSClient();

list($result, $code, $error) = $rs->Stat($bucket1, $key1);
echo time() . "===> Stat $key1 result:\n";
if ($code == 200) {
	var_dump($result);
} else {
	$msg = QBox_ErrorMessage($code, $error);
	echo "Stat failed: $code - $msg\n";
	exit(-1);
}


$params = array(new EntryPath($bucket1, $key1), new EntryPath($bucket2, $key2));
list($result, $code, $error) = $rs->BatchStat($params);
echo time() . " ===> BatchStat $key1 result:\n";
if ($code == 200) {
	var_dump($result);
} else {
	$msg = QBox_ErrorMessage($code, $error);
	echo "BatchGet failed: $code - $msg\n";
	exit(-1);
}
