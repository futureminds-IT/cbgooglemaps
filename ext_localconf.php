<?php
defined('TYPO3_MODE') or die();

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
    'Brinkert.cbgooglemaps',
    'Quickgooglemap',

    [
        'Map' => 'index',
    ],

    // non-cacheable actions
    []
);
