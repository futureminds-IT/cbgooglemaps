<?php

namespace Brinkert\Cbgooglemaps\Form\Element;

use TYPO3\CMS\Backend\Form\Element\AbstractFormElement;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManager;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

class JsLibrariesElement extends AbstractFormElement
{
    public function render(): array
    {
        $cm = GeneralUtility::makeInstance(ConfigurationManager::class);
        $ts = $cm->getConfiguration($cm::CONFIGURATION_TYPE_FULL_TYPOSCRIPT);
        $filePath = 'EXT:cbgooglemaps/';

        // Defensive: check for settings
        $settings = [];
        if (
            isset($ts['plugin.']) &&
            isset($ts['plugin.']['tx_cbgooglemaps.']) &&
            isset($ts['plugin.']['tx_cbgooglemaps.']['settings.'])
        ) {
            $settings = $ts['plugin.']['tx_cbgooglemaps.']['settings.'];
        }

        $pageRenderer = GeneralUtility::makeInstance(\TYPO3\CMS\Core\Page\PageRenderer::class);

        // Only add files if settings are present
        if (!empty($settings)) {
            $pageRenderer->addJsFile($filePath . 'Resources/Public/JavaScript/class.gmaps.js',
                'text/javascript', false, false, '', false);
            $pageRenderer->addJsFile($filePath . 'Resources/Public/JavaScript/class.geocoding.js',
                'text/javascript', false, false, '', false);
            $pageRenderer->addJsFile($filePath . 'Resources/Public/JavaScript/utilities.js',
                'text/javascript', false, false, '', false);

            if ('Google' === $settings['mapProvider']) {
                $googleMapsUri = $settings['googleapi.']['uri'];
                if ($settings['googleapi.']['key'])
                    $googleMapsUri .= '?key=' . $settings['googleapi.']['key'];
                $pageRenderer->addJsFile($googleMapsUri, 'text/javascript', false, false, '', true);
            } else if ('MapBox' === $settings['mapProvider']) {
                $pageRenderer->addJsFile(
                    $filePath . 'Resources/Public/JavaScript/mapbox/mapbox-gl-patched.js',
                    'text/javascript', false, false, '', true);
                $pageRenderer->addCssFile(
                    $filePath . 'Resources/Public/JavaScript/mapbox/mapbox-gl.css',
                    'stylesheet', 'all', '', false, false, '', true);
            } else {
                $pageRenderer->addJsFile(
                    $filePath . 'Resources/Public/JavaScript/leaflet/leaflet-src.js',
                    'text/javascript', false, false, '', true);
                $pageRenderer->addCssFile(
                    $filePath . 'Resources/Public/JavaScript/leaflet/leaflet.css',
                    'stylesheet', 'all', '', false, false, '', true);
            }
        }

        // Always return an array
        return $this->initializeResultArray();
    }
}