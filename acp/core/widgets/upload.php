<?php
session_start();
error_reporting(0);
require '../vendor/autoload.php';
use Medoo\Medoo;

if($_SESSION['user_class'] != "administrator"){
    header("location:../index.php");
    die("PERMISSION DENIED!");
}

require '../config.php';
if(is_file('../'.SE_CONTENT.'/config.php')) {
    include '../'.SE_CONTENT.'/config.php';
}

// only show errors when explicitly running in development mode
if ($se_environment === 'd') {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
}

if(is_file('../config_database.php')) {
    include '../config_database.php';
    $db_type = 'mysql';

    $database = new Medoo([
        'type' => 'mysql',
        'database' => "$database_name",
        'host' => "$database_host",
        'username' => "$database_user",
        'password' => "$database_psw",
        'charset' => 'utf8',
        'port' => $database_port,
        'prefix' => DB_PREFIX
    ]);

    $db_content = $database;
    $db_user = $database;
    $db_statistics = $database;

} else {
    $db_type = 'sqlite';

    define("CONTENT_DB", "$se_db_content");

    $db_content = new Medoo([
        'type' => 'sqlite',
        'database' => CONTENT_DB
    ]);
}


require '../app/functions/functions.php';


if($_POST['csrf_token'] !== $_SESSION['token']) {
    die('Error: CSRF Token is invalid');
}

$time = time();

/* branding uploads: page logo / page thumbnail / favicon source.
 * Fixed filenames in their own folder, no se_media entry - see
 * acp/core/settings/branding-widget.php for the field markup this responds to. */
$branding_target = $_POST['branding_target'] ?? '';
if (in_array($branding_target, ['logo', 'thumbnail', 'favicon'], true)) {
    require_once '../acp/core/settings/branding-widget.php';

    $result = se_handle_branding_upload(
        $branding_target,
        $se_branding_path,
        $se_upload_img_types,
        (int) ($_POST['w'] ?? 0),
        (int) ($_POST['h'] ?? 0),
        $_POST
    );

    if (isset($result['error'])) {
        http_response_code(422);
        exit;
    }

    // only the preview fragment is returned - the Uppy dropzone that triggered this
    // upload (see backend.js) stays mounted and replaces #branding-preview-{target} itself.
    // remove_label/confirm_text are translated strings threaded through as upload
    // meta (set server-side in general.php, which has $lang - this bare endpoint doesn't)
    echo se_render_branding_preview(
        $branding_target,
        $result['filename'],
        $se_branding_path,
        '/admin-xhr/settings/general/write/',
        $_POST['remove_label'] ?? 'Remove',
        $_POST['confirm_text'] ?? 'Are you sure you want to delete this file?'
    );
    exit;
}

$max_w = (int) $_POST['w']; // max image width
$max_h = (int) $_POST['h']; // max image height
$max_w_tmb = (int) $_POST['w_tmb']; // max thumbnail width
$max_h_tmb = (int) $_POST['h_tmb']; // max thumbnail height
$max_fz = (int) $_POST['fz']; // max filesize

if($max_w_tmb < 1) {
    $max_w_tmb = 250;
}

if($max_h_tmb < 1) {
    $max_h_tmb = 250;
}

if(str_contains($_POST['upload_destination'], "/images")) {
    $destination = se_filter_filepath($_POST['upload_destination']);
    $upload_type = 'images';
} else if(str_contains($_POST['upload_destination'], "/files")) {
    $destination = se_filter_filepath($_POST['upload_destination']);
    $upload_type = 'files';
}

/* thumbnail directories */
$tmb_dir = $img_tmb_path;
$tmb_dir_year = $tmb_dir.'/'.date('Y',time());
$tmb_destination = $tmb_dir_year.'/'.date('m',time());
if(!is_dir($tmb_dir_year)) {
    mkdir($tmb_dir_year);
}
if(!is_dir($tmb_destination)) {
    mkdir($tmb_destination);
}


// upload images to assets/images/
if($upload_type == 'images') {
    if(array_key_exists('file',$_FILES) && $_FILES['file']['error'] == 0 ){
        $tmp_name = $_FILES['file']['tmp_name'];
        $org_name = $_FILES['file']['name'];
        $suffix = substr(strrchr($org_name,'.'),1);
        $prefix = basename($org_name,".$suffix");
        $img_name = generate_filename($prefix,$suffix);
        $target = "$destination/$img_name";

        //$se_upload_img_types from config.php
        if(!in_array($suffix, $se_upload_img_types)) {
            exit;
        } else {

            if($_POST['unchanged'] == 'yes' OR $suffix == 'svg') {
                @move_uploaded_file($tmp_name, $target);
            } else {
                resize_image($tmp_name,$target,$max_w,$max_h,100);
                $tmb_name = md5(substr($target, 3,strlen($target))).'.jpg';
                $store_tmb_name = $tmb_destination.'/'.$tmb_name;
                se_create_tmb($target,$tmb_name,$max_w_tmb,$max_h_tmb,80);
            }

            $filetype = mime_content_type(realpath($target));
            $filesize = filesize(realpath($target));
            if($_POST['file_mode'] !== 'overwrite') {
                se_write_media_data_name($target,$store_tmb_name,$filesize,$time,$filetype);
            }

            $data = ['url' => $target, 'message' => 'The file ' . $target . ' has been uploaded.'];
            echo json_encode($data);
        }
    }
}


/* upload files to /content/files/ */
if($upload_type == 'files') {
    if(array_key_exists('file',$_FILES) && $_FILES['file']['error'] == 0 ){
        $tmp_name = $_FILES["file"]["tmp_name"];
        $org_name = $_FILES["file"]["name"];
        $suffix = substr(strrchr($org_name,'.'),1);
        $prefix = basename($org_name,".$suffix");
        $files_name = generate_filename($prefix,$suffix);
        $target = "$destination/$files_name";

        $se_upload_types = array_merge($se_upload_img_types,$se_upload_file_types);
        if(!in_array($suffix, $se_upload_types)) {
            exit;
        } else {
            @move_uploaded_file($tmp_name, $target);
            $filetype = mime_content_type(realpath($target));
            $filesize = filesize(realpath($target));
            if($_POST['file_mode'] != 'overwrite') {
                se_write_media_data_name($target,'',$filesize,$time,$filetype);
            }
        }

        $data = ['url' => $target, 'message' => 'The file ' . $files_name . ' has been uploaded.'];
        echo json_encode($data);

    }
}

// gallery upload
if((isset($_POST['gal'])) && is_numeric($_POST['gal'])) {
    $year = (int) $_REQUEST['post_year'];
    $gallery_id = 'gallery'. (int) $_POST['gal'];
    $uploads_dir = SE_PUBLIC.'/assets/galleries/'.$year.'/'.$gallery_id;
    $max_width = (int) $_REQUEST['w']; // max image width
    $max_height = (int) $_REQUEST['h']; // max image height
    $max_width_tmb = (int) $_REQUEST['w_tmb']; // max thumbnail width
    $max_height_tmb = (int) $_REQUEST['h_tmb']; // max thumbnail height
    if(!is_dir($uploads_dir)) {
        mkdir($uploads_dir, 0777, true);
    }

    if(array_key_exists('file',$_FILES) && $_FILES['file']['error'] == 0 ){

        $tmp_name = $_FILES["file"]["tmp_name"];
        $timestring = microtime(true);
        $random_int = random_int(0, 999);

        $suffix = substr(strrchr($_FILES["file"]["name"],"."),1);
        $org_name = $timestring .'.'. $suffix;
        $img_name = $timestring.$random_int."_img.jpg";
        $tmb_name = $timestring.$random_int."_tmb.jpg";

        if(!in_array($suffix, $se_upload_img_types)) {
            exit;
        } else {

            if(move_uploaded_file($tmp_name, "$uploads_dir/$org_name")) {
                se_create_gallery_thumbs($uploads_dir,$org_name,$img_name, $max_width,$max_height,90);
                se_create_gallery_thumbs($uploads_dir,$img_name,$tmb_name, $max_width_tmb,$max_height_tmb,80);
                unlink("$uploads_dir/$org_name");
            }
            $data = ['url' => $uploads_dir, 'message' => 'Gallery: #'.$_POST['gal'].' The files has been uploaded.'];
            echo json_encode($data);
        }

    }

}


function resize_image($img, $name, $thumbnail_width, $thumbnail_height, $quality){

    $arr_image_details	= GetImageSize("$img");
    $original_width		= $arr_image_details[0];
    $original_height	= $arr_image_details[1];

    $a = $thumbnail_width / $thumbnail_height;
    $b = $original_width / $original_height;


    if($a<$b) {
        $new_width = $thumbnail_width;
        $new_height	= intval($original_height*$new_width/$original_width);
    } else {
        $new_height = $thumbnail_height;
        $new_width	= intval($original_width*$new_height/$original_height);
    }

    if(($original_width <= $thumbnail_width) AND ($original_height <= $thumbnail_height)) {
        $new_width = $original_width;
        $new_height = $original_height;
    }



    if($arr_image_details[2]==1) { $imgt = "imagegif"; $imgcreatefrom = "imagecreatefromgif";  }
    if($arr_image_details[2]==2) { $imgt = "imagejpeg"; $imgcreatefrom = "imagecreatefromjpeg";  }
    if($arr_image_details[2]==3) { $imgt = "imagepng"; $imgcreatefrom = "imagecreatefrompng";  }
    if($arr_image_details[2]==18) { $imgt = "imagewebp"; $imgcreatefrom = "imagecreatefromwebp";  }

    if($imgt == 'imagejpeg') {
        $old_image	= $imgcreatefrom("$img");
        $new_image	= imagecreatetruecolor($new_width, $new_height);
        imagecopyresampled($new_image,$old_image,0,0,0,0,$new_width,$new_height,$original_width,$original_height);
        imagejpeg($new_image,"$name",$quality);
        imagedestroy($new_image);
    }

    if($imgt == 'imagewebp') {
        $old_image	= $imgcreatefrom("$img");
        $new_image	= imagecreatetruecolor($new_width, $new_height);
        imagealphablending($new_image, false);
        imagesavealpha($new_image, true);
        imagecopyresampled($new_image,$old_image,0,0,0,0,$new_width,$new_height,$original_width,$original_height);
        imagewebp($new_image,"$name",$quality);
        imagedestroy($new_image);
    }

    if($imgt == 'imagepng') {
        $old_image	= $imgcreatefrom("$img");
        $new_image	= imagecreatetruecolor($new_width, $new_height);
        imagealphablending($new_image, false);
        imagesavealpha($new_image, true);
        $transparency = imagecolorallocatealpha($new_image, 255, 255, 255, 127);
        imagefilledrectangle($new_image, 0, 0, $new_width, $new_height, $transparency);
        imagecopyresampled($new_image,$old_image,0,0,0,0,$new_width,$new_height,$original_width,$original_height);
        imagepng($new_image,"$name",0);
    }

    if($imgt == 'imagegif') {
        return $name;
    }

}


function increment_prefix($cnt,$target) {

    $nbr = $cnt+1;
    $path = pathinfo($target);
    $filepath = $path['dirname'];
    $filename = $path['filename'];
    $extension = $path['extension'];

    if(substr("$filename", -2,1) == '_' AND is_numeric(substr("$filename", -1))) {
        $filename_without_nbr = substr("$filename", 0,-2);
        $new_filename = $filename_without_nbr.'_'.$nbr;
        $new_target = "$filepath/$new_filename.$extension";

        if(is_file("$new_target")) {
            $nbr = increment_prefix($nbr,$new_target);
        }

    } else {
        $new_target = "$filepath/$filename"."_$nbr.".$extension;
        if(is_file("$new_target")) {
            $nbr = increment_prefix($nbr,$new_target);
        }
    }
    return $nbr;
}


function generate_filename($prefix,$suffix) {

    global $destination;
    $prefix = strtolower($prefix);

    $a = array('ä','ö','ü','ß',' - ',' + ','_',' / ','/');
    $b = array('ae','oe','ue','ss','-','-','_','-','-');
    $prefix = str_replace($a, $b, $prefix);
    $prefix = preg_replace('/\s/s', '_', $prefix);  // replace blanks -> '_'
    $prefix = preg_replace('/[^a-z0-9_-]/isU', '', $prefix); // only a-z 0-9
    $prefix = trim($prefix);

    $target = "$destination/$prefix.$suffix";

    if((is_file($target) && $_POST['file_mode'] != 'overwrite')) {
        $prefix = $prefix . '_' . increment_prefix('0',"$target");
    }


    $filename = $prefix . '.' . $suffix;
    $filename = strtolower($filename);

    return $filename;
}





function se_write_media_data_name($filename,$store_tmb_name,$filesize,$time,$mediatype) {

    global $db_content;
    global $languagePack;

    $filename = str_replace("assets/","../",$filename);
    $store_tmb_name = str_replace("assets/","../",$store_tmb_name);
    $uploader = $_SESSION['user_nick'];

    $columns = [
        "media_file" => "$filename",
        "media_thumb" => "$store_tmb_name",
        "media_filesize" => "$filesize",
        "media_lastedit" => "$time",
        "media_upload_time" => "$time",
        "media_upload_from" => "$uploader",
        "media_type" => "$mediatype",
        "media_lang" => $_SESSION['lang']
    ];

    $cnt_changes = $db_content->insert("se_media", $columns);
}



function se_create_tmb($img_src, $tmb_name, $tmb_width, $tmb_height, $tmb_quality) {

    global $tmb_destination;

    $arr_image_details	= GetImageSize("$img_src");
    $original_width		= $arr_image_details[0];
    $original_height	= $arr_image_details[1];
    $a = $tmb_width / $tmb_height;
    $b = $original_width / $original_height;


    if ($a<$b) {
        $new_width = $tmb_width;
        $new_height	= intval($original_height*$new_width/$original_width);
    } else {
        $new_height = $tmb_height;
        $new_width	= intval($original_width*$new_height/$original_height);
    }

    if(($original_width <= $tmb_width) AND ($original_height <= $tmb_height)) {
        $new_width = $original_width;
        $new_height = $original_height;
    }

    if($arr_image_details[2]==1) { $imgt = "imagegif"; $imgcreatefrom = "imagecreatefromgif";  }
    if($arr_image_details[2]==2) { $imgt = "imagejpeg"; $imgcreatefrom = "imagecreatefromjpeg";  }
    if($arr_image_details[2]==3) { $imgt = "imagepng"; $imgcreatefrom = "imagecreatefrompng";  }
    if($arr_image_details[2]==18) { $imgt = "imagewebp"; $imgcreatefrom = "imagecreatefromwebp";  }


    if($imgt) {
        $old_image	= $imgcreatefrom("$img_src");
        $new_image	= imagecreatetruecolor($new_width, $new_height);
        imagecopyresampled($new_image,$old_image,0,0,0,0,$new_width,$new_height,$original_width,$original_height);
        imagejpeg($new_image,"$tmb_destination/$tmb_name",$tmb_quality);
        imagedestroy($new_image);
    }

}

function se_create_gallery_thumbs($updir, $img, $name, $thumbnail_width, $thumbnail_height, $quality){
    $arr_image_details	= GetImageSize("$updir/$img");
    $original_width		= $arr_image_details[0];
    $original_height	= $arr_image_details[1];
    $a = $thumbnail_width / $thumbnail_height;
    $b = $original_width / $original_height;


    if ($a<$b) {
        $new_width = $thumbnail_width;
        $new_height	= intval($original_height*$new_width/$original_width);
    } else {
        $new_height = $thumbnail_height;
        $new_width	= intval($original_width*$new_height/$original_height);
    }

    if(($original_width <= $thumbnail_width) AND ($original_height <= $thumbnail_height)) {
        $new_width = $original_width;
        $new_height = $original_height;
    }
    if($arr_image_details[2]==1) { $imgt = "imagegif"; $imgcreatefrom = "imagecreatefromgif";  }
    if($arr_image_details[2]==2) { $imgt = "imagejpeg"; $imgcreatefrom = "imagecreatefromjpeg";  }
    if($arr_image_details[2]==3) { $imgt = "imagepng"; $imgcreatefrom = "imagecreatefrompng";  }
    if($imgt) {
        $old_image	= $imgcreatefrom("$updir/$img");
        $new_image	= imagecreatetruecolor($new_width, $new_height);
        imagecopyresampled($new_image,$old_image,0,0,0,0,$new_width,$new_height,$original_width,$original_height);
        imagejpeg($new_image,"$updir/$name",$quality);
        imagedestroy($new_image);
    }
}


/**
 * Handle an upload for one of the fixed branding slots (logo/thumbnail/favicon).
 * Stores the file under a fixed filename in $branding_path, updates the
 * matching setting directly (bypassing the general settings "Update" button),
 * and - for the favicon - generates the full icon set + web manifest.
 *
 * @return array{filename: string}|array{error: string}
 */
function se_handle_branding_upload(string $target, string $branding_path, array $allowed_types, int $max_w, int $max_h, array $post): array {

    if (!array_key_exists('file', $_FILES) || $_FILES['file']['error'] != 0) {
        return ['error' => 'no_file'];
    }

    $tmp_name = $_FILES['file']['tmp_name'];
    $org_name = $_FILES['file']['name'];
    $suffix = strtolower(substr(strrchr($org_name, '.'), 1));

    $target_types = $allowed_types;
    if ($target === 'favicon') {
        // the favicon set is generated with GD, which can only read raster formats
        $target_types = array_intersect($allowed_types, ['gif', 'jpg', 'jpe', 'jpeg', 'png', 'webp']);
    }

    if (!in_array($suffix, $target_types, true)) {
        return ['error' => 'unsupported_type'];
    }

    // validate the upload is actually a readable image *before* touching anything
    // already on disk, so a bad upload never wipes out a working file
    if ($suffix !== 'svg') {
        $details = @getimagesize($tmp_name);
        if ($details === false) {
            return ['error' => 'invalid_image'];
        }
    }

    if (!is_dir($branding_path)) {
        mkdir($branding_path, 0777, true);
    }

    $filename = "$target.$suffix";
    $file_target = "$branding_path/$filename";

    if ($target === 'favicon') {
        // reads from $tmp_name directly (does its own size/format validation) and
        // writes the derivative sizes in place - nothing old is touched until this succeeds
        $set_result = se_generate_favicon_set(
            $tmp_name,
            $branding_path,
            $post['manifest_name'] ?? '',
            $post['manifest_short_name'] ?? ''
        );

        if ($set_result !== true) {
            return ['error' => $set_result];
        }

        if (!move_uploaded_file($tmp_name, $file_target)) {
            return ['error' => 'upload_failed'];
        }
    } else if (($post['unchanged'] ?? '') == 'yes' || $suffix == 'svg') {
        if (!move_uploaded_file($tmp_name, $file_target)) {
            return ['error' => 'upload_failed'];
        }
    } else {
        resize_image($tmp_name, $file_target, $max_w ?: 1600, $max_h ?: 1600, 100);
    }

    // the new file is safely in place - now remove any leftover from a previous
    // upload that used a different file extension. favicon.ico is deliberately
    // excluded: it matches the "$target.*" glob too, but is (re)written by
    // se_generate_favicon_set() above and must not be treated as a stale leftover.
    foreach (glob("$branding_path/$target.*") as $old_file) {
        if ($old_file === $file_target) {
            continue;
        }
        if ($target === 'favicon' && basename($old_file) === 'favicon.ico') {
            continue;
        }
        @unlink($old_file);
    }

    se_write_branding_option('prefs_page' . $target, $filename);

    return ['filename' => $filename];
}


/**
 * Minimal insert-or-update for a single "se_options" row. Deliberately not
 * reusing se_write_option() from acp/core/functions.php: that file pulls in
 * a large chain of ACP-only includes this bare upload endpoint does not (and
 * should not need to) bootstrap.
 */
function se_write_branding_option(string $key, string $value): void {

    global $db_content;

    $entry = $db_content->get('se_options', '*', [
        'option_key' => $key,
        'option_module' => 'se'
    ]);

    if (!empty($entry['option_key'])) {
        $db_content->update('se_options', [
            'option_value' => $value
        ], [
            'AND' => [
                'option_key' => $key,
                'option_module' => 'se'
            ]
        ]);
    } else {
        $db_content->insert('se_options', [
            'option_value' => $value,
            'option_key' => $key,
            'option_module' => 'se'
        ]);
    }

    // keep se_get_preferences()'s cache file in sync - see se_write_option()
    // in acp/core/functions.php for the other write path that does the same
    se_build_preferences_cache();
}


/**
 * Generate the full favicon icon set (multiple PNG sizes + a PNG-based .ico
 * + a site.webmanifest) from one uploaded source image. The source is
 * center-cropped to a square first if it isn't already square.
 *
 * @return true|string true on success, otherwise an error code string
 */
function se_generate_favicon_set(string $source_path, string $branding_path, string $manifest_name, string $manifest_short_name) {

    $details = @getimagesize($source_path);
    if ($details === false) {
        return 'invalid_image';
    }

    [$width, $height, $type] = $details;

    if ($width < 512 || $height < 512) {
        return 'too_small';
    }

    $loaders = [
        IMAGETYPE_JPEG => 'imagecreatefromjpeg',
        IMAGETYPE_PNG => 'imagecreatefrompng',
        IMAGETYPE_GIF => 'imagecreatefromgif',
        IMAGETYPE_WEBP => 'imagecreatefromwebp'
    ];

    if (!isset($loaders[$type])) {
        return 'unsupported_format';
    }

    $source = $loaders[$type]($source_path);
    if (!$source) {
        return 'invalid_image';
    }

    // center-crop to square
    $crop_size = min($width, $height);
    $crop_x = intval(($width - $crop_size) / 2);
    $crop_y = intval(($height - $crop_size) / 2);

    $square = imagecreatetruecolor($crop_size, $crop_size);
    imagealphablending($square, false);
    imagesavealpha($square, true);
    $transparent = imagecolorallocatealpha($square, 0, 0, 0, 127);
    imagefilledrectangle($square, 0, 0, $crop_size, $crop_size, $transparent);
    imagecopyresampled($square, $source, 0, 0, $crop_x, $crop_y, $crop_size, $crop_size, $crop_size, $crop_size);
    imagedestroy($source);

    $sizes = [16, 32, 180, 192, 512];
    $ico_sizes = [16, 32];
    $ico_images = [];

    foreach ($sizes as $size) {
        $resized = imagecreatetruecolor($size, $size);
        imagealphablending($resized, false);
        imagesavealpha($resized, true);
        $transparent = imagecolorallocatealpha($resized, 0, 0, 0, 127);
        imagefilledrectangle($resized, 0, 0, $size, $size, $transparent);
        imagecopyresampled($resized, $square, 0, 0, 0, 0, $size, $size, $crop_size, $crop_size);

        imagepng($resized, "$branding_path/favicon-$size.png", 9);

        if (in_array($size, $ico_sizes, true)) {
            ob_start();
            imagepng($resized);
            $ico_images[] = ['size' => $size, 'data' => ob_get_clean()];
        }

        imagedestroy($resized);
    }

    imagedestroy($square);

    file_put_contents("$branding_path/favicon.ico", se_build_ico($ico_images));

    $manifest = [
        'name' => $manifest_name !== '' ? $manifest_name : 'Website',
        'short_name' => $manifest_short_name !== '' ? $manifest_short_name : ($manifest_name !== '' ? $manifest_name : 'Website'),
        'icons' => [
            ['src' => 'favicon-192.png', 'sizes' => '192x192', 'type' => 'image/png'],
            ['src' => 'favicon-512.png', 'sizes' => '512x512', 'type' => 'image/png']
        ],
        'theme_color' => '#ffffff',
        'background_color' => '#ffffff',
        'display' => 'standalone'
    ];

    file_put_contents("$branding_path/site.webmanifest", json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

    return true;
}


/**
 * Build a minimal .ico file wrapping one or more PNG images (a format
 * supported since Windows Vista and by all current browsers). Avoids
 * pulling in an image-manipulation library just for icon export.
 *
 * @param array<int, array{size:int, data:string}> $png_images
 */
function se_build_ico(array $png_images): string {

    $count = count($png_images);
    $header = pack('vvv', 0, 1, $count);

    $entries = '';
    $image_data = '';
    $offset = 6 + ($count * 16);

    foreach ($png_images as $img) {
        $dimension = $img['size'] >= 256 ? 0 : $img['size']; // 0 means 256px in the ICO format
        $bytes = strlen($img['data']);
        $entries .= pack('CCCCvvVV', $dimension, $dimension, 0, 0, 1, 32, $bytes, $offset);
        $image_data .= $img['data'];
        $offset += $bytes;
    }

    return $header . $entries . $image_data;
}