<?php

/**
 * @param string $type all public groups
 * @return mixed
 */
function se_get_usergroups($type='all') {

    global $db_user;
    $type = sanitizeUserInputs($type);

    if($type == 'all') {
        $user_groups = $db_user->select("se_groups", "*");
    } else {
        $user_groups = $db_user->select("se_groups","*",[
            "group_type" => 'p'
        ]);
    }

    return $user_groups;
}

/**
 * @param integer $id
 * @return array data from user group
 */
function se_get_usergroup_by_id($id) {

    global $db_user;
    $id = (int) $id;

    $data = $db_user->get("se_groups","*",[
        "group_id" => $id
    ]);

    return $data;
}

/**
 * @param integer $id
 * @return array user data
 */
function se_get_userdata_by_id($id) {

    global $db_user;
    $id = (int) $id;

    $user_data = $db_user->get("se_user", "*", [
        "user_id" => $id
    ]);

    return $user_data;
}