<?php

namespace Drupal\nvs_widget\Plugin\Block;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Component\Utility\MapArray;

/**
 * 
 *
 * @Block(
 *   id = "twitterwidget",
 *   admin_label = @Translation("[NVS] Twitter widget"),
 *   category = @Translation("Naviteam widget")
 * )
 */
class TwitterWidget extends BlockBase {

  /**
   * Overrides \Drupal\block\BlockBase::blockForm().
   */

  public function blockForm($form, FormStateInterface $form_state) {
    $form['user_name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Your twitter username'),
      '#description' => $this->t('Eg: <em>drupal</em>'),
      '#required' => TRUE,
      '#default_value' => isset($this->configuration['user_name']) ? $this->configuration['user_name'] : 'drupal',
    ];
    $form['record'] = [
      '#type' => 'select',
      '#title' => $this->t('Number of recent tweets items to display'),
      '#required' => TRUE,
      '#options' => 
        array(
          1 => $this->t('1'),
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
      '#default_value' => isset($this->configuration['record']) ? $this->configuration['record'] : '',
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
  //

  //
  public function build() {
    return [
      '#markup' => $this->getTweets(),
    ];
  }
  //add qoutes
  public function add_quotes($str) { return '"'.$str.'"'; }
  //get tweets
  public function getTweets(){
    //API
    $token = \Drupal::config('nvs_widget.settings')->get('twitter_access_token');
    $token_secret = \Drupal::config('nvs_widget.settings')->get('twitter_access_token_secret');
    $consumer_key = \Drupal::config('nvs_widget.settings')->get('twitter_customer_key');
    $consumer_secret = \Drupal::config('nvs_widget.settings')->get('twitter_customer_secret');
    //get user
    $config = $this->getConfiguration();
    $twitter_username = $config['user_name'];
    $tweets_count = $config['record'];

    $host = 'api.twitter.com';
    $method = 'GET';
    $path = '/1.1/statuses/user_timeline.json'; // api call path

    $query = array( // query parameters
        'screen_name' => $twitter_username,
        'count' => $tweets_count
    );

    $oauth = array(
        'oauth_consumer_key' => $consumer_key,
        'oauth_token' => $token,
        'oauth_nonce' => (string)mt_rand(), // a stronger nonce is recommended
        'oauth_timestamp' => time(),
        'oauth_signature_method' => 'HMAC-SHA1',
        'oauth_version' => '1.0'
    );

    $oauth = array_map("rawurlencode", $oauth); // must be encoded before sorting
    $query = array_map("rawurlencode", $query);

    $arr = array_merge($oauth, $query); // combine the values THEN sort

    asort($arr); // secondary sort (value)
    ksort($arr); // primary sort (key)

    // http_build_query automatically encodes, but our parameters
    // are already encoded, and must be by this point, so we undo
    // the encoding step
    $querystring = urldecode(http_build_query($arr, '', '&'));

    $url = "https://$host$path";

    // mash everything together for the text to hash
    $base_string = $method."&".rawurlencode($url)."&".rawurlencode($querystring);

    // same with the key
    $key = rawurlencode($consumer_secret)."&".rawurlencode($token_secret);

    // generate the hash
    $signature = rawurlencode(base64_encode(hash_hmac('sha1', $base_string, $key, true)));

    // this time we're using a normal GET query, and we're only encoding the query params
    // (without the oauth params)
    $url .= "?".http_build_query($query);
    $url=str_replace("&amp;","&",$url); //Patch by @Frewuill

    $oauth['oauth_signature'] = $signature; // don't want to abandon all that work!
    ksort($oauth); // probably not necessary, but twitter's demo does it

    // also not necessary, but twitter's demo does this too
    $oauth = array_map("add_quotes", $oauth);

    // this is the full value of the Authorization line
    $auth = "OAuth " . urldecode(http_build_query($oauth, '', ', '));

    // if you're doing post, you need to skip the GET building above
    // and instead supply query parameters to CURLOPT_POSTFIELDS
    $options = array( CURLOPT_HTTPHEADER => array("Authorization: $auth"),
                      //CURLOPT_POSTFIELDS => $postfields,
                      CURLOPT_HEADER => false,
                      CURLOPT_URL => $url,
                      CURLOPT_RETURNTRANSFER => true,
                      CURLOPT_SSL_VERIFYPEER => false);

    // do our business
    $feed = curl_init();
    curl_setopt_array($feed, $options);
    $json = curl_exec($feed);
    curl_close($feed);

    $my_tweets = json_decode($json);

    $tweetout = '';
    foreach ($my_tweets as $key => $value) {
      if(isset($my_tweets->errors)){           
          $tweetout .= '<p>Error :'. $my_tweets->errors[0]->code. ' - '. $my_tweets->errors[0]->message.'</p>';
      }else{
        //print_r($value);
        //profile_image_url
        $profile_image_url = $value->user->profile_image_url;
        //STATUS
        $status = $value->text;
        //created
        $created = $value->created_at;

        //output custom here
        $tweetout .= '<div class="row col-xs-b15">
                        <div class="col-xs-6">
                          <div class="date"><i>'.$this->twitter_time($created).'</i></div>  
                        </div>
                        <div class="col-xs-6 text-right">
                            <i class="fa fa-twitter"></i>
                        </div>
                      </div>
                      <div class="simple-article light col-xs-b10">'.$this->makeClickableLinks($status).'</div>
                      <a href="https://twitter.com/'.$twitter_username.'" class="author">@'.$twitter_username.'</a>
                      <div class="col-xs-b10"></div>';
      }
    }
    return $tweetout;
  }
  //convert time to TIME AGO
  function twitter_time($a) {
    //get current timestampt
    $b = strtotime("now"); 
    //get timestamp when tweet created
    $c = strtotime($a);
    //get difference
    $d = $b - $c;
    //calculate different time values
    $minute = 60;
    $hour = $minute * 60;
    $day = $hour * 24;
    $week = $day * 7;
        
    if(is_numeric($d) && $d > 0) {
        //if less then 3 seconds
        if($d < 3) return "right now";
        //if less then minute
        if($d < $minute) return floor($d) . " seconds ago";
        //if less then 2 minutes
        if($d < $minute * 2) return "about 1 minute ago";
        //if less then hour
        if($d < $hour) return floor($d / $minute) . " minutes ago";
        //if less then 2 hours
        if($d < $hour * 2) return "about 1 hour ago";
        //if less then day
        if($d < $day) return floor($d / $hour) . " hours ago";
        //if more then day, but less then 2 days
        if($d > $day && $d < $day * 2) return "yesterday";
        //if less then year
        if($d < $day * 365) return floor($d / $day) . " days ago";
        //else return more than a year
        return "over a year ago";
    }
  }

  //convert text url into links.
  function makeClickableLinks($s) {
    return preg_replace('@(https?://([-\w\.]+[-\w])+(:\d+)?(/([\w/_\.#-]*(\?\S+)?[^\.\s])?)?)@', '<a target="blank" rel="nofollow" href="$1">$1</a>', $s);
  }
}

