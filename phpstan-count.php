<?php

/**
 * Summarizes PHPStan's JSON output by error identifier.
 * Used by "composer analyse:count", which pipes the output of
 * "phpstan analyse --error-format=json" (without baseline) into this script.
 */

$result = json_decode(stream_get_contents(STDIN), true);

if (!is_array($result) || !isset($result['files'])) {
    fwrite(STDERR, "No PHPStan JSON output received.\n");
    exit(1);
}

$counts = [];
foreach ($result['files'] as $file) {
    foreach ($file['messages'] as $message) {
        $identifier = $message['identifier'] ?? '(no identifier)';
        $counts[$identifier] = ($counts[$identifier] ?? 0) + 1;
    }
}

arsort($counts);

foreach ($counts as $identifier => $count) {
    printf("%6d %s\n", $count, $identifier);
}

echo "------\n";
printf("%6d total (%d files)\n", array_sum($counts), count($result['files']));

// errors that are not bound to a file, e.g. configuration problems
foreach ($result['errors'] as $error) {
    fwrite(STDERR, "Error: $error\n");
}
