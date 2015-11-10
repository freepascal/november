<?php

use Silex\Application as Application;
use Symfony\Component\HttpFoundation\Request as Request;
use Silex\ControllerProviderInterface as ControllerProviderInterface;

class UsersControllerProvider implements ControllerProviderInterface {
	
	public function connect(Application $app) {
		$controllers = $app['controllers_factory'];
		
		$controllers->get('/login', function() use($app) {
			return $app['twig']->render('login.html.twig', array(
			));
		})->bind('users_login');
		
		$controllers->get('/logout', function() use($app) {
			$app['session']->clear();
			$app['session']->getFlashBag()->add('msg', 'You are logged out');
			return $app->redirect(
				$app['url_generator']->generate('users_login')
			);
		})->bind('users_logout');
		
		$controllers->post('/login', function(Request $request) use($app) {
			$username = trim($request->get('username'));
			$password = trim($request->get('password'));
			
			$query = "SELECT november_users.id, username, password, group_concat(november_roles.title) "
				."FROM november_users, november_roles, november_user_roles "
				."WHERE november_users.id = november_user_roles.user_id "
				."AND november_roles.id = november_user_roles.role_id "
				."AND username = ?";
	
			$user = $app['db']->fetchAssoc($query, array(strtolower($username)));
			
			$logged_in = $user? password_verify($password, $user['password']): false;	
	
			if ($logged_in) {
				$app['session']->set('user', $user);	
				
				// redirect to homepage
				return $app->redirect($app['ROOT_DIR']. '/');	
					
			} else {
				$app['session']->set('user', null);
				$app['session']->getFlashBag()->add('msg', 'Login failed');
				return $app->redirect(
					$app['url_generator']->generate('users_register')
				);
			}						
		});
		
		$controllers->get('/register', function() use($app) {
			$app['captcha.builder']->build();			
			$app['session']->set('captcha.register', $app['captcha.builder']->getPhrase());				
			return $app['twig']->render('register.html.twig', array(
				'captcha_image' =>	$app['captcha.builder']->inline() 
			));
		})->bind('users_register');
		
		$controllers->post('/register', function(Request $request) use($app) {
			$pwd = $request->get('password');
			$pwd_again = $request->get('password_again');						
			if ($pwd != $pwd_again) {
				$app['session']->getFlashBag()->add('msg', 'Passwords not match!');
				return $app->redirect(
					$app['url_generator']->generate('users_register')
				);
			}
			
			if ($app['session']->get('captcha.register') !== $request->get('captcha_register')) {
				$app['session']->getFlashBag()->add('msg', 'Wrong captcha');
				return $app->redirect(
					$app['url_generator']->generate('users_register')
				);
			}
			
			$app['db']->insert('november_users', array(
				'username' 	=> $request->get('username'),				
				'password'	=> password_hash($request->get('password'), PASSWORD_BCRYPT)				
			));
						
			return $app->redirect(
				$app['url_generator']->generate('posts_index')
			);
		});
				
		return $controllers;
	}
	
	public static function fetchUser(Application $app) {
		if (isset($app['session']->get('user')['username'])) {
			$query = "SELECT november_users.id, username, password, group_concat(november_roles.title) "
				."FROM november_users, november_roles, november_user_roles "
				."WHERE november_users.id = november_user_roles.user_id "
				."AND november_roles.id = november_user_roles.role_id "
				."AND username = ?";
				
			$user = $app['db']->fetchAssoc(
				$query, 
				array(
					strtolower(
						$app['session']->get('user')['username']
					)
				)
			);
			if (!$user) {
				$app['session']->clear();
			}			
		}		
	}
}

?>
