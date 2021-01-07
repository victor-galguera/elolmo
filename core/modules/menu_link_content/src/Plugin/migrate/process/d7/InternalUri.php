<?php

namespace Drupal\menu_link_content\Plugin\migrate\process\d7;

use Drupal\menu_link_content\Plugin\migrate\process\LinkUri;

/**
 * Processes an internal uri into an 'internal:' or 'entity:' URI.
 *
 * @deprecated in drupal:8.2.0 and is removed from drupal:9.0.0. Use
 * \Drupal\menu_link_content\Plugin\migrate\process\LinkUri instead.
 *
 * @see https://www.drupal.org/node/2761389
 */
class InternalUri extends LinkUri {}
