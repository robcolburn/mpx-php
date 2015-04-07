<?php
/**
 * @file
 * Contains \Mpx\Services\FeedMedia\Client
 */

namespace Mpx\Services\FeedMedia;

use GuzzleHttp\Query;
use GuzzleHttp\Url;
use Mpx\MpxException;
use Mpx\ClientInterface as MpxClientInterface;
use Pimple\Container;

/**
 * Class FeedMediaMedia
 *
 * @package Mpx\Services
 *
 * @see http://help.theplatform.com/display/vms2/Requesting+Media+feeds
 */
class Client implements ClientInterface {

  /**
   * The MPX client.
   *
   * @var MpxClientInterface
   */
  protected $client;

  /**
   * Base feed URL.
   *
   * @var Url
   */
  protected $baseUrl;

  /**
   * The Account.pid of the account that owns the feed.
   *
   * @see http://help.theplatform.com/display/wsf2/Account.pid
   *
   * @var string
   */
  protected $accountPid;

  /**
   * The FeedConfig.pid of the feed.
   *
   * @see http://help.theplatform.com/display/fs3/FeedConfig.pid
   *
   * @var string
   */
  protected $feedPid;

  /**
   * Specifies a subfeed of the main feed. This corresponds to a
   * SubFeed.FeedType value of an item in the FeedConfig.subFeeds field.
   *
   * @see http://help.theplatform.com/display/fs3/SubFeed+object
   * @see http://help.theplatform.com/display/fs3/FeedConfig.subFeeds
   *
   * @var string
   */
  protected $feedType;

  /**
   * The feed path segment forces the response to be in feed format, regardless
   * of the number of items in the feed. A feed that returns multiple items is
   * always in feed format. However, a feed with only 1 item returns the item as
   * an object by default. If the feed path segment is included, a feed with
   * only 1 item returns a feed with only 1 item.
   *
   * @var bool
   */
  protected $feed;

  /**
   * A list of numeric IDs for individual items in the feed. This path segment
   * cannot be used with the guid/<owner ID>/<GUIDs> path segment.
   *
   * @var array
   */
  protected $ids = array();

  /**
   * Contains either an owner ID or a dash (—). GUIDs are only guaranteed to be
   * unique within an account. Because some feeds can be configured to include
   * inherited objects from other accounts, it is possible that 2 objects in a
   * feed could have the same GUID. You can include the object's ownerId to
   * uniquely identify the object. Alternatively, you can include a dash (—) to
   * specify that the owner ID is the owner ID of the FeedConfig.
   *
   * @see http://help.theplatform.com/display/fs3/FeedConfig+endpoint
   *
   * @var string
   */
  protected $ownerId;

  /**
   * Globally unique identifiers.
   *
   * @var array
   */
  protected $guids = array();

  /**
   * A list of SEO terms for the feed.
   *
   * @var array
   */
  protected $seoTerms = array();

  /**
   * Query params.
   *
   * @var Query
   */
  protected $queryParams;

  /**
   * Constructs a Client object.
   *
   * @param MpxClientInterface $client
   * @param Url $baseUrl
   * @param $accountPid
   * @param $feedPid
   * @param $feedType
   * @param $feed
   * @param $ids
   * @param $ownerId
   * @param $guids
   * @param $seoTerms
   */
  public function __construct(MpxClientInterface $client, Url $baseUrl, $accountPid, $feedPid, $feedType = NULL, $feed = FALSE, $ids = array(), $ownerId = NULL, $guids = array(), $seoTerms = array()) {
    $this->client = $client;
    $this->baseUrl = $baseUrl;
    $this->client->setBaseUrl($baseUrl);
    $this->accountPid = $accountPid;
    $this->feedPid = $feedPid;
    $this->feedType = $feedType;
    $this->feed = $feed;
    $this->ids = $ids;
    $this->ownerId = $ownerId;
    $this->guids = $guids;
    $this->seoTerms = $seoTerms;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(Container $container) {
    $feed_type = $container['feed_type'] ? $container['feed_type'] : NULL;
    $feed = $container['feed'] ? $container['feed'] : FALSE;
    $ids = $container['ids'] ? $container['ids'] : array();
    $owner_id = $container['owner_id'] ? $container['owner_id'] : NULL;
    $guids = $container['guids'] ? $container['guids'] : NULL;
    $seo_terms = $container['seo_terms'] ? $container['seo_terms'] : NULL;
    return new static($container['client'], $container['base_url'], $container['account_pid'], $container['feed_pid'], $feed_type, $feed, $ids, $owner_id, $guids, $seo_terms);
  }

  /**
   * {@inheritdoc}
   */
  public function get() {
    return $this->client->get($this->buildPath(), $this->queryParams);
  }

  /**
   * {@inheritdoc}
   */
  public function count() {
    $result = $this->client->count($this->buildPath(), $this->queryParams);
    // TODO: See what this actually returns.
    return $result['elements'];
  }

  /**
   * Builds the path to request based on the internal parameters.
   *
   * @throws MpxException
   *   For incompatible options.
   *
   * @return string
   *   The path.
   */
  protected function buildPath() {
    if (!empty($this->ids) && $this->guids) {
      // If there is information about the IDs and GUIDs, then throw an exception.
      throw new MpxException(sprintf('Cannot provide IDs and GUIDs for the %s client.', __CLASS__));
    }
    $path = $this->accountPid;
    $path .= $this->feedPid;
    $path .= $this->feedType ? $this->feedType : '';
    $path .= $this->feed ? 'feed' : '';
    $path .= $this->ids ? implode(',', $this->ids) : '';
    if ($this->guids) {
      $path .= 'guid/';
      $path .= $this->accountPid ? $this->accountPid : '-';
      $path .= implode(',', $this->guids);
    }
    $path .= $this->seoTerms ? implode(',', $this->seoTerms) : '';

    return $path;
  }

}