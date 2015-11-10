<?php

use Silex\Application as Application;
use Silex\ControllerProviderInterface as ControllerProviderInterface;

class TestControllerProvider implements ControllerProviderInterface {
	public function connect(Application $app) {
		$controllers = $app['controllers_factory'];
		
		$controllers->get('/', function() use($app) {
			return sprintf(
				'<a href="%s">%s</a>',
				$app['url_generator']->generate('homepage'),
				'Home'
			);
		})->bind('homepage');
		
		$controllers->get('/author', function() {
			return 'Khang Tran';
		});
		
		return $controllers;
	}
}

?>
