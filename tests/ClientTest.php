<?php
/**
 * @Author: Julien Goldberg
 * @Date:   2017-02-28 11:22:02
 * @Last Modified by:   Julien Goldberg
 * @Last Modified time: 2017-03-23 11:38:04
 */

namespace APIClient\Tests;

use PHPUnit\Framework\TestCase;
use APIClient\Client;

class ClientTest extends TestCase
{
	public function testMakeGet()
	{
		$httpbin = new Client('https://httpbin.org');
		$res = $httpbin->get('/get');

		$this->assertEquals(200, $res->getStatusCode());
		$res = json_decode($res->getBody(), true);
		$this->assertEquals('https://httpbin.org/get', $res['url']);
	}

	public function testMakePut()
	{
		$httpbin = new Client('https://httpbin.org');
		$res = $httpbin->put('/put', ['test' => 'ok']);
		
		$this->assertEquals(200, $res->getStatusCode());
		$res = json_decode($res->getBody(), true);
		$this->assertEquals('ok', $res['json']['test']);
		$this->assertEquals('https://httpbin.org/put', $res['url']);
	}

	public function testMakePatch()
	{
		$httpbin = new Client('https://httpbin.org');
		$res = $httpbin->patch('/patch', ['test' => 'ok']);

		$this->assertEquals(200, $res->getStatusCode());
		$res = json_decode($res->getBody(), true);
		$this->assertEquals('ok', $res['json']['test']);
		$this->assertEquals('https://httpbin.org/patch', $res['url']);
	}

	public function testMakeStatus500()
	{
		$httpbin = new Client('https://httpbin.org');
		$res = $httpbin->get('/status/500');

		$this->assertEquals(false, $res);
		$this->assertEquals(500, $httpbin->errors[0]['code']);
	}

	public function testMalformedUrl()
	{
		$client = new Client('');
		$res = $client->get('/get');

		$this->assertEquals(false, $res);
		$this->assertEquals(500, $client->errors[0]['code']);
	}
}
