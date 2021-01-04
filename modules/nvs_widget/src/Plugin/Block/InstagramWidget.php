<?php

namespace Drupal\nvs_widget\Plugin\Block;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;

/**
 *
 *
 * @Block(
 *   id = "instagramwidget",
 *   admin_label = @Translation("[NVS] Instagram widget"),
 *   category = @Translation("Naviteam widget")
 * )
 */

class InstagramWidget extends BlockBase {

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
    $out = '<div class="row"><div class="swiper-container instagram-slider" data-autoplay="0" data-loop="0" data-speed="500" data-center="0" data-auto-height="false" data-slides-per-view="6" data-breakpoints="1" data-xs-slides="1" data-sm-slides="3" data-md-slides="4" data-parallax="0" data-ini="0"><div class="swiper-button-prev swiper-button hidden"></div><div class="swiper-button-next swiper-button hidden"></div><div class="swiper-wrapper">';
    //print_r($ins_media['data']);
    foreach ($ins_media['data'] as $vm): 

        if($count == $i){ break;}
        $i++;
        //kint($vm['images']);
        $img = $vm['images']['thumbnail']['url'];
        $img_2 = $vm['images']['standard_resolution']['url'];
        $link = $vm["link"];
        $out .= '<div class="swiper-slide">
                  <div class="content">
                      <a target="_blank" class="entry mouseover" data-background-image="'.$img_2.'" href="'.$link.'">
                          <span class="mouseover-helper-frame"></span>
                          <span class="mouseover-helper-icon"></span>
                      </a>
                  </div>
                </div>';
        
    endforeach;
    //kint($ins_media);
    $out .= '</div><div class="swiper-pagination relative-pagination"></div></div></div>';
    $out .= '<div class="empty-space col-xs-b35"></div>

            <div class="row">
                <div class="col-md-12 text-center">
                    <a target="_blank" class="button type-2" href="https://www.instagram.com/'.$username.'">follow us on instagram <span><b>@'.$username.'</b></span></a>
                </div>
            </div>

            <div class="empty-space col-xs-b55 col-sm-b110"></div>';
    return [
      '#markup' => $out,
    ];
  }
 
}

