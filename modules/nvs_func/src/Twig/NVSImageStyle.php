<?php

/**
 * @file
 * Contains \Drupal\nvs_func\Twig\NVSImageStyle.
 */

namespace Drupal\nvs_func\Twig;
use Drupal\image\Entity\ImageStyle;
/**
 * Provides the NodeViewCount debugging function within Twig templates.
 */
class NVSImageStyle extends \Twig_Extension {

  /**
   * {@inheritdoc}
   */
  public function getName() {
    return 'getUrlByImageStyle';
  }

  /**
   * {@inheritdoc}
   */
  public function getFunctions() {
    return array(
      new \Twig_SimpleFunction('getUrlByImageStyle', array($this, 'getUrlByImageStyle'), array(
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
  public function getUrlByImageStyle($original_image_uri, $style_name){
	
  	$style = ImageStyle::load($style_name);

  	//$uri = $style->buildUri($original_image_uri);
  	$url = $style->buildUrl($original_image_uri);
    return $url;
  }
}
