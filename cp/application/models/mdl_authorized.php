<?php

if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/*Работа с пользовательскими данными*/
class Mdl_authorized extends CI_Model {
    
    /*Информация о пользователе*/
    function get_info($username)  {
        $query = "SELECT * FROM users WHERE login = '".$username."' LIMIT 1";
        return $this->mdl_query->select($query);
    }   
    
    /*Возвращает хэшированный пароль на основе конфигураций*/
    function process_pass($password)  {
        $password = strval($password);
        if  ($this->config->item('pass'))  {
            $pass_key = $this->config->item('pass');
            if  (isset($pass_key['key_1']) && isset($pass_key['key_2']) && isset($pass_key['key_3']))  {
                return md5($pass_key['key_2'].$password.$pass_key['key_3'].$pass_key['key_1']);
            }  else  {
                return md5($password.'56uyfgh');
            }
        }  else  {
            return md5($password.'56uyfgh');
        }
    }
    
}