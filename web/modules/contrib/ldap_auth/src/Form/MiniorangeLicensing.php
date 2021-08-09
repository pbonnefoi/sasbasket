<?php
/**
 * @file
 * Contains Licensing information for miniOrange LDAP Login Module.
 */

 /**
 * Showing Licensing form info.
 */
namespace Drupal\ldap_auth\Form;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Form\FormBase;
use Drupal\ldap_auth\MiniorangeLdapSupport;

class MiniorangeLicensing extends FormBase {

public function getFormId() {
    return 'miniorange_ldap_licensing';
  }

public function buildForm(array $form, FormStateInterface $form_state)
{
    $form['markup_library'] = array(
        '#attached' => array(
            'library' => array(
                "ldap_auth/ldap_auth.admin",
                "ldap_auth/ldap_auth.style_settings",
                "ldap_auth/ldap_auth.main",

            )
        ),
      );
      \Drupal::configFactory()->getEditable('ldap_auth.settings')->set('miniorange_ldap_license_page_visited',"True")->save();
      
        $form['markup_1'] = array(
            '#markup' =>t('<div class="mo_ldap_table_layout_1">
            <div class="mo_ldap_table_layout"><br><h2>Upgrade Plans</h2><hr><br>'),
        );

      $form['miniorange_ldap_license'] = array(
          '#markup' => '<html lang="en">
          <head>
            <meta charset="utf-8">
            <meta name="viewport" content="width=device-width, initial-scale=1">
            <!-- Main Style -->
          </head>
        <body>
        <h2>If you want to test any of our paid modules, please contact us at <a href="mailto:drupalsupport@xecurify.com">drupalsupport@xecurify.com</a></h2>
         <!-- Pricing Table Section -->
        <section id="pricing-table">
            <div class="container_1">
                <div class="row">
                    <div class="pricing">
                        <div>
                        <div class="pricing-table class_inline_1">
                                <div class="pricing-header">
                                    <h2 class="pricing-title">Features</h2>
                                </div>

                                <div class="pricing-list">
                                    <ul>
                                        <li>Unlimited Authentications</li>
                                        <li>Single LDAP Directory Configuration</li>
                                        <li>Search User by Single Attribute</li>
                                        <li>Single Search Base</li>
                                        <li>Attribute Mapping</li>
                                        <li>Auto Create User</li>
                                        <li>Role Mapping</li>
                                        <li>TLS Connection</li>
                                        <li>Drupal to LDAP updates on user profile</li>
                                        <li>NTLM & Kerberos Authentication</li>
                                        <li>Support for Custom Integration</li>
                                    </ul>
                                </div>
                            </div>
                        <div class="pricing-table class_inline">
                                <div class="pricing-header">
                                    <p class="pricing-title">Free</p>
                                    <p class="pricing-rate"><sup>$</sup> 0 </p>
                                    <p class="pricing-title">Your current plan</p>
                                    <a class="btn btn-custom">You are on this plan</a>
                                </div>

                                <div class="pricing-list">
                                    <ul>
                                        <li>&#x2714;</li>
                                        <li>&#x2714;</li>
                                        <li>&#x2714;</li>
                                        <li>&#x2714;</li>
                                        <li>Username Mapping </li>
                                        <li> </li>
                                        <li> </li>
                                        <li> </li>
                                        <li> </li>
                                        <li> </li>
                                        <li> </li>
                                    </ul>
                                </div>
                            </div>


                            <div class="pricing-table class_inline">
                            <div class="pricing-header">
                                <p class="pricing-title">Standard</p>
                                <p class="pricing-rate"><sup>$</sup> 249<sup>*</sup> </p>
                                <p class="pricing-title">One Time Payment</p>
                                <a href="https://login.xecurify.com/moas/login?redirectUrl=https://login.xecurify.com/moas/initializepayment&requestOrigin=drupal8_ldap_standard_plan" target="_blank" class="btn btn-custom">Click to Upgrade</a>
                            </div>

                            <div class="pricing-list">
                                <ul>
                                    <li>&#x2714;</li>
                                    <li>&#x2714;</li>
                                    <li>&#x2714;</li>
                                    <li>&#x2714;</li>
                                    <li>Custom Mapping</li>
                                    <li>&#x2714;</li>
                                    <li>Basic</li>
                                    <li>&#x2714;</li>
                                    <li> </li><li> </li><li> </li>
                                </ul>
                            </div>
                        </div>
                        <div class="pricing-table class_inline">
                                <div class="pricing-header">
                                    <p class="pricing-title">Premium</p>
                                    <p class="pricing-rate"><sup>$</sup> 399<sup>*</sup> </p>
                                    <p class="pricing-title">One Time Payment</p>
                                    <a href="https://login.xecurify.com/moas/login?redirectUrl=https://login.xecurify.com/moas/initializepayment&requestOrigin=drupal8_ldap_premium_plan" target="_blank" class="btn btn-custom">Click to Upgrade</a>
                                </div>

                                <div class="pricing-list">
                                    <ul>
                                        <li>&#x2714;</li>
                                        <li>&#x2714;</li>
                                        <li>Custom Search Filter</li>
                                        <li>Multiple Search Bases</li>
                                        <li>Custom Mapping</li>
                                        <li>&#x2714;</li>
                                        <li>Advanced</li>
                                        <li>&#x2714;</li>
                                        <li>&#x2714;</li>
                                        <li>&#x2714;</li>
                                        <li>&#x2714;</li>
                                    </ul>
                                </div>
                                </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <!-- Pricing Table Section End -->
        *Cost applicable for one instance only. However, we do provide a discount on the purchase of multiple licenses <b>[<a target="_blank" href="https://plugins.miniorange.com/drupal-ldap#pricing">CLICK HERE TO KNOW MORE</a>]</b>. The licenses are perpetual and includes 12 months of free maintenance(version updates). If you want to renew, you can renew the maintenance after 12 months at 50% of the current license cost.
        <br><br><b>Return Policy -</b><br> 
    At miniOrange, we want to ensure that you are 100% happy with your purchase. If the module you purchased is not working as advertised and you have attempted to resolve any issues with our support team, which could not get resolved, we will refund the whole amount given that you raised a request for refund within the first 10 days of the purchase. Please email us at <a href="mailto:drupalsupport@xecurify.com">drupalsupport@xecurify.com</a> for any queries regarding the return policy.
        </body>
        </html>',
      );
          return $form;
     }

    public function submitForm(array &$form, FormStateInterface $form_state) {
    }


    function saved_support($form, &$form_state){
      $email = $form['miniorange_saml_email_address_support']['#value'];
      $phone = $form['miniorange_saml_phone_number_support']['#value'];
      $query = $form['miniorange_saml_support_query_support']['#value'];
      $support = new MiniorangeLdapSupport($email, $phone, $query);
      $support_response = $support->sendSupportQuery();
      if ($support_response) {
          \Drupal::messenger()->addStatus(t('Support query successfully sent'));
      } else {
          \Drupal::messenger()->addError(t('Error sending support query'));
      }
    }
}

