<?php

declare(strict_types=1);

namespace Brinkert\Cbgooglemaps\Updates;

use TYPO3\CMS\Install\Attribute\UpgradeWizard;
use TYPO3\CMS\Install\Updates\AbstractListTypeToCTypeUpdate;

/**
 * Migriert die Karten-Elemente von `list_type` auf den eigenen CType.
 *
 * Unser Fork registriert das Element als eigenes Inhaltselement
 * (ExtensionUtility::configurePlugin(..., PLUGIN_TYPE_CONTENT_ELEMENT)) — in
 * TYPO3 13.4 UND 14. Ein `list_type`-Pfad
 * (tt_content.list.20.cbgooglemaps_quickgooglemap) entsteht dabei nicht mehr.
 *
 * Datensätze aus älteren Installationen stehen aber auf CType='list' +
 * list_type='cbgooglemaps_quickgooglemap' und werden dann nicht mehr gerendert:
 * die Karte fehlt im Frontend, in TYPO3 14 zusätzlich mit dem Hinweis
 * "Content Element with uid <n> and type list has no rendering definition!".
 *
 * Die Basisklasse erledigt tt_content UND die Plugin-Berechtigungen
 * (be_groups.explicit_allowdeny).
 *
 * Kompatibilität: Die Basisklasse und das Attribut liegen in TYPO3 13.4 in
 * EXT:install (nativ) und in TYPO3 14 in den Kompatibilitätsklassen von
 * EXT:core — ein Wizard, der in beiden Versionen läuft. Registriert wird er in
 * Configuration/Services.php (dort auch der Schutz für TYPO3 12).
 */
#[UpgradeWizard('cbgooglemapsListTypeToCType')]
final class CbgooglemapsListTypeToCTypeUpdate extends AbstractListTypeToCTypeUpdate
{
    /**
     * Key = Wert in tt_content.list_type, Value = neuer Wert für tt_content.CType.
     *
     * @return array<string, string>
     */
    protected function getListTypeToCTypeMapping(): array
    {
        return [
            'cbgooglemaps_quickgooglemap' => 'cbgooglemaps_quickgooglemap',
        ];
    }

    public function getTitle(): string
    {
        return 'cbgooglemaps: Karten-Elemente auf das eigene Inhaltselement (CType) migrieren';
    }

    public function getDescription(): string
    {
        return 'Stellt tt_content-Datensätze mit list_type=cbgooglemaps_quickgooglemap auf den '
            . 'eigenen CType cbgooglemaps_quickgooglemap um und passt die Plugin-Berechtigungen '
            . 'in be_groups an. Ohne diesen Schritt bleibt die Karte im Frontend leer.';
    }
}
