<?php

/**
 * @file
 * Contains \Drupal\video_embed_wysiwyg\Tests\TextFormatConfigurationTest.
 */

namespace Drupal\video_embed_wysiwyg\Tests;

use Drupal\video_embed_field\Tests\WebTestBase;

/**
 * Test the format configuration form.
 *
 * @group video_embed_wysiwyg
 */
class TextFormatConfigurationTest extends WebTestBase {

  /**
   * Modules to install.
   *
   * @var array
   */
  public static $modules = [
    'video_embed_field',
    'video_embed_wysiwyg',
    'editor',
    'ckeditor',
    'field_ui',
    'node',
    'image',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    $this->drupalLogin($this->adminUser);
    $this->drupalGet('admin/config/content/formats/manage/plain_text');

    // Setup the filter to have an editor.
    $this->drupalPostForm(NULL, [
      'editor[editor]' => 'ckeditor',
    ], t('Save configuration'));
    $this->drupalPostAjaxForm(NULL, [], 'editor_configure');
    $this->drupalPostForm(NULL, [], t('Save configuration'));
  }

  /**
   * Test both the input filter and button need to be enabled together.
   */
  public function testFormatConfiguration() {
    // Save the settings with the filter enabled, but with no button.
    $this->drupalPostForm('admin/config/content/formats/manage/plain_text', [
      'filters[video_embed_wysiwyg][status]' => TRUE,
      'editor[settings][toolbar][button_groups]' => '[]',
    ], 'Save configuration');
    $this->assertText('To embed videos, make sure you have enabled the "Video Embed WYSIWYG" filter and dragged the video icon into the WYSIWYG toolbar.');

    $this->drupalPostForm('admin/config/content/formats/manage/plain_text', [
      'filters[video_embed_wysiwyg][status]' => FALSE,
      'editor[settings][toolbar][button_groups]' => '[[{"name":"Group","items":["video_embed"]}]]',
    ], 'Save configuration');
    $this->assertText('To embed videos, make sure you have enabled the "Video Embed WYSIWYG" filter and dragged the video icon into the WYSIWYG toolbar.');

    $this->drupalPostForm('admin/config/content/formats/manage/plain_text', [
      'filters[video_embed_wysiwyg][status]' => TRUE,
      'editor[settings][toolbar][button_groups]' => '[[{"name":"Group","items":["video_embed"]}]]',
    ], 'Save configuration');
    $this->assertText('The text format Plain text has been updated.');
  }

  /**
   * Test the dialog defaults can be set and work correctly.
   */
  public function testDialogDefaultValues() {
    $this->drupalGet('admin/config/content/formats/manage/plain_text');

    // Assert all the form fields that appear on the modal, appear as
    // configurable defaults.
    $this->assertText('Autoplay');
    $this->assertText('Responsive Video');
    $this->assertText('Width');
    $this->assertText('Height');

    $this->drupalPostForm(NULL, [
      'filters[video_embed_wysiwyg][status]' => TRUE,
      'editor[settings][toolbar][button_groups]' => '[[{"name":"Group","items":["video_embed"]}]]',
      'editor[settings][plugins][video_embed][defaults][children][width]' => '123',
      'editor[settings][plugins][video_embed][defaults][children][height]' => '456',
      'editor[settings][plugins][video_embed][defaults][children][responsive]' => FALSE,
      'editor[settings][plugins][video_embed][defaults][children][autoplay]' => FALSE,
    ], 'Save configuration');

    // Ensure the configured defaults show up on the modal window.
    $this->drupalGet('video-embed-wysiwyg/dialog/plain_text');
    $this->assertFieldByXPath('//input[@name="width"]', '123');
    $this->assertFieldByXPath('//input[@name="height"]', '456');
    $this->assertFieldByXPath('//input[@name="autoplay"]', FALSE);
    $this->assertFieldByXPath('//input[@name="responsive"]', FALSE);
  }

}
