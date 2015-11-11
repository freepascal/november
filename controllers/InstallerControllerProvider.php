<?php

use Silex\Application as Application;
use Silex\ControllerProviderInterface as ControllerProviderInterface;
use Symfony\Component\HttpFoundation\Request as Request;

class InstallerControllerProvider implements ControllerProviderInterface {
	public function connect(Application $app) {
		$controllers = $app['controllers_factory'];
		
		$controllers->get('/', function() use($app) {
			return $app->redirect(
				$app['url_generator']->generate('installer_create_tables')
			);
		});
		
		$controllers->get('/create_tables', function() use($app) {
			$app['db']->query(<<<DOC_HERE
			DROP TABLE IF EXISTS november_roles;
			DROP TABLE IF EXISTS november_users;
			DROP TABLE IF EXISTS november_posts;
			DROP TABLE IF EXISTS november_user_role;
			
			CREATE TABLE november_roles (
				id INT auto_increment,
				title VARCHAR(32),
				description TEXT,
	
				PRIMARY KEY (id),
				UNIQUE KEY (title)
			);
			
			CREATE TABLE november_users (
				id INT auto_increment,
				username VARCHAR(20),
				password VARCHAR(64),
				created TIMESTAMP default current_timestamp,
	
				PRIMARY KEY (id),
				UNIQUE KEY (username)
			);			
			
			CREATE TABLE november_posts (
				id INT auto_increment,
				title VARCHAR(255),
				body TEXT,
				created TIMESTAMP default current_timestamp,
				user_id INT,
				slug VARCHAR(512),
	
				PRIMARY KEY (id)
			);
			
			CREATE TABLE november_user_roles (
				id INT auto_increment,
				user_id INT,
				role_id INT,
	
				PRIMARY KEY (id),
				UNIQUE KEY (user_id, role_id)			
			);
DOC_HERE
);			
			return $app->redirect(
				$app['url_generator']->generate('installer_create_data')
			);
		})->bind('installer_create_tables');			
		
		$controllers->get('/create_data', function() use($app) {
			$app['db']->insert('november_roles', array(
				'title' 		=> 'Root Admin',
				'description'	=> 'The root administrator'
			));
			
			$app['db']->insert('november_roles',array(
				'title' 		=> 'Admin',
				'description' 	=> ''
			));
			
			$app['db']->insert('november_roles', array(
				'title' 		=> 'Member',
				'description' 	=> 'Registered members',				
			));
			
			return $app->redirect(
				$app['url_generator']->generate('installer_create_root_user')
			);
		})->bind('installer_create_data');
		
		
		$controllers->get('/create_root_user', function() use($app) {
			return $app['twig']->render('create_root_user.html.twig');
		})->bind('installer_create_root_user');
		
		$controllers->post('/create_root_user', function(Request $request) use($app) {
			$username = trim($request->get('username'));
			$password = trim($request->get('password'));
			$password_again = trim($request->get('password_again'));
			
			if ($password !== $password_again) {
				$app['session']->getFlashBag()->add('msg', 'Error');
				return $app->redirect(
					$app['url_generator']->generate('installer_create_root_user')
				);
			}	
			
			$app['db']->insert('november_users', array(
				'username'	=> $username,
				'password'	=> password_hash($password, PASSWORD_BCRYPT)
			));
			
			return $app->redirect(
				$app['url_generator']->generate('installer_create_first_post')
			);
		});
		
		$controllers->get('/create_first_post', function() use($app) {
			$app['db']->insert('november_posts', array(
				'title' => 'A PHP micro-framework standing on the shoulder of giants (first post)',
				'body'	=> <<<DOC_HERE
Silex is a PHP microframework for PHP. It is built on the shoulders of Symfony2 and Pimple and also inspired by sinatra.

A microframework provides the guts for building simple single-file apps. Silex aims to be:

<ul>
    <li><i>Concise</i>: Silex exposes an intuitive and concise API that is fun to use.</li>
    <li><i>Extensible</i>: Silex has an extension system based around the Pimple micro service-container that makes it even easier to tie in third party libraries.</li>
    <li><i>Testable</i>: Silex uses Symfony2's HttpKernel which abstracts request and response. This makes it very easy to test apps and the framework itself. It also respects the HTTP specification and encourages its proper use.</li>
</ul>				
DOC_HERE
				,'user_id' => 1
			));
			
			$app['db']->insert('november_posts', array(
				'title' => 'Connect to Source Repository with SVN',
				'body' 	=> <<<DOC_HERE
As an alternative to the daily zip files of the SVN sources, the SVN repository has been made accessible for everyone, with read-only access. This means that you can always have access to the latest source code. It is also a method which requires less bandwidth once you have done the first download (called a "checkout" in SVN lingo).
<p>
<b>Development snapshots</b><br>
How do you obtain the sources via SVN? Generally, you need 3 steps:
(once you have SVN installed, of course. Look <a href="http://subversion.tigris.org">here</a> for instructions on how to do that.) 

<ol>
<li>
    To retrieve the full fpc source repository, type

    <i>svn checkout http://svn.freepascal.org/svn/fpc/trunk fpc</i>

    This will create a directory called "fpc" in the current directory, containing subdirectories with the following components:
        rtl, the run time library source code for all platforms.
        compiler, the compiler source code.
        packages, packages source code (contains Free Component Library, gtk, ncurses, mysql and many more)
        utils, the utilities source code.
        fv, Free Vision.
        tests, the compiler and RTL tests.
        ide, the IDE source code.
        installer, the text mode installer source code.
    If you do not want the entire repository, you can check out subsections using, e.g.,

    <i>svn checkout http://svn.freepascal.org/svn/fpc/trunk/rtl fpc/rtl</i>

    Normally, you should perform this checkout step just once.
</li>
    
<li>
    To update the sources that were downloaded (checked out) above to the latest available version, use

    <i>svn update fpc</i>

    or

    <i>svn update fpc/rtl</i>

    if you only downloaded some separate components, such as the rtl sources in this case.
    These commands will retrieve patches ONLY for the files that have changed on the server.

    You can repeat this step whenever you want to update your sources. It is by far the most economic way to remain up-to-date in terms of bandwidth.
</li>    
</ol>    	
DOC_HERE
				,'user_id' => 1
			));
			return $app->redirect(
				$app['url_generator']->generate('posts_index')
			);
		})->bind('installer_create_first_post');
		
		return $controllers;
	}
}

?>
