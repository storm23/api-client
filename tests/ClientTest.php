<?php
/**
 * @Author: catalisio
 * @Date:   2017-02-28 11:22:02
 * @Last Modified by:   Julien Goldberg
 * @Last Modified time: 2017-03-03 14:23:45
 */

namespace Catalisio\APIClient\Tests;

use PHPUnit\Framework\TestCase;
use Catalisio\APIClient\Tests\Httpbin;

class ClientTest extends TestCase
{
	public function testMakeGet()
	{
		$httpbin = new Httpbin;
		$res = $httpbin->makeGet();

		$this->assertEquals('https://httpbin.org/get', $res['url']);
	}

	public function testMakePut()
	{
		$httpbin = new Httpbin;
		$res = $httpbin->makePut(['test' => 'ok']);

		$this->assertEquals('ok', $res['json']['test']);
		$this->assertEquals('https://httpbin.org/put', $res['url']);
	}

	public function testMakePatch()
	{
		$httpbin = new Httpbin;
		$res = $httpbin->makePatch(['test' => 'ok']);

		$this->assertEquals('ok', $res['json']['test']);
		$this->assertEquals('https://httpbin.org/patch', $res['url']);
	}
}
