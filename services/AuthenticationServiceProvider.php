<?php

use Silex\Application as Application;
use Silex\ServiceProviderInterface as ServiceProviderInterface;

class AuthenticationServiceProvider implements ServiceProviderInterface {
	public function register(Application $app) {
		// who is him?
		//$app['authenticated'] = $app['session']
	}
	
	public function boot(Application $app) {
	}
}
?>
