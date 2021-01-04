<?php



namespace Drupal\md_slider;


interface MDSliderInterface {

  /**
   * @param $slid
   * @return mixed
   */
  public function setSettings();

  /**
   * @param $slid
   * @return mixed
   */
  public function getDataSlider($slid);

  /**
   * @param $slid
   * @return mixed
   */
  public function saveDataSlider($slid = NULL);

  /**
   * @param $slid
   * @return mixed
   */
  public function deleteDataSlider($slid);

}
