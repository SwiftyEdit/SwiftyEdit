<?php

/**
 * Save, Duplicate, Delete, Preview and Update Pages
 * @author Patrick Konstandin
 *
 */

//prohibit unauthorized access
require __DIR__.'/access.php';

$show_form = "true";
$modus = "new";

foreach($_POST as $key => $val) {
	$$key = $val; 
}

if(!empty($_POST['editpage'])) {
	$editpage = (int) $_POST['editpage'];
	$modus = "update";
}

if((!empty($_POST['duplicate'])) OR ($_POST['modus'] == 'duplicate')) {
	$editpage = (int) $_POST['duplicate'];
	$modus = "duplicate";
}

if(!empty($_POST['preview_the_page'])) {
	$editpage = (int) $_POST['editpage'];
	$modus = "preview";
}


/**
 * if we have custom fields
 * expand the array ($pdo_fields...)
 */
 
if(preg_match("/custom_/i", implode(",", array_keys($_POST))) ){
  $custom_fields = get_custom_fields();
  $cnt_result = count($custom_fields);
  
  for($i=0;$i<$cnt_result;$i++) {
  	if(substr($custom_fields[$i],0,7) == "custom_") {
  		$cf = $custom_fields[$i]; 		
  		$custom_fields[] = $cf;
  	}
  }   
}


/**
 * delete the page by page_id - $editpage
 */

if(isset($_POST['delete_the_page'])) {


	if(is_numeric($editpage)) {
		$comment_id = 'p'.$editpage;
		
		/**
		 * we check, if this page has subpages
		 * if there are subpages, we can not delete the page
		 */

		$delete_page = $db_content->get("se_pages", ["page_sort","page_language"],[
			"page_id" => $editpage
		]);
		
		$delpage_sort = $delete_page['page_sort'];
		$delpage_lang = $delete_page['page_language'];
		
		if($delpage_sort != '') {
			$subpages = $db_content->select("se_pages", ["page_sort","page_title"],[
				"AND" => [
					"page_sort[~]" => "$delpage_sort%",
					"page_sort[!]" => "$delpage_sort",
					"page_language" => $delpage_lang
				]
			]);
		} else {
			$subpages = array();
		}
		
		$cnt_subpages = count($subpages);
		
		if($cnt_subpages > 0) {
			echo '<div class="alert alert-danger">';
			echo $lang['msg_error_deleting_sub_pages'] .' ('.$cnt_subpages.')';
			echo '<ol>';
			foreach($subpages as $pages) {
				echo '<li>'.$pages['page_title'].'</li>';
			}
			echo '</ol>';
			
			echo '</div>';
		} else {

			$del_page = $db_content->delete("se_pages", [
				"page_id" => $editpage
			]);
			$db_content->delete("se_pages_cache", [
				"page_id_original" => $editpage
			]);
			$db_content->delete("se_pages_cache", [
				"page_id_original" => NULL
			]);
			$db_content->delete("se_comments", [
				"comment_parent" => $comment_id
			]);
			
			if($del_page->rowCount() > 0) {
				$success_message = '{OKAY} '. $lang['msg_success_page_deleted'];
				record_log($_SESSION['user_nick'],"deleted page id: $editpage","10");
				generate_xml_sitemap();
				unset($editpage);
				print_sysmsg("$success_message");
			}
		}
	}




	$show_form = "false";
}



/**
 * Save, update or show preview
 */

if(!empty($_POST['save_the_page']) OR (!empty($_POST['preview_the_page']))) {

	/**
	 * modus update
	 */
	
	if($modus == "update") {

		se_update_page($_POST,$editpage);
		// take a snapshot
		se_snapshot_page($editpage);
	}


	/**
	 * modus new page
	 * or duplicate page
	 */							
	
	if($modus == "new" || $modus == 'duplicate') {

		$editpage = se_save_page($_POST);
		// take a snapshot
		se_snapshot_page($editpage);
	
	} // eo modus new



	/**
	 * modus preview
	 */
	 							
	if($modus == "preview") {

		se_save_preview_page($_POST);
		
		/* delete older entries from se_pages_cache */
		$interval = time() - 86400; // now - 24h		
		$db_content->delete("se_pages_cache", [
			"AND" => [
				"page_cache_type" => "preview",
				"page_lastedit[<]" => $interval
			]
		]);
		
		
	} // eo modus preview



	$dbh = null;
	
	
	/* generate cache files */
	mods_check_in();
	cache_url_paths();
	

    if (isset($_POST['send_hook'])) {
        if (is_array($_POST['send_hook'])) {
            se_run_hooks($_POST['send_hook'],$_POST);
        }
    }


	se_delete_smarty_cache(md5($_POST['page_permalink']));
	
	if($_POST['page_status'] == 'ghost' OR $_POST['page_status'] == 'public') {
		se_update_or_insert_index($_POST['page_permalink']);
	}
	

}


/* get the data to fill the form (again) */
if(isset($editpage) AND is_numeric($editpage)) {

	if($modus == "preview") {		
		$page_data = $db_content->get("se_pages_cache","*",[
			"AND" => [
			"page_id_original" => $editpage
		],
			"ORDER" => ["page_id" => "DESC"]
		]);
		
	} else {
		$page_data = $db_content->get("se_pages","*",[ "page_id" => $editpage ]);
	}
	
	if(!empty($_POST['restore_id'])) {
		$restore_id = (int) $_POST['restore_id'];
		$page_data = $db_content->get("se_pages_cache","*",[ "page_id" => $restore_id ]);	
		$restore_page_version = $db_content->query("SELECT page_version FROM se_pages WHERE page_id = $editpage")->fetch();
	}

	
	foreach($page_data as $k => $v) {
	   $$k = htmlentities(stripslashes($v), ENT_QUOTES, "UTF-8");
	}
	
	/**
	 * check if this page can handle theme values
	 */
	if($page_data['page_template'] == 'use_standard') {
		// get theme from prefernces
		$theme_base = '../styles/'.$se_prefs['prefs_template'];
	} else {
		$theme_base = '../styles/'.$page_data['page_template'];
	}
	
	$theme_tab = '';
	if(is_file("$theme_base".'/php/page_values.php')) {
		$theme_tab = '<li class="nav-item"><a class="nav-link" href="#theme_values" data-bs-toggle="tab">Theme</a></li>';
	}
	
	if(is_array($restore_page_version)) {
		$page_version = $restore_page_version['page_version'];
	}
	
	
	$form_title = '<h3>'.$lang['status_edit'].' <span class="badge bg-widget">ID: '.$editpage.'</span> <span class="badge bg-widget">Version: '.$page_version.'</span></h3>';
	//set submit button
	$submit_button = "<input type='submit' class='btn btn-success w-100' name='save_the_page' value='$lang[update]'>";
	$delete_button = "<hr><input type='submit' class='btn btn-danger btn-sm w-100' name='delete_the_page' value='$lang[delete]' onclick=\"return confirm('$lang[msg_confirm_delete]')\">";
	$previev_button = "<input type='submit' class='btn btn-default w-100' id='preview_the_page' name='preview_the_page' value='$lang[preview]'>";
	
	if($modus == 'duplicate') {
		$form_title = '<h3>'.$lang['h_modus_duplicate'].' - '.$page_title.'</h3>';
		$submit_button = "<input type='submit' class='btn w-100 btn-success' name='save_the_page' value='$lang[duplicate]'>";
		$delete_button = '';
		$previev_button = '';
	}
	
} else {
	// modus newpage
	
	
	$form_title = '<h3>'.$lang['status_new'].'</h3>';
	//set submit button
	$submit_button = "<input type='submit' class='btn btn-success w-100' name='save_the_page' value='$lang[save]'>";
	$delete_button = '';
	$previev_button = '';
}

echo '<div class="subHeader">';
echo $form_title;
echo '</div>';


if($_SESSION['acp_editpages'] != "allowed") {
	$arr_checked_admins = explode(",",$page_authorized_users);
	if(!in_array("$_SESSION[user_nick]", $arr_checked_admins)) {
		$show_form = "false";
		echo '<p>'.$lang['rm_no_access'].'</p>';
	}
}


if($show_form == "true") {
	include 'pages.edit_form.php';
}


/* Attach the preview */

if(!empty($_POST['preview_the_page'])) {

    echo '<div class="modal fade" id="previewModal" tabindex="-1" aria-labelledby="previewModalLabel" aria-hidden="true">';
    echo '<div class="modal-dialog modal-xl">';
    echo '<div class="modal-content">';
    echo '<div class="modal-header">Preview</div>';
    echo '<div class="modal-body">';

	//echo '<div class="alert alert-info alert-block">';
	echo '<iframe src="../index.php?preview='.$editpage.'" width="100%" height="600">';
	echo '<a href="../index.php?preview='.$editpage.'" target="_blank">../index.php?preview='.$editpage.'</a>';
	echo '</iframe>';
	//echo '</div>';

    echo '</div>';
    echo '</div>';
    echo '</div>';
    echo '</div>';

    ?>
    <script>
    $( document ).ready(function() {
        $("#previewModal").modal('show');
    });
    </script>
    <?php

}



/* show older versions of the current page */

if($show_form == "true" AND $sub != "new") {

	
	$max = 25;
	if($prefs_nbr_page_versions != '') {
		$max = $prefs_nbr_page_versions;
	}
	
	$cnt_all_sql = "SELECT COUNT(*) AS 'nbr' FROM se_pages_cache WHERE page_id_original = $editpage AND page_cache_type = 'history' ";
	$cnt_all = $db_content->query($cnt_all_sql)->fetch(PDO::FETCH_ASSOC);
	$delete_nbr = $cnt_all['nbr']-$max;

	
	$cache_result = $db_content->select("se_pages_cache",
		[
		"page_id", "page_linkname", "page_title", "page_lastedit", "page_lastedit_from", "page_version"
		],[ 
		"AND" => [
			"page_id_original" => $editpage,
			"page_cache_type" => "history"
			],
		"ORDER" => ["page_id" => "DESC"]
	 ]);
	
	$cnt_result = count($cache_result);
	
	echo '<hr>';
	echo '<div class="card p-3">';

    echo '<div class="accordion accordion-flush" id="accordionVersions">';
    echo '<div class="accordion-item">';
    echo '<h2 class="accordion-header" id="headingOne">';
	echo '<button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseVersions" aria-expanded="false" aria-controls="collapseOne">Versions ('.$cnt_result.')</button>';
    echo '</h2>';
	
	echo '<div id="collapseVersions" class="accordion-collapse collapse" aria-labelledby="headingOne" data-bs-parent="#accordionVersions">';
	echo '<div class="accordion-body">';
	echo '<div class="scroll-container">';
	echo '<table class="table table-condensed table-hover">';

	for($i=0;$i<$cnt_result;$i++) {
	
		$nbr = $i+1;
		$page_id = $cache_result[$i]['page_id'];

		
		if($i >= $max) {
			
			$del_sql = "DELETE FROM se_pages_cache WHERE page_id IN (
								SELECT page_id
								FROM se_pages_cache
								WHERE page_id_original = '$editpage'
								ORDER BY page_lastedit ASC
								LIMIT 1)";
			
			$db_content->query("$del_sql");
			continue;

		}
		
		
		
		$date = date("d.m.Y",$cache_result[$i]['page_lastedit']);
		$time = date("H:i:s",$cache_result[$i]['page_lastedit']);
		$yesterday = date('d.m.Y', time()-(60*60*24));
		$today = date('d.m.Y', time());
			
		if($date == "$today") {
			$setdate = $lang['today'];
		} elseif($date == "$yesterday") {
			$setdate = $lang['yesterday'];
		} else {
			$setdate = $date;
		}
		
		$edit_button = '<button class="btn btn-sm btn-default w-100" name="editpage" value="'.$editpage.'" title="'.$lang['edit'].'">'.$icon['edit'].' '.$lang['edit'].'</button>';
			
		echo '<tr>';
		echo '<td>' . $cache_result[$i]['page_version'] . '</td>';
		echo '<td width="100">'.$setdate.'</td>';
		echo '<td width="100">'.$time.'</td>';
		echo '<td>' . $cache_result[$i]['page_title'] . '</td>';
		echo '<td>' . $cache_result[$i]['page_lastedit_from'] . '</td>';
		echo '<td width="150" align="right">';
		echo '<form action="?tn=pages&sub=edit" method="POST">';
		echo $edit_button;
		echo '<input type="hidden" name="restore_id" value="'.$page_id.'">';
		echo '<input type="hidden" name="csrf_token" value="'.$_SESSION['token'].'">';
		echo '</form>';
		echo '</td>';
		echo '</tr>';
	}
	

	echo '</table>';
	echo '</div>'; // scroll-container
	echo '</div>'; // accordion-body
	echo '</div>'; // collapse
	echo '</div>'; // accordion-item
	echo '</div>'; // accordion
    echo '</div>'; // card
}

?>