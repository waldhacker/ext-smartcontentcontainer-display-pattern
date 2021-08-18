<?php

declare(strict_types=1);

/*
 * This file is part of the
 * smartcontentcontainer_display_pattern extension for TYPO3
 * - (c) 2021 Waldhacker UG (haftungsbeschränkt)
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

namespace Waldhacker\SmartContentContainerDisplayPattern\Hooks;

use TYPO3\CMS\Core\Utility\GeneralUtility;

class TcaItemsProcessorFunctions
{
    public function populateAvailableRenderableTables(array &$fieldDefinition): void
    {
        $renderableTables = array_filter(
            array_unique(GeneralUtility::trimExplode(',', $GLOBALS['TCA']['tt_content']['columns']['content_pool']['config']['allowed'] ?? '', true)),
            function (string $table): bool {
                return $table !== 'sys_category';
            }
        );

        foreach ($renderableTables as $tableName) {
            $tableConfiguration = $GLOBALS['TCA'][$tableName] ?? null;
            if (empty($tableConfiguration)) {
                continue;
            }

            $label = ($tableConfiguration['ctrl']['title'] ?? '') ?: '';
            $fieldDefinition['items'][] = [$label, $tableName];
        }
    }
}
