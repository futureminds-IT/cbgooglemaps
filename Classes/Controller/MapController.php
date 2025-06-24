<?php

namespace Brinkert\Cbgooglemaps\Controller;

use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Service\FlexFormService;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class to extend the backend with a tca user field
 * @package             Cbgooglemaps
 * @path                Cbgooglemaps\Controller\MapController.php
 * @version             5.0: MapController.php,  02.07.2018
 * @copyright           (c)2011-2020 Christian Brinkert / Tobi
 * @author              Christian Brinkert <christian.brinkert@googlemail.com>
 */
class MapController extends \TYPO3\CMS\Extbase\Mvc\Controller\ActionController
{

    protected $ceData;
    protected $settings;
    protected $cobj;
    protected $filePath;


    
        // Inject FlexFormService

    public function __construct()
    {
        $this->flexFormService = GeneralUtility::makeInstance(FlexFormService::class);
    }

    private function loadFlexForm($flexFormString): array
    {
        return $this->flexFormService
            ->convertFlexFormContentToArray($flexFormString);
    }
    
    /**
     * Do some global initialization
     */
    public function initializeAction()
    {
        // store content element data to local property
        
       
        $this->ceData = $this->configurationManager->getContentObject()->data;
        $this->data = $this->loadFlexForm($this->ceData['pi_flexform']);

        // get extension typoscript
        $this->settings = $this->configurationManager->getConfiguration(
            \TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface::CONFIGURATION_TYPE_SETTINGS,
            'Cbgooglemaps',
            'Quickgooglemap');

        // set sitepath
        $request = $GLOBALS['TYPO3_REQUEST'];
        $normalizedParams = $request->getAttribute('normalizedParams');
        $baseUri = $normalizedParams->getSiteUrl();
        $this->filePath = $baseUri . '/typo3conf/ext/cbgooglemaps/';

        // set content object renderer
        $this->cobj = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(
            'TYPO3\\CMS\\Frontend\\ContentObject\\ContentObjectRenderer');
    }


    /**
     * Create map content element to the frontend
     */
    public function indexAction()
    {
        // add google or openstreetmap scripts/styles
        $assets = $this->addJsCss();

        // set map parameters
        $mapParameter = $this->getMapParameters();

        // assign mapStyling and settings
        $mapParameter['mapStyling'] = $this->getMapStyling();

        // assign map parameters to the view
        $this->view->assignMultiple($mapParameter);
        $this->view->assign('assets',$assets);
        
        
        return $this->htmlResponse();
    }


    /**
     * Build and return map parameter array, with defaults or ce specific values
     * @return array
     */
private function getMapParameters()
{
    $parameters = [];

    // assign uid of current content element
    if (isset($this->ceData['uid'])) {
        $parameters['contentId'] = $this->ceData['uid'] . '_' . $this->configurationManager->getContentObject()->parentRecord['data']['uid'];
    } else {
        $parameters['contentId'] = rand(1, 999999) . '_' . $this->configurationManager->getContentObject()->parentRecord['data']['uid'];
    }

    // map provider to build map: googleMaps or OpenStreetMap
    $parameters['mapProvider'] = $this->settings['mapProvider'] ?? '';

    // assign width and height of map
    if (isset($this->ceData['width'])) {
        $width = $this->ceData['width'];
    } elseif ((int)($this->settings['cbgmMapWidth'] ?? 0) > 0) {
        $width = $this->settings['cbgmMapWidth'];
    } else {
        $width = $this->settings['display']['width'] ?? '';
    }
    $parameters['width'] = $width;

    // assign height of map
    if (isset($this->ceData['height'])) {
        $parameters['height'] = $this->ceData['height'];
    } else {
        if (0 < (int)($this->settings['cbgmMapHeight'] ?? 0)) {
            $parameters['height'] = $this->settings['cbgmMapHeight'];
        } else {
            $parameters['height'] = $this->settings['display']['height'] ?? '';
        }
    }

    // assign pin description text, to be placed into info box
    if (isset($this->ceData['infoText'])) {
        $infoText = urlencode($this->ceData['infoText']);
    } else {
        if (isset($this->settings['cbgmDescription'])) {
            $infoText = urlencode($this->settings['cbgmDescription']);
        } else {
            $infoText = urlencode($this->settings['infoText'] ?? '');
        }
    }
    $parameters['infoText'] = $infoText;

    // assign auto open flag to the view
    $parameters['openInfoBox'] = isset($this->settings['cbgmAutoOpen']) ? $this->settings['cbgmAutoOpen'] : ($this->settings['infoTextOpen'] ?? '');

    // assign deactivation of zooming by mousewheel
    $parameters['useScrollwheel'] = $this->settings['options']['useScrollwheel'] ?? '';

    // assign location (longitude and latitude) to the view
    $parameters['latitude'] = isset($this->ceData['latitude']) ? (float)$this->ceData['latitude'] : (isset($this->settings['cbgmLatitude']) ? (float)$this->settings['cbgmLatitude'] : (float)($this->settings['latitude'] ?? ''));

    $parameters['longitude'] = isset($this->ceData['longitude']) ? (float)$this->ceData['longitude'] : (isset($this->settings['cbgmLongitude']) ? (float)$this->settings['cbgmLongitude'] : (float)($this->settings['longitude'] ?? ''));

    // assign map zoom level to the view, if given value is valid
    $parameters['mapZoom'] = isset($this->ceData['zoom']) ? (int)$this->ceData['zoom'] : ((0 <= (int)($this->settings['cbgmScaleLevel'] ?? 0) && !empty($this->settings['cbgmScaleLevel'])) ? (int)$this->settings['cbgmScaleLevel'] : (int)($this->settings['display']['zoom'] ?? ''));

    // assign map type to the view, if given value is valid
    $parameters['mapType'] = (isset($this->ceData['mapType']) && in_array((string)$this->ceData['mapType'], preg_split("/[\s]*[,][\s]*/", $this->settings['valid']['mapTypes'] ?? []))) ?
        $this->ceData['mapType'] :
        (in_array((string)($this->settings['cbgmMapType'] ?? ''), preg_split("/[\s]*[,][\s]*/", $this->settings['valid']['mapTypes'] ?? [])) ?
            $this->settings['cbgmMapType'] :
            ($this->settings['display']['mapType'] ?? ''));

    // assign navigation controls to the view
    $parameters['mapControl'] = (isset($this->ceData['navigationControl']) && in_array((string)$this->ceData['navigationControl'], preg_split("/[\s]*[,][\s]*/", $this->settings['valid']['navigationControl'] ?? []))) ?
        $this->ceData['navigationControl'] :
        (in_array((string)($this->settings['cbgmNavigationControl'] ?? ''), preg_split("/[\s]*[,][\s]*/", $this->settings['valid']['navigationControl'] ?? [])) ?
            $this->settings['cbgmNavigationControl'] :
            ($this->settings['display']['navigationControl'] ?? ''));

    // assign icon if given by constant or typoscript
    $iconPath = Environment::getPublicPath() . '/' . ($this->ceData['icon'] ?? '');
    if (isset($this->ceData['icon']) && file_exists($iconPath)) {
        $parameters['icon'] = \TYPO3\CMS\Core\Utility\GeneralUtility::getIndpEnv('TYPO3_REQUEST_HOST') . '/' . $this->ceData['icon'];
    } else {
        $displayIconPath = Environment::getPublicPath() . '/' . ($this->settings['display']['icon'] ?? '');
        if (!empty($this->settings['display']['icon']) && file_exists($displayIconPath)) {
            $parameters['icon'] = \TYPO3\CMS\Core\Utility\GeneralUtility::getIndpEnv('TYPO3_REQUEST_HOST') . '/' . $this->settings['display']['icon'];
        } else {
            $parameters['icon'] = null;
        }
    }

    // add map styling default
    $parameters['mapStyling'] = null;

    // some braces?
    $parameters['braceStart'] = '{';
    $parameters['braceEnd'] = '}';

    return $parameters;
}



    /**
     * Fetch and return map styling from file if defined
     * @param array $mapParameter
     * @return mixed
     */
private function getMapStyling()
{
    $mapStyling = null;

    if (isset($this->ceData['mapStyling']) && file_exists(Environment::getPublicPath() . '/' . $this->ceData['mapStyling'])) {
        // assign map styling from content element
        $styling = file_get_contents(Environment::getPublicPath() . '/' . $this->ceData['mapStyling']);
        $mapStyling = !is_null(json_decode($styling)) ? $styling : null;

    } elseif (!empty($this->settings['display']['mapStyling']) && file_exists(Environment::getPublicPath() . '/' . $this->settings['display']['mapStyling'])) {
        // assign map styling from typoscript file definition
        $styling = file_get_contents(Environment::getPublicPath() . '/' . $this->settings['display']['mapStyling']);
        $mapStyling = !is_null(json_decode($styling)) ? $styling : null;

    } elseif (!empty($this->settings['display']['mapStyling']) && !is_null(json_decode($this->settings['display']['mapStyling']))) {
        // assign map styling from typoscript style definition
        $mapStyling = $this->settings['display']['mapStyling'];
    }

    return $mapStyling;
}


    /**
     * Add some javascripts and css styles to the view
     * @return void
     */
    private function addJsCss()
    {

        // add google or openstreet map scripts and styles to the view
        if ('Google' == $this->settings['mapProvider']) {

            
            // build google maps uri
            $googleMapsUri = preg_match('/^http/', $this->settings['googleapi']['uri'])
                ? $this->settings['googleapi']['uri']
                : $this->filePath . $this->settings['googleapi']['uri'];
            
            

            // add optional or required given key
            if (!empty($this->settings['googleapi']['key']))
                $googleMapsUri .= '?key=' . $this->settings['googleapi']['key'];

            // add google api file
//            $GLOBALS['TSFE']->additionalHeaderData['cbgooglemaps'] =
//                '<script src="' . $googleMapsUri . '"></script>';
            
            $js = $googleMapsUri;
            $css = NULL;


        } else if ('MapBox' == $this->settings['mapProvider']) {
            // add mapbox js and css files
            $mapboxJs = preg_match('/^http/', $this->settings['mapboxapi']['js'])
                ? $this->settings['mapboxapi']['js']
                : 'EXT:cbgooglemaps/'.$this->settings['mapboxapi']['js'];

            $mapboxCss = preg_match('/^http/', $this->settings['mapboxapi']['css'])
                ? $this->settings['mapboxapi']['css']
                : 'EXT:cbgooglemaps/'.$this->settings['mapboxapi']['css'];

//            $GLOBALS['TSFE']->additionalHeaderData['cbgooglemapsJs'] =
//                '<script src="' . $mapboxJs . '"></script>';
//            $GLOBALS['TSFE']->additionalHeaderData['cbgooglemapsCss'] =
//                '<link href="' . $mapboxCss . '" rel="stylesheet" />';
            
            $js = $mapboxJs; 
            $css = $mapboxCss;


        } else {
            // add leaflet js and css files
            $osmJs = preg_match('/^http/', $this->settings['osmapi']['js'])
                ? $this->settings['osmapi']['js']
                : 'EXT:cbgooglemaps/'.$this->settings['osmapi']['js'];

            $osmCss = preg_match('/^http/', $this->settings['osmapi']['css'])
                ? $this->settings['osmapi']['css']
                : 'EXT:cbgooglemaps/'.$this->settings['osmapi']['css'];

//            $GLOBALS['TSFE']->additionalHeaderData['cbgooglemapsJs'] =
//                '<script src="' . $osmJs . '"></script>';
//            $GLOBALS['TSFE']->additionalHeaderData['cbgooglemapsCss'] =
//                '<link href="' . $osmCss . '" rel="stylesheet" />';
            
            $js = $osmJs;
            $css = $osmCss;

        }
        return array('js'=>$js,'css'=>$css);
    }

}
