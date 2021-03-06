<?php

namespace Drupal\social_auth_twitter\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\social_auth_twitter\TwitterAuthManager;
use Drupal\social_api\Plugin\NetworkManager;
use Drupal\social_auth\SocialAuthUserManager;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Zend\Diactoros\Response\RedirectResponse;

/**
 * Manages requests to Twitter API.
 */
class TwitterAuthController extends ControllerBase {

  /**
   * The network plugin manager.
   *
   * @var \Drupal\social_api\Plugin\NetworkManager
   */
  private $networkManager;

  /**
   * The Twitter authentication manager.
   *
   * @var \Drupal\social_auth_twitter\TwitterAuthManager
   */
  private $twitterManager;

  /**
   * The user manager.
   *
   * @var \Drupal\social_auth\SocialAuthUserManager
   */
  private $userManager;

  /**
   * The session manager.
   *
   * @var \Symfony\Component\HttpFoundation\Session\SessionInterface
   */
  protected $session;

  /**
   * TwitterLoginController constructor.
   *
   * @param \Drupal\social_api\Plugin\NetworkManager $network_manager
   *   Used to get an instance of social_auth_twitter network plugin.
   * @param \Drupal\social_auth_twitter\TwitterAuthManager $twitter_manager
   *   Used to manage authentication methods.
   * @param \Drupal\social_auth\SocialAuthUserManager $user_manager
   *   Manages user login/registration.
   * @param \Symfony\Component\HttpFoundation\Session\SessionInterface $session
   *   Used to store the access token into a session variable.
   */
  public function __construct(NetworkManager $network_manager, TwitterAuthManager $twitter_manager, SocialAuthUserManager $user_manager, SessionInterface $session) {
    $this->networkManager = $network_manager;
    $this->twitterManager = $twitter_manager;
    $this->userManager = $user_manager;
    $this->session = $session;

    // Sets the plugin id.
    $this->userManager->setPluginId('social_auth_twitter');

    // Sets the session keys to nullify if user could not logged in.
    $this->userManager->setSessionKeysToNullify(['social_auth_twitter_access_token']);
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('plugin.network.manager'),
      $container->get('twitter_auth.manager'),
      $container->get('social_auth.user_manager'),
      $container->get('session')
    );
  }

  /**
   * Redirect to Twitter Services Authentication page.
   *
   * @return \Zend\Diactoros\Response\RedirectResponse
   *   Redirection to Twitter Accounts.
   */
  public function redirectToTwitter() {
    /* @var \Drupal\social_auth_twitter\Plugin\Network\TwitterAuth $network_plugin */
    // Creates an instance of the social_auth_twitter Network Plugin.
    $network_plugin = $this->networkManager->createInstance('social_auth_twitter');
    try {
      /* @var \Abraham\TwitterOAuth\TwitterOAuth $connection */
      $connection = $network_plugin->getSdk();

      // Requests Twitter to get temporary tokens.
      $request_token = $connection->oauth('oauth/request_token', ['oauth_callback' => $network_plugin->getOauthCallback()]);

      // Saves the temporary token values in session.
      $this->twitterManager->setOauthToken($request_token['oauth_token']);
      $this->twitterManager->setOauthTokenSecret($request_token['oauth_token_secret']);

      // Generates url for user authentication.
      $url = $connection->url('oauth/authorize', ['oauth_token' => $request_token['oauth_token']]);

      // Redirects the user to allow him to grant permissions.
      return new RedirectResponse($url);
    }
    catch (\Exception $ex) {
      drupal_set_message($this->t('You could not be authenticated, please contact the administrator'), 'error');
    }
    return $this->redirect('user.login');
  }

  /**
   * Callback function to login user.
   */
  public function callback() {
    $oauth_token = $this->twitterManager->getOauthToken();
    $oauth_token_secret = $this->twitterManager->getOauthTokenSecret();
    /* @var \Abraham\TwitterOAuth\TwitterOAuth $client */
    $client = $this->networkManager->createInstance('social_auth_twitter')->getSdk2($oauth_token, $oauth_token_secret);

    // Gets the permanent access token.
    $access_token = $client->oauth('oauth/access_token', ['oauth_verifier' => $this->twitterManager->getOauthVerifier()]);
    $connection = $this->networkManager->createInstance('social_auth_twitter')->getSdk2($access_token['oauth_token'], $access_token['oauth_token_secret']);
    $params = [
      'include_email' => 'true',
      'include_entities' => 'false',
      'skip_status' => 'true',
    ];

    // Saves access token so that event subscribers can call Twitter API.
    $this->session->set('social_auth_twitter_access_token', $access_token);

    // Gets user information.
    $user = $connection->get("account/verify_credentials", $params);

    // If user information could be retrieved.
    if ($user) {
      // Remove _normal from url to get a bigger profile picture.
      $picture = str_replace('_normal', '', $user->profile_image_url_https);

      return $this->userManager->authenticateUser($user->email, $user->name, $user->id, $picture);
    }

    drupal_set_message($this->t('You could not be authenticated, please contact the administrator'), 'error');
    return $this->redirect('user.login');
  }

}
