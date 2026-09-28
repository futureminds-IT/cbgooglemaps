<?php

declare(strict_types=1);

use Brinkert\Cbgooglemaps\Updates\CbgooglemapsListTypeToCTypeUpdate;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use TYPO3\CMS\Install\Attribute\UpgradeWizard;
use TYPO3\CMS\Install\Updates\AbstractListTypeToCTypeUpdate;

/*
 * Upgrade-Wizard "list_type -> CType" registrieren.
 *
 * Die Basisklasse und das Attribut gibt es erst ab TYPO3 13.4 (EXT:install);
 * in TYPO3 14 kommen sie über die Kompatibilitätsklassen in EXT:core. In
 * TYPO3 12.4 existiert beides nicht — dort wird der Service deshalb NICHT
 * registriert und die Klasse nie geladen (sonst Fatal Error beim
 * Container-Aufbau).
 */
return static function (ContainerConfigurator $container, ContainerBuilder $containerBuilder): void {
    if (!class_exists(UpgradeWizard::class) || !class_exists(AbstractListTypeToCTypeUpdate::class)) {
        return;
    }

    $container->services()
        ->set(CbgooglemapsListTypeToCTypeUpdate::class)
        ->autowire()
        ->autoconfigure()
        ->public();
};
