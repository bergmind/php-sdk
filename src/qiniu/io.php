<?php
/**
 * HTTP Methods
 */
define('QBOX_HTTP_METHOD_GET',    'GET');
define('QBOX_HTTP_METHOD_POST',   'POST');
define('QBOX_HTTP_METHOD_PUT',    'PUT');
define('QBOX_HTTP_METHOD_DELETE', 'DELETE');
define('QBOX_HTTP_METHOD_HEAD',   'HEAD');


define('QBOX_HTTP_FORM_CONTENT_TYPE_APPLICATION', 0);
define('QBOX_HTTP_FORM_CONTENT_TYPE_MULTIPART', 1);

class PutExtra
{
	public $callbackParams;
	public $bucket;
	public $customMeta;
	public $mimeType;
}

function put($upToken, $key, $body, $extra) 
{
	$httpHeaders = array('Content-Type: application/octet-stream');
	$fInfo = stat($body);
	$curlExtraOpts = array(
			CURLOPT_UPLOAD => true,
			CURLOPT_PUT => true,
			CURLOPT_INFILE => fopen($body, "r"),
			CURLOPT_INFILESIZE => $fInfo['size']
	);
	$entryURI = $extra->bucket . ':' . $key;
	$action = '/rs-put/' . URLSafeBase64Encode($entryURI);
	
	if (isset($extra->mimeType) && $extra->mimeType !== '') {
		$action .= '/mimeType/' . URLSafeBase64Encode($extra->mimeType);
	}
	if (isset($extra->customMeta) && $extra->customMeta !== '') {
		$action .= '/meta/' . URLSafeBase64Encode($extra->customMeta);
	}

	$params = array('action' => $action, 'auth' => $upToken);
	if (isset($extra->callbackParams) && $extra->callbackParams !== '') {
		if (is_array($extra->callbackParams)) {
			$extra->callbackParams = http_build_query($extra->callbackParams);
		}
		$params['params'] = $extra->callbackParams;
	}

	error_log(print_r($params, true));
	$response = QBox_ExecuteRequest(UP_HOST . '/upload', $params, QBOX_HTTP_METHOD_POST, $httpHeaders, QBOX_HTTP_FORM_CONTENT_TYPE_MULTIPART, $curlExtraOpts);
	
	$code = $response['code'];
	if ($code === 200) {
		return array($response['result'], 200, null);
	}
	return array(null, $code, $response['result']);	
}

function putFile($upToken, $key, $localFile, $extra)
{
	
}

function getUrl($domain, $key, $dnToken)
{
	return "$domain/$key" . '?token=' . $dnToken;
}

function QBox_ExecuteRequest(
		$url,
		array $parameters = array(),
		$http_method = QBOX_HTTP_METHOD_GET,
		$http_headers = null,
		$form_content_type = QBOX_HTTP_FORM_CONTENT_TYPE_MULTIPART,
		$curl_extra_options = null,
		$i = 0
)
{
	$curl_options = array(
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_SSL_VERIFYPEER => true,
			CURLOPT_CUSTOMREQUEST  => $http_method
	);
	if (!empty($curl_extra_options)) {
		foreach ($curl_extra_options as $k => $v)
			$curl_options[$k] = $v;
	}

	switch($http_method)
	{
		case QBOX_HTTP_METHOD_POST:
			$curl_options[CURLOPT_POST] = true;
			/* No break */
		case QBOX_HTTP_METHOD_PUT:
			/**
			 * Passing an array to CURLOPT_POSTFIELDS will encode the data as multipart/form-data,
			 * while passing a URL-encoded string will encode the data as application/x-www-form-urlencoded.
			 * http://php.net/manual/en/function.curl-setopt.php
			 */
	
				$curl_options[CURLOPT_POSTFIELDS] = $parameters;
			echo "here";
			break;
		case QBOX_HTTP_METHOD_HEAD:
			$curl_options[CURLOPT_NOBODY] = true;
			/* No break */
		case QBOX_HTTP_METHOD_DELETE:
		case QBOX_HTTP_METHOD_GET:
			$url .= '?' . http_build_query($parameters, null, '&');
			break;
		default:
			break;
	}

	if (is_array($http_headers))
	{
		$header = array();
		foreach($http_headers as $key => $parsed_urlvalue) {
			$header[] = "$key: $parsed_urlvalue";
		}
		$curl_options[CURLOPT_HTTPHEADER] = $header;
	}

	$curl_options[CURLOPT_URL] = $url;
	$ch = curl_init();
	curl_setopt_array($ch, $curl_options);
	echo "1111";
	var_dump($curl_options);
	$result = curl_exec($ch);
	echo "2222";
	$errno = curl_errno($ch);

	echo "error........: $errno \n";
	$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
	$content_type = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
	curl_close($ch);

	if ($errno > 0) {
		echo "nnn";
		echo "error........: $errno \n";
		
	}

	if ($content_type === "application/json") {
		$json_decode = json_decode($result, true);
	} else {
		$json_decode = null;
	}
	return array(
			'result' => (null === $json_decode) ? $result : $json_decode,
			'code' => $http_code,
			'content_type' => $content_type
	);
}

