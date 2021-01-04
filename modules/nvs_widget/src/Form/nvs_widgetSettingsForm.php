<?php 
/**
 * @file
 * Contains \Drupal\nvs_widget\Form\nvs_widgetSettingsForm
 */
namespace Drupal\nvs_widget\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Configure nvs_widget settings for this site.
 */
class nvs_widgetSettingsForm extends ConfigFormBase {
  /** 
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'nvs_widget_admin_settings';
  }

  /** 
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'nvs_widget.settings',
    ];
  }

  /** 
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('nvs_widget.settings');
    //sss
    $form['twitter'] = array(
      '#type' => 'details',
      '#title' => $this->t('Twitter Settings'),
      '#open' => FALSE,
    );
    $form['twitter']['customer_key']  = array(
      '#type' => 'textfield',
      '#required' => TRUE,
      '#title' => $this->t('Twitter customer key'),
      '#description' => $this->t('Eg: Blkg0rBkgrP0crGEBovF4h8kc'),
      '#default_value' => $config->get('twitter_customer_key'),

    );
    $form['twitter']['customer_secret']  = array(
      '#type' => 'textfield',
      '#required' => TRUE,
      '#title' => $this->t('Twitter customer secret'),
      '#description' => $this->t('Eg: qbP52o8I1xwEYq3EVunRWQHdsIKKib4hYCThJbbYeQJmPnGjYu'),
      '#default_value' => $config->get('twitter_customer_secret'),

    );
    $form['twitter']['access_token']  = array(
      '#type' => 'textfield',
      '#required' => TRUE,
      '#title' => $this->t('Twitter access token'),
      '#description' => $this->t('Eg: 739112964477747200-CxT2HD2L0A91n3v43UNkGvbRO190ulU'),
      '#default_value' => $config->get('twitter_access_token'),

    );
    $form['twitter']['access_token_secret']  = array(
      '#type' => 'textfield',
      '#required' => TRUE,
      '#title' => $this->t('Twitter access token secret'),
      '#description' => $this->t('Eg: BClFShD7BM5Us7V4s72kLYInGs3jBJh3ZcBA22bZ58YO4'),
      '#default_value' => $config->get('twitter_access_token_secret'),
    );
    //ins
    $form['instagram'] = array(
      '#type' => 'details',
      '#title' => $this->t('Instagram Settings'),
      '#open' => FALSE,
    );
    $form['instagram']['ins_access_token']  = array(
      '#type' => 'textfield',
      '#required' => TRUE,
      '#title' => $this->t('Instagram access token'),
      //username sirdoan94
      '#description' => $this->t('Eg: 2248030459.ba4c844.945e592a31114c19a0f4668b1821e61b'),
      '#default_value' => $config->get('instagram_access_token'),

    );
    return parent::buildForm($form, $form_state);
  }

  /** 
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $config = \Drupal::service('config.factory')->getEditable('nvs_widget.settings');
    $config->set('twitter_customer_key', $form_state->getValue('customer_key'))
      ->save();
    $config->set('twitter_customer_secret', $form_state->getValue('customer_secret'))
      ->save();
    $config->set('twitter_access_token', $form_state->getValue('access_token'))
      ->save();
    $config->set('twitter_access_token_secret', $form_state->getValue('access_token_secret'))
      ->save();
    //instagram
    $config->set('instagram_access_token', $form_state->getValue('ins_access_token'))
      ->save(); 
    parent::submitForm($form, $form_state);
  }
}