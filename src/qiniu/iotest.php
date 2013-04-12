<?php
require_once 'io.php';
require_once 'auth_token.php';

$bucket = "phpsdk";
$key = "demo3";


$putPolicy = new PutPolicy("phpsdk");
$upToken = $putPolicy->token();

$extra = new PutExtra();
$extra->bucket = $bucket;
put($upToken, $key, "rpc.php", $extra);