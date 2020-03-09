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
  }

  function chaptermember_get_tokens($cid, &$value, $onlyActive) {
    $activeClause = $onlyActive ? ' AND m.is_active = 1' : '';
    try {
      $results = CRM_Core_DAO::executeQuery("
         SELECT m.id as membership_id, GROUP_CONCAT(DISTINCT e.email) as chapter_email,
          GROUP_CONCAT(DISTINCT p.phone) as chapter_phone, GROUP_CONCAT(DISTINCT coordinator.display_name) as chapter_admin
        FROM civicrm_membership m
        INNER JOIN civicrm_membership_type mt ON mt.id = m.membership_type_id AND m.is_test = 0
        INNER JOIN civicrm_value_cagis_members_1 cm ON cm.entity_id = m.id
        INNER JOIN civicrm_contact org ON org.id = mt.member_of_contact_id
        LEFT JOIN civicrm_email e ON e.contact_id = org.id
        LEFT JOIN civicrm_phone p ON p.contact_id = org.id
        INNER JOIN civicrm_value_chapter_admin_9 ca ON ca.administrator_for_chapter_35 = org.id
        LEFT JOIN civicrm_contact coordinator ON coordinator.id = ca.entity_id
        WHERE m.status_id = 1 AND (cm.membership_card_sent_70 <> 1 OR cm.membership_card_sent_70 IS NULL) AND m.contact_id = $cid $activeClause
        GROUP BY m.contact_id
        ")->fetchAll();
      foreach($results as $result) {
        if (!empty($result['membership_id'])) {
          $result['chapters'] = (array) civicrm_api3('membership', 'getsingle', [
            'id' => $result['membership_id'],
            'return' => array(CAGIS_CHAPTER),
          ])[CAGIS_CHAPTER];
          $result['chapters'] = implode(', ', $result['chapters']);
        }

        foreach (['chapters', 'chapter_admin', 'chapter_email'] as $column) {
          if (!empty($result[$column]) && strpos($result[$column], ',') !== FALSE) {
            $values = (array) explode(',', $result[$column]);
            $last = array_pop($values);
            $result[$column] = count($values) ? implode(", ", $values) . " and " . $last : $last;
          }
        }

        $membership['chapteradmin_email'] = CRM_Utils_Array::value('chapter_email', $result);
        $membership['chapteradmin_phone'] = CRM_Utils_Array::value('chapter_phone', $result);
        $membership[CAGIS_CHAPTER] = CRM_Utils_Array::value('chapters', $result);

        if (!empty($result['chapter_admin'])) {
          if (strpos($result['chapter_admin'], ' and ') !== FALSE) {
            $membership['chapteradmin_coordinator_text'] = "coordinators are " . $result['chapter_admin'];
            if (strpos($result['chapter_admin'], ',') !== FALSE) {
              $membership['chapteradmin_coordinator_text'] .= ' respectively';
            }
          }
          else {
            $membership['chapteradmin_coordinator_text'] = "coordinator is " . $result['chapter_admin'];
          }
          $membership['chapteradmin_coordinator_name'] = $result['chapter_admin'];
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
