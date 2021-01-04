<?php

/**
 * @file
 * Contains \Drupal\nvs_func\Twig\PregMatchImgExtension.
 */

namespace Drupal\nvs_func\Twig;

class PregMatchImgExtension extends \Twig_Extension {

  /**
   * {@inheritdoc}
   */
  public function getName() {
    return 'getSrcFromImgTag';
  }

  /**
   * {@inheritdoc}
   */
  public function getFunctions() {
    return array(
      new \Twig_SimpleFunction('getSrcFromImgTag', array($this, 'getSrcFromImgTag'), array(
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
  public function getSrcFromImgTag($img){
    //$img = strip_tags($img,'<img>');
    preg_match("<img.*?src=[\"\"'](?<url>.*?)[\"\"'].*?>",$img,$out);
    $output = $out['url'];
    return $output;
  }
  

}
