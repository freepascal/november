<?php
require_once('services/PermissionsServiceProvider.php');

use Symfony\Component\HttpKernel\Debug\ErrorHandler as ErrorHandler;
use Symfony\Component\HttpKernel\Debug\ExceptionHandler as ExceptionHandler;
use Kilte\Silex\Captcha\CaptchaServiceProvider;

// set the error handling
ini_set('display_errors', 1);
error_reporting(-1);

ErrorHandler::register();
if ('cli' !== php_sapi_name()) {
  ExceptionHandler::register();
}

$app = new Silex\Application();

// config
$app['debug'] = true;
$app['blog_config'] = array(
	'page_limit_posts' => 2
);

$app->register(new Silex\Provider\MonologServiceProvider(), array(
	'monolog.logfile' => __DIR__.'/log.log'
));

$app->register(new Silex\Provider\SessionServiceProvider());
$app->register(new Silex\Provider\DoctrineServiceProvider(), array(
	'db.options' 	=> array(
			'driver'	=> 'pdo_mysql',
			'host'		=> 'localhost',
			'user'		=> 'root',
			'password'	=> '123456',
			'dbname'	=> 'november'
		)
	)
);

// for using path() in twig
$app->register(new Silex\Provider\UrlGeneratorServiceProvider());

// Templates
$app->register(new Silex\Provider\TwigServiceProvider(), array(
	'twig.path' => __DIR__.'/views'
));

$app->register(new CaptchaServiceProvider());

// SERVICES
$app->register(new PermissionsServiceProvider());

$app['ROOT_DIR'] = str_replace($_SERVER['DOCUMENT_ROOT'], '', __DIR__);

?>
