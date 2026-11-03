<?php

/**
 * Includes the PHPStan baseline (tracked in git, see phpstan.neon).
 * PHPSTAN_NO_BASELINE=1 skips it, e.g. to count all findings (composer analyse:count).
 */

$baseline = __DIR__ . '/phpstan-baseline.neon';

if (getenv('PHPSTAN_NO_BASELINE')) {
    return [];
}

return is_file($baseline) ? ['includes' => [$baseline]] : [];
