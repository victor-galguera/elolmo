<?php

namespace Drupal\splash_screen\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Url;
use Drupal\Core\Link;
use Drupal\Core\Database\Connection;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class SplashScreenController.
 *
 * @package Drupal\splash_screen\Controller
 */
class SplashScreenController extends ControllerBase {

  /**
   * Database connection variable.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $database;

  /**
   * Constructor to define connection and other.
   */
  public function __construct(Connection $connection) {
    $this->database = $connection;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('database')
    );
  }

  /**
   * Function to get pop-up content.
   */
  public function getContent() {
    // First we'll tell the user what's going on. This content can be found
    // in the twig template file: templates/description.html.twig.
    // @todo: Set up links to create nodes and point to devel module.
    $build = [
      'description' => [
        '#theme' => 'splash_screen_description',
        '#description' => 'foo',
        '#attributes' => [],
      ],
    ];
    return $build;
  }

  /**
   * Display.
   *
   * @return string
   *   Return Hello string.
   */
  public function display() {
    // Create table header.
    $header_table = [
      'id' => $this->t('SrNo'),
      'name' => $this->t('Name'),
      'lang' => $this->t('Language'),
      'opt' => $this->t('operations'),
      'opt1' => $this->t('operations'),
    ];

    // Select records from table.
    $query = $this->database->select('splash_screen', 's');
    $query->fields('s', ['oid', 'name', 'lang']);
    $results = $query->execute()->fetchAll();
    $rows = [];
    foreach ($results as $data) {
      $delete = Url::fromUserInput('/admin/content/splash-screen/delete/' . $data->oid);
      $edit   = Url::fromUserInput('/admin/content/splash-screen/add?num=' . $data->oid);

      // Print the data from table.
      $rows[] = [
        'id' => $data->oid,
        'name' => $data->name,
        'lang' => $data->lang,
        'Delete' => Link::fromTextAndUrl('Delete', $delete)->toString(),
        'Edit' => Link::fromTextAndUrl('Edit', $edit)->toString(),
      ];

    }
    // Display data in site.
    $form['table'] = [
      '#type' => 'table',
      '#header' => $header_table,
      '#rows' => $rows,
      '#empty' => $this->t('No users found'),
    ];

    return $form;

  }

}
