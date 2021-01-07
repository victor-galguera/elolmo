<?php

namespace Drupal\simplenews\Form;

use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\simplenews\Entity\Newsletter;
use Drupal\simplenews\Entity\Subscriber;

/**
 * Entity form for Subscriber with common routines.
 */
abstract class SubscriptionsFormBase extends ContentEntityForm {

  /**
   * Submit button ID for creating new subscriptions.
   *
   * @var string
   */
  const SUBMIT_SUBSCRIBE = 'subscribe';

  /**
   * Submit button ID for removing existing subscriptions.
   *
   * @var string
   */
  const SUBMIT_UNSUBSCRIBE = 'unsubscribe';

  /**
   * Submit button ID for creating and removing subscriptions.
   *
   * @var string
   */
  const SUBMIT_UPDATE = 'update';

  /**
   * The newsletters available to select from.
   *
   * @var \Drupal\simplenews\Entity\Newsletter[]
   */
  protected $newsletters;

  /**
   * Allow delete button.
   *
   * @var bool
   */
  protected $allowDelete = FALSE;

  /**
   * Set the newsletters available to select from.
   *
   * Unless called otherwise, all newsletters will be available.
   *
   * @param string[] $newsletters
   *   An array of Newsletter IDs.
   */
  public function setNewsletterIds(array $newsletters) {
    $this->newsletters = Newsletter::loadMultiple($newsletters);
  }

  /**
   * Returns the newsletters available to select from.
   *
   * @return \Drupal\simplenews\Entity\Newsletter[]
   *   The newsletters available to select from, indexed by ID.
   */
  public function getNewsletters() {
    if (!isset($this->newsletters)) {
      $this->setNewsletterIds(array_keys(simplenews_newsletter_get_visible()));
    }
    return $this->newsletters;
  }

  /**
   * Returns the newsletters available to select from.
   *
   * @return string[]
   *   The newsletter IDs available to select from, as an indexed array.
   */
  public function getNewsletterIds() {
    return array_keys($this->getNewsletters());
  }

  /**
   * Convenience method for the case of only one available newsletter.
   *
   * @see ::setNewsletterIds()
   *
   * @return string|null
   *   If there is exactly one newsletter available in this form, this method
   *   returns its ID. Otherwise it returns NULL.
   */
  protected function getOnlyNewsletterId() {
    $newsletters = $this->getNewsletterIds();
    if (count($newsletters) == 1) {
      return array_shift($newsletters);
    }
    return NULL;
  }

  /**
   * Returns a message to display to the user upon successful form submission.
   *
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   Form state object.
   * @param string $op
   *   A string equal to either ::SUBMIT_UPDATE, ::SUBMIT_SUBSCRIBE or
   *   ::SUBMIT_UNSUBSCRIBE.
   * @param bool $confirm
   *   Whether a confirmation mail is sent or not.
   *
   * @return string
   *   A HTML message.
   */
  abstract protected function getSubmitMessage(FormStateInterface $form_state, $op, $confirm);

  /**
   * Returns the renderer for the 'subscriptions' field.
   *
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state object.
   *
   * @return \Drupal\simplenews\SubscriptionWidgetInterface
   *   The widget.
   */
  protected function getSubscriptionWidget(FormStateInterface $form_state) {
    return $this->getFormDisplay($form_state)->getRenderer('subscriptions');
  }

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $this->getSubscriptionWidget($form_state)
      ->setAvailableNewsletterIds(array_keys($this->getNewsletters()));

    $form = parent::form($form, $form_state);

    // Modify UI texts.
    if ($mail = $this->entity->getMail()) {
      $form['subscriptions']['widget']['#title'] = $this->t('Subscriptions for %mail', ['%mail' => $mail]);
      $form['subscriptions']['widget']['#description'] = $this->t('Check the newsletters you want to subscribe to. Uncheck the ones you want to unsubscribe from.');
    }
    else {
      $form['subscriptions']['widget']['#title'] = $this->t('Manage your newsletter subscriptions');
      $form['subscriptions']['widget']['#description'] = $this->t('Select the newsletter(s) to which you want to subscribe or unsubscribe.');
    }

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  protected function actions(array $form, FormStateInterface $form_state) {
    // There are three user groups for subscriptions forms:
    // 1) An authenticated subscriber updating existing subscriptions. The main
    // case is a logged in user, but it could also be an anonymous subscription
    // authenticated by means of a hash. In both cases, the email address is
    // set.
    // 2) An unauthenticated user who enters an email address in the form and
    // requests to subscribe or unsubscribe. In this case the email address
    // is not set.
    // 3) An administrator adding a new subscription. In this case the email
    // address is not set, but there is a logged in user.
    $has_widget = !$this->getSubscriptionWidget($form_state)->isHidden();
    $has_mail = (bool) $this->entity->getMail();

    $actions = parent::actions($form, $form_state);

    if ($has_widget && ($has_mail || \Drupal::currentUser()->isAuthenticated())) {
      // 1a) When authenticated with a widget
      // 3) An administrator adding a new subscription.
      // In both cases, show a single update button.
      $actions[static::SUBMIT_UPDATE] = $actions['submit'];
      $actions[static::SUBMIT_UPDATE]['#submit'][] = '::submitUpdate';
    }
    else {
      // 2) When not authenticated, show subscribe and unsubscribe buttons. The
      // user can check which newsletters to alter.
      //
      // 1b) The final case is when authenticated with no widget which is for a
      // form that applies to a single newsletter. In this case there will be a
      // single button either subscribe or unsubscribe depending on the current
      // subscription state.
      if ($has_widget || !$this->entity->isSubscribed($this->getOnlyNewsletterId())) {
        // Subscribe button.
        $actions[static::SUBMIT_SUBSCRIBE] = $actions['submit'];
        $actions[static::SUBMIT_SUBSCRIBE]['#value'] = $this->t('Subscribe');
        $actions[static::SUBMIT_SUBSCRIBE]['#submit'][] = '::submitSubscribe';
      }

      if ($has_widget || $this->entity->isSubscribed($this->getOnlyNewsletterId())) {
        // Unsubscribe button.
        $actions[static::SUBMIT_UNSUBSCRIBE] = $actions['submit'];
        $actions[static::SUBMIT_UNSUBSCRIBE]['#value'] = $this->t('Unsubscribe');
        $actions[static::SUBMIT_UNSUBSCRIBE]['#submit'][] = '::submitUnsubscribe';
      }
    }

    unset($actions['submit']);
    if (!$this->allowDelete) {
      unset($actions['delete']);
    }

    return $actions;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    $mail = $form_state->getValue(['mail', 0, 'value']);
    // Users should login to manage their subscriptions.
    if (\Drupal::currentUser()->isAnonymous() && $user = user_load_by_mail($mail)) {
      $message = $user->isBlocked() ?
        $this->t('The email address %mail belongs to a blocked user.', ['%mail' => $mail]) :
        $this->t('There is an account registered for the e-mail address %mail. Please log in to manage your newsletter subscriptions.', ['%mail' => $mail]);
      $form_state->setErrorByName('mail', $message);
    }

    // Unless the submit handler is 'update', if the newsletter checkboxes are
    // available, at least one must be checked.
    $update = in_array('::submitUpdate', $form_state->getSubmitHandlers());
    if (!$update && !$this->getSubscriptionWidget($form_state)->isHidden() && !count($form_state->getValue('subscriptions'))) {
      $form_state->setErrorByName('subscriptions', $this->t('You must select at least one newsletter.'));
    }

    parent::validateForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Subclasses try to load an existing subscriber in different ways in
    // buildForm. For anonymous user the email is unknown in buildForm, but here
    // we can try again to load an existing subscriber.
    $mail = $form_state->getValue(['mail', 0, 'value']);
    if ($this->entity->isNew() && $subscriber = Subscriber::loadByMail($mail)) {
      $this->setEntity($subscriber);
    }

    parent::submitForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  protected function copyFormValuesToEntity(EntityInterface $entity, array $form, FormStateInterface $form_state) {
    // Subscriptions are handled later, in the submit callbacks through
    // ::getSelectedNewsletters(). Letting them be copied here would break
    // subscription management.
    $subsciptions_value = $form_state->getValue('subscriptions');
    $form_state->unsetValue('subscriptions');
    parent::copyFormValuesToEntity($entity, $form, $form_state);
    $form_state->setValue('subscriptions', $subsciptions_value);
  }

  /**
   * Submit callback that subscribes to selected newsletters.
   *
   * @param array $form
   *   The form structure.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state object.
   */
  public function submitSubscribe(array $form, FormStateInterface $form_state) {
    /** @var \Drupal\simplenews\Subscription\SubscriptionManagerInterface $subscription_manager */
    $subscription_manager = \Drupal::service('simplenews.subscription_manager');
    foreach ($this->extractNewsletterIds($form_state, TRUE) as $newsletter_id) {
      $subscription_manager->subscribe($this->entity->getMail(), $newsletter_id, NULL, 'website');
    }
    $sent = $subscription_manager->sendConfirmations();
    $this->messenger()->addMessage($this->getSubmitMessage($form_state, static::SUBMIT_SUBSCRIBE, $sent));
  }

  /**
   * Submit callback that unsubscribes from selected newsletters.
   *
   * @param array $form
   *   The form structure.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state object.
   */
  public function submitUnsubscribe(array $form, FormStateInterface $form_state) {
    /** @var \Drupal\simplenews\Subscription\SubscriptionManagerInterface $subscription_manager */
    $subscription_manager = \Drupal::service('simplenews.subscription_manager');
    foreach ($this->extractNewsletterIds($form_state, TRUE) as $newsletter_id) {
      $subscription_manager->unsubscribe($this->entity->getMail(), $newsletter_id, NULL, 'website');
    }
    $sent = $subscription_manager->sendConfirmations();
    $this->messenger()->addMessage($this->getSubmitMessage($form_state, static::SUBMIT_UNSUBSCRIBE, $sent));
  }

  /**
   * Submit callback that (un)subscribes to newsletters based on selection.
   *
   * @param array $form
   *   The form structure.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state object.
   */
  public function submitUpdate(array $form, FormStateInterface $form_state) {
    // We first subscribe, then unsubscribe. This prevents deletion of
    // subscriptions when unsubscribed from the newsletter.
    /** @var \Drupal\simplenews\Subscription\SubscriptionManagerInterface $subscription_manager */
    $subscription_manager = \Drupal::service('simplenews.subscription_manager');
    foreach ($this->extractNewsletterIds($form_state, TRUE) as $newsletter_id) {
      $subscription_manager->subscribe($this->entity->getMail(), $newsletter_id, FALSE, 'website');
    }

    if (!$this->entity->isNew()) {
      foreach ($this->extractNewsletterIds($form_state, FALSE) as $newsletter_id) {
        $subscription_manager->unsubscribe($this->entity->getMail(), $newsletter_id, FALSE, 'website');
      }
    }
    $this->messenger()->addMessage($this->getSubmitMessage($form_state, static::SUBMIT_UPDATE, FALSE));
  }

  /**
   * Extracts selected/deselected newsletters IDs from the subscriptions widget.
   *
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   Form state object.
   * @param bool $selected
   *   Whether to extract selected (TRUE) or deselected (FALSE) newsletter IDs.
   *
   * @return string[]
   *   IDs of selected/deselected newsletters.
   */
  protected function extractNewsletterIds(FormStateInterface $form_state, $selected) {
    return $this->getSubscriptionWidget($form_state)
      ->extractNewsletterIds($form_state->getValue('subscriptions'), $selected);
  }

}
