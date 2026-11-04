<?php

/**
 * update channels of versions.json compared with the installed build,
 * rendered by acp/templates/update/channels.twig
 *
 * @param array $remote_versions decoded versions.json, see get_remote_versions()
 * @return array|null list of {name, title, build, date, file, available, installed, reloadable},
 *   null if swiftyedit.net was not reachable or the file is invalid
 */
function se_update_channels(array $remote_versions): ?array {

    global $se_environment;

    if (!is_array($remote_versions['version'] ?? null)) {
        return null;
    }

    $se_version = json_decode((string) file_get_contents(SE_ROOT.'version.json'), true);
    $installed_build = (string) ($se_version['build'] ?? '');

    $channels = [];
    foreach (['stable', 'beta', 'alpha'] as $channel) {
        $remote = $remote_versions['version'][$channel] ?? null;
        if (!is_array($remote)) {
            continue;
        }
        $remote_build = (string) ($remote['build'] ?? '');
        $channels[] = [
            'name' => $channel,
            'title' => (string) ($remote['title'] ?? ''),
            'build' => $remote_build,
            'date' => (string) ($remote['date'] ?? ''),
            'file' => basename((string) ($remote['file'] ?? '')),
            // builds are compared as strings, e.g. 26-0229 < 26-0230
            'available' => ($installed_build < $remote_build),
            'installed' => ($installed_build === $remote_build),
            // in development mode, alpha can always be reloaded
            'reloadable' => ($channel === 'alpha' && $se_environment == 'd')
        ];
    }
    return $channels;
}

/**
 * Notices from swiftyedit.net ("notices" in versions.json), e.g. to announce
 * changes before an update. Shown on the update page, newest first as in the
 * file, rendered by acp/templates/update/notices.twig as plain text - no HTML
 * from the remote file reaches the backend, links only for https:// URLs.
 *
 * Format of one notice (title and text required, build and link optional;
 * build = the build from which the notice applies):
 * {"build": "26-0230", "title": "...", "text": "...", "link": "https://..."}
 *
 * @param array $remote_versions decoded versions.json
 * @return array list of {title, text, build, link}
 */
function se_update_notices(array $remote_versions): array {

    $notices = $remote_versions['notices'] ?? [];
    if (!is_array($notices)) {
        return [];
    }

    $valid_notices = [];
    foreach ($notices as $notice) {
        if (!is_array($notice) || !is_string($notice['title'] ?? null) || !is_string($notice['text'] ?? null)) {
            continue;
        }
        $link = $notice['link'] ?? '';
        $valid_notices[] = [
            'title' => $notice['title'],
            'text' => $notice['text'],
            'build' => is_string($notice['build'] ?? null) ? $notice['build'] : '',
            'link' => (is_string($link) && str_starts_with($link, 'https://') && filter_var($link, FILTER_VALIDATE_URL)) ? $link : ''
        ];
    }
    return $valid_notices;
}

/**
 * message of the update writer - the writer keeps printing plain HTML instead of
 * Twig templates: during an update it runs as the old version, a template loaded
 * after move_new_files() would already be the one of the new version
 *
 * @param string $type bootstrap alert type (info, warning, danger, success)
 * @param string $text plain text, escaped here
 * @return string
 */
function se_update_alert(string $type, string $text): string {
    return '<div class="alert alert-'.$type.'">'.htmlspecialchars($text, ENT_QUOTES).'</div>';
}

/**
 * @return array
 */
function get_remote_versions(): array {
    $remote_versions_file = @file_get_contents("https://swiftyedit.net/releases/v2/versions.json");
    $remote_versions_array = json_decode((string) $remote_versions_file,true);
    // server not reachable or invalid file - empty instead of a TypeError
    return is_array($remote_versions_array) ? $remote_versions_array : [];
}


/**
 * get the files from the extracted zip file
 * and copy them to their destination
 *
 * @param string $source directory of the extracted release in download/extract/
 * @return bool false if the release is missing - nothing has been changed then
 */
function move_new_files(string $source): bool {

    // copy_recursive() counts the errors
    $_SESSION['errors_cnt'] = 0;

    $sources_path = '../acp/core/update/download/extract/';
    $sources_dir = basename($source);

    // without a complete release, /install/ must not be removed below
    if($sources_dir === '' || !is_dir($sources_path.$sources_dir.'/install')) {
        return false;
    }
    $new_files = scandir_recursive($sources_path.$sources_dir);

    // at first, the /install/ directory
    rmdir_recursive('../install');
    copy_recursive($sources_path.$sources_dir."/install","../install");

    // update htaccess file
    update_htaccess_file();

    // bundled plugins (payment + editors) - these ship inside /plugins/ like any
    // other plugin, but /plugins/ as a whole is excluded from the generic copy
    // loop below (so user-installed plugins are left alone), so the ones that
    // are actually part of the release have to be re-copied explicitly here
    copy_recursive($sources_path.$sources_dir."/plugins/se_invoice-pay","../plugins/se_invoice-pay");
    copy_recursive($sources_path.$sources_dir."/plugins/se_cash-pay","../plugins/se_cash-pay");
    copy_recursive($sources_path.$sources_dir."/plugins/tinymce-editor","../plugins/tinymce-editor");
    copy_recursive($sources_path.$sources_dir."/plugins/ace-editor","../plugins/ace-editor");

    // now copy the other files and directories
    foreach($new_files as $value) {

        if(str_contains("$value","/install/")) { continue; }
        if(str_contains("$value","/data/")) { continue; }
        if(str_contains("$value","/plugins/")) { continue; }
        if(str_contains("$value",".github")) { continue; }
        if(str_contains("$value",".idea")) { continue; }
        if(str_starts_with(basename($value),".")) { continue; }
        if($value === '.' || $value === '..') {continue;}
        if(str_contains("$value","robots.txt")) { continue; }

        // copy files from 'download/extract/*'
        $target = '..' . substr($value, strlen($sources_path.$sources_dir));
        copy_recursive("$value","$target");

    }

    // remove files from older builds that are no longer part of this release
    // (the copy loop above only ever adds/overwrites files)
    remove_obsolete_files($sources_path.$sources_dir);

    return true;
}

/**
 * Delete local files that are no longer part of the new release.
 * Compares the current install against whitelist.json (built by
 * build_prepare.sh), which lists every file the release ships under
 * acp/, app/, languages/, vendor/ and the two bundled themes
 * (public/assets/themes/administration, public/assets/themes/default).
 * Anything under those directories that isn't in the whitelist is a
 * leftover from an older build (e.g. a renamed/removed compiled theme
 * asset) and gets removed. install/ is already fully replaced in
 * move_new_files(); data/, plugins/ and any other/custom theme under
 * public/assets/themes/ are intentionally left untouched (user data /
 * installed plugins / user themes). acp/core/update/download/ is also
 * skipped: it's the updater's own runtime cache (gitignored, never part
 * of the release build), so it never appears in whitelist.json.
 * @return void
 */
function remove_obsolete_files($extracted_source_dir) {
    $whitelist_file = $extracted_source_dir.'/whitelist.json';

    if (!is_file($whitelist_file)) {
        $_SESSION['protocol'] .= 'ERROR whitelist.json not found in release, skipping cleanup of obsolete files<|>';
        return;
    }

    $whitelist = json_decode(file_get_contents($whitelist_file), true);

    if (!is_array($whitelist)) {
        $_SESSION['protocol'] .= 'ERROR whitelist.json could not be parsed, skipping cleanup of obsolete files<|>';
        return;
    }

    $whitelist = array_flip($whitelist);

    foreach (['../acp', '../app', '../languages', '../vendor', '../public/assets/themes/administration', '../public/assets/themes/default'] as $dir) {
        if (!is_dir($dir)) {
            continue;
        }

        foreach (scandir_recursive($dir) as $path) {
            if (is_dir($path)) {
                continue;
            }

            $relative = substr($path, strlen('../'));

            if (str_starts_with($relative, 'acp/core/update/download/')) {
                continue;
            }

            if (!isset($whitelist[$relative])) {
                unlink($path);
                $_SESSION['protocol'] .= 'removed obsolete file: '.htmlspecialchars($relative, ENT_QUOTES).'<|>';
            }
        }

        remove_empty_dirs_recursive($dir);
    }
}

/**
 * Remove now-empty directories left behind by remove_obsolete_files().
 * @return void
 */
function remove_empty_dirs_recursive($dir) {
    if (!is_dir($dir)) {
        return;
    }

    foreach (scandir($dir) as $entry) {
        if ($entry === '.' || $entry === '..') {
            continue;
        }

        $path = $dir.'/'.$entry;

        if (is_dir($path)) {
            remove_empty_dirs_recursive($path);
        }
    }

    if (count(scandir($dir)) === 2) {
        rmdir($dir);
    }
}

/**
 * all files and directories of a directory (recursive)
 *
 * @param string $dir
 * @return array paths, e.g. "$dir/sub/file.php"
 */
function scandir_recursive(string $dir): array {
    $result = [];
    foreach(scandir($dir) ?: [] as $value) {
        if($value === '.' || $value === '..') {continue;}
        $result[] = "$dir/$value";
        if(is_dir("$dir/$value")) {
            foreach(scandir_recursive("$dir/$value") as $sub_value) {
                $result[] = $sub_value;
            }
        }
    }
    return $result;
}


/**
 * copy a file or a directory with its contents, the result is written to the protocol
 *
 * @param string $source
 * @param string $target
 * @return void
 */
function copy_recursive(string $source, string $target): void {

    if(is_dir($source)) {
        if(!is_dir("$target")) {
            $_SESSION['protocol'] .= "missing: ".htmlspecialchars($target, ENT_QUOTES)." <|>";
            mkdir_recursive($target,0755);
        }

        $dir = dir($source);
        while(FALSE !== ($entry = $dir->read())) {

            if($entry == '.' || $entry == '..') { continue; }

            $sub = $source . '/' . $entry;

            if(is_dir($sub)) {
                chmod("$sub", 0755);
                copy_recursive($sub, $target . '/' . $entry);
                // copy() of a directory would only fail with a warning
                continue;
            }
            copy($sub, $target . '/' . $entry);
        }

        $dir->close();
    } else {
        // replace the file - copy() creates it with the default (umask) permissions,
        // it used to be chmod'ed to 0777 (world-writable) first
        if(is_file($target)) {
            unlink($target);
        }
        if(copy($source, $target)) {
            $_SESSION['protocol'] .= '<b>copied:</b> '.htmlspecialchars($target, ENT_QUOTES).'<|>';
        } else {
            $error = error_get_last();
            $_SESSION['protocol'] .= '<b class="text-danger">ERROR:</b> '.htmlspecialchars($target, ENT_QUOTES).' '.htmlspecialchars($error['message'] ?? '', ENT_QUOTES).'<|>';
            $_SESSION['errors_cnt'] = ($_SESSION['errors_cnt'] ?? 0) + 1;
        }
    }
}

/**
 * delete directory (recursive)
 */

function rmdir_recursive($dir) {
    if (is_dir($dir)) {
        $objects = scandir($dir);
        foreach ($objects as $object) {
            if ($object != "." && $object != "..") {
                if(filetype($dir."/".$object) == "dir") {
                    rmdir_recursive($dir."/".$object);
                } else {
                    unlink($dir."/".$object);
                }
            }
        }
        reset($objects);
        rmdir($dir);
    }
}

/**
 * create a directory and its missing parents, written to the protocol
 *
 * @param string $dir
 * @param int $chmod
 * @return void
 */
function mkdir_recursive(string $dir, int $chmod = 0755): void {
    $directory = '';
    foreach (explode('/', $dir) as $part) {
        $directory .= $part.'/';
        if(!is_dir($directory)) {
            mkdir($directory, $chmod);
            chmod("$directory", $chmod);
            $_SESSION['protocol'] .= "created: ".htmlspecialchars($directory, ENT_QUOTES)." <|>";
        }
    }
}


/**
 * Update the database
 * build an array from all php files in directory ../install/contents
 * @return void
 */
function update_database() {

    // every table file sets $database, $table_name and $cols - loaded in its own
    // scope, so no values from the previous table file are carried over
    $load_table = static function(string $file): array {
        include $file;
        $table = get_defined_vars();
        return [
            (string) ($table['database'] ?? ''),
            (string) ($table['table_name'] ?? ''),
            is_array($table['cols'] ?? null) ? $table['cols'] : []
        ];
    };

    foreach(glob(__DIR__."/../../../install/contents/*.php") ?: [] as $table_file) {

        [$database, $table_name, $cols] = $load_table($table_file);

        $is_table = table_exists("$database","$table_name");

        if($is_table < 1) {
            add_table("$database","$table_name",$cols);

            $_SESSION['protocol'] .= '<b class="text-success">new table:</b> '.htmlspecialchars($table_name, ENT_QUOTES).' in '.htmlspecialchars($database, ENT_QUOTES).'<|>';
        }


        $existing_cols = get_columns("$database","$table_name");


        foreach ($cols as $k => $v) {

            if(!array_key_exists("$k", $existing_cols)) {
                //update_table -> column, type, table, database
                update_table("$k","$cols[$k]","$table_name","$database");
                $_SESSION['protocol'] .= '<b class="text-success">new column:</b> '.htmlspecialchars($k, ENT_QUOTES).' in table '.htmlspecialchars($table_name, ENT_QUOTES).'<|>';
            }

        }
    }
}

/**
 * Update public/.htaccess from install/contents/_htaccess.txt
 *
 * Only the core block is replaced - the section before it (own rules that
 * have to run before the front controller) and the one after it are kept.
 * Whenever the file changes, the previous version is saved to
 * SE_CONTENT/backups/ first. $se_update_htaccess = false in data/config.php
 * leaves the file alone entirely (e.g. when it is managed elsewhere).
 *
 * Notes for the person running the update are collected in
 * $_SESSION['update_notices'] (shown below the protocol).
 */
function update_htaccess_file(): void
{
    global $se_update_htaccess;

    $htaccessFile = SE_ROOT . '/public/.htaccess';
    $templateFile = SE_ROOT . '/install/contents/_htaccess.txt';

    if (($se_update_htaccess ?? true) === false) {
        $_SESSION['protocol'] .= '<b>.htaccess:</b> not updated ($se_update_htaccess = false)<|>';
        $_SESSION['update_notices'][] = 'public/.htaccess was not updated because $se_update_htaccess is set to false. Compare it with install/contents/_htaccess.txt and apply changes of the core block yourself.';
        return;
    }

    // Load current and template
    $current  = file_exists($htaccessFile) ? (string) file_get_contents($htaccessFile) : '';
    $template = file_exists($templateFile) ? file_get_contents($templateFile) : '';

    if ($template === false || $template === '') {
        die("No template core rules found.\n");
    }

    // Markers
    $beforeMarker    = "# --- User rules before SwiftyEdit (safe to edit) ---";
    $beforeEndMarker = "# --- END user rules before SwiftyEdit ---";
    $startMarker     = "# --- SwiftyEdit core (do not edit) ---";
    $endCoreMarker   = "# --- END SwiftyEdit core ---";

    // --- Extract "core-only" section from template (start..END core, without user marker) ---
    $tStart = strpos($template, $startMarker);
    $tEnd   = strpos($template, $endCoreMarker);
    if ($tStart === false || $tEnd === false) {
        $_SESSION['protocol'] .= '<b class="text-danger">.htaccess:</b> not updated - core markers missing in the template<|>';
        return;
    }
    $coreFromTemplate = substr($template, $tStart, $tEnd - $tStart + strlen($endCoreMarker));

    $hasCore = str_contains($current, $startMarker) && str_contains($current, $endCoreMarker);
    $notice = '';

    if ($hasCore) {
        // Replace only the existing core block
        $cStart = strpos($current, $startMarker);
        $cEnd   = strpos($current, $endCoreMarker) + strlen($endCoreMarker);
        $coreFromCurrent = substr($current, $cStart, $cEnd - $cStart);

        $newContent = substr_replace($current, $coreFromTemplate, $cStart, $cEnd - $cStart);

        // files from before the "before" section existed get it in front of the core block
        if (!str_contains($newContent, $beforeMarker)) {
            $bStart = strpos($template, $beforeMarker);
            $bEnd = strpos($template, $beforeEndMarker);
            if ($bStart !== false && $bEnd !== false) {
                $beforeSection = substr($template, $bStart, $bEnd - $bStart + strlen($beforeEndMarker))."\n\n";
                $newContent = substr_replace($newContent, $beforeSection, $cStart, 0);
            }
        }

        // a core block that differs from the new one may hold own rules -
        // they are lost in the new file, but not in the backup
        if (se_normalize_htaccess($coreFromCurrent) !== se_normalize_htaccess($coreFromTemplate)) {
            $notice = 'The SwiftyEdit core block of public/.htaccess has been replaced with the new version. If you had added own rules inside that block, they are no longer active - copy them from the backup {BACKUP} into the section "User rules before SwiftyEdit" at the top of the file.';
        }
    } elseif (trim($current) === '') {
        // no file yet - the template as it is
        $newContent = $template;
    } else {
        // file without SwiftyEdit markers (e.g. written by hand) - SwiftyEdit
        // needs its rules, so the template replaces it, the old file stays in the backup
        $newContent = $template;
        $notice = 'public/.htaccess had no SwiftyEdit markers and has been replaced with the template. Your previous file is saved as {BACKUP} - move own rules from it into the section "User rules before SwiftyEdit" at the top of the new file.';
    }

    if ($newContent === $current) {
        $_SESSION['protocol'] .= '<b>.htaccess:</b> unchanged<|>';
        return;
    }

    // keep the previous version - outside the web root
    $backupName = '';
    if (trim($current) !== '') {
        $backupDir = SE_CONTENT . '/backups';
        if (!is_dir($backupDir)) {
            @mkdir($backupDir, 0755, true);
        }
        $backupName = 'htaccess-' . date('Y-m-d-His') . '.txt';
        if (@file_put_contents($backupDir . '/' . $backupName, $current) === false) {
            $backupName = '';
        }
    }

    if ($current !== '' && $backupName === '') {
        // without a backup, own rules could get lost - leave the file alone
        $_SESSION['protocol'] .= '<b class="text-danger">.htaccess:</b> not updated - backup could not be written<|>';
        $_SESSION['update_notices'][] = 'public/.htaccess was not updated because no backup could be written to data/backups/. Check the permissions, or compare the file with install/contents/_htaccess.txt and apply the changes yourself.';
        return;
    }

    file_put_contents($htaccessFile, $newContent);

    $backupPath = 'data/backups/' . $backupName;
    $_SESSION['protocol'] .= '<b class="text-success">.htaccess:</b> updated'.($backupName !== '' ? ', previous version saved as '.htmlspecialchars($backupPath, ENT_QUOTES) : '').'<|>';
    if ($notice !== '') {
        $_SESSION['update_notices'][] = str_replace('{BACKUP}', $backupPath, $notice);
    }
}

/**
 * .htaccess content for comparing - line endings and trailing spaces don't count
 */
function se_normalize_htaccess(string $content): string
{
    $lines = preg_split('/\r\n|\r|\n/', $content);
    return trim(implode("\n", array_map('rtrim', $lines)));
}

// se_helper_fill_uuids() moved to install/php/functions.php (2026_08_17_000002_uuid_backfill
// migration now owns this logic; the file is required by data-writer.php further up).