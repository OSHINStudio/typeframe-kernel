<?php

class WebRequest {
	private $_url;
	private $_method;
	private $_parameters;
	private $_headers;
	private $_response;
	private $_responseCode;
	private $_responseHeaders;
	private $_contentType;
	private $_requested;
	public function __construct($url, $method = 'get', array $parameters = array(), array $headers = array()) {
		$this->_url = $url;
		$this->_method = strtolower($method);
		$this->_parameters = $parameters;
		$this->_headers = $headers;
		$this->_response = null;
		$this->_responseCode = null;
		$this->_contentType = null;
		$this->_requested = false;
	}
	/**
	 * Send the request.
	 */
	public function send() {
		$this->_request();
	}
	/**
	 * Get the body of the response. This function will send the request if
	 * it has not been sent yet.
	 * @return string|array|null Usually a string, but responses with a content
	 * type of application/json return an associative array.
	 */
	public function response() {
		$this->_request();
		return $this->_response;
	}
	/**
	 * Get the content type of the response. This function will send the request
	 * if it has not been sent yet.
	 * @return string The content type.
	 */
	public function contentType() {
		$this->_request();
		return $this->_contentType;
	}
	public function responseCode() {
		$this->_request();
		return $this->_responseCode;
	}
	public function responseHeader($key) {
		$this->_request();
		foreach ($this->_responseHeaders as $line) {
			if (strtolower(substr($line, 0, strlen($key) + 1)) == strtolower($key) . ':') {
				return trim(substr($line, strlen($key) + 1));
			}
		}
		return false;
	}
	private function _request() {
		// Request is performed once per object.
		if (!$this->_requested) {
			$parameterString = '';
			foreach ($this->_parameters as $key => $value) {
				$parameterString .= '&' . urlencode($key) . '=' . urlencode($value);
			}
			$parameterString = substr($parameterString, 1);
			$curl = curl_init();
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
			if ($this->_method == 'post') {
				curl_setopt($curl, CURLOPT_POST, true);
				curl_setopt($curl, CURLOPT_POSTFIELDS, $parameterString);
			} else {
				if ($parameterString) {
					$this->_url = $this->_url . (strpos($this->_url, '?') === false ? '?' . $parameterString : '&' . $parameterString);
				}
			}
			curl_setopt($curl, CURLOPT_URL, $this->_url);
			curl_setopt($curl, CURLOPT_HTTPHEADER, $this->_headers);
			curl_setopt($curl, CURLOPT_HEADER, true);
			//curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
			$returned = curl_exec($curl);
			if (!curl_error($curl)) {
				$parts = explode("\r\n\r\n", $returned, 2);
				$this->_responseHeaders = explode("\r\n", $parts[0]);
				$response = @$parts[1];
				$type = curl_getinfo($curl, CURLINFO_CONTENT_TYPE);
				if (strpos($type, ';') !== false) {
					$parts = explode(';', $type);
					$type = trim($parts[0]);
				}
				$this->_contentType = $type;
				switch ($type) {
					case 'application/json':
						$this->_response = json_decode($response, true);
						break;
					default:
						$this->_response = $response;
				}
				$this->_responseCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
			}
			curl_close($curl);
			$this->_requested = true;
		}
	}
}
