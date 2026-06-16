<?php

/*
 * This file is part of the package jweiland/mail-link-fixer.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

$EM_CONF[$_EXTKEY] = [
    'title' => 'Mail Link Fixer',
    'description' => 'CLI command to migrate legacy javascript:linkTo_UnCryptMailto() email hrefs in RTE fields to standard mailto: links.',
    'category' => 'backend',
    'author' => 'Hoja Mustaffa Abdul Latheef',
    'author_email' => 'projects@jweiland.net',
    'author_company' => 'jweiland.net',
    'state' => 'stable',
    'version' => '1.0.0',
    'constraints' => [
        'depends' => [
            'typo3' => '13.4.0-14.3.99',
        ],
    ],
];
