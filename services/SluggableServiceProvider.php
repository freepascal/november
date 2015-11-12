<?php

use Silex\Application as Application;
use Silex\ServiceProviderInterface as ServiceProviderInterface;

use Cocur\Slugify\Slugify as Slugify;

class SluggableServiceProvider implements ServiceProviderInterface {
	
	public function register(Application $app) {		
		$app['slugify'] = $app->share(function() {
			return new Slugify;
		});
	}
	
	public function boot(Application $app) {
	}
}

?>
