<?php

/**
 * @file
 * Contains Drupal\like_and_dislike\Form\SettingsForm.
 * Configures administrative settings for Like & Dislike.
 */

namespace Drupal\like_and_dislike\Form;

use Drupal\comment\Entity\CommentType;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\node\Entity\NodeType;

/**
 * Class SettingsForm.
 *
 * @package Drupal\like_and_dislike\Form
 *
 * @ingroup like_and_dislike
 */
class SettingsForm extends ConfigFormBase {

  /**
   * Returns a unique string identifying the form.
   *
   * @return string
   *   The unique string identifying the form.
   */
  public function getFormId() {
    return 'like_and_dislike_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['like_and_dislike.settings'];
  }

  /**
   * Defines the settings form for each entity type bundles.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   *
   * @return array
   *   Form definition array.
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    $config = $this->config('like_and_dislike.settings');

    $form['vote_types_enabled'] = array(
      '#type' => 'fieldset',
      '#collapsible' => TRUE,
      '#collapsed' => FALSE,
      '#title' => t('Entity types with Like & Dislike widgets enabled:'),
      '#description' => t('If you disable any type here, already existing data will remain untouched.'),
    );

    foreach (NodeType::loadMultiple() as $type) {
      $id = 'node_' . $type->id() . '_available';
      $form['vote_types_enabled'][$id] = array(
        '#type' => 'checkbox',
        '#title' => $type->label(),
        '#default_value' => $config->get($id, 0),
      );
    }
    if (\Drupal::moduleHandler()->moduleExists('comment')) {
      foreach (CommentType::loadMultiple() as $type) {
        $id = 'comment_' . $type->id() . '_available';
        $form['vote_types_enabled'][$id] = array(
          '#type' => 'checkbox',
          '#title' => $type->label(),
          '#default_value' => $config->get($id, 0),
        );
      }
    }

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $config = $this->config('like_and_dislike.settings');

    foreach (NodeType::loadMultiple() as $type) {
      $id = 'node_' . $type->id() . '_available';
      $config->set($id, $form_state->getValue($id))->save();
    }

    if (\Drupal::moduleHandler()->moduleExists('comment')) {
      foreach (CommentType::loadMultiple() as $type) {
        $id = 'comment_' . $type->id() . '_available';
        $config->set($id, $form_state->getValue($id))->save();
      }
    }
    parent::submitForm($form, $form_state);
  }
}