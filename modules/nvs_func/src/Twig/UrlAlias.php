<?php

/**
 * @file
 * Contains \Drupal\nvs_func\Twig\UrlAlias.
 */

namespace Drupal\nvs_func\Twig;

/**
 * Provides the NodeViewCount debugging function within Twig templates.
 */
class UrlAlias extends \Twig_Extension {

  /**
   * {@inheritdoc}
   */
  public function getName() {
    return 'getPathAlias';
  }

  /**
   * {@inheritdoc}
   */
  public function getFunctions() {
    return array(
      new \Twig_SimpleFunction('getPathAlias', array($this, 'getPathAlias'), array(
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
  public function getPathAlias($url_ori){
  	$path_alias = \Drupal::service('path.alias_manager')->getAliasByPath($url_ori);
	$url = base_path().ltrim($path_alias,'/');
      
    return $url;
  }
  

}
