<?php

if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/*Включение сессий*/
class Session {
    function __construct()  {
        if (!session_id()) {
			ini_set('session.use_cookies', 'On');
			ini_set('session.use_trans_sid', 'Off');
            
			session_set_cookie_params(0, '/');
			session_start();
		}
    }   
}
/**/