<?php

namespace Drupal\ldap_auth\Form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Form\FormBase;
//use Drupal\ldap_auth\MiniorangeLdapSupport;
use Drupal\ldap_auth\Utilities;

class MiniorangeDebug extends FormBase {
    
    public function getFormId() {
        return 'miniorange_ldap_debug';
    }

    public function buildForm(array $form, FormStateInterface $form_state){
      global $base_url;
      $current_status = \Drupal::config('ldap_auth.settings')->get('miniorange_ldap_status');
      $form['markup_library'] = array(
        '#attached' => array(
            'library' => array(
                "ldap_auth/ldap_auth.admin",
            )
        ),
      );  
      $form['markup_14'] = array(
        '#markup' => '<div class="mo_ldap_table_layout_1"><div class="mo_ldap_table_layout container">');
  
      $form['markup_reg'] = array(
          '#markup' => '<div><h2>Debugging and Troubleshooting</h2><hr>',
      );  
      $form['ldap_debug'] = array(
        '#type' => 'vertical_tabs',
        '#default_tab' => 'edit-debug',
      );
      $form['debug'] = array(
        '#type' => 'details',
        '#title' => $this
          ->t('Debug Logs'),
        '#group' => 'ldap_debug',
      );
      $form['debug']['loggers'] = array(
        '#type' => 'checkbox',
        '#name' => 'loggers',
        '#title' => t('Enable Logging '),
        '#description' => 'Enabling this checkbox will add loggers under the <a target = "_blank" href="'.$base_url.'/admin/reports/dblog?type%5B%5D=ldap_auth">Reports</a> section',
        '#default_value' => \Drupal::config('ldap_auth.settings')->get('miniorange_ldap_enable_logs'),
      );
      $form['debug']['miniorange_ldap_save_logs_option'] = array(
        '#type' => 'submit',
        '#value' => t('Save'),
        '#submit' => array('::save_logs_option'),
        '#attributes' => array('style' => 'background: #337ab7;color: #ffffff;text-shadow: 0 -1px 1px #337ab7, 1px 0 1px #337ab7, 0 1px 1px #337ab7, -1px 0 1px #337ab7;box-shadow: 0 1px 0 #337ab7;border-color: #337ab7 #337ab7 #337ab7;'),
      );
      // $form['faq'] = array(
      //   '#type' => 'details',
      //   '#title' => $this
      //     ->t("FAQ's"),
      //   '#group' => 'ldap_debug',
      // );
      // $form['faq']['inline_faq'] = array(
      //   '#type' => 'inline_template',
      //   '#title' => $this
      //     ->t('FAQ'),
      //   '#template' => '<iframe src="https://faq.miniorange.com/kb/ldap-authentication/" width= "100%" height="600px"></iframe>',
      // );
      // $form['setup'] = array(
      //   '#type' => 'details',
      //   '#title' => $this
      //     ->t("Setup Guide & Video"),
      //   '#group' => 'ldap_debug',
      // );
      // $form['setup']['inline_faq_for_setup'] = array(
      //   '#type' => 'inline_template',
      //   '#title' => $this
      //     ->t('Setup Guide'),
      //   '#template' => '<iframe src="https://plugins.miniorange.com/guide-to-configure-ldap-ad-integration-module-for-drupal/" width= "100%" height="600px"></iframe>',
      // );

      $form['demo_support'] = array(
        '#type' => 'details',
        '#title' => $this
          ->t("Request for a Demo"),
        '#group' => 'ldap_debug',
      );

      $form['demo_support']['miniorange_ldap_demo_email_address'] = array(
        '#type' => 'textfield',
        '#title' => t('Email Address'),
        '#attributes' => array('placeholder' => 'Enter your email'),
      );

      $form['demo_support']['miniorange_ldap_demo_phone_number'] = array(
        '#type' => 'textfield',
        '#title' => t('Phone number'),
        '#attributes' => array('placeholder' => 'Enter your phone number'),
      );

      $form['demo_support']['miniorange_ldap_demo_support_query'] = array(
        '#type' => 'textarea',
        '#title' => t('Query'),
        '#cols' => '10',
        '#rows' => '5',
        '#attributes' => array('style'=>'width:80%','placeholder' => 'Write your query here'),
      );

      $form['demo_support']['miniorange_ldap_request_for_demo_submit'] = array(
        '#type' => 'submit',
        '#value' => t('Request for Demo/Trail'),
        '#submit' => array('::request_for_demo'), 
      );

      $form['demo_support']['miniorange_ldap_support_note'] = array(
        '#markup' => '<div><br/>If you want custom features in the module, just drop an email to <a href="mailto:drupalsupport@xecurify.com">drupalsupport@xecurify.com</a></div>'
      );

      $form['register_close'] = array(
          '#markup' => '</div>',
      );   
  
      Utilities::AddSupportButton($form, $form_state);
  
      return $form;
    }
    public function save_logs_option(array &$form, FormStateInterface $form_state){
        $enable_loggers = $form['debug']['loggers']['#value'];
        \Drupal::configFactory()->getEditable('ldap_auth.settings')->set('miniorange_ldap_enable_logs', $enable_loggers)->save();
        \Drupal::messenger()->addStatus(t('Settings Saved Successfully.'));
    }
    public function submitForm(array &$form, FormStateInterface $form_state) {
    }
    public function request_for_demo(array &$form, FormStateInterface $form_state) {
      $email = $form['demo_support']['miniorange_ldap_demo_email_address']['#value'];
      $phone = $form['demo_support']['miniorange_ldap_demo_phone_number']['#value'];
      $query = $form['demo_support']['miniorange_ldap_demo_support_query']['#value'];
      $query = '<br><b>Demo Request: </b> <br>'.$query;
      if(empty($email)||empty($query)){
        \Drupal::messenger()->addError(t('The <b><u>Email </u></b> and <b><u>Query</u></b> fields are mandatory.'));
        return; 
      }
      Utilities::send_support_query($email, $phone, $query);
    }
    
  
    public function miniorange_ldap_back(&$form, $form_state) {
      $current_status = 'CUSTOMER_SETUP';
      \Drupal::configFactory()->getEditable('ldap_auth.settings')->set('miniorange_ldap_status', $current_status)->save();
      \Drupal::configFactory()->getEditable('ldap_auth.settings')->clear('miniorange_miniorange_ldap_customer_admin_email')->save();
      \Drupal::configFactory()->getEditable('ldap_auth.settings')->clear('miniorange_ldap_customer_admin_phone')->save();
      \Drupal::configFactory()->getEditable('ldap_auth.settings')->clear('miniorange_ldap_tx_id')->save();
      \Drupal::messenger()->addStatus(t('Register/Login with your miniOrange Account'));
    }
  
    public function miniorange_ldap_resend_otp(&$form, $form_state) {
      \Drupal::configFactory()->getEditable('ldap_auth.settings')->clear('miniorange_ldap_tx_id')->save();
      $username = \Drupal::config('ldap_auth.settings')->get('miniorange_ldap_customer_admin_email');
      $phone = \Drupal::config('ldap_auth.settings')->get('miniorange_ldap_customer_admin_phone');
      $customer_config = new MiniorangeLdapCustomer($username, $phone, NULL, NULL);
      $send_otp_response = json_decode($customer_config->sendOtp());
      if ($send_otp_response->status == 'SUCCESS') {
        // Store txID.
          \Drupal::configFactory()->getEditable('ldap_auth.settings')->set('miniorange_ldap_tx_id', $send_otp_response->txId)->save();
          $current_status = 'VALIDATE_OTP';
          \Drupal::configFactory()->getEditable('ldap_auth.settings')->set('miniorange_ldap_status', $current_status)->save();
          \Drupal::messenger()->addMessage(t('Verify email address by entering the passcode sent to @username', array('@username' => $username)));
      }
    }
  
    public function miniorange_ldap_validate_otp_submit(&$form, $form_state) {
      $otp_token = $form['miniorange_ldap_customer_otp_token']['#value'];
      $username = \Drupal::config('ldap_auth.settings')->get('miniorange_ldap_customer_admin_email');
      $phone = \Drupal::config('ldap_auth.settings')->get('miniorange_ldap_customer_admin_phone');
      $tx_id = \Drupal::config('ldap_auth.settings')->get('miniorange_ldap_tx_id');
      $customer_config = new MiniorangeLdapCustomer($username, $phone, NULL, $otp_token);
      $validate_otp_response = json_decode($customer_config->validateOtp($tx_id));
  
      if ($validate_otp_response->status == 'SUCCESS')
      {
          \Drupal::configFactory()->getEditable('ldap_auth.settings')->clear('miniorange_ldap_tx_id')->save();
          $password = \Drupal::config('ldap_auth.settings')->get('miniorange_ldap_customer_admin_password');
          $customer_config = new MiniorangeLdapCustomer($username, $phone, $password, NULL);
          $create_customer_response = json_decode($customer_config->createCustomer());
          if ($create_customer_response->status == 'SUCCESS') {
              $current_status = 'PLUGIN_CONFIGURATION';
              \Drupal::configFactory()->getEditable('ldap_auth.settings')->set('miniorange_ldap_status', $current_status)->save();
              \Drupal::configFactory()->getEditable('ldap_auth.settings')->set('miniorange_ldap_customer_admin_email', $username)->save();
              \Drupal::configFactory()->getEditable('ldap_auth.settings')->set('miniorange_ldap_customer_admin_phone', $phone)->save();
              \Drupal::configFactory()->getEditable('ldap_auth.settings')->set('miniorange_ldap_customer_admin_token', $create_customer_response->token)->save();
              \Drupal::configFactory()->getEditable('ldap_auth.settings')->set('miniorange_ldap_customer_id', $create_customer_response->id)->save();
              \Drupal::configFactory()->getEditable('ldap_auth.settings')->set('miniorange_ldap_customer_api_key', $create_customer_response->apiKey)->save();
              \Drupal::messenger()->addStatus(t('Customer account created.'));
          }
          else if(trim($create_customer_response->message) == 'Email is not enterprise email.'){
              \Drupal::messenger()->addError(t('There was an error creating an account for you.<br> You may have entered an invalid Email-Id
              <strong>(We discourage the use of disposable emails) </strong>
              <br>Please try again with a valid email.'));
              return;
          }
          else {
              \Drupal::messenger()->addError(t('Error creating customer'));
              return;
          }
      }
      else {
          \Drupal::messenger()->addError(t('Error validating OTP'));
          return;
      }
    }
    function saved_support(array &$form, FormStateInterface $form_state) {
          $email = $form['miniorange_ldap_email_address']['#value'];
          $phone = $form['miniorange_ldap_phone_number']['#value'];
          $query = $form['miniorange_ldap_support_query']['#value'];
          Utilities::send_support_query($email, $phone, $query);
      }
}