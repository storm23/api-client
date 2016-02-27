<?php
/**
 * @Author: catalisio
 * @Date:   2016-02-27 16:54:30
 * @Last Modified by:   Julien Goldberg
 * @Last Modified time: 2016-02-27 17:05:24
 */

namespace Catalisio\APIClient;

use GuzzleHttp\Client;

abstract class Client {

	private $httpClient;
	private $constantParams;

	public function __construct($constantParams) {

		$this->httpClient = new Client();
		$this->constantParams = $constantParams;
	}

	abstract protected function getEndPoint($endPoint);

	protected function get($url, $params) {

		$url = $this->makeURL($url, $params);
		$response = $this->httpClient->get($url);
		$body = $response->getBody();

		return json_decode($body, true);
	}

	protected function post($url, $params, $formParams) {

		$url = $this->makeURL($url, $params);
		$response = $this->httpClient->post($url, [ 'form_params' => $formParams ]);
		$body = $response->getBody();

		return json_decode($body, true);
	}

	private function makeURL($url, $params, $formParams) {

		$params = array_merge($params, $this->constantParams);

		if (count($params) > 0)	{

			$queryString = implode('&', array_map(
   														function ($v, $k) { return $k . '=' . $v; }, 
    													$params, 
    													array_keys($params)
			));
		}

		return sprintf("%s%s?%s", $this->endPoint, $url, $queryString);
	}
}
