<?php

/**
 * Normalize a folder of the media library (relative to public/) and confine it
 * to the media roots ($img_path / $files_path, i.e. assets/images and
 * assets/files). Everything else in public/ (themes, editors, branding, ...)
 * is not part of the media library and must not be selected, created in or
 * deleted from here.
 *
 * @param string $dir e.g. "assets/images/gallery"
 * @param bool $allow_root whether the media root folders themselves are accepted
 * @return string|false normalized relative path, false if outside the media roots
 */
function se_media_folder(string $dir, bool $allow_root = true): string|false {

    global $img_path, $files_path;

    $resolved = se_resolve_within(SE_PUBLIC, $dir);
    if ($resolved === false || $resolved === SE_PUBLIC) {
        return false;
    }
    $relative = substr($resolved, strlen(SE_PUBLIC) + 1);

    foreach ([$img_path, $files_path] as $media_root) {
        if ($relative === $media_root) {
            return $allow_root ? $relative : false;
        }
        if (str_starts_with($relative, $media_root.'/')) {
            return $relative;
        }
    }

    return false;
}

/**
 * the folder selected in the media library, the images root by default
 *
 * @return string e.g. "assets/images" or "assets/files/manuals"
 */
function se_media_current_folder(): string {

    global $img_path;

    $folder = se_media_folder((string) ($_SESSION['disk'] ?? ''));
    return ($folder !== false) ? $folder : $img_path;
}

/**
 * Delete a subfolder of the media library with its content
 * and the database entries of the deleted files
 *
 * @param string $dir e.g. "assets/images/gallery"
 * @return bool
 */
function delete_folder(string $dir): bool {

    // only subfolders of the media roots, never the roots themselves
    $dir = se_media_folder(se_filter_filepath($dir), false);
    if ($dir === false) {
        return false;
    }

    $delete_folder = SE_PUBLIC.'/'.$dir;

    // a symlink is removed itself - never follow it and delete its target
    if (is_link($delete_folder)) {
        return unlink($delete_folder);
    }
    if (!is_dir($delete_folder)) {
        return false;
    }

    $files = array_diff(scandir($delete_folder) ?: [],array('.','..'));
    foreach ($files as $file) {
        $path = $delete_folder.'/'.$file;
        if(is_dir($path) && !is_link($path)) {
            delete_folder("$dir/$file");
        } else {
            unlink($path);
            // media DB paths are stored as "../images/..." / "../files/..."
            $filename = str_replace('assets/', '../', "$dir/$file");
            se_delete_media_data("$filename");
        }

    }
    return rmdir($delete_folder);
}
