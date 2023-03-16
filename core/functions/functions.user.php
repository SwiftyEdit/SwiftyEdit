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
        $user_groups = $db_user->select("se_groups",[
            "group_type" => 'p'
        ]);
    }

    return $user_groups;
}