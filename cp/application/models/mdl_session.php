<?php

if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/*Работа с сессиями*/
class Mdl_session extends CI_Model {
    
    function __construct()  {
        if (!session_id()) {
            return false;    
        }
    }
    
    /*Установить данные*/
    function set_data($key, $value)  {
        $_SESSION[strval($key)] = serialize($value);
    }
    
    /*Получить данные*/
    function get_data($key)  {
        if  ($this->check_data($key))  {
            return unserialize($_SESSION[strval($key)]);
        }  else  {
            return false;
        }
    }

    /*Проверить существование данных*/
    function check_data($key)  {
        if  (!isset($_SESSION[strval($key)]))  {
            return false;
        }  else  {
            return true;
        }
    }
    
    /*Удаляем данные*/
    function delete_data($key)  {
        unset($_SESSION[strval($key)]);
    }
    
    function delete_all_data()  {
        unset($_SESSION);
    }

}
/**/