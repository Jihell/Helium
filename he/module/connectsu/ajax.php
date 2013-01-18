<?php
/**
 * Description of ajax
 *
 * @author Joseph Lemoine - lemoine.joseph@gmail.com
 * @version 1
 */
namespace He\Module\Connectsu;

final class Ajax extends \He\Control
{
	public static function run()
	{
		if(\He\POST::test(__DIR__.'/template/form/connectsu.html'))
		{

			if(md5($_POST['mdp_su'].PWD_SEED) == PWD_SU)
			{
				\He\DB::he_su_log()->setPassword('');
				$_SESSION['SU'] = true;
				static::logConnection();
				echo 'true';
			}
			else
			{
				\He\DB::he_su_log()->setPassword($_POST['mdp_su']);
				static::logConnection();
				header('location: /index');
			}
		}
		else
		{
			\He\DB::he_su_log()->setPassword($_POST['mdp_su']);
			static::logConnection();
			header('location: /index');
		}
		
		return true;
	}
	
	private static function logConnection()
	{
		/**
		 * Log de l'affichage du l'invit de connection en tant que SU
		 * Le mot de passe est enregistré en clair si il ne correspond pas.
		 */
		return \He\DB::he_su_log()->setDate('NOW()')
								  ->setIp($_SERVER['REMOTE_ADDR'])
								  ->setHost($_SERVER['REMOTE_HOST'])
								  ->setPort($_SERVER['REMOTE_PORT'])
								  ->stor();
	}
}