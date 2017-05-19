<?php

if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/*Работа с опечатками - пользователь*/
class Typos extends CI_Controller {

    function __construct()  {
        parent::__construct();
        
        $this->load->model('typo');
        $this->load->helper('menu');
        
        $this->login_id = $this->session->userdata("login_id");
        
        $this->header_name = "header";
        $this->view_name = "users/typos";
        $this->menu_name = "menus/menu";
        $this->footer_name = "footer";
    }
    
    /*Создаем шаблон*/
    function index()  {
        $data['base_url'] = $this->config->base_url();
        
        if ($this->session->usertype == 'admin')  {
            $data['items'] = menu_admin($data['base_url']);
            
        }
        if ($this->session->usertype == 'user')  {
            $data['items'] = menu_user($data['base_url']);
        }
        
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

    /*Получить список сайтов для пользователя*/
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
        
        log_message("error", $this->login_id);
        
        echo json_encode($this->typo->getSitesList($data));
    }
    
    /*Получить список сообщений об опечатках для пользователя*/
    function get_list_messages()  {
        log_message("debug", "get_list_messages()");
        
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
        
        echo json_encode($this->typo->getMessagesList($data));
    }

    /*Управление сайтами*/
    function panel_sites()  {
        $id_site = $this->input->get("id");
        $oper = $this->input->get("oper");
        $status = $this->input->get("status");
        $login_id = $this->login_id;
        
        if  ($oper == 'edit')  {
            if  ($status != 0 && $status != 1)  {
                $status = 1;
            }
            
            $data['id_site'] = $id_site;
            $data['status'] = $status;
            $data['login_id'] = $login_id;
            $this->typo->updateStatus($data);
        }
        
    }

    /*Управление сообщениями*/
    function panel_messages()  {
        
        $oper = $this->input->get('oper');
        
        $data = array();
        
        if  ($oper == 'add')  {
            $data['id_site'] = $this->input->get('id_site');
            $data['link'] = $this->input->get('link');
            $data['error_text'] = $this->input->get('error_text');
            $data['comment'] = $this->input->get('comment');
            $data['status'] = $this->input->get('status');
            if  ($data['status'] != 0 && $data['status'] != 1)  {
                $data['status'] = 1;
            }
            $data['login_id'] = $this->login_id;
            
            $this->typo->addMessage($data);
        }  else if  ($oper == 'del')  {
            $data['id_message'] = $this->input->get('id');
            $data['id_site'] = $this->input->get('id_site');
            $data['login_id'] = $this->login_id;
            
            $this->typo->deleteMessage($data);
        }  else if  ($oper == 'edit')  {
            $data['id_message'] = $this->input->get('id');
            $data['id_site'] = $this->input->get('id_site');
            $data['status'] = $this->input->get('status');
            
            $data['login_id'] = $this->login_id;
            if  ($data['status'] != 0 && $data['status'] != 1)  {
                $data['status'] = 0;
            }
            
            $this->typo->editMessages($data);
        }
    }



    
}
/**/