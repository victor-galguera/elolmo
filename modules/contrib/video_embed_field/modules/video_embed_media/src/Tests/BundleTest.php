<?php

/**
 * @file
 * Contains \Drupal\video_embed_media\Tests\BundleTest.
 */

namespace Drupal\video_embed_media\Tests;

use Drupal\video_embed_field\Tests\WebTestBase;

/**
 * Test the video_embed_field media integration.
 *
 * @group video_embed_media
 */
class BundleTest extends WebTestBase {

  /**
   * Modules to install.
   *
   * @var array
   */
  public static $modules = [
    'video_embed_field',
    'video_embed_media',
    'media_entity',
    'field_ui',
    'node',
    'image',
  ];

  /**
   * Test the dialog form.
   */
  public function testMediaBundleCreation() {
    $this->drupalLogin($this->adminUser);

    // Create a new media bundle
    $this->drupalGet('admin/structure/media/add');
    $this->drupalPostForm(NULL, [
      'label' => 'Video Bundle',
      'id' => 'video_bundle',
      'type' => 'video_embed_field',
    ], 'Save media bundle');
    $this->assertText('The media bundle Video Bundle has been added.');

    // Ensure the video field is added to the media entity.
    $this->drupalGet('admin/structure/media/manage/video_bundle/fields');
    $this->assertText('field_media_video_embed_field');
    $this->assertText('Video URL');

    // Add a media entity with the new field.
    $this->drupalGet('media/add/video_bundle');
    $this->drupalPostForm(NULL, [
      'name[0][value]' => 'Drupal video!',
      'field_media_video_embed_field[0][value]' => 'https://www.youtube.com/watch?v=XgYu7-DQjDQ',
    ], 'Save');
    // We should see the video thumbnail on the media page.
    $this->assertRaw('video_thumbnails/XgYu7-DQjDQ.jpg');

    // Add another field and change the configured media field.
    $this->drupalPostForm('admin/structure/media/manage/video_bundle/fields/add-field', array(
      'new_storage_type' => 'video_embed_field',
      'label' => 'New Video Field',
      'field_name' => 'new_video_field',
    ), 'Save and continue');
    $this->drupalPostForm(NULL, [], 'Save field settings');
    $this->drupalPostForm(NULL, [], 'Save settings');

    // Update video source field.
    $this->drupalPostForm('admin/structure/media/manage/video_bundle', [
      'type_configuration[video_embed_field][source_field]' => 'field_new_video_field',
    ], 'Save media bundle');

    // Create a video, populating both video URL fields.
    $this->drupalGet('media/add/video_bundle');
    $this->drupalPostForm(NULL, [
      'name[0][value]' => 'Another Video!',
      'field_media_video_embed_field[0][value]' => 'https://www.youtube.com/watch?v=XgYu7-DQjDQ',
      'field_new_video_field[0][value]' => 'https://www.youtube.com/watch?v=gnERPdAiuSo',
    ], 'Save');

    // We should see the newly configured video thumbnail, but not the original.
    $this->assertRaw('video_thumbnails/gnERPdAiuSo.jpg');
    $this->assertNoRaw('video_thumbnails/XgYu7-DQjDQ.jpg');
  }

}
