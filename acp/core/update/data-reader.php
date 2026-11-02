<?php

/**
 * SwiftyEdit /admin-xhr/update/read/
 * versions of the update channels with the notices of swiftyedit.net, and the downloaded releases
 *
 * global variables
 * @var object $twig
 */

require_once __DIR__.'/functions.php';

$action = $_GET['action'] ?? '';

// the channels of swiftyedit.net, the notices of the same versions.json are swapped into their own area
if($action == 'read_versions') {

    $remote_versions = get_remote_versions();
    $channels = se_update_channels($remote_versions);

    $stable_available = false;
    foreach($channels ?? [] as $channel) {
        if($channel['name'] == 'stable' && $channel['available']) {
            $stable_available = true;
        }
    }

    echo $twig->render('update/channels.twig', [
        'channels' => $channels,
        'stable_available' => $stable_available,
        'notices' => se_update_notices($remote_versions),
        'writer_uri' => '/admin-xhr/update/write/'
    ]);
    exit;
}

// downloaded releases that can be installed
if($action == 'check_download') {

    $downloads = [];
    $extract_dir = __DIR__.'/download/extract';
    if(is_dir($extract_dir)) {
        foreach(scandir($extract_dir) ?: [] as $download) {
            if(!str_starts_with($download, '.')) {
                $downloads[] = $download;
            }
        }
    }

    echo $twig->render('update/downloads.twig', [
        'downloads' => $downloads,
        'writer_uri' => '/admin-xhr/update/write/'
    ]);
    exit;
}
