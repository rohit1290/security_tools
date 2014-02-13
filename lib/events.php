<?php
/**
 * All event handler callback functions are handled in this file
 */

/**
 * Event is called when an user is made admin on the site
 *
 * @param string   $event make_admin
 * @param string   $type  user
 * @param ElggUser $user  the affected user
 *
 * @return void
 */
function security_tools_make_admin_handler($event, $type, ElggUser $user) {
	
	if (!empty($user) && elgg_instanceof($user, "user")) {
		$site = elgg_get_site_entity();
		$logged_in_user = elgg_get_logged_in_user_entity();
		
		// notify other administrators about this
		if (elgg_get_plugin_setting("mails_admin_admins", "security_tools") != "no") {
			// get all the site administrators
			$options = array(
				"limit" => false,
				"joins" => array("JOIN " . elgg_get_config("dbprefix") . "entity_relationships r ON e.guid = r.guid_one"),
				"wheres" => array("(r.relationship = 'member_of_site' AND r.guid_two = " . $site->getGUID() . ")")
			);
			$admins = elgg_get_admins($options);
			
			// allow other plugins to modify the admins
			$params = array(
				"event" => "make_admin",
				"admins" => $admins,
				"user" => $user
			);
			$admins = elgg_trigger_plugin_hook("notify_admins", "security_tools", $params, $admins);
			// if we have administrators left, notify them
			if (!empty($admins) && is_array($admins)) {
				$subject = elgg_echo("security_tools:notify_admins:make_admin:subject", array($site->name));
				$message = elgg_echo("security_tools:notify_admins:make_admin:message", array(
					$user->name,
					$logged_in_user->name,
					$user->getURL(),
					$site->url
				));
				
				foreach ($admins as $admin) {
					// force notifications to email so nobody misses this
					notify_user($admin->getGUID(), $site->getGUID(), $subject, $message, null, "email");
				}
			}
		}
		
		// notify the user about this
		if (elgg_get_plugin_setting("mails_admin_user", "security_tools") == "yes") {
			$notify = true;
			// allow other plugins to block this notification
			$params = array(
				"event" => "make_admin",
				"user" => $user,
			);
			$notify = elgg_trigger_plugin_hook("notify_user", "security_tools", $params, $notify);
			if ($notify) {
				$subject = elgg_echo("security_tools:notify_user:make_admin:subject", array($site->name));
				$message = elgg_echo("security_tools:notify_user:make_admin:message", array(
					$user->name,
					$logged_in_user->name,
					$site->url
				));
				
				notify_user($user->getGUID(), $site->getGUID(), $subject, $message, null, "email");
			}
		}
	}
}

/**
 * Event is called when the admin rights of an user are removed
 *
 * @param string   $event remove_admin
 * @param string   $type  user
 * @param ElggUser $user  the affected user
 *
 * @return void
 */
function security_tools_remove_admin_handler($event, $type, ElggUser $user) {
	
	if (!empty($user) && elgg_instanceof($user, "user")) {
		$site = elgg_get_site_entity();
		$logged_in_user = elgg_get_logged_in_user_entity();
		
		// notify other administrators about this
		if (elgg_get_plugin_setting("mails_admin_admins", "security_tools") != "no") {
			// get all the site administrators
			$options = array(
				"limit" => false,
				"joins" => array("JOIN " . elgg_get_config("dbprefix") . "entity_relationships r ON e.guid = r.guid_one"),
				"wheres" => array(
					"(r.relationship = 'member_of_site' AND r.guid_two = " . $site->getGUID() . ")",
					"(e.guid <> " . $user->getGUID() . ")"
				)
			);
			$admins = elgg_get_admins($options);
			
			// allow other plugins to modify the admins
			$params = array(
				"event" => "remove_admin",
				"admins" => $admins,
				"user" => $user
			);
			$admins = elgg_trigger_plugin_hook("notify_admins", "security_tools", $params, $admins);
			// if we have administrators left, notify them
			if (!empty($admins) && is_array($admins)) {
				$subject = elgg_echo("security_tools:notify_admins:remove_admin:subject", array($site->name));
				$message = elgg_echo("security_tools:notify_admins:remove_admin:message", array(
					$user->name,
					$logged_in_user->name,
					$user->getURL(),
					$site->url
				));
				
				foreach ($admins as $admin) {
					// force notifications to email so nobody misses this
					notify_user($admin->getGUID(), $site->getGUID(), $subject, $message, null, "email");
				}
			}
		}
		
		// notify the user about this
		if (elgg_get_plugin_setting("mails_admin_user", "security_tools") == "yes") {
			$notify = true;
			// allow other plugins to block this notification
			$params = array(
				"event" => "remove_admin",
				"user" => $user,
			);
			$notify = elgg_trigger_plugin_hook("notify_user", "security_tools", $params, $notify);
			if ($notify) {
				$subject = elgg_echo("security_tools:notify_user:remove_admin:subject", array($site->name));
				$message = elgg_echo("security_tools:notify_user:remove_admin:message", array(
					$user->name,
					$logged_in_user->name
				));
				
				notify_user($user->getGUID(), $site->getGUID(), $subject, $message, null, "email");
			}
		}
	}
}
