<?php
/**
 * Copyright (c) Enalean, 2019 - Present. All Rights Reserved.
 *
 * This file is a part of Tuleap.
 *
 * Tuleap is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * Tuleap is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Tuleap. If not, see <http://www.gnu.org/licenses/>.
 */

declare(strict_types=1);

use Tuleap\AgileDashboard\REST\v1\AdditionalPanesForMilestoneEvent;
use Tuleap\AgileDashboard\REST\v1\PaneInfoRepresentation;
use Tuleap\Layout\IncludeAssets;
use Tuleap\Request\CollectRoutesEvent;
use Tuleap\Taskboard\AgileDashboard\TaskboardPaneInfo;
use Tuleap\Taskboard\AgileDashboard\TaskboardPaneInfoBuilder;
use Tuleap\Taskboard\Board\BoardPresenterBuilder;
use Tuleap\Taskboard\Column\ColumnPresenterCollectionRetriever;
use Tuleap\Taskboard\Routing\MilestoneExtractor;

require_once __DIR__ . '/../vendor/autoload.php';

// phpcs:ignore PSR1.Classes.ClassDeclaration.MissingNamespace, Squiz.Classes.ValidClassName.NotCamelCaps
class taskboardPlugin extends Plugin
{
    public const NAME = 'taskboard';

    public function __construct($id)
    {
        parent::__construct($id);
        $this->setScope(self::SCOPE_PROJECT);
        bindtextdomain('tuleap-taskboard', __DIR__ . '/../site-content');
    }

    public function getDependencies(): array
    {
        return ['agiledashboard'];
    }

    public function getPluginInfo()
    {
        if (! $this->pluginInfo) {
            $this->pluginInfo = new \Tuleap\Taskboard\Plugin\PluginInfo($this);
        }

        return $this->pluginInfo;
    }

    public function getHooksAndCallbacks(): Collection
    {
        $this->addHook(CollectRoutesEvent::NAME);

        if (defined('AGILEDASHBOARD_BASE_URL')) {
            $this->addHook(AGILEDASHBOARD_EVENT_ADDITIONAL_PANES_ON_MILESTONE);
            $this->addHook(AdditionalPanesForMilestoneEvent::NAME);
        }

        return parent::getHooksAndCallbacks();
    }

    public function routeGet(): \Tuleap\Taskboard\Routing\TaskboardController
    {
        $agiledashboard_plugin = PluginManager::instance()->getPluginByName('agiledashboard');
        if (! $agiledashboard_plugin instanceof AgileDashboardPlugin) {
            throw new RuntimeException('Cannot instantiate Agiledashboard plugin');
        }

        return new \Tuleap\Taskboard\Routing\TaskboardController(
            new MilestoneExtractor(
                $agiledashboard_plugin->getMilestoneFactory(),
                $this->getCardwallOnTopDao(),
                PluginManager::instance(),
                $this
            ),
            TemplateRendererFactory::build()->getRenderer(__DIR__ . '/../templates'),
            $agiledashboard_plugin->getAllBreadCrumbsForMilestoneBuilder(),
            new BoardPresenterBuilder(
                $agiledashboard_plugin->getMilestonePaneFactory(),
                new ColumnPresenterCollectionRetriever(new Cardwall_OnTop_ColumnDao())
            ),
            $agiledashboard_plugin->getIncludeAssets(),
            new IncludeAssets(
                __DIR__ . '/../../../src/www/assets/taskboard/themes',
                '/assets/taskboard/themes'
            ),
            new IncludeAssets(
                __DIR__ . '/../../../src/www/assets/taskboard/scripts',
                '/assets/taskboard/scripts'
            )
        );
    }

    public function collectRoutesEvent(CollectRoutesEvent $event)
    {
        $event->getRouteCollector()->addGroup(
            '/taskboard',
            function (FastRoute\RouteCollector $r) {
                $r->get('/{project_name:[A-z0-9-]+}/{id:\d+}', $this->getRouteHandler('routeGet'));
            }
        );
    }

    /** @see AGILEDASHBOARD_EVENT_ADDITIONAL_PANES_ON_MILESTONE */
    public function agiledashboardEventAdditionalPanesOnMilestone(array $params): void
    {
        $milestone = $params['milestone'];
        assert($milestone instanceof Planning_Milestone);

        $pane = $this->getPaneForMilestone($milestone);
        if ($pane !== null) {
            $params['panes'][] = $pane;
        }
    }

    private function getCardwallOnTopDao(): Cardwall_OnTop_Dao
    {
        return new Cardwall_OnTop_Dao();
    }

    public function additionalPanesForMilestoneEvent(AdditionalPanesForMilestoneEvent $event): void
    {
        $milestone = $event->getMilestone();

        $pane = $this->getPaneForMilestone($milestone);
        if ($pane !== null) {
            $representation = new PaneInfoRepresentation();
            $representation->build($pane);

            $event->add($representation);
        }
    }

    public function getPaneForMilestone(Planning_Milestone $milestone): ?TaskboardPaneInfo
    {
        $pane_builder = new TaskboardPaneInfoBuilder(PluginManager::instance(), $this, $this->getCardwallOnTopDao());

        return $pane_builder->getPaneForMilestone($milestone);
    }
}
