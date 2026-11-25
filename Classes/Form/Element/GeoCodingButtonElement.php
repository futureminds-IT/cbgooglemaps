<?php

namespace Brinkert\Cbgooglemaps\Form\Element;

use TYPO3\CMS\Backend\Form\Element\AbstractFormElement;

class GeoCodingButtonElement extends AbstractFormElement
{
    use PluginTypoScriptTrait;

    public function render(): array
    {
        $settings = $this->getPluginSettings();

        // TYPO3 14: die FormEngine-Daten enthalten nicht immer 'vanillaUid'
        $vanillaUid = (int)($this->data['vanillaUid'] ?? $this->data['uid'] ?? 0);
        $mapProvider = (string)($settings['mapProvider'] ?? '');
        $mapboxToken = (string)($settings['mapboxapi.']['accessToken'] ?? '');
        $btnGeocoding = htmlspecialchars($this->getButtonLabel('btnGeocoding', 'Ermittle Koordinaten aus oben genannter Adresse'));
        $btnDisplayMap = htmlspecialchars($this->getButtonLabel('btnDisplayMap', 'Zeige Kartenansicht'));

        $fieldset = '<div class="cbgm_geocoding">';
        $fieldset .= '<input type="button" id="dogeocoding" '
            . ' data-vanillauid="' . $vanillaUid
            . '" data-mapprovider="' . htmlspecialchars($mapProvider)
            . '" data-accestoken="' . htmlspecialchars($mapboxToken)
            . '" value="'
            . $btnGeocoding . '">';
        $fieldset .= '<input type="button" id="dodisplaylocation" '
            . ' data-vanillauid="' . $vanillaUid
            . '" data-mapprovider="' . htmlspecialchars($mapProvider)
            . '" data-accestoken="' . htmlspecialchars($mapboxToken)
            . '" value="'
            . $btnDisplayMap . '">';
        $fieldset .= '<div id="cbgm_previewLocation"></div>';
        $fieldset .= '</div>';

        $result = $this->initializeResultArray();
        $result['html'] = $fieldset;
        return $result;
    }
}
