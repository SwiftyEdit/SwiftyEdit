<?php

function show_sysdocs_index() {
    global $Parsedown, $doc_filepath, $languagePackFallback;
    $languagePack = $_SESSION['lang'];

    $docs_root = '../docs/v2/en/*.md';
    if (is_dir('../docs/v2/'.$languagePack)) {
        $docs_root = '../docs/v2/'.$languagePack;
    } else if(is_dir('../docs/v2/'.$languagePackFallback)) {
        $docs_root = '../docs/v2/'.$languagePackFallback;
    }
    $docsfiles = glob($docs_root.'/*.md');

    $parsed_files = [];
    foreach ($docsfiles as $doc) {
        if (str_starts_with(basename($doc), 'tip-')) continue;

        $parsed_file = se_parse_docs_file($doc);
        $parsed_files[] = [
            "title" => $parsed_file['header']['title'],
            "priority" => $parsed_file['header']['priority'],
            "btn" => $parsed_file['header']['btn'],
            "file" => basename($doc),
            "fullpath" => $doc,
            "level" => get_doc_level_universal(basename($doc))
        ];
    }

    usort($parsed_files, function($a, $b) {
        return strnatcmp($a['file'], $b['file']);
    });

    $list = '<div class="card mb-3">';
    $list .= '<div class="card-header"><h6>SwiftyEdit</h6></div>';
    $list .= '<div class="list-group list-group-flush">';

    foreach ($parsed_files as $item) {
        $active = str_ends_with($doc_filepath, $item['fullpath']) ? 'active' : '';
        $hx_get = '/admin-xhr/docs/read/?file=' . $item['fullpath'];

        $class = 'list-group-level-'.$item['level'];

        $list .= '<button class="list-group-item list-group-item-action '.$class.' ' . $active . '" 
                  hx-get="' . $hx_get . '" hx-target="#helpModal"
                  title="' . $item['title'] . '">';
        $list .= $item['btn'];
        $list .= '</button>';
    }

    $list .= '</div>';
    $list .= '</div>';

    return $list;
}

function get_doc_level_universal($filename) {
    preg_match_all('/(\d{2})-/', $filename, $matches);

    if (empty($matches[1])) {
        return 0;
    }

    $blocks = $matches[1];
    $level = 0;

    // Count non-zero blocks after the first
    for ($i = 1; $i < count($blocks); $i++) {
        if ((int)$blocks[$i] > 0) {
            $level++;
        }
    }

    return $level;
}




/**
 * Resolve a theme or plugin docs path to its context and the file on disk.
 *
 * Accepted forms (leading slash optional, absolute filesystem paths work too):
 *   /themes/{theme}/readme.md          -> {theme}/readme.md
 *   /themes/{theme}/{file}.md          -> {theme}/docs/{lang}/{file}.md
 *   /plugins/{plugin}/readme.md        -> {plugin}/readme.md
 *   /plugins/{plugin}/{file}.md        -> {plugin}/docs/{lang}/{file}.md
 *   .../{theme|plugin}/docs/{lang}/{file}.md is treated like /{type}/{name}/{file}.md
 *
 * Returns null for anything else (= core docs).
 *
 * @param string $path already filtered with se_filter_filepath()
 * @return array|null ['type' => 'theme'|'plugin', 'name', 'root', 'base', 'file']
 */
function se_docs_resolve_context(string $path): ?array {

    if(!preg_match('#(?:^|/)(themes|plugins)/([A-Za-z0-9_-][A-Za-z0-9_.-]*)/(?:docs/[A-Za-z_-]+/)?([A-Za-z0-9_-][A-Za-z0-9_.-]*\.md)$#', $path, $m)) {
        return null;
    }

    $type = ($m[1] == 'themes') ? 'theme' : 'plugin';
    $name = $m[2];
    $file = $m[3];
    $root = ($type == 'theme') ? SE_THEMES.'/'.$name : SE_PLUGINS.'/'.$name;

    if(!is_dir($root)) {
        return null;
    }

    if($file == 'readme.md') {
        $filepath = $root.'/readme.md';
    } else {
        $docs_dir = se_docs_context_lang_dir($root);
        $filepath = $root.'/docs/'.$docs_dir.'/'.$file;
        if($docs_dir !== '' && !is_file($filepath)) {
            // the file may exist in another language only
            foreach(se_docs_context_lang_order($root) as $l) {
                if(is_file($root.'/docs/'.$l.'/'.$file)) {
                    $filepath = $root.'/docs/'.$l.'/'.$file;
                    break;
                }
            }
        }
    }

    return [
        'type' => $type,
        'name' => $name,
        'root' => $root,
        'base' => '/'.$m[1].'/'.$name.'/',
        'file' => $filepath
    ];
}

/**
 * Allowed theme/plugin folder and docs file names (matches se_docs_resolve_context()):
 * letters, digits, "_", "-" and ".", not starting with a dot.
 *
 * @param string $name
 * @return bool
 */
function se_docs_valid_name(string $name): bool {
    return preg_match('/^[A-Za-z0-9_-][A-Za-z0-9_.-]*$/', $name) === 1;
}

/**
 * Language folders of a theme/plugin docs directory in the order they
 * should be tried: backend language, fallback language, then any other.
 *
 * @param string $root theme or plugin directory
 * @return array
 */
function se_docs_context_lang_order(string $root): array {
    global $languagePack, $languagePackFallback;

    $available = [];
    foreach(glob($root.'/docs/*', GLOB_ONLYDIR) ?: [] as $dir) {
        $available[] = basename($dir);
    }
    sort($available);

    $order = [];
    foreach([$languagePack, $languagePackFallback, 'en'] as $l) {
        if($l != '' && in_array($l, $available) && !in_array($l, $order)) {
            $order[] = $l;
        }
    }
    foreach($available as $l) {
        if(!in_array($l, $order)) {
            $order[] = $l;
        }
    }
    return $order;
}

/**
 * The docs language folder used for a theme/plugin ('' if it has no docs).
 *
 * @param string $root theme or plugin directory
 * @return string
 */
function se_docs_context_lang_dir(string $root): string {
    foreach(se_docs_context_lang_order($root) as $l) {
        if(glob($root.'/docs/'.$l.'/*.md')) {
            return $l;
        }
    }
    return '';
}

/**
 * Read only the front matter of a docs file (no markdown parsing).
 *
 * @param string $file
 * @return array
 */
function se_docs_read_header(string $file): array {
    $src = (string) @file_get_contents($file);
    if(!str_starts_with($src, '---')) {
        return [];
    }
    $parts = explode('---', $src, 3);
    try {
        $header = \Symfony\Component\Yaml\Yaml::parse($parts[1] ?? '');
    } catch (\Symfony\Component\Yaml\Exception\ParseException $e) {
        return [];
    }
    return is_array($header) ? $header : [];
}

/**
 * All docs files of a theme/plugin, sorted by front matter priority.
 * docs/{lang}/*.md come first, readme.md (if present) is appended last.
 *
 * @param string $type 'theme' or 'plugin'
 * @param string $name theme or plugin folder name
 * @return array list of ['path', 'fullpath', 'btn', 'title', 'priority']
 */
function se_docs_context_files(string $type, string $name): array {

    // same name rules as se_docs_resolve_context(), anything else could not be opened anyway
    if(!se_docs_valid_name($name)) {
        return [];
    }

    $root =($type == 'theme') ? SE_THEMES.'/'.$name : SE_PLUGINS.'/'.$name;
    $base = '/'.($type == 'theme' ? 'themes' : 'plugins').'/'.$name.'/';

    $items = [];
    $docs_dir = se_docs_context_lang_dir($root);
    if($docs_dir !== '') {
        foreach(glob($root.'/docs/'.$docs_dir.'/*.md') as $doc) {
            $filename = basename($doc);
            if(str_starts_with($filename, 'tip-')) continue;
            if(!se_docs_valid_name($filename)) continue;

            $header = se_docs_read_header($doc);
            $items[] = [
                'path' => $base.$filename,
                'fullpath' => $doc,
                'btn' => $header['btn'] ?? ($header['title'] ?? $filename),
                'title' => $header['title'] ?? '',
                'priority' => (int) ($header['priority'] ?? 1000)
            ];
        }
    }

    usort($items, function($a, $b) {
        return [$a['priority'], $a['path']] <=> [$b['priority'], $b['path']];
    });

    if(is_file($root.'/readme.md')) {
        $items[] = [
            'path' => $base.'readme.md',
            'fullpath' => $root.'/readme.md',
            'btn' => 'readme.md',
            'title' => '',
            'priority' => PHP_INT_MAX
        ];
    }

    return $items;
}

/**
 * Navigation shown in the help modal while a theme or plugin doc is open:
 * only the docs of that theme/plugin plus a link back to the core docs.
 *
 * @param array $context result of se_docs_resolve_context()
 * @return string
 */
function show_context_docs_index(array $context): string {
    global $doc_filepath, $icon;

    $label = ($context['type'] == 'theme') ? 'Theme' : 'Plugin';

    $list = '<div class="card mb-3">';
    $list .= '<div class="card-header"><h6>'.$label.': '.htmlspecialchars($context['name']).'</h6></div>';
    $list .= '<div class="list-group list-group-flush">';

    foreach(se_docs_context_files($context['type'], $context['name']) as $item) {
        $active = ($doc_filepath == $item['fullpath']) ? 'active' : '';
        $hx_get = htmlspecialchars('/admin-xhr/docs/read/?file='.$item['path'], ENT_QUOTES);

        $list .= '<button class="list-group-item list-group-item-action '.$active.'"
                  hx-get="'.$hx_get.'" hx-target="#helpModal"
                  title="'.htmlspecialchars($item['title']).'">';
        $list .= htmlspecialchars($item['btn']);
        $list .= '</button>';
    }

    $list .= '</div>';
    $list .= '</div>';

    $list .= '<button class="btn btn-default w-100" hx-get="/admin-xhr/docs/read/?file=start" hx-target="#helpModal">';
    $list .= $icon['arrow_left'].' SwiftyEdit';
    $list .= '</button>';

    return $list;
}

/**
 * Overview list of themes or plugins that ship documentation.
 * Each entry opens the first doc of that theme/plugin (by priority).
 *
 * @param string $type 'theme' or 'plugin'
 * @return string
 */
function show_context_overview_index(string $type): string {

    if($type == 'theme') {
        $names = get_all_templates();
        $heading = 'Themes';
    } else {
        $names = array_keys(se_get_all_addons());
        $heading = 'Plugins';
    }

    $list = '<div class="card mb-3">';
    $list .= '<div class="card-header"><h6>'.$heading.'</h6></div>';
    $list .= '<div class="list-group list-group-flush">';

    foreach($names as $name) {
        $name = basename($name);
        $files = se_docs_context_files($type, $name);
        if(empty($files)) {
            continue;
        }

        $hx_get = htmlspecialchars('/admin-xhr/docs/read/?file='.$files[0]['path'], ENT_QUOTES);

        $list .= '<button class="list-group-item list-group-item-action" hx-get="'.$hx_get.'" hx-target="#helpModal">';
        $list .= htmlspecialchars($name);
        $list .= '</button>';
    }

    $list .= '</div>';
    $list .= '</div>';

    return $list;
}