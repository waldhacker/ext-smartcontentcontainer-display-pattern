<?php

$GLOBALS['TCA']['tt_content']['columns']['pi_flexform']['config']['ds']['*,smartcontentcontainer'] = array_replace_recursive(
    $GLOBALS['TCA']['tt_content']['columns']['pi_flexform']['config']['ds']['*,smartcontentcontainer'] ?? [],
    [
        'sheets' => [
            'sSccSortingConfiguration' => [
                'ROOT' => [
                    'TCEforms' => [
                        'sheetTitle' => 'LLL:EXT:smartcontentcontainer_display_pattern/Resources/Private/Language/locallang_tca.xlf:sheets.sSccSortingConfiguration',
                    ],
                    'type' => 'array',
                    'el' => [
                        'sorting' => [
                            'TCEforms' => [
                                'displayCond' => 'FIELD:displayPatternActivate:=:0',
                            ],
                        ],
                        'displayPatternActivate' => [
                            'TCEforms' => [
                                'label' => 'LLL:EXT:smartcontentcontainer_display_pattern/Resources/Private/Language/locallang_tca.xlf:displayPatternActivate',
                                'onChange' => 'reload',
                                'config' => [
                                    'type' => 'check',
                                    'renderType' => 'checkboxToggle',
                                    'items' => [
                                        [
                                            0 => '',
                                            1 => '',
                                        ],
                                    ],
                                    'default' => '0',
                                ],
                            ],
                        ],
                        'displayPatternRules' => [
                            'section' => '1',
                            'type' => 'array',
                            'title' => 'LLL:EXT:smartcontentcontainer_display_pattern/Resources/Private/Language/locallang_tca.xlf:displayPatternRules',
                            'displayCond' => [
                                'AND' => [
                                    'REC:NEW:FALSE',
                                    'FIELD:displayPatternActivate:=:1'
                                ],
                            ],
                            'el' => [
                                'container' => [
                                    'title' => 'LLL:EXT:smartcontentcontainer_display_pattern/Resources/Private/Language/locallang_tca.xlf:rule',
                                    'type' => 'array',
                                    'el' => [
                                        'displayPatternRuleTitle' => [
                                            'TCEforms' => [
                                                'label' => 'LLL:EXT:smartcontentcontainer_display_pattern/Resources/Private/Language/locallang_tca.xlf:ruleTitle',
                                                'config' => [
                                                    'type' => 'input',
                                                    'eval' => 'trim',
                                                    'default' => '',
                                                ],
                                            ],
                                        ],
                                        'displayPatternRuleDisabled' => [
                                            'TCEforms' => [
                                                'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.enabled',
                                                'config' => [
                                                    'type' => 'check',
                                                    'renderType' => 'checkboxToggle',
                                                    'items' => [
                                                        [
                                                            0 => '',
                                                            1 => '',
                                                            'invertStateDisplay' => true
                                                        ]
                                                    ],
                                                ]
                                            ],
                                        ],
                                        'displayPatternRuleScope' => [
                                            'TCEforms' => [
                                                'label' => 'LLL:EXT:smartcontentcontainer_display_pattern/Resources/Private/Language/locallang_tca.xlf:displayPatternRuleScope',
                                                'onChange' => 'reload',
                                                'config' => [
                                                    'type' => 'select',
                                                    'renderType' => 'selectSingle',
                                                    'items' => [
                                                        [
                                                            'LLL:EXT:smartcontentcontainer_display_pattern/Resources/Private/Language/locallang_tca.xlf:custom_recordtype',
                                                            'custom_recordtype',
                                                        ],
                                                        [
                                                            'LLL:EXT:smartcontentcontainer_display_pattern/Resources/Private/Language/locallang_tca.xlf:custom_category',
                                                            'custom_category',
                                                        ],
                                                        [
                                                            'LLL:EXT:smartcontentcontainer_display_pattern/Resources/Private/Language/locallang_tca.xlf:auto_collected_categories_from_page',
                                                            'auto_collected_categories_from_page',
                                                        ],
                                                    ],
                                                    'default' => 'custom_recordtype',
                                                ],
                                            ],
                                        ],
                                        'displayPatternRuleFromCustomRecordType' => [
                                            'TCEforms' => [
                                                'label' => 'LLL:EXT:smartcontentcontainer_display_pattern/Resources/Private/Language/locallang_tca.xlf:displayPatternRuleFromCustomRecordType',
                                                'displayCond' => 'FIELD:displayPatternRuleScope:=:custom_recordtype',
                                                'config' => [
                                                    'type' => 'select',
                                                    'renderType' => 'selectMultipleSideBySide',
                                                    'fieldControl' => [
                                                        'addRecord' => [
                                                            'disabled' => '1',
                                                        ],
                                                        'editPopup' => [
                                                            'disabled' => '1',
                                                        ],
                                                        'listModule' => [
                                                            'disabled' => '1',
                                                        ],
                                                    ],
                                                    'minitems' => 1,
                                                    'maxitems' => 1,
                                                    'itemsProcFunc' => \Waldhacker\SmartContentContainerDisplayPattern\Hooks\TcaItemsProcessorFunctions::class . '->populateAvailableRenderableTables',
                                                ],
                                            ],
                                        ],
                                        'displayPatternRuleFromCustomCategory' => [
                                            'TCEforms' => [
                                                'label' => 'LLL:EXT:smartcontentcontainer_display_pattern/Resources/Private/Language/locallang_tca.xlf:displayPatternRuleFromCustomCategory',
                                                'displayCond' => 'FIELD:displayPatternRuleScope:=:custom_category',
                                                'config' => [
                                                    'type' => 'select',
                                                    'renderType' => 'selectMultipleSideBySide',
                                                    'foreign_table' => 'sys_category',
                                                    'foreign_table_where' => ' AND sys_category.sys_language_uid IN (-1, 0)',
                                                    'fieldControl' => [
                                                        'addRecord' => [
                                                            'disabled' => '1',
                                                        ],
                                                        'editPopup' => [
                                                            'disabled' => '1',
                                                        ],
                                                        'listModule' => [
                                                            'disabled' => '1',
                                                        ],
                                                    ],
                                                    'minitems' => 1,
                                                    'maxitems' => 1,
                                                ],
                                            ],
                                        ],
                                        'displayPatternRuleFromPageCategoryField' => [
                                            'TCEforms' => [
                                                'label' => 'LLL:EXT:smartcontentcontainer_display_pattern/Resources/Private/Language/locallang_tca.xlf:displayPatternRuleFromPageCategoryField',
                                                'displayCond' => 'FIELD:displayPatternRuleScope:=:auto_collected_categories_from_page',
                                                'config' => [
                                                    'type' => 'select',
                                                    'renderType' => 'selectSingle',
                                                    'size' => 1,
                                                    'minitems' => 1,
                                                    'maxitems' => 1,
                                                    'itemsProcFunc' => \TYPO3\CMS\Core\Hooks\TcaItemsProcessorFunctions::class . '->populateAvailableCategoryFields',
                                                    'itemsProcConfig' => [
                                                        'table' => 'pages'
                                                    ],
                                                ],
                                            ],
                                        ],
                                        'displayPatternRuleMinTotalRecords' => [
                                            'TCEforms' => [
                                                'label' => 'LLL:EXT:smartcontentcontainer_display_pattern/Resources/Private/Language/locallang_tca.xlf:displayPatternRuleMinTotalRecords',
                                                'displayCond' => 'FIELD:displayPatternRuleScope:REQ:true',
                                                'description' => 'field description',
                                                'config' => [
                                                    'type' => 'input',
                                                    'eval' => 'int',
                                                    'default' => '1',
                                                ],
                                            ],
                                        ],
                                        'displayPatternRuleMaxTotalRecords' => [
                                            'TCEforms' => [
                                                'label' => 'LLL:EXT:smartcontentcontainer_display_pattern/Resources/Private/Language/locallang_tca.xlf:displayPatternRuleMaxTotalRecords',
                                                'displayCond' => 'FIELD:displayPatternRuleScope:REQ:true',
                                                'description' => 'field description',
                                                'config' => [
                                                    'type' => 'input',
                                                    'eval' => 'int',
                                                    'default' => '1',
                                                ],
                                            ],
                                        ],
                                        'displayPatternRuleSorting' => [
                                            'TCEforms' => [
                                                'label' => 'LLL:EXT:smartcontentcontainer_display_pattern/Resources/Private/Language/locallang_tca.xlf:displayPatternRuleSorting',
                                                'displayCond' => 'FIELD:displayPatternRuleScope:REQ:true',
                                                'config' => [
                                                    'type' => 'select',
                                                    'renderType' => 'selectSingle',
                                                    'items' => [
                                                        [
                                                            'LLL:EXT:smartcontentcontainer_display_pattern/Resources/Private/Language/locallang_tca.xlf:last_updated',
                                                            'last_updated',
                                                        ],
                                                        [
                                                            'LLL:EXT:smartcontentcontainer_display_pattern/Resources/Private/Language/locallang_tca.xlf:random',
                                                            'random',
                                                        ],
                                                    ],
                                                    'default' => 'last_updated',
                                                ],
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                        'displayPatternRulesRepeat' => [
                            'TCEforms' => [
                                'label' => 'LLL:EXT:smartcontentcontainer_display_pattern/Resources/Private/Language/locallang_tca.xlf:displayPatternRulesRepeat',
                                'displayCond' => 'FIELD:displayPatternActivate:=:1',
                                'config' => [
                                    'type' => 'check',
                                    'renderType' => 'checkboxToggle',
                                    'items' => [
                                        [
                                            0 => '',
                                            1 => '',
                                        ],
                                    ],
                                    'default' => 1,
                                ],
                            ],
                        ],
                    ],
                ],
            ],

            'sSccContentPlacements' => [
                'ROOT' => [
                    'TCEforms' => [
                        'sheetTitle' => 'LLL:EXT:smartcontentcontainer_display_pattern/Resources/Private/Language/locallang_tca.xlf:sheets.sSccContentPlacements',
                    ],
                    'type' => 'array',
                    'el' => [
                        'contentPlacements' => [
                            'section' => '1',
                            'type' => 'array',
                            'title' => 'LLL:EXT:smartcontentcontainer_display_pattern/Resources/Private/Language/locallang_tca.xlf:contentPlacements',
                            'displayCond' => 'REC:NEW:FALSE',
                            'el' => [
                                'container' => [
                                    'title' => 'LLL:EXT:smartcontentcontainer_display_pattern/Resources/Private/Language/locallang_tca.xlf:rule',
                                    'type' => 'array',
                                    'el' => [
                                        'contentPlacementTitle' => [
                                            'TCEforms' => [
                                                'label' => 'LLL:EXT:smartcontentcontainer_display_pattern/Resources/Private/Language/locallang_tca.xlf:ruleTitle',
                                                'config' => [
                                                    'type' => 'input',
                                                    'eval' => 'trim',
                                                    'default' => '',
                                                ],
                                            ],
                                        ],
                                        'contentPlacementDisabled' => [
                                            'TCEforms' => [
                                                'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.enabled',
                                                'config' => [
                                                    'type' => 'check',
                                                    'renderType' => 'checkboxToggle',
                                                    'items' => [
                                                        [
                                                            0 => '',
                                                            1 => '',
                                                            'invertStateDisplay' => true
                                                        ]
                                                    ],
                                                ]
                                            ],
                                        ],
                                        'contentPlacementScope' => [
                                            'TCEforms' => [
                                                'label' => 'LLL:EXT:smartcontentcontainer_display_pattern/Resources/Private/Language/locallang_tca.xlf:contentPlacementScope',
                                                'onChange' => 'reload',
                                                'config' => [
                                                    'type' => 'select',
                                                    'renderType' => 'selectSingle',
                                                    'items' => [
                                                        [
                                                            'LLL:EXT:smartcontentcontainer_display_pattern/Resources/Private/Language/locallang_tca.xlf:custom_record_list',
                                                            'custom_record_list',
                                                        ],
                                                        [
                                                            'LLL:EXT:smartcontentcontainer_display_pattern/Resources/Private/Language/locallang_tca.xlf:custom_category_list',
                                                            'custom_category_list',
                                                        ],
                                                        [
                                                            'LLL:EXT:smartcontentcontainer_display_pattern/Resources/Private/Language/locallang_tca.xlf:auto_collected_categories_from_page',
                                                            'auto_collected_categories_from_page',
                                                        ]
                                                    ],
                                                    'default' => 'custom_record_list',
                                                ],
                                            ],
                                        ],
                                        'contentPlacementFromCustomRecordtypeList' => [
                                            'TCEforms' => [
                                                'label' => 'LLL:EXT:smartcontentcontainer_display_pattern/Resources/Private/Language/locallang_tca.xlf:contentPlacementFromCustomRecordtypeList',
                                                'displayCond' => 'FIELD:contentPlacementScope:=:custom_record_list',
                                                'config' => [
                                                    'type' => 'group',
                                                    'internal_type' => 'db',
                                                    'allowed' => 'tt_content',
                                                    'size' => 5,
                                                    'maxitems' => 200,
                                                    'minitems' => 0
                                                ]
                                            ],
                                        ],

                                        'contentPlacementFromCustomCategoryList' => [
                                            'TCEforms' => [
                                                'label' => 'LLL:EXT:smartcontentcontainer_display_pattern/Resources/Private/Language/locallang_tca.xlf:contentPlacementFromCustomCategoryList',
                                                'displayCond' => 'FIELD:contentPlacementScope:=:custom_category_list',
                                                'config' => [
                                                    'type' => 'select',
                                                    'renderType' => 'selectMultipleSideBySide',
                                                    'foreign_table' => 'sys_category',
                                                    'foreign_table_where' => ' AND sys_category.sys_language_uid IN (-1, 0)',
                                                    'fieldControl' => [
                                                        'addRecord' => [
                                                            'disabled' => '1',
                                                        ],
                                                        'editPopup' => [
                                                            'disabled' => '1',
                                                        ],
                                                        'listModule' => [
                                                            'disabled' => '1',
                                                        ],
                                                    ],
                                                ],
                                            ],
                                        ],
                                        'contentPlacementFromPageCategoryField' => [
                                            'TCEforms' => [
                                                'label' => 'LLL:EXT:smartcontentcontainer_display_pattern/Resources/Private/Language/locallang_tca.xlf:contentPlacementFromPageCategoryField',
                                                'displayCond' => 'FIELD:contentPlacementScope:=:auto_collected_categories_from_page',
                                                'config' => [
                                                    'type' => 'select',
                                                    'renderType' => 'selectSingle',
                                                    'size' => 1,
                                                    'minitems' => 0,
                                                    'maxitems' => 1,
                                                    'itemsProcFunc' => \TYPO3\CMS\Core\Hooks\TcaItemsProcessorFunctions::class . '->populateAvailableCategoryFields',
                                                    'itemsProcConfig' => [
                                                        'table' => 'pages'
                                                    ],
                                                ],
                                            ],
                                        ],
                                        'contentPlacementRecordListSorting' => [
                                            'TCEforms' => [
                                                'label' => 'LLL:EXT:smartcontentcontainer_display_pattern/Resources/Private/Language/locallang_tca.xlf:contentPlacementRecordListSorting',
                                                'displayCond' => 'FIELD:contentPlacementScope:=:custom_record_list',
                                                'config' => [
                                                    'type' => 'select',
                                                    'renderType' => 'selectSingle',
                                                    'items' => [
                                                        [
                                                            'LLL:EXT:smartcontentcontainer_display_pattern/Resources/Private/Language/locallang_tca.xlf:last_updated',
                                                            'last_updated',
                                                        ],
                                                        [
                                                            'LLL:EXT:smartcontentcontainer_display_pattern/Resources/Private/Language/locallang_tca.xlf:contentPlacementRecordListSorting.collection_sorting',
                                                            'collection_sorting',
                                                        ],
                                                        [
                                                            'LLL:EXT:smartcontentcontainer_display_pattern/Resources/Private/Language/locallang_tca.xlf:random',
                                                            'random',
                                                        ],
                                                    ],
                                                    'default' => 'last_updated',
                                                ],
                                            ],
                                        ],
                                        'contentPlacementCategorySorting' => [
                                            'TCEforms' => [
                                                'label' => 'LLL:EXT:smartcontentcontainer_display_pattern/Resources/Private/Language/locallang_tca.xlf:contentPlacementCategorySorting',
                                                'displayCond' => 'FIELD:contentPlacementScope:!=:custom_record_list',
                                                'config' => [
                                                    'type' => 'select',
                                                    'renderType' => 'selectSingle',
                                                    'items' => [
                                                        [
                                                            'the update date of the records',
                                                            'last_updated',
                                                        ],
                                                        [
                                                            'random',
                                                            'random',
                                                        ],
                                                    ],
                                                    'default' => 'last_updated',
                                                ],
                                            ],
                                        ],
                                        'contentPlacementPosition' => [
                                            'TCEforms' => [
                                                'label' => 'LLL:EXT:smartcontentcontainer_display_pattern/Resources/Private/Language/locallang_tca.xlf:contentPlacementPosition',
                                                'config' => [
                                                    'type' => 'input',
                                                    'eval' => 'int',
                                                    'default' => '1',
                                                ],
                                            ],
                                        ],
                                        'contentPlacementRepetitionType' => [
                                            'TCEforms' => [
                                                'label' => 'LLL:EXT:smartcontentcontainer_display_pattern/Resources/Private/Language/locallang_tca.xlf:contentPlacementRepetitionType',
                                                'config' => [
                                                    'type' => 'check',
                                                    'renderType' => 'checkboxToggle',
                                                    'items' => [
                                                        [
                                                            0 => '',
                                                            1 => '',
                                                        ],
                                                    ],
                                                    'default' => '0',
                                                ],
                                            ],
                                        ],
                                        'contentPlacementReplace' => [
                                            'TCEforms' => [
                                                'label' => 'LLL:EXT:smartcontentcontainer_display_pattern/Resources/Private/Language/locallang_tca.xlf:contentPlacementReplace',
                                                'config' => [
                                                    'type' => 'check',
                                                    'renderType' => 'checkboxToggle',
                                                    'items' => [
                                                        [
                                                            0 => '',
                                                            1 => '',
                                                        ],
                                                    ],
                                                    'default' => '0',
                                                ],
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ]
);
