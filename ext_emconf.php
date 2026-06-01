<?php

declare(strict_types=1);

$EM_CONF[$_EXTKEY] = [
    'title' => 'Resolve Unsecure Mail',
    'description' => 'CLI command to fix obsolete email hrefs in RTE Fields.',
    'category' => 'backend',
    'version' => '1.0.0',
    'state' => 'stable',
    'author' => 'Hoja Mustaffa Abdul Latheef',
    'author_email' => 'projects@jweiland.net',
    'author_company' => 'jweiland.net',
    'constraints' => [
        'depends' => [
            'typo3' => '13.4.24-13.4.99',
        ],
    ],
];
