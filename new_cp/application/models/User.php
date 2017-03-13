<?php

/**
 * User model
 *
 * @author george popoff <ambulance@etersoft.ru>
 */
class User extends CI_Model
{
    /**
     * Retrieve user by given username
     * 
     * @param type $username
     * @return User array
     */
    public function getUser($username) {
        $query = $this->db->query("SELECT * FROM users WHERE login = '".$username."' LIMIT 1");
        return $query->row();
    }
    
    /**
     * Apply hash function to the password
     * 
     * @param password
     * @return type
     */
    public function hashPassword($password) {
        $password = strval($password);
        
        if  ($this->config->item('pass'))  {
            
            $pass_key = $this->config->item('pass');
            
            if  (isset($pass_key['key_1']) && isset($pass_key['key_2']) && isset($pass_key['key_3']))  {
                return md5($pass_key['key_2'].$password.$pass_key['key_3'].$pass_key['key_1']);
            }  else  {
                return md5($password.'56uyfgh');
            }
        }  else  {
            return md5($password.'56uyfgh');
        }
    }
}
