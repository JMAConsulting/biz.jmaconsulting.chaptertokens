<?php
// This file declares a managed database record of type "Job".
// The record will be automatically inserted, updated, or deleted from the
// database as appropriate. For more details, see "hook_civicrm_managed" at:
// https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_managed/
return array (
  0 => 
  array (
    'name' => 'Send Membership Cards',
    'entity' => 'Job',
    'params' => 
    array (
      'version' => 3,
      'name' => 'Send Membership Cards',
      'description' => 'This service sends out membership cards periodically to new members who have joined the system.',
      'run_frequency' => 'Always',
      'api_entity' => 'Cagis',
      'api_action' => 'sendMembershipCard',
      'parameters' => '',
    ),
  ),
);
