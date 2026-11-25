<?php

namespace Brinkert\Cbgooglemaps\Form\Element;

use TYPO3\CMS\Backend\Form\Element\AbstractFormElement;

class PreviewButtonElement extends AbstractFormElement
{
    use PluginTypoScriptTrait;

    public function render(): array
    {
        $settings = $this->getPluginSettings();
        $display = $settings['display.'] ?? [];

        // TYPO3 14: die FormEngine-Daten enthalten nicht immer 'vanillaUid'
        $vanillaUid = (int)($this->data['vanillaUid'] ?? $this->data['uid'] ?? 0);
        $mapProvider = (string)($settings['mapProvider'] ?? '');
        $mapboxToken = (string)($settings['mapboxapi.']['accessToken'] ?? '');
        $btnPreviewMap = htmlspecialchars($this->getButtonLabel('btnPreviewMap', 'zeige Kartenvorschau'));

        $fieldset = '<div class="cbgm_preview">';
        $fieldset .= '<input type="button" id="mappreview"'
            . ' data-vanillauid="' . $vanillaUid
            . '" data-zoom="' . htmlspecialchars((string)($display['zoom'] ?? ''))
            . '" data-maptype="' . htmlspecialchars((string)($display['mapType'] ?? ''))
            . '" data-navcontrol="' . htmlspecialchars((string)($display['navigationControl'] ?? ''))
            . '" data-mapprovider="' . htmlspecialchars($mapProvider)
            . '" data-accestoken="' . htmlspecialchars($mapboxToken)
            . '" value="'
            . $btnPreviewMap . '">';
        $fieldset .= '<div id="cbgm_previewMap"></div>';
        $fieldset .= '</div>';

        $result = $this->initializeResultArray();
        $result['html'] = $fieldset;
        return $result;
    }
}
