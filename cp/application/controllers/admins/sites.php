<?php

if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/*Работа с сайтами - администратор*/
class Sites extends CI_Controller {

    function __construct()  {
        parent::__construct();
        $this->load->model('admins/mdl_sites');
            
        $this->login_id = $this->mdl_session->get_data('login_id');
        $this->usertype = $this->mdl_session->get_data('usertype');
        if  ($this->usertype != 'admin')  {
            redirect('users/typos');
        }
        
    }
    
    /*Создаем шаблон*/
    function index()  {
        $views['body']['url'] = "admins/sites";
        
        $views['menu']['url'] = "menu";
        $views['menu']['data']['items'] = $this->mdl_menu->admin();
        
        $this->mdl_views->view($views);
        return true;
    }
    
    function typos()  {
        $this->index();
        return;
    }

    /*Получить сайты*/
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
        
        echo json_encode($this->mdl_sites->get_list_sites($data));
    }

    /*Получить пользователей по сайту*/
    function get_list_users()  {
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
        
        echo json_encode($this->mdl_sites->get_list_users($data));
    }

    /*Управление сайтами*/
    function panel_sites()  {
        $oper = $this->mdl_post->string('oper');
        if  ($oper == 'add')  {
            $data['site'] = $this->mdl_post->string('site');
            if  ($data['site'] == '')  {
                echo json_encode(array('message' => 'Название сайта некорректно'));
            }  else  {
                $return = $this->mdl_sites->add_site($data);
                if ($return)  {
                    echo json_encode($return);
                }
            }
            return true;
        }  else if  ($oper == 'edit')  {
            $data['id_site'] = $this->mdl_post->int('id');
            $data['site'] = $this->mdl_post->string('site');
            if  ($data['site'] == '')  {
                echo json_encode(array('message' => 'Название сайта некорректно'));
            }  else  {
                $return = $this->mdl_sites->edit_site($data);
                if ($return)  {
                    echo json_encode($return);
                }
            }
            return true;
        }  else  if  ($oper == 'del')  {
            $data['id_site'] = $this->mdl_post->int('id');
            if  (!$this->mdl_sites->delete_site($data))  {
                echo json_encode(array('message' => 'Сайт нельзя удалить. Количество пользователей не равно 0'));
            }
            return true;
		}
	}

    function panel_users()  {
        $oper = $this->mdl_post->string('oper');
        if  ($oper == 'del')  {
            $this->load->model('admins/mdl_users');
            $data['id_user'] = $this->mdl_post->int('id');
            $data['id_site'] = $this->mdl_post->int('id_site');
            $this->mdl_users->delete_responsible($data);
        }
    }
}
/**/
