<?php

require_once("config.php");
require_once("rpc.php");
require_once("utils.php");

class RSClient extends Client
{
	
	public function __construct()
	{	
	}
	
	/**
	 * Generate the QBOX signature
	 *
	 * @param string $url Called URL
	 * @param array  $parameters Parameters
	 */
	public function setAuth($url, &$httpHeader, &$parameters)
	{
		$parsed_url = parse_url($url);
		$path = $parsed_url['path'];
		$data = $path;
		if (isset($parsed_url['query'])) {
			$data .= "?" . $parsed_url['query'];
		}
		$data .= "\n";
	
		if ($parameters) {
			if (is_array($parameters)) {
				$parameters = http_build_query($parameters);									                       
			}
			$data .= $parameters;
		}
		$digest = URLSafeBase64Encode(hash_hmac('sha1', $data, SECRET_KEY, true));
		$digest = ACCESS_KEY . ":" .$digest;
		$httpHeader["Authorization"] = "QBox " . $digest;
		return;
	}
	
	public function stat($bucket, $key)
	{
		$url = RS_HOST . $this->URIStat($bucket, $key);
		return $this->call($url);	
	}
	
	public function delete($bucket, $key)
	{
		$url = RS_HOST . $this->URIDelete($bucket, $key);
		 return $this->callNoRet($url);
	}
	
	public function move($bucketSrc, $keySrc, $bucketDest, $keyDest)
	{
		$url = RS_HOST . $this->URIMove($bucketSrc, $keySrc, $bucketDest, $keyDest);
		return $this->callNoRet($url);
	}
	
	public function copy($bucketSrc, $keySrc, $bucketDest, $keyDest)
	{
		$url = RS_HOST . $this->URICopy($bucketSrc, $keySrc, $bucketDest, $keyDest);
		return $this->callNoRet($url);
	}	
	
	public function batch($params)
	{
		return $this->callWithForm(RS_HOST . '/batch', $params);
	}
	
	public function batchStat($entries)
	{
		$params = '';
		foreach ($entries as $entry) {
			if ($params == '') { 
				$params = 'op=' . $this->URIStat($entry->bucket, $entry->key);
				continue;
			}
			$params .= '&op=' . $this->URIStat($entry->bucket, $entry->key);
		}
		return $this->batch($params);
	}
	
	public function batchDelete($entries)
	{
		$params = '';
		foreach ($entries as $entry) {
			if ($params == '') {
				$params = 'op=' . $this->URIDelete($entry->bucket, $entry->key);
				continue;
			}
			$params .= '&op=' . $this->URIDelete($entry->bucket, $entry->key);
		}
		return $this->batch($params);
	}
	
	public function batchMove($entryPairs)
	{
		$params = '';
		foreach ($entryPairs as $entryPair) {
			if ($params == '') {
				$params = 'op=' . $this->URIMove($entryPair->src->bucket, $entryPair->src->key, $entryPair->dest->bucket, $entryPair->dest->key);
				continue;
			}
			$params .= '&op=' . $this->URIMove($entryPair->src->bucket, $entryPair->src->key, $entryPair->dest->bucket, $entryPair->dest->key);
		}
		return $this->batch($params);
	}
	
	public function batchCopy($entryPairs)
	{
		$params = '';
		foreach ($entryPairs as $entryPair) {
			if ($params == '') {
				$params = 'op=' . $this->URICopy($entryPair->src->bucket, $entryPair->src->key, $entryPair->dest->bucket, $entryPair->dest->key);
				continue;
			}
			$params .= '&op=' . $this->URICopy($entryPair->src->bucket, $entryPair->src->key, $entryPair->dest->bucket, $entryPair->dest->key);
		}
		return $this->batch($params);
	}
	
	public function URIStat($bucket, $key) 
	{
		return  '/stat/' . URLSafeBase64Encode("$bucket:$key");
	}
	
	public function URIDelete($bucket, $key)
	{
		return '/delete/' . URLSafeBase64Encode("$bucket:$key");
	}
	
	public function URIMove($bucketSrc, $keySrc, $bucketDest, $keyDest)
	{
		return '/move/' . URLSafeBase64Encode("$bucketSrc:$keySrc") . '/' . URLSafeBase64Encode("$bucketDest:$keyDest");
	}
	
	public function URICopy($bucketSrc, $keySrc, $bucketDest, $keyDest) 
	{
		return '/copy/' . URLSafeBase64Encode("$bucketSrc:$keySrc") . '/' . URLSafeBase64Encode("$bucketDest:$keyDest");
	}
	
}

class EntryPath
{
	public $bucket;
	public $key;
	public function __construct($bucket, $key)
	{
		$this->bucket = $bucket;
		$this->key = $key;
	}
}

class EntryPathPair
{
	public $src;
	public $dest;
	public function __construct($src, $dest)
	{
		$this->src = $src;
		$this->dest = $dest;
	}
}






