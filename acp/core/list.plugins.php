<?php

//prohibit unauthorized access
require 'core/access.php';

if(count($all_plugins)<1) {
	/* no plugins */
	echo '<div class="alert alert-info">'.$lang['alert_no_plugins'].'</div>';
} else {

	$template_file = file_get_contents("templates/pluginlist.tpl");
	
	foreach($all_plugins as $plugin) {
		
		$id = md5($plugin);
		
		$pathinfo = pathinfo('/content/plugins/'.$plugin);
		
		if($pathinfo['extension'] == 'php') {
			$plugin_src = file_get_contents(SE_CONTENT.'/plugins/'.$plugin);
			$plugin_info = get_include_contents(SE_CONTENT.'/plugins/'.$plugin);
		} else {
			if((is_dir(SE_CONTENT.'/plugins/'.$plugin)) && (is_file(SE_CONTENT.'/plugins/'.$plugin.'/index.php'))) {
				$plugin_src = file_get_contents(SE_CONTENT.'/plugins/'.$plugin.'/index.php');
				$plugin_info = get_include_contents(SE_CONTENT.'/plugins/'.$plugin.'/index.php');
			}
		}
		
		$btn_delete_addon = '<form class="d-inline ps-2" action="?tn=addons&sub=p" method="POST" onsubmit="return confirm(\''.$lang['confirm_delete_file'].'\');">';
		$btn_delete_addon .= '<button type="submit" name="delete_addon" class="btn btn-sm btn-primary text-danger">'.$icon['trash_alt'].'</button>';
		$btn_delete_addon .= '<input type="hidden" name="type" value="p">';
		$btn_delete_addon .= '<input type="hidden" name="addon" value="'.$plugin.'">';
		$btn_delete_addon .= '<input type="hidden" name="csrf_token" value="'.$_SESSION['token'].'">';
		$btn_delete_addon .= '</form>';
		
		$tpl_icon = "images/plugin-icon.png";
		$source_btn = '<a class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#myModal'.$id.'" href="javascript:;">Source</a>';
		$btn_group = '<div class="float-end">'.$source_btn.$btn_delete_addon.'</div>';
		
		/* shorten the filename if needed */
		$plugin_name = basename($plugin,'.php');
		if(strlen($plugin_name) > 10) {
			$plugin_name = substr($plugin_name, 0,10);
		}
		
		/* show version information */
		$plugin_version = '<p class="text-muted">';
		if($plugin_info['version'] != '') {
			$plugin_version .= '<span class="">Version: '.$plugin_info['version'].'</span> · ';
		}
		/* show author information */
		$plugin_author = '';
		if($plugin_info['author'] != '') {
			$plugin_version .= '<span class="">Author: '.$plugin_info['author'].'</span>';
		}
		$plugin_version .= '</p>';
				
		
		$tpl = $template_file;
		$tpl = str_replace("{\$PLUGIN_NAME}", "$plugin_name","$template_file");
		$tpl = str_replace("{\$PLUGIN_TITLE}", $plugin_info['title'],"$tpl");
		$tpl = str_replace("{\$PLUGIN_DESCRIPTION}", $plugin_info['description'],"$tpl");
		$tpl = str_replace("{\$PLUGIN_VERSION}", $plugin_version,"$tpl");
		$tpl = str_replace("{\$PLUGIN_ICON}", "$tpl_icon","$tpl");
		$tpl = str_replace("{\$PLUGIN_NAV}", "$btn_group","$tpl");
		echo $tpl;

		/* Modal */
		echo '<div class="modal fade" id="myModal'.$id.'" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">';
		echo '<div class="modal-dialog modal-lg" role="document">';
		echo '<div class="modal-content">';
		echo '<div class="modal-header">';
		echo '<h4 class="modal-title" id="myModalLabel'.$id.'">'.$plugin.'</h4>';
		echo '<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>';
		echo '</div>';
		echo '<div class="modal-body">';
		echo '<pre class="form-control" style="height:400px;overflow:auto;">'.htmlentities($plugin_src,ENT_QUOTES,"UTF-8").'</pre>';
		echo '</div>';
		echo '<div class="modal-footer">';
		echo '<button type="button" class="btn btn-primary" data-bs-dismiss="modal">Close</button>';
		echo '</div>';
		echo '</div>';
		echo '</div>';
		echo '</div>';
		
	}
}

function get_include_contents($filename) {
    if (is_file($filename)) {
        ob_start();
        include $filename;
        ob_get_clean();
        return $plugin;
    }
    return false;
}
?>