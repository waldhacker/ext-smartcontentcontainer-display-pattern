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

use TYPO3\CMS\Core\TypoScript\TypoScriptService;

class DisplayPatternRulesResolver extends AbstractResolver
{
    protected array $tca;
    protected array $categoryUidsFromPage = [];

    public function __construct(TypoScriptService $typoScriptService)
    {
        parent::__construct();
        $this->tca = $GLOBALS['TCA']['tt_content']['columns']['pi_flexform']['config']['ds']['*,smartcontentcontainer']['sheets']['sSccSortingConfiguration']['ROOT']['el']['displayPatternRules']['el']['container']['el'] ?? [];
    }

    public function resolveRelations(
        array $processedContentPoolItems,
        array $unprocessedContentPoolItems,
        array $contentPoolRecords,
        array $excludedPageRecords,
        array $configuration,
        array $renderableTables
    ) {
        if (!(bool)($configuration['displayPatternActivate'] ?? false)) {
            return [
                'records' => $contentPoolRecords,
                'items' => $processedContentPoolItems,
            ];
        }
        $this->categoryUidsFromPage = [];
        $repeatDisplayPattern = (bool)($configuration['displayPatternRulesRepeat'] ?? true);
        $maxTotalRecords = $configuration['maxTotalRecords'];
        $configuration['displayPatternRules'] = is_array($configuration['displayPatternRules'])
                                              ? $configuration['displayPatternRules']
                                              : [];

        $configuration['displayPatternRules'] = array_filter(
            $configuration['displayPatternRules'],
            function (array $ruleConfiguration): bool {
                return (bool)($ruleConfiguration['container']['displayPatternRuleDisabled'] ?? false) === false;
            }
        );

        if (empty($configuration['displayPatternRules'])) {
            return [
                'records' => $contentPoolRecords,
                'items' => [],
            ];
        }

        $this->buildAvailableItemsStack(
            $unprocessedContentPoolItems,
            $excludedPageRecords,
            $configuration,
            $renderableTables
        );

        $patternBasedContentPoolItems = [];
        do {
            $processedRelationsFromRound = 0;
            foreach ($configuration['displayPatternRules'] as $ruleConfiguration) {
                $ruleConfiguration = $ruleConfiguration['container'] ?? [];
                $displayPatternScope = $ruleConfiguration['displayPatternRuleScope'] ?? 'custom_recordtype';
                $patternMinTotalRecords = (int)($ruleConfiguration['displayPatternRuleMinTotalRecords'] ?? 1);
                $patternMaxTotalRecords = (int)($ruleConfiguration['displayPatternRuleMaxTotalRecords'] ?? 1);
                $sortingType = $ruleConfiguration['displayPatternRuleSorting'] ?? 'last_updated';

                if ($displayPatternScope === 'custom_recordtype') {
                    $table = $ruleConfiguration['displayPatternRuleFromCustomRecordType'] ?? null;
                    $patternRelationItems = $this->getFromStackForTable($table, $patternMaxTotalRecords);
                    if (count($patternRelationItems) < $patternMinTotalRecords) {
                        $this->addToStack($patternRelationItems);
                        continue;
                    }
                } elseif ($displayPatternScope === 'custom_category') {
                    $categoryUid = (int)($ruleConfiguration['displayPatternRuleFromCustomCategory'] ?? 0);
                    $patternRelationItems = $this->getFromStackForCategory($categoryUid, $patternMaxTotalRecords);
                    if (count($patternRelationItems) < $patternMinTotalRecords) {
                        $this->addToStack($patternRelationItems);
                        continue;
                    }
                } elseif ($displayPatternScope === 'auto_collected_categories_from_page') {
                    $pageCategoryField = $configuration['displayPatternRuleFromPageCategoryField'] ?? 'categories';

                    $patternRelationItems = [];
                    $localStack = [];
                    foreach ($this->categoryUidsFromPage[$pageCategoryField] as $categoryUid) {
                        $patternRelationItemsFromCategory = $this->getFromStackForCategory($categoryUid, $patternMaxTotalRecords);
                        $localStack[] = $patternRelationItemsFromCategory;
                        foreach ($patternRelationItemsFromCategory as $patternRelationItemFromCategory) {
                            if (count($patternRelationItems) > $patternMaxTotalRecords) {
                                $this->addToStack([$patternRelationItemFromCategory]);
                            } else {
                                $patternRelationItems[] = $patternRelationItemFromCategory;
                            }
                        }

                        if (count($patternRelationItems) === $patternMaxTotalRecords) {
                            break;
                        }
                    }

                    if (count($patternRelationItems) < $patternMinTotalRecords) {
                        $this->addToStack($localStack);
                        continue;
                    }
                }

                $patternRelationItems = $this->removeMetaDataFromRelationItems($patternRelationItems);
                $processedRelationsFromRound += count($patternRelationItems);
                $patternRelations = $this->sortRelations(['items' => $patternRelationItems, 'records' => $contentPoolRecords], $sortingType);
                $patternBasedContentPoolItems = array_merge($patternBasedContentPoolItems, $patternRelations['items']);
            }

            if ($processedRelationsFromRound === 0 || count($patternBasedContentPoolItems) >= $maxTotalRecords) {
                $repeatDisplayPattern = false;
            }
        } while ($repeatDisplayPattern);

        return [
            'records' => $contentPoolRecords,
            'items' => $patternBasedContentPoolItems,
        ];
    }

    protected function removeMetaDataFromRelationItems(array $relationItems): array
    {
        return array_map(
            function (array $relationItem): array {
                unset($relationItem['_category']);
                return $relationItem;
            },
            $relationItems
        );
    }

    protected function addToStack(array $relationItems): void
    {
        array_unshift($this->availableItemsStack, ...$relationItems);
    }

    protected function getFromStackForTable(string $table, int $numberOfItems): array
    {
        return $this->getFromStackForScope('table', $table, $numberOfItems);
    }

    protected function getFromStackForCategory(int $categoryUid, int $numberOfItems): array
    {
        return $this->getFromStackForScope('_category', $categoryUid, $numberOfItems);
    }

    protected function getFromStackForScope(string $scope, $value, int $numberOfItems): array
    {
        $result = [];
        foreach ($this->availableItemsStack as $index => $relationItem) {
            if ($relationItem[$scope] === $value) {
                $result[] = $relationItem;
                unset($this->availableItemsStack[$index]);
            }
            if (count($result) === $numberOfItems) {
                break;
            }
        }
        return $result;
    }

    protected function buildAvailableItemsStack(
        array $unprocessedContentPoolItems,
        array $excludedPageRecords,
        array $configuration,
        array $renderableTables
    ): void {
        $categoryUids = [];
        $tables = [];
        foreach ($configuration['displayPatternRules'] as $ruleConfiguration) {
            $ruleConfiguration = $ruleConfiguration['container'] ?? [];
            $displayPatternScope = $ruleConfiguration['displayPatternRuleScope'] ?? 'custom_recordtype';

            if ($displayPatternScope === 'custom_recordtype') {
                $table = $ruleConfiguration['displayPatternRuleFromCustomRecordType'] ?? null;
                if (empty($table) || !in_array($table, $renderableTables, true)) {
                    continue;
                }
                $tables[] = $table;
            } elseif ($displayPatternScope === 'custom_category') {
                $categoryUid = (int)($ruleConfiguration['displayPatternRuleFromCustomCategory'] ?? 0);
                if ($categoryUid <= 0) {
                    continue;
                }
                $categoryUids[$categoryUid] = $categoryUid;
            } elseif ($displayPatternScope === 'auto_collected_categories_from_page') {
                $pageCategoryField = $configuration['displayPatternRuleFromPageCategoryField'] ?? 'categories';
                if (empty($this->categoryUidsFromPage[$pageCategoryField])) {
                    $this->categoryUidsFromPage[$pageCategoryField] = $this->getCategoryUidsFromPage($pageCategoryField);
                }
                foreach ($this->categoryUidsFromPage[$pageCategoryField] as $categoryUid) {
                    $categoryUids[$categoryUid] = $categoryUid;
                }
            }
        }

        $recordIdentifierToCategoryMapping = [];
        foreach ($categoryUids as $categoryUid) {
            $relationsFromCategory = $this->collectRecordsFromSysCategory(
                $categoryUid,
                $renderableTables
            );

            foreach ($relationsFromCategory['items'] as $index => $relationItem) {
                $identifier = $relationItem['table'] . ':' . $relationItem['id'];
                if (isset($recordIdentifierToCategoryMapping[$identifier])) {
                    continue;
                }
                $recordIdentifierToCategoryMapping[$identifier] = $categoryUid;
            }
        }

        $availableItemsStack = $this->reduceRelationItemsByRecordsToExclude(
            $unprocessedContentPoolItems,
            $excludedPageRecords
        );

        $availableItemsStack = array_filter(
            $availableItemsStack,
            function (array $relationItem) use ($tables, $recordIdentifierToCategoryMapping): bool {
                $table = $relationItem['table'];
                $identifier = $relationItem['table'] . ':' . $relationItem['id'];

                return in_array($table, $tables)
                       || array_key_exists($identifier, $recordIdentifierToCategoryMapping);
            }
        );

        foreach ($availableItemsStack as $index => $relationItem) {
            $identifier = $relationItem['table'] . ':' . $relationItem['id'];
            if (!isset($recordIdentifierToCategoryMapping[$identifier])) {
                continue;
            }
            $availableItemsStack[$index]['_category'] = $recordIdentifierToCategoryMapping[$identifier];
        }

        $this->availableItemsStack = array_values($availableItemsStack);
    }
}
