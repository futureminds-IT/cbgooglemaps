<?php

declare(strict_types=1);

use TYPO3\CMS\Core\Security\ContentSecurityPolicy\Directive;
use TYPO3\CMS\Core\Security\ContentSecurityPolicy\Mutation;
use TYPO3\CMS\Core\Security\ContentSecurityPolicy\MutationCollection;
use TYPO3\CMS\Core\Security\ContentSecurityPolicy\MutationMode;
use TYPO3\CMS\Core\Security\ContentSecurityPolicy\Scope;
use TYPO3\CMS\Core\Security\ContentSecurityPolicy\SourceKeyword;
use TYPO3\CMS\Core\Security\ContentSecurityPolicy\SourceScheme;
use TYPO3\CMS\Core\Security\ContentSecurityPolicy\UriValue;
use TYPO3\CMS\Core\Type\Map;

return Map::fromEntries([
    // Provide declarations for the backend
    Scope::backend(),
    // NOTICE: When using `MutationMode::Set` existing declarations will be overridden

    new MutationCollection(
       new Mutation(
            MutationMode::Extend,
            Directive::ScriptSrc,
            SourceKeyword::unsafeInline
        ),
       new Mutation(
            MutationMode::Extend,
            Directive::ConnectSrc,
            SourceScheme::data,
            new UriValue('https://*.openstreetmap.org')
        ),
            new Mutation(
            MutationMode::Extend,
            Directive::ImgSrc,
            SourceScheme::data,
            new UriValue('https://*.openstreetmap.org')
        ),
       new Mutation(
            MutationMode::Extend,
            Directive::ConnectSrc,
            SourceScheme::data,
            new UriValue('https://*.mapbox.com')
        ),            
           new Mutation(
            MutationMode::Extend,
            Directive::ImgSrc,
            SourceScheme::data,
            new UriValue('https://*.mapbox.com')
        ),
            new Mutation(
            MutationMode::Extend,
            Directive::ImgSrc,
            SourceScheme::blob
        ),
            // Mapbox GL JS ab v2/v3 erzeugt seinen Rendering-Worker aus einem
            // Blob - ohne worker-src/child-src laeuft er unter default-src
            // 'self' und wird blockiert: die Karte bleibt leer (kein Fehler
            // im Formular, nur "mapboxgl is not defined"/leerer Container).
            new Mutation(
            MutationMode::Extend,
            Directive::WorkerSrc,
            SourceScheme::blob
        ),
            new Mutation(
            MutationMode::Extend,
            Directive::ChildSrc,
            SourceScheme::blob
        ),   
            new Mutation(
            MutationMode::Extend,
            Directive::ConnectSrc,
            SourceScheme::data,
            new UriValue('https://*.googleapis.com')
        ),    
    ),
]);


