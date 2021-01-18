<?php

/**
 * @file
 * Contains \Drupal\video_embed_media\Tests\UpgradePathTest.
 */

namespace Drupal\video_embed_media\Tests;

use Drupal\video_embed_field\Tests\WebTestBase;

/**
 * Test the upgrade path from media_entity_embedded_video.
 *
 * @group video_embed_media
 */
class UpgradePathTest extends WebTestBase {

  /**
   * Disable strict checking for media_entity_embeddable_video.
   *
   * @var bool
   */
  protected $strictConfigSchema = FALSE;

  /**
   * Modules to install.
   *
   * @var array
   */
  public static $modules = [
    'video_embed_field',
    'media_entity_embeddable_video',
    'media_entity',
    'field_ui',
    'node',
    'image',
    'text',
  ];

  /**
   * Test the upgrade path.
   */
  public function testMediaBundleCreation() {
    $this->drupalLogin($this->adminUser);

    // Create a media_entity_embeddable_video bundle and field.
    $this->drupalGet('admin/structure/media/add');
    $this->drupalPostForm(NULL, [
      'label' => 'embeddable Video Bundle',
      'id' => 'embeddable_bundle',
      'type' => 'embeddable_video',
    ], 'Save media bundle');
    $this->assertText('The media bundle embeddable Video Bundle has been added.');
    $this->drupalPostForm('admin/structure/media/manage/embeddable_bundle/fields/add-field', array(
      'new_storage_type' => 'string',
      'label' => 'Video Text Field',
      'field_name' => 'video_text_field',
    ), 'Save and continue');
    $this->drupalPostForm(NULL, [], 'Save field settings');
    $this->drupalPostForm(NULL, [], 'Save settings');
    $this->drupalPostForm('admin/structure/media/manage/embeddable_bundle', ['type_configuration[embeddable_video][source_field]' => 'field_video_text_field'], 'Save media bundle');
    $this->drupalPostForm('media/add/embeddable_bundle', [
      'field_video_text_field[0][value]' => 'https://www.youtube.com/watch?v=gnERPdAiuSo',
      'name[0][value]' => 'Test Media Entity',
    ], 'Save');

    // Install video_embed_field.
    $this->drupalPostForm('admin/modules', [
      'modules[Video Embed Field][video_embed_media][enable]' => '1',
    ], 'Install');

    $this->assertUpgradeComplete();

    // Uninstall the module and ensure everything is still okay.
    $this->drupalPostForm('admin/modules/uninstall', [
      'uninstall[media_entity_embeddable_video]' => TRUE,
    ], 'Uninstall');
    $this->drupalPostForm(NULL, [], 'Uninstall');

    $this->assertUpgradeComplete();
  }

  /**
   * Assert the upgrade was successful.
   */
  protected function assertUpgradeComplete() {
    // Ensure the new type is selected.
    $this->drupalGet('admin/structure/media/manage/embeddable_bundle');
    $this->assertTrue(!empty($this->xpath('//option[@value="video_embed_field" and @selected="selected"]')), 'The media type was updated.');
    // Ensure the media entity has updated values.
    $this->drupalGet('media/1/edit');
    $this->assertFieldByName('field_video_text_field[0][value]', 'https://www.youtube.com/watch?v=gnERPdAiuSo', 'Field values were copied.');
  }

}
