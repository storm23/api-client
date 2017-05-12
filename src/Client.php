<?php
/**
 * @Author: Julien Goldberg
 * @Date:   2016-02-27 16:54:30
 * @Last Modified by:   Julien Goldberg
 * @Last Modified time: 2017-05-12 14:47:59
 */

namespace APIClient;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\TransferException;
use GuzzleHttp\Exception\RequestException;

class Client 
{
	private $httpClient;
	private $constantParams;
	private $endpoint;
		
	public $errors;
	public $hasError;

	public function __construct($endpoint, $verifySSL = true) 
	{
		$this->endpoint = $endpoint;
		$headers = ['headers' => ['Accept' => 'application/json']];
		$this->httpClient = new GuzzleClient($headers);
		$this->httpClient->setDefaultOption('verify', $verifySSL);

		$this->initError();
	}

	public function setConstantParams(array $constantParams)
	{
		$this->constantParams = $constantParams;
	}

	private function initError()
	{
		$this->errors = [];
		$this->hasError = false;
		$this->errorCode = null; 
	}

	private function makeCall($verb, $url, array $queryParams = null, array $formParams = null)
	{
		$this->initError();

		$url = $this->makeURL($url, $queryParams);
		$params = [];
		if (isset($formParams)) {

			$params['json'] = $formParams;
		}

		try {

			$response = $this->httpClient->request($verb, $url, $params);
		}
		catch (RequestException $e) {

			$this->addError(500, $e->getMessage());
			$response = false;
		}
		catch (TransferException $e) {

			$this->addError($errorResponse->getStatusCode(), $e->getResponse());
			$response = false;
		}

		return $response;
	}

	private function addError($errorCode, $errorMessage)
	{
		$this->hasError = true;
		$this->errors[] = [
			'code' => $errorCode,
			'message' => $errorMessage
		];
	}

	public function get($url, array $queryParams = null) 
	{
		return $this->makeCall('GET', $url, $queryParams);
	}

	public function post($url, array $formParams, array $queryParams = null) 
	{
		return $this->makeCall('POST', $url, $queryParams, $formParams);
	}

	public function put($url, array $formParams, array $queryParams = null) 
	{
		return $this->makeCall('PUT', $url, $queryParams, $formParams);
	}

	public function patch($url, array $formParams, array $queryParams = null) 
	{
		return $this->makeCall('PATCH', $url, $queryParams, $formParams);
	}

	public function delete($url, array $queryParams = null) 
	{
		return $this->makeCall('DELETE', $url, $queryParams);
	}

	public function makeURL($url, array $queryParams = null) 
	{
		if (!isset($queryParams)) {

			$queryParams = [];
		}

		if (isset($this->constantParams)) {

			$queryParams = array_merge($queryParams, $this->constantParams);
		}
		
		if (count($queryParams) > 0)	{

			$queryString = '?' . implode('&', array_map(
   														function ($v, $k) { return $k . '=' . $v; }, 
    													$queryParams, 
    													array_keys($queryParams)
			));
		}
		else {

			$queryString = '';
		}

		return sprintf("%s%s%s", $this->endpoint, $url, $queryString);
	}
}
