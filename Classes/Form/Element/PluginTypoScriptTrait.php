<?php

namespace Brinkert\Cbgooglemaps\Form\Element;

use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\BackendConfigurationManager;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManager;

/**
 * Ermittelt die TypoScript-Einstellungen von plugin.tx_cbgooglemaps im
 * Backend-Formular.
 *
 * Hintergrund (TYPO3 14): Im Backend haengt das verfuegbare TypoScript am
 * 'id'-Parameter des Requests (BackendConfigurationManager::getCurrentPageId).
 * Beim Oeffnen eines Inhaltselements (Layout-Modul, Modal/AJAX) fehlt 'id'
 * haeufig -> Setup von pid 0 -> plugin.tx_cbgooglemaps ist leer und die
 * Buttons rendern ohne Beschriftung/Provider. Deshalb: Fallback auf die
 * Seite, auf der der bearbeitete Datensatz liegt (effectivePid / pid).
 *
 * Die Button-Beschriftungen kommen zusaetzlich aus der XLF der Extension,
 * damit sie auch ohne TypoScript nie leer sind.
 */
trait PluginTypoScriptTrait
{
    protected ?array $cbgooglemapsPluginTypoScript = null;

    protected function getPluginTypoScript(): array
    {
        if ($this->cbgooglemapsPluginTypoScript !== null) {
            return $this->cbgooglemapsPluginTypoScript;
        }

        // 1) Normaler Weg (Request kennt die Seite)
        $request = $GLOBALS['TYPO3_REQUEST'] ?? null;
        if ($request instanceof ServerRequestInterface) {
            $plugin = $this->loadPluginTypoScriptForRequest($request);
            if ($plugin !== []) {
                return $this->cbgooglemapsPluginTypoScript = $plugin;
            }
        }

        // 2) Fallback: Seite des bearbeiteten Datensatzes
        $pageId = (int)($this->data['effectivePid'] ?? 0);
        if ($pageId <= 0) {
            $pageId = (int)($this->data['databaseRow']['pid'] ?? 0);
        }
        if ($pageId > 0 && $request instanceof ServerRequestInterface) {
            $requestForPage = $request->withQueryParams(
                array_merge($request->getQueryParams(), ['id' => $pageId])
            );
            $plugin = $this->loadPluginTypoScriptForRequest($requestForPage);
        }

        return $this->cbgooglemapsPluginTypoScript = $plugin ?? [];
    }

    /**
     * @return array plugin.tx_cbgooglemaps. des uebergebenen Requests (leer, wenn nicht ermittelbar)
     */
    private function loadPluginTypoScriptForRequest(ServerRequestInterface $request): array
    {
        try {
            $configurationManager = GeneralUtility::makeInstance(ConfigurationManager::class);

            // Der BackendConfigurationManager ist im DI-Container nicht public,
            // laesst sich aber ueber den ConfigurationManager erreichen - damit
            // ist das Setup unabhaengig davon, welchen Request der
            // ConfigurationManager selbst kennt.
            $reflection = new \ReflectionObject($configurationManager);
            if ($reflection->hasProperty('beConfigManager')) {
                $property = $reflection->getProperty('beConfigManager');
                $property->setAccessible(true);
                $backendConfigurationManager = $property->getValue($configurationManager);
                if ($backendConfigurationManager instanceof BackendConfigurationManager) {
                    $typoScript = $backendConfigurationManager->getTypoScriptSetup($request);
                    $plugin = $typoScript['plugin.']['tx_cbgooglemaps.'] ?? [];
                    if ($plugin !== []) {
                        return $plugin;
                    }
                }
            }

            $typoScript = $configurationManager->getConfiguration(
                $configurationManager::CONFIGURATION_TYPE_FULL_TYPOSCRIPT
            );
            return $typoScript['plugin.']['tx_cbgooglemaps.'] ?? [];
        } catch (\Throwable) {
            return [];
        }
    }

    protected function getPluginSettings(): array
    {
        return $this->getPluginTypoScript()['settings.'] ?? [];
    }

    /**
     * Button-Beschriftung: 1) XLF der Extension in der Sprache des Backend-Users
     * (TYPO3-14-Standard, korrekt lokalisiert), 2) Altbestand
     * TypoScript-_LOCAL_LANG, 3) hartkodierter Fallback.
     */
    protected function getButtonLabel(string $key, string $fallback): string
    {
        if (isset($GLOBALS['LANG']) && is_object($GLOBALS['LANG'])) {
            $label = (string)$GLOBALS['LANG']->sL(
                'LLL:EXT:cbgooglemaps/Resources/Private/Language/locallang.xlf:' . $key
            );
            if ($label !== '') {
                return $label;
            }
        }

        $plugin = $this->getPluginTypoScript();
        $localLang = $plugin['_LOCAL_LANG.'] ?? [];
        $iso2 = (string)($GLOBALS['BE_USER']->uc['lang'] ?? $GLOBALS['BE_USER']->user['lang'] ?? 'default') . '.';
        $buttonLabels = $localLang[$iso2] ?? ($localLang['default.'] ?? []);
        $label = (string)($buttonLabels[$key] ?? '');

        return $label !== '' ? $label : $fallback;
    }
}
