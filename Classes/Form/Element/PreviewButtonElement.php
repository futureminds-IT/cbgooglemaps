<?php

namespace Brinkert\Cbgooglemaps\Form\Element;

use TYPO3\CMS\Backend\Form\Element\AbstractFormElement;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManager;

class PreviewButtonElement extends AbstractFormElement
{

    public function render()
    {

        /** @var ConfigurationManager $cm */
        $cm = GeneralUtility::makeInstance(ConfigurationManager::class);
        $ts = $cm->getConfiguration($cm::CONFIGURATION_TYPE_FULL_TYPOSCRIPT);
        $settings = $ts['plugin.']['tx_cbgooglemaps.']['settings.'];
        $i18n = $ts['plugin.']['tx_cbgooglemaps.']['_LOCAL_LANG.'];
        $iso2 = $GLOBALS['BE_USER']->uc['lang'] . '.';
        $btnLabels = isset($i18n[$iso2]) ? $i18n[$iso2] : $i18n['default.'];

        $fieldset =  '<div class="cbgm_preview">';
        $fieldset .= '<input type="button" id="mappreview"'
            . ' data-vanillauid="'. $this->data['vanillaUid']
            . '" data-zoom="' . $settings['display.']['zoom']
            . '" data-maptype="' . $settings['display.']['mapType']
            . '" data-navcontrol="' . $settings['display.']['navigationControl']
            . '" data-mapprovider="' . $settings['mapProvider']
            . '" data-accestoken="' .  $settings['mapboxapi.']['accessToken']
            . '" value="'
            . $btnLabels['btnPreviewMap'] .'">';
        $fieldset .= '<div id="cbgm_previewMap"></div>';
        $fieldset .= '</div>';

        $result = $this->initializeResultArray();
        $result['html'] = $fieldset;
        return $result;
    }
}