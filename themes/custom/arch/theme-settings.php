<?php
use Drupal\Component\Utility\Html;
use Drupal\Core\Form\FormStateInterface;
use Drupal\system\Form\ThemeSettingsForm;
use Drupal\file\Entity\File;
use Drupal\Core\Url;

function arch_form_system_theme_settings_alter(&$form, \Drupal\Core\Form\FormStateInterface &$form_state) {
    global $base_url;    
    $form['settings'] = array(
        '#type' => 'details',
        '#title' => t('Theme settings'),
        '#open' => TRUE,
    );
    $form['settings']['theme_display'] = array(
        '#type' => 'details',
        '#title' => t('Theme display'),
        '#open' => FALSE,
    );
    $form['settings']['theme_display']['display_mode_demo'] = array(
      '#type' => 'checkbox',
      '#title' => t('Display mode demo'),
      '#description' => t('Checked on use <em>?version=light</em> or <em>?version=dark</em> on URL'),
      '#default_value' => theme_get_setting('display_mode_demo', 'arch'),
      );
    $form['settings']['theme_display']['display_mode'] = array(
      '#type' => 'select',
      '#title' => t('Display mode'),
      '#options' => array(
        'light' => t('Light'),
        'dark' => t('Dark'),
        ),

      '#default_value' => theme_get_setting('display_mode', 'arch') ? theme_get_setting('display_mode', 'arch') : 'light',
      );

    $form['settings']['header'] = array(
        '#type' => 'details',
        '#title' => t('Header settings'),
        '#open' => FALSE,
    );
    
    $form['settings']['header']['vertical_panel_title'] = array(
      '#type' => 'textfield',
      '#title' => t('Vertical panel title'),
      '#default_value' => theme_get_setting('vertical_panel_title', 'arch'),
    );
    $form['settings']['header']['brand_name'] = array(
      '#type' => 'textfield',
      '#title' => t('Brand name'),
      '#default_value' => theme_get_setting('brand_name', 'arch'),
    );
    $form['settings']['header']['text_logo_status'] = array(
      '#type' => 'checkbox',
      '#title' => t('Use text logo.'),
      '#default_value' => theme_get_setting('text_logo_status', 'arch'),
    );
    $form['settings']['header']['text_logo'] = array(
      '#type' => 'textfield',
      '#title' => t('Text logo'),
      '#default_value' => theme_get_setting('text_logo', 'arch'),
      '#states' => array(
          'visible' => array(
            array(
              ':input[name="text_logo_status"]' => array('checked' => TRUE),
              ),
          ),
      ),
    );
    
    $form['settings']['header']['header_social_networks'] = array(
      '#type' => 'textarea',
      '#title' => t('Social networks'),
      '#default_value' => theme_get_setting('header_social_networks', 'arch'),
    );
    $form['settings']['header']['header_phone'] = array(
      '#type' => 'textfield',
      '#title' => t('Tel'),
      '#default_value' => theme_get_setting('header_phone', 'arch'),
    );

    
    //print_r($form['settings']['header']['second_logo']);
    $form['settings']['general_setting'] = array(
        '#type' => 'details',
        '#title' => t('General Settings'),
        '#open' => FALSE,
    );

    $form['settings']['general_setting']['general_setting_tracking_code'] = array(
        '#type' => 'textarea',
        '#title' => t('Tracking Code'),
        '#default_value' => theme_get_setting('general_setting_tracking_code', 'arch'),
    );
   
  // Blog settings
    $form['settings']['blog'] = array(
      '#type' => 'details',
      '#title' => t('Blog settings'),
      '#open' => FALSE,
    );
  
    
    $form['settings']['blog']['blog_listing'] = array(
      '#type' => 'details',
      '#title' => t('Blog listing'),
      '#open' => FALSE,
    );
    $form['settings']['blog']['blog_listing']['blog_bg_wrap'] = array(
      '#type' => 'details',
      '#title' => t('Page title background'),
      '#open' => FALSE,           
    );
    if(!empty(theme_get_setting('blog_page_title_background_image','arch'))){
      $form['settings']['blog']['blog_listing']['blog_bg_wrap']['blog_bg_preview'] = array(
          '#prefix' => '<div id="blog_bg_preview">',
          '#markup' => '<img src="'.$base_url.theme_get_setting('blog_page_title_background_image','arch').
          '" height="50" width="150" />',
          '#suffix' => '</div>',
      );
    }
    $form['settings']['blog']['blog_listing']['blog_bg_wrap']['blog_page_title_background_image'] = array(
      '#type' => 'hidden',
      //'#title' => t('URL of the background image'),
      '#default_value' => theme_get_setting('blog_page_title_background_image'),
      '#size' => 40,
      //'#disabled' => 'disabled',
      '#maxlength' => 512,
    );
    
    $form['settings']['blog']['blog_listing']['blog_bg_wrap']['blog_page_title_background_image_upload'] = array(
      '#type' => 'file',
      '#title' => t('Upload background image'),
      '#size' => 40,
      '#attributes' => array('enctype' => 'multipart/form-data'),
      '#description' => t('If you don\'t jave direct access to the server, use this field to upload your background image. Uploads limited to .png .gif .jpg .jpeg .apng .svg extensions'),
      '#element_validate' => array('blog_page_title_background_image_validate'),
    );
    //
    $form['settings']['blog']['blog_tags'] = array(
      '#type' => 'details',
      '#title' => t('Blog tags'),
      '#open' => FALSE,
    );

    if(!empty(theme_get_setting('blog_tags_background_image','arch'))){
      $form['settings']['blog']['blog_tags']['blog_tags_bg_wrap']['blog_tags_bg_preview'] = array(
          '#prefix' => '<div id="blog_tags_bg_preview">',
          '#markup' => '<img src="'.$base_url.theme_get_setting('blog_tags_background_image','arch').
          '" height="50" width="150" />',
          '#suffix' => '</div>',
      );
    }
    $form['settings']['blog']['blog_tags']['blog_tags_bg_wrap']['blog_tags_background_image'] = array(
      '#type' => 'hidden',
      //'#title' => t('URL of the background image'),
      '#default_value' => theme_get_setting('blog_tags_background_image'),
      '#size' => 40,
      //'#disabled' => 'disabled',
      '#maxlength' => 512,
    );
    
    $form['settings']['blog']['blog_tags']['blog_tags_bg_wrap']['blog_tags_page_title_background_image_upload'] = array(
      '#type' => 'file',
      '#title' => t('Upload background image'),
      '#size' => 40,
      '#attributes' => array('enctype' => 'multipart/form-data'),
      '#description' => t('If you don\'t jave direct access to the server, use this field to upload your background image. Uploads limited to .png .gif .jpg .jpeg .apng .svg extensions'),
      '#element_validate' => array('blog_tags_page_title_background_image_validate'),
    );
   

    $form['settings']['other_page'] = array(
      '#type' => 'details',
      '#title' => t('Other page'),
      '#open' => FALSE,
    );
    if(theme_get_setting('other_page_bg_image','arch')){
      $form['settings']['other_page']['other_page_wrap']['other_page_bg_preview'] = array(
          '#prefix' => '<div id="other_page_bg_preview">',
          '#markup' => '<img src="'.$base_url.theme_get_setting('other_page_bg_image','arch').
          '" height="50" width="150" />',
          '#suffix' => '</div>',
      );
    }
    $form['settings']['other_page']['other_page_wrap']['other_page_bg_image'] = array(
      '#type' => 'hidden',
      //'#title' => t('URL of the background image'),
      '#default_value' => theme_get_setting('other_page_bg_image'),
      '#size' => 40,
      //'#disabled' => 'disabled',
      '#maxlength' => 512,
    );
    
    $form['settings']['other_page']['other_page_wrap']['other_page_bg_image_upload'] = array(
      '#type' => 'file',
      '#title' => t('Upload background image'),
      '#size' => 40,
      '#attributes' => array('enctype' => 'multipart/form-data'),
      '#description' => t('If you don\'t jave direct access to the server, use this field to upload your background image. Uploads limited to .png .gif .jpg .jpeg .apng .svg extensions'),
      '#element_validate' => array('other_page_bg_image_validate'),
    );
  
    // custom css
  $form['settings']['custom_css'] = array(
    '#type' => 'details',
    '#title' => t('Custom CSS'),
    '#open' => FALSE,
  );
  

  $form['settings']['custom_css']['custom_css'] = array(
    '#type' => 'textarea',
    '#title' => t('Custom CSS'),
    '#default_value' => theme_get_setting('custom_css', 'arch'),
    '#description'  => t('<strong>Example:</strong><br/>h1 { font-family: \'Metrophobic\', Arial, serif; font-weight: 400; }')
  );
    $form['settings']['contact_page'] = array(
      '#type' => 'details',
      '#title' => t('Contact page'),
      '#open' => FALSE,
    );  
    $form['settings']['contact_page']['contact_phone'] = array(
      '#type' => 'textarea',
      '#title' => t('Phone'),
      '#default_value' => theme_get_setting('contact_phone', 'arch'),
    );
      $form['settings']['contact_page']['contact_email'] = array(
      '#type' => 'textfield',
      '#title' => t('Email'),
      '#default_value' => theme_get_setting('contact_email', 'arch'),
    );
    $form['settings']['contact_page']['contact_address'] = array(
      '#type' => 'textarea',
      '#title' => t('Address'),
      '#default_value' => theme_get_setting('contact_address', 'arch'),
    );
    $form['settings']['contact_page']['maps'] = array(
      '#type' => 'details',
      '#title' => t('Googlemaps'),
      '#open' => TRUE,
    );  
    $form['settings']['contact_page']['maps']['latitude'] = array(
      '#type' => 'textfield',
      '#title' => t('Latitude'),
      '#default_value' => theme_get_setting('latitude', 'arch') ? theme_get_setting('latitude', 'arch') : -37.823534,

    );  
    $form['settings']['contact_page']['maps']['longitude'] = array(
      '#type' => 'textfield',
      '#title' => t('Longitude'),
      '#default_value' => theme_get_setting('longitude', 'arch') ? theme_get_setting('longitude', 'arch') : 144.975617,

    );  
}

function blog_page_title_background_image_validate($element, FormStateInterface $form_state) {
  global $base_url;

  $validators = array('file_validate_extensions' => array('png gif jpg jpeg apng svg'));
  $file = file_save_upload('blog_page_title_background_image_upload', $validators, "public://page_title", NULL, FILE_EXISTS_REPLACE);

  if (!empty($file)) {
    // change file's status from temporary to permanent and update file database
    if ((is_object($file[0]) == 1)) {
      $file[0]->status = FILE_STATUS_PERMANENT;
      $file[0]->save();
      $uri = $file[0]->getFileUri();
      $file_url = file_create_url($uri);
      $file_url = str_ireplace($base_url, '', $file_url);
      $form_state->setValue('blog_page_title_background_image', $file_url);
    }
 }
}
//
function blog_tags_page_title_background_image_validate($element, FormStateInterface $form_state) {
  global $base_url;

  $validators = array('file_validate_extensions' => array('png gif jpg jpeg apng svg'));
  $file = file_save_upload('blog_tags_page_title_background_image_upload', $validators, "public://page_title", NULL, FILE_EXISTS_REPLACE);

  if (!empty($file)) {
    // change file's status from temporary to permanent and update file database
    if ((is_object($file[0]) == 1)) {
      $file[0]->status = FILE_STATUS_PERMANENT;
      $file[0]->save();
      $uri = $file[0]->getFileUri();
      $file_url = file_create_url($uri);
      $file_url = str_ireplace($base_url, '', $file_url);
      $form_state->setValue('blog_tags_background_image', $file_url);
    }
 }
}
//


function other_page_bg_image_validate($element, FormStateInterface $form_state) {
  global $base_url;

  $validators = array('file_validate_extensions' => array('png gif jpg jpeg apng svg'));
  $file = file_save_upload('other_page_bg_image_upload', $validators, "public://page_title", NULL, FILE_EXISTS_REPLACE);

  if (!empty($file)) {
    // change file's status from temporary to permanent and update file database
    if ((is_object($file[0]) == 1)) {
      $file[0]->status = FILE_STATUS_PERMANENT;
      $file[0]->save();
      $uri = $file[0]->getFileUri();
      $file_url = file_create_url($uri);
      $file_url = str_ireplace($base_url, '', $file_url);
      $form_state->setValue('other_page_bg_image', $file_url);
    }
 }
}

