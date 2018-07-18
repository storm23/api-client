<?php
/**
 * @Author: Julien Goldberg
 * @Date:   2016-02-27 16:54:30
 * @Last Modified by:   Julien Goldberg
 * @Last Modified time: 2017-05-12 15:52:02
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
	private $verifySSL;
    private $timeout = 0;

	public $errors;
	public $hasError;

	public function __construct($endpoint, $verifySSL = true)
	{
		$this->endpoint = $endpoint;
		$headers = ['headers' =>
                        ['Accept' => 'application/json'],
                        ['Content-Type' => 'application/json']
                    ];
		$this->httpClient = new GuzzleClient($headers);
		$this->verifySSL = $verifySSL;

		$this->initError();
	}

    public function setConstantHeaders(array $headers)
    {
        $this->constantHeaders = $headers;
    }

	public function setConstantParams(array $constantParams)
	{
		$this->constantParams = $constantParams;
	}

    public function setTimeout($timeout)
    {
        $this->timeout = $timeout;
    }

	private function initError()
	{
		$this->errors = [];
		$this->hasError = false;
		$this->errorCode = null;
	}

	private function makeCall($verb, $url, array $queryParams = null, array $formParams = null, array $multipart = null)
	{
		$this->initError();

		$url = $this->makeURL($url, $queryParams);
		$params = [];
		if (isset($formParams) && !empty($formParams)) {

			$params['json'] = $formParams;
		}

        if (isset($multipart)  && !empty($multipart)) {

            $params['multipart'] = $multipart;
        }

		$params = $this->addParams($params);

		try {

			$response = $this->httpClient->request($verb, $url, $params);
		}
		catch (RequestException $e) {

            if ($e->getResponse() == null) {

                $this->addError(500, $e->getMessage());
                $response = false;
            }
            else {

                $this->addError($e->getResponse()->getStatusCode(), $e->getResponse()->getBody()->getContents());
    			$response = false;
            }
		}
		catch (TransferException $e) {

			$this->addError($e->getStatusCode(), $e->getResponse());
			$response = false;
		}

		return $response;
	}

	private function addParams(array $params)
	{
		if (!$this->verifySSL) {

			$params['verify'] = false;
		}

        if (isset($this->constantHeaders)) {

            $params['headers'] = $this->constantHeaders;
        }

        if ($this->timeout > 0) {

            $params['timeout'] = $this->timeout;
        }

		return $params;
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

	public function post($url, array $formParams, array $queryParams = null, array $multipart = null)
	{
		return $this->makeCall('POST', $url, $queryParams, $formParams, $multipart);
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
