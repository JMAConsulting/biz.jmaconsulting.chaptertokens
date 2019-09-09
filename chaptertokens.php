<?php

require_once 'chaptertokens.civix.php';
use CRM_Chaptertokens_ExtensionUtil as E;

/**
 * Implements hook_civicrm_config().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_config/
 */
function chaptertokens_civicrm_config(&$config) {
  _chaptertokens_civix_civicrm_config($config);
}

/**
 * Implements hook_civicrm_xmlMenu().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_xmlMenu
 */
function chaptertokens_civicrm_xmlMenu(&$files) {
  _chaptertokens_civix_civicrm_xmlMenu($files);
}

/**
 * Implements hook_civicrm_install().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_install
 */
function chaptertokens_civicrm_install() {
  _chaptertokens_civix_civicrm_install();
}

/**
 * Implements hook_civicrm_postInstall().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_postInstall
 */
function chaptertokens_civicrm_postInstall() {
  _chaptertokens_civix_civicrm_postInstall();
}

/**
 * Implements hook_civicrm_uninstall().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_uninstall
 */
function chaptertokens_civicrm_uninstall() {
  _chaptertokens_civix_civicrm_uninstall();
}

/**
 * Implements hook_civicrm_enable().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_enable
 */
function chaptertokens_civicrm_enable() {
  _chaptertokens_civix_civicrm_enable();
}

/**
 * Implements hook_civicrm_disable().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_disable
 */
function chaptertokens_civicrm_disable() {
  _chaptertokens_civix_civicrm_disable();
}

/**
 * Implements hook_civicrm_upgrade().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_upgrade
 */
function chaptertokens_civicrm_upgrade($op, CRM_Queue_Queue $queue = NULL) {
  return _chaptertokens_civix_civicrm_upgrade($op, $queue);
}

/**
 * Implements hook_civicrm_managed().
 *
 * Generate a list of entities to create/deactivate/delete when this module
 * is installed, disabled, uninstalled.
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_managed
 */
function chaptertokens_civicrm_managed(&$entities) {
  _chaptertokens_civix_civicrm_managed($entities);
}

/**
 * Implements hook_civicrm_caseTypes().
 *
 * Generate a list of case-types.
 *
 * Note: This hook only runs in CiviCRM 4.4+.
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_caseTypes
 */
function chaptertokens_civicrm_caseTypes(&$caseTypes) {
  _chaptertokens_civix_civicrm_caseTypes($caseTypes);
}

/**
 * Implements hook_civicrm_angularModules().
 *
 * Generate a list of Angular modules.
 *
 * Note: This hook only runs in CiviCRM 4.5+. It may
 * use features only available in v4.6+.
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_angularModules
 */
function chaptertokens_civicrm_angularModules(&$angularModules) {
  _chaptertokens_civix_civicrm_angularModules($angularModules);
}

/**
 * Implements hook_civicrm_alterSettingsFolders().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_alterSettingsFolders
 */
function chaptertokens_civicrm_alterSettingsFolders(&$metaDataFolders = NULL) {
  _chaptertokens_civix_civicrm_alterSettingsFolders($metaDataFolders);
}

/**
 * Implements hook_civicrm_entityTypes().
 *
 * Declare entity types provided by this module.
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_entityTypes
 */
function chaptertokens_civicrm_entityTypes(&$entityTypes) {
  _chaptertokens_civix_civicrm_entityTypes($entityTypes);
}

/**
 * Implements hook_civicrm_thems().
 */
function chaptertokens_civicrm_themes(&$themes) {
  _chaptertokens_civix_civicrm_themes($themes);
}

function chaptertokens_civicrm_alterMailParams(&$params, $context) {
  if ($params['messageTemplateID'] == 69) {
    if (!empty($params['contactID'])) {
      $params = array(
        'version' => 3,
        'contact_id' => $params['contactID'],
        'sequential' => 1,
        'is_test' => 0,
        'options' => array('limit' => 1, 'sort' => 'end_date DESC'),
        'return' => array(CAGIS_CHAPTER),
      );
      try {
        $membership = civicrm_api3('membership', 'getsingle', $params);
        if (!empty($membership[CAGIS_CHAPTER])) {
          $chapters = CRM_Core_OptionGroup::values('cagis_chapter');
          $membership[CAGIS_CHAPTER] = $chapters[$membership[CAGIS_CHAPTER]];
          $org = CRM_Core_DAO::singleValueQuery("SELECT id FROM civicrm_contact WHERE organization_name = %1", [1 => [$membership[CAGIS_CHAPTER], 'String']]);

          // Retrieve the Chapter Information.
          $information = CRM_Core_DAO::executeQuery("SELECT email FROM civicrm_email e WHERE e.contact_id = %1 AND e.is_primary = 1", [1 => [$org, "Integer"]])->fetchAll();
          if ($information[0]['email']) {
            $name = $membership[CAGIS_CHAPTER] ? '"' . $membership[CAGIS_CHAPTER] .'"' : '';
            $params['cc'] = sprintf('%s <%s>', $name, $information[0]['email']);
          }
        }
      } catch (Exception $e) {}
    }

    $attachment = array(
      'fullPath' => '/home/cagis.jmaconsulting.biz/htdocs/wp-content/uploads/civicrm/custom/WAIVER.pdf',
      'mime_type' => 'application/pdf',
      'cleanName' => 'WAIVER.pdf',
    );
    $params['attachments'] = array($attachment);
  }
}


// --- Functions below this ship commented out. Uncomment as required. ---

/**
 * Implements hook_civicrm_preProcess().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_preProcess
 *
function chaptertokens_civicrm_preProcess($formName, &$form) {

} // */

/**
 * Implements hook_civicrm_navigationMenu().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_navigationMenu
 *
function chaptertokens_civicrm_navigationMenu(&$menu) {
  _chaptertokens_civix_insert_navigation_menu($menu, 'Mailings', array(
    'label' => E::ts('New subliminal message'),
    'name' => 'mailing_subliminal_message',
    'url' => 'civicrm/mailing/subliminal',
    'permission' => 'access CiviMail',
    'operator' => 'OR',
    'separator' => 0,
  ));
  _chaptertokens_civix_navigationMenu($menu);
} // */
