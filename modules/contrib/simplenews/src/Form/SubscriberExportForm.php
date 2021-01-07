<?php

namespace Drupal\simplenews\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\simplenews\Entity\Subscriber;
use Drupal\simplenews\SubscriberInterface;

/**
 * Do a mass subscription for a list of email addresses.
 */
class SubscriberExportForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'simplenews_subscriber_export';
  }

  /**
   * Implement getEmails($states, $subscribed, $newsletters)
   */
  public function getEmails($states, $subscribed, $newsletters) {
    // Build conditions for active state, subscribed state and newsletter
    // selection.
    if (isset($states['active'])) {
      $condition_active[] = SubscriberInterface::ACTIVE;
    }
    if (isset($states['inactive'])) {
      $condition_active[] = SubscriberInterface::INACTIVE;
    }
    if (isset($subscribed['subscribed'])) {
      $condition_subscribed[] = SIMPLENEWS_SUBSCRIPTION_STATUS_SUBSCRIBED;
    }
    if (isset($subscribed['unsubscribed'])) {
      $condition_subscribed[] = SIMPLENEWS_SUBSCRIPTION_STATUS_UNSUBSCRIBED;
    }
    if (isset($subscribed['unconfirmed'])) {
      $condition_subscribed[] = SIMPLENEWS_SUBSCRIPTION_STATUS_UNCONFIRMED;
    }

    // Get emails from the database.
    $query = \Drupal::entityQuery('simplenews_subscriber')
      ->condition('status', $condition_active, 'IN')
      ->condition('subscriptions.status', $condition_subscribed, 'IN')
      ->condition('subscriptions.target_id', (array) $newsletters, 'IN');
    $subscriber_ids = $query->execute();

    $mails = [];
    foreach ($subscriber_ids as $id) {
      $subscriber = Subscriber::load($id);
      $mails[] = $subscriber->getMail();
    }

    // Return comma separated array of emails or empty text.
    if ($mails) {
      return implode(", ", $mails);
    }
    return $this->t('No addresses were found.');
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    // Get sensible default values for the form elements in this form.
    $default['states'] = isset($_GET['states']) ? $_GET['states'] : ['active' => 'active'];
    $default['subscribed'] = isset($_GET['subscribed']) ? $_GET['subscribed'] : ['subscribed' => 'subscribed'];
    $default['newsletters'] = isset($_GET['newsletters']) ? $_GET['newsletters'] : [];

    $form['states'] = [
      '#type' => 'checkboxes',
      '#title' => $this->t('Status'),
      '#options' => [
        'active' => $this->t('Active users'),
        'inactive' => $this->t('Inactive users'),
      ],
      '#default_value' => $default['states'],
      '#description' => $this->t('Subscriptions matching the selected states will be exported.'),
      '#required' => TRUE,
    ];

    $form['subscribed'] = [
      '#type' => 'checkboxes',
      '#title' => $this->t('Subscribed'),
      '#options' => [
        'subscribed' => $this->t('Subscribed to the newsletter'),
        'unconfirmed' => $this->t('Unconfirmed to the newsletter'),
        'unsubscribed' => $this->t('Unsubscribed from the newsletter'),
      ],
      '#default_value' => $default['subscribed'],
      '#description' => $this->t('Subscriptions matching the selected subscription states will be exported.'),
      '#required' => TRUE,
    ];

    $options = simplenews_newsletter_list();
    $form['newsletters'] = [
      '#type' => 'checkboxes',
      '#title' => $this->t('Newsletter'),
      '#options' => $options,
      '#default_value' => $default['newsletters'],
      '#description' => $this->t('Subscriptions matching the selected newsletters will be exported.'),
      '#required' => TRUE,
    ];

    // Get export results and display them in a text area. Only get the results
    // if the form is build after redirect, not after submit.
    $input = $form_state->getUserInput();
    if (isset($_GET['states']) && empty($input)) {
      $form['emails'] = [
        '#type' => 'textarea',
        '#title' => $this->t('Export results'),
        '#cols' => 60,
        '#rows' => 5,
        '#value' => $this->getEmails($_GET['states'], $_GET['subscribed'], $_GET['newsletters']),
      ];
    }

    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Export'),
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $form_values = $form_state->getValues();

    // Get data for query string and redirect back to the current page.
    $options['query']['states'] = array_filter($form_values['states']);
    $options['query']['subscribed'] = array_filter($form_values['subscribed']);
    $options['query']['newsletters'] = array_keys(array_filter($form_values['newsletters']));
    $form_state->setRedirect('simplenews.subscriber_export', [], $options);
  }

}
