<?php

/**
 * Includes the local PHPStan baseline if it exists.
 * The baseline is not tracked in git, so a fresh checkout runs without it.
 */

$baseline = __DIR__ . '/phpstan-baseline.neon';

return is_file($baseline) ? ['includes' => [$baseline]] : [];
