<?php

require_once('auth_digest.php');

$bucket1 = "phpsdk";
$key1 = 'demo';

$bucket2 = "phpsdk";
$key2 = "demo1";

$rs = new RSClient();

list($result, $error) = $rs->Stat($bucket1, $key1);
echo time() . "\n===> Stat $key1 result:\n";

var_dump($result);
var_dump($error);


// $params = array(new EntryPath($bucket1, $key1), new EntryPath($bucket2, $key2));
// list($result, $error) = $rs->BatchStat($params);
// echo time() . " ===> BatchStat $key1 result:\n";

// var_dump($result);

// var_dump($error);

