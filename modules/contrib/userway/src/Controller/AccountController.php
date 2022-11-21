<?php

namespace Drupal\userway\Controller;

use Drupal;
use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Class AccountController.
 */
class AccountController extends ControllerBase {

  /**
   * @return JsonResponse
   * @throws \Exception
   */
  public function index() {
    $accountId = Drupal::request()->query->get('accountId');
    $status = Drupal::request()->query->get('state');
    $database = Drupal::database();
    $query = $database->query("SELECT account, status FROM {userway_data}");
    $dbRecord = $query->fetchAssoc();

    if ($dbRecord) {
      $database->update('userway_data')
        ->fields([
          'status' => $status,
        ])
        ->condition('account', $accountId)
        ->execute();
    }
    else {
      $database->insert('userway_data')
        ->fields([
          'account' => $accountId,
          'status' => $status,
        ])
        ->execute();
    }

    return new JsonResponse(['account' => $accountId, 'status' => $status]);
  }

}
