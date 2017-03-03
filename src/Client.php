<?php
/**
 * @Author: catalisio
 * @Date:   2016-02-27 16:54:30
 * @Last Modified by:   Julien Goldberg
 * @Last Modified time: 2017-03-03 14:22:14
 */

namespace Catalisio\APIClient;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\TransferException;
use GuzzleHttp\Psr7\Response;

abstract class Client 
{
	private $httpClient;
	private $constantParams;
	
	public $errors;
	public $hasError;
	public $errorCode; 

	public function __construct() 
	{
		$headers = ['headers' => ['Accept' => 'application/json']];
		$this->httpClient = new GuzzleClient($headers);

		$this->initError();
	}

	abstract protected function getEndPoint();

	public function setConstantParams(array $constantParams)
	{
		$this->constantParams = $constantParams;
	}

	private function initError()
	{
		$this->errors = null;
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
			$response = $this->getBody($response);
		}
		catch (TransferException $e) {

			$errorResponse = $e->getResponse();
			$this->errorCode = $errorResponse->getStatusCode();
			$this->hasError = true;
			$this->errors = $this->getBody($errorResponse);
			$response = false;
		}

		return $response;
	}

	private function getBody(Response $response)
	{
		$body = $response->getBody();

		try {

			$jsonBody = json_decode($body, true);
		}
		catch (\Exception $e) {

			throw new \Exception('Response is not json format');
		}

		return $jsonBody;
	}

	protected function get($url, array $queryParams = null) 
	{
		return $this->makeCall('GET', $url, $queryParams);
	}

	protected function post($url, array $formParams, array $queryParams = null) 
	{
		return $this->makeCall('POST', $url, $queryParams, $formParams);
	}

	protected function put($url, array $formParams, array $queryParams = null) 
	{
		return $this->makeCall('PUT', $url, $queryParams, $formParams);
	}

	protected function patch($url, array $formParams, array $queryParams = null) 
	{
		return $this->makeCall('PATCH', $url, $queryParams, $formParams);
	}

	protected function delete($url, array $queryParams = null) 
	{
		return $this->makeCall('DELETE', $url, $queryParams);
	}

	protected function makeURL($url, array $queryParams = null) 
	{
		if (isset($this->constantParams)) {

			$queryParams = array_merge($queryParams, $this->constantParams);
		}
		
		if (isset($queryParams) && count($queryParams) > 0)	{

			$queryString = implode('&', array_map(
   														function ($v, $k) { return $k . '=' . $v; }, 
    													$queryParams, 
    													array_keys($queryParams)
			));
		}
		else {

			$queryString = '';
		}

		return sprintf("%s%s?%s", $this->getEndPoint(), $url, $queryString);
	}
}
