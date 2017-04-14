<?php

if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Authorization extends CI_Controller {
        
    /**
     * Contains common data
     * @var type array
     */
    private $data;
    
    /**
     * View name for this controller
     * @var type string
     */
    private $view_name;
    
    function __construct() {
        parent::__construct();
        
        $this->data['auth_url'] = $this->config->base_url()."authorization/check";
        $this->view_name = 'authorization/index';
        
        $this->load->model('userHelper');
    }
    
    function index()  {
        $this->load->view($this->view_name, $this->data);
    }
    
    function check()  {
        if  (!$this->check_login_error())  {
            
            $this->data['error_message'] = "Вы превысили число попыток";
            $this->load->view($this->view_name, $this->data);
            return;
        }

        $username = $this->input->post('username');
        $password = $this->input->post('password');
        
        if  ($username == "" || $password == "")  {
            $this->data['error_message'] = "Логин/пароль пустой";
            $this->load->view($this->view_name, $this->data);
            return;
        }
        
       /* Look for admin account information in config/typos_config*/
        if  ($this->config->item('typos_admin_login') && 
              $this->config->item('typos_admin_password') &&
              $this->config->item('typos_admin_email'))  {
            
            if  ($username == $this->config->item('typos_admin_login') &&
                  $password == $this->config->item('typos_admin_password'))  {
                $this->session->login = $username;
                $this->session->usertype = 'admin';
                $this->session->email = $this->config->item('typos_admin_email');
                $this->session->login_id = -1;
                redirect('admins/sites/');
            }
        }
        
        $user_info = $this->userHelper->getUser($username);
        $password = $this->userHelper->hashPassword($password);

        echo var_dump($user_info);
        
        if (!$user_info)  {
                $this->error_login();
                $this->data['error_message'] = "Пароль/логин не верен";
                $this->load->view($this->view_name, $this->data);
                return;
        }  else  {
            if  ($password == $user_info->password)  {
                if  (intval($user_info->activity) == 1)  {
                    $loginData = array(
                        'login'      => $username,
                        'usertype'   => $user_info->type,
                        'email'      => $user_info->email,
                        'login_id'   => $user_info->id,
                        'firstname'  => $user_info->firstname,
                        'lastname'   => $user_info->lastname,
                        'middlename' => $user_info->middlename,
                    );
                    
                    $this->session->set_userdata($loginData);
                    
                    /*Перенаправлям в зависимости от типа пользователя*/
                    if  ($user_info->type == 'admin')  {
                        redirect('admins/sites/');
                    }  else if  ($user_info->type == 'user')  {
                        redirect('users/typos/');
                    }
                }
            }  else  {
                $this->error_login();
                $this->data['error_message'] = "Пароль/логин не верен";
                $this->load->view($this->view_name, $this->data);
                return;
            }
        }
        
    }
    
    function logout()  {
        $this->session->sess_destroy();
        
        unset($_SESSION);
        
        redirect ("authorized");
    }
    
    /*Устанавливаем счетчики ошибок входа*/
    function error_login()  {
        $this->session->set_userdata('error_login', intval($this->session->error_login_count) + 1);
        $this->session->set_userdata('error_login_time', time());
    }
    
    function check_login_error()  {
        
        if  (!$this->session->error_login)  {
            return true;
        }  else  {
            $count_error = intval($this->session->userdata('error_login'));

            $config_count = $this->config->item('error_login_count');
            if  (!$config_count)  {
                $config_count = 3;
            }
            $config_time = $this->config->item('error_login_time');
            if  (!$config_time)  {
                $config_time = 10000;
            }

            $time_error = time() - intval($this->session->error_login_time);

            /*Если время бана прошло, обнуляем*/
            if  ($time_error > $config_time)  {
                $this->session->error_login_count = null;
                return true;
            }
                      
            if  ($time_error <= $config_time && $count_error >= $config_count)  {
                return false;
            }  else  {
                return true;
            }
            
        }
    }
}
/**/
