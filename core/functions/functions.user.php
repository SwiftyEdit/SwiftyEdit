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

/**
 * @param integer $user
 * @param integer $group
 * @return void
 */
function se_add_user_to_group(int $user, int $group): void {

    global $db_user;

    // get data from group
    $group_data = $db_user->select("se_groups","*",[
        "group_id" => $group
    ]);
    
    print_r($group_data);

    $users = explode(" ", $group_data['group_user']);
    if(in_array($user, $users)) {
        return;
    } else {
        $users[] = $user;
        $db_user->update("se_groups",[
            "group_user" => implode(" ", $users)
        ],[
            "group_id" => $group
        ]);
    }




}