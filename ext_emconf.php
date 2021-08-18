<?php

/*
 * This file is part of the smartcontentcontainer_display_pattern extension for TYPO3
 * - (c) 2021 Waldhacker UG
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

$EM_CONF[$_EXTKEY] = [
    'title'            => 'smart content container display pattern expansion',
    'description'      => 'Extends the smart content container content element with "display pattern" and "special content placement"',
    'category'         => 'frontend',
    'author'           => 'Ralf Zimmermann',
    'author_email'     => 'hello@waldhacker.dev',
    'author_company'   => 'waldhacker UG (haftungsbeschränkt)',
    'state'            => 'beta',
    'uploadfolder'     => '0',
    'clearCacheOnLoad' => 1,
    'version'          => '0.0.1',
    'constraints'      => [
        'depends' => [
            'typo3' => '11.4.0-11.4.99',
        ]
    ]
];
