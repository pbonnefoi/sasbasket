<?php
/**
 * @file
 * Contains support form for miniOrange Login with NTLM Module.
 */

/**
 * Showing Support form info.
 */
namespace Drupal\ldap_auth\Form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Form\FormBase;
use Drupal\ldap_auth\MiniorangeLdapSupport;

class MiniorangeSupport extends FormBase {

    public function getFormId() {
        return 'miniorange_ldap_support';
    }

    public function buildForm(array $form, FormStateInterface $form_state) {

        $attachments['#attached']['library'][] = 'ldap_auth/ldap_auth.admin';
        $form['markup_library'] = array(
        '#attached' => array(
            'library' => array(
                "ldap_auth/ldap_auth.admin",
                "ldap_auth/ldap_auth.testconfig",
                )
            ),
        );

        $form['header_top_style_1'] = array('#markup' => '<div class="mo_ldap_table_layout_1">',
        );

        $form['markup_1'] = array(
            '#markup' => '<div class="mo_ldap_table_layout container"><h2>Support</h2><hr><div></br>Need any help? Just send us your query so that we can help you out accordingly.<br/><br/></div>',
        );

        $form['miniorange_ldap_email_address'] = array(
            '#type' => 'textfield',
            '#title' => t('Email Address'),
            '#attributes' => array('placeholder' => 'Enter your email'),
            '#required' => TRUE,
        );

        $form['miniorange_ldap_phone_number'] = array(
            '#type' => 'textfield',
            '#title' => t('Phone number'),
            '#attributes' => array('placeholder' => 'Enter your phone number'),
        );

        $form['miniorange_ldap_support_query'] = array(
            '#type' => 'textarea',
            '#title' => t('Query'),
            '#cols' => '10',
            '#rows' => '5',
            '#attributes' => array('style'=>'width:80%','placeholder' => 'Write your query here'),
            '#required' => TRUE,
        );

        $form['miniorange_ldap_support_submit'] = array(
            '#type' => 'submit',
            '#value' => t('Submit Query'),
        );

        $form['miniorange_ldap_support_note'] = array(
            '#markup' => '<div><br/>If you want custom features in the module, just drop an email to <a href="mailto:drupalsupport@xecurify.com">drupalsupport@xecurify.com</a></div>'
        );

        return $form;

    }

    /**
     * Send support query.
     */
    public function submitForm(array &$form, FormStateInterface $form_state) {

        $email = $form['miniorange_ldap_email_address']['#value'];
        $phone = $form['miniorange_ldap_phone_number']['#value'];
        $query = $form['miniorange_ldap_support_query']['#value'];
        $support = new MiniorangeLdapSupport($email, $phone, $query);
        $support_response = $support->sendSupportQuery();
        if($support_response) {
            \Drupal::messenger()->addStatus(t('Support query successfully sent'));
        }
        else {
            \Drupal::messenger()->addError(t('Error sending support query'));
        }
    }
}