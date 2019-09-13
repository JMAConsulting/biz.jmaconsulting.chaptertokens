<?php
use CRM_Chaptertokens_ExtensionUtil as E;

/**
 * Cagis.SendMembershipCard API specification (optional)
 * This is used for documentation and validation.
 *
 * @param array $spec description of fields supported by this API call
 * @return void
 * @see https://docs.civicrm.org/dev/en/latest/framework/api-architecture/

function _civicrm_api3_cagis_SendMembershipCard_spec(&$spec) {
  $spec['membership_id']['api.required'] = 1;
}
*/

/**
 * Cagis.SendMembershipCard API
 *
 * @param array $params
 * @return array API result descriptor
 * @see civicrm_api3_create_success
 * @see civicrm_api3_create_error
 * @throws API_Exception
 */
function civicrm_api3_cagis_sendMembershipCard($params) {
  // Get memberships that are new and have not been processed.
  $validContacts = CRM_Core_DAO::executeQuery("SELECT MAX(m.id) as membership_id, m.contact_id, e.email as chapter_email, ce.email as admin_email
    FROM civicrm_membership m
    INNER JOIN civicrm_value_cagis_members_1 cm ON cm.entity_id = m.id
    LEFT JOIN civicrm_contact c ON c.organization_name = cm.cagis_chapter_1
    LEFT JOIN civicrm_email e ON e.contact_id = c.id AND e.is_primary = 1
    LEFT JOIN civicrm_value_chapter_admin_9 ca ON ca.administrator_for_chapter_35 = c.id
    LEFT JOIN civicrm_email ce ON ca.entity_id = ce.contact_id AND ce.is_primary = 1 
    WHERE m.status_id = 1 AND m.membership_type_id IN (1,2,3,4) AND (cm.membership_card_sent_70 <> 1 OR cm.membership_card_sent_70 IS NULL)
    GROUP BY m.contact_id")->fetchAll();
  foreach ($validContacts as $contact) {
    $cc = [
      'larissa.vingilis.jaremko@gmail.com',
      'mkzcatherine@gmail.com',
    ];
    $emailParams = [
      'contact_id' => $contact['contact_id'],
      'template_id' => 69,
      'from_name' => 'The Canadian Association of Girls in Science (CAGIS)',
      'from_email' => 'cagis@uwo.ca',
    ];
    if (!empty($contact['chapter_email'])) {
      $cc[] = $contact['chapter_email'];
    }
    /*if (!empty($contact['admin_email'])) {
      $cc[] = $contact['admin_email'];
    }*/
    if (!empty($cc)) {
      $emailParams['cc'] = implode(',', $cc);
    }
    try {
      $sent = civicrm_api3('Email', 'send', $emailParams);
      if (!$sent['is_error']) {
        $contacts[] = $contact['contact_id'];
        // Process membership too.
        civicrm_api3('Membership', 'create', ['id' => $contact['membership_id'], 'custom_70' => 1]);
      }
    }
    catch (CiviCRM_API3_Exception $e) {
      CRM_Core_Error::debug_var('Welcome Cards Error', $e->getMessage());
      throw new API_Exception('Welcome Cards were not sent. Please check logs for errors.');
    }
  }
  if (!empty($contacts)) {
    $returnValues = array(
      'status_msg' => 'Sent membership welcome card to ' . count($contacts) . ' contacts.',
    );
  }
  else {
    $returnValues = array(
      'status_msg' => 'No welcome cards were sent.',
    );
  }
  return civicrm_api3_create_success($returnValues, $params, 'Cagis', 'sendMembershipCard');
}
