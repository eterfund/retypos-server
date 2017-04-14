<?php

if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/*Работа с сайтами - администратор*/
class Sites extends CI_Controller {

    function __construct()  {
        parent::__construct();
        
        $this->load->model('admins/site');
        $this->load->helper('menu');
        
        $this->login_id = $this->session->login_id;
        $this->usertype = $this->session->usertype;
        if  ($this->usertype != 'admin')  {
            redirect('users/typos');
        }
        
        $this->header_name = "header";
        $this->view_name = "admins/sites";
        $this->menu_name = "menus/menu";
        $this->footer_name = "footer";
    }
    
    /*Создаем шаблон*/
    function index()  {
        $data['items'] = menu_admin($this->config->base_url());
        $data['base_url'] = $this->config->base_url();
        
        $this->load->view($this->header_name, $data);
        $this->load->view($this->menu_name,   $data);
        $this->load->view($this->view_name,   $data);
        $this->load->view($this->footer_name, $data);
        
        return true;
    }
    
    function typos()  {
        $this->index();
        return;
    }

    /*Получить сайты*/
    function get_list_sites()  {
        $data['page'] = $this->input->get('page');
        $data['limit'] = $this->input->get('rows', 1);
        $data['sord'] = $this->input->get('sord');
        $data['sidx'] = $this->input->get('sidx');
        $data['search'] = $this->input->get('_search');
        $data['searchField'] = $this->input->get('searchField');
        $data['searchOper'] = $this->input->get('searchOper');		
        $data['searchString'] = $this->input->get('searchString');
        $data['login_id'] = $this->login_id;
        
        echo json_encode($this->site->getSites($data));
    }

    /*Получить пользователей по сайту*/
    function get_list_users()  {
        $data['id_site'] = $this->input->get("id");
        $data['page'] = $this->input->get('page');
        $data['limit'] = $this->input->get('rows', 1);
        $data['sord'] = $this->input->get('sord');
        $data['sidx'] = $this->input->get('sidx');
        $data['search'] = $this->input->get('_search');
        $data['searchField'] = $this->input->get('searchField');
        $data['searchOper'] = $this->input->get('searchOper');		
        $data['searchString'] = $this->input->get('searchString');
        $data['login_id'] = $this->login_id;
        
        echo json_encode($this->site->getSiteUsers($data));
    }

    /*Управление сайтами*/
    function panel_sites()  {
        $oper = $this->input->post('oper');
        log_message('error', "oper = $oper");
        
        if  ($oper == 'add')  { // Добавление пользователя
            $data['site'] = $this->input->post('site');
            if  ($data['site'] == '')  {
                echo json_encode(array('message' => 'Название сайта некорректно'));
            }  else  {
                $return = $this->site->addSite($data);
                if ($return)  {
                    echo json_encode($return);
                }
            }
            return true;
        }  else if  ($oper == 'edit')  { // Редактирование пользователя
            $data['id_site'] = $this->input->post('id');
            $data['site'] = $this->input->post('site');
            if  ($data['site'] == '')  {
                echo json_encode(array('message' => 'Название сайта некорректно'));
            }  else  {
                $return = $this->site->updateSite($data);
                if ($return)  {
                    echo json_encode($return);
                }
            }
            return true;
        }  else  if  ($oper == 'del')  { // Удаление пользователя
            $data['id_site'] = $this->input->post('id');
            if  (!$this->site->deleteSite($data))  {
                echo json_encode(array('message' => 'Сайт нельзя удалить. Количество пользователей не равно 0'));
            }
            return true;
	}
    }

    function panel_users()  {
        $oper = $this->input->post('oper');
        if  ($oper == 'del')  {
            $this->load->model('admins/user');
            $data['id_user'] = $this->input->get('id');
            $data['id_site'] = $this->input->get('id_site');
            $this->user->deleteResponsible($data);
        }
    }
}
/**/
