<?php

/**
 * @var array $lang
 * @var array $icon
 */

$reader_uri = '/admin-xhr/dashboard/read/';

echo '<div class="subHeader d-flex align-items-center">';
echo $icon['speedometer'].' '.$lang['nav_btn_dashboard'];
echo '<span class="ms-auto">';
echo se_print_docs_link('01-02-basics.md#dashboard');
echo '</span>';
echo '</div>';

// two-factor authentication: warn while the emergency switch is set,
// otherwise recommend enabling it (can be hidden per user)
if (is_array($se_2fa_bypass) && strtotime((string) ($se_2fa_bypass['until'] ?? '')) > time()) {
    echo '<div class="alert alert-danger">'.$icon['exclamation_triangle'].' ';
    echo htmlspecialchars(str_replace('{USER}', (string) ($se_2fa_bypass['user'] ?? ''), $lang['dashboard_2fa_bypass']), ENT_QUOTES);
    echo '</div>';
} elseif (($se_2fa_required ?? false) !== true && empty(se_get_my_presets()['hide_2fa_notice'])) {
    echo '<div class="alert alert-info d-flex align-items-center gap-2" id="twofaNotice">';
    echo '<span>'.$icon['shield_lock'].' '.htmlspecialchars($lang['dashboard_2fa_off'], ENT_QUOTES).'</span>';
    echo '<span class="ms-auto">'.se_print_docs_link('07-00-user.md', null, 'two-factor-authentication').'</span>';
    echo '<button class="btn btn-sm btn-default" hx-post="/admin-xhr/users/settings/write/" ';
    echo 'hx-vals=\'{"hide_2fa_notice":1,"csrf_token":"'.$_SESSION['token'].'"}\' hx-target="#twofaNotice" hx-swap="outerHTML">';
    echo $lang['dashboard_2fa_hide'].'</button>';
    echo '</div>';
}

// the OPcache card is only shown to admins with the "sensitive files" right
// (same right that gates the update/addon installers - resetting the
// server's bytecode cache is just as server-wide/sensitive)
echo $twig->render('dashboard/cards.twig', [
    'reader_uri' => $reader_uri,
    'show_opcache' => se_hasPermission('drm_acp_sensitive_files')
]);
