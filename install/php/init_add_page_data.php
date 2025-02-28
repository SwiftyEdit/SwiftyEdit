<?php

/**
 * SwiftyEdit / install
 *
 * @var array $lang Translations
 */

if(!defined('INSTALLER')) {
	header("location:../login.php");
	die("PERMISSION DENIED!");
}

$prefs_cms_domain = 'http://'.$_SERVER['HTTP_HOST'];
$prefs_cms_ssl_domain = '';
$prefs_cms_base = dirname(dirname(htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES, "utf-8")));
$prefs_cms_base = str_replace('\\',  '/' ,$prefs_cms_base);
if($prefs_cms_base == '') {
    $prefs_cms_base = '/';
}

echo '<fieldset>';

echo '<legend>'.$lang['label_add_page_data'].'</legend>';
echo '<p class="lead">'.$lang['description_add_page_data'].'</p>';


echo '<form action="index.php" method="POST">';
echo '<div class="form-group">';
echo '<label>'.$lang['label_settings_cms_domain'].'</label>';
echo '<input type="text" class="form-control" name="prefs_cms_domain" value="'.$prefs_cms_domain.'">';
echo '</div>';

echo '<div class="form-group">';
echo '<label>'.$lang['label_settings_cms_domain_ssl'].'</label>';
echo '<input type="text" class="form-control" name="prefs_cms_ssl_domain" value="'.$prefs_cms_ssl_domain.'">';
echo '</div>';

echo '<div class="form-group">';
echo '<label>'.$lang['label_settings_cms_base'].'</label>';
echo '<input type="text" class="form-control" name="prefs_cms_base" value="'.$prefs_cms_base.'">';
echo '</div>';

echo '<hr>';

echo '<input type="submit" class="btn btn-info" name="step2" value="'.$lang['prev_step'].'"> ';
echo '<input type="submit" class="btn btn-success" name="step4" value="'.$lang['next_step'].'">';

echo '</form>';
echo '</fieldset>';