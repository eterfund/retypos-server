<?php

if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/*Работа с шаблонами*/
class Mdl_views extends CI_Model {
    
    /*Загружаем основные шаблоны*/
    function view($data = array())  {
        $url = $this->config->item('base_url');
        $data['header']['base_url'] = $url;
        $data['menu']['base_url'] = $url;
        $data['body']['base_url'] = $url;
        $data['footer']['base_url'] = $url;
           
        /**/     
        $this->load->view('header', $data['header']);
        
        if  (isset($data['menu']['url']) && isset($data['menu']['data']))  {
            $this->load->view($data['menu']['url'], $data['menu']['data']);
        }
        
        if  (!isset($data['body']['data']))  {
            $data['body']['data'] = array();
        }
        $this->load->view($data['body']['url'], $data['body']['data']);
        
        $this->load->view('footer', $data['footer']);
        /**/
    }
    
    
    
}
/**/