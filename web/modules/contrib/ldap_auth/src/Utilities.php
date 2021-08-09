<?php
/**
 * @package    miniOrange
 * @subpackage Plugins
 * @license    GNU/GPLv3
 * @copyright  Copyright 2015 miniOrange. All Rights Reserved.
 *
 *
 * This file is part of miniOrange LDAP Login with NTLM module for Drupal.
 *
 * miniOrange Login with NTLM module for Drupal is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * miniOrange Login with NTLM module for Drupal is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with miniOrange LDAP Login plugin.  If not, see <http://www.gnu.org/licenses/>.
 */
namespace Drupal\ldap_auth;
use Drupal\Core\Form\FormStateInterface;
use Drupal\ldap_auth\handler;
use Drupal\ldap_auth\MiniorangeLdapSupport;
class Utilities {

    /**
     * Shows support block
     */
    public static function AddSupportButton(array &$form, FormStateInterface $form_state)
    {
        $attachments['#attached']['library'][] = 'ldap_auth/ldap_auth.admin';
    $form['markup_library'] = array(
        '#attached' => array(
            'library' => array(
                "ldap_auth/ldap_auth.admin",
            )
        ),
    );

    $form['markup_idp_attr_header_top_support'] = array('#markup' => '</div><div class="mo_ldap_table_layout_support_1 container" id="ma_saml_support_query">');

      $form['markup_support_1'] = array(
      '#markup' => '<h3><b>Feature Request/Contact Us:</b></h3><div><i>Need any help? We can help you with configuring your LDAP / Active Directory server. Just send us a query and we will get back to you soon.<br /></i></div><br>',
      );

      $form['miniorange_ldap_email_address'] = array(
      '#type' => 'textfield',
      '#attributes' => array('style' => 'width:100%','placeholder' => 'Enter your email'),
      '#default_value' => \Drupal::config('ldap_auth.settings')->get('miniorange_saml_customer_admin_email'),
      );

      $form['miniorange_ldap_phone_number'] = array(
      '#type' => 'textfield',
      '#attributes' => array('style' => 'width:100%','placeholder' => 'Enter your phone number'),
      '#default_value' => \Drupal::config('ldap_auth.settings')->get('miniorange_saml_customer_admin_phone'),
      );

      $form['miniorange_ldap_support_query'] = array(
      '#type' => 'textarea',
      '#cols' => '10',
      '#rows' => '5',
      '#attributes' => array('style' => 'width:100%','placeholder' => 'Write your query here'),
      );

      $form['miniorange_ldap_support_submit'] = array(
        '#type' => 'submit',
        '#value' => t('Submit Query'),
        '#submit' => array('::saved_support'),
        '#attributes' => array('style' => 'background: #337ab7;color: #ffffff;text-shadow: 0 -1px 1px #337ab7, 1px 0 1px #337ab7, 0 1px 1px #337ab7, -1px 0 1px #337ab7;box-shadow: 0 1px 0 #337ab7;border-color: #337ab7 #337ab7 #337ab7;display:block;margin-left:auto;margin-right:auto;'),
      );

      $form['miniorange_ldap_support_note'] = array(
      '#markup' => '<br>
      <div class="ldap-align"><a class="btn btn-primary btn-large" href="https://plugins.miniorange.com/guide-to-configure-ldap-ad-integration-module-for-drupal" target="_blank">Setup Guide</a>
      <a class="btn btn-success btn-large" href="mailto:drupalsupport@xecurify.com?subject=Demo%20Request%20|%20Drupal%20LDAP%20Login%20Module%20Setup" target="_blank">Request for an online meeting</a></div><div><br/>If you want any custom features in the module, just drop an email to <a href="mailto:drupalsupport@xecurify.com">drupalsupport@xecurify.com</a></div></div>'
      );
    }

    public function saved_support(array &$form, FormStateInterface $form_state) {
        $email = $form['miniorange_ldap_email_address']['#value'];
        $phone = $form['miniorange_ldap_phone_number']['#value'];
        $query = $form['miniorange_ldap_support_query']['#value'];
        self::send_support_query($email, $phone, $query);
    }

    /**
     * Returns all search Bases from AD
    */
    public static function getSearchBases(){
      $ldapconn = getConnection();
      $ldap_bind_dn = \Drupal::config('ldap_auth.settings')->get('miniorange_ldap_server_account_username');
	    $ldap_bind_password = \Drupal::config('ldap_auth.settings')->get('miniorange_ldap_server_account_password');
      $bind = @ldap_bind($ldapconn, $ldap_bind_dn, $ldap_bind_password);
      $search_base_list = array();
      if($bind){
        $result = ldap_read($ldapconn, '', '(objectclass=*)', array('namingContexts'));
        $data = ldap_get_entries($ldapconn, $result); 
        $count = $data[0]['namingcontexts']['count'];
        for ($i = 0; $i < $count; $i++) {
          if ($i == 0) {
            $base_dn = $data[0]['namingcontexts'][$i];
          }
          $valuetext = $data[0]['namingcontexts'][$i];

          array_push($search_base_list, $data[0]['namingcontexts'][$i]);                   
        }
        
        $filter = "(|(objectclass=organizationalUnit)(&(objectClass=top)(cn=users)))";
        $search_attr = array("dn", "ou");
        @$ldapsearch = ldap_search($ldapconn, $base_dn, $filter, $search_attr);
        @$info = ldap_get_entries($ldapconn, $ldapsearch);

        for ($i = 0; $i < $info["count"]; $i++) {
            $textvalue = $info[$i]["dn"];
                array_push($search_base_list, $info[$i]["dn"]);
        }
      }  
      return $search_base_list;        
    }



    public static function send_support_query($email, $phone, $query){
        if(empty($email)||empty($query)){
            \Drupal::messenger()->addError(t('The <b><u>Email</u></b> and <b><u>Query</u></b> fields are mandatory.'));
            return;
        } elseif( !\Drupal::service('email.validator')->isValid( $email ) ) {
            \Drupal::messenger()->addError(t('The email address <b><i>' . $email . '</i></b> is not valid.'));
            return;
        }
        $support = new MiniorangeLdapSupport($email, $phone, $query);
        $support_response = $support->sendSupportQuery();
        if($support_response) {
            \Drupal::messenger()->addStatus(t('Support query successfully sent'));
        }
        else {
            \Drupal::messenger()->addError(t('Error sending support query'));
        }
    }

    public static function isLDAPInstalled() {
        return (in_array('ldap', get_loaded_extensions()));
    }
	  public static function isCurlInstalled() {
      if (in_array('curl', get_loaded_extensions()))
        return 1;
      else
        return 0;
    }

  public static function drupal_is_cli(){
    $server = \Drupal::request()->server;
    $server_software = $server->get('SERVER_SOFTWARE');
    $server_argc = $server->get('argc');
    if(!isset($server_software) && (php_sapi_name() == 'cli' || (is_numeric($server_argc) && $server_argc > 0)))
      return TRUE;
    else
      return FALSE;
  }
}
?>