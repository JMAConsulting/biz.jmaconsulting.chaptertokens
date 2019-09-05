<?php

  function chaptermember_civitoken_declare($token){
    return array(
      $token . '.chapter' => 'CAGIS Chapter',
      $token . '.chapteradmin_email' => 'CAGIS Chapter Admin Email',
      $token . '.chapteradmin_phone' => 'CAGIS Chapter Admin Phone',
      $token . '.chapteradmin_coordinator_text' => 'Chapter coordinator text',
      $token . '.chapteradmin_coordinator_name' => 'Chapter coordinator real name',
    );
  }

  function chaptermember_civitoken_get($cid, &$value, $context){
    chaptermember_get_tokens($cid, $value, FALSE);
    chaptermember_get_tokens($cid, $value, TRUE);
  }

  function chaptermember_get_tokens($cid, &$value, $onlyActive) {
    $params = array(
      'version' => 3,
      'contact_id' => $cid,
      'sequential' => 1,
      'is_test' => 0,
      'options' => array('limit' => 1, 'sort' => 'end_date DESC'),
      'return' => array(CAGIS_CHAPTER),
    );
    if ($onlyActive == TRUE) {
      $params['is_active'] = 1;
      $params['active_only'] = 1;
    }

    try {
      $membership = civicrm_api3('membership', 'getsingle', $params);

      // Get label for chapter
      if (!empty($membership[CAGIS_CHAPTER])) {
        $chapters = CRM_Core_OptionGroup::values('cagis_chapter');
        $membership[CAGIS_CHAPTER] = $chapters[$membership[CAGIS_CHAPTER]];

        // Now retrieve the Chapter Organization
        $org = CRM_Core_DAO::singleValueQuery("SELECT id FROM civicrm_contact WHERE organization_name = %1", [1 => [$membership[CAGIS_CHAPTER], 'String']]);

        // Retrieve the Chapter Information.
        $information = CRM_Core_DAO::executeQuery("SELECT email, phone FROM civicrm_email e
          LEFT JOIN civicrm_phone p ON p.contact_id = e.contact_id AND p.is_primary = 1
          WHERE e.contact_id = %1 AND e.is_primary = 1", [1 => [$org, "Integer"]])->fetchAll();
        if (!empty($information[0])) {
          $membership['chapteradmin_email'] = $information[0]['email'];
          $membership['chapteradmin_phone'] = $information[0]['phone'];
        }
        else {
          $membership['chapteradmin_email'] = NULL;
          $membership['chapteradmin_phone'] = NULL;
        }

        // Get chapter admins
        $chapterAdmin = CRM_Core_DAO::singleValueQuery("SELECT GROUP_CONCAT(c.display_name SEPARATOR ' and ') FROM civicrm_contact c
          INNER JOIN civicrm_value_chapter_admin_9 a ON a.entity_id = c.id WHERE a.	administrator_for_chapter_35 = %1", [1 => [$org, 'Integer']]);
        if (!empty($chapterAdmin)) {
          if (strpos($chapterAdmin, ' and ') !== false) {
            $membership['chapteradmin_coordinator_text'] = "coordinators are " . $chapterAdmin;
          }
          else {
            $membership['chapteradmin_coordinator_text'] = "coordinator is " . $chapterAdmin;
          }
          $membership['chapteradmin_coordinator_name'] = $chapterAdmin;
        }
        else {
          $membership['chapteradmin_coordinator_text'] = "coordinator is not allocated yet.";
          $membership['chapteradmin_coordinator_name'] = NULL;
        }
      }
    }
    catch (Exception $e) {
      $tokens = array('chapter', 'chapteradmin_email', 'chapteradmin_phone');
      foreach ($tokens as $token) {
        $value['chaptermember.' . $token] = NULL;
      }
      return $value;
    }

    if (!CRM_Utils_Array::value('is_error', $membership)) {
      $value['chaptermember.' . 'chapter'] = $membership[CAGIS_CHAPTER];
      $value['chaptermember.' . 'chapteradmin_email'] = $membership['chapteradmin_email'];
      $value['chaptermember.' . 'chapteradmin_phone'] = $membership['chapteradmin_phone'];
      $value['chaptermember.' . 'chapteradmin_coordinator_text'] = $membership['chapteradmin_coordinator_text'];
      $value['chaptermember.' . 'chapteradmin_coordinator_name'] = $membership['chapteradmin_coordinator_name'];
    }
    return $value;
  }
