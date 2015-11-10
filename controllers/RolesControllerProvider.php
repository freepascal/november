<?php

use Silex\Application as Application;
use Silex\ControllerProviderInterface as ControllerProviderInterface;
use Symfony\Component\HttpFoundation\Request as Request;

class RolesControllerProvider implements ControllerProviderInterface {
	public function connect(Application $app) {
		$controllers = $app['controllers_factory'];
		
		$controllers->get('/create', function() use($app) {
			
		});
		
		return $controllers;
	}
	
	public static function addUser($user_id) {
		
	}
}

?>
