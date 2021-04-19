<?php

namespace Drupal\splash_screen\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Redirect .html pages to corresponding Node page.
 */
class CustomredirectSubscriber implements EventSubscriberInterface {

  /**
   * Define redirectCode here.
   *
   * @var redirectCode
   */
  private $redirectCode = 301;

  /**
   * Redirect pattern based url.
   *
   * @param \Symfony\Component\HttpKernel\Event\GetResponseEvent $event
   *   Get event response.
   */
  public function customRedirection(GetResponseEvent $event) {

    $request = \Drupal::request();
    $requestUrl = $request->server->get('REQUEST_URI', NULL);

    /*
     * Here i am redirecting the about-us.html to respective about-us node.
     * Here you can implement your logic and search the URL in the DB
     * and redirect them on the respective node.
     */
    if ($requestUrl) {
      // $response = new RedirectResponse("/admin/content/splash-screen/add");
      // $response->send();
    }
  }

  /**
   * Listen to kernel.request events and call customRedirection.
   */
  public static function getSubscribedEvents() {
    $events[KernelEvents::REQUEST][] = ['customRedirection'];
    return $events;
  }

}
