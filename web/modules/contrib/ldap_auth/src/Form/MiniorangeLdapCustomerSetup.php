<?php

/**
 * @file
 * Contains \Drupal\ldap_auth\Form\MiniorangeLdapCustomerSetup.
 */

namespace Drupal\ldap_auth\Form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\ldap_auth\MiniorangeLdapCustomer;
use Drupal\Core\Form\FormBase;
use Drupal\ldap_auth\MiniorangeLdapSupport;
use Drupal\ldap_auth\Utilities;

class MiniorangeLdapCustomerSetup extends FormBase {

  public function getFormId() {
    return 'miniorange_ldap_customer_setup';
  }

  public function buildForm(array $form, FormStateInterface $form_state)
  {
    global $base_url;
    $current_status = \Drupal::config('ldap_auth.settings')->get('miniorange_ldap_status');
    $form['markup_library'] = array(
      '#attached' => array(
          'library' => array(
              "ldap_auth/ldap_auth.admin",
          )
      ),
    );
    if ($current_status == 'VALIDATE_OTP') {

        $form['miniorange_ldap_customer_otp_token'] = array(
            '#type' => 'textfield',
            '#title' => t('OTP'),
            '#prefix' => '<div class="mo_ldap_table_layout_1"><div class="mo_ldap_table_layout container">',
            '#suffix' => '<br>',
        );

        $form['miniorange_ldap_customer_validate_otp_button'] = array(
          '#type' => 'submit',
          '#value' => t('Validate OTP'),
          '#submit' => array('::miniorange_ldap_validate_otp_submit'),
        );

        $form['miniorange_ldap_customer_setup_resendotp'] = array(
          '#type' => 'submit',
          '#value' => t('Resend OTP'),
          '#submit' => array('::miniorange_ldap_resend_otp'),
        );

        $form['miniorange_ldap_customer_setup_back'] = array(
          '#type' => 'submit',
          '#value' => t('Back'),
          '#submit' => array('::miniorange_ldap_back'),
          '#suffix' => '<br>'
        );

        Utilities::AddSupportButton($form, $form_state);

        return $form;
      }
      elseif ($current_status == 'PLUGIN_CONFIGURATION')
      {
          $form['header_top_style_1'] = array('#markup' => '<div class="mo_ldap_table_layout_1"><div class="mo_ldap_table_layout container">
          <div class="mo_ldap_welcome_message">Thank you for registering with miniOrange</div><br><h4>Your Profile: </h4>',
          );

        $header = array(
          'email' => array('data' => t('Customer Email')),
          'customerid' => array('data' => t('Customer ID')),
          'token' => array('data' => t('Token Key')),
          'apikey' => array('data' => t('API Key')),
        );

        $options = [];

        $options[0] = array(
          'email' => \Drupal::config('ldap_auth.settings')->get('miniorange_ldap_customer_admin_email'),
          'customerid' => \Drupal::config('ldap_auth.settings')->get('miniorange_ldap_customer_id'),
          'token' => \Drupal::config('ldap_auth.settings')->get('miniorange_ldap_customer_admin_token'),
          'apikey' => \Drupal::config('ldap_auth.settings')->get('miniorange_ldap_customer_api_key'),
        );

        $form['fieldset']['customerinfo'] = array(
          '#theme' => 'table',
          '#header' => $header,
          '#rows' => $options,
          '#suffix' => '<br><br><br><br>'
        );

        $form['markup_idp_attr_header_top_support'] = array('#markup' => '</div>',
        );

        Utilities::AddSupportButton($form, $form_state);

        return $form;
      }

    $form['markup_14'] = array(
      '#markup' => '<div class="mo_ldap_table_layout_1"><div class="mo_ldap_table_layout container">');

    $form['markup_reg'] = array(
        '#markup' => '<div><h2>Register with mini<span class="mo_orange"><b>O</b></span>range</h2><hr>',
    );  

    $form['fmarkup_15'] = array(
      '#markup' => '<br><div class="mo_ldap_highlight_background_note_1">Please enter a valid email id that you have access to. You will be able to move forward after verifying an OTP that we will send to this email.
      <p>In case you are facing any issues trying to register with us, you can directly create an account from the link <a target="_blank" href="https://www.miniorange.com/businessfreetrial"><b>HERE</b></a> or you can reach out to us at <a href="mailto:drupalsupport@xecurify.com">drupalsupport@xecurify.com</a>
      </div>'
      );
    $form['miniorange_ldap_customer_setup_username'] = array(
      '#type' => 'textfield',
      '#title' => t('Email'),
    );

    $form['miniorange_ldap_customer_setup_phone'] = array(
      '#type' => 'textfield',
      '#title' => t('Phone'),
      '#description' => t('<b>NOTE:</b> We will only call if you need support.'),
    );

    $form['miniorange_ldap_customer_setup_password'] = array(
      '#type' => 'password_confirm',
    );

    $form['miniorange_ldap_customer_setup_button'] = array(
      '#type' => 'submit',
      '#value' => t('Register'),
    );

    $form['register_close'] = array(
        '#markup' => '</div>',
    );   

    Utilities::AddSupportButton($form, $form_state);

    return $form;
  }

  public function submitForm(array &$form, FormStateInterface $form_state) {
    $username = $form['miniorange_ldap_customer_setup_username']['#value'];
    $phone = $form['miniorange_ldap_customer_setup_phone']['#value'];
    $password = $form['miniorange_ldap_customer_setup_password']['#value']['pass1'];
    if(empty($username)||empty($password)){
        \Drupal::messenger()->addError(t('The <b><u>Email </u></b> and <b><u>Password</u></b> fields are mandatory.'));
      return;
  }
    if (!\Drupal::service('email.validator')->isValid( $username )) {
        \Drupal::messenger()->addError(t('The email address <i>' . $username . '</i> is not valid.'));
            return;
    }
    $customer_config = new MiniorangeLdapCustomer($username, $phone, $password, NULL);
    $check_customer_response = json_decode($customer_config->checkCustomer());
    if ($check_customer_response->status == 'CUSTOMER_NOT_FOUND') {
      \Drupal::configFactory()->getEditable('ldap_auth.settings')->set('miniorange_ldap_customer_admin_email', $username)->save();
      \Drupal::configFactory()->getEditable('ldap_auth.settings')->set('miniorange_ldap_customer_admin_phone', $phone)->save();
      \Drupal::configFactory()->getEditable('ldap_auth.settings')->set('miniorange_ldap_customer_admin_password', $password)->save();
      $send_otp_response = json_decode($customer_config->sendOtp());

      if ($send_otp_response->status == 'SUCCESS') {
        \Drupal::configFactory()->getEditable('ldap_auth.settings')->set('miniorange_ldap_tx_id', $send_otp_response->txId)->save();
        $current_status = 'VALIDATE_OTP';
        \Drupal::configFactory()->getEditable('ldap_auth.settings')->set('miniorange_ldap_status', $current_status)->save();
          \Drupal::messenger()->addMessage(t('Verify email address by entering the passcode sent to @username', [
          '@username' => $username
          ]));
      }
    }
    elseif ($check_customer_response->status == 'CURL_ERROR') {
        \Drupal::messenger()->addError(t('cURL is not enabled. Please enable cURL'));
    }
    else {
      $customer_keys_response = json_decode($customer_config->getCustomerKeys());

      if (json_last_error() == JSON_ERROR_NONE) {
        \Drupal::configFactory()->getEditable('ldap_auth.settings')->set('miniorange_ldap_customer_id', $customer_keys_response->id)->save();
        \Drupal::configFactory()->getEditable('ldap_auth.settings')->set('miniorange_ldap_customer_admin_token', $customer_keys_response->token)->save();
        \Drupal::configFactory()->getEditable('ldap_auth.settings')->set('miniorange_ldap_customer_admin_email', $username)->save();
        \Drupal::configFactory()->getEditable('ldap_auth.settings')->set('miniorange_ldap_customer_admin_phone', $phone)->save();
        \Drupal::configFactory()->getEditable('ldap_auth.settings')->set('miniorange_ldap_customer_api_key', $customer_keys_response->apiKey)->save();
        $current_status = 'PLUGIN_CONFIGURATION';
        \Drupal::configFactory()->getEditable('ldap_auth.settings')->set('miniorange_ldap_status', $current_status)->save();
          \Drupal::messenger()->addStatus(t('Successfully retrieved your account.'));
      }
      else {
          \Drupal::messenger()->addError(t('Invalid credentials'), 'error');
          return;
      }
    }
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