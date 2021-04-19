<?php

namespace Drupal\splash_screen\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\CloseModalDialogCommand;

/**
 * Class SplashScreenForm.
 *
 * @package Drupal\splash_screen\Form
 */
class PopUpForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'popup_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['#attached']['library'][] = 'core/drupal.dialog.ajax';
    $form['#attached']['library'][] = 'splash_screen/splash_screen_libr';

    $splash_screen_details = $_SESSION['splash_screen_details'];

    $content = '';
    if (is_array($splash_screen_details) && count($splash_screen_details) > 0) {
      $content = '<div class="content">
										<div class="body">
											<div class="so_top">
												<span class="so_popup_close"><i class="fa fa-close"></i></span>												
												<div class="so_content">' . $splash_screen_details['splash_screen_markup_value'] . '</div>
											</div>		
										</div>
									</div>';
    }
    else {
      $content .= '<div>' . $this->t('No Content') . '</divdiv>';
    }
    $form['content'] = [
      '#type' => 'markup',
      '#markup' => $content,
    ];
    $cookies_name = 'showPopup' . $splash_screen_details['oid'];
    if (isset($_COOKIE[$cookies_name]) && $splash_screen_details['page'] == $_COOKIE[$cookies_name]) {
      $default_value_check = ['yes'];
    }
    else {
      if ($splash_screen_details['cookies_default']) {
        $default_value_check = ['yes'];
      }
      else {
        $default_value_check = ['no'];
      }
    }

    if (isset($splash_screen_details['cookies']) && intval($splash_screen_details['cookies']) > 0) {
      $form['cookies'] = [
        '#type' => 'checkboxes',
        '#options' => ['yes' => 'Do not show again'],
        '#default_value' => $default_value_check,
        '#attributes' => ['id' => 'splash-screen-set-cookie'],
      ];
    }
    $form['actions']['#type'] = 'actions';
    $form['actions']['submit'] = [
      '#type' => 'button',
      '#value' => (isset($splash_screen_details['btn_accept']) && $splash_screen_details['btn_accept']) ? $splash_screen_details['btn_accept'] : $this->t('Ok'),
      '#button_type' => 'primary',
      '#ajax' => [
        'callback' => '::splashScreenPopupAjaxCallback',
      ],
    ];
    if (isset($splash_screen_details['btn_decline']) && $splash_screen_details['btn_decline']) {
      $form['actions']['cancel'] = [
        '#type' => 'button',
        '#value' => isset($splash_screen_details['btn_decline']) ? $splash_screen_details['btn_decline'] : $this->t('Cencel'),
        '#button_type' => 'primary',
        '#ajax' => [
          'callback' => '::splashScreenPopupCancelAjaxCallback',
        ],
      ];
    }

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

  }

  /**
   * Implements callback for Ajax event on color selection.
   *
   * @param array $form
   *   From render array.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   Current state of form.
   *
   * @return array
   *   Color selection section of the form.
   */
  public function splashScreenPopupAjaxCallback(array $form, FormStateInterface $form_state) {

    $splash_screen_details = $_SESSION['splash_screen_details'];
    if (isset($splash_screen_details['cookies']) && intval($splash_screen_details['cookies']) > 0) {
      $hour = '24';
      $second = '3600';
      if ($splash_screen_details['cookies_lifetime']) {
        $cl = $splash_screen_details['cookies_lifetime'];
        $cookies_lifetime_day = $cl * $hour * $second;
        $cookies_lifetime = time() + $cookies_lifetime_day;
      }
      else {
        $cookies_lifetime_day = $hour * $second;
        $cookies_lifetime = time() + $cookies_lifetime_day;
      }
      $field = $form_state->getValues();
      $cookies_check = $field['cookies'];

      $page_deails = $splash_screen_details['page'];
      $cookies_name = 'showPopup' . $splash_screen_details['oid'];
      if ($cookies_check['yes'] == 'yes' && $cookies_check['yes'] != '0') {
        // 24 hours.
        setcookie($cookies_name, $page_deails, $cookies_lifetime, '/');
      }
      else {
        unset($_COOKIE[$cookies_name]);
      }
    }
    $command = new CloseModalDialogCommand();
    $response = new AjaxResponse();
    $response->addCommand($command);

    return $response;

  }

  /**
   * Implements callback for Ajax event on cancel selection.
   *
   * @param array $form
   *   From render array.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   Current state of form.
   *
   * @return array
   *   Color selection section of the form.
   */
  public function splashScreenPopupCancelAjaxCallback(array $form, FormStateInterface $form_state) {

    $command = new CloseModalDialogCommand();
    $response = new AjaxResponse();
    $response->addCommand($command);
    return $response;

  }

}
