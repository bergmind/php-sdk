<?php


class Client 
{
	/**
	 * HTTP Methods
	 */
	const HTTP_METHOD_GET    = 'GET';
	const HTTP_METHOD_POST   = 'POST';
	const HTTP_METHOD_PUT    = 'PUT';
	const HTTP_METHOD_DELETE = 'DELETE';
	const HTTP_METHOD_HEAD   = 'HEAD';

	/**
	 * HTTP Form content types
	 */
	const HTTP_FORM_CONTENT_TYPE_APPLICATION = 0;
	const HTTP_FORM_CONTENT_TYPE_MULTIPART = 1;
	
	public $httpMethodPost = self::HTTP_METHOD_POST;

	
	public $httpFormContentTypeApplication = self::HTTP_FORM_CONTENT_TYPE_APPLICATION;
	public $httpFormContentTypeMultipart = self::HTTP_FORM_CONTENT_TYPE_MULTIPART;
	
	
	
	public function __construct()
	{

	}
	
	
	/**
	 * func call(url string) => (result array, code int, err Error)
	 */
	function call($url) {
		$response = $this->executeRequestSafely($url, array(), $this->httpMethodPost, array(), $this->httpFormContentTypeApplication);
	
		$code = $response['code'];
		if ($code === 200) {
			return array($response['result'], 200, null);
		}
		return array(null, $code, $response['result']);
	}
	
	/**
	 * func callNoRet(url string) => (code int, err Error)
	 */
	function callNoRet($url) {
		try {
			$response = $this->executeRequestSafely($url, array(), $this->httpMethodPost, array(), $this->httpFormContentTypeApplication);
		} catch (Exception $e) {
			echo $e->getMessage()."\n";
			echo $e->getCode()."\n";
			die;
		}
	
		$code = $response['code'];
		if ($code === 200) {
			return array(200, null);
		}
		return array($code, $response['result']);
	}
	

	/**
	 * func callWithParams(url string, params stringOrArray) => (result array, code int, err Error)
	 */
	function callWithForm($url, $params) {
	
		$response = $this->executeRequestSafely($url, $params, $this->httpMethodPost, array(), $this->httpFormContentTypeApplication);
		$code = $response['code'];
		if ($code === 200 || $code === 298) {
			return array($response['result'], $code, null);
		}
		return array(null, $code, $response['result']);
	}
	
	
	/**
	 * func callWithParamsNoRet(url string, params stringOrArray) => (code int, err Error)
	 */
	function callWithFormNoRet($url, $params) {
	
		$response = $this->executeRequestSafely($url, $params, $this->httpMethodPost, array(), $this->httpFormContentTypeApplication);
		$code = $response['code'];
		if ($code === 200) {
			return array(200, null);
		}
		return array($code, $response['result']);
	}
	
	function callWith($url, $bodyType, $body, $bodyLength)
	{
		$httpHeaders = array("Content-Type: $bodyType");
		$curlOptions = array(
				CURLOPT_UPLOAD => true,
				CURLOPT_INFILE => $body,
				CURLOPT_INFILESIZE => $bodyLength
		);
		$response = $this->executeRequestSafely(
				$url, array(), $this->httpMethodPost, $httpHeaders, $this->httpFormContentTypeApplication, $curlOptions);
		
		$code = $response['code'];
		if ($code === 200) {
			return array($response['result'], 200, null);
		}
		return array(null, $code, $response['result']);		
	}
	
	
	/**
	 * func CallWithBinary(url string, fp File, bytes int64, timeout int) => (result array, code int, err Error)
	 */
	function callWithBinary($url, $fp, $bytes, $timeout) {
		$http_headers = array('Content-Type: application/octet-stream');
		$curl_options = array(
				CURLOPT_UPLOAD => true,
				CURLOPT_INFILE => $fp,
				CURLOPT_INFILESIZE => $bytes,
				CURLOPT_TIMEOUT_MS => $timeout
		);
		$response = $this->executeRequestSafely(
				$url, array(), $this->httpMethodPost, $http_headers, $this->httpFormContentTypeApplication, $curl_options);
		//var_dump($response);
	
		$code = $response['code'];
		if ($code === 200) {
			return array($response['result'], 200, null);
		}
		return array(null, $code, $response['result']);
	}
		
	
	public function setAuth($url, $http_header, $parameters) 
	{
	}
	
    /**
     * Execute a request safely (with curl)
     *
     * @param string $url URL
     * @param mixed  $parameters Array of parameters
     * @param string $http_method HTTP Method
     * @param array  $http_header HTTP Headers
     * @param int    $form_content_type HTTP form content type to use
     * @return array
     */
    private function executeRequestSafely($url, $parameters = '', $http_method = self::HTTP_METHOD_GET, $http_header = null, $form_content_type = self::HTTP_FORM_CONTENT_TYPE_MULTIPART, $curl_extra_options = null)
    {
    	$this->setAuth($url, $http_header, $parameters);
    	error_log(print_r($http_header, true));
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
            case self::HTTP_METHOD_POST:
                $curl_options[CURLOPT_POST] = true;
                /* No break */
            case self::HTTP_METHOD_PUT:
                /**
                 * Passing an array to CURLOPT_POSTFIELDS will encode the data as multipart/form-data,
                 * while passing a URL-encoded string will encode the data as application/x-www-form-urlencoded.
                 * http://php.net/manual/en/function.curl-setopt.php
                 */
                if (!isset($curl_options[CURLOPT_UPLOAD])) {
                    if (self::HTTP_FORM_CONTENT_TYPE_APPLICATION === $form_content_type) {
                        if (is_array($parameters))
                            $parameters = http_build_query($parameters);
                    }
                    $curl_options[CURLOPT_POSTFIELDS] = $parameters;
                }
                break;
            case self::HTTP_METHOD_HEAD:
                $curl_options[CURLOPT_NOBODY] = true;
                /* No break */
            case self::HTTP_METHOD_DELETE:
            case self::HTTP_METHOD_GET:
                $url .= '?' . http_build_query($parameters, null, '&');
                break;
            default:
                break;
        }

        if (is_array($http_header))
        {
            $header = array();
            foreach($http_header as $key => $parsed_urlvalue) {
                $header[] = "$key: $parsed_urlvalue";
            }
            $curl_options[CURLOPT_HTTPHEADER] = $header;
        }

        $curl_options[CURLOPT_URL] = $url;

        $ch = curl_init();
        curl_setopt_array($ch, $curl_options);
        $result = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $content_type = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
        curl_close($ch);

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
	
}

function SiginJson($key, $secret, $data)
{
	$encodedData = URLSafeBase64Encode(json_encode($data));
	$checksum = hash_hmac('sha1', $scope, $secret, true);
	$encodedChecksum = QBox_Encode($checksum);

	return "$key:$encodedChecksum:$encodedData";
}