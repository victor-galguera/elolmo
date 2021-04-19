<?php

namespace Drupal\splash_screen\Controller;

use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\OpenModalDialogCommand;
use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Form\FormBuilderInterface;
use Drupal\Core\Config\ConfigFactoryInterface;

/**
 * Class CustomModalController for defining pop-up.
 */
class CustomModalController extends ControllerBase {

  /**
   * Define Form builder interface to get form.
   *
   * @var \Drupal\Core\Form\FormBuilderInterface
   */
  protected $formbuilder;

  /**
   * Define config option.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $config;

  /**
   * Constructor to define connection and other.
   */
  public function __construct(FormBuilderInterface $form_builder, ConfigFactoryInterface $config_factory) {
    $this->formbuilder = $form_builder;
    $this->config = $config_factory;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('form_builder'),
      $container->get('config.factory')
    );
  }

  /**
   * Function to generate pop-up.
   */
  public function modal() {

    $form = $this->formbuilder->getForm('\Drupal\splash_screen\Form\PopUpForm');

    $popup_title = $_SESSION['splash_screen_details']['popup_title'];
    if (!($popup_title)) {
      $popup_title = $this->config('system.site')->get('name');
    }
    $options = [
      'dialogClass' => 'popup-dialog-class',
      'width' => '50%',
    ];

    $response = new AjaxResponse();
    $response->addCommand(new OpenModalDialogCommand($popup_title, $form, $options));

    return $response;
  }

}
