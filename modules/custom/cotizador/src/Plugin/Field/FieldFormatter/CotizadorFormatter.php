<?php

/**
 * @file
 * Contains \Drupal\Core\Field\Plugin\Field\FieldFormatter\CotizadorFormatter.
 */

namespace Drupal\cotizador_formatter\Plugin\Field\FieldFormatter;

use Drupal\computed_field\Plugin\Field\FieldFormatter\ComputedPhpFormatterBase;
use Drupal\Core\Field\FieldItemInterface;

/**
 * Plugin implementation of the 'Example PHP' formatter for computed fields.
 *
 * @FieldFormatter(
 *   id = "field_precio_cotizacion",
 *   label = @Translation("Computed PHP (example)"),
 *   field_types = {
 *     "computed_integer",
 *     "computed_decimal",
 *     "computed_float",
 *     "computed_string",
 *     "computed_string_long",
 *   }
 * )
 */
class CotizadorFormatter extends ComputedPhpFormatterBase {

  /**
   * Do something with the value before displaying it.
   *
   * @param mixed $value
   *   The (computed) value of the item.
   * @param \Drupal\Core\Field\FieldItemInterface $item
   *   The item to format for output
   * @param int $delta
   *   The delta value (index) of the item in case of multiple items
   * @param string $langcode
   *   The language code
   * @return mixed
   */
  public function formatItem($value, FieldItemInterface $item, $delta = 0, $langcode = NULL) {
    return $value;
  }
}
