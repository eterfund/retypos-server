<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/*TYPOS super admin*/
$config['typos_admin_login'] = 'admin';
$config['typos_admin_password'] = 'password';
$config['typos_admin_email'] = 'email@admin.com';

// Admin passwords
$config['pass']['key_1'] = "password1";
$config['pass']['key_2'] = "password2";
$config['pass']['key_3'] = "password3";

$config['error_login_count'] = 3;
$config['error_login_time'] = 600; //10 минут

/* Typos credentials for external apis */
$config['correction_path'] = "correctTypo";
$config['typos_user'] = 'typos.etersoft';
$config['typos_password'] = '';