<?php

namespace Drupal\userway\Controller;

use Drupal;
use Drupal\Core\Controller\ControllerBase;

/**
 * Class ConfigurationController.
 */
class ConfigurationController extends ControllerBase {

  /**
   * Index
   */
  public function index() {

    $widgetState = 'false';
    $widgetAccount = '';
    $database = Drupal::database();
    $query = $database->query('SELECT account, status FROM {userway_data} LIMIT 1');
    $widgetData = $query->fetchAssoc();
    $protocol = strtolower(substr($_SERVER["SERVER_PROTOCOL"], 0, strpos($_SERVER["SERVER_PROTOCOL"], '/'))) . '://';
    $rootUrl = rtrim(\Drupal\Core\Url::fromUserInput('/', ['absolute' => TRUE])
      ->toString(), '/');
    $rootUrl = str_replace('http://', $protocol, $rootUrl);

    if (isset($widgetData['account']) && isset($widgetData['status'])) {
      $widgetState = $widgetData['status'] === '1' ? 'true' : 'false';
      $widgetAccount = $widgetData['account'] ?: '';
    }

    $frameUrl = "https://api.userway.org/api/apps/drupal?storeUrl={$rootUrl}";

    if ($widgetAccount !== '') {
      $frameUrl .= '&account_id=' . $widgetAccount;
      $frameUrl .= '&active=' . $widgetState;
    }

    return [
      '#theme' => 'userway',
      '#attached' => [
        'library' => [
          'userway/userway',
        ],
      ],
      '#url' => $frameUrl,
      '#rootUrl' => $rootUrl,
    ];
  }

}
