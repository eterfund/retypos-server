<?php

if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/*Авторизация пользователя*/
class Authorized extends CI_Controller {
        
    function index()  {
        $views['body']['url'] = "authorized";
        $views['body']['data']['auth_url'] = $this->config->base_url()."authorized/check";
        $this->mdl_views->view($views);
    }
    
    function check()  {
        if  (!$this->check_login_error())  {
            $views['body']['data']['auth_url'] = $this->config->base_url()."authorized/check";
            $views['body']['data']['error_message'] = "Вы превысили число попыток";
            $views['body']['url'] = "authorized";
            $this->mdl_views->view($views);
            return;
        }

        $username = $this->mdl_post->string('username');
        $password = $this->mdl_post->string('password');
        if  ($username == "" || $password == "")  {
            $views['body']['data']['auth_url'] = $this->config->base_url()."authorized/check";
            $views['body']['data']['error_message'] = "Логин/пароль пустой";
            $views['body']['url'] = "authorized";
            $this->mdl_views->view($views);
            return;
        }
        
        /*Если совпадает с данными администратора по-умолчанию (см. typos_congig.php)*/
        if  ($this->config->item('typos_admin_login') && $this->config->item('typos_admin_password') && $this->config->item('typos_admin_email'))  {
            if  ($username == $this->config->item('typos_admin_login') && $password == $this->config->item('typos_admin_password'))  {
                $this->mdl_session->set_data('login', $username);
                $this->mdl_session->set_data('usertype', 'admin');
                $this->mdl_session->set_data('email', $this->config->item('typos_admin_email'));
                $this->mdl_session->set_data('login_id', -1);
                $this->session->set_userdata('login_id', -1);
                redirect('admins/sites/');
            }
        }
        
        $user_info = $this->mdl_authorized->get_info($username);

        $password = $this->mdl_authorized->process_pass($password);

        if (!$user_info)  {
                $this->error_login();
                $views['body']['data']['auth_url'] = $this->config->base_url()."authorized/check";
                $views['body']['data']['error_message'] = "Пароль/логин не верен";
                $views['body']['url'] = "authorized";
                $this->mdl_views->view($views);
                return;
        }  else  {
            if  ($password == $user_info[0]['password'])  {
                if  (intval($user_info[0]['activity']) == 1)  {
                    $this->mdl_session->set_data('login', $username);
                    $this->mdl_session->set_data('usertype', $user_info[0]['type']);
                    $this->mdl_session->set_data('email', $user_info[0]['email']);
                    $this->mdl_session->set_data('login_id', $user_info[0]['id']);
                    $this->mdl_session->set_data('firstname', $user_info[0]['firstname']);
                    $this->mdl_session->set_data('lastname', $user_info[0]['lastname']);
                    $this->mdl_session->set_data('middlename', $user_info[0]['middlename']);
                    $this->session->set_userdata('login_id', $user_info[0]['id']);
                    /*Перенаправлям в зависимости от типа пользователя*/
                    if  ($user_info[0]['type'] == 'admin')  {
                        redirect('admins/sites/');
                    }  else if  ($user_info[0]['type'] == 'user')  {
                        redirect('users/typos/');
                    }
                }
            }  else  {
                $this->error_login();
                $views['body']['data']['auth_url'] = $this->config->base_url()."authorized/check";
                $views['body']['data']['error_message'] = "Пароль/логин не верен";
                $views['body']['url'] = "authorized";
                $this->mdl_views->view($views);
                return;
            }
        }
        
    }
    
    function logout()  {
        $this->session->sess_destroy();
        $this->mdl_session->delete_all_data();
        redirect ("authorized");
    }
    
    /*Устанавливаем счетчики ошибок входа*/
    function error_login()  {
        $this->mdl_session->set_data('error_login', intval($this->mdl_session->get_data('error_login_count')) + 1);
        $this->mdl_session->set_data('error_login_time', time());

        $this->session->set_userdata('error_login', intval($this->session->userdata('error_login')) + 1);
        $this->session->set_userdata('error_login_time', time());
    }
    
    function check_login_error()  {
        if  (!$this->mdl_session->get_data('error_login') && !$this->session->userdata('error_login'))  {
            return true;
        }  else  {
            $count_error = max(intval($this->mdl_session->get_data('error_login')), intval($this->session->userdata('error_login')));

            $config_count = $this->config->item('error_login_count');
            if  (!$config_count)  {
                $config_count = 3;
            }
            $config_time = $this->config->item('error_login_time');
            if  (!$config_time)  {
                $config_time = 10000;
            }

            $time_error = max(intval($this->mdl_session->get_data('error_login_time')), intval($this->session->userdata('error_login_time')));
            $time_error = time() - $time_error;

            /*Если время бана прошло, обнуляем*/
            if  ($time_error > $config_time)  {
                $this->mdl_session->set_data('error_login_count', 0);
                $this->session->userdata('error_login_count', 0);
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
