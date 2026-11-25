<?php

// Prevent script from being called directly
defined('TYPO3') or die();

// encapsulate all locally defined variables
(static function() {
    // Plugin-Registrierung: der eigene CType entsteht durch configurePlugin()
    // in ext_localconf.php (dort PLUGIN_TYPE_CONTENT_ELEMENT).
    \TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
        'cbgooglemaps',
        'Quickgooglemap',
        'Quick map integration',
        'EXT:cbgooglemaps/Resources/Public/Icons/ce_wiz.svg'
    );

    // TYPO3 14: subtypes_addlist/subtypes_excludelist auf types['list'] sind entfernt.
    // Plugin hat eigenen CType → showitem direkt am CType + FlexForm-DS via
    // columnsOverrides am CType.
    $GLOBALS['TCA']['tt_content']['types']['cbgooglemaps_quickgooglemap']['showitem'] = str_replace(
        '--palette--;;headers,',
        '--palette--;;headers, pi_flexform,',
        $GLOBALS['TCA']['tt_content']['types']['cbgooglemaps_quickgooglemap']['showitem'] ?? ''
    );

    // FlexForm-Datenstruktur - je Version unterschiedlich registriert:
    // - TYPO3 14: 'ds' als String (Single-Entry) in den columnsOverrides des CType.
    // - TYPO3 <= 13.4: die FormEngine loest den dataStructureKey gegen die GLOBALE
    //   ds-Liste von tt_content.pi_flexform auf (Muster '*,<CType>'). Dort traegt
    //   addPiFlexFormValue() die Datei ein - ein 'ds' in den columnsOverrides wird
    //   in 13.4 ignoriert und stattdessen die Core-Datenstruktur geladen.
    $flexFormFile = 'FILE:EXT:cbgooglemaps/Configuration/FlexForms/flexform_quickgooglemap.xml';
    $flexFormLabel = 'LLL:EXT:cbgooglemaps/Resources/Private/Language/locallang.xlf:label.sheetLocation';
    $typo3MajorVersion = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(
        \TYPO3\CMS\Core\Information\Typo3Version::class
    )->getMajorVersion();

    if ($typo3MajorVersion >= 14) {
        $GLOBALS['TCA']['tt_content']['types']['cbgooglemaps_quickgooglemap']['columnsOverrides']['pi_flexform'] = [
            'label' => $flexFormLabel,
            'config' => [
                'ds' => $flexFormFile,
            ],
        ];
    } else {
        \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPiFlexFormValue(
            '*',
            $flexFormFile,
            'cbgooglemaps_quickgooglemap'
        );
        $GLOBALS['TCA']['tt_content']['types']['cbgooglemaps_quickgooglemap']['columnsOverrides']['pi_flexform'] = [
            'label' => $flexFormLabel,
        ];
    }
})();
