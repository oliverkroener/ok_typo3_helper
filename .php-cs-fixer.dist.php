<?php

/*
 * PHP-CS-Fixer configuration for the ok_typo3_helper extension.
 *
 * Uses the official TYPO3 coding standards preset.
 * Run with: composer run cs:check  (dry-run) or composer run cs:fix (apply)
 */

if (PHP_SAPI !== 'cli') {
    die('This script supports command line usage only. Please check your command.');
}

$config = \TYPO3\CodingStandards\CsFixerConfig::create();
$config->getFinder()
    ->in(__DIR__ . '/Classes')
    ->name('*.php');

return $config;
