<?php

namespace Drupal\content_planner\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Implements DashboardController class.
 */
class DashboardController extends ControllerBase {

  /**
   * The database connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $database;

  /**
   * The config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * The dashboard settings service.
   *
   * @var \Drupal\content_planner\DashboardSettingsService
   */
  protected $dashboardSettingsService;

  /**
   * The dashboard service.
   *
   * @var \Drupal\content_planner\DashboardService
   */
  protected $dashboardService;

  /**
   * The dashboard block plugin manager.
   *
   * @var \Drupal\content_planner\DashboardBlockPluginManager
   */
  protected $dashboardBlockPluginManager;

  /**
   * Defines an interface for a service which has the current account stored.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  protected $currentUser;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {

    $instance = parent::create($container);
    $instance->database = $container->get('database');
    $instance->configFactory = $container->get('config.factory');
    $instance->dashboardSettingsService = $container->get('content_planner.dashboard_settings_service');
    $instance->dashboardService = $container->get('content_planner.dashboard_service');
    $instance->dashboardBlockPluginManager = $container->get('content_planner.dashboard_block_plugin_manager');
    $instance->currentUser = $container->get('current_user');

    return $instance;
  }

  /**
   * Showdashboard.
   *
   * @return array
   *   The content planner dashboard render array.
   */
  public function showDashboard() {

    // Check if Content Calendar or Kanban is enabled.
    if (!$this->dashboardService->isContentCalendarEnabled() &&
      !$this->dashboardService->isContentKanbanEnabled()) {

      $this->messenger()->addMessage($this->t('This dashboard can only be used with Content Calendar or Content Kanban enabled'), 'error');
      return [];
    }

    // Get enabled blocks.
    $blocks = $this->dashboardSettingsService->getBlockConfigurations();

    // If there are no blocks enabled, display error message.
    if (!$blocks) {
      $this->messenger()->addMessage($this->t('Dashboard is not configured yet. Please do this in the Settings tab.'), 'error');
      return [];
    }

    // Get registered Plugins.
    $plugins = $this->dashboardBlockPluginManager->getDefinitions();

    // Build blocks.
    $block_builds = $this->buildBlocks($blocks, $plugins);

    $build = [
      '#theme' => 'content_planner_dashboard',
      '#blocks' => $block_builds,
      '#attached' => [
        'library' => ['content_planner/dashboard'],
      ],
    ];

    return $build;
  }

  /**
   * Build blocks.
   *
   * @param array $blocks
   *   The blocks to render.
   * @param array $plugins
   *   The block plugins.
   *
   * @return array
   *   Array of content block render arrays.
   */
  protected function buildBlocks(array &$blocks, array &$plugins) {

    $block_builds = [];

    // Loop over every enabled block.
    foreach ($blocks as $block_id => $block) {

      // If a Dashboard Block plugin exists.
      if (array_key_exists($block_id, $plugins)) {

        /** @var \Drupal\content_planner\DashboardBlockInterface $instance */
        $instance = $this->dashboardBlockPluginManager->createInstance($block_id, $block);

        if ($this->currentUser->hasPermission('administer content planner dashboard settings')) {
          $has_permission = TRUE;
        }
        else {
          $has_permission = FALSE;
        }

        // Build block render array.
        if ($block_build = $instance->build()) {

          $block_builds[$block_id] = [
            '#theme' => 'content_planner_dashboard_block',
            '#css_id' => str_replace('_', '-', $block_id),
            '#block_id' => $block_id,
            '#name' => (isset($block['title']) && $block['title']) ? $block['title'] : $instance->getName(),
            '#has_permission' => $has_permission,
            '#block' => $block_build,
            '#weight' => $block['weight'],
          ];
        }
      }
    }

    return $block_builds;
  }

}
