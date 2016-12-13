<?php

if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/*Создание меню*/
class Mdl_menu extends CI_Model {
    
    function admin()  {
        $data['sites'] = "<a href='".$this->config->base_url()."index.php/admins/sites'>Сайты</a>";
        $data['users'] = "<a href='".$this->config->base_url()."index.php/admins/users'>Пользователи</a>";
        $data['typos'] = "<a href='".$this->config->base_url()."index.php/users/typos'>Опечатки</a>";
        $data['logout'] = "<a href='".$this->config->base_url()."index.php/authorized/logout'>Выйти</a>";
        
        return $data;
    }   
    
    function user()  {
        $data['logout'] = "<a href='".$this->config->base_url()."index.php/authorized/logout'>Выйти</a>";
        
        return $data;
    }
}