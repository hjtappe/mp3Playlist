<?php
/**
 * Configuration Array
 * 
 * @var array $config
 */
$config['searchDir'] = '.';
// $config['searchDir'] = '../Archive';

$config['loginpage'] = dirname($_SERVER['SCRIPT_NAME']).'/login/';

$config['redirRegex'] = '/';
$config['redirRegex'] .=
	'(^https?:\/\/'.preg_quote($_SERVER['HTTP_HOST'].dirname($_SERVER['SCRIPT_NAME']).'/login/', '/').')';
// $config['redirRegex'] .=
//   '|(^https?:\/\/'.preg_quote('www.domain1.com/?q=whatever', '/').')';
// $config['redirRegex'] .=
//   '|(^https?:\/\/'.preg_quote('www.domain2.org/whateverelse/', '/').')';
$config['redirRegex'] .= '/';
?>
