<?php

/**
 * @file
 * Contains \Mpx\Test\ClientTest.
 */

namespace Mpx\Test;

use GuzzleHttp\Client;
use Mpx\Client as MpxClient;
use Mpx\ClientInterface as MpxClientInterface;

class ClientTest extends \PHPUnit_Framework_TestCase {

  /**
   * @var MpxClientInterface
   */
  protected $client;

  /**
   * Set up the testing environment.
   */
  public function setUp() {
    parent::setUp();
    $base_url = 'http://example.com';
    $guzzle_client = new Client(array(
        'base_url' => $base_url
      )
    );

    $this->client = new MpxClient($guzzle_client);
  }

  /**
   * Test default format.
   */
  public function testDefaultFormat() {
    $defaults = $this->client->getDefaults();
    $this->assertArrayHasKey('form', $defaults);
    $this->assertEquals($defaults['form'], MpxClient::FORMAT_RSS);
  }

}
