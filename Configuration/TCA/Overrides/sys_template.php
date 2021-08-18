<?php

defined('TYPO3_MODE') or die();

call_user_func(function () {
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile(
        'smartcontentcontainer_display_pattern',
        'Configuration/TypoScript',
        'Smart content container display pattern configuration'
    );
});
