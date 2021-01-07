<?php

namespace Drupal\Tests\simplenews\Functional;

use Drupal\block\Entity\Block;
use Drupal\Component\Utility\Html;
use Drupal\node\Entity\Node;
use Drupal\simplenews\Entity\Newsletter;
use Drupal\simplenews\Entity\Subscriber;
use Drupal\views\Entity\View;

/**
 * Managing of newsletter categories and content types.
 *
 * @group simplenews
 */
class SimplenewsAdministrationTest extends SimplenewsTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = ['help'];

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();
    $this->drupalPlaceBlock('help_block');
  }

  /**
   * Implement getNewsletterFieldId($newsletter_id)
   */
  protected function getNewsletterFieldId($newsletter_id) {
    return 'edit-subscriptions-' . str_replace('_', '-', $newsletter_id);
  }

  /**
   * Test various combinations of newsletter settings.
   */
  public function testNewsletterSettings() {

    // Allow registration of new accounts without approval.
    $site_config = $this->config('user.settings');
    $site_config->set('verify_mail', FALSE);
    $site_config->save();

    $admin_user = $this->drupalCreateUser([
      'administer blocks',
      'administer content types',
      'administer nodes',
      'access administration pages',
      'administer permissions',
      'administer newsletters',
      'administer simplenews subscriptions',
      'create simplenews_issue content',
      'send newsletter',
    ]);
    $this->drupalLogin($admin_user);

    $this->drupalGet('admin/config/services/simplenews');
    // Check if the help text is displayed.
    $this->assertText('Newsletter allow you to send periodic e-mails to subscribers.');

    // Create a newsletter for all possible setting combinations.
    $new_account = ['none', 'off', 'on', 'silent'];
    $opt_inout = ['hidden', 'single', 'double'];

    foreach ($new_account as $new_account_setting) {
      foreach ($opt_inout as $opt_inout_setting) {
        $this->clickLink(t('Add newsletter'));
        $edit = [
          'name' => implode('-', [$new_account_setting, $opt_inout_setting]),
          'id' => implode('_', [$new_account_setting, $opt_inout_setting]),
          'description' => $this->randomString(20),
          'new_account' => $new_account_setting,
          'opt_inout' => $opt_inout_setting,
          'priority' => rand(0, 5),
          'receipt' => rand(0, 1) ? TRUE : FALSE,
          'from_name' => $this->randomMachineName(),
          'from_address' => $this->randomEmail(),
        ];
        $this->drupalPostForm(NULL, $edit, t('Save'));
      }
    }

    // New title should be saved correctly.
    $this->drupalPostForm('admin/config/services/simplenews/manage/default', ['subject' => 'Edited subject'], t('Save'));
    $this->drupalGet('admin/config/services/simplenews/manage/default');
    $this->assertFieldByName('subject', 'Edited subject');

    $newsletters = simplenews_newsletter_get_all();

    // Check registration form.
    $this->drupalLogout();
    $this->drupalGet('user/register');
    foreach ($newsletters as $newsletter) {
      if (strpos($newsletter->name, '-') === FALSE) {
        continue;
      }

      // Explicitly subscribe to the off-double newsletter.
      if ($newsletter->name == 'off-double') {
        $off_double_newsletter_id = $newsletter->id();
      }

      list($new_account_setting, $opt_inout_setting) = explode('-', $newsletter->name);
      if ($newsletter->new_account == 'on' && $newsletter->opt_inout != 'hidden') {
        $this->assertFieldChecked($this->getNewsletterFieldId($newsletter->id()));
      }
      elseif ($newsletter->new_account == 'off' && $newsletter->opt_inout != 'hidden') {
        $this->assertNoFieldChecked($this->getNewsletterFieldId($newsletter->id()));
      }
      else {
        $this->assertNoField('subscriptions[' . $newsletter->id() . ']', t('Hidden or silent newsletter is not shown.'));
      }
    }

    // Register a new user through the form.
    $pass = $this->randomMachineName();
    $edit = [
      'name' => $this->randomMachineName(),
      'mail' => $this->randomEmail(),
      'pass[pass1]' => $pass,
      'pass[pass2]' => $pass,
      'subscriptions[' . $off_double_newsletter_id . ']' => $off_double_newsletter_id,
    ];
    $this->drupalPostForm(NULL, $edit, t('Create new account'));

    // Verify confirmation messages.
    $this->assertText(t('Registration successful. You are now logged in.'));
    foreach ($newsletters as $newsletter) {
      // Check confirmation message for all on and non-hidden newsletters and
      // the one that was explicitly selected.
      if (($newsletter->new_account == 'on' && $newsletter->opt_inout != 'hidden') || $newsletter->name == 'off-double') {
        $this->assertText(t('You have been subscribed to @name.', ['@name' => $newsletter->name]));
      }
      else {
        // All other newsletters must not show a message, e.g. those which were
        // subscribed silently.
        $this->assertNoText(t('You have been subscribed to @name.', ['@name' => $newsletter->name]));
      }
    }

    // Log out again.
    $this->drupalLogout();

    $user = user_load_by_name($edit['name']);
    // Set the password so that the login works.
    $user->passRaw = $edit['pass[pass1]'];

    // Verify newsletter subscription pages.
    $this->drupalLogin($user);
    foreach (['newsletter/subscriptions', 'user/' . $user->id() . '/simplenews'] as $path) {
      $this->drupalGet($path);
      foreach ($newsletters as $newsletter) {
        if (strpos($newsletter->name, '-') === FALSE) {
          continue;
        }
        list($new_account_setting, $opt_inout_setting) = explode('-', $newsletter->name);
        if ($newsletter->opt_inout == 'hidden') {
          $this->assertNoField('subscriptions[' . $newsletter->id() . ']', t('Hidden newsletter is not shown.'));
        }
        elseif ($newsletter->new_account == 'on' || $newsletter->name == 'off-double' || $newsletter->new_account == 'silent') {
          // All on, silent and the explicitly selected newsletter should be
          // checked.
          $this->assertFieldChecked($this->getNewsletterFieldId($newsletter->id()));
        }
        else {
          $this->assertNoFieldChecked($this->getNewsletterFieldId($newsletter->id()));
        }
      }
    }

    // Unsubscribe from a newsletter.
    $edit = [
      'subscriptions[' . $off_double_newsletter_id . ']' => FALSE,
    ];
    $this->drupalPostForm(NULL, $edit, t('Save'));
    $this->drupalGet('user/' . $user->id() . '/simplenews');
    $this->assertNoFieldChecked($this->getNewsletterFieldId($off_double_newsletter_id));

    // Get a newsletter which has the block enabled.
    // @codingStandardsIgnoreStart
    /*foreach ($newsletters as $newsletter) {
      // The default newsletter is missing the from mail address. Use another one.
      if ($newsletter->block == TRUE && $newsletter->newsletter_id != 1 && $newsletter->opt_inout != 'hidden') {
        $edit_newsletter = $newsletter;
        break;
      }
    }*/
    // @codingStandardsIgnoreEnd

    // Check saving the subscriber as admin does not wipe the hidden newsletter
    // settings.
    $this->drupalLogin($admin_user);
    $subscriber = Subscriber::loadByMail($user->getEmail());
    $this->drupalGet('admin/people/simplenews/edit/' . $subscriber->id());
    $this->assertNoField($this->getNewsletterFieldId('on_hidden'));
    $this->assertNoField('mail');
    $this->drupalPostForm('admin/people/simplenews/edit/' . $subscriber->id(), [], t('Save'));
    $this->drupalGet('admin/people/simplenews/edit/' . $subscriber->id());
    $this->assertTrue($subscriber->isSubscribed('on_hidden'));
    $this->assertTrue($subscriber->isUnsubscribed($off_double_newsletter_id));

    // @codingStandardsIgnoreStart
    /*$this->setupSubscriptionBlock($edit_newsletter->newsletter_id, $settings = array(
      'issue count' => 2,
      'previous issues' => 1,
    ));

    // Create a bunch of newsletters.
    $generated_names = array();
    $date = strtotime('monday this week');
    for ($index = 0; $index < 3; $index++) {
      $name = $this->randomMachineName();
      $generated_names[] = $name;
      $this->drupalGet('node/add/simplenews_issue');
      $edit = array(
        'title' => $name,
        'simplenews_newsletter[und]' => $edit_newsletter->newsletter_id,
        'date' => date('c', strtotime('+' . $index . ' day', $date)),
      );
      $this->drupalPostForm(NULL, $edit, ('Save'));
      $this->clickLink(t('Newsletter'));
      $this->drupalPostForm(NULL, array('simplenews[send]' => SIMPLENEWS_COMMAND_SEND_NOW), t('Submit'));
    }

    // Display the two recent issues.
    $this->drupalGet('');
    $this->assertText(t('Previous issues'), 'Should display recent issues.');

    $displayed_issues = $this->xpath("//div[@class='issues-list']/div/ul/li/a");

    $this->assertEqual(count($displayed_issues), 2, 'Displys two recent issues.');

    $this->assertFalse(in_array($generated_names[0], $displayed_issues));
    $this->assertTrue(in_array($generated_names[1], $displayed_issues));
    $this->assertTrue(in_array($generated_names[2], $displayed_issues));

    $this->drupalGet('admin/config/services/simplenews/manage/' . $edit_newsletter->id());
    $this->assertFieldByName('name', $edit_newsletter->name, t('Newsletter name is displayed when editing'));
    $this->assertFieldByName('description', $edit_newsletter->description, t('Newsletter description is displayed when editing'));

    $edit = array('block' => FALSE);
    $this->drupalPostForm(NULL, $edit, t('Save'));

    \Drupal::entityTypeManager()->getStorage('simplenews_newsletter')->resetCache();
    $updated_newsletter = Newsletter::load($edit_newsletter->newsletter_id);
    $this->assertEqual(0, $updated_newsletter->block, t('Block for newsletter disabled'));

    $this->drupalGet('admin/structure/block');
    $this->assertNoText($edit_newsletter->name, t('Newsletter block was removed'));

    // Delete a newsletter.
    $this->drupalGet('admin/config/services/simplenews/manage/' . $edit_newsletter->id());
    $this->clickLink(t('Delete'));
    $this->drupalPostForm(NULL, array(), t('Delete'));

    // Verify that the newsletter has been deleted.
    \Drupal::entityTypeManager()->getStorage('simplenews_newsletter')->resetCache();
    $this->assertFalse(Newsletter::load($edit_newsletter->newsletter_id));
    $this->assertFalse(db_query('SELECT newsletter_id FROM {simplenews_newsletter} WHERE newsletter_id = :newsletter_id', array(':newsletter_id' => $edit_newsletter->newsletter_id))->fetchField());*/
    // @codingStandardsIgnoreEnd
    // Check if the help text is displayed.
    $this->drupalGet('admin/help/simplenews');
    $this->assertText('Simplenews adds elements to the newsletter node add/edit');
    $this->drupalGet('admin/config/services/simplenews/add');
    $this->assertText('You can create different newsletters (or subjects)');
  }

  /**
   * Test newsletter subscription management.
   *
   * Steps performed:
   */
  public function testSubscriptionManagement() {
    $admin_user = $this->drupalCreateUser([
      'administer newsletters',
      'administer simplenews settings',
      'administer simplenews subscriptions',
      'administer users',
    ]);
    $this->drupalLogin($admin_user);

    // Create a newsletter.
    $newsletter_name = mb_strtolower($this->randomMachineName());
    $edit = [
      'name' => $newsletter_name,
      'id'  => $newsletter_name,
    ];
    $this->drupalPostForm('admin/config/services/simplenews/add', $edit, t('Save'));

    // This test adds a number of subscribers to each newsletter separately and
    // then adds another bunch to both. First step is to create some arrays
    // that describe the actions to take.
    $subscribers = [];

    $groups = [];
    $newsletters = simplenews_newsletter_get_all();
    foreach ($newsletters as $newsletter) {
      $groups[$newsletter->id()] = [$newsletter->id()];
    }
    $groups['all'] = array_keys($groups);

    $subscribers_flat = [];
    foreach ($groups as $key => $group) {
      for ($i = 0; $i < 5; $i++) {
        $mail = $this->randomEmail();
        $subscribers[$key][$mail] = $mail;
        $subscribers_flat[$mail] = $mail;
      }
    }

    // Create a user and assign one of the mail addresses of the all group.
    // The other subscribers will not be users, just anonymous subscribers.
    $user = $this->drupalCreateUser(['subscribe to newsletters']);
    // Make sure that user_save() does not update the user object, as it will
    // override the pass_raw property which we'll need to log this user in
    // later on.
    $user_mail = current($subscribers['all']);
    $user->setEmail($user_mail);
    $user->save();

    $delimiters = [',', ' ', "\n"];

    // Add the subscribers using mass subscribe.
    $this->drupalGet('admin/people');
    $this->clickLink('Subscribers');
    $i = 0;
    foreach ($groups as $key => $group) {
      $this->clickLink(t('Mass subscribe'));
      $edit = [
        // Implode with a different, supported delimiter for each group.
        'emails' => implode($delimiters[$i++], $subscribers[$key]),
      ];
      foreach ($group as $newsletter_id) {
        $edit['newsletters[' . $newsletter_id . ']'] = TRUE;
      }
      $this->drupalPostForm(NULL, $edit, t('Subscribe'));
    }

    // Verify that all addresses are displayed in the table.
    $rows = $this->xpath('//tbody/tr');
    $mail_addresses = [];
    for ($i = 0; $i < count($subscribers_flat); $i++) {
      $email = trim($rows[$i]->find('xpath', '/td[1]')->getText());
      $mail_addresses[] = $email;
      if ($email == $user_mail) {
        // The user to which the mail was assigned should show the user name.
        $this->assertEqual(trim($rows[$i]->find('xpath', '/td[2]/a')->getText()), $user->getAccountName());
      }
      else {
        // Blank value for user name.
        $this->assertEqual($rows[$i]->find('xpath', '/td[2]/a'), NULL);
      }
    }
    $this->assertEqual(15, count($mail_addresses));
    foreach ($mail_addresses as $mail_address) {
      $mail_address = (string) $mail_address;
      $this->assertTrue(isset($subscribers_flat[$mail_address]));
      unset($subscribers_flat[$mail_address]);
    }
    // All entries of the array should be removed by now.
    $this->assertTrue(empty($subscribers_flat));

    reset($groups);
    $first = 'default';

    $first_mail = array_rand($subscribers[$first]);
    $all_mail = array_rand($subscribers['all']);

    // Limit list to subscribers of the first newsletter only.
    // Build a flat list of the subscribers of this list.
    $subscribers_flat = array_merge($subscribers[$first], $subscribers['all']);

    $this->drupalGet('admin/people/simplenews', ['query' => ['subscriptions_target_id' => $first]]);

    // Verify that all addresses are displayed in the table.
    $rows = $this->xpath('//tbody/tr');
    $mail_addresses = [];
    for ($i = 0; $i < count($subscribers_flat); $i++) {
      $mail_addresses[] = trim($rows[$i]->find('xpath', '/td[1]')->getText());
    }
    $this->assertEqual(10, count($mail_addresses));
    foreach ($mail_addresses as $mail_address) {
      $mail_address = (string) $mail_address;
      $this->assertTrue(isset($subscribers_flat[$mail_address]));
      unset($subscribers_flat[$mail_address]);
    }
    // All entries of the array should be removed by now.
    $this->assertTrue(empty($subscribers_flat));

    // Filter a single mail address, the one assigned to a user.
    $edit = [
      'mail' => mb_substr(current($subscribers['all']), 0, 4),
    ];
    $this->drupalGet('admin/people/simplenews', ['query' => ['mail' => $edit['mail']]]);

    $rows = $this->xpath('//tbody/tr');
    $this->assertEqual(1, count($rows));
    $this->assertEqual(current($subscribers['all']), trim($rows[0]->find('xpath', '/td[1]')->getText()));
    // Mysteriously, the username is sometimes a span and sometimes a link.
    // Accept both.
    $this->assertEqual($user->label(), trim($rows[0]->find('xpath', '/td[2]/span|/td[2]/a')->getText()));

    // Reset the filter.
    $this->drupalGet('admin/people/simplenews');

    // Test mass-unsubscribe, unsubscribe one from the first group and one from
    // the all group, but only from the first newsletter.
    unset($subscribers[$first][$first_mail]);
    $edit = [
      'emails' => $first_mail . ', ' . $all_mail,
      'newsletters[' . $first . ']' => TRUE,
    ];
    $this->clickLink(t('Mass unsubscribe'));
    $this->drupalPostForm(NULL, $edit, t('Unsubscribe'));

    // The all mail is still displayed because it's still subscribed to the
    // second newsletter. Reload the page to get rid of the confirmation
    // message.
    $this->drupalGet('admin/people/simplenews');
    $this->assertNoText($first_mail);
    $this->assertText($all_mail);

    // Limit to first newsletter, the all mail shouldn't be shown anymore.
    $this->drupalGet('admin/people/simplenews', ['query' => ['subscriptions_target_id' => $first]]);
    $this->assertNoText($first_mail);
    $this->assertNoText($all_mail);

    // Check exporting.
    $this->clickLink(t('Export'));
    $this->drupalPostForm(NULL, ['newsletters[' . $first . ']' => TRUE], t('Export'));
    $export_field = $this->xpath($this->constructFieldXpath('name', 'emails'));
    $exported_mails = $export_field[0]->getText();
    foreach ($subscribers[$first] as $mail) {
      $this->assertTrue(strpos($exported_mails, $mail) !== FALSE, t('Mail address exported correctly.'));
    }
    foreach ($subscribers['all'] as $mail) {
      if ($mail != $all_mail) {
        $this->assertTrue(strpos($exported_mails, $mail) !== FALSE, t('Mail address exported correctly.'));
      }
      else {
        $this->assertFalse(strpos($exported_mails, $mail), t('Unsubscribed mail address not exported.'));
      }
    }

    // Only export unsubscribed mail addresses.
    $edit = [
      'subscribed[subscribed]' => FALSE,
      'subscribed[unsubscribed]' => TRUE,
      'newsletters[' . $first . ']' => TRUE,
    ];
    $this->drupalPostForm(NULL, $edit, t('Export'));

    $export_field = $this->xpath($this->constructFieldXpath('name', 'emails'));
    $exported_mails = $export_field[0]->getText();
    $exported_mails = explode(', ', $exported_mails);
    $this->assertEqual(2, count($exported_mails));
    $this->assertTrue(in_array($all_mail, $exported_mails));
    $this->assertTrue(in_array($first_mail, $exported_mails));

    /** @var \Drupal\simplenews\Subscription\SubscriptionManagerInterface $subscription_manager */
    $subscription_manager = \Drupal::service('simplenews.subscription_manager');

    // Make sure there are unconfirmed subscriptions.
    $unconfirmed = [];
    $unconfirmed[] = $this->randomEmail();
    $unconfirmed[] = $this->randomEmail();
    foreach ($unconfirmed as $mail) {
      $subscription_manager->subscribe($mail, $first, TRUE);
    }

    // Export unconfirmed active and inactive users.
    $edit = [
      'states[active]' => TRUE,
      'states[inactive]' => TRUE,
      'subscribed[subscribed]' => FALSE,
      'subscribed[unconfirmed]' => TRUE,
      'subscribed[unsubscribed]' => FALSE,
      'newsletters[' . $first . ']' => TRUE,
    ];
    $this->drupalPostForm(NULL, $edit, t('Export'));

    $export_field = $this->xpath($this->constructFieldXpath('name', 'emails'));
    $exported_mails = $export_field[0]->getText();
    $exported_mails = explode(', ', $exported_mails);
    $this->assertTrue(in_array($unconfirmed[0], $exported_mails));
    $this->assertTrue(in_array($unconfirmed[1], $exported_mails));

    // Only export unconfirmed mail addresses.
    $edit = [
      'subscribed[subscribed]' => FALSE,
      'subscribed[unconfirmed]' => TRUE,
      'subscribed[unsubscribed]' => FALSE,
      'newsletters[' . $first . ']' => TRUE,
    ];
    $this->drupalPostForm(NULL, $edit, t('Export'));

    $export_field = $this->xpath($this->constructFieldXpath('name', 'emails'));
    $exported_mails = $export_field[0]->getText();
    $exported_mails = explode(', ', $exported_mails);
    $this->assertEqual(2, count($exported_mails));
    $this->assertTrue(in_array($unconfirmed[0], $exported_mails));
    $this->assertTrue(in_array($unconfirmed[1], $exported_mails));

    // Make sure the user is subscribed to the first newsletter_id.
    $spool_storage = \Drupal::service('simplenews.spool_storage');
    $issue = Node::create([
      'type' => 'simplenews_issue',
      'title' => $this->randomString(10),
      'simplenews_issue' => ['target_id' => $first],
    ]);

    $subscription_manager->subscribe($user_mail, $first, FALSE);
    $before_count = $spool_storage->issueCountRecipients($issue);

    // Block the user.
    $user->block();
    $user->save();

    $this->drupalGet('admin/people/simplenews');

    // Verify updated subscriptions count.
    drupal_static_reset('Drupal\simplenews\Plugin\simplenews\RecipientHandler\RecipientHandlerBase::count');
    $after_count = $spool_storage->issueCountRecipients($issue);
    $this->assertEqual($before_count - 1, $after_count, t('Blocked users are not counted in subscription count.'));

    // Test mass subscribe with previously unsubscribed users.
    for ($i = 0; $i < 3; $i++) {
      $tested_subscribers[] = $this->randomEmail();
    }
    $subscription_manager->subscribe($tested_subscribers[0], $first, FALSE);
    $subscription_manager->subscribe($tested_subscribers[1], $first, FALSE);
    $subscription_manager->unsubscribe($tested_subscribers[0], $first, FALSE);
    $subscription_manager->unsubscribe($tested_subscribers[1], $first, FALSE);
    $unsubscribed = implode(', ', array_slice($tested_subscribers, 0, 2));
    $edit = [
      'emails' => implode(', ', $tested_subscribers),
      'newsletters[' . $first . ']' => TRUE,
    ];

    $this->drupalPostForm('admin/people/simplenews/import', $edit, t('Subscribe'));
    \Drupal::entityTypeManager()->getStorage('simplenews_subscriber')->resetCache();
    $subscription_manager->reset();
    $this->assertFalse($subscription_manager->isSubscribed($tested_subscribers[0], $first), t('Subscriber not resubscribed through mass subscription.'));
    $this->assertFalse($subscription_manager->isSubscribed($tested_subscribers[1], $first), t('Subscriber not resubscribed through mass subscription.'));
    $this->assertTrue($subscription_manager->isSubscribed($tested_subscribers[2], $first), t('Subscriber subscribed through mass subscription.'));
    $substitutes = ['@name' => Newsletter::load($first)->label(), '@mail' => $unsubscribed];
    $this->assertText(t('The following addresses were skipped because they have previously unsubscribed from @name: @mail.', $substitutes));
    $this->assertText(t("If you would like to resubscribe them, use the 'Force resubscription' option."));

    // Try to mass subscribe without specifying newsletters.
    $tested_subscribers[2] = $this->randomEmail();
    $edit = [
      'emails' => implode(', ', $tested_subscribers),
      'resubscribe' => TRUE,
    ];

    $this->drupalPostForm('admin/people/simplenews/import', $edit, t('Subscribe'));
    $this->assertText('Subscribe to field is required.');

    // Test mass subscribe with previously unsubscribed users and force
    // resubscription.
    $tested_subscribers[2] = $this->randomEmail();
    $edit = [
      'emails' => implode(', ', $tested_subscribers),
      'newsletters[' . $first . ']' => TRUE,
      'resubscribe' => TRUE,
    ];
    $this->drupalPostForm('admin/people/simplenews/import', $edit, t('Subscribe'));

    $subscription_manager->reset();
    \Drupal::entityTypeManager()->getStorage('simplenews_subscriber')->resetCache();
    $this->assertTrue($subscription_manager->isSubscribed($tested_subscribers[0], $first, t('Subscriber resubscribed trough mass subscription.')));
    $this->assertTrue($subscription_manager->isSubscribed($tested_subscribers[1], $first, t('Subscriber resubscribed trough mass subscription.')));
    $this->assertTrue($subscription_manager->isSubscribed($tested_subscribers[2], $first, t('Subscriber subscribed trough mass subscription.')));

    // Try to mass unsubscribe without specifying newsletters.
    $tested_subscribers[2] = $this->randomEmail();
    $edit = [
      'emails' => implode(', ', $tested_subscribers),
    ];

    $this->drupalPostForm('admin/people/simplenews/unsubscribe', $edit, t('Unsubscribe'));
    $this->assertText('Unsubscribe from field is required.');

    // Create two blocks, to ensure that they are updated/deleted when a
    // newsletter is deleted.
    $only_first_block = $this->setupSubscriptionBlock(['newsletters' => [$first]]);
    $all_block = $this->setupSubscriptionBlock(['newsletters' => array_keys($groups)]);
    $enabled_newsletters = $all_block->get('settings')['newsletters'];
    $this->assertTrue(in_array($first, $enabled_newsletters));

    // Delete newsletter.
    \Drupal::entityTypeManager()->getStorage('simplenews_newsletter')->resetCache();
    $this->drupalGet('admin/config/services/simplenews/manage/' . $first);
    $this->clickLink(t('Delete'));
    $this->drupalPostForm(NULL, [], t('Delete'));

    $this->assertText(t('All subscriptions to newsletter @newsletter have been deleted.', ['@newsletter' => $newsletters[$first]->name]));

    // Verify that all related data has been deleted/updated.
    $this->assertNull(Newsletter::load($first));
    $this->assertNull(Block::load($only_first_block->id()));

    $all_block = Block::load($all_block->id());
    $enabled_newsletters = $all_block->get('settings')['newsletters'];
    $this->assertFalse(in_array($first, $enabled_newsletters));

    // Verify that all subscriptions of that newsletter have been removed.
    $this->drupalGet('admin/people/simplenews');
    foreach ($subscribers[$first] as $mail) {
      $this->assertNoText($mail);
    }

    $this->clickLink(t('Edit'), 1);

    // Get the subscriber id from the path.
    $this->assertEqual(1, preg_match('|admin/people/simplenews/edit/(\d+)\?destination|', $this->getUrl(), $matches), 'Subscriber found');
    $subscriber = Subscriber::load($matches[1]);

    $this->assertTitle('Edit subscriber ' . $subscriber->getMail() . ' | Drupal');
    $this->assertFieldChecked('edit-status');

    // Disable account.
    $edit = [
      'status' => FALSE,
    ];
    $this->drupalPostForm(NULL, $edit, t('Save'));
    \Drupal::entityTypeManager()->getStorage('simplenews_subscriber')->resetCache();
    $subscription_manager->reset();
    $this->assertFalse($subscription_manager->isSubscribed($subscriber->getMail(), $this->getRandomNewsletter()), t('Subscriber is not active'));

    // Re-enable account.
    $this->drupalGet('admin/people/simplenews/edit/' . $subscriber->id());
    $this->assertTitle('Edit subscriber ' . $subscriber->getMail() . ' | Drupal');
    $this->assertNoFieldChecked('edit-status');
    $edit = [
      'status' => TRUE,
    ];
    $this->drupalPostForm(NULL, $edit, t('Save'));
    \Drupal::entityTypeManager()->getStorage('simplenews_subscriber')->resetCache();
    $subscription_manager->reset();
    $this->assertTrue($subscription_manager->isSubscribed($subscriber->getMail(), $this->getRandomNewsletter()), t('Subscriber is active again.'));

    // Remove the newsletter.
    $this->drupalGet('admin/people/simplenews/edit/' . $subscriber->id());
    $this->assertTitle('Edit subscriber ' . $subscriber->getMail() . ' | Drupal');
    \Drupal::entityTypeManager()->getStorage('simplenews_subscriber')->resetCache();
    $subscriber = Subscriber::load($subscriber->id());
    $nlids = $subscriber->getSubscribedNewsletterIds();
    // If the subscriber still has subscribed to newsletter, try to unsubscribe.
    $newsletter_id = reset($nlids);
    $edit['subscriptions[' . $newsletter_id . ']'] = FALSE;
    $this->drupalPostForm(NULL, $edit, t('Save'));
    \Drupal::entityTypeManager()->getStorage('simplenews_subscriber')->resetCache();
    $subscription_manager->reset();
    $nlids = $subscriber->getSubscribedNewsletterIds();
    $this->assertFalse($subscription_manager->isSubscribed($subscriber->getMail(), reset($nlids)), t('Subscriber not subscribed anymore.'));

    /*
     * @todo Test Admin subscriber edit preferred $subscription->language
     */

    // Register a subscriber with an insecure e-mail address through the API
    // and make sure the address is correctly encoded.
    $xss_mail = "<script>alert('XSS');</script>";
    $subscription_manager->subscribe($xss_mail, $this->getRandomNewsletter(), FALSE);
    $this->drupalGet('admin/people/simplenews');
    $this->assertNoRaw($xss_mail);
    $this->assertRaw(Html::escape($xss_mail));

    $xss_subscriber = Subscriber::loadByMail($xss_mail);
    $this->drupalGet('admin/people/simplenews/edit/' . $xss_subscriber->id());
    $this->assertNoRaw($xss_mail);
    $this->assertRaw(Html::escape($xss_mail));

    // Create a new user for the next test.
    $new_user = $this->drupalCreateUser(['subscribe to newsletters']);
    // Test for saving the subscription for no newsletter.
    $this->drupalPostForm('user/' . $new_user->id() . '/simplenews', NULL, t('Save'));
    $this->assertText('The newsletter subscriptions for user ' . $new_user->getAccountName() . ' have been updated.');

    // Editing a subscriber with subscription.
    $edit = [
      'subscriptions[' . $newsletter_name . ']' => TRUE,
      'status' => TRUE,
      'mail[0][value]' => 'edit@example.com',
    ];
    $this->drupalPostForm('admin/people/simplenews/edit/' . $xss_subscriber->id(), $edit, t('Save'));
    $this->assertText('Subscriber edit@example.com has been updated.');

    // Create a second newsletter.
    $second_newsletter_name = mb_strtolower($this->randomMachineName());
    $edit2 = [
      'name' => $second_newsletter_name,
      'id'  => $second_newsletter_name,
    ];
    $this->drupalPostForm('admin/config/services/simplenews/add', $edit2, t('Save'));

    // Test for adding a subscriber.
    $subscribe = [
      'newsletters[' . $newsletter_name . ']' => TRUE,
      'emails' => 'drupaltest@example.com',
    ];
    $this->drupalPostForm('admin/people/simplenews/import', $subscribe, t('Subscribe'));

    // The subscriber should appear once in the list.
    $rows = $this->xpath('//tbody/tr');
    $counter = 0;
    foreach ($rows as $value) {
      if (trim($value->find('xpath', '/td[1]')->getText()) == 'drupaltest@example.com') {
        $counter++;
      }
    }
    $this->assertEqual(1, $counter);
    $this->assertText(t('The following addresses were added or updated: @email.', ['@email' => 'drupaltest@example.com']));
    $this->assertText(t('The addresses were subscribed to the following newsletters: @newsletter.', ['@newsletter' => $newsletter_name]));

    // Check exact subscription statuses.
    $subscriber = Subscriber::loadByMail('drupaltest@example.com');
    $this->assertEqual($subscriber->getSubscription($newsletter_name)->get('status')->getValue(), SIMPLENEWS_SUBSCRIPTION_STATUS_SUBSCRIBED);
    // The second newsletter was not subscribed, so there should be no
    // subscription record at all.
    $this->assertFalse($subscriber->getSubscription($second_newsletter_name));
  }

  /**
   * Test content type configuration.
   */
  public function testContentTypes() {
    $admin_user = $this->drupalCreateUser([
      'administer blocks',
      'administer content types',
      'administer nodes',
      'access administration pages',
      'administer permissions',
      'administer newsletters',
      'administer simplenews subscriptions',
      'bypass node access',
      'send newsletter',
    ]);
    $this->drupalLogin($admin_user);

    $this->drupalGet('admin/structure/types');
    $this->clickLink(t('Add content type'));
    $name = $this->randomMachineName();
    $type = strtolower($name);
    $edit = [
      'name' => $name,
      'type' => $type,
      'simplenews_content_type' => TRUE,
    ];
    $this->drupalPostForm(NULL, $edit, t('Save content type'));

    // Verify that the newsletter settings are shown.
    $this->drupalGet('node/add/' . $type);
    $this->assertText(t('Issue'));

    // Create an issue.
    $edit = [
      'title[0][value]' => $this->randomMachineName(),
      'body[0][value]' => 'User ID: [current-user:uid]',
      'simplenews_issue[target_id]' => $this->getRandomNewsletter(),
    ];
    $this->drupalPostForm(NULL, $edit, ('Save'));

    $node = $this->drupalGetNodeByTitle($edit['title[0][value]']);

    $edit = [
      'title[0][value]' => $this->randomMachineName(),
      'body[0][value]' => 'Sample body text - Newsletter issue',
      'simplenews_issue[target_id]' => $this->getRandomNewsletter(),
    ];
    $this->drupalPostForm('node/add/simplenews_issue', $edit, ('Save'));

    // Assert that body text is displayed.
    $this->assertText('Sample body text - Newsletter issue');

    $node2 = $this->drupalGetNodeByTitle($edit['title[0][value]']);

    // Assert subscriber count.
    $this->clickLink(t('Newsletter'));
    $this->assertText(t('Send newsletter issue to 0 subscribers.'));

    // Create some subscribers.
    $subscribers = [];
    for ($i = 0; $i < 3; $i++) {
      $subscribers[] = Subscriber::create(['mail' => $this->randomEmail()]);
    }
    foreach ($subscribers as $subscriber) {
      $subscriber->setStatus(TRUE);
    }

    // Subscribe to the default newsletter and set subscriber status.
    $subscribers[0]->subscribe('default', SIMPLENEWS_SUBSCRIPTION_STATUS_SUBSCRIBED);
    $subscribers[1]->subscribe('default', SIMPLENEWS_SUBSCRIPTION_STATUS_SUBSCRIBED);
    $subscribers[2]->subscribe('default', SIMPLENEWS_SUBSCRIPTION_STATUS_SUBSCRIBED);

    foreach ($subscribers as $subscriber) {
      $subscriber->save();
    }

    // Check if the subscribers are listed in the newsletter tab.
    $this->drupalGet('node/1/simplenews');
    $this->assertText('Send newsletter issue to 3 subscribers.');

    // Send mails.
    $this->assertField('test_address', $admin_user->getEmail());
    // Test newsletter to empty address and check the error message.
    $this->drupalPostForm(NULL, ['test_address' => ''], t('Send test newsletter issue'));
    $this->assertText(t('Missing test email address.'));
    // Test newsletter to invalid address and check the error message.
    $this->drupalPostForm(NULL, ['test_address' => 'invalid_address'], t('Send test newsletter issue'));
    $this->assertText(t('Invalid email address "invalid_address"'));
    $this->drupalPostForm(NULL, ['test_address' => $admin_user->getEmail()], t('Send test newsletter issue'));
    $this->assertText(t('Test newsletter sent to user @name &lt;@email&gt;', ['@name' => $admin_user->getAccountName(), '@email' => $admin_user->getEmail()]));

    $mails = $this->getMails();
    $this->assertEqual('simplenews_test', $mails[0]['id']);
    $this->assertEqual($admin_user->getEmail(), $mails[0]['to']);
    $this->assertEqual(t('[Default newsletter] @title', ['@title' => $node->getTitle()]), $mails[0]['subject']);
    $this->assertTrue(strpos($mails[0]['body'], 'User ID: ' . $admin_user->id()) !== FALSE);

    // Update the content type, remove the simpletest checkbox.
    $edit = [
      'simplenews_content_type' => FALSE,
    ];
    $this->drupalPostForm('admin/structure/types/manage/' . $type, $edit, t('Save content type'));

    // Verify that the newsletter settings are still shown.
    // Note: Previously the field got autoremoved. We leave it remaining due to
    // potential data loss.
    $this->drupalGet('node/add/' . $type);
    $this->assertNoText(t('Replacement patterns'));
    $this->assertText(t('Issue'));

    // Test the visibility of subscription user component.
    $this->drupalGet('node/' . $node->id());
    $this->assertNoText('Subscribed to');

    // Delete created nodes.
    $node->delete();
    $node2->delete();

    // @todo: Test node update/delete.
    // Delete content type.
    // @todo: Add assertions.
    $this->drupalPostForm('admin/structure/types/manage/' . $type . '/delete', [], t('Delete'));

    // Check the Add Newsletter Issue button.
    $this->drupalGet('admin/content/simplenews');
    $this->clickLink(t('Add Newsletter Issue'));
    $this->assertUrl('node/add/simplenews_issue');
    // Check if the help text is displayed.
    $this->assertText('Add this newsletter issue to a newsletter by selecting a newsletter from the select list.');
  }

  /**
   * Test content subscription status filter in subscriber view.
   */
  public function testSubscriberStatusFilter() {
    // Make sure subscription overview can't be accessed without permission.
    $this->drupalGet('admin/people/simplenews');
    $this->assertResponse(403);

    $admin_user = $this->drupalCreateUser([
      'administer newsletters',
      'create simplenews_issue content',
      'administer nodes',
      'administer simplenews subscriptions',
    ]);
    $this->drupalLogin($admin_user);

    $subscribers = [];
    // Create some subscribers.
    for ($i = 0; $i < 3; $i++) {
      $subscribers[] = Subscriber::create(['mail' => $this->randomEmail()]);
    }
    foreach ($subscribers as $subscriber) {
      $subscriber->setStatus(TRUE);
    }

    // Subscribe to the default newsletter and set subscriber status.
    $subscribers[0]->subscribe('default', SIMPLENEWS_SUBSCRIPTION_STATUS_SUBSCRIBED);
    $subscribers[1]->subscribe('default', SIMPLENEWS_SUBSCRIPTION_STATUS_UNCONFIRMED);
    $subscribers[2]->subscribe('default', SIMPLENEWS_SUBSCRIPTION_STATUS_UNSUBSCRIBED);

    foreach ($subscribers as $subscriber) {
      $subscriber->save();
    }

    $newsletters = simplenews_newsletter_get_all();

    // Filter out subscribers by their subscription status and assert the
    // output.
    $this->drupalGet('admin/people/simplenews', ['query' => ['subscriptions_status' => SIMPLENEWS_SUBSCRIPTION_STATUS_SUBSCRIBED]]);
    $row = $this->xpath('//tbody/tr');
    $this->assertEqual(1, count($row));
    $this->assertEqual($subscribers[0]->getMail(), trim($row[0]->find('xpath', '/td')->getText()));
    $this->drupalGet('admin/people/simplenews', ['query' => ['subscriptions_status' => SIMPLENEWS_SUBSCRIPTION_STATUS_UNCONFIRMED]]);
    $row = $this->xpath('//tbody/tr');
    $this->assertEqual(1, count($row));
    $this->assertEqual($subscribers[1]->getMail(), trim($row[0]->find('xpath', '/td')->getText()));
    $this->assertText($newsletters['default']->name . ' (' . t('Unconfirmed') . ')');
    $this->drupalGet('admin/people/simplenews', ['query' => ['subscriptions_status' => SIMPLENEWS_SUBSCRIPTION_STATUS_UNSUBSCRIBED]]);
    $row = $this->xpath('//tbody/tr');
    $this->assertEqual(1, count($row));
    $this->assertEqual($subscribers[2]->getMail(), trim($row[0]->find('xpath', '/td')->getText()));
    $this->assertText($newsletters['default']->name . ' (' . t('Unsubscribed') . ')');
  }

  /**
   * Test newsletter issue overview.
   */
  public function testNewsletterIssuesOverview() {
    // Verify newsletter overview isn't available without permission.
    $this->drupalGet('admin/content/simplenews');
    $this->assertResponse(403);

    $admin_user = $this->drupalCreateUser([
      'administer newsletters',
      'create simplenews_issue content',
      'administer simplenews subscriptions',
      'administer nodes',
      'send newsletter',
    ]);
    $this->drupalLogin($admin_user);

    // Create a newsletter.
    $name = $this->randomMachineName();
    $edit = [
      'name' => $name,
      'id'  => mb_strtolower($name),
    ];
    $this->drupalPostForm('admin/config/services/simplenews/add', $edit, t('Save'));

    // Create a newsletter issue and publish.
    $edit = [
      'title[0][value]' => 'Test_issue_1',
      'simplenews_issue[target_id]' => mb_strtolower($name),
    ];
    $this->drupalPostForm('node/add/simplenews_issue', $edit, t('Save'));

    // Create another newsletter issue and keep unpublished.
    $edit = [
      'title[0][value]' => 'Test_issue_2',
      'simplenews_issue[target_id]' => mb_strtolower($name),
      'status[value]' => FALSE,
    ];
    $this->drupalPostForm('node/add/simplenews_issue', $edit, t('Save'));

    // Test mass subscribe with previously unsubscribed users.
    for ($i = 0; $i < 3; $i++) {
      $subscribers[] = $this->randomEmail();
    }
    $edit = [
      'emails' => implode(', ', $subscribers),
      'newsletters[' . mb_strtolower($name) . ']' => TRUE,
    ];
    $this->drupalPostForm('admin/people/simplenews/import', $edit, t('Subscribe'));

    $this->drupalGet('admin/content/simplenews');
    // Check the correct values are present in the view.
    $rows = $this->xpath('//tbody/tr');
    // Check the number of results in the view.
    $this->assertEqual(2, count($rows));

    foreach ($rows as $row) {
      if ($row->find('xpath', '/td[2]/a')->getText() == 'Test_issue_2') {
        $this->assertEqual($name, trim($row->find('xpath', '/td[3]/a')->getText()));
        $this->assertEqual('Newsletter issue will be sent to 3 subscribers.', trim($row->find('xpath', '/td[6]/span')->getAttribute('title')));
        $this->assertEqual('✖', trim($row->find('xpath', '/td[4]')->getText()));
        $this->assertEqual('0/3', trim($row->find('xpath', '/td[6]/span')->getText()));
      }
      else {
        $this->assertEqual('✔', trim($row->find('xpath', '/td[4]')->getText()));
      }
    }
    // Send newsletter issues using bulk operations.
    $edit = [
      'node_bulk_form[0]' => TRUE,
      'node_bulk_form[1]' => TRUE,
      'action' => 'simplenews_send_action',
    ];
    $this->drupalPostForm(NULL, $edit, t('Apply to selected items'));
    // Check the relevant messages.
    $this->assertText('Newsletter issue Test_issue_2 will be sent when published.');
    $this->assertText('Newsletter issue Test_issue_1 pending.');
    $rows = $this->xpath('//tbody/tr');
    // Assert the status message of each newsletter.
    foreach ($rows as $row) {
      if ($row->find('xpath', '/td[2]/a')->getText() == 'Test_issue_2') {
        $this->assertEqual('Newsletter issue will be sent to 3 subscribers on publish.', trim($row->find('xpath', '/td[6]/span')->getAttribute('title')));
      }
      else {
        $this->assertEqual('Newsletter issue is pending, 0 mails sent out of 3, 0 errors.', trim($row->find('xpath', '/td[6]/img')->getAttribute('title')));
        $this->assertEqual(file_url_transform_relative(file_create_url(drupal_get_path('module', 'simplenews') . '/images/sn-cron.png')), trim($row->find('xpath', '/td[6]/img')->getAttribute('src')));
      }
    }
    // Stop sending the pending newsletters.
    $edit = [
      'node_bulk_form[0]' => TRUE,
      'node_bulk_form[1]' => TRUE,
      'action' => 'simplenews_stop_action',
    ];
    $this->drupalPostForm(NULL, $edit, t('Apply to selected items'));
    // Check the stop message.
    $this->assertText('Sending of Test_issue_1 was stopped. 3 pending email(s) were deleted.');
    $rows = $this->xpath('//tbody/tr');
    // Check the send status of each issue.
    foreach ($rows as $row) {
      if ($row->find('xpath', '/td[2]/a')->getText() == 'Test_issue_2') {
        $this->assertEqual('Newsletter issue will be sent to 3 subscribers on publish.', trim($row->find('xpath', '/td[6]/span')->getAttribute('title')));
      }
      else {
        $this->assertEqual('Newsletter issue will be sent to 3 subscribers.', trim($row->find('xpath', '/td[6]/span')->getAttribute('title')));
      }
    }

    // Send newsletter issues using bulk operations.
    $edit = [
      'node_bulk_form[0]' => TRUE,
      'node_bulk_form[1]' => TRUE,
      'action' => 'simplenews_send_action',
    ];
    $this->drupalPostForm(NULL, $edit, t('Apply to selected items'));
    // Run cron to send the mails.
    $this->cronRun();
    $this->drupalGet('admin/content/simplenews');
    $rows = $this->xpath('//tbody/tr');
    // Check the send status of each issue.
    foreach ($rows as $row) {
      if ($row->find('xpath', '/td[2]/a')->getText() == 'Test_issue_2') {
        $this->assertEqual('Newsletter issue will be sent to 3 subscribers on publish.', trim($row->find('xpath', '/td[6]/span')->getAttribute('title')));
      }
      else {
        $this->assertEqual('Newsletter issue sent to 3 subscribers, 0 errors.', trim($row->find('xpath', '/td[6]/img')->getAttribute('title')));
        $this->assertEqual(file_url_transform_relative(file_create_url(drupal_get_path('module', 'simplenews') . '/images/sn-sent.png')), trim($row->find('xpath', '/td[6]/img')->getAttribute('src')));
      }
    }
  }

  /**
   * Test access for subscriber admin page.
   */
  public function testAccess() {
    $admin_user = $this->drupalCreateUser([
      'administer newsletters',
      'administer simplenews subscriptions',
    ]);
    $this->drupalLogin($admin_user);

    // Create a newsletter.
    $newsletter_name = mb_strtolower($this->randomMachineName());
    $edit = [
      'name' => $newsletter_name,
      'id'  => $newsletter_name,
    ];
    $this->drupalPostForm('admin/config/services/simplenews/add', $edit, t('Save'));

    // Create a user and subscribe them.
    $user = $this->drupalCreateUser();
    $subscriber = Subscriber::create(['mail' => $user->getEmail()]);
    $subscriber->subscribe('default', SIMPLENEWS_SUBSCRIPTION_STATUS_SUBSCRIBED);
    $subscriber->setStatus(TRUE);
    $subscriber->save();

    // Check anonymous user can't access admin page.
    $this->drupalLogout();
    $this->drupalGet('admin/people/simplenews');
    $this->assertResponse(403);

    // Turn off the access permission on the view.
    $view = View::load('simplenews_subscribers');
    $display = &$view->getDisplay('default');
    $display['display_options']['access'] = ['type' => 'none', 'options' => []];
    $view->save();
    \Drupal::service('router.builder')->rebuild();

    // Check username is public but email is not shown.
    $this->drupalGet('admin/people/simplenews');
    $this->assertText($user->getAccountName());
    $this->assertNoText($user->getEmail());

    // Grant view permission.
    $view_user = $this->drupalCreateUser([
      'view simplenews subscriptions',
    ]);
    $this->drupalLogin($view_user);

    // Check can see username and email.
    $this->drupalGet('admin/people/simplenews');
    $this->assertText($user->getAccountName());
    $this->assertText($user->getEmail());
  }

}
