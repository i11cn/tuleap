<?php
/**
 * Copyright (c) Enalean, 2015 - Present. All Rights Reserved.
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

use Tuleap\AgileDashboard\BacklogItem\RemainingEffortValueRetriever;
use Tuleap\AgileDashboard\BreadCrumbDropdown\AdministrationCrumbBuilder;
use Tuleap\AgileDashboard\BreadCrumbDropdown\AgileDashboardCrumbBuilder;
use Tuleap\AgileDashboard\BreadCrumbDropdown\MilestoneCrumbBuilder;
use Tuleap\AgileDashboard\BreadCrumbDropdown\VirtualTopMilestoneCrumbBuilder;
use Tuleap\AgileDashboard\FormElement\Burnup\CountElementsModeChecker;
use Tuleap\AgileDashboard\FormElement\Burnup\ProjectsCountModeDao;
use Tuleap\AgileDashboard\FormElement\BurnupFieldRetriever;
use Tuleap\AgileDashboard\Milestone\AllBreadCrumbsForMilestoneBuilder;
use Tuleap\AgileDashboard\Milestone\ParentTrackerRetriever;
use Tuleap\AgileDashboard\MonoMilestone\MonoMilestoneBacklogItemDao;
use Tuleap\AgileDashboard\MonoMilestone\MonoMilestoneItemsFinder;
use Tuleap\AgileDashboard\MonoMilestone\ScrumForMonoMilestoneChecker;
use Tuleap\AgileDashboard\MonoMilestone\ScrumForMonoMilestoneDao;
use Tuleap\AgileDashboard\PermissionsPerGroup\AgileDashboardJSONPermissionsRetriever;
use Tuleap\AgileDashboard\PermissionsPerGroup\AgileDashboardPermissionsRepresentationBuilder;
use Tuleap\AgileDashboard\PermissionsPerGroup\PlanningPermissionsRepresentationBuilder;
use Tuleap\AgileDashboard\Planning\MilestoneBurndownFieldChecker;
use Tuleap\AgileDashboard\Planning\ScrumPlanningFilter;
use Tuleap\AgileDashboard\REST\v1\BacklogItemRepresentationFactory;
use Tuleap\Cardwall\BackgroundColor\BackgroundColorBuilder;
use Tuleap\Project\Admin\PermissionsPerGroup\PermissionPerGroupUGroupRepresentationBuilder;
use Tuleap\Tracker\FormElement\Field\ListFields\Bind\BindDecoratorRetriever;
use Tuleap\Tracker\RecentlyVisited\VisitRecorder;
use Tuleap\Tracker\Semantic\Timeframe\SemanticTimeframeBuilder;
use Tuleap\Tracker\Semantic\Timeframe\SemanticTimeframeDao;
use Tuleap\Tracker\Semantic\Timeframe\TimeframeBuilder;
use Tuleap\Tracker\Semantic\Timeframe\TimeframeChecker;

class AgileDashboardRouterBuilder
{
    /** PluginFactory */
    private $plugin_factory;
    /**
     * @var Planning_MilestonePaneFactory
     */
    private $pane_factory;
    /**
     * @var VisitRecorder
     */
    private $visit_recorder;
    /**
     * @var AllBreadCrumbsForMilestoneBuilder
     */
    private $all_bread_crumbs_for_milestone_builder;

    public function __construct(
        PluginFactory $plugin_factory,
        Planning_MilestonePaneFactory $pane_factory,
        VisitRecorder $visit_recorder,
        AllBreadCrumbsForMilestoneBuilder $all_bread_crumbs_for_milestone_builder
    ) {
        $this->plugin_factory                         = $plugin_factory;
        $this->pane_factory                           = $pane_factory;
        $this->visit_recorder                         = $visit_recorder;
        $this->all_bread_crumbs_for_milestone_builder = $all_bread_crumbs_for_milestone_builder;
    }

    /**
     * @param Codendi_Request $request
     *
     * @return AgileDashboardRouter
     */
    public function build(Codendi_Request $request)
    {
        $plugin = $this->plugin_factory->getPluginByName(
            AgileDashboardPlugin::PLUGIN_NAME
        );

        $planning_factory                                = $this->getPlanningFactory();
        $milestone_factory                               = $this->getMilestoneFactory();
        $milestone_representation_builder                = $this->getMilestoneRepresentationBuilder();
        $paginated_backlog_items_representations_builder = $this->getPaginatedBacklogItemsRepresentationsBuilder();

        $pane_presenter_builder_factory = $this->getPanePresenterBuilderFactory($milestone_factory);

        $top_milestone_pane_factory = $this->getTopMilestonePaneFactory(
            $request,
            $pane_presenter_builder_factory,
            $plugin,
            $milestone_representation_builder,
            $paginated_backlog_items_representations_builder
        );

        $service_crumb_builder        = new AgileDashboardCrumbBuilder($plugin->getPluginPath());
        $admin_crumb_builder          = new AdministrationCrumbBuilder($plugin->getPluginPath());
        $milestone_controller_factory = new Planning_MilestoneControllerFactory(
            ProjectManager::instance(),
            $milestone_factory,
            $this->pane_factory,
            $top_milestone_pane_factory,
            $service_crumb_builder,
            new VirtualTopMilestoneCrumbBuilder($plugin->getPluginPath()),
            $this->visit_recorder,
            $this->all_bread_crumbs_for_milestone_builder
        );

        $mono_milestone_checker = new ScrumForMonoMilestoneChecker(new ScrumForMonoMilestoneDao(), $planning_factory);

        $ugroup_manager = new UGroupManager();
        return new AgileDashboardRouter(
            $plugin,
            $milestone_factory,
            $planning_factory,
            $milestone_controller_factory,
            ProjectManager::instance(),
            new AgileDashboard_XMLFullStructureExporter(
                EventManager::instance(),
                $this
            ),
            $this->getKanbanManager(),
            $this->getConfigurationManager(),
            $this->getKanbanFactory(),
            new PlanningPermissionsManager(),
            $this->getHierarchyChecker(),
            $mono_milestone_checker,
            new ScrumPlanningFilter($mono_milestone_checker, $planning_factory),
            new AgileDashboardJSONPermissionsRetriever(
                new AgileDashboardPermissionsRepresentationBuilder(
                    $ugroup_manager, $planning_factory,
                    new PlanningPermissionsRepresentationBuilder(
                        new PlanningPermissionsManager(),
                        PermissionsManager::instance(),
                        new PermissionPerGroupUGroupRepresentationBuilder(
                            $ugroup_manager
                        )
                    )
                )
            ),
            $service_crumb_builder,
            $admin_crumb_builder,
            new TimeframeChecker(
                Tracker_FormElementFactory::instance()
            ),
            new CountElementsModeChecker(new ProjectsCountModeDao())
        );
    }

    /**
     * @return Planning_VirtualTopMilestonePaneFactory
     */
    private function getTopMilestonePaneFactory(
        $request,
        $pane_presenter_builder_factory,
        Plugin $plugin,
        AgileDashboard_Milestone_MilestoneRepresentationBuilder $milestone_representation_builder,
        AgileDashboard_BacklogItem_PaginatedBacklogItemsRepresentationsBuilder $paginated_backlog_items_representations_builder
    ) {
        return new Planning_VirtualTopMilestonePaneFactory(
            $request,
            $pane_presenter_builder_factory,
            $plugin->getThemePath(),
            $milestone_representation_builder,
            $paginated_backlog_items_representations_builder
        );
    }

    private function getPanePresenterBuilderFactory($milestone_factory)
    {
        return new AgileDashboard_Milestone_Pane_PanePresenterBuilderFactory(
            $this->getBacklogFactory(),
            $this->getBacklogItemPresenterCollectionFactory($milestone_factory),
            new BurnupFieldRetriever(Tracker_FormElementFactory::instance()),
            EventManager::instance()
        );
    }

    /**
     * Builds a new PlanningFactory instance.
     *
     * @return PlanningFactory
     */
    private function getPlanningFactory()
    {
        return PlanningFactory::build();
    }

    /**
     * Builds a new Planning_MilestoneFactory instance.
     * @return Planning_MilestoneFactory
     */
    private function getMilestoneFactory()
    {
        $form_element_factory = Tracker_FormElementFactory::instance();

        return new Planning_MilestoneFactory(
            $this->getPlanningFactory(),
            $this->getArtifactFactory(),
            $form_element_factory,
            $this->getTrackerFactory(),
            $this->getStatusCounter(),
            new PlanningPermissionsManager(),
            new AgileDashboard_Milestone_MilestoneDao(),
            $this->getMonoMileStoneChecker(),
            new TimeframeBuilder(
                $form_element_factory,
                new SemanticTimeframeBuilder(new SemanticTimeframeDao(), $form_element_factory),
                new \BackendLogger()
            ),
            new MilestoneBurndownFieldChecker($form_element_factory)
        );
    }

    private function getBacklogItemPresenterCollectionFactory($milestone_factory)
    {
        $form_element_factory = Tracker_FormElementFactory::instance();

        return new AgileDashboard_Milestone_Backlog_BacklogItemCollectionFactory(
            new AgileDashboard_BacklogItemDao(),
            $this->getArtifactFactory(),
            $form_element_factory,
            $milestone_factory,
            $this->getPlanningFactory(),
            new AgileDashboard_Milestone_Backlog_BacklogItemPresenterBuilder(),
            new RemainingEffortValueRetriever(
                $form_element_factory
            )
        );
    }

    /**
     * @return AgileDashboard_KanbanManager
     */
    private function getKanbanManager()
    {
        return new AgileDashboard_KanbanManager(
            new AgileDashboard_KanbanDao(),
            $this->getTrackerFactory(),
            $this->getHierarchyChecker()
        );
    }

    /**
     * @return AgileDashboard_HierarchyChecker
     */
    private function getHierarchyChecker()
    {
        return new AgileDashboard_HierarchyChecker(
            $this->getPlanningFactory(),
            $this->getKanbanFactory(),
            $this->getTrackerFactory()
        );
    }

    /**
     * @return TrackerFactory
     */
    private function getTrackerFactory()
    {
        return TrackerFactory::instance();
    }

    /**
     * @return AgileDashboard_ConfigurationManager
     */
    private function getConfigurationManager()
    {
        return new AgileDashboard_ConfigurationManager(
            new AgileDashboard_ConfigurationDao()
        );
    }

    /**
     * @return AgileDashboard_KanbanFactory
     */
    private function getKanbanFactory()
    {
        return new AgileDashboard_KanbanFactory(
            $this->getTrackerFactory(),
            new AgileDashboard_KanbanDao()
        );
    }

    private function getArtifactFactory()
    {
        return Tracker_ArtifactFactory::instance();
    }

    private function getStatusCounter()
    {
        return new AgileDashboard_Milestone_MilestoneStatusCounter(
            new AgileDashboard_BacklogItemDao(),
            new Tracker_ArtifactDao(),
            $this->getArtifactFactory()
        );
    }

    private function getMilestoneRepresentationBuilder()
    {
        return new AgileDashboard_Milestone_MilestoneRepresentationBuilder(
            $this->getMilestoneFactory(),
            $this->getBacklogFactory(),
            EventManager::instance(),
            $this->getMonoMileStoneChecker(),
            new ParentTrackerRetriever($this->getPlanningFactory())
        );
    }

    private function getPaginatedBacklogItemsRepresentationsBuilder()
    {
        $color_builder = new BackgroundColorBuilder(new BindDecoratorRetriever());
        $item_factory  = new BacklogItemRepresentationFactory(
            $color_builder,
            UserManager::instance(),
            EventManager::instance()
        );

        return new AgileDashboard_BacklogItem_PaginatedBacklogItemsRepresentationsBuilder(
            $item_factory,
            $this->getBacklogItemCollectionFactory(),
            $this->getBacklogFactory()
        );
    }

    private function getBacklogItemCollectionFactory()
    {
        $form_element_factory = Tracker_FormElementFactory::instance();

        return new AgileDashboard_Milestone_Backlog_BacklogItemCollectionFactory(
            new AgileDashboard_BacklogItemDao(),
            $this->getArtifactFactory(),
            $form_element_factory,
            $this->getMilestoneFactory(),
            $this->getPlanningFactory(),
            new AgileDashboard_Milestone_Backlog_BacklogItemBuilder(),
            new RemainingEffortValueRetriever(
                $form_element_factory
            )
        );
    }

    private function getBacklogFactory()
    {
        return new AgileDashboard_Milestone_Backlog_BacklogFactory(
            new AgileDashboard_BacklogItemDao(),
            $this->getArtifactFactory(),
            $this->getPlanningFactory(),
            $this->getMonoMileStoneChecker(),
            $this->getMonoMilestoneItemsFinder()
        );
    }

    private function getMonoMileStoneChecker()
    {
        return new ScrumForMonoMilestoneChecker(new ScrumForMonoMilestoneDao(), $this->getPlanningFactory());
    }

    private function getMonoMilestoneItemsFinder()
    {
        return new MonoMilestoneItemsFinder(
            new MonoMilestoneBacklogItemDao(),
            $this->getArtifactFactory()
        );
    }
}
