<?php

namespace Drupal\like_and_dislike;

use Drupal\Core\Access\AccessResultAllowed;
use Drupal\Core\Controller\ControllerBase;

class LikeDislikeAccessController extends ControllerBase {

  public function vote() {
    return AccessResultAllowed::allowed();
  }
}