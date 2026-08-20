<?php

/**
 * @var array $lang
 * @var array $mod
 */


/**
 * get all installed Plugins
 * return as array
 */

function get_all_plugins() {
	
	$plugins = array();
	$scanned_directory = array_diff(scandir('assets/plugins/'), array('..', '.','.DS_Store'));
	foreach($scanned_directory as $p) {
		
		$path_parts = pathinfo($p);
		if($path_parts['extension'] == 'php') {
			$plugins[] = $p;
		} else {
			if((is_dir('assets/plugins/'.$p)) && (is_file('assets/plugins/'.$p.'/index.php'))) {
				$plugins[] = $p;
			}
		}
		
	}
	return $plugins;
}



function se_get_all_addons(): array {

    $addons_root = SE_PLUGINS;
    $scanned_directory = array_diff(scandir($addons_root), array('..', '.','.DS_Store'));

    foreach($scanned_directory as $plugin_dir) {
        $addon_info_file = "$addons_root/$plugin_dir/info.json";
        if(is_file("$addon_info_file")) {
            $info_json = file_get_contents("$addon_info_file");
            $addon_info[$plugin_dir] = json_decode($info_json, true);
        }
    }
    return $addon_info;
}


/**
 * Get every installed theme's manifest, keyed by theme directory name, in
 * the same shape as an info.json ("addon" => [...]). Mirrors
 * se_get_all_addons() but scans SE_THEMES.
 *
 * Falls back to parsing the retired info.xml manifest for older themes that
 * don't have an info.json yet (see se_parse_theme_info_xml()) - without
 * this, every pre-info.json theme would simply vanish from the addons UI.
 * A theme with neither file is omitted; callers fall back to the raw
 * directory name/get_all_templates().
 *
 * @return array<string, array>
 */
function se_get_all_themes(): array {

    $themes_root = SE_THEMES;
    $scanned_directory = array_diff(scandir($themes_root), array('..', '.','.DS_Store'));

    $theme_info = [];
    foreach($scanned_directory as $theme_dir) {

        $info_file = "$themes_root/$theme_dir/info.json";
        if(is_file($info_file)) {
            $theme_info[$theme_dir] = json_decode(file_get_contents($info_file), true);
            continue;
        }

        $xml_file = "$themes_root/$theme_dir/info.xml";
        if(is_file($xml_file)) {
            $legacy = se_parse_theme_info_xml($xml_file);
            if($legacy !== null) {
                $theme_info[$theme_dir] = $legacy;
            }
        }
    }
    return $theme_info;
}


/**
 * Parse the retired info.xml theme manifest into the same shape as an
 * info.json's "addon" block, so callers don't need to care which format a
 * given theme still ships. info.xml never had an "id", "build", "versions"
 * or "update_url" field, so a theme identified this way is simply never
 * offered for the install-from-URL/auto-update flow - it can only show up
 * in the plain installed-themes list.
 */
function se_parse_theme_info_xml(string $file): ?array {

    $xml = @simplexml_load_file($file);
    if($xml === false) {
        return null;
    }

    return [
        'addon' => [
            'type' => 'theme',
            'name' => (string) ($xml->name ?? ''),
            'version' => (string) ($xml->version ?? ''),
            'author' => (string) ($xml->author ?? ''),
            'description' => '',
        ]
    ];
}


/**
 * Get the installed and active editor plugins (info.json "type": "editor").
 *
 * An editor is considered active when it is flagged as a core editor
 * ("editor": { "core": true }) or when it has been activated in the
 * se_addons table. Returns the editor meta blocks keyed by plugin directory,
 * each enriched with a "dir" key, sorted by their numeric "order".
 *
 * @return array<string, array>
 */
/**
 * @param array|null $all_plugins Result of se_get_all_addons(), if the caller
 *        already has it - avoids re-scanning plugins/ and re-parsing every
 *        info.json a second time in the same request. Falls back to scanning
 *        itself when omitted.
 */
function se_get_editor_addons(?array $all_plugins = null): array {

    $all = $all_plugins ?? se_get_all_addons();

    // Collect directories of DB-activated plugins
    $active_dirs = [];
    $active_rows = se_get_addons('plugin');
    if (is_array($active_rows)) {
        foreach ($active_rows as $row) {
            if (!empty($row['addon_dir'])) {
                $active_dirs[$row['addon_dir']] = true;
            }
        }
    }

    $editors = [];
    foreach ($all as $dir => $info) {

        if (($info['addon']['type'] ?? '') !== 'editor') {
            continue;
        }

        $is_core = !empty($info['editor']['core']);
        if (!$is_core && empty($active_dirs[$dir])) {
            continue;
        }

        $editor = $info['editor'] ?? [];
        $editor['dir'] = $dir;
        $editors[$dir] = $editor;
    }

    // Order by the numeric "order" hint for a stable switch UI
    uasort($editors, static function ($a, $b) {
        return ($a['order'] ?? 100) <=> ($b['order'] ?? 100);
    });

    return $editors;
}


/**
 * Recursively copy a directory tree.
 */
function se_copy_recursive(string $src, string $dest): void {
    if (!is_dir($src)) {
        return;
    }
    if (!is_dir($dest)) {
        mkdir($dest, 0755, true);
    }
    $items = array_diff(scandir($src), ['.', '..', '.DS_Store']);
    foreach ($items as $item) {
        $from = $src . '/' . $item;
        $to   = $dest . '/' . $item;
        if (is_dir($from)) {
            se_copy_recursive($from, $to);
        } else {
            copy($from, $to);
        }
    }
}


/**
 * Publish an editor plugin's browser assets to the web-served folder
 * public/assets/editors/<id>. Core editors ship their assets pre-published and
 * carry no bundled public/ folder, so this is a no-op for them.
 */
function se_publish_editor_assets(string $plugin_dir): void {
    $plugin_dir = basename($plugin_dir);
    $info_file  = SE_PLUGINS . '/' . $plugin_dir . '/info.json';
    if (!is_file($info_file)) {
        return;
    }
    $info = json_decode(file_get_contents($info_file), true);
    if (($info['addon']['type'] ?? '') !== 'editor') {
        return;
    }
    $id  = basename($info['editor']['id'] ?? '');
    $src = SE_PLUGINS . '/' . $plugin_dir . '/public';
    if ($id === '' || !is_dir($src)) {
        return;
    }
    se_copy_recursive($src, SE_PUBLIC . '/assets/editors/' . $id);
}


/**
 * Remove a non-core editor plugin's published browser assets.
 */
function se_unpublish_editor_assets(string $plugin_dir): void {
    $plugin_dir = basename($plugin_dir);
    $info_file  = SE_PLUGINS . '/' . $plugin_dir . '/info.json';
    if (!is_file($info_file)) {
        return;
    }
    $info = json_decode(file_get_contents($info_file), true);
    if (($info['addon']['type'] ?? '') !== 'editor') {
        return;
    }
    if (!empty($info['editor']['core'])) {
        return; // never remove core editor assets
    }
    $id = basename($info['editor']['id'] ?? '');
    if ($id === '') {
        return;
    }
    $dest = SE_PUBLIC . '/assets/editors/' . $id;
    if (is_dir($dest)) {
        se_reomove_addon_files($dest);
    }
}


function se_load_addon_info(string $url): array {

    // Load info.json
    $json = @file_get_contents($url);

    if($json === false) {
        return ['success' => false, 'message' => 'Could not load URL.'];
    }

    // Parse JSON
    $data = json_decode($json, true);

    if(!$data || !isset($data['addon']) || !isset($data['versions'])) {
        return ['success' => false, 'message' => 'Invalid plugin info.json'];
    }

    // Load SwiftyEdit build number
    $se_version = json_decode(file_get_contents(SE_ROOT.'version.json'), true);
    $se_build = $se_version['build'];

    // Find the most recent compatible version
    $compatible_version = null;

    foreach($data['versions'] as $v) {
        if($se_build >= $v['requires_build']) {
            $compatible_version = $v;
            break;
        }
    }

    if($compatible_version === null) {
        return ['success' => false, 'message' => 'No compatible version found for your SwiftyEdit build ('.$se_build.').'];
    }

    // Determine plugin ID – from info.json or derive from URL
    $plugin_id = $data['addon']['id'] ?? basename(dirname($url));

    if(empty($plugin_id)) {
        return ['success' => false, 'message' => 'Could not determine plugin ID.'];
    }

    return [
        'success' => true,
        'message' => '',
        'addon' => $data['addon'],
        'navigation' => $data['navigation'] ?? [],
        'addon_type' => $data['addon']['type'] ?? null,
        'plugin_id' => $plugin_id,
        'compatible_version' => $compatible_version
    ];
}

function se_check_addon_update(array $addon_info): array {

    // Return unknown if update_url or build is not defined
    if(!isset($addon_info['addon']['update_url']) || !isset($addon_info['addon']['build'])) {
        return ['status' => 'unknown'];
    }

    // Load remote info.json
    $json = @file_get_contents($addon_info['addon']['update_url']);

    if($json === false) {
        return ['status' => 'unknown'];
    }

    $remote = json_decode($json, true);

    if(!$remote || !isset($remote['versions'])) {
        return ['status' => 'unknown'];
    }

    // Load SwiftyEdit build number
    $se_version = json_decode(file_get_contents(SE_ROOT.'version.json'), true);
    $se_build = $se_version['build'];

    // Find most recent compatible version
    $compatible_version = null;

    foreach($remote['versions'] as $v) {
        if($se_build >= $v['requires_build']) {
            $compatible_version = $v;
            break;
        }
    }

    if($compatible_version === null) {
        return ['status' => 'unknown'];
    }

    // Compare build numbers
    if($compatible_version['build'] > $addon_info['addon']['build']) {
        return [
            'status' => 'update_available',
            'version' => $compatible_version['version'],
            'build' => $compatible_version['build'],
            'download_url' => $compatible_version['download_url']
        ];
    }

    return [
        'status' => 'up_to_date',
        'version' => $addon_info['addon']['version'],
        'build' => $addon_info['addon']['build'],
        'download_url' => null
    ];
}


function se_install_plugin(string $plugin_id, string $download_url): array {
    return se_install_addon_zip($plugin_id, $download_url, SE_PLUGINS, 'Plugin');
}


function se_install_theme(string $theme_id, string $download_url): array {
    return se_install_addon_zip($theme_id, $download_url, SE_THEMES, 'Theme');
}


/**
 * Download and extract an addon ZIP (plugin or theme) into $target_root/$addon_id,
 * shared by se_install_plugin() and se_install_theme() - the two only differ in
 * which directory the ZIP is unpacked into.
 */
function se_install_addon_zip(string $addon_id, string $download_url, string $target_root, string $label): array {

    // Download ZIP to temporary file
    $tmp_zip = tempnam(sys_get_temp_dir(), 'se_addon_');
    $zip_content = @file_get_contents($download_url);

    if($zip_content === false) {
        unlink($tmp_zip);
        return ['success' => false, 'message' => 'Could not download '.strtolower($label).' ZIP.'];
    }

    file_put_contents($tmp_zip, $zip_content);

    // Open and validate ZIP
    $zip = new ZipArchive();
    if($zip->open($tmp_zip) !== true) {
        unlink($tmp_zip);
        return ['success' => false, 'message' => 'Could not open ZIP file.'];
    }

    // Validate file types – only allowed extensions
    $allowed_extensions = ['php', 'tpl', 'json', 'js', 'css', 'html', 'svg', 'png', 'jpg', 'jpeg', 'gif', 'webp', 'txt', 'md', 'sqlite3'];

    for($i = 0; $i < $zip->numFiles; $i++) {
        $filename = $zip->getNameIndex($i);
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        if($ext !== '' && !in_array($ext, $allowed_extensions)) {
            $zip->close();
            unlink($tmp_zip);
            return ['success' => false, 'message' => 'ZIP contains invalid file type: '.$filename];
        }
    }

    // Determine addon path
    $addon_path = $target_root . DIRECTORY_SEPARATOR . $addon_id;

    // Create addon directory if necessary
    if(!is_dir($addon_path)) {
        mkdir($addon_path, 0755, true);
    }

    // Extract ZIP – strip root folder, skip /data/ directory
    for($i = 0; $i < $zip->numFiles; $i++) {
        $filename = $zip->getNameIndex($i);

        // Strip first folder
        $relative_path = preg_replace('#^[^/]+/#', '', $filename);

        // Skip empty paths, directories and /data/
        if(empty($relative_path) || str_ends_with($relative_path, '/') || str_starts_with($relative_path, 'data/')) {
            continue;
        }

        // Write file to target path
        $target = $addon_path . DIRECTORY_SEPARATOR . $relative_path;

        // Create subdirectory if necessary
        if(!is_dir(dirname($target))) {
            mkdir(dirname($target), 0755, true);
        }

        file_put_contents($target, $zip->getFromIndex($i));
    }

    $zip->close();
    unlink($tmp_zip);

    return ['success' => true, 'message' => $label.' successfully installed.'];
}

/**
 * @param string $addon directory of the addon
 * @return array
 */
function se_return_addon_translations($addon, $type = 'plugin'): array {
    global $languagePack;
    $translations = [];
    $addon_root = ($type === 'theme') ? SE_THEMES."/".$addon : SE_ROOT."/plugins/".$addon;
    $addons_lang_file = $addon_root."/lang/".$languagePack.'.json';
    $addons_lang_file_alt = $addon_root."/lang/en.json";

    if(is_file($addons_lang_file)) {
        $translations = json_decode(file_get_contents($addons_lang_file), true);
    } else {
        if(is_file($addons_lang_file_alt)) {
            $translations = json_decode(file_get_contents($addons_lang_file_alt), true);
        }
    }
    return $translations;
}


/**
 * get all addons stored in table se_addons
 * type = theme | module
 */
 
function se_get_addons($t='module') {
	
	global $db_content;
	$result = array();
	
	if($t == 'module' OR $t == 'plugin') {
		$type = 'plugin';
	} else {
		$type = 'theme';
	}

	$result = $db_content->select("se_addons", "*", [
	    "addon_type" => "$type"
	]);

	return $result;
}


/**
 * Sanitize + JSON-encode posted addon_values (already plugin-prefixed by the browser,
 * see se_render_record_addons()). Handles both scalar fields (addon_values[key]) and
 * array fields (addon_values[key][], e.g. multi-select/checkboxes).
 */
function se_encode_addon_values(?array $postedAddonValues): string {

	if (!is_array($postedAddonValues)) {
		return '';
	}

	$sanitized = [];
	foreach ($postedAddonValues as $key => $value) {
		if (is_array($value)) {
			$sanitized[$key] = array_map(
				fn($v) => htmlentities(stripslashes($v), ENT_QUOTES),
				$value
			);
		} else {
			$sanitized[$key] = htmlentities(stripslashes($value), ENT_QUOTES);
		}
	}

	return json_encode($sanitized, JSON_UNESCAPED_UNICODE);
}


/**
 * Strip this plugin's prefix from a record's addon_string before handing the data to
 * the plugin's backend/{recordType}-values.php include, so existing plugin code
 * (e.g. $get_addon_data['key']) keeps working unchanged.
 */
function se_prepare_addon_scope(string $pluginSlug, array $recordData): array {

	$allValues = json_decode($recordData['addon_string'] ?? '', true) ?: [];
	$prefix = $pluginSlug . '__';
	$ownValues = [];

	foreach ($allValues as $key => $value) {
		if (str_starts_with($key, $prefix)) {
			$ownValues[substr($key, strlen($prefix))] = $value;
		}
	}

	$recordData['addon_string'] = json_encode($ownValues);
	return $recordData;
}


/**
 * Render addon cards for every active plugin providing backend/{recordType}-values.php
 * (recordType is 'page', 'product' or 'post'). Field names are prefixed with the plugin's
 * slug after include, so plugin authors don't need to namespace their own field names.
 */
function se_render_record_addons(string $recordType, array $recordData): string {

	$combined = '';

	foreach (se_get_addons('plugin') as $addon) {
		$pluginSlug = $addon['addon_dir'];
		$file = SE_PLUGINS . "/{$pluginSlug}/backend/{$recordType}-values.php";

		if (!is_file($file)) {
			continue;
		}

		$record_data = se_prepare_addon_scope($pluginSlug, $recordData);
		$plugin_form_tpl = '';
		include $file;

		$plugin_form_tpl = preg_replace(
			'/name="addon_values\[([a-zA-Z0-9_]+)\](\[\])?"/',
			'name="addon_values[' . $pluginSlug . '__$1]$2"',
			$plugin_form_tpl
		);

		$combined .= '<div class="card mb-1">';
		$combined .= '<div class="card-header">' . htmlspecialchars($addon['addon_name']) . '</div>';
		$combined .= '<div class="card-body">' . $plugin_form_tpl . '</div>';
		$combined .= '</div>';
	}

	return $combined;
}


/**
 * delete addon and its contents
 */
 
function se_delete_addon($addon,$type) {
	
	if($type == 'plugin') {
		$dir = SE_PLUGINS;
	} else if($type == 'theme') {
		$dir = SE_THEMES;
	}
	
	$remove_dir = $dir.'/'.basename($addon);
	se_reomove_addon_files($remove_dir);
	$record_msg = 'removed addon: <strong>'.$addon.' ('.$type.')</strong>';
	record_log($_SESSION['user_nick'],$record_msg,"8");
}

/**
 * remove addon contents
 * folders (recursive) and/or files
 */

 function se_reomove_addon_files($item) {
   if(is_dir($item)){
     $objects = scandir($item);
     foreach ($objects as $object) {
       if ($object != "." && $object != "..") {
         if(filetype($item."/".$object) == "dir") {
         	se_reomove_addon_files($item."/".$object);
         } else {
         		unlink($item."/".$object);
         }
       }
     }
     reset($objects);
     rmdir($item);
   }
   
   if(is_file($item)) {
	   unlink($item);
   }
   
 }



/**
 * show all installed templates
 * return as array
 */

function get_all_templates() {

	//templates folder
	$sdir = SE_ROOT."/public/assets/themes/";
	$cntStyles = 0;
	$scanned_directory = array_diff(scandir($sdir), array('..', '.','.DS_Store'));
	
	foreach($scanned_directory as $tpl_folder) {
		if(is_dir("$sdir/$tpl_folder")) {
			$arr_Styles[] = "$tpl_folder";
		}	
	}

	return($arr_Styles);
}

/**
 * return available stylesheets from a theme
 * find css files theme_*.css
 */
 
function se_get_stylesheets($theme){
	
	$stylesheets = glob('../styles/'.$theme.'/css/theme_*.css');
	
	if(is_array($stylesheets) && (count($stylesheets) > 0)){
		return $stylesheets;
	} else {
		return '0';
	}
	
	
}



/**
 * check in active modules and pages with posts
 * generate array from pages containing a module or post categories
 * and from addon_dir -> content.sqlite3
 * store in ... cache/active_addons.json
 */

function mods_check_in() {
	
	global $db_content;

    $m = [];
    $data = [];

	$mods = $db_content->select("se_addons", "addon_dir", [
	    "addon_type" => ["module","plugin"]
	]);

    foreach ($mods as $mod) {
        $m[] = [
            'page_modul' => $mod,
            'page_permalink' => 'NULL',
            // $pages entries (below) come from a select() with these
            // columns - plugins/modules don't have pages, so default them
            // to the same "empty" shape to avoid undefined-key warnings
            // once $pages and $m are merged into $items.
            'page_posts_categories' => '',
            'page_type_of_use' => '',
        ];
    }

	$pages = $db_content->select("se_pages", ["page_modul","page_permalink","page_posts_categories","page_type_of_use"]);
	$items = array_merge($pages, $m);

	$cnt_items = count($items);
	for($i=0;$i<$cnt_items;$i++) {
	
		if($items[$i]['page_modul'] != "" OR
            $items[$i]['page_posts_categories'] != "" OR
            $items[$i]['page_type_of_use'] == "display_post" OR
            $items[$i]['page_type_of_use'] == "display_product" OR
            $items[$i]['page_type_of_use'] == "display_event" OR
            $items[$i]['page_type_of_use'] == "wishlist" OR
            $items[$i]['page_type_of_use'] == "tagged") {

			if($items[$i]['page_posts_categories'] != '') {
				$items[$i]['page_modul'] = 'se_post';
			}

			if($items[$i]['page_type_of_use'] == 'display_post') {
				$items[$i]['page_modul'] = 'se_post';
			}
            if($items[$i]['page_type_of_use'] == 'display_product') {
                $items[$i]['page_modul'] = 'se_shop';
            }
            if($items[$i]['page_type_of_use'] == 'display_event') {
                $items[$i]['page_modul'] = 'se_events';
            }
            if($items[$i]['page_type_of_use'] == 'wishlist') {
                $items[$i]['page_modul'] = 'se_wishlist';
            }
            if($items[$i]['page_type_of_use'] == 'tagged') {
                $items[$i]['page_modul'] = 'se_tagged';
            }

            $data[] = [
                'page_modul' => $items[$i]['page_modul'],
                'page_permalink' => $items[$i]['page_permalink']
            ];

		}
	
	}

    $file = SE_CONTENT . "/cache/active_addons.json";
    file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

    /**
     * Registry of activated plugins that ship a bootstrap-free endpoint.php,
     * read by public/dispatch.php. This is what lets a plugin run before
     * app.php's DB/session/Smarty bootstrap (e.g. for a fast asset endpoint)
     * without dispatch.php trusting the request to say which plugin is
     * allowed - only plugins that are both activated (present in $mods,
     * straight from se_addons) AND ship an endpoint.php end up here. A
     * plugin already gets full code-execution trust the moment it's
     * activated (via hooks-global/hooks-frontend/global/index.php on every
     * normal request) - this only adds one more entry point to that same,
     * already-granted trust, it does not grant anything new.
     */
    $bootstrap_endpoints = [];
    foreach ($mods as $mod) {
        if (is_file(SE_PLUGINS . '/' . $mod . '/endpoint.php')) {
            $bootstrap_endpoints[$mod] = true;
        }
    }
    file_put_contents(
        SE_CONTENT . '/cache/bootstrap_endpoints.json',
        json_encode($bootstrap_endpoints, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
    );
}

/**
 * write/update theme options
 * $data (array) $data['theme'] -> name of the theme
 * values are prefixed by 'theme' f.e. $data['theme_']
 */

function se_write_theme_options($data) {

	global $db_content;

	$db_content->delete("se_themes", [
		"theme_name" => $data['theme']
	]);

	foreach($data as $key => $value) {

		if($key == 'theme') {
			$theme = $value;
			continue;
		}

		if((strstr($key, '_', true)) == 'theme') {
			$db_content->insert("se_themes", ["theme_name" => $data['theme'],"theme_label" => "$key","theme_value" => "$value"]);
		}


	}
}

/**
 * Load the catalog of installable plugins from the SwiftyEdit catalog
 * service (swiftyedit.net/api - syncs the swiftyedit/registry GitHub repo
 * into a small REST API; version/build/download_url are resolved there
 * live from each entry's own info.json on every sync, not stored in the
 * registry itself). Cached with a simple TTL file cache (no generic cache
 * helper exists in this codebase - this follows the same
 * SE_CONTENT/cache/<subdir>/ convention already used by mods_check_in()
 * and se_get_categories()).
 *
 * A stale-but-present cache is preferred over an empty catalog when the
 * live fetch fails - the service being temporarily unreachable shouldn't
 * blank out an otherwise-working page.
 *
 * @return array{success: bool, source: string, entries: array, message: string}
 */
function se_get_catalog_entries(bool $force_refresh = false): array {

	$cache_dir  = SE_CONTENT.'/cache/registry';
	$cache_file = $cache_dir.'/plugins.json';
	$ttl        = 3600;

	$is_fresh = is_file($cache_file) && (filemtime($cache_file) >= time() - $ttl);

	if(!$force_refresh && $is_fresh) {
		$cached = json_decode(file_get_contents($cache_file), true);
		if(is_array($cached)) {
			return ['success' => true, 'source' => 'cache', 'entries' => $cached, 'message' => ''];
		}
	}

	$json = @file_get_contents('https://swiftyedit.net/api/plugins');

	if($json === false || !is_array($rows = json_decode($json, true))) {
		$message = $json === false ? 'Could not reach the plugin catalog.' : 'Unexpected catalog response.';
		if(is_file($cache_file)) {
			$cached = json_decode(file_get_contents($cache_file), true);
			if(is_array($cached)) {
				return ['success' => true, 'source' => 'stale_cache', 'entries' => $cached, 'message' => $message];
			}
		}
		return ['success' => false, 'source' => 'none', 'entries' => [], 'message' => $message];
	}

	$entries = [];
	foreach($rows as $row) {

		if(empty($row['slug'])) {
			continue;
		}

		// The catalog service stores tags as a JSON-encoded string column
		// and returns it as such via SELECT * - decode here so every
		// caller always gets a real array, never a raw JSON string.
		$row['tags'] = is_string($row['tags'] ?? null) ? (json_decode($row['tags'], true) ?: []) : ($row['tags'] ?? []);

		$entries[$row['slug']] = $row;
	}

	if(!is_dir($cache_dir)) {
		mkdir($cache_dir, 0777, true);
	}
	file_put_contents($cache_file, json_encode($entries, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

	return ['success' => true, 'source' => 'live', 'entries' => $entries, 'message' => ''];
}

/**
 * Derive a raw.githubusercontent.com URL for a file at the root of a
 * registry entry's "repo" field (e.g. "info.json" for the existing
 * get_addon_info_from_url install pipeline, or "poster.png" for the
 * catalog's card thumbnail). Returns null for anything that isn't a plain
 * https://github.com/{org}/{repo} URL.
 *
 * Hardcodes the "main" branch (true for every plugin repo published so far);
 * isolated here so a future per-entry "branch" field only needs to change
 * this one function.
 */
function se_registry_repo_to_raw_url(string $repo_url, string $path): ?string {

	$repo_url = rtrim(trim($repo_url), '/');

	if(!preg_match('#^https://github\.com/([^/]+)/([^/]+)$#', $repo_url, $m)) {
		return null;
	}

	return "https://raw.githubusercontent.com/{$m[1]}/{$m[2]}/main/{$path}";
}