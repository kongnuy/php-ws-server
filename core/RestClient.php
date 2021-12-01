<?php

namespace MyApp;

use GuzzleHttp\Client;

class RestClient {

  private $client;

  public function __construct() {
    $this->client = new Client([
      'base_uri' => 'http://localhost:8008',
      'timeout' => 15,
    ]);
  }

  public function getUserBySession($token) {
    $url = 'http://localhost:8008/wsaction/getuserbysession?token=' . $token;
    return RestClient::sendRequest($url);
  }

  public function updateConnection($resourceId, $userID) {
    $url = 'http://localhost:8008/wsaction/updateconnection?userID=' . $userID . '&resourceId=' . $resourceId;
    return RestClient::sendRequest($url);
  }

  public function userData($userID) {
    $url = 'http://localhost:8008/wsaction/userdata?userID=' . $userID;
    return RestClient::sendRequest($url);
  }

  /**
   * Send cURL request
   */
  public static function sendRequest($url) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

    $result_response = curl_exec($ch);
    $results = array();

    if (curl_errno($ch) > 0) {
      curl_close($ch);
      return false;
    } else {
      $results = json_decode($result_response, true);
      curl_close($ch);
      if ($results && isset($results['data'])) {
        return $results['data'];
      } else {
        return false;
      }
    }
  }
}