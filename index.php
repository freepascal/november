<?php

require_once('vendor/autoload.php');
require_once('bootstrap.php');

use Symfony\Component\HttpFoundation\Request as Request;
	
$app->match('/', function() use($app) {	
	return $app['twig']->render('index.html.twig', array(
		'logged_in' => $app['session']->get('user')? true: false,
		'username'	=> $app['session']->get('user')['username'],
		'title' 	=> 'Homepage'
	));
})
->method('get');

$app->get('/users', function() use($app) {
	return $app['twig']->render('index.html.twig', array(
		'title' 	=> 'Index',
		'logged_in' => true
	));
});

$app->get('/generate_passwd', function() use($app) {
	return $app['twig']->render('generate_passwd.html.twig', array());
});

$app->post('/generate_passwd', function(Request $request) use($app) {
	return sprintf("%s",
		password_hash($request->get('password'), PASSWORD_BCRYPT)
	);
});

// Login && logout
$app->get('/login', function() use($app) {
	return $app['twig']->render('login.html.twig', array());
});

$app->post('/login', function(Request $request) use($app) {
	$username = trim($request->get('username'));
	$password = trim($request->get('password'));
	
	$query = "SELECT november_users.id, username, password, group_concat(november_roles.title) "
			."FROM november_users, november_roles, november_user_role "
			."WHERE november_users.id = november_user_role.user_id "
			."AND november_roles.id = november_user_role.role_id "
			."AND username = ?";
	
	$user = $app['db']->fetchAssoc($query, array(strtolower($username)));
	
	$logged_in = $user? password_verify($password, $user['password']): false;	
	
	if ($logged_in) {
		$app['session']->set('user', $user);	
		return $app->redirect($app['ROOT_DIR']. '/');		
	} else {
		$app['session']->set('user', null);
		$app['session']->getFlashBag()->add('msg', 'Login failed');
		return $app->redirect($app['ROOT_DIR']. '/login');
	}
});

$app->get('/logout', function() use($app) {
	$app['session']->clear();	
	$app['session']->getFlashBag()->add('msg', 'You are logged out'); 
	return $app->redirect($app['ROOT_DIR'].'/login');
});

$app->run();

?>
