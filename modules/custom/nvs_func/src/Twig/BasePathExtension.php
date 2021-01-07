<?php

/**
 * @file
 * Contains \Drupal\nvs_func\Twig\getThemePath.
 */

namespace Drupal\nvs_func\Twig;

/**
 * Provides the NodeViewCount debugging function within Twig templates.
 */
class BasePathExtension extends \Twig_Extension {

  /**
   * {@inheritdoc}
   */
  public function getName() {
    return 'getThemePath';
  }

  /**
   * {@inheritdoc}
   */
  public function getFunctions() {
    return array(
      new \Twig_SimpleFunction('getThemePath', array($this, 'getThemePath'), array(
        'is_safe' => array('html'),
        
      )),
    );
  }

  /**
   * Provides Kint function to Twig templates.
   *
   * Handles 0, 1, or multiple arguments.
   *
   * Code derived from https://github.com/barelon/CgKintBundle.
   *
   * @param Twig_Environment $env
   *   The twig environment instance.
   * @param array $context
   *   An array of parameters passed to the template.
   */
  public function getThemePath($theme_name){
      $path = drupal_get_path('theme', $theme_name);
      return base_path().$path;
  }
  

}
