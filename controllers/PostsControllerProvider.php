<?php

use Silex\Application as Application;
use Silex\ControllerProviderInterface as ControllerProviderInterface;
use Symfony\Component\HttpFoundation\Request as Request;

use BeatSwitch\Lock\Callers\Caller;
use BeatSwitch\Lock\Drivers\ArrayDriver as ArrayDriver;
use BeatSwitch\Lock\Lock;
use BeatSwitch\Lock\Manager as Manager;

class PostsControllerProvider implements ControllerProviderInterface {
	
	public function connect(Application $app) {
		
		$controllers = $app['controllers_factory'];
		
		$controllers->get('/', function() use($app) {
			return $app->redirect(							
				$app['url_generator']->generate('posts_page', array('page_id' => 1), true)
			); 
		})->bind('posts_index');
		
		$controllers->get('/page/{page_id}', function($page_id) use($app) {
			$int_id = (int)$page_id;
			if ($int_id < 1 || (strval($int_id) !== $page_id)) {
				return $app['twig']->render('404.html.twig');
			}
			
			$total_pages = $this->page($app, $this->_count($app));

			if ($int_id > 1 && $int_id > $total_pages) {
				return $app['twig']->render('404.html.twig');
			}
			
			UsersControllerProvider::fetchUser($app);
			
			$pattern = "SELECT november_posts.id, title, body, november_posts.created, username "
					."FROM november_posts, november_users "
					."WHERE november_posts.user_id = november_users.id "
					."ORDER BY id DESC "					
					."LIMIT %d, %d";
					
			$query = sprintf(
				$pattern,
				($int_id-1)*$app['blog_config']['page_limit_posts'],
				$app['blog_config']['page_limit_posts']
			);		
			
			$posts = $app['db']->fetchAll($query);
			
			return $app['twig']->render('index.html.twig', array(
				'posts' 	=> $posts,
				'title' 	=> 'Homepage',
				'logged_in' => $app['session']->get('user')? true: false,
				'username'	=> $app['session']->get('user')['username'],
				'login'		=> $app['url_generator']->generate('users_login'),				
				'logout'	=> $app['url_generator']->generate('users_logout'),
				'new_post'	=> $app['url_generator']->generate('posts_create'),
				'total_page'=> $total_pages
			));			
		})->bind('posts_page');			
				
		$controllers->get('/create', function() use($app) {
			
			// fetch user again for security
			UsersControllerProvider::fetchUser($app);
			
			return $app['twig']->render('create.html.twig', array(
				'create_new' => true,
				'post' => array(
					'id' 	=> 0,
					'title' => '',
					'body'	=> ''
				) 
			));
		})->bind('posts_create');
		
		$controllers->post('/create_cgi', function(Request $request) use($app) {
			$inserted = $app['db']->insert('november_posts', array(
				'title' 	=> $request->get('title'),
				'body'		=> $request->get('body'),
				'user_id' 	=> $app['session']->get('user')['id']
			));
	
			if ($inserted) {	
				$app['session']->getFlashBag()->add('msg', 'Add post successfully');
				return $app->redirect($app['ROOT_DIR']. '/');
			} else {
				$app['session']->getFlashBag()->add('msg', 'Can not add your post');
				return $app->redirect($app['ROOT_DIR']. '/post/create');
			}
		})->bind('posts_create_cgi');	
		
		$controllers->get('/edit/{id}', function($id) use($app) {
			$int_id = (int)$id;
			if ($id < 1 || strval($int_id) !== $id) {
				return $app['twig']->render('404.html.twig');
			}
			
			$post = $app['db']->fetchAssoc('SELECT november_posts.id, title, body, november_posts.created, username '
									.'FROM november_posts, november_users '
									.'WHERE november_posts.user_id = november_users.id AND november_posts.id = ?'
									,array($int_id)
			);			
			
			if ($post) {
				return $app['twig']->render('create.html.twig', array(
					'post' 			=> $post,
					'create_new' 	=> false
				)); 	
			}
			
			return $app['twig']->render('404.html.twig');
			
		})->bind('posts_edit');
		
		$controllers->post('/edit/{id}', function(Request $request, $id) use($app) {
			$int_id = (int)$id;
			if ($id < 1 || strval($int_id) !== $id) {
				return $app['twig']->render('404.html.twig');
			}
			
			$count = $app['db']->executeQuery('UPDATE november_posts SET title = ?, body = ? WHERE id = ?', array(
				$request->get('title'),
				$request->get('body'),
				$int_id
			));
			
			if ($count) {
				$app['session']->getFlashBag()->add('msg', 'Save post successfully');
				return $app->redirect(
					$app['url_generator']->generate('posts_index')
				);			
			}
			
			return $app['twig']->render('404.html.twig');
		})->bind('posts_edit_cgi');
		
		$controllers->get('/post/{id}', function($id) use($app) {
			$int_id = (int)$id;
			if ($id < 1 || strval($int_id) !== $id) {
				return $app['twig']->render('404.html.twig');
			}
			
			$post = $app['db']->fetchAssoc('SELECT november_posts.id, title, body, november_posts.created, username '
									.'FROM november_posts, november_users '
									.'WHERE november_posts.user_id = november_users.id AND november_posts.id = ?'
									,array($int_id)
			);
			
			return $app['twig']->render('specified_post.html.twig', array(
				'post' => $post,
			));
		})->bind('posts_specified_post');
		
		$controllers->get('/delete/{id}', function($id) use($app) {
			$int_id = (int)$id;
			if ($id < 1 || strval($int_id) !== $id) {
				return $app['twig']->render('404.html.twig');
			}
			
			// fetch user again for security
			UsersControllerProvider::fetchUser($app);			 
			
			// check wether this post belongs to current user
			$his_posts = $app['db']->fetchColumn('SELECT * FROM november_posts '
									.'WHERE id = ? AND user_id = ?'
									,array($int_id, isset($app['session']->get('user')['id'])? $app['session']->get('user')['id']: 0)
			);		
			
			//$app['permissions_can']('delete', 'his_posts');
			//$app['_acl']->allow('delete', 'his_posts');
			
			
			//if ($app['permissions_can']('delete', 'posts') or $app['permissions_can']('delete', 'his_posts', $his_posts)) {
				$deleted = $app['db']->delete('november_posts', array('id' => $int_id));
				return $app->redirect(
					$app['url_generator']->generate('posts_index')
				);				
			//}			
			
			return 'Ko co quyen';
		})->bind('posts_delete');
				
		return $controllers;
	}	
		
	// count the total valid post records	
	private function _count(Application $app) {
		return $app['db']->fetchColumn(
			sprintf('SELECT count(*) FROM november_posts, november_users '
					.'WHERE november_posts.user_id = november_users.id'					
			)
		);
	}
	
	// calculate the total page 
	function page(Application $app, $count) {
		if (!isset($app['blog_config']['page_limit_posts'])) {
			return 1;
		} elseif (isset($app['blog_config']['page_limit_posts']) && $app['blog_config']['page_limit_posts'] < 1) {
			return 1;
		}
		return ceil($count/$app['blog_config']['page_limit_posts']);
	}
}

?>
