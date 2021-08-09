<?php
namespace Drupal\ldap_auth;
use Drupal\ldap_auth\Mo_Ldap_Auth_Response;
class handler{
    public static function test_mo_config($username=null, $password=null){
      $login_with_ldap = \Drupal::config('ldap_auth.settings')->get('miniorange_ldap_enable_ldap');
      if(empty($username))
        $username = \Drupal::config('ldap_auth.settings')->get('miniorange_ldap_server_account_username');
      if(empty($password))
        $password = \Drupal::config('ldap_auth.settings')->get('miniorange_ldap_server_account_password');
      if(empty($username) || empty($password)){
        $return_status = array("Username or Password can not be empty until and unless you do not have any authenticaiton setup and wish to perform anonymous bind to your server. If that is the case, please ignore this message and continue with the setup.","error");
        return $return_status;
      }
      $auth_response = self::ldap_login($username, $password);
      if(!empty($auth_response)){
        if($auth_response->statusMessage == "SUCCESS"){
          $return_status = array("Enable <b>LDAP Login</b> at the top and then Logout from your Drupal site and login again with your LDAP credentials.","Success");
          return $return_status;
        }
        else if($auth_response->statusMessage =="USER_NOT_EXIST"){
          $return_status = array("The user you entered does not exist in the Active Directory. Please check your configurations or contact the administrator","error");
          return $return_status;
        }
        else if($auth_response->statusMessage =="Test_Connection_was_successful"){
          $return_status = array("Your test connection was successful","Success");
          return $return_status;
        }
        else{
          $return_status = array("Invalid Password. Please check your password and try again.","error");
          return $return_status;
        }
      }
    }
    /**
     * Login function
     */
    public static function ldap_login($username, $password){
      $username = stripcslashes($username);
      if(empty($username)){
        \Drupal::messenger()->addError(t("Username can not be empty"));
        return;
      }
      $password = stripcslashes($password);
      if(empty($password)){
        \Drupal::messenger()->addError(t("The Password can not be empty"));
        return;
      }
      $authStatus = null;
      $ldapconn = self::getConnection();
      if ($ldapconn) {
          $search_filter = $server_name = \Drupal::config('ldap_auth.settings')->get('miniorange_ldap_username_attribute');
          $value_filter = '(&(objectClass=*)(' . $search_filter . '=?))';
          $search_bases = \Drupal::config('ldap_auth.settings')->get('miniorange_ldap_search_base');
          if($search_bases == 'custom_base')
            $search_bases = \Drupal::config('ldap_auth.settings')->get('miniorange_ldap_custom_sb_attribute');
          $ldap_bind_dn = \Drupal::config('ldap_auth.settings')->get('miniorange_ldap_server_account_username');
          $ldap_bind_password = \Drupal::config('ldap_auth.settings')->get('miniorange_ldap_server_account_password');
          $filter = str_replace('?', $username, $value_filter);
          $user_search_result = null;
          $entry = null;
          $info = null;
          $fname_attribute = strtolower(\Drupal::config('ldap_auth.settings')->get('mo_ldap_local_fname_attribute'));
          $lname_attribute = strtolower(\Drupal::config('ldap_auth.settings')->get('mo_ldap_local_lname_attribute'));
          $email_attribute = strtolower(\Drupal::config('ldap_auth.settings')->get('mo_ldap_local_email_attribute'));
          $phone_attribute = strtolower(\Drupal::config('ldap_auth.settings')->get('mo_ldap_local_phone_attribute'));
          if(empty($fname_attribute))
            $fname_attribute = 'mail';
          if(empty($lname_attribute))
            $lname_attribute = 'dn';
          if(empty($email_attribute))
            $email_attribute = 'cn';
          if(empty($phone_attribute))
            $phone_attribute = 'samaccountname';
          $attr = array($fname_attribute, $lname_attribute, $email_attribute, $phone_attribute);
          if(\Drupal::config('ldap_auth.settings')->get('miniorange_ldap_enable_tls') != '')
            ldap_start_tls($ldapconn);
          $bind = @ldap_bind($ldapconn, $ldap_bind_dn, $ldap_bind_password);
          $err = ldap_error($ldapconn);
          if(strtolower($err) != 'success'){
            $auth_response = new Mo_Ldap_Auth_Response();
            $auth_response->status = false;
            $auth_response->statusMessage = 'LDAP_NOT_RESPONDING';
            $auth_response->userDn = '';
            return $auth_response;
          }
          else if (isset($_COOKIE['Drupal_visitor_mo_ldap_test']) && ($_COOKIE['Drupal_visitor_mo_ldap_test'] == true)){
            $auth_response = new Mo_Ldap_Auth_Response();
            $auth_response->status = true;
            $auth_response->statusMessage = 'Test_Connection_was_successful';
            $auth_response->userDn = '';
            return $auth_response;
          }
          $s1 = ldap_search($ldapconn, $search_bases, $filter);
          if($s1){
            $user_search_result = ldap_search($ldapconn, $search_bases, $filter, $attr);
          }
          else{
            $err = ldap_error($ldapconn);
            $auth_response = new Mo_Ldap_Auth_Response();
            $auth_response->status = false;
            $auth_response->statusMessage = 'USER_NOT_EXIST';
            $auth_response->userDn = '';
            return $auth_response;
          }
          $info = ldap_first_entry($ldapconn, $user_search_result);
          $entry = ldap_get_entries($ldapconn, $user_search_result);
          if($info){
            $userDn = ldap_get_dn($ldapconn, $info);
          }
          else{
            $auth_response = new Mo_Ldap_Auth_Response();
            $auth_response->status = false;
            $auth_response->statusMessage = 'USER_NOT_EXIST';
            $auth_response->userDn = '';
            return $auth_response;
          }
          $authentication_response = self::authenticate($userDn, $password);
          if($authentication_response->statusMessage == 'SUCCESS'){
            $attributes_array = array();
            $profile_attributes = array();
          $authentication_response->attributeList = $attributes_array;
          }
          return $authentication_response;
        } else{
          $auth_response = new Mo_Ldap_Auth_Response();
          $auth_response->status = false;
          $auth_response->statusMessage = 'ERROR';
          $auth_response->userDn = '';
          return $auth_response;
        }
    }
    /**
      * Used to establish a connection with the LDAP Server
    */
    public static function getConnection() {
      $server_name = \Drupal::config('ldap_auth.settings')->get('miniorange_ldap_server');
      $ldaprdn = \Drupal::config('ldap_auth.settings')->get('miniorange_ldap_server_account_username');
      $ldappass =\Drupal::config('ldap_auth.settings')->get('miniorange_ldap_server_account_password');
      $ldapconn = ldap_connect($server_name);
      if ( version_compare(PHP_VERSION, '5.3.0') >= 0 ) {
        ldap_set_option($ldapconn, LDAP_OPT_NETWORK_TIMEOUT, 5);
      }
      ldap_set_option($ldapconn, LDAP_OPT_PROTOCOL_VERSION, 3);
      ldap_set_option($ldapconn, LDAP_OPT_REFERRALS, 0);
      return $ldapconn;
    }
    /**
     * Authenticate LDAP Credentials
     */
    function authenticate($userDn, $password) {
      $server_name = \Drupal::config('ldap_auth.settings')->get('miniorange_ldap_server');
      $ldapconn = ldap_connect($server_name);
      if ( version_compare(PHP_VERSION, '5.3.0') >= 0 ) {
        ldap_set_option(null, LDAP_OPT_NETWORK_TIMEOUT, 5);
      }
      ldap_set_option($ldapconn, LDAP_OPT_PROTOCOL_VERSION, 3);
      ldap_set_option($ldapconn, LDAP_OPT_REFERRALS, 0);
      // binding to ldap server
      if(\Drupal::config('ldap_auth.settings')->get('miniorange_ldap_enable_tls'))
        ldap_start_tls($ldapconn);
      $ldapbind = @ldap_bind($ldapconn, $userDn, $password);
      // verify binding
      $search_filter =\Drupal::config('ldap_auth.settings')->get('miniorange_ldap_username_attribute');
        $value_filter = '(&(objectClass=*)(' . $search_filter . '=?))';
        $filter = str_replace('?', $userDn, $value_filter);
      if ($ldapbind) {
        $search_result = ldap_search($ldapconn, $userDn,$filter);
        $auth_response = new Mo_Ldap_Auth_Response();
        $auth_response->status = true;
        $auth_response->statusMessage = 'SUCCESS';
        $auth_response->userDn = $userDn;
        return $auth_response;
      } else {
      }
      $auth_response = new Mo_Ldap_Auth_Response();
      $auth_response->status = false;
      $auth_response->statusMessage = 'ERROR';
      $auth_response->userDn = $userDn;
      return $auth_response;
    }
}
?>