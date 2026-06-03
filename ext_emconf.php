<?php

declare(strict_types=1);

$EM_CONF[$_EXTKEY] = [
    'title' => 'Mail Link Fixer',
    'description' => 'CLI command to migrate legacy javascript:linkTo_UnCryptMailto() email hrefs in RTE fields to standard mailto: links.',
    'category' => 'backend',
    'version' => '1.0.0',
    'state' => 'stable',
    'author' => 'Hoja Mustaffa Abdul Latheef',
    'author_email' => 'projects@jweiland.net',
    'author_company' => 'jweiland.net',
    'constraints' => [
        'depends' => [
            'typo3' => '13.4.0-14.99.99',
        ],
    ],
];
