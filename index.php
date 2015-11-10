<?php

require_once('vendor/autoload.php');
require_once('bootstrap.php');

require_once('models/User.php');




require_once('controllers/TestControllerProvider.php');
require_once('controllers/UsersControllerProvider.php');
require_once('controllers/PostsControllerProvider.php');
require_once('controllers/InstallerControllerProvider.php');


use Symfony\Component\HttpFoundation\Request as Request;

$app->get('/generate_passwd', function() use($app) {
	return $app['twig']->render('generate_passwd.html.twig', array());
});

$app->post('/generate_passwd', function(Request $request) use($app) {
	return sprintf("%s",
		password_hash($request->get('password'), PASSWORD_BCRYPT)
	);
});

$postsControllerProvider = new PostsControllerProvider();

$app->mount('/tests', new TestControllerProvider());
$app->mount('/users', new UsersControllerProvider());
$app->mount('/install', new InstallerControllerProvider());
$app->mount('/', $postsControllerProvider);

$app->run();

?>
