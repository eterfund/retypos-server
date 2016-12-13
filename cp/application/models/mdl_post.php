<?php

if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/*Обработка входящих данных*/
class Mdl_post extends CI_Model {
    
    function data($key = '', $null = '')  {
        if  (!$this->input->get_post($key))  {
            return $null;
        }  else  {
            return $this->input->get_post($key, TRUE);
        }
    }
    
    function int($key = '', $null = 0)  {
        if  (!$this->input->get_post($key))  {
            return $null;
        }  else  {
            return intval($this->input->get_post($key));
        }
    }
    
    function float($key = '', $null = 0)  {
        if  (!$this->input->get_post($key))  {
            return $null;
        }  else  {
            return floatval($this->input->get_post($key));
        }
    }
    
    function string($key, $null = '')  {
        if  (!$this->input->get_post($key))  {
            return $null;
        }  else  {
            return trim(strval($this->input->get_post($key, TRUE)));
        }
    }
        
}
/**/