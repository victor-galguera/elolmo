<?php

/**
 * @file
 * Contains \Drupal\video_embed_field\Tests\Web\FormatterConfigurationTest.
 */

namespace Drupal\video_embed_field\Tests\Web;

use Drupal\video_embed_field\Plugin\Field\FieldFormatter\Thumbnail;
use Drupal\video_embed_field\ProviderPluginInterface;
use Drupal\video_embed_field\Tests\WebTestBase;

/**
 * Tests the field formatter configuration forms.
 *
 * @group video_embed_field
 */
class FormatterConfigurationTest extends WebTestBase {

  /**
   * The URL to the manage display interface.
   *
   * @var string
   */
  protected $manageDisplay;

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();
    $this->drupalLogin($this->adminUser);
    $this->manageDisplay = 'admin/structure/types/manage/' . $this->contentTypeName . '/display/teaser';
  }

  /**
   * Test the formatter configuration forms.
   */
  public function testVideoConfirmationForm() {
    // Test the settings form and summaries for the video formatter.
    $this->setFormatter('video_embed_field_video');
    $this->assertText('Embedded Video (Responsive, autoplaying).');
    $this->updateFormatterSettings([
      'autoplay' => FALSE,
      'responsive' => FALSE,
      'width' => '100%',
      'height' => '100%',
    ]);
    $this->assertText('Embedded Video (100%x100%).');

    // Test the image formatter.
    $this->setFormatter('video_embed_field_thumbnail');
    $this->assertText('Video thumbnail (no image style).');
    $this->updateFormatterSettings([
      'image_style' => 'thumbnail',
      'link_image_to' => Thumbnail::LINK_CONTENT,
    ]);
    $this->assertText('Video thumbnail (thumbnail, linked to content).');
    $this->updateFormatterSettings([
      'image_style' => 'medium',
      'link_image_to' => Thumbnail::LINK_PROVIDER,
    ]);
    $this->assertText('Video thumbnail (medium, linked to provider).');
  }

  /**
   * Set the field formatter for the test field.
   *
   * @param string $formatter
   *   The field formatter ID to use.
   */
  protected function setFormatter($formatter) {
    $this->drupalPostAjaxForm($this->manageDisplay, [
      'fields[' . $this->fieldName . '][type]' => $formatter,
      'refresh_rows' => $this->fieldName,
    ], ['op' => t('Refresh')]);
    $this->drupalPostForm(NULL, [], t('Save'));
  }

  /**
   * Update the settings for the current formatter.
   *
   * @param array $settings
   *   The settings to update the foramtter with.
   */
  protected function updateFormatterSettings($settings) {
    $edit = [];
    foreach ($settings as $key => $value) {
      $edit["fields[{$this->fieldName}][settings_edit_form][settings][$key]"] = $value;
    }
    $this->drupalGet($this->manageDisplay);
    $this->drupalPostAjaxForm(NULL, [], $this->fieldName . '_settings_edit');
    $this->drupalPostAjaxForm(NULL, $edit, $this->fieldName . '_plugin_settings_update');
    $this->drupalPostForm(NULL, [], t('Save'));
  }

}
