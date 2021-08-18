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

namespace Waldhacker\SmartContentContainerDisplayPattern\Resolver;

use TYPO3\CMS\Core\Database\RelationHandler;
use TYPO3\CMS\Core\Domain\Repository\PageRepository;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

abstract class AbstractResolver
{
    protected PageRepository $pageRepository;

    public function __construct()
    {
        $this->pageRepository = $this->getTypoScriptFrontendController()->sys_page instanceof PageRepository
                                ? $this->getTypoScriptFrontendController()->sys_page
                                : GeneralUtility::makeInstance(PageRepository::class);
    }

    protected function getCategoryUidsFromPage(string $pageCategoryField): array
    {
        $categoriesFromPage = $this->collectSysCategoriesFromPage(
            $this->getTypoScriptFrontendController()->contentPid,
            $pageCategoryField
        );

        return array_map(
            function (array $relationItem): int {
                return (int)$relationItem['id'];
            },
            $categoriesFromPage['items']
        );
    }

    protected function collectSysCategoriesFromPage(
        int $pageUid,
        string $categoryColumn
    ): array {
        /** @var RelationHandler $relationHandler */
        $relationHandler = GeneralUtility::makeInstance(RelationHandler::class);
        $relationHandler->start(
            '',
            'sys_category',
            $GLOBALS['TCA']['pages']['columns'][$categoryColumn]['config']['MM'] ?? '',
            $pageUid,
            'pages',
            $GLOBALS['TCA']['pages']['columns'][$categoryColumn]['config'] ?? []
        );
        $relationHandler->additionalWhere['sys_category'] = $this->pageRepository->enableFields('sys_category');
        $records = $relationHandler->getFromDB();
        reset($relationHandler->itemArray);
        $items = $relationHandler->itemArray;

        return $this->overlayRelations([
            'records' => $records,
            'items' => $items,
        ]);
    }

    protected function collectRecordsFromSysCategory(
        int $categoryUid,
        array $tables
    ): array {
        /** @var RelationHandler $relationHandler */
        $relationHandler = GeneralUtility::makeInstance(RelationHandler::class);
        $relationHandler->start(
            '',
            implode(',', $tables),
            $GLOBALS['TCA']['sys_category']['columns']['items']['config']['MM'] ?? '',
            $categoryUid,
            'sys_category',
            $GLOBALS['TCA']['sys_category']['columns']['items']['config'] ?? []
        );
        foreach ($tables as $table) {
            $relationHandler->additionalWhere[$table] = $this->pageRepository->enableFields($table);
        }
        $records = $relationHandler->getFromDB();
        reset($relationHandler->itemArray);
        $items = $relationHandler->itemArray;

        return $this->overlayRelations([
            'records' => $records,
            'items' => $items,
        ]);
    }

    protected function overlayRelations(array $relations): array
    {
        // Remove items with references to unresolved records
        $relations['items'] = array_values(array_filter(
            $relations['items'],
            function (array $relationItem) use ($relations): bool {
                return isset($relations['records'][$relationItem['table']][$relationItem['id']]);
            }
        ));

        $accessiblePageIdsCache = [];
        foreach ($relations['records'] as $table => $records) {
            foreach ($records as $uid => $record) {
                $recordPageUid = (int)$record['pid'];
                if (!isset($accessiblePageIdsCache[$recordPageUid])) {
                    $validPageUid = $this->pageRepository->filterAccessiblePageIds([$recordPageUid])[0] ?? null;
                    if (empty($validPageUid)) {
                        $record = null;
                    } else {
                        $accessiblePageIdsCache[$validPageUid] = $validPageUid;
                    }
                }

                if (is_array($record)) {
                    $this->pageRepository->versionOL($table, $record);
                }

                if (is_array($record)) {
                    $record = $this->pageRepository->getLanguageOverlay($table, $record);
                }

                if (is_array($record)) {
                    // Add to records
                    $relations['records'][$table][$uid] = $record;
                } else {
                    // Remove from records
                    unset($relations['records'][$table][$uid]);
                    // Remove from items
                    $relations['items'] = array_values(array_filter(
                        $relations['items'],
                        function (array $relationItem) use ($table, $uid): bool {
                            return !($relationItem['table'] === $table && $relationItem['id'] === $uid);
                        }
                    ));
                }
            }
        }

        return $relations;
    }

    protected function reduceRelationItemsByRecordsToExclude(array $relationItems, array $recordsToExclude): array
    {
        return array_values(array_filter(
            $relationItems,
            function (array $relationItem) use ($recordsToExclude): bool {
                $recordIdentifier = $relationItem['table'] . ':' . $relationItem['id'];
                return !isset($recordsToExclude[$recordIdentifier]);
            }
        ));
    }

    protected function sortRelations(array $relations, string $sortingType): array
    {
        if ($sortingType === 'last_updated') {
            usort(
                $relations['items'],
                function (array $relationItemA, array $relationItemB) use ($relations): int {
                    $relationTableA = $relationItemA['table'];
                    $relationTableB = $relationItemB['table'];
                    $tstampColumnA = $GLOBALS['TCA'][$relationTableA]['ctrl']['tstamp'] ?? null;
                    $tstampColumnB = $GLOBALS['TCA'][$relationTableB]['ctrl']['tstamp'] ?? null;
                    $recordA = $relations['records'][$relationTableA][$relationItemA['id']];
                    $recordB = $relations['records'][$relationTableB][$relationItemB['id']];

                    // order by tstamp DESC
                    return ((int)($recordA[$tstampColumnA] ?? 0) <=> (int)($recordB[$tstampColumnB] ?? 0)) * -1;
                }
            );
        } elseif ($sortingType === 'random') {
            shuffle($relations['items']);
        }

        return $relations;
    }

    protected function getTypoScriptFrontendController(): TypoScriptFrontendController
    {
        return $GLOBALS['TSFE'];
    }
}
