<?php

namespace Drupal\splash_screen\Form;

use Drupal\user\Entity\User;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Component\Utility\UrlHelper;
use Drupal\Core\Path\AliasManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Database\Connection;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\Path\PathValidator;
use Drupal\Core\Config\ConfigFactoryInterface;

/**
 * Class SplashScreenForm.
 *
 * @package Drupal\splash_screen\Form
 */
class SplashScreenForm extends FormBase {

  /**
   * Define use account data.
   *
   * @var Drupal\Core\Session\AccountInterface
   */
  protected $account;

  /**
   * Define path alias.
   *
   * @var \Drupal\Core\Path\AliasManagerInterface
   */
  protected $aliasManager;

  /**
   * Database connection variable.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $database;

  /**
   * Include the messenger service.
   *
   * @var \Drupal\Core\Messenger\MessengerInterface
   */
  protected $messenger;

  /**
   * Include the path_validator service.
   *
   * @var \Drupal\Core\Path\PathValidator
   */
  protected $pathValidator;

  /**
   * Define config option.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $config;

  /**
   * Constructor to define connection.
   */
  public function __construct(Connection $connection, AliasManagerInterface $alias_manager, AccountInterface $account, MessengerInterface $messenger, PathValidator $path_validator, ConfigFactoryInterface $config_factory) {
    $this->database = $connection;
    $this->aliasManager = $alias_manager;
    $this->account = $account;
    $this->messenger = $messenger;
    $this->pathValidator = $path_validator;
    $this->config = $config_factory;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('database'),
      $container->get('path.alias_manager'),
      $container->get('current_user'),
      $container->get('messenger'),
      $container->get('path.validator'),
      $container->get('config.factory')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'splash_screen_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    $record = [];
    if (isset($_GET['num'])) {
      $query = $this->database->select('splash_screen', 's')
        ->condition('oid', $_GET['num'])
        ->fields('s');
      $record = $query->execute()->fetchAssoc();
    }
    $form['name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Name'),
      '#description' => $this->t('Name that displays in the admin interface.'),
      '#maxlength' => 255,
      '#required' => TRUE,
      '#weight' => -20,
      '#default_value' => (isset($record['name']) && $_GET['num']) ? $record['name'] : '',
    ];
    $form['popup_title'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Splash Screen Popup Title'),
      '#description' => $this->t('Splash Screen title display on header section of pop-up box.'),
      '#maxlength' => 255,
      '#weight' => -20,
      '#default_value' => (isset($record['popup_title']) && $_GET['num']) ? $record['popup_title'] : '',
    ];
    $form['splash_screen_markup'] = [
      '#type' => 'text_format',
      '#title' => $this->t('Message or Markup'),
      '#description' => $this->t('Enter the text or markup to present inside the Splash Screen; <b>HTML is allowed</b>'),
      '#weight' => 20,
      '#default_value' => (isset($record['splash_screen_markup_value']) && $_GET['num']) ? $record['splash_screen_markup_value'] : '',
    ];
    $form['data'] = [
      '#tree' => TRUE,
      '#weight' => 40,
    ];

    $form['data']['links'] = [
      '#type' => 'details',
      '#title' => $this->t('Buttons'),
      '#open' => FALSE,
    ];

    $form['data']['links']['yes']['text'] = [
      '#type' => 'textfield',
      '#description' => $this->t('Text to appear on the accept button.'),
      '#title' => $this->t('Accept Button'),
      '#size' => 25,
      '#default_value' => (isset($record['btn_accept']) && $_GET['num']) ? $record['btn_accept'] : '',
    ];
    $form['data']['links']['no']['text'] = [
      '#type' => 'textfield',
      '#description' => $this->t('Text to appear on the decline button. When the user clicks this button, the Splash Screens will disappear.'),
      '#title' => $this->t('Decline Button'),
      '#size' => 25,
      '#default_value' => (isset($record['btn_decline']) && $_GET['num']) ? $record['btn_decline'] : '',
    ];

    $form['data']['storage'] = [
      '#type' => 'details',
      '#title' => $this->t('Repeat Viewing'),
      '#open' => FALSE,
    ];
    $form['data']['storage']['cookies']['enabled'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Use cookies?'),
      '#description' => $this->t("Should this Splash Screens use cookies to know when it's been previously viewed by a user?"),
      '#default_value' => (isset($record['cookies']) && $_GET['num']) ? $record['cookies'] : '',
    ];

    $form['data']['storage']['cookies']['fs_cookies'] = [
      '#type' => 'details',
      '#title' => $this->t('Advanced (cookies)'),
      '#open' => FALSE,
      '#states' => [
        'visible' => [
          ':input[name="data[storage][cookies][enabled]"]' => ['checked' => TRUE],
        ],
      ],
    ];
    $form['data']['storage']['cookies']['fs_cookies']['lifetime'] = [
      '#type' => 'textfield',
      '#description' => $this->t('How many days before the cookie expires? Set to 0 (zero) to expire when the current browser session ends.'),
      '#title' => $this->t('Cookie Lifespan in Days?'),
      '#required' => FALSE,
      '#size' => 10,
      '#default_value' => (isset($record['cookies_lifetime']) && $_GET['num']) ? $record['cookies_lifetime'] : '0',

    ];
    $form['data']['storage']['cookies']['fs_cookies']['default'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Set cookie by default?'),
      '#description' => $this->t("When checked, the user will not have to check the <em>Don't show again</em> option; it will already be checked for them.  Uncheck here for the opposite to be true."),
      '#default_value' => (isset($record['cookies_default']) && $_GET['num']) ? $record['cookies_default'] : '',
    ];

    // Per-path visibility.
    $form['data']['audience'] = [
      '#type' => 'details',
      '#title' => $this->t('Pages/Audience'),
      '#open' => FALSE,
    ];

    $form['data']['audience']['path'] = [
      '#type' => 'textfield',
      '#description' => $this->t('Enter the URL to where you want to display this popup. Please enter "<strong>node</strong>" only if you want to display this to home page.'),
      '#title' => $this->t('Enter URL'),
      '#size' => 100,
      '#default_value' => (isset($record['page']) && $_GET['num']) ? $record['page'] : '',
    ];

    $form['other'] = [
      '#type' => 'details',
      '#title' => $this->t('Active/Inactive'),
      '#open' => FALSE,
      '#weight' => 100,
    ];

    $form['other']['status'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Active'),
      '#description' => $this->t('To disable a Splash Screens and hide it from users, uncheck this box.'),
      '#default_value' => (isset($record['status']) && $_GET['num']) ? $record['status'] : '',
    ];

    $form['actions'] = [
      '#type' => 'container',
      '#attributes' => ['class' => ['form-actions']],
      '#weight' => 400,
    ];

    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Save !entity'),
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    // Validate the cookie lifetime.
    $field = $form_state->getValues();
    if (!empty($field['data']['audience']['path'])) {
      // Check that the link exists.
      $record = [];
      $query = $this->database->select('splash_screen', 's')
        ->condition('page', $field['data']['audience']['path'])
        ->fields('s');
      $record = $query->execute()->fetchAssoc();

      if (is_array($record) && $record['page'] == $field['data']['audience']['path']) {
        if (!(splash_screen_starts_with($field['data']['audience']['path'], "/"))) {
          $form_state->setErrorByName('data][audience][path', $this->t('Please enter URL start from /.'));
        }
      }
      else {
        if (!(splash_screen_starts_with($field['data']['audience']['path'], "/"))) {
          $form_state->setErrorByName('data][audience][path', $this->t('Please enter URL start from /.'));
        }
        else {
          $source = UrlHelper::isValid($field['data']['audience']['path'], TRUE);
          if (!$source) {
            $normal_path = $this->aliasManager->getPathByAlias($field['data']['audience']['path']);
            $source = $this->pathValidator->isValid($normal_path);
          }
          if (!$source) {
            $form_state->setErrorByName('data][audience][path', $this->t('The path / url does not exist.'));
          }
        }

      }
    }
    else {
      $form_state->setErrorByName('data][audience][path', $this->t('Please enter a valid accept path / url.'));
    }

    if ($field['data']['storage']['cookies']['enabled']) {
      $lifetime = $field['data']['storage']['cookies']['fs_cookies']['lifetime'];
      if (!is_numeric($lifetime) || $lifetime < 0) {
        $form_state->setErrorByName('data][storage', $this->t('Cookie lifespan must be zero or a positive number when using cookies.'));
      }
    }

    parent::validateForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

    $field = $form_state->getValues();

    $name = $field['name'];
    $title = $field['popup_title'];
    if ($title) {
      $popup_title = $title;
    }
    else {
      $popup_title = $this->config('system.site')->get('name');
    }
    $splash_screen_markup = $field['splash_screen_markup']['value'];

    // Buttons Details.
    $links = $field['data']['links'];
    $btn_accept = $links['yes']['text'];
    $btn_decline = $links['no']['text'];

    // Cookies.
    $storage = $field['data']['storage'];
    $cookies = $storage['cookies']['enabled'];
    $cookies_lifetime = $storage['cookies']['fs_cookies']['lifetime'];
    $cookies_default = $storage['cookies']['fs_cookies']['default'];

    // Pages/Audience.
    $audience = $field['data']['audience'];
    $page_visibility = $audience['path'];

    // Active/Inactive.
    $status = $field['status'];

    $path = $this->aliasManager->getPathByAlias($page_visibility);
    $page_visibility = $path;

    $langcode = $this->account->getPreferredLangcode();
    $user = User::load($this->account->id());
    $field_arr = [
      'name'   => $name,
      'popup_title'   => $popup_title,
      'splash_screen_markup_value' => $splash_screen_markup,
      'btn_accept' => $btn_accept,
      'btn_decline' => $btn_decline,
      'cookies' => $cookies,
      'cookies_lifetime' => $cookies_lifetime,
      'cookies_default' => $cookies_default,
      'page' => $page_visibility,
      'lang' => $langcode,
      'status' => $status,
      'uid' => $user->get('uid')->value,
    ];

    if (isset($_GET['num'])) {
      $field_update = $field_arr;
      $this->database->update('splash_screen')
        ->fields($field_update)
        ->condition('oid', $_GET['num'])
        ->execute();
      $this->messenger->addMessage('succesfully updated');
      $form_state->setRedirect('splash_screen.display_splash_screen');

    }
    else {
      $field_add = $field_arr;
      $this->database->insert('splash_screen')
        ->fields($field_add)
        ->execute();
      $this->messenger->addMessage('succesfully saved');
      $form_state->setRedirect('splash_screen.display_splash_screen');
    }

  }

}
