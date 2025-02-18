<?php

echo '<div class="subHeader d-flex align-items-center">';
echo $icon['mailbox'].' '.$lang['nav_btn_mails'];
echo '<a href="/admin/inbox/mail/edit/" class="btn btn-default text-success ms-auto">'.$icon['plus'].' '.$lang['new'].'</a>';
echo '</div>';

require '../acp/core/functions_inbox.php';

$section_url = '/admin/inbox/mail/edit/';

// delete mail
if(isset($_POST['delete_message'])) {

    $message_id = (int) $_POST['delete_message'];
    $edit_message_data = se_inbox_get_message($message_id);
    $mail_subject = htmlspecialchars($edit_message_data['subject'],ENT_QUOTES, 'UTF-8');

    if(isset($_POST['delete_confirmed'])) {
        $del = se_inbox_delete_message($message_id);
        if($del > 0) {
            echo '<div class="alert alert-success">'.$lang['msg_success_entry_delete'].'</div>';
        }
    } else {
        echo '<div class="alert alert-primary">';
        echo '<h4>' . $lang['msg_confirm_delete'] . '</h4>';
        echo '<p>Subject: ' . $mail_subject . ' ID: ' . $message_id . '</p>';
        echo '<form action="/admin/inbox/mail/" method="POST">';
        echo '<button class="btn btn-sm btn-default me-1" name="delete_message" value="' . $message_id . '">' . $lang['yes'] . '</button>';
        echo '<input type="hidden" name="delete_confirmed" value="' . $message_id . '">';
        echo $hidden_csrf_token;
        echo '</form>';
        echo '</div>';
    }
}


// list messages
$all_messages = se_inbox_get_messages();
$cnt_messages = count($all_messages);

echo '<div class="card p-3">';
echo '<table class="table table-sm">';
echo '<tr>';
echo '<td>'.$lang['label_time_created'].'</td>';
echo '<td>'.$lang['label_time_last_edit'].'</td>';
echo '<td>'.$lang['label_time_sent'].'</td>';
echo '<td>'.$lang['label_author'].'</td>';
echo '<td>'.$lang['label_mail_subject'].'</td>';
echo '<td>'.$lang['label_mail_recipients'].'</td>';
echo '<td></td>';
echo '</tr>';

$modal_tpl = file_get_contents('../acp/templates/bs-modal.tpl');

for($i=0;$i<$cnt_messages;$i++) {

    $format_time = $se_prefs['prefs_dateformat'].' '.$se_prefs['prefs_timeformat'];
    $time_created = date("$format_time",$all_messages[$i]['time_created']);
    $time_lastedit = date("$format_time",$all_messages[$i]['time_lastedit']);

    $edit_btn  = '<form action="'.$section_url.'" method="POST" class="d-inline">';
    $edit_btn .= '<button class="btn btn-sm btn-default me-1" name="edit_message" value="'.$all_messages[$i]['id'].'">'.$lang['edit'].'</button>';
    $edit_btn .= $hidden_csrf_token;
    $edit_btn .= '</form>';
    $edit_btn .= '<form action="/admin/inbox/mail/" method="POST" class="d-inline">';
    $edit_btn .= '<button class="btn btn-sm btn-default text-danger" name="delete_message" value="'.$all_messages[$i]['id'].'">'.$icon['trash_alt'].'</button>';
    $edit_btn .= $hidden_csrf_token;
    $edit_btn .= '</form>';

    // recipients
    if(is_numeric($all_messages[$i]['recipients'])) {
        $get_group = se_get_usergroup_by_id($all_messages[$i]['recipients']);
        $show_recipient = $icon['user_friends'].' '.$get_group['group_name'];
    } else {
        $show_recipient = $lang['label_all_users'];
    }

    // show the log in modal
    $modal_id = 'log_modal_'.$i;
    $log = json_decode($all_messages[$i]['log'], true);
    $log_str = '<table class="table table-sm">';
    foreach($log as $l) {
        $log_str .= '<tr>';
        $log_str .= '<td>'.$l['status'].'</td>';
        $log_str .= '<td>'.date("$format_time",$l['time']).'</td>';
        $log_str .= '<td>'.$l['recipient'].'</td>';
        $log_str .= '</tr>';
    }
    $log_str .= '</table>';

    $this_modal = $modal_tpl;
    $this_modal = str_replace('{modalID}',$modal_id,$this_modal);
    $this_modal = str_replace('{modalTitle}',"Log",$this_modal);
    $this_modal = str_replace('{modalBody}',$log_str,$this_modal);

    if($all_messages[$i]['time_send'] > 0) {
        $time_sent = '<a href="#" data-bs-toggle="modal" data-bs-target="#'.$modal_id.'">';
        $time_sent .= date("$format_time", $all_messages[$i]['time_send']);
        $time_sent .= '</a>';
    } else {
        $time_sent = '-';
    }

    echo '<tr>';
    echo '<td>'.$time_created.'</td>';
    echo '<td>'.$time_lastedit.'</td>';
    echo '<td>'.$time_sent.$this_modal.'</td>';
    echo '<td>'.$all_messages[$i]['autor'].'</td>';
    echo '<td>'.$all_messages[$i]['subject'].'</td>';
    echo '<td>'.$show_recipient.'</td>';
    echo '<td class="text-end">'.$edit_btn.'</td>';
    echo '</tr>';

}
echo '</table>';
echo '</div>';