<?php

require_once 'hideactivitytypes.civix.php';
use CRM_Hideactivitytypes_ExtensionUtil as E;

/**
 * Implements hook_civicrm_config().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_config/
 */
function hideactivitytypes_civicrm_config(&$config) {
  _hideactivitytypes_civix_civicrm_config($config);
}

function hideactivitytypes_civicrm_buildForm($formName, &$form) {
  if ($formName == 'CRM_Admin_Form_Options' && $form->getVar('_gName') == 'activity_type') {
    //add select2 for contact types
    try {
      $contactTypes = \Civi\Api4\ContactType::get()
        ->setCheckPermissions(FALSE)
        ->execute();
    }
    catch (\API_Exception $e) {
      $error = $e->getMessage();
    }
    if ($contactTypes) {
      foreach ($contactTypes as $contactType) {
        $selectArr[$contactType['id']] = $contactType['label'];
      }
      $form->add('select', 'hidden_contact_array', ts('Hide for Contact Type(s)'), $selectArr, FALSE, 'multiple');
      CRM_Core_Region::instance('page-body')->add(array(
        'template' => 'contactTypeHide.tpl',
      ));
    }
    //add checkbox for actions menu
    $form->add('checkbox', 'hide_from_actions', ts('Hide from Actions Menu'));
    CRM_Core_Region::instance('page-body')->add(array(
      'template' => 'hideFromActions.tpl',
    ));
    //Set defaults
    $masks = \Civi\Api4\ActivityMask::get()
      ->setCheckPermissions(FALSE)
      ->addWhere('activity_type_id', '=', $form->getVar('_values')['value'])
      ->execute();
    foreach ($masks as $mask) {
      $defaults['hide_from_actions'] = $mask['hidden_from_actions'];
      $defaults['hidden_contact_array'] = explode(',', $mask['hidden_from_contact_ids_array']);
      $form->setDefaults($defaults);
    }
    //Use some JS to add select2 and move elements around the page
    CRM_Core_Resources::singleton()->addScriptFile('com.joineryhq.hideactivitytypes', 'js/activityTypeFields.js');
  }
}

function hideactivitytypes_civicrm_postProcess($formName, $form) {
  if ($formName == 'CRM_Admin_Form_Options' && $form->getVar('_gName') == 'activity_type') {
    $submitted = $form->getVar('_submitValues');
    if ($submitted['hide_from_actions'] || $submitted['hidden_contact_array']) {
      $contactTypes = '';
      if (count($submitted['hidden_contact_array'] > 0)) {
        foreach ($submitted['hidden_contact_array'] as $contactTypeId) {
          $contactTypes .= $contactTypeId . ',';
        }
      }
      try {
        $remove = \Civi\Api4\ActivityMask::delete()
          ->addWhere('activity_type_id', '=', $form->getVar('_values')['value'])
          ->setCheckPermissions(FALSE)
          ->execute();
      }
      catch (\API_Exception $e) {
        $error = $e->getMessage();
      }
      try {
        $result = \Civi\Api4\ActivityMask::create()
          ->setCheckPermissions(FALSE)
          ->addValue('hidden_from_actions', $submitted['hide_from_actions'])
          ->addValue('hidden_from_contact_ids_array', $contactTypes)
          ->addValue('activity_type_id', $form->getVar('_values')['value'])
          ->execute();
      }
      catch (\API_Exception $e) {
        $error = $e->getMessage();
      }
    }
    else {
      //If there are no configurations, just remove the rows
      try {
        $remove = \Civi\Api4\ActivityMask::delete()
          ->addWhere('activity_type_id', '=', $form->getVar('_values')['value'])
          ->setCheckPermissions(FALSE)
          ->execute();
      }
      catch (\API_Exception $e) {
        $error = $e->getMessage();
      }
    }
  }
}

function hideactivitytypes_civicrm_summaryActions(&$actions, $contactID) {
  //Find out if there are any activity masks
  $masks = \Civi\Api4\ActivityMask::get()
    ->setCheckPermissions(FALSE)
    ->addWhere('hidden_from_actions', '=', 1)
    ->execute();
  $removeActivities = [];
  foreach ($masks as $mask) {
    $removeActivities[] = $mask['activity_type_id'];
  }
  CRM_Core_Resources::singleton()->addVars('hideActions', $removeActivities);
  CRM_Core_Resources::singleton()->addScriptFile('com.joineryhq.hideactivitytypes', 'js/hideActions.js');
}

function hideactivitytypes_civicrm_tabset($tabsetName, &$tabs, $context) {
  if ($tabsetName == 'civicrm/contact/view') {
    //Find out if there are any masks
    $masks = \Civi\Api4\ActivityMask::get()
      ->setCheckPermissions(FALSE)
      ->execute();
    if (count($masks) > 0) {
      $contactID = $context['contact_id'];
      //Get the contact's array of types and subtypes together
      $contacts = \Civi\Api4\Contact::get()
        ->setSelect([
          'contact_type',
          'contact_sub_type',
        ])
        ->addWhere('id', '=', $contactID)
        ->setCheckPermissions(FALSE)
        ->execute();
      //Collect Contact Types and Subtypes
      foreach ($contacts as $contact) {
        $types[] = $contact['contact_type'];
        foreach ($contact['contact_sub_type'] as $subtype) {
          $types[] = $subtype;
        }
      }
      //Remove any duplicates as a sanity check
      $types = array_unique($types);
      //Get the contact type IDs from types array
      $contactTypes = \Civi\Api4\ContactType::get()
        ->setSelect([
          'id',
        ])
        ->addWhere('label', 'IN', $types)
        ->setCheckPermissions(FALSE)
        ->execute();
      $removeActivities = [];
      foreach ($contactTypes as $contactType) {
        //Find out if activity masks exist for this contact type and get their type ids
        foreach ($masks as $mask) {
          if (in_array($contactType['id'], explode(',', $mask['hidden_from_contact_ids_array']))) {
            $removeActivities[] = $mask['activity_type_id'];
          }
        }
      }
      if (count($removeActivities) > 0) {
        CRM_Core_Resources::singleton()->addVars('hideActivities', $removeActivities);
        CRM_Core_Resources::singleton()->addScriptFile('com.joineryhq.hideactivitytypes', 'js/hideActivities.js');
      }
    }
  }
}

/**
 * Implements hook_civicrm_xmlMenu().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_xmlMenu
 */
function hideactivitytypes_civicrm_xmlMenu(&$files) {
  _hideactivitytypes_civix_civicrm_xmlMenu($files);
}

/**
 * Implements hook_civicrm_install().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_install
 */
function hideactivitytypes_civicrm_install() {
  _hideactivitytypes_civix_civicrm_install();
}

/**
 * Implements hook_civicrm_postInstall().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_postInstall
 */
function hideactivitytypes_civicrm_postInstall() {
  _hideactivitytypes_civix_civicrm_postInstall();
}

/**
 * Implements hook_civicrm_uninstall().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_uninstall
 */
function hideactivitytypes_civicrm_uninstall() {
  _hideactivitytypes_civix_civicrm_uninstall();
}

/**
 * Implements hook_civicrm_enable().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_enable
 */
function hideactivitytypes_civicrm_enable() {
  _hideactivitytypes_civix_civicrm_enable();
}

/**
 * Implements hook_civicrm_disable().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_disable
 */
function hideactivitytypes_civicrm_disable() {
  _hideactivitytypes_civix_civicrm_disable();
}

/**
 * Implements hook_civicrm_upgrade().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_upgrade
 */
function hideactivitytypes_civicrm_upgrade($op, CRM_Queue_Queue $queue = NULL) {
  return _hideactivitytypes_civix_civicrm_upgrade($op, $queue);
}

/**
 * Implements hook_civicrm_managed().
 *
 * Generate a list of entities to create/deactivate/delete when this module
 * is installed, disabled, uninstalled.
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_managed
 */
function hideactivitytypes_civicrm_managed(&$entities) {
  _hideactivitytypes_civix_civicrm_managed($entities);
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
function hideactivitytypes_civicrm_caseTypes(&$caseTypes) {
  _hideactivitytypes_civix_civicrm_caseTypes($caseTypes);
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
function hideactivitytypes_civicrm_angularModules(&$angularModules) {
  _hideactivitytypes_civix_civicrm_angularModules($angularModules);
}

/**
 * Implements hook_civicrm_alterSettingsFolders().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_alterSettingsFolders
 */
function hideactivitytypes_civicrm_alterSettingsFolders(&$metaDataFolders = NULL) {
  _hideactivitytypes_civix_civicrm_alterSettingsFolders($metaDataFolders);
}

/**
 * Implements hook_civicrm_entityTypes().
 *
 * Declare entity types provided by this module.
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_entityTypes
 */
function hideactivitytypes_civicrm_entityTypes(&$entityTypes) {
  _hideactivitytypes_civix_civicrm_entityTypes($entityTypes);
}

/**
 * Implements hook_civicrm_thems().
 */
function hideactivitytypes_civicrm_themes(&$themes) {
  _hideactivitytypes_civix_civicrm_themes($themes);
}

// --- Functions below this ship commented out. Uncomment as required. ---

/**
 * Implements hook_civicrm_preProcess().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_preProcess
 *
function hideactivitytypes_civicrm_preProcess($formName, &$form) {

} // */

/**
 * Implements hook_civicrm_navigationMenu().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_navigationMenu
 *
function hideactivitytypes_civicrm_navigationMenu(&$menu) {
  _hideactivitytypes_civix_insert_navigation_menu($menu, 'Mailings', array(
    'label' => E::ts('New subliminal message'),
    'name' => 'mailing_subliminal_message',
    'url' => 'civicrm/mailing/subliminal',
    'permission' => 'access CiviMail',
    'operator' => 'OR',
    'separator' => 0,
  ));
  _hideactivitytypes_civix_navigationMenu($menu);
} // */
