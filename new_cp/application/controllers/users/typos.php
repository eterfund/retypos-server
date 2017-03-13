<?php

if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/*Работа с опечатками - пользователь*/
class Typos extends CI_Controller {

    function __construct()  {
        parent::__construct();
        $this->load->model('users/mdl_typos');
        $this->load->model('mdl_session');
        
        $this->login_id = $this->mdl_session->get_data('login_id');
    }
    
    /*Создаем шаблон*/
    function index()  {
        if ($this->mdl_session->get_data('usertype') == 'admin')  {$views['menu']['data']['items'] = $this->mdl_menu->admin();}
        if ($this->mdl_session->get_data('usertype') == 'user')  {$views['menu']['data']['items'] = $this->mdl_menu->user();}
        $views['body']['url'] = "users/sites";
        $views['menu']['url'] = "menu";
        $this->mdl_views->view($views);
        return true;
    }
    
    function typos()  {
        $this->index();
        return;
    }

    /*Получить список сайтов для пользователя*/
    function get_list_sites()  {
        $data['page'] = $this->mdl_post->int('page');
        $data['limit'] = $this->mdl_post->int('rows', 1);
        $data['sord'] = $this->mdl_post->string('sord');
        $data['sidx'] = $this->mdl_post->string('sidx');
        $data['search'] = $this->mdl_post->string('_search');
        $data['searchField'] = $this->mdl_post->string('searchField');
        $data['searchOper'] = $this->mdl_post->string('searchOper');		
        $data['searchString'] = $this->mdl_post->string('searchString');
        $data['login_id'] = $this->login_id;
        
        echo json_encode($this->mdl_typos->get_list_sites($data));
    }
    
    /*Получить список сообщений об опечатках для пользователя*/
    function get_list_messages()  {
        $data['id_site'] = $this->mdl_post->int("id");
        $data['page'] = $this->mdl_post->int('page');
        $data['limit'] = $this->mdl_post->int('rows', 1);
        $data['sord'] = $this->mdl_post->string('sord');
        $data['sidx'] = $this->mdl_post->string('sidx');
        $data['search'] = $this->mdl_post->string('_search');
        $data['searchField'] = $this->mdl_post->string('searchField');
        $data['searchOper'] = $this->mdl_post->string('searchOper');		
        $data['searchString'] = $this->mdl_post->string('searchString');
        $data['login_id'] = $this->login_id;
        
        echo json_encode($this->mdl_typos->get_list_messages($data));
    }

    /*Управление сайтами*/
    function panel_sites()  {
        $id_site = $this->mdl_post->int("id");
        $oper = $this->mdl_post->string("oper");
        $status = $this->mdl_post->int("status");
        $login_id = $this->login_id;
        
        if  ($oper == 'edit')  {
            if  ($status != 0 && $status != 1)  {
                $status = 1;
            }
            $data['id_site'] = $id_site;
            $data['status'] = $status;
            $data['login_id'] = $login_id;
            $this->mdl_typos->update_status($data);
        }
        
    }

    /*Управление сообщениями*/
    function panel_messages()  {
        $oper = $this->mdl_post->string('oper');
        $data = array();
        if  ($oper == 'add')  {
            $data['id_site'] = $this->mdl_post->int('id_site');
            $data['link'] = $this->mdl_post->string('link');
            $data['error_text'] = $this->mdl_post->string('error_text');
            $data['comment'] = $this->mdl_post->string('comment');
            $data['status'] = $this->mdl_post->int('status');
            if  ($data['status'] != 0 && $data['status'] != 1)  {
                $data['status'] = 1;
            }
            $data['login_id'] = $this->login_id;
            $this->mdl_typos->add_message($data);
        }  else if  ($oper == 'del')  {
            $data['id_message'] = $this->mdl_post->int('id');
            $data['id_site'] = $this->mdl_post->int('id_site');
            $data['login_id'] = $this->login_id;
            $this->mdl_typos->delete_message($data);
        }  else if  ($oper == 'edit')  {
            $data['id_message'] = $this->mdl_post->int('id');
            $data['id_site'] = $this->mdl_post->int('id_site');
            $data['status'] = $this->mdl_post->int('status');
            $data['login_id'] = $this->login_id;
            if  ($data['status'] != 0 && $data['status'] != 1)  {
                $data['status'] = 0;
            }
            $this->mdl_typos->edit_message($data);
        }
    }



    
}
/**/