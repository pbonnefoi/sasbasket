<?php

/**
 * @file
 * Contains \Drupal\miniorange_ldap\Form\MiniorangeGeneralSettings.
 */

namespace Drupal\ldap_auth\Form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Form\FormBase;
use Drupal\ldap_auth\MiniorangeLdapSupport;
use Drupal\ldap_auth\Utilities;

class MiniorangeGeneralSettings extends FormBase
{

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'miniorange_general_settings';
  }
  public function buildForm(array $form, FormStateInterface $form_state)
  {
    global $base_url;
    $attachments['#attached']['library'][] = 'ldap_auth/ldap_auth.admin';
    $form['markup_library'] = array(
        '#attached' => array(
            'library' => array(
                "ldap_auth/ldap_auth.admin",
                "ldap_auth/ldap_auth.testconfig",
            )
        ),
    );
      \Drupal::configFactory()->getEditable('ldap_auth.settings')->set('miniorange_ldap_disabled', FALSE)->save();

    $form['markup_top'] = array(
        '#markup' => t('<div class="mo_ldap_table_layout_1"><div class="mo_ldap_table_layout container">')
    );

    $form['miniorange_ldap_enable_ntlm'] = array(
        '#type' => 'checkbox',
        '#disabled' => TRUE,
        '#description' => t('<b style="color: red">Note:</b> Enabling NTLM/Kerberos login will protect your website through login with NTLM/Kerberos. Upgrade to the <a href="' . $base_url .'/admin/config/people/ldap_auth/Licensing"><b>PREMIUM</b></a> version of the module to use this feature.'),
        '#title' => t('Enable NTLM/ Kerberos Login '),
    );

    $form['miniorange_ldap_image'] = array(
      '#markup' => '<div><br>
        <h1>What is Microsoft NTLM?</h1><hr>
        <p>NTLM is the authentication protocol used on networks that include systems running the Windows operating system and on stand-alone systems.</p>
        <p>NTLM credentials are based on data obtained during the interactive logon process and consist of a domain name, a user name, and a one-way hash of the users password. NTLM uses an encrypted challenge/response protocol to authenticate a user without sending the user password over the wire. Instead, the system requesting authentication must perform a calculation that proves it has access to the secured NTLM credentials.<br></p></div>',
    );

    $form['miniorange_ldap_kerbeors_desc'] = array(
      '#markup' => '<br>
        <h1>What is Kerberos?</h1><hr>
        <p>Kerberos is a client-server authentication protocol that enables mutual authentication –  both the user and the server verify each other’s identity – over non-secure network connections.  The protocol is resistant to eavesdropping and replay attacks, and requires a trusted third party.</p>
        <p>The Kerberos protocol uses a symmetric key derived from the user password to securely exchange a session key for the client and server to use. A server component is known as a Ticket Granting Service (TGS) then issues a security token (AKA Ticket-Granting-Ticket TGT) that can be later used by the client to gain access to different services provided by a Service Server.<br></p><br>',
    );
    $form['save_config_ntlms'] = array(
      '#type' => 'submit',
      '#value' => t('Save Changes'),
      '#disabled' => TRUE,
    );

    Utilities::AddSupportButton($form, $form_state);

    return $form;
  }

    function saved_support(array &$form, FormStateInterface $form_state) {
        $email = $form['miniorange_ldap_email_address']['#value'];
        $phone = $form['miniorange_ldap_phone_number']['#value'];
        $query = $form['miniorange_ldap_support_query']['#value'];
        Utilities::send_support_query($email, $phone, $query);
    }

    function miniorange_ldap_ntlm($form, $form_state)
    {
        $enable_ntlm = '';
        $enable_ntlm = $form['miniorange_ldap_enable_ntlm']['#value'];
        \Drupal::configFactory()->getEditable('ldap_auth.settings')->set('miniorange_ldap_enable_ntlm', $enable_ntlm)->save();
    }
    public function submitForm(array &$form, FormStateInterface $form_state) {
  }
}