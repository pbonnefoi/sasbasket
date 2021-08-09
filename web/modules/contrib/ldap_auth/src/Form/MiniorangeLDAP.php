<?php

/**
 * @file
 * Contains \Drupal\miniorange_ldap\Form\MiniorangeLDAP.
 */

namespace Drupal\ldap_auth\Form;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Drupal\ldap_auth\MiniorangeLdapSupport;
use Drupal\Core\Form\FormBase;
use Drupal\ldap_auth\handler;
use Drupal\ldap_auth\LDAPLOGGERS;
use Drupal\Component\Render\FormattableMarkup;
use Drupal\ldap_auth\Utilities;
use Drupal\Component\Utility\Html;

class MiniorangeLDAP extends FormBase{
  public function getFormId() {
    return 'miniorange_ldap_config_client';
  }
  public function buildForm(array $form, FormStateInterface $form_state){
      global $base_url;
    \Drupal::configFactory()->getEditable('ldap_auth.settings')->set('miniorange_ldap_disabled', FALSE)->save();
    $form['markup_library'] = array(
        '#attached' => array(
            'library' => array(
                "ldap_auth/ldap_auth.usernamefield",
                "ldap_auth/ldap_auth.admin",
            )
        ),
    );

    if(!Utilities::isLDAPInstalled()){
        $form['markup_reg_msg'] = array(
            '#markup' => '<div class="mo_ldap_enable_extension_message"><b>The PHP LDAP extension is not enabled.</b><br> Please Enable the PHP LDAP Extension for you server to continue. If you want, you refer to the steps given on the link  <a target="_blank" href="https://faq.miniorange.com/knowledgebase/how-to-enable-php-ldap-extension/" >here</a> to enable the extension for your server.</div><br>',
        );
    }

    $status= \Drupal::config('ldap_auth.settings')->get('miniorange_ldap_config_status');
    if($status=='')
      $status = 'two';

    $form['#prefix'] = '<div class="mo_ldap_table_layout_1"><div class="mo_ldap_table_layout container">
    <strong>Note: </strong>You need to find out the values of the below given field from your LDAP administrator</strong><div class="btn-right"><a class="btn btn-success btn-large" href ="https://www.youtube.com/watch?v=wBe8T6FLKx4" target="_blank">Setup Video</a> <a class="btn btn-primary btn-large" href="https://plugins.miniorange.com/guide-to-configure-ldap-ad-integration-module-for-drupal" target="_blank">Setup Guide</a></div>
    <br><br>';
    

    if($status=='review_config'){
        $form['miniorange_ldap_enable_ldap_markup'] = array(
        '#markup' => "<h1><b>Login With LDAP</b></h1><hr><br>",
        );
        $form['miniorange_ldap_enable_ldap'] = array(
            '#type' => 'checkbox',
            '#title' => t('Enable Login with LDAP '),
            '#default_value' => \Drupal::config('ldap_auth.settings')->get('miniorange_ldap_enable_ldap'),
        );
        $form['set_of_radiobuttons']['miniorange_ldap_authentication'] = array(
            '#type' => 'radios',
            '#disabled' => true,
            '#title' => t('Authentication restrictions: <a href="' . $base_url .'/admin/config/people/ldap_auth/Licensing">[Premium]</a>'),
            '#description' => t('Only particular personalities will be able to login by selecting the above option.'),
            '#tree' => TRUE,
            '#default_value' => is_null(\Drupal::config('ldap_auth.settings')->get('miniorange_ldap_authentication'))?1:\Drupal::config('ldap_auth.settings')->get('miniorange_ldap_authentication'),
            '#options' => array(0 => t('Drupal and LDAP Users'), 1 => t('LDAP Users and Administrators of Drupal'), 2 => t('LDAP Users')),
        );
        $form['miniorange_ldap_enable_auto_reg'] = array(
            '#type' => 'checkbox',
            '#disabled' => 'true',
            '#title' => t('Enable Auto Registering users if they do not exist in Drupal <a href="' . $base_url .'/admin/config/people/ldap_auth/Licensing">[Premium]</a>'),
            '#default_value' =>\Drupal::config('ldap_auth.settings')->get('miniorange_ldap_enable_auto_reg'),
        );
        $form['ldap_server'] = array(
            '#markup' => "<br><br>
                <h1><b>LDAP Connection Information</b></h1><hr><br><br><h3>LDAP Server:</h3>",
        );

        $form['ldap_server_url_markup_start'] = array(
            '#markup' => '<div class="ldap_Server_row">',
        );

        $form['miniorange_ldap_options'] = array(
            '#type' => 'value',
            '#id' => 'miniorange_ldap_options',
            '#value' => array(
              'ldap://' => t('ldap://'),
              'ldaps://' => t('ldaps://'),),
        );
      
        $form['miniorange_ldap_protocol'] = array(
        '#id' => 'miniorange_ldap_protocol',
        '#type' => 'select',
        '#prefix' => '<div class="ldap-column left">',
        '#suffix' => '</div>',
        '#default_value' =>\Drupal::config('ldap_auth.settings')->get('miniorange_ldap_protocol'),
        '#options' => $form['miniorange_ldap_options']['#value'],
        '#attributes' => array('style'=>'width:100%'),
        );

        $form['miniorange_ldap_server_address'] = array(
            '#type' => 'textfield',
            '#id' => 'miniorange_ldap_server_address',
            '#prefix' => '<div class="ldap-column middle">',
            '#suffix' => '</div>',
            '#default_value' =>\Drupal::config('ldap_auth.settings')->get('miniorange_ldap_server_address'),
            '#attributes' => array('style' => 'width:100%;','placeholder' => 'Enter your server-address or IP'),
        );
        $form['miniorange_ldap_server_port_number'] = array(
            '#type' => 'textfield',
            '#prefix' => '<div class="ldap-column right">',
            '#suffix' => '</div>',
            '#default_value' =>\Drupal::config('ldap_auth.settings')->get('miniorange_ldap_server_port_number'),
            '#attributes' => array('style' => 'width:100%;','placeholder' => '<port>'),
        );
        $form['ldap_server_url_markup_end'] = array(
            '#markup' => '</div><p><i>Select ldap or ldaps from the above dropdown list. Specify the host name for the LDAP server in the above text field. Edit the port number if you have custom port number.</i></p>',
        );

        $form['miniorange_ldap_contact_server_button'] = array(
            '#type' => 'submit',
            '#value' => t('Contact LDAP Server'),
            '#attributes' => array('style' => 'border-radius:4px;background: #337ab7;color: #ffffff;text-shadow: 0 -1px 1px #337ab7, 1px 0 1px #337ab7, 0 1px 1px #337ab7, -1px 0 1px #337ab7;
                box-shadow: 0 2px 0 #006799;border-color: #337ab7 #337ab7 #337ab7; width: 30%;display:block;margin-right:auto;'),
            '#submit' => array('::test_ldap_connection'),
        );
        $form['miniorange_ldap_enable_tls'] = array(
            '#prefix' => '<br>',
            '#type' => 'checkbox',
            '#disabled' => true,
            '#id' => 'check',
            '#title' => t('Enable TLS (Check this only if your server use TLS Connection)  <a href="' . $base_url .'/admin/config/people/ldap_auth/Licensing">[Premium]</a>'),
            '#default_value' =>\Drupal::config('ldap_auth.settings')->get('miniorange_ldap_enable_tls'),
        );
        global $base_url;
        $form['miniorange_ldap_anonymous_bind_markup_2'] = array(
            '#markup' => '<br><div class="mo_ldap_highlight_background_note_1" >In case you do not have any authentication setup and wish to perform anonymous bind to your server, please click on the Next button and continue with your setup.</div>',
        );
        $form['miniorange_ldap_server_account_username'] = array(
            '#type' => 'textfield',
            '#title' => t('Service Account Username:'),
            '#default_value' =>\Drupal::config('ldap_auth.settings')->get('miniorange_ldap_server_account_username'),
            '#description' => "This service account username will be used to establish the connection. Specify the Service Account Username of the LDAP server in the either way as follows Username@domainname or domainname\Username. or Distinguished Name(DN) format",
            '#attributes' => array('style' => 'width:73%;','placeholder' => 'CN=service,DC=domain,DC=com'),
        );
        $form['miniorange_ldap_server_account_password'] = array(
            '#type' => 'password',
            '#title' => t('Service Account Password:'),
            '#default_value' =>\Drupal::config('ldap_auth.settings')->get('miniorange_ldap_server_account_password'),
            '#attributes' => array('style' => 'width:73%;','placeholder' => 'Enter password for Service Account'),
        );
        $form['miniorange_ldap_test_connection_button'] = array(
            '#type' => 'submit',
            '#prefix' => '<br>',
            '#value' => t('Test Connection'),
            '#attributes' => array('style' => 'border-radius:4px;background: #337ab7;color: #ffffff;text-shadow: 0 -1px 1px #337ab7, 1px 0 1px #337ab7, 0 1px 1px #337ab7, -1px 0 1px #337ab7;
                box-shadow: 0 2px 0 #006799;border-color: #337ab7 #337ab7 #337ab7; width: 30%;display:block;margin-right:auto;'),
            '#submit' => array('::test_connection_ldap'),
        );
        $form['troubleshooting_1'] = array(
            '#markup' => "
                <div style='background-color: white; padding: 10px ;margin-left: 20px; width: 70%' id='enable_ldap'>
                    <input type='button' style='background-color: #008CBA;border: none;color: white;padding: 8px 20px;text-align: center;text-decoration: none;display: inline-block;border-radius: 12px;font-size: 16px;' value='Troubleshooting' onclick='msg()'>
                </div><br><br>",
        );

        $possible_search_bases = Utilities::getSearchBases();
        $possible_search_bases_in_key_val = array();
        foreach($possible_search_bases as $search_base){
            $possible_search_bases_in_key_val[$search_base]=$search_base;
        }
        $possible_search_bases_in_key_val['custom_base']='Provide Custom LDAP Search Base';


        $form['miniorange_search_base_options'] = array(
            '#type' => 'value',
            '#id' => 'miniorange_search_base_options',
            '#value' => $possible_search_bases_in_key_val,
        );


        $form['miniorange_search_base_options']['search_base_attribute'] = array(
            '#id' => 'miniorange_ldap_search_base_attribute',
            '#title' => t('Search Base(s):'),
            '#type' => 'select',
            '#description' => t('This is the LDAP tree under which we will search for the users for authentication. If we are not able to find a user in LDAP it means they are not present in this search base or any of its sub trees.
                Provide the distinguished name of the Search Base object. <b>E.g. cn=Users,dc=domain,dc=com.</b>
               <p> Multiple Search Bases are supported in the <a href="' . $base_url .'/admin/config/people/ldap_auth/Licensing">Premium</a> version of the module.</p>'),
            '#default_value' =>\Drupal::config('ldap_auth.settings')->get('miniorange_ldap_search_base'),
            '#options' => $form['miniorange_search_base_options']['#value'],
            '#attributes' => array('style'=>'width:73%;height:30px'),               
        );
        $form['miniorange_ldap_custom_sb_attribute'] = array(
            '#type' => 'textfield',
            '#title' => t('Custom Search Base(s):'),
            '#default_value' =>\Drupal::config('ldap_auth.settings')->get('miniorange_ldap_custom_sb_attribute'),
            '#states' => array('visible' => array(':input[name = "search_base_attribute"]' => array('value' => 'custom_base' ), ),),
            '#attributes' => array('style' => 'width:73%;'),
        );

        $form['miniorange_username_options'] = array(
            '#type' => 'value',
            '#id' => 'miniorange_username_options',
            '#value' => array(
              'samaccountName' => t('samaccountName'),
              'mail' => t('mail'),
              'userPrincipalName' => t('userPrincipalName'),
              'cn' => t('cn'),
              'custom' => t('Provide Custom LDAP attribute name'),
            ),
          );
      
          $form['ldap_auth']['settings']['username_attribute'] = array(
            '#id' => 'miniorange_ldap_username_attribute',
            '#title' => t('Search Filter/Username Attribute:'),
            '#type' => 'select',
            '#prefix' => '<br>',
            '#default_value' =>\Drupal::config('ldap_auth.settings')->get('miniorange_ldap_username_attribute_option'),
            '#options' => $form['miniorange_username_options']['#value'],
            '#attributes' => array('style'=>'width:73%;height:30px'),
          );
          $form['miniorange_ldap_custom_username_attribute'] = array(
            '#type' => 'textfield',
            '#title' => t('Custom Search Filter:'),
            '#default_value' =>\Drupal::config('ldap_auth.settings')->get('miniorange_ldap_custom_username_attribute'),
            '#states' => array('visible' => array(':input[name = "username_attribute"]' => array('value' => 'custom' ), ),),
            '#attributes' => array('style' => 'width:73%;'),
        );

        $form['miniorange_ldap_username_attribute_options_markup'] = array(
            '#markup' => 'This field is important for two reasons: <br>
            1. While searching for users, this is the attribute that is going to be matched to see if the user exists.<br>
            2. If you want your users to login with their username or firstname.lastname or email - you need to specify those options in this field. e.g. <b>LDAP_ATTRIBUTE</b>. <br><br>Replace <b><LDAP_ATTRIBUTE></b> with the attribute where your username is stored. Some common attributes are</p>
            <table>
                <tr>
                    <td>common name</td>
                    <td>cn</td>
                    <td></td>
                    <td></td>
                </tr>
                <tr>
                    <td>email</td>
                    <td>mail</td>
                    <td></td>
                    <td></td>
                </tr>
                <tr>
                    <td>logon name</td>
                    <td>samaccountName</td>
                    <td>or</td>
                    <td>userPrincipalName</td>
                </tr>
            </table>
            <p>You can even search for your user using a Custom Search Filter. You can also allow logging in with multiple attributes, separated with  <i>semicolon</i> <strong>e.g. cn;mail</strong>  <a href="' . $base_url .'/admin/config/people/ldap_auth/Licensing">[Premium]</a><br>',
            '#attributes' => array('style'=>'width:73%'),
        );

        $form['save_user_mapping'] = array(
            '#markup' => "
                <div style='background-color: white; padding: 10px ;margin-left: 20px; width: 70%' id='enable_ldap'>
                    <p><b>Please Note:</b> The attributes that we are showing are examples and the actual ones could be different. These should be confirmed with the LDAP Admin.<p>
                    <input type='button' style='background-color: #4CAF50;border: none;color: white;padding: 8px 20px;text-align: center;text-decoration: none;display: inline-block;border-radius: 12px;font-size: 16px;' value='Save User Mapping' onclick='msg()'>
                </div>",
        );
        $form['troubleshooting_2'] = array(
            '#markup' => "
                <div style='background-color: white; padding: 10px ;margin-left: 20px; width: 70%' id='enable_ldap'>
                    <input type='button' style='background-color: #008CBA;border: none;color: white;padding: 8px 20px;text-align: center;text-decoration: none;display: inline-block;border-radius: 12px;font-size: 16px;' value='Troubleshooting' onclick='msg()'>
                </div>",
        );
        $form['back_step_3'] = array(
            '#type' => 'submit',
            '#prefix' => "<table><tr><td>",
            '#suffix' => "</td>",
            '#value' => t('Reset Configurations'),
            '#submit' => array('::miniorange_ldap_back_2'),
            '#attributes' => array('style' => 'border-radius:4px;float:left;opacity:0.7;background: red;color: #ffffff;text-shadow: 0 -1px 1px red, 1px 0 1px red, 0 1px 1px red, -1px 0 1px red;
                box-shadow: 0 2px 0 red;border-color: red red red; width: fit-content;display:block;margin-left:90px;margin-right:auto;margin-bottom:10px;'),
        );
        $form['save_config_edit'] = array(
            '#type' => 'submit',
            '#prefix' => "<td>",
            '#suffix' => "</td></tr></table>",     
            '#value' => t('Save Changes'),
            '#submit' => array('::miniorange_ldap_review_changes'),
            '#attributes' => array('style' => 'border-radius:4px;background: #337ab7;float: right;color: #ffffff;text-shadow: 0 -1px 1px #337ab7, 1px 0 1px #337ab7, 0 1px 1px #337ab7, -1px 0 1px #337ab7;
                    box-shadow: 0 2px 0 #006799;border-color: #337ab7 #337ab7 #337ab7; width: 50%;display:block;margin-right:130px;'),       
        );
        }
    if($status=='one'){
        $form['miniorange_ldap_enable_ldap_markup'] = array(
            '#markup' => "<h1><b>Login With LDAP Options</b></h1><hr><br>",
        );
        $form['miniorange_ldap_enable_ldap'] = array(
            '#type' => 'checkbox',
            '#description' => t('Enabling LDAP login will protect your login page by your configured LDAP. Please check this only after you have successfully tested your configuration as the default Drupal login will stop working'),
            '#title' => t('Enable Login with LDAP '),
            '#default_value' =>\Drupal::config('ldap_auth.settings')->get('miniorange_ldap_enable_ldap'),
        );
        $form['set_of_radiobuttons']['miniorange_ldap_authentication'] = array(
            '#type' => 'radios',
            '#disabled' => 'true',
            '#title' => t('Authentication restrictions: <a href="' . $base_url .'/admin/config/people/ldap_auth/Licensing">[Premium]</a>'),
            '#description' => t('Only particular personalities will be able to login by selecting the above option.'),
            '#tree' => TRUE,
            '#default_value' => is_null(\Drupal::config('ldap_auth.settings')->get('miniorange_ldap_authentication'))?1:\Drupal::config('ldap_auth.settings')->get('miniorange_ldap_authentication'),
            '#options' => array(0 => t('Drupal and LDAP Users'), 1 => t('LDAP Users and Administrators of Drupal'), 2 => t('LDAP Users')),
        );
        $form['miniorange_ldap_enable_auto_reg'] = array(
            '#type' => 'checkbox',
            '#title' => t('Enable Auto Registering users if they do not exist in Drupal <a href="' . $base_url .'/admin/config/people/ldap_auth/Licensing">[Premium]</a>'),
            '#disabled' => 'true',
            '#default_value' =>\Drupal::config('ldap_auth.settings')->get('miniorange_ldap_enable_auto_reg'),
        );
        $form['back_step_3'] = array(
            '#type' => 'submit',
            '#id' => 'button_config',
            '#prefix' => "<br><table><tr><td>",
            '#suffix' => "</td>",
            '#value' => t('BACK'),
            '#submit' => array('::miniorange_ldap_back_5'),
            '#attributes' => array('style' => 'border-radius:4px;opacity:0.7;background: red;color: #ffffff;text-shadow: 0 -1px 1px red, 1px 0 1px red, 0 1px 1px red, -1px 0 1px red;
                box-shadow: 0 2px 0 red;border-color: red red red; width: fit-content;display:block;margin-right:auto;margin-bottom:10px;'),
        );
        $form['next_step_1'] = array(
            '#type' => 'submit',
            '#prefix' => "<td>",
            '#suffix' => "</td></tr></table>",
            '#id' => 'button_config',
            '#value' => t('Save & Review Configurations'),
            '#attributes' => array('style' => 'border-radius:4px;background: #337ab7;float: right;color: #ffffff;text-shadow: 0 -1px 1px #337ab7, 1px 0 1px #337ab7, 0 1px 1px #337ab7, -1px 0 1px #337ab7;
                box-shadow: 0 2px 0 #006799;border-color: #337ab7 #337ab7 #337ab7; width: fit-content;display:block;margin-right:auto;'),
            '#submit' => array('::miniorange_ldap_next_1'),
        );
    }
    else if($status=='two'){
        $form['mo_ldap_local_configuration_form_action'] = array(
            '#markup' => "<input type='hidden' name='option' id='mo_ldap_local_configuration_form_action' value='mo_ldap_local_save_config'></input>",
        );
        $form['ldap_server'] = array(
            '#markup' => "
            <br><h1><b>LDAP Connection Information</b></h1><hr><br><br><h3>LDAP Server:</h3>",
        );

        $form['ldap_server_url_markup_start'] = array(
            '#markup' => '<div class="ldap_Server_row">',
        );

        $form['miniorange_ldap_options'] = array(
            '#type' => 'value',
            '#id' => 'miniorange_ldap_options',
            '#value' => array(
              'ldap://' => t('ldap://'),
              'ldaps://' => t('ldaps://'),),
          );
      
          $form['miniorange_ldap_protocol'] = array(
            '#id' => 'miniorange_ldap_protocol',
            '#type' => 'select',
            '#prefix' => '<div class="ldap-column left">',
            '#suffix' => '</div>',
            '#default_value' =>\Drupal::config('ldap_auth.settings')->get('miniorange_ldap_protocol'),
            '#options' => $form['miniorange_ldap_options']['#value'],
            '#attributes' => array('style'=>'width:100%'),
          );

        $form['miniorange_ldap_server_address'] = array(
            '#type' => 'textfield',
            '#id' => 'miniorange_ldap_server_address',
            '#prefix' => '<div class="ldap-column middle">',
            '#suffix' => '</div>',
            '#default_value' =>\Drupal::config('ldap_auth.settings')->get('miniorange_ldap_server_address'),
            '#attributes' => array('style' => 'width:100%;','placeholder' => 'Enter your server-address or IP'),
        );
        $form['miniorange_ldap_server_port_number'] = array(
            '#type' => 'textfield',
            '#prefix' => '<div class="ldap-column right">',
            '#suffix' => '</div>',
            '#default_value' =>\Drupal::config('ldap_auth.settings')->get('miniorange_ldap_server_port_number'),
            '#attributes' => array('style' => 'width:100%;','placeholder' => '<port>'),
        );
        $form['ldap_server_url_markup_end'] = array(
            '#markup' => '</div><p><i>Select ldap or ldaps from the above dropdown list. Specify the host name for the LDAP server in the above text field. Edit the port number if you have custom port number.</i></p>',
        );

        $form['miniorange_ldap_contact_server_button'] = array(
            '#type' => 'submit',
            '#value' => t('Contact LDAP Server'),
            '#id' => 'button_config',
            '#attributes' => array('style' => 'border-radius:4px;background: #337ab7;color: #ffffff;text-shadow: 0 -1px 1px #337ab7, 1px 0 1px #337ab7, 0 1px 1px #337ab7, -1px 0 1px #337ab7;
                box-shadow: 0 2px 0 #006799;border-color: #337ab7 #337ab7 #337ab7; width: 30%;display:block;margin-right:auto;'),
            '#submit' => array('::test_ldap_connection'),
        );
        $form['miniorange_ldap_enable_tls'] = array(
            '#prefix' => '<br><br>',
            '#type' => 'checkbox',
            '#id' => 'check',
            '#disabled' => 'true',
            '#title' => t('Enable TLS (Check this only if your server use TLS Connection) <b><a href="' . $base_url .'/admin/config/people/ldap_auth/Licensing">[PREMIUM]</a></b>'),
            '#default_value' =>\Drupal::config('ldap_auth.settings')->get('miniorange_ldap_enable_tls'),
        );
        $form['miniorange_ldap_anonymous_bind_markup'] = array(
            '#markup' => '<br><div class="mo_ldap_highlight_background_note_1" >In case you do not have any authentication setup and wish to perform anonymous bind to your server, please click on the Next button and continue with your setup.</div>',
        );
        $form['miniorange_ldap_server_account_username'] = array(
          '#type' => 'textfield',
          '#title' => t('Service Account Username:'),
          '#default_value' =>\Drupal::config('ldap_auth.settings')->get('miniorange_ldap_server_account_username'),
          '#description' => t("This service account username will be used to establish the connection. Specify the Service Account Username of the LDAP server in the either way as follows Username@domainname or domainname\Username. or Distinguished Name(DN) format"),
          '#attributes' => array('style' => 'width:73%;','placeholder' => 'CN=service,DC=domain,DC=com'),
        );
        $form['miniorange_ldap_server_account_password'] = array(
            '#type' => 'password',
            '#title' => t('Service Account Password:'),
            '#default_value' =>\Drupal::config('ldap_auth.settings')->get('miniorange_ldap_server_account_password'),
            '#attributes' => array('style' => 'width:73%;','placeholder' => 'Enter password for Service Account'),
        );
        $form['miniorange_ldap_test_connection_button'] = array(
            '#type' => 'submit',
            '#prefix' => '<br>',
            '#value' => t('Test Connection'),
            '#attributes' => array('style' => 'border-radius:4px;background: #006799;color: #ffffff;text-shadow: 0 -1px 1px #006799, 1px 0 1px #006799, 0 1px 1px #006799, -1px 0 1px #006799;
            box-shadow: 0 2px 0 #006799;border-color: #006799 #006799 #006799; width: 30%;display:block;margin-right:auto;'),
            '#submit' => array('::test_connection_ldap'),
        );
        $form['next_step_2'] = array(
            '#type' => 'submit',
            '#value' => t('NEXT'),
            '#attributes' => array('style' => 'border-radius:4px;float:right;opacity:0.7;background: green;color: #ffffff;text-shadow: 0 -1px 1px green, 1px 0 1px green, 0 1px 1px green, -1px 0 1px green;
            box-shadow: 0 2px 0 green;border-color: green green green; width: 20%;display:block;margin-bottom:10px;'),
            '#submit' => array('::miniorange_ldap_next_2'),
        );
    }
    else if($status=='three'){
            //Get all Search bases from AD
            $possible_search_bases = Utilities::getSearchBases();
            $possible_search_bases_in_key_val = array();
            foreach($possible_search_bases as $search_base){
                $possible_search_bases_in_key_val[$search_base]=$search_base;
            }
            $possible_search_bases_in_key_val['custom_base']='Provide Custom LDAP Search Base';


            $form['miniorange_search_base_options'] = array(
                '#type' => 'value',
                '#id' => 'miniorange_search_base_options',
                '#value' => $possible_search_bases_in_key_val,
            );
          
            
            $form['miniorange_search_base_options']['search_base_attribute'] = array(
                '#id' => 'miniorange_ldap_search_base_attribute',
                '#title' => t('Search Base(s):'),
                '#type' => 'select',
                '#description' => t('This is the LDAP tree under which we will search for the users for authentication. If we are not able to find a user in LDAP it means they are not present in this search base or any of its sub trees.
                    Provide the distinguished name of the Search Base object. <b>E.g. cn=Users,dc=domain,dc=com.</b>
                    <p>Multiple Search Bases are supported in the <a href="' . $base_url .'/admin/config/people/ldap_auth/Licensing">Premium</a> version of the module.</p>'),
                '#default_value' =>\Drupal::config('ldap_auth.settings')->get('miniorange_ldap_search_base'),
                '#options' => $form['miniorange_search_base_options']['#value'],
                '#attributes' => array('style'=>'width:73%;height:30px'),               
            );
            $form['miniorange_ldap_custom_sb_attribute'] = array(
                '#type' => 'textfield',
                '#title' => t('Custom Search Base(s):'),
                '#default_value' =>\Drupal::config('ldap_auth.settings')->get('miniorange_ldap_custom_sb_attribute'),
                '#states' => array('visible' => array(':input[name = "search_base_attribute"]' => array('value' => 'custom_base' ), ),),
                '#attributes' => array('style' => 'width:73%;'),
            );

            $form['miniorange_username_options'] = array(
                '#type' => 'value',
                '#id' => 'miniorange_username_options',
                '#value' => array(
                'samaccountName' => t('samaccountName'),
                'mail' => t('mail'),
                'userPrincipalName' => t('userPrincipalName'),
                'cn' => t('cn'),
                'custom' => t('Provide Custom LDAP attribute name'),
                ),
            );
      
            $form['ldap_auth']['settings']['username_attribute'] = array(
                '#id' => 'miniorange_ldap_username_attribute',
                '#title' => t('Search Filter/Username Attribute:'),
                '#prefix' => '<br>',
                '#type' => 'select',
                '#default_value' =>\Drupal::config('ldap_auth.settings')->get('miniorange_ldap_username_attribute_option'),
                '#options' => $form['miniorange_username_options']['#value'],
                '#attributes' => array('style'=>'width:73%;height:30px'),
            );

            $form['miniorange_ldap_custom_username_attribute'] = array(
                '#type' => 'textfield',
                '#title' => t('Custom Search Filter:'),
                '#default_value' =>\Drupal::config('ldap_auth.settings')->get('miniorange_ldap_custom_username_attribute'),
                '#states' => array('visible' => array(':input[name = "username_attribute"]' => array('value' => 'custom' ), ),),
                '#attributes' => array('style' => 'width:73%;'),
            );

            $form['miniorange_ldap_username_attribute_options_markup'] = array(
                '#markup' => 'This field is important for two reasons: <br>
                1. While searching for users, this is the attribute that is going to be matched to see if the user exists.<br>
                2. If you want your users to login with their username or firstname, lastname or email - you need to specify those options in this field. e.g. <b>LDAP_ATTRIBUTE</b>. <br><br>Replace <b><LDAP_ATTRIBUTE></b> with the attribute where your username is stored. Some common attributes are</p>
                <table>
                    <tr>
                        <td>common name</td>
                        <td>cn</td>
                        <td></td>
                        <td></td>
                    </tr>
                    <tr>
                        <td>email</td>
                        <td>mail</td>
                        <td></td>
                        <td></td>
                    </tr>
                    <tr>
                        <td>logon name</td>
                        <td>samaccountName</td>
                        <td>or</td>
                        <td>userPrincipalName</td>
                    </tr>
                </table>
                <p>You can even search for your user using a Custom Search Filter. You can also allow logging in with multiple attributes, separated with a <b>semicolon</b> <i>example:</i> cn;mail  <a href="' . $base_url .'/admin/config/people/ldap_auth/Licensing">[Premium]</a><br>',
                '#attributes' => array('style'=>'width:73%'),
            );
            $form['back_step_3'] = array(
                '#type' => 'submit',
                '#id' => 'button_config',
                '#prefix' => "<table><tr><td>",
                '#suffix' => "</td>",
                    '#value' => t('BACK'),
                    '#submit' => array('::miniorange_ldap_back_3'),
                    '#attributes' => array('style' => 'border-radius:4px;opacity:0.7;background: red;color: #ffffff;text-shadow: 0 -1px 1px red, 1px 0 1px red, 0 1px 1px red, -1px 0 1px red;
                    box-shadow: 0 2px 0 red;border-color: red red red; width: 30%;display:block;margin-bottom:10px;'),
            );
            $form['next_step_3'] = array(
                '#type' => 'submit',
                '#id' => 'button_config',
                '#value' => t('NEXT'),
                '#prefix' => "<td>",
                '#suffix' => "</td></tr></table>",
                '#attributes' => array('style' => 'border-radius:4px;background: green;float: right;color: #ffffff;text-shadow: 0 -1px 1px green, 1px 0 1px green, 0 1px 1px green, -1px 0 1px green;
                    box-shadow: 0 2px 0 green;border-color: green green green; width: 30%;display:block;margin-right:auto;'),
                '#submit' => array('::miniorange_ldap_next3'),
            );
    }

    Utilities::AddSupportButton($form, $form_state);
    
    return $form;
  }
  function miniorange_ldap_back_1($form,$form_state){
        \Drupal::configFactory()->getEditable('ldap_auth.settings')->set('miniorange_ldap_config_status', 'one')->save();
  }
  function miniorange_ldap_back_2($form,$form_state){
        \Drupal::configFactory()->getEditable('ldap_auth.settings')->clear('miniorange_ldap_enable_ldap')->save();
        \Drupal::configFactory()->getEditable('ldap_auth.settings')->clear('miniorange_ldap_authenticate_admin')->save();
        \Drupal::configFactory()->getEditable('ldap_auth.settings')->clear('miniorange_ldap_authenticate_drupal_users')->save();
        \Drupal::configFactory()->getEditable('ldap_auth.settings')->clear('miniorange_ldap_enable_auto_reg')->save();
        \Drupal::configFactory()->getEditable('ldap_auth.settings')->clear('miniorange_ldap_server')->save();
        \Drupal::configFactory()->getEditable('ldap_auth.settings')->clear('miniorange_ldap_enable_tls')->save();
        \Drupal::configFactory()->getEditable('ldap_auth.settings')->clear('miniorange_ldap_server_account_username')->save();
        \Drupal::configFactory()->getEditable('ldap_auth.settings')->clear('miniorange_ldap_server_account_password')->save();
        \Drupal::configFactory()->getEditable('ldap_auth.settings')->clear('miniorange_ldap_search_base')->save();
        \Drupal::configFactory()->getEditable('ldap_auth.settings')->clear('miniorange_ldap_username_attribute')->save();
        \Drupal::configFactory()->getEditable('ldap_auth.settings')->clear('miniorange_ldap_test_username')->save();
        \Drupal::configFactory()->getEditable('ldap_auth.settings')->clear('miniorange_ldap_test_password')->save();
        \Drupal::configFactory()->getEditable('ldap_auth.settings')->set('miniorange_ldap_server_port_number', '389')->save();
        \Drupal::configFactory()->getEditable('ldap_auth.settings')->set('miniorange_ldap_custom_username_attribute', 'samaccountName')->save();
        \Drupal::configFactory()->getEditable('ldap_auth.settings')->clear('miniorange_ldap_server_address')->save();
        \Drupal::configFactory()->getEditable('ldap_auth.settings')->clear('miniorange_ldap_protocol')->save();
        \Drupal::configFactory()->getEditable('ldap_auth.settings')->clear('miniorange_ldap_username_attribute_option')->save();
        \Drupal::configFactory()->getEditable('ldap_auth.settings')->set('miniorange_ldap_config_status', 'two')->save();
        \Drupal::messenger()->addStatus(t('Configurations removed successfully.'));
    }
    function miniorange_ldap_back_3($form,$form_state){
        \Drupal::configFactory()->getEditable('ldap_auth.settings')->set('miniorange_ldap_config_status', 'two')->save();
    }
    function miniorange_ldap_back_5($form,$form_state){
        \Drupal::configFactory()->getEditable('ldap_auth.settings')->set('miniorange_ldap_config_status', 'three')->save();
    }
    function miniorange_ldap_back_4($form,$form_state){
        \Drupal::configFactory()->getEditable('ldap_auth.settings')->set('miniorange_ldap_config_status', 'four')->save();
    }
    /**
     * Test Connection
     */
    function test_connection_ldap(){
        $server_account_username ="";
        $server_account_password ="";
        if(isset($_POST['miniorange_ldap_server_account_username']) && !empty($_POST['miniorange_ldap_server_account_username'])){
            $server_account_username = Html::escape($_POST['miniorange_ldap_server_account_username']);
            \Drupal::configFactory()->getEditable('ldap_auth.settings')->set('miniorange_ldap_server_account_username', $server_account_username)->save();
        }
        if(isset($_POST['miniorange_ldap_server_account_password']) && !empty($_POST['miniorange_ldap_server_account_password'])){
            $server_account_password = Html::escape($_POST['miniorange_ldap_server_account_password']);
            \Drupal::configFactory()->getEditable('ldap_auth.settings')->set('miniorange_ldap_server_account_password', $server_account_password)->save();
        }
        user_cookie_save(array("mo_ldap_test" => true));
        $error = array();
        $error = handler::test_mo_config($server_account_username,$server_account_password);
        if( $error[1] == "error" ) {
            \Drupal::configFactory()->getEditable('ldap_auth.settings')->set('miniorange_ldap_test_connection', 'Tried and Failed')->save();
            if($error[0] == "Invalid Password. Please check your password and try again."){
                \Drupal::configFactory()->getEditable('ldap_auth.settings')->set('miniorange_ldap_server_account_password', '')->save();
                \Drupal::messenger()->addError(t( $error[0] ));
            }
            elseif($error[0] == "Username or Password can not be empty until and unless you do not have any authenticaiton setup and wish to perform anonymous bind to your server. If that is the case, please ignore this message and continue with the setup."){
                \Drupal::messenger()->addError(t( $error[0] ));
            }
            else
                \Drupal::messenger()->addError(t("There was an error processing your request."));
        } else if($error[1] == "Success") {
            \Drupal::configFactory()->getEditable('ldap_auth.settings')->set('miniorange_ldap_test_connection', 'Successful')->save();
            \Drupal::configFactory()->getEditable('ldap_auth.settings')->set('miniorange_ldap_server_account_password', $server_account_password)->save();
            \Drupal::messenger()->addStatus(t('Test Connection is successful. Now, click on the <b>Next / Save Changes</b> button to continue.'));
        }
    }
    function miniorange_ldap_next_2($form, $form_state){
        


        $server_address ="";
        if(isset($_POST['miniorange_ldap_server_address']) && !empty($_POST['miniorange_ldap_server_address']))
            $server_address = Html::escape(trim($_POST['miniorange_ldap_server_address']));
        if(trim($server_address) == ''){
            \Drupal::messenger()->addError(t('LDAP Server Address can not be empty'));
            return;
        }
        if(isset($_POST['miniorange_ldap_protocol']) && !empty($_POST['miniorange_ldap_protocol']))
            $protocol = Html::escape(trim($_POST['miniorange_ldap_protocol']));
        $server_name = $protocol.$server_address;
        if(isset($_POST['miniorange_ldap_server_port_number']) && !empty($_POST['miniorange_ldap_server_port_number'])){
            $port_number = Html::escape(trim($_POST['miniorange_ldap_server_port_number']));
            $server_name = $server_name.':'.$port_number;
        }

        \Drupal::configFactory()->getEditable('ldap_auth.settings')->set('miniorange_ldap_server', $server_name)->save();
        \Drupal::configFactory()->getEditable('ldap_auth.settings')->set('miniorange_ldap_server_address', $server_address)->save();
        \Drupal::configFactory()->getEditable('ldap_auth.settings')->set('miniorange_ldap_protocol', $protocol)->save();
        \Drupal::configFactory()->getEditable('ldap_auth.settings')->set('miniorange_ldap_server_port_number', $port_number)->save();

        if(!empty($form['miniorange_ldap_enable_tls']['#value'])){
            $enable_tls = $form['miniorange_ldap_enable_tls']['#value'];

            \Drupal::configFactory()->getEditable('ldap_auth.settings')->set('miniorange_ldap_enable_tls', $enable_tls)->save();
        }
        if(!empty($form['miniorange_ldap_server_account_username']['#value'])){
            $server_account_username = $form['miniorange_ldap_server_account_username']['#value'];
            \Drupal::configFactory()->getEditable('ldap_auth.settings')->set('miniorange_ldap_server_account_username', $server_account_username)->save();
        }
        if(!empty($form['miniorange_ldap_server_account_password']['#value'])){
            $server_account_password = $form['miniorange_ldap_server_account_password']['#value'];
            \Drupal::configFactory()->getEditable('ldap_auth.settings')->set('miniorange_ldap_server_account_password', $server_account_password)->save();
        }
        \Drupal::configFactory()->getEditable('ldap_auth.settings')->set('miniorange_ldap_config_status', 'three')->save();
        
    }

    function miniorange_ldap_next3($form, $form_state){
        \Drupal::configFactory()->getEditable('ldap_auth.settings')->set('miniorange_ldap_config_status', 'one')->save();
        if(!empty($form['miniorange_search_base_options']['search_base_attribute']['#value'])){
            $searchBase = $form['miniorange_search_base_options']['search_base_attribute']['#value'];
            if($searchBase == 'custom_base'){
                \Drupal::configFactory()->getEditable('ldap_auth.settings')->set('miniorange_ldap_username_attribute_option', 'custom')->save();
                $searchBaseCustomAttribute = $form['miniorange_ldap_custom_sb_attribute']['#value'];
                \Drupal::configFactory()->getEditable('ldap_auth.settings')->set('miniorange_ldap_custom_sb_attribute', $searchBaseCustomAttribute)->save();    
            }

            \Drupal::configFactory()->getEditable('ldap_auth.settings')->set('miniorange_ldap_search_base', $searchBase)->save();
        }

        if(!empty($form['ldap_auth']['settings']['username_attribute']['#value'])){
            $usernameAttribute = $form['ldap_auth']['settings']['username_attribute']['#value'];
            if($usernameAttribute == 'custom'){
                \Drupal::configFactory()->getEditable('ldap_auth.settings')->set('miniorange_ldap_username_attribute_option', 'custom')->save();
                $usernameCustomAttribute = $form['miniorange_ldap_custom_username_attribute']['#value'];
                if(trim($usernameCustomAttribute) == ''){
                    $usernameCustomAttribute = 'samaccountName';
                }
                \Drupal::configFactory()->getEditable('ldap_auth.settings')->set('miniorange_ldap_custom_username_attribute', $usernameCustomAttribute)->save();    
                \Drupal::configFactory()->getEditable('ldap_auth.settings')->set('miniorange_ldap_username_attribute', $usernameCustomAttribute)->save();    
            }else{
            \Drupal::configFactory()->getEditable('ldap_auth.settings')->set('miniorange_ldap_username_attribute_option', $usernameAttribute)->save();
            \Drupal::configFactory()->getEditable('ldap_auth.settings')->set('miniorange_ldap_username_attribute', $usernameAttribute)->save();    
            }
        }


        if(!empty($form['miniorange_ldap_test_username']['#value'])){
            $testUsername = $form['miniorange_ldap_test_username']['#value'];
            \Drupal::configFactory()->getEditable('ldap_auth.settings')->set('miniorange_ldap_test_username', $testUsername)->save();
        }
        if(!empty($form['miniorange_ldap_test_password']['#value'])){
            $testPassword = $form['miniorange_ldap_test_password']['#value'];
            \Drupal::configFactory()->getEditable('ldap_auth.settings')->set('miniorange_ldap_test_password', $testPassword)->save();
        }
    }
    /**
     * Contact LDAP server
     */
    function test_ldap_connection($form,$form_state){
        global $base_url;
        LDAPLOGGERS::addLogger('L101: Entered Contact LDAP Server ');
		
        if(!Utilities::isLDAPInstalled()){
            LDAPLOGGERS::addLogger('L102: PHP_LDAP Extension is not enabled');
            \Drupal::messenger()->addError(t('You have not enabled the PHP LDAP extension'));
            return;
        }

        $server_address ="";
        if(isset($_POST['miniorange_ldap_server_address']) && !empty($_POST['miniorange_ldap_server_address']))
            $server_address = Html::escape(trim($_POST['miniorange_ldap_server_address']));
        if(trim($server_address) == ''){
            \Drupal::messenger()->addError(t('LDAP Server Address can not be empty'));
            return;
        }
        if(isset($_POST['miniorange_ldap_protocol']) && !empty($_POST['miniorange_ldap_protocol']))
            $protocol = Html::escape(trim($_POST['miniorange_ldap_protocol']));
        $server_name = $protocol.$server_address;
        if(isset($_POST['miniorange_ldap_server_port_number']) && !empty($_POST['miniorange_ldap_server_port_number'])){
            $port_number = Html::escape(trim($_POST['miniorange_ldap_server_port_number']));
            $server_name = $server_name.':'.$port_number;
        }

        \Drupal::configFactory()->getEditable('ldap_auth.settings')->set('miniorange_ldap_server', $server_name)->save();
        \Drupal::configFactory()->getEditable('ldap_auth.settings')->set('miniorange_ldap_server_address', $server_address)->save();
        \Drupal::configFactory()->getEditable('ldap_auth.settings')->set('miniorange_ldap_protocol', $protocol)->save();
        \Drupal::configFactory()->getEditable('ldap_auth.settings')->set('miniorange_ldap_server_port_number', $port_number)->save();
        $login_with_ldap = \Drupal::config('ldap_auth.settings')->get('miniorange_ldap_enable_ldap');
        $ldapconn = getConnection();
        if($ldapconn){
            LDAPLOGGERS::addLogger('L102: Entered ldapconn');
            ldap_set_option($ldapconn, LDAP_OPT_PROTOCOL_VERSION, 3);
            ldap_set_option($ldapconn, LDAP_OPT_REFERRALS, 0);
            $ldap_bind_dn = \Drupal::config('ldap_auth.settings')->get('miniorange_ldap_server_account_username');
            $ldap_bind_password = \Drupal::config('ldap_auth.settings')->get('miniorange_ldap_server_account_password');
            if(!empty(\Drupal::config('ldap_auth.settings')->get('miniorange_ldap_enable_tls')))
                ldap_start_tls($ldapconn);
            $bind = @ldap_bind($ldapconn, $ldap_bind_dn, $ldap_bind_password);
            $err = ldap_error($ldapconn);
            if(strtolower($err) != 'success'){
                \Drupal::configFactory()->getEditable('ldap_auth.settings')->set('miniorange_ldap_contacted_server', "Failed")->save();        
                \Drupal::messenger()->addError(t("There seems to be an error trying to contact your LDAP server. Please check your configurations or contact the administrator for the same."));
                return;
            }
            else{
                \Drupal::configFactory()->getEditable('ldap_auth.settings')->set('miniorange_ldap_contacted_server', "Successful")->save();
                \Drupal::messenger()->addStatus(t("Congratulations, you were able to successfully connect to your LDAP Server"));
                return;
            }
        }
    }

    function miniorange_ldap_next_1($form, $form_state){
        \Drupal::configFactory()->getEditable('ldap_auth.settings')->set('miniorange_ldap_config_status', 'review_config')->save();
        $enable_ldap = $form['miniorange_ldap_enable_ldap']['#value'];
        \Drupal::configFactory()->getEditable('ldap_auth.settings')->set('miniorange_ldap_enable_ldap', $enable_ldap)->save();
        if(!empty($form['miniorange_ldap_authenticate_admin']['#value'])){
            $authn_admin = $form['miniorange_ldap_authenticate_admin']['#value'];
            \Drupal::configFactory()->getEditable('ldap_auth.settings')->set('miniorange_ldap_authenticate_admin', $authn_admin)->save();
        }
        if(!empty($form['miniorange_ldap_authenticate_drupal']['#value'])){
            $authn_drupal_users = $form['miniorange_ldap_authenticate_drupal']['#value'];
            \Drupal::configFactory()->getEditable('ldap_auth.settings')->set('miniorange_ldap_authenticate_drupal_users', $authn_drupal_users)->save();
        }
        $auto_reg_users = $form['miniorange_ldap_enable_auto_reg']['#value'];
        \Drupal::configFactory()->getEditable('ldap_auth.settings')->set('miniorange_ldap_enable_auto_reg', $auto_reg_users)->save();
        user_cookie_save(array("mo_ldap_test" => true));
        $error = handler::test_mo_config();
        if($error[1]=="error")
            \Drupal::messenger()->addError(t($error[0]));
        else
            \Drupal::messenger()->addStatus(t($error[0]));
        return;
    }

    function miniorange_ldap_review_changes($form, $form_state){
        $enable_ldap = $form['miniorange_ldap_enable_ldap']['#value'];
        \Drupal::configFactory()->getEditable('ldap_auth.settings')->set('miniorange_ldap_enable_ldap', $enable_ldap)->save();
        if(!empty($form['miniorange_ldap_authenticate_admin']['#value'])){
            $authn_admin = $form['miniorange_ldap_authenticate_admin']['#value'];
            \Drupal::configFactory()->getEditable('ldap_auth.settings')->set('miniorange_ldap_authenticate_admin', $authn_admin)->save();
        }
        if(!empty($form['miniorange_ldap_authenticate_drupal']['#value'])){
            $authn_drupal_users = $form['miniorange_ldap_authenticate_drupal']['#value'];
            \Drupal::configFactory()->getEditable('ldap_auth.settings')->set('miniorange_ldap_authenticate_drupal', $authn_drupal_users)->save();
        }
        $auto_reg_users = $form['miniorange_ldap_enable_auto_reg']['#value'];
        \Drupal::configFactory()->getEditable('ldap_auth.settings')->set('miniorange_ldap_enable_auto_reg', $auto_reg_users)->save();
        if(!empty($form['miniorange_ldap_server']['#value'])){
            $mo_ldap_server = $form['miniorange_ldap_server']['#value'];
            \Drupal::configFactory()->getEditable('ldap_auth.settings')->set('miniorange_ldap_server', $mo_ldap_server)->save();
        }
        if(!empty($form['miniorange_ldap_enable_tls']['#value'])){
            $enable_tls = $form['miniorange_ldap_enable_tls']['#value'];
            \Drupal::configFactory()->getEditable('ldap_auth.settings')->set('miniorange_ldap_enable_tls', $enable_tls)->save();
        }
        if(!empty($form['miniorange_ldap_server_account_username']['#value'])){
            $server_account_username = $form['miniorange_ldap_server_account_username']['#value'];
            \Drupal::configFactory()->getEditable('ldap_auth.settings')->set('miniorange_ldap_server_account_username', $server_account_username)->save();
        }
        if(!empty($form['miniorange_ldap_server_account_password']['#value'])){
            $server_account_password = $form['miniorange_ldap_server_account_password']['#value'];
            \Drupal::configFactory()->getEditable('ldap_auth.settings')->set('miniorange_ldap_server_account_password', $server_account_password)->save();
        }

        if(!empty($form['miniorange_search_base_options']['search_base_attribute']['#value'])){
            $searchBase = $form['miniorange_search_base_options']['search_base_attribute']['#value'];
            if($searchBase == 'custom_base'){
                \Drupal::configFactory()->getEditable('ldap_auth.settings')->set('miniorange_ldap_username_attribute_option', 'custom')->save();
                $searchBaseCustomAttribute = $form['miniorange_ldap_custom_sb_attribute']['#value'];
                \Drupal::configFactory()->getEditable('ldap_auth.settings')->set('miniorange_ldap_custom_sb_attribute', $searchBaseCustomAttribute)->save();    
            }
            \Drupal::configFactory()->getEditable('ldap_auth.settings')->set('miniorange_ldap_search_base', $searchBase)->save();
        }

        if(!empty($form['ldap_auth']['settings']['username_attribute']['#value'])){
            $usernameAttribute = $form['ldap_auth']['settings']['username_attribute']['#value'];
            if($usernameAttribute == 'custom'){
                \Drupal::configFactory()->getEditable('ldap_auth.settings')->set('miniorange_ldap_username_attribute_option', 'custom')->save();
                $usernameCustomAttribute = $form['miniorange_ldap_custom_username_attribute']['#value'];
                if(trim($usernameCustomAttribute) == ''){
                    $usernameCustomAttribute = 'samaccountName';
                }
                \Drupal::configFactory()->getEditable('ldap_auth.settings')->set('miniorange_ldap_custom_username_attribute', $usernameCustomAttribute)->save();    
                \Drupal::configFactory()->getEditable('ldap_auth.settings')->set('miniorange_ldap_username_attribute', $usernameCustomAttribute)->save();    
            }else{
            \Drupal::configFactory()->getEditable('ldap_auth.settings')->set('miniorange_ldap_username_attribute_option', $usernameAttribute)->save();
            \Drupal::configFactory()->getEditable('ldap_auth.settings')->set('miniorange_ldap_username_attribute', $usernameAttribute)->save();    
            }
        }
        if(!empty($form['miniorange_ldap_test_username']['#value'])){
            $testUsername = $form['miniorange_ldap_test_username']['#value'];
            \Drupal::configFactory()->getEditable('ldap_auth.settings')->set('miniorange_ldap_test_username', $testUsername)->save();
        }
        if(!empty($form['miniorange_ldap_test_password']['#value'])){
            $testPassword = $form['miniorange_ldap_test_password']['#value'];
            \Drupal::configFactory()->getEditable('ldap_auth.settings')->set('miniorange_ldap_test_password', $testPassword)->save();
        }
        \Drupal::messenger()->addStatus(t("Configuration updated successfully. <br><br>Now please open a private/incognito window and try to login to your Drupal site using your LDAP credentials. In case you face any issues or if you need any sort of assistance, please feel free to reach out to us at <u><i>drupalsupport@xecurify.com</i></u>"));
    }

    function submitForm(array &$form, FormStateInterface $form_state){
    }

    function saved_support(array &$form, FormStateInterface $form_state) {
        $email = $form['miniorange_ldap_email_address']['#value'];
        $phone = $form['miniorange_ldap_phone_number']['#value'];
        $query = $form['miniorange_ldap_support_query']['#value'];
        Utilities::send_support_query($email, $phone, $query);
    }
}