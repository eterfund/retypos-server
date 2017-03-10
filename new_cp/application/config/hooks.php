<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
| -------------------------------------------------------------------------
| Hooks
| -------------------------------------------------------------------------
| This file lets you define "hooks" to extend CI without hacking the core
| files.  Please see the user guide for info:
|
|	https://codeigniter.com/user_guide/general/hooks.html
|
*/

$hook['pre_system'][] = array(
                                'class'    => 'Session',
                                'function' => '__construct',
                                'filename' => 'session.php',
                                'filepath' => 'hooks');

$hook['pre_controller'][] = array(
                                'class'    => 'Check_authorized',
                                'function' => 'index',
                                'filename' => 'check_authorized.php',
                                'filepath' => 'hooks');