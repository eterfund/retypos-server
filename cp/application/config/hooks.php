<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/*
| -------------------------------------------------------------------------
| Hooks
| -------------------------------------------------------------------------
| This file lets you define "hooks" to extend CI without hacking the core
| files.  Please see the user guide for info:
|
|	http://codeigniter.com/user_guide/general/hooks.html
|
*/

/*Включаем сессии*/
$hook['pre_system'][] = array(
                                'class'    => 'Session',
                                'function' => '__construct',
                                'filename' => 'session.php',
                                'filepath' => 'hooks'
                        );

/*Проверяем авторизирован ли пользователь*/
$hook['pre_controller'][] = array(
                                'class'    => 'Check_authorized',
                                'function' => 'index',
                                'filename' => 'check_authorized.php',
                                'filepath' => 'hooks'
                            );


/* End of file hooks.php */
/* Location: ./application/config/hooks.php */