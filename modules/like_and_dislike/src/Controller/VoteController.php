<?php

/**
 * @file
 * Contains \Drupal\like_and_dislike\Controller\VoteController.
 */

namespace Drupal\like_and_dislike\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\votingapi\Entity\Vote;
use Drupal\votingapi\Entity\VoteType;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Returns responses for Like & Dislikes routes.
 */
class VoteController extends ControllerBase implements ContainerInjectionInterface {

  protected $request;

  /**
   * {@inheritdoc}
   */
  function vote($entity_type_id, $vote_type_id, $entity_id, Request $request) {

    $vote_storage = \Drupal::entityManager()->getStorage('vote');
    $user_votes = $vote_storage->getUserVotes(
      \Drupal::currentUser()->id(),
      $vote_type_id,
      $entity_type_id,
      $entity_id
    );

    if (empty($user_votes)) {
      $vote_type = VoteType::load($vote_type_id);
      $vote = Vote::create(['type' => $vote_type_id]);
      $vote->setVotedEntityId($entity_id);
      $vote->setVotedEntityType($entity_type_id);
      $vote->setValueType($vote_type->getValueType());
      $vote->setValue(1);
      $vote->save();

      drupal_set_message(t('Your :type vote was added.', [
        ':type' => $vote_type_id
      ]));
    }
    else {
      drupal_set_message(
        t('You are not allowed to vote the same way multiple times.')
        , 'warning'
      );
    }
    $url = $request->getUriForPath($request->getPathInfo());
    return new RedirectResponse($url);
  }
}