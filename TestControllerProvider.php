<?php

use Silex\Application as Application;
use Silex\ControllerProviderInterface as ControllerProviderInterface;

class TestControllerProvider implements ControllerProviderInterface {
	public function connect(Application $app) {
		$controllers = $app['controllers_factory'];
		
		$controllers->get('/', function() {
			return 'Test';
		});
		
		$controllers->get('/author', function() {
			return 'Khang Tran';
		});
		
		return $controllers;
	}
}

?>
