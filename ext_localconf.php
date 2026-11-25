<?php

// Prevent script from being called directly
defined('TYPO3') or die();

// encapsulate all locally defined variables
(static function() {
    // PageTS auto-load via Configuration/page.tsconfig
    
    \TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
        'cbgooglemaps',
        'Quickgooglemap',
        [
            \Brinkert\Cbgooglemaps\Controller\MapController::class => 'index',
        ],

        // non-cacheable actions
        [],

        // TYPO3 13.4: Default ist PLUGIN_TYPE_PLUGIN (list_type), erst mit
        // PLUGIN_TYPE_CONTENT_ELEMENT entsteht der eigene CType.
        // TYPO3 14: Default ist bereits CType, die Angabe ist dort wirkungslos.
        \TYPO3\CMS\Extbase\Utility\ExtensionUtility::PLUGIN_TYPE_CONTENT_ELEMENT
    );
    
    $tStamp = (new Datetime("now"))->getTimestamp();
    $GLOBALS['TYPO3_CONF_VARS']['SYS']['formEngine']['nodeRegistry'][$tStamp] = [
        'nodeName' => 'previewButtonElement',
        'priority' => 40,
        'class' => \Brinkert\Cbgooglemaps\Form\Element\PreviewButtonElement::class,
    ];
    $GLOBALS['TYPO3_CONF_VARS']['SYS']['formEngine']['nodeRegistry'][($tStamp + 1)] = [
        'nodeName' => 'jsLibrariesElement',
        'priority' => 40,
        'class' => \Brinkert\Cbgooglemaps\Form\Element\JsLibrariesElement::class,
    ];
    $GLOBALS['TYPO3_CONF_VARS']['SYS']['formEngine']['nodeRegistry'][($tStamp + 2)] = [
        'nodeName' => 'geoCodingButtonElement',
        'priority' => 40,
        'class' => \Brinkert\Cbgooglemaps\Form\Element\GeoCodingButtonElement::class,
    ];
})();
