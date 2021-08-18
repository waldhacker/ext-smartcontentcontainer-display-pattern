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
use TYPO3\CMS\Core\TypoScript\TypoScriptService;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class ContentPlacementsResolver extends AbstractResolver
{
    protected array $tca;
    protected array $pluginSettings;

    public function __construct(TypoScriptService $typoScriptService)
    {
        parent::__construct();
        $this->tca = $GLOBALS['TCA']['tt_content']['columns']['pi_flexform']['config']['ds']['*,smartcontentcontainer']['sheets']['sSccContentPlacements']['ROOT']['el']['contentPlacements']['el']['container']['el'] ?? [];
        $this->pluginSettings = $typoScriptService->convertTypoScriptArrayToPlainArray(
            $this->getTypoScriptFrontendController()->tmpl->setup['plugin.']['tx_smartcontentcontainerdisplaypattern.']['settings.'] ?? []
        );
    }

    public function resolveRelations(
        array $processedContentPoolItems,
        array $unprocessedContentPoolItems,
        array $contentPoolRecords,
        array $excludedPageRecords,
        array $configuration
    ) {
        $renderableTables = $this->normalizeTablesList(
            $this->pluginSettings['specialContentPlacement']['allowedCategoryItemsTables'] ?? ''
        );
        $configuration['contentPlacements'] = is_array($configuration['contentPlacements'])
                                              ? $configuration['contentPlacements']
                                              : [];

        $configuration['contentPlacements'] = array_filter(
            $configuration['contentPlacements'],
            function (array $ruleConfiguration): bool {
                return (bool)($ruleConfiguration['container']['contentPlacementDisabled'] ?? false) === false;
            }
        );

        foreach ($configuration['contentPlacements'] as $ruleConfiguration) {
            $ruleConfiguration = $ruleConfiguration['container'] ?? [];

            $contentPlacementScope = $ruleConfiguration['contentPlacementScope'] ?? 'custom_record_list';
            if ($contentPlacementScope === 'custom_record_list') {
                $sortingType = $ruleConfiguration['contentPlacementRecordListSorting'] ?? 'last_updated';
            } else {
                $sortingType = $ruleConfiguration['contentPlacementCategorySorting'] ?? 'last_updated';
            }

            $replacementRelations = ['items' => [], 'records' => []];
            if ($contentPlacementScope === 'custom_record_list') {
                $replacementRelations = $this->resolveReplacementRelationsFromRecordList($ruleConfiguration);
            } elseif ($contentPlacementScope === 'custom_category_list') {
                $replacementRelations = $this->resolveReplacementRelationsFromCategoryList(
                    $ruleConfiguration,
                    $renderableTables
                );
            } elseif ($contentPlacementScope === 'auto_collected_categories_from_page') {
                $replacementRelations = $this->resolveReplacementRelationsFromCategoriesFromPage(
                    $ruleConfiguration,
                    $renderableTables
                );
            }

            $replacementRelations['items'] = $this->reduceRelationItemsByRecordsToExclude(
                $replacementRelations['items'],
                $excludedPageRecords
            );

            $replacementRelations = $this->sortRelations($replacementRelations, $sortingType);

            $contentReplacements = $this->placeContentReplacements(
                $replacementRelations,
                $processedContentPoolItems,
                $contentPoolRecords,
                $ruleConfiguration
            );

            $contentPoolRecords = $contentReplacements['contentPoolRecords'];
            $processedContentPoolItems = $contentReplacements['processedContentPoolItems'];
        }

        return [
            'records' => $contentPoolRecords,
            'items' => $processedContentPoolItems,
        ];
    }

    protected function placeContentReplacements(
        array $replacementRelations,
        array $processedContentPoolItems,
        array $contentPoolRecords,
        array $configuration
    ): array {
        $replaceContent = (bool)($configuration['contentPlacementReplace'] ?? false);
        $repeatReplacement = (bool)($configuration['contentPlacementRepetitionType'] ?? false);
        $contentPlacementPosition = (int)($configuration['contentPlacementPosition'] ?? 1);
        $contentPlacementPosition = $contentPlacementPosition < 1 ? 1 : $contentPlacementPosition;

        foreach ($processedContentPoolItems as $index => $processedContentPoolItem) {
            if (empty($replacementRelations['items'])) {
                break;
            }
            if (($index + 1) % $contentPlacementPosition !== 0) {
                continue;
            }

            // add record
            $replacementRelationTable = $replacementRelations['items'][0]['table'];
            $replacementRelationUid = (int)$replacementRelations['items'][0]['id'];
            $replacementRelationRecord = $replacementRelations['records'][$replacementRelationTable][$replacementRelationUid];
            $contentPoolRecords[$replacementRelationTable] = isset($contentPoolRecords[$replacementRelationTable])
                                                             ? $contentPoolRecords[$replacementRelationTable]
                                                             : [];
            $contentPoolRecords[$replacementRelationTable][$replacementRelationUid] = $replacementRelationRecord;

            // inject / replace item
            if ($replaceContent) {
                $processedContentPoolItems[$index] = $replacementRelations['items'][0];
            } else {
                array_splice($processedContentPoolItems, $index, 0, [$replacementRelations['items'][0]]);
                array_pop($processedContentPoolItems);
            }
            $replacementRelations['items'] = $this->rotateRelationItems($replacementRelations['items'], $repeatReplacement);
        }

        return [
            'contentPoolRecords' => $contentPoolRecords,
            'processedContentPoolItems' => $processedContentPoolItems
        ];
    }

    protected function rotateRelationItems(array $relationItems, bool $repeatReplacement): array
    {
        $firstRelationItem = array_shift($relationItems);
        if ($repeatReplacement) {
            $relationItems[] = $firstRelationItem;
        }
        return array_values($relationItems);
    }

    protected function resolveReplacementRelationsFromRecordList(array $configuration): array
    {
        $sourceItemsList = $configuration['contentPlacementFromCustomRecordtypeList'] ?? '';
        $tables = $this->normalizeTablesList(
            $this->tca['contentPlacementFromCustomRecordtypeList']['TCEforms']['config']['allowed'] ?? ''
        );

        return $this->collectRecordsFromSourceItemsList(
            $sourceItemsList,
            $tables
        );
    }

    protected function resolveReplacementRelationsFromCategoryList(
        array $configuration,
        array $renderableTables
    ): array {
        $categories = $this->normalizeCategoryList(
            $configuration['contentPlacementFromCustomCategoryList'] ?? ''
        );

        return $this->resolveReplacementRelationsFromCategories(
            $categories,
            $renderableTables
        );
    }

    protected function resolveReplacementRelationsFromCategoriesFromPage(
        array $configuration,
        array $renderableTables
    ): array {
        $pageCategoryField = $configuration['contentPlacementFromPageCategoryField'] ?? 'categories';
        $categoryUids = $this->getCategoryUidsFromPage($pageCategoryField);

        return $this->resolveReplacementRelationsFromCategories(
            $categoryUids,
            $renderableTables
        );
    }

    protected function resolveReplacementRelationsFromCategories(
        array $categoryUids,
        array $renderableTables
    ): array {
        $replacementRelations = ['items' => [], 'records' => []];
        foreach ($categoryUids as $categoryUid) {
            $relationsFromCategory = $this->collectRecordsFromSysCategory(
                $categoryUid,
                $renderableTables
            );

            $replacementRelations['items'] = array_merge(
                $replacementRelations['items'],
                $relationsFromCategory['items'] ?? []
            );
            $replacementRelations['records'] = array_replace_recursive(
                $replacementRelations['records'],
                $relationsFromCategory['records'] ?? []
            );
        }

        return $replacementRelations;
    }

    protected function collectRecordsFromSourceItemsList(
        string $sourceItemsList,
        array $tables
    ): array {
        /** @var RelationHandler $relationHandler */
        $relationHandler = GeneralUtility::makeInstance(RelationHandler::class);
        $relationHandler->start($sourceItemsList, implode(',', $tables));
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

    protected function normalizeTablesList(string $tables): array
    {
        $tables = array_unique(GeneralUtility::trimExplode(',', $tables, true));

        return array_filter(
            $tables,
            function (string $table): bool {
                return isset($GLOBALS['TCA'][$table]);
            }
        );
    }

    protected function normalizeCategoryList(string $categories): array
    {
        return array_map(
            'intval',
            GeneralUtility::trimExplode(',', $categories, true)
        );
    }
}
