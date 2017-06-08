<?php

if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/*Работа с пользователями - администратор*/
class Users extends CI_Controller {

    function __construct()  {
        parent::__construct();
        
        $this->load->model('admins/user');
        
        $this->load->helper('menu');
        
        $this->login_id = $this->session->login_id;
        $this->usertype = $this->session->usertype;
        
        if  ($this->usertype != 'admin')  {
            redirect('users/typos');
        }
        
        $this->header_name = "header";
        $this->view_name = "admins/users";
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
    
    function users()  {
        $this->index();
        return;
    }
    
    /*Получить пользователей*/
    function get_list_users()  {
        $data['page'] = $this->input->get('page');
        $data['limit'] = $this->input->get('rows', 1);
        $data['sord'] = $this->input->get('sord');
        $data['sidx'] = $this->input->get('sidx');
        $data['search'] = $this->input->get('_search');
        $data['searchField'] = $this->input->get('searchField');
        $data['searchOper'] = $this->input->get('searchOper');		
        $data['searchString'] = $this->input->get('searchString');
        
        echo json_encode($this->user->getUsers($data));
    }
    
    /*Получить сайты пользователя*/
    function get_user_sites() {
       
        $data['page'] = $this->input->get('page');
        $data['limit'] = $this->input->get('rows', 1);
        $data['sord'] = $this->input->get('sord');
        $data['sidx'] = $this->input->get('sidx');
        $data['search'] = $this->input->get('_search');
        $data['searchField'] = $this->input->get('searchField');
        $data['searchOper'] = $this->input->get('searchOper');		
        $data['searchString'] = $this->input->get('searchString');
        $data['id_user'] = $this->input->get('id');
        
        echo json_encode($this->user->getUserSites($data));
    }
    
    /*Управление пользователями*/
    function panel_users()  {
        $oper = $this->input->post('oper');
        $data = array();
        
        if  ($oper == 'add')  {
            $data['login'] = $this->input->post('login');
            if  (strlen($data['login']) < 3)  {
                echo json_encode(array('message' => 'Логин не корректен'));
                return;
            }
            
            $data['type'] = $this->input->post('type');
            if  ($data['type'] != 'user' && $data['type'] != 'admin')  {
                $data['type']  = 'user';
            }
            
            $data['email'] = $this->input->post('email');
            if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL))  {
				echo json_encode(array('message' => 'Email не корректен'));
                return;
			}
            
            $data['firstname'] = $this->input->post('firstname');
            if  (strlen($data['firstname']) < 2)  {
                echo json_encode(array('message' => 'Имя не корректно'));
                return;
            }
            
            $data['middlename'] = $this->input->post('middlename');
            
            $data['lastname'] = $this->input->post('lastname');
            if  (strlen($data['lastname']) < 2)  {
                echo json_encode(array('message' => 'Фамилия не корректна'));
                return;
            }
            
            $data['password'] = $this->input->post('password');
            if  (strlen($data['password']) < 4)  {
                 echo json_encode(array('message' => 'Пароль не корректен'));
                return;
            }
            
            $data['status'] = $this->input->post('status');
            if  ($data['status'] != 1 && $data['status'] != 0)  {
                $data['status']  = 0;
            }
            $data['activity'] = $this->input->post('activity');
            if  ($data['activity'] != 0 && $data['activity'] != 1)  {
                $data['activity']  = 'user';
            }
            $return = $this->user->addUser($data);
            if ($return)  {
                echo json_encode($return);
            }
            return;
        }  else if ($oper == 'del')  {
                $data['id_user'] = $this->input->post('id');
                $this->user->deleteUser($data);
                return;
        }  else if ($oper == 'edit')  {
            $data['id_user'] = $this->input->post('id');
            $data['login'] = $this->input->post('login');
            if  (strlen($data['login']) < 3)  {
                echo json_encode(array('message' => 'Логин не корректен')); 
                return;
            }
            
            $data['type'] = $this->input->post('type');
            if  ($data['type'] != 'user' && $data['type'] != 'admin')  {
                $data['type']  = 'user';
            }
            
            $data['email'] = $this->input->post('email');
            if  (!preg_match("/^([a-z0-9_-]+\.)*[a-z0-9_-]+@[a-z0-9_-]+(\.[a-z0-9_-]+)*\.[a-z]{2,4}$/", $data['email']))  {
                 echo json_encode(array('message' => 'Email не корректен'));
                return;
            }
            
            $data['firstname'] = $this->input->post('firstname');
            if  (strlen($data['firstname']) < 2)  {
                echo json_encode(array('message' => 'Имя не корректна'));
                return;
            }
            
            $data['middlename'] = $this->input->post('middlename');
            
            $data['lastname'] = $this->input->post('lastname');
            if  (strlen($data['lastname']) < 2)  {
                 echo json_encode(array('message' => 'Фамилия не корректна'));
                return;
            }
            
            $data['password'] = $this->input->post('password');
            if  (strlen($data['password']) < 2)  {
                echo json_encode(array('message' => 'Пароль не корректен'));
                return;
            }
            
            $data['status'] = $this->input->post('status');
            if  ($data['status'] != 1 && $data['status'] != 0)  {
                $data['status']  = 0;
            }
            $data['activity'] = $this->input->post('activity');
            if  ($data['activity'] != 0 && $data['activity'] != 1)  {
                $data['activity']  = 'user';
            }
            $return = $this->user->editUser($data);
            if ($return)  {
                echo json_encode($return);
            }
            return;
        }
        
    
    }
    
    /*Получаем сайты для пользователя, кроме уже принадлежащих*/
    //Возвращать должен html-список
    function get_sites()  {
        $id_user = $this->input->get('id_user');
        $sites = $this->user->getAvailableSites($id_user);
        
        $select = "<select>";
        if  ($sites->num_rows() == 0)  {
            $select .= "<option disabled selected value='-1'>Сайтов нет</option>";
        }  else  {
            foreach ($sites->result() as $site) {
                $select .= "<option value='".$site->id."'>".$site->site."</option>";
            }
        }
        $select .= "</select>";
        
        echo $select;
        return;
    }
    
    /*Управление сайтами пользователя*/
    function panel_users_site()  {
        $oper = $this->input->post('oper');
        
        if ($oper == 'add')  {
            $data['id_user'] = $this->input->get('id_user');
            $data['id_site'] = $this->input->post('site'); 
            $data['status'] = $this->input->post('status');
            
            if  ($data['status'] != 1 && $data['status'] != 0)  {
                $data['status']  = 0;
            }
            
            $return = $this->user->addResponsible($data);
            
            if ($return)  {
                echo json_encode($return);
            }
        } else if  ($oper == 'edit')  {
            $data['id_user'] = $this->input->get('id_user');
            $data['id_site'] = $this->input->post('id');
            $data['status'] = $this->input->post('status');
            
            if  ($data['status'] != 1 && $data['status'] != 0)  {
                $data['status']  = 0;
            }
            
            $this->user->editResponsible($data);
        }  else if ($oper == 'del')  {
            $data['id_user'] = $this->input->get('id_user');
            $data['id_site'] = $this->input->post('id');
            $this->user->deleteResponsible($data);
        }
    }





}
/**/
