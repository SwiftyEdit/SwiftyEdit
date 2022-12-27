<?php

//prohibit unauthorized access
require __DIR__.'/access.php';



$cnt_mods = count($all_mods);

if($cnt_mods > 0) {
	echo '<hr class="shadow-line">';
	
	echo '<div class="row row-cols-1 row-cols-md-3 row-cols-xl-5">';
	
	for($i=0;$i<$cnt_mods;$i++) {
		$modFolder = $all_mods[$i]['folder'];
	
		$mod_info_file = SE_CONTENT."/modules/$modFolder/info.inc.php";
		
		$poster_img = '';
		if(is_file(SE_CONTENT."/modules/$modFolder/poster.png")) {
			$poster_img = '<img src="/content/modules/'.$modFolder.'/poster.png" class="card-img-top">';
		} else {
			$poster_img = '<img src="images/poster-addons.png" class="card-img-top">';
		}
		
		if(is_file("$mod_info_file")) {
			
			unset($mod,$modnav);
			include $mod_info_file;
			
			$mod_id = 'id_'.clean_filename($mod['name']);
			
			echo '<div class="col mb-4">';	
			echo '<div class="card" style="max-width:450px;">';
			echo '<div class="card-header p-1"><strong>'.$mod['name'].'</strong> <span class="badge badge-dark float-end">v'.$mod['version'].'</span></div>';
			echo $poster_img;
			echo '<div class="card-img-overlay fade-menu">';
			echo '<div class="list-group list-group-flush">';
			
			foreach($modnav as $nav) {
				echo '<a class="list-group-item list-group-item-ghost p-1 px-2" href="acp.php?tn=moduls&sub='.$modFolder.'&a='.$nav['file'].'">'.$icon['caret_right'].' '.$nav['link'].'</a>';
			}
			
			echo '</div>';
			echo '</div>';
			echo '</div>';
			echo '</div>';	
			
		}
		
		
		
	
	}
	echo '</div>';

	se_get_hook('dashboard_listed_all_addons','');

}
?>