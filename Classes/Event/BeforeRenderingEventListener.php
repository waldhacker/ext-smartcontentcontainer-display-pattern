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

namespace Waldhacker\SmartContentContainerDisplayPattern\Event;

use TYPO3\CMS\Frontend\Event\SmartContentContainer\BeforeRenderingEvent;
use Waldhacker\SmartContentContainerDisplayPattern\Resolver\ContentPlacementsResolver;
use Waldhacker\SmartContentContainerDisplayPattern\Resolver\DisplayPatternRulesResolver;

final class BeforeRenderingEventListener
{
    protected DisplayPatternRulesResolver $displayPatternRulesResolver;
    protected ContentPlacementsResolver $contentPlacementsResolver;

    public function __construct(
        DisplayPatternRulesResolver $displayPatternRulesResolver,
        ContentPlacementsResolver $contentPlacementsResolver
    ) {
        $this->displayPatternRulesResolver = $displayPatternRulesResolver;
        $this->contentPlacementsResolver = $contentPlacementsResolver;
    }

    public function beforeRendering(BeforeRenderingEvent $event): void
    {
        $relations = $this->displayPatternRulesResolver->resolveRelations(
            $event->getProcessedContentPoolItems(),
            $event->getUnprocessedContentPoolItems(),
            $event->getContentPoolRecords(),
            $event->getExcludedPageRecords(),
            $event->getConfiguration(),
            $event->getRenderableTables()
        );

        $relations = $this->contentPlacementsResolver->resolveRelations(
            $relations['items'],
            $event->getUnprocessedContentPoolItems(),
            $relations['records'],
            $event->getExcludedPageRecords(),
            $event->getConfiguration()
        );

        foreach ($relations['records'] as $table => $records) {
            foreach ($records as $record) {
                $event->addContentPoolRecord($table, $record);
            }
        }

        $event->setProcessedContentPoolItems($relations['items']);
    }
}
