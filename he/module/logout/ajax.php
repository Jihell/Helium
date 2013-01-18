<?php
/**
 * Déconnecte le compte super user
 */
namespace He\Module\Logout;

final class Ajax extends \He\Control
{
	public static function run()
	{
		$_SESSION['SU'] = null;
		echo 'true';
	}
}