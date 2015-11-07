<?php

use Silex\Application as Application;
use Silex\ControllerProviderInterface as ControllerProviderInterface;
use Symfony\Component\HttpFoundation\Request as Request;

class PostsControllerProvider implements ControllerProviderInterface {
	public function connect(Application $app) {
		
		$controllers = $app['controllers_factory'];
		
		$controllers->get('/create', function() use($app) {
			return $app['twig']->render('create.html.twig');
		});
		
		$controllers->post('/create_cgi', function(Request $request) use($app) {
			$res = $app['db']->insert('november_posts', array(
				'title' 	=> $request->get('title'),
				'body'		=> $request->get('body'),
				'user_id' 	=> $app['session']->get('user')['id']
			));
	
			if ($res) {	
				$app['session']->getFlashBag()->add('msg', 'Add post successfully');
				return $app->redirect($app['ROOT_DIR']. '/');
			} else {
				$app['session']->getFlashBag()->add('msg', 'Can not add your post');
				return $app->redirect($app['ROOT_DIR']. '/post/create');
			}
		});
				
		return $controllers;
	}
}

?>
