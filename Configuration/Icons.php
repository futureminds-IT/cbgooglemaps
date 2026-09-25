<?php

return [
    // Identifier mit Extension-Namen präfixiert: generische Namen wie
    // "ce-default-icon" können mit anderen Extensions kollidieren.
    'cbgooglemaps-wizard-icon' => [
        'provider' => \TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider::class,
        'source' => 'EXT:cbgooglemaps/Resources/Public/Icons/ce_wiz.svg',
    ],
];