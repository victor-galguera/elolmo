<?php

namespace Drupal\galleryslider\Plugin\views\style;

use Drupal\Core\Annotation\Translation;
use Drupal\Core\Form\FormStateInterface;
use Drupal\views\Annotation\ViewsStyle;
use Drupal\views\Plugin\views\style\StylePluginBase;

/**
 * Style plugin to render each item into simple carousel.
 *
 * @ingroup views_style_plugins
 *
 * @ViewsStyle(
 *   id = "galleryslider",
 *   title = @Translation("gallerySlider"),
 *   help = @Translation("Displays rows as gallerySlider."),
 *   theme = "galleryslider_views",
 *   display_types = {"normal"}
 * )
 */
class GallerySlider extends StylePluginBase {

  /**
   * Does the style plugin allows to use style plugins.
   *
   * @var bool
   */
  protected $usesRowPlugin = TRUE;

  /**
   * Does the style plugin support custom css class for the rows.
   *
   * @var bool
   */
  protected $usesRowClass = TRUE;

  /**
   * Set default options.
   */
  protected function defineOptions() {
    $options = parent::defineOptions();

    $settings = _galleryslider_default_settings();
    foreach ($settings as $k => $v) {
      $options[$k] = ['default' => $v];
    }
    return $options;
  }

  /**
   * Render the given style.
   *
   * @param $form
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   */
  public function buildOptionsForm(&$form, FormStateInterface $form_state) {
    parent::buildOptionsForm($form, $form_state);
    $image_grid = [
      'true' => $this->t('Show'),
    ];
    $form['imgGrid'] = [
      '#title' => $this->t('Show Small pics at bottom'),
      '#type' => 'select',
      '#default_value' => $this->options['imgGrid'],
      '#empty_option' => $this->t('Nothing'),
      '#options' => $image_grid,
    ];
    $menu_type = [
      'grid' => $this->t('Grid'),
      'nav' => $this->t('Nav'),
    ];
    $form['menu'] = [
      '#title' => $this->t('Menu Types'),
      '#type' => 'select',
      '#default_value' => $this->options['menu'],
      '#empty_option' => $this->t('Nothing'),
      '#options' => $menu_type,
    ];
    $form['speed'] = [
      '#type' => 'number',
      '#title' => $this->t('Transition Speed'),
      '#default_value' => $this->options['speed'],
      '#description' => $this->t('Transition speed in milliseconds.'),
    ];
  }

}
