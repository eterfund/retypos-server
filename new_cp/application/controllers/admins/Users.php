<?php

if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/*Работа с пользователями - администратор*/
class Users extends CI_Controller {

    function __construct()  {
        parent::__construct();
        $this->load->model('admins/mdl_users');
            
        $this->login_id = $this->mdl_session->get_data('login_id');
        $this->usertype = $this->mdl_session->get_data('usertype');
        if  ($this->usertype != 'admin')  {
            redirect('users/typos');
        }
    }
    
    /*Создаем шаблон*/
    function index()  {
        $views['body']['url'] = "admins/users";
        
        $views['menu']['url'] = "menu";
        $views['menu']['data']['items'] = $this->mdl_menu->admin();
        
        $this->mdl_views->view($views);
        return true;
    }
    
    function users()  {
        $this->index();
        return;
    }
    
    /*Получить пользователей*/
    function get_list_users()  {
        $data['page'] = $this->mdl_post->int('page');
        $data['limit'] = $this->mdl_post->int('rows', 1);
        $data['sord'] = $this->mdl_post->string('sord');
        $data['sidx'] = $this->mdl_post->string('sidx');
        $data['search'] = $this->mdl_post->string('_search');
        $data['searchField'] = $this->mdl_post->string('searchField');
        $data['searchOper'] = $this->mdl_post->string('searchOper');		
        $data['searchString'] = $this->mdl_post->string('searchString');
        
        echo json_encode($this->mdl_users->get_list_users($data));
    }
    
    /*Получить сайты пользователя*/
    function get_user_sites() {
        $data['page'] = $this->mdl_post->int('page');
        $data['limit'] = $this->mdl_post->int('rows', 1);
        $data['sord'] = $this->mdl_post->string('sord');
        $data['sidx'] = $this->mdl_post->string('sidx');
        $data['search'] = $this->mdl_post->string('_search');
        $data['searchField'] = $this->mdl_post->string('searchField');
        $data['searchOper'] = $this->mdl_post->string('searchOper');		
        $data['searchString'] = $this->mdl_post->string('searchString');
        $data['id_user'] = $this->mdl_post->int('id');
        
        echo json_encode($this->mdl_users->get_user_sites($data));
    }
    
    /*Управление пользователями*/
    function panel_users()  {
        $oper = $this->mdl_post->string('oper');
        $data = array();
        
        if  ($oper == 'add')  {
            $data['login'] = $this->mdl_post->string('login');
            if  (strlen($data['login']) < 3)  {
                echo json_encode(array('message' => 'Логин не корректен'));
                return;
            }
            
            $data['type'] = $this->mdl_post->string('type');
            if  ($data['type'] != 'user' && $data['type'] != 'admin')  {
                $data['type']  = 'user';
            }
            
            $data['email'] = $this->mdl_post->string('email');
            if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL))  {
				echo json_encode(array('message' => 'Email не корректен'));
                return;
			}
            
            $data['firstname'] = $this->mdl_post->string('firstname');
            if  (strlen($data['firstname']) < 2)  {
                echo json_encode(array('message' => 'Имя не корректна'));
                return;
            }
            
            $data['middlename'] = $this->mdl_post->string('middlename');
            
            $data['lastname'] = $this->mdl_post->string('lastname');
            if  (strlen($data['lastname']) < 2)  {
                echo json_encode(array('message' => 'Фамилия не корректна'));
                return;
            }
            
            $data['password'] = $this->mdl_post->string('password');
            if  (strlen($data['password']) < 4)  {
                 echo json_encode(array('message' => 'Пароль не корректен'));
                return;
            }
            
            $data['status'] = $this->mdl_post->int('status');
            if  ($data['status'] != 1 && $data['status'] != 0)  {
                $data['status']  = 0;
            }
            $data['activity'] = $this->mdl_post->int('activity');
            if  ($data['activity'] != 0 && $data['activity'] != 1)  {
                $data['activity']  = 'user';
            }
            $return = $this->mdl_users->add_user($data);
            if ($return)  {
                echo json_encode($return);
            }
            return;
        }  else if ($oper == 'del')  {
                $data['id_user'] = $this->mdl_post->int('id');
                $this->mdl_users->delete_user($data);
                return;
        }  else if ($oper == 'edit')  {
            $data['id_user'] = $this->mdl_post->int('id');
            $data['login'] = $this->mdl_post->string('login');
            if  (strlen($data['login']) < 3)  {
                echo json_encode(array('message' => 'Логин не корректен')); 
                return;
            }
            
            $data['type'] = $this->mdl_post->string('type');
            if  ($data['type'] != 'user' && $data['type'] != 'admin')  {
                $data['type']  = 'user';
            }
            
            $data['email'] = $this->mdl_post->string('email');
            if  (!preg_match("/^([a-z0-9_-]+\.)*[a-z0-9_-]+@[a-z0-9_-]+(\.[a-z0-9_-]+)*\.[a-z]{2,4}$/", $data['email']))  {
                 echo json_encode(array('message' => 'Email не корректен'));
                return;
            }
            
            $data['firstname'] = $this->mdl_post->string('firstname');
            if  (strlen($data['firstname']) < 2)  {
                echo json_encode(array('message' => 'Имя не корректна'));
                return;
            }
            
            $data['middlename'] = $this->mdl_post->string('middlename');
            
            $data['lastname'] = $this->mdl_post->string('lastname');
            if  (strlen($data['lastname']) < 2)  {
                 echo json_encode(array('message' => 'Фамилия не корректна'));
                return;
            }
            
            $data['password'] = $this->mdl_post->string('password');
            if  (strlen($data['password']) < 2)  {
                echo json_encode(array('message' => 'Пароль не корректен'));
                return;
            }
            
            $data['status'] = $this->mdl_post->int('status');
            if  ($data['status'] != 1 && $data['status'] != 0)  {
                $data['status']  = 0;
            }
            $data['activity'] = $this->mdl_post->int('activity');
            if  ($data['activity'] != 0 && $data['activity'] != 1)  {
                $data['activity']  = 'user';
            }
            $return = $this->mdl_users->edit_user($data);
            if ($return)  {
                echo json_encode($return);
            }
            return;
        }
        
    
    }
    
    /*Получаем сайты для пользователя, кроме уже принадлежащих*/
    //Возвращать должен html-список
    function get_sites()  {
        $id_user = $this->mdl_post->int('id_user');
        $sites = $this->mdl_users->get_sites($id_user);
        
        $select = "<select>";
        if  (!$sites)  {
            $select .= "<option disabled selected value='-1'>Сайтов нет</option>";
        }  else  {
            for ($i=0; $i<count($sites); $i++)  {
                $select .= "<option value='".$sites[$i]['id']."'>".$sites[$i]['site']."</option>";
            }
        }
        $select .= "</select>";
        
        echo $select;
        return;
    }
    
    /*Управление сайтами пользователя*/
    function panel_users_site()  {
        $oper = $this->mdl_post->string('oper');
        if ($oper == 'add')  {
            $data['id_user'] = $this->mdl_post->int('id_user');
            $data['id_site'] = $this->mdl_post->int('site'); 
            $data['status'] = $this->mdl_post->int('status');
            if  ($data['status'] != 1 && $data['status'] != 0)  {
                $data['status']  = 0;
            }
            $return = $this->mdl_users->add_responsible($data);
            if ($return)  {
                echo json_encode($return);
            }
        } else if  ($oper == 'edit')  {
            $data['id_user'] = $this->mdl_post->int('id_user');
            $data['id_site'] = $this->mdl_post->int('id');
            $data['status'] = $this->mdl_post->int('status');
            $data['status'] = $this->mdl_post->int('status');
            if  ($data['status'] != 1 && $data['status'] != 0)  {
                $data['status']  = 0;
            }
            $this->mdl_users->edit_responsible($data);
        }  else if ($oper == 'del')  {
            $data['id_user'] = $this->mdl_post->int('id_user');
            $data['id_site'] = $this->mdl_post->int('id');
            $this->mdl_users->delete_responsible($data);
        }
    }





}
/**/
