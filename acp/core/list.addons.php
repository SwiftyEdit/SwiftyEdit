<?php

//prohibit unauthorized access
require 'core/access.php';

/* check in a new module */
if(isset($_GET['enable'])) {
	
	$modFolder = basename($_GET['enable']);
	include SE_CONTENT.'/modules/'.$modFolder.'/info.inc.php';
	
	$db_content->insert("se_addons", [
		"addon_type" => "module",
		"addon_dir" => $_GET['enable'],
		"addon_name" => $mod['name'],
		"addon_version" => $mod['version']
	]);
	
	
	mods_check_in();
}

/* check out an existing module */
if(isset($_GET['disable'])) {
	
	$db_content->delete("se_addons", [
		"AND" => [
			"addon_dir" => $_GET['disable']
		]
	]);
	
	mods_check_in();
}

$se_addons = se_get_addons($t='module');

$template_file = file_get_contents("templates/modlist.tpl");
$modal_template_file = file_get_contents("templates/bs-modal.tpl");

if($cnt_mods < 1) {
	echo '<div class="alert alert-info">'.$lang['alert_no_modules'].'</div>';
}

for($i=0;$i<$cnt_mods;$i++) {

	unset($listlinks, $modnav);
	
	$modFolder = $all_mods[$i]['folder'];
	$bnt_check_in_out = '<a class="btn btn-sm btn-default text-success" href="acp.php?tn=addons&sub=list&enable='.$modFolder.'">'.$lang['btn_mod_enable'].'</a>';
		
	foreach($se_addons as $a) {
		if($modFolder == $a['addon_dir']) {
			$bnt_check_in_out = '<a class="btn btn-sm btn-default text-danger" href="acp.php?tn=addons&sub=list&disable='.$modFolder.'">'.$lang['btn_mod_disable'].'</a>';
		}
	}
	
	if($_SESSION['drm_acp_sensitive_files'] !== 'allowed') {
		$bnt_check_in_out = '';
	}
			
	include SE_CONTENT.'/modules/'.$modFolder.'/info.inc.php';
	
	$listlinks = '<div class="btn-group">';
	for($x=0;$x<count($modnav);$x++) {
		$showlink = $modnav[$x]['link'];
		$incpage = $modnav[$x]['file'];
		$listlinks .= "<a class='btn btn-sm btn-default' href='acp.php?tn=addons&sub=$modFolder&a=$incpage'>$showlink</a> ";
	}
	
	$listlinks .= '</div>';
	

	
	$poster_img = '';
	if(is_file(SE_CONTENT."/modules/$modFolder/poster.png")) {
		$poster_img = '<a href="acp.php?tn=addons&sub='.$modFolder.'&a=start"><img src="/content/modules/'.$modFolder.'/poster.png" class="rounded-circle img-fluid"></a>';
	} else {
		$poster_img = '<a href="acp.php?tn=addons&sub='.$modFolder.'&a=start"><img src="images/poster-addons.png" class="rounded-circle img-fluid"></a>';
	}
	
	
	
	$btn_help_text = '';
	$modal = '';
	if(is_file(SE_CONTENT.'/modules/'.$modFolder.'/readme.md')) {
		$addon_id = 'addonID'.$i;
		$btn_help_text = '<button type="button" class="btn btn-sm btn-default" data-bs-toggle="modal" data-bs-target="#'.$addon_id.'">'.$icon['question'].'</button>';
		
		$modal_body_text = file_get_contents(SE_CONTENT.'/modules/'.$modFolder.'/readme.md');
		$Parsedown = new Parsedown();
		$modal_body = $Parsedown->text($modal_body_text);
		
		$modal = $modal_template_file;
		$modal = str_replace('{modalID}', $addon_id, $modal);
		$modal = str_replace('{modalTitle}', $mod['name'], $modal);
		$modal = str_replace('{modalBody}', $modal_body, $modal);
		echo $modal;
	}
	
	$btn_delete_addon = '<form class="d-inline ps-2" action="?tn=addons&sub=m" method="POST" onsubmit="return confirm(\''.$lang['confirm_delete_file'].'\');">';
	$btn_delete_addon .= '<button type="submit" name="delete_addon" class="btn btn-sm btn-default text-danger">'.$icon['trash_alt'].'</button>';
	$btn_delete_addon .= '<input type="hidden" name="type" value="m">';
	$btn_delete_addon .= '<input type="hidden" name="addon" value="'.$modFolder.'">';
	$btn_delete_addon .= '<input type="hidden" name="csrf_token" value="'.$_SESSION['token'].'">';
	$btn_delete_addon .= '</form>';
	
	
	$tpl = $template_file;
	
	$tpl = str_replace("{\$MOD_NAME}", "$mod[name]","$template_file"); 
	$tpl = str_replace("{\$MOD_DESCRIPTION}", "$mod[description]","$tpl");
	$tpl = str_replace("{\$MOD_VERSION}", "$mod[version]","$tpl");
	$tpl = str_replace("{\$MOD_AUTHOR}", "$mod[author]","$tpl");
	$tpl = str_replace("{\$MOD_ICON}", "$poster_img","$tpl");
	$tpl = str_replace("{\$MOD_LIVECODE}", "$mod_livecode","$tpl");
	$tpl = str_replace("{\$MOD_CHECK_IN_OUT}", "$bnt_check_in_out","$tpl");
	$tpl = str_replace("{\$MOD_README}", "$btn_help_text","$tpl");
	$tpl = str_replace("{\$MOD_DELETE}", "$btn_delete_addon","$tpl");
	
	$tpl = str_replace("{\$MOD_NAV}", "$listlinks","$tpl");
	
	echo $tpl;

}