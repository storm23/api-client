<?php
/**
 * @Author: catalisio
 * @Date:   2017-02-28 11:27:33
 * @Last Modified by:   Julien Goldberg
 * @Last Modified time: 2017-03-03 14:22:47
 */
namespace Catalisio\APIClient\Tests;

use Catalisio\APIClient\Client;

class Httpbin extends Client
{
	protected function getEndPoint()
	{
		return 'https://httpbin.org';
	}

	public function makeGet()
	{
		$url = '/get';

		return $this->get($url);
	}

	public function makePut($formParams)
	{
		$url = '/put';

		return $this->put($url, $formParams);
	}

	public function makePatch($formParams)
	{
		$url = '/patch';

		return $this->patch($url, $formParams);
	}
}