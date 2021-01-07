<?php

namespace Drupal\nvs_widget\Plugin\Block;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;

/**
 *
 *
 * @Block(
 *   id = "instagramwidget2",
 *   admin_label = @Translation("[NVS] Instagram widget 2"),
 *   category = @Translation("Naviteam widget")
 * )
 */

class InstagramWidget_2 extends BlockBase {

  /**
   * Overrides \Drupal\block\BlockBase::blockForm().
   */

  public function blockForm($form, FormStateInterface $form_state) {
    $form['user_name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('User name'),
      '#description' => $this->t('Eg: <em>saatchi_gallery</em>'),
      '#required' => TRUE,
      '#default_value' => isset($this->configuration['user_name']) ? $this->configuration['user_name'] : 'saatchi_gallery',
    ];
    $form['record'] = [
      '#type' => 'select',
      '#title' => $this->t('Number of recent photos items to display'),
      '#required' => TRUE,
      '#options' => 
        array(
          2 => $this->t('2'),
          3 => $this->t('3'),
          4 => $this->t('4'),
          5 => $this->t('5'),
          6 => $this->t('6'),
          7 => $this->t('7'),
          8 => $this->t('8'),
          9 => $this->t('9'),
          10 => $this->t('10'),
          11 => $this->t('11'),
          12 => $this->t('12'),
          13 => $this->t('13'),
          14 => $this->t('14'),
          15 => $this->t('15'),
          16 => $this->t('16'),
          17 => $this->t('17'),
          18 => $this->t('18'),
          19 => $this->t('19'),
          20 => $this->t('20'),
          25 => $this->t('25'),
          30 => $this->t('30'),
        ),
      '#default_value' => isset($this->configuration['record']) ? $this->configuration['record'] : 10,
    ];
    return $form;
  }

  /**
   * Overrides \Drupal\block\BlockBase::blockSubmit().
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    $this->configuration['user_name'] = $form_state->getValue('user_name');
    $this->configuration['record'] = $form_state->getValue('record');
  }
  public function build() {
    $config = $this->getConfiguration();

    $username = $config['user_name']; // your username
    
    $access_token = \Drupal::config('nvs_widget.settings')->get('instagram_access_token'); // put your access token here
    $count = $config['record']; // number of images to show
    include 'instagram.php'; 

    $ins_media = $insta->userMedia(); 
    $i = 0; 
    $out = '<div class="instagram-widget clearfix">
                        <div class="instagram-widget-row">';
    //print_r($ins_media['data']);
    foreach ($ins_media['data'] as $vm): 

        if($count == $i){ break;}
        $i++;
        //kint($vm['images']);
        $img = $vm['images']['thumbnail']['url'];
        $img_2 = $vm['images']['standard_resolution']['url'];
        $link = $vm["link"];
        $out .= '<div class="instagram-widget-column">
    <a class="overlay-thumbnail" href="'.$link.'"><img src="'.$img.'" alt=""><img src="'.$img.'" alt="img-'.$i.'"></a>
    <div class="empty-space col-xs-b10"></div>
</div>';
        
    endforeach;
    //kint($ins_media);
    $out .= '</div></div>';
    return [
      '#markup' => $out,
    ];
  }
 
}

