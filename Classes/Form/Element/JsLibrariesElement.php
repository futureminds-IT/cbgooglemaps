<?php

namespace Brinkert\Cbgooglemaps\Form\Element;

use TYPO3\CMS\Backend\Form\Element\AbstractFormElement;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

class JsLibrariesElement extends AbstractFormElement
{
    use PluginTypoScriptTrait;

    public function render(): array
    {
        $filePath = 'EXT:cbgooglemaps/';

        $settings = $this->getPluginSettings();

        $pageRenderer = GeneralUtility::makeInstance(\TYPO3\CMS\Core\Page\PageRenderer::class);
        // add own scripts for gmaps object and mapping functions
        $pageRenderer->addJsFile($filePath . 'Resources/Public/JavaScript/class.gmaps.js',
            'text/javascript', FALSE, FALSE, '', FALSE);
        $pageRenderer->addJsFile($filePath . 'Resources/Public/JavaScript/class.geocoding.js',
            'text/javascript', FALSE, FALSE, '', FALSE);
        $pageRenderer->addJsFile($filePath . 'Resources/Public/JavaScript/utilities.js',
            'text/javascript', FALSE, FALSE, '', FALSE);

        $mapProvider = (string)($settings['mapProvider'] ?? '');
        // add map provider specific libraries
        if ('Google' === $mapProvider) {
            // Google Maps JS API: aktueller Stand = dynamischer Bibliotheks-Import
            // (v=weekly, loading=async, Bibliotheken marker+geocoding)
            $googleMapsUri = (string)($settings['googleapi.']['uri'] ?? '');
            if ($googleMapsUri === '') {
                $googleMapsUri = 'https://maps.googleapis.com/maps/api/js';
            }
            $googleMapsParameters = [
                'v' => 'weekly',
                'loading' => 'async',
                'libraries' => 'marker,geocoding',
            ];
            if (!empty($settings['googleapi.']['key']) && !str_contains($googleMapsUri, 'key=')) {
                $googleMapsParameters['key'] = $settings['googleapi.']['key'];
            }
            $googleMapsUri .= (str_contains($googleMapsUri, '?') ? '&' : '?') . http_build_query($googleMapsParameters);
            // add google libraries
            $pageRenderer->addJsFile($googleMapsUri, 'text/javascript', FALSE, FALSE, '', TRUE);
        } elseif ('MapBox' === $mapProvider) {
            // add mapbox libraries
            $pageRenderer->addJsFile(
                $filePath . 'Resources/Public/JavaScript/mapbox/mapbox-gl.js',
                'text/javascript', FALSE, FALSE, '', TRUE);
            $pageRenderer->addCssFile(
                $filePath . 'Resources/Public/JavaScript/mapbox/mapbox-gl.css',
                'stylesheet', 'all', '', FALSE, FALSE, '', TRUE);
        } else {
            // add openstreetmap libraries
            $pageRenderer->addJsFile(
                $filePath . 'Resources/Public/JavaScript/leaflet/leaflet-src.js',
                'text/javascript', FALSE, FALSE, '', TRUE);
            $pageRenderer->addCssFile(
                $filePath . 'Resources/Public/JavaScript/leaflet/leaflet.css',
                'stylesheet', 'all', '', FALSE, FALSE, '', TRUE);
        }

        // TYPO3 14: render() hat einen strikten array-Return-Type. Dieses Element
        // hat kein eigenes Feld-UI - es laedt nur Karten-JS/CSS ins Backend-Formular
        // (fuer Geo-Coding- und Map-Preview-Button). Ohne return: 500
        // "JsLibrariesElement::render(): Return value must be of type array, none returned".
        return $this->initializeResultArray();
    }
}