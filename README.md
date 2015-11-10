# November PHP Blog
Just a weblog engine written in PHP based on [Silex] micro framework

#Installation
1. Open november/boostrap.php and find and edit the following code snippet:
```
// config
$app['debug'] = true;
$app['blog_config'] = array(
	'page_limit_posts' => 2,	
	'db' => array(
		'driver'	=> 'pdo_mysql',
		'host'		=> 'localhost',
		'user'		=> 'root', // your mysql username
		'password'	=> '123456',
		'dbname'	=> 'november'
	)	
);
```

2. Open browser and run localhost/november/install
