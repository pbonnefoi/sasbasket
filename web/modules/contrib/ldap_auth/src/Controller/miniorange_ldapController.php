<?php /**
 * @file
 * Contains \Drupal\miniorange_ldap\Controller\DefaultController.
 */

namespace Drupal\ldap_auth\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\user\Entity\User;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;

class miniorange_ldapController extends ControllerBase{
    public function ldap_auth_feedback_func(){
        $res = '';
        $reason ='';
        $_SESSION['mo_other'] = "False";
        global $base_url;

        if(isset($_GET['miniorange_feedback_submit'])) {
          $reason = $_GET['deactivate_plugin'];
          $q_feedback = $_GET['query_feedback'];
          $message = 'Reason: ' . $reason . '<br>' . 'Feedback: ' . $q_feedback;
          $url = 'https://login.xecurify.com/moas/api/notify/send';
          $chi = curl_init($url);
          $inst_time = \Drupal::config('ldap_auth.settings')->get('miniorange_ldap_inst_time_ref');
          $inst_time = date('m/d/Y H:i:s', $inst_time);
          $page_visited = \Drupal::config('ldap_auth.settings')->get('miniorange_ldap_license_page_visited');
          $cont_ldap = \Drupal::config('ldap_auth.settings')->get('miniorange_ldap_contacted_server');
          $test_con = \Drupal::config('ldap_auth.settings')->get('miniorange_ldap_test_connection');
          $drupal_login = \Drupal::config('ldap_auth.settings')->get('miniorange_ldap_drupal_login');
          $email = \Drupal::config('ldap_auth.settings')->get('miniorange_ldap_customer_admin_email');
          if (empty($email)) {
            $email = $_GET['miniorange_feedback_email'];
          }
          $phone = \Drupal::config('ldap_auth.settings')->get('miniorange_ldap_customer_admin_phone');
          $customerKey = \Drupal::config('ldap_auth.settings')->get('miniorange_ldap_customer_id');
          $apikey = \Drupal::config('ldap_auth.settings')->get('miniorange_ldap_customer_api_key');
          if ($customerKey == '') {
            $customerKey = "16555";
            $apikey = "fFd2XcvTGDemZvbw1bcUesNJWEqKbbUq";
          }
          $currentTimeInMillis = self::get_oauth_timestamp();
          $stringToHash = $customerKey . $currentTimeInMillis . $apikey;
          $hashValue = hash("sha512", $stringToHash);
          $customerKeyHeader = "Customer-Key: " . $customerKey;
          $timestampHeader = "Timestamp: " . $currentTimeInMillis;
          $authorizationHeader = "Authorization: " . $hashValue;
          $fromEmail = $email;
          $subject = "Drupal " . \Drupal::VERSION . " Active Directory / LDAP Integration - NTLM & Kerberos Login Feedback";
          $query = '[Drupal ' . \Drupal::VERSION . ' Active Directory / LDAP Integration - NTLM & Kerberos Login]: ' . $message;
          $content = '<div >Hello, <br><br>Company :<a href="' . $_SERVER['SERVER_NAME'] . '" target="_blank" >' . $_SERVER['SERVER_NAME'] . '</a><br><br>Phone Number :' . $phone . '<br><br>Email :<a href="mailto:' . $fromEmail . '" target="_blank">' . $fromEmail . '</a><br><br>Installed on: '.$inst_time.'<br><br>Payment Page Visited: '.$page_visited.'<br><br>Contact LDAP Server: '.$cont_ldap.'<br><br>Performed Test Connection: '.$test_con.'<br><br>Tried Login to Drupal using LDAP: '.$drupal_login.'<br><br>Query: '.$query.'</div>';
          $fields = array(
            'customerKey' => $customerKey,
            'sendEmail' => true,
            'email' => array(
              'customerKey' => $customerKey,
              'fromEmail' => $fromEmail,
              'fromName' => 'miniOrange',
              'toEmail' => 'drupalsupport@xecurify.com',
              'toName' => 'drupalsupport@xecurify.com',
              'subject' => $subject,
              'content' => $content
            ),
          );
          $field_string = json_encode($fields);
          curl_setopt($chi, CURLOPT_FOLLOWLOCATION, true);
          curl_setopt($chi, CURLOPT_ENCODING, "");
          curl_setopt($chi, CURLOPT_RETURNTRANSFER, true);
          curl_setopt($chi, CURLOPT_AUTOREFERER, true);
          curl_setopt($chi, CURLOPT_SSL_VERIFYPEER, false);    # required for https urls
          curl_setopt($chi, CURLOPT_MAXREDIRS, 10);
          curl_setopt($chi, CURLOPT_HTTPHEADER, array("Content-Type: application/json", $customerKeyHeader,
            $timestampHeader, $authorizationHeader));
          curl_setopt($chi, CURLOPT_POST, true);
          curl_setopt($chi, CURLOPT_POSTFIELDS, $field_string);
          $content = curl_exec($chi);
          if (curl_errno($chi)) {
            return json_encode(array("status" => 'ERROR', 'statusMessage' => curl_error($chi)));
          }
          curl_close($chi);
        }
        \Drupal::configFactory()->getEditable('ldap_auth.settings')->set('miniorange_ldap_auth_uninstall_status',1)->save();
        \Drupal::service('module_installer')->uninstall(['ldap_auth']);
        $uninstall_redirect = $base_url.'/admin/modules';
        \Drupal::messenger()->addStatus(t("Module uninstalled successfully."));
        return new RedirectResponse($uninstall_redirect);
    }

    /**
     * This function is used to get the timestamp value
     */
    public function get_oauth_timestamp(){
        $url = 'https://login.xecurify.com/moas/rest/mobile/get-timestamp';
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_ENCODING, "");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_AUTOREFERER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false); // required for https urls
        curl_setopt($ch, CURLOPT_MAXREDIRS, 10);
        curl_setopt($ch, CURLOPT_POST, true);
        $content = curl_exec($ch);
        if (curl_errno($ch)) {
            echo 'Error in sending curl Request';
            exit ();
        }
        curl_close($ch);
        if (empty($content)) {
            $currentTimeInMillis = round(microtime(true) * 1000);
            $currentTimeInMillis = number_format($currentTimeInMillis, 0, '', '');
        }
        return empty($content) ? $currentTimeInMillis : $content;
    }

    public function uninst_mod(){
        global $base_url;
        \Drupal::configFactory()->getEditable('ldap_auth.settings')->clear('miniorange_ldap_feedback_status')->save();
        \Drupal::service('module_installer')->uninstall(['ldap_auth']);
        $uninstall_redirect = $base_url . '/admin/modules';
        $response = new RedirectResponse($uninstall_redirect);
        $response->send();
        return new Response();
    }
}
