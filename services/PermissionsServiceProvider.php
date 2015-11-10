<?php

use Silex\Application as Application;
use Silex\ServiceProviderInterface as ServiceProviderInterface;

use BeatSwitch\Lock\Callers\Caller;
use BeatSwitch\Lock\Drivers\ArrayDriver as ArrayDriver;
use BeatSwitch\Lock\Lock;
use BeatSwitch\Lock\Manager as Manager;

class PermissionsServiceProvider implements ServiceProviderInterface {
	private $acl;
	
	public function register(Application $app) {
		$app['permissions_can'] = $app->protect(function ($action, $resource, $resource_defined = true) use($app) {
			
			$user = new User(isset($app['session']->get('user')['id'])? $app['session']->get('user')['id']: 0);
			
			// ACL
			$acl_manager = new Manager(new ArrayDriver());
			$this->acl = $acl_manager->caller($user);
			
			if ($this->acl->can($action, $resource) && $resource_defined) {
				return true;
			}
			
			return false;
		});

		$app['permissions_allow'] = $app->protect(function ($action, $resource) use($app) {
			$user = new User(isset($app['session']->get('user')['id'])? $app['session']->get('user')['id']: 0);
			
			//ACL
			$acl_manager = new Manager(new ArrayDriver());			
			$acl = $acl_manager->caller($user);
			
			$acl->allow($action, $resource);			
		});
		
		$app['fetch_user'] = $app->protect(function() use($app) {
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
					return false;
				}
				return true;			
			}
			return false;
		});
	}
	
	public function boot(Application $app) {
	}
}

?>
