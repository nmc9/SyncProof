<?php

namespace Tests\Proof;

use Orchestra\Testbench\TestCase as BaseTestCase;

abstract class TestCaseN extends BaseTestCase
{
	protected function getEnvironmentSetUp($app){
		parent::setup();
		$app['config']->set('database.default', 'remote');
		$app['config']->set('database.connections.remote', [
			'driver'   => 'sqlite',
			'database' => ':memory:',
			'prefix'   => 'remote',
		]);
		$app['config']->set('database.connections.local', [
			'driver'   => 'sqlite',
			'database' => ':memory:',
			'prefix'   => 'local',
		]);
	}
}
