<?php

/**
 * @file
 * Contains \Drupal\video_embed_media\UpgradeManager.
 */

namespace Drupal\video_embed_media;

/**
 * Upgrades existing media_entity_embedded_video bundles.
 */
interface UpgradeManagerInterface {

  /**
   * Upgrade the existing media_entity_embedded_video bundles.
   */
  public function upgrade();

}
