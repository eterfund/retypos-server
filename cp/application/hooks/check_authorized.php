<?php

if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/*Проверка авторизированности пользователя*/
class Check_authorized extends CI_Controller  {
    /*Если не страница авторизации/проверки логина/пароля - перенаправляем на главную*/
    function index()  {
        $class = $this->uri->segment(1);
        $method = $this->uri->segment(2); 
        if ((!$class) || ($class == 'authorized') || ($class.'/'.$method == 'authorized/check') || ($class.'/'.$method == 'authorized/logout'))  {
            return true;
        }  else  {
            if  (!$this->session->userdata('login_id'))  {
                redirect('authorized');
            }
        }
    } 
    
}
/**/
