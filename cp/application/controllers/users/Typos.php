<?php

if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/*Работа с опечатками - пользователь*/
class Typos extends CI_Controller {

    /* @var $session CI_Session */
    public $session;

    /* @var $config CI_Config */
    public $config;

    /* @var $input CI_Input */
    protected $input;

    /* @var $typo Typo */
    public $typo;

    /* @var $parser CI_Parser */
    public $parser;

    /* @var $output CI_Output */
    public $output;

    /* user id */
    private $login_id;

    private $header_name;
    private $view_name;
    private $menu_name;
    private $footer_name;

    function __construct()  {
        parent::__construct();
        
        $this->load->model('typo');
        $this->load->helper('menu');
        $this->load->library('parser');

        $this->login_id = $this->session->userdata("login_id");

        if (is_null($this->login_id)) {
            redirect("authorization");
        }

        $this->header_name = "header";
        $this->view_name = "users/typos";
        $this->menu_name = "menus/menu";
        $this->footer_name = "footer";
    }
    
    /*Создаем шаблон*/
    function index()  {
        $data['base_url'] = $this->config->base_url();
        
        if ($this->session->usertype == 'admin')  {
            $data['menuItems'] = menu_admin($data['base_url']);
            
        } else if ($this->session->usertype == 'user')  {
            $data['menuItems'] = menu_user($data['base_url']);
        }
        
        $this->load->view($this->header_name, $data);
        $this->load->view($this->menu_name,   $data);
        $this->parser->parse($this->view_name,   $data);
        $this->load->view($this->footer_name, $data);
        
        return true;
    }
    
    function typos()  {
        $this->index();
        return;
    }

    /*Получить список сайтов для пользователя*/
    function getSiteList()  {
        return $this->output
            ->set_content_type('application/json')
            ->set_status_header(200)
            ->set_output(json_encode($this->typo->getSitesList($this->login_id)));
    }
    
    /*Получить список сообщений об опечатках для пользователя*/
    function getListTypos()  {
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

    function getSiteTypos($siteId = null) {
        if (is_null($siteId)) {
            return $this->output->set_status_header(400)
                ->set_output("Missing siteId parameter!");
        }

        return $this->output->set_content_type("application/json")
            ->set_status_header(200)
            ->set_output(json_encode($this->typo->getSiteTypos($siteId)));
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
        
        $oper = $this->input->post('oper');
        
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
            $data['id_message'] = $this->input->post('id');
            $data['id_site'] = $this->input->get('id_site');
            $data['login_id'] = $this->login_id;
            
            $this->typo->deleteMessage($data);
        }  else if  ($oper == 'edit')  {
            $data['id_message'] = $this->input->post('id');
            $data['id_site'] = $this->input->get('id_site');
            $data['status'] = $this->input->post('status');
            
            $data['login_id'] = $this->login_id;
            if  ($data['status'] != 0 && $data['status'] != 1)  {
                $data['status'] = 0;
            }
            
            $this->typo->editMessage($data);
        }
    }



    
}
/**/