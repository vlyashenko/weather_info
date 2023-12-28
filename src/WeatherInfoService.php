<?php

declare(strict_types = 1);

namespace Drupal\weather_info;

use Drupal\Component\Serialization\Json;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Http\Client\ClientInterface;

/**
 * Service to fetch data from openweathermap.
 */
final class WeatherInfoService {
  use StringTranslationTrait;

  /**
   * Base url of openweathermap API.
   */
  public const API_URL = 'https://api.openweathermap.org/data/2.5/weather';

  /**
   * Constructs a WeatherInfoService object.
   */
  public function __construct(
        private readonly ConfigFactoryInterface $config,
        private readonly ClientInterface $httpClient,
        private readonly CacheBackendInterface $cacheData,
    ) {
  }

  /**
   * Fetch current weather data from selected city the API .
   */
  public function getWeatherInfo(string $city) :array {
    $cid = 'weather_info_' . md5($city);
    $data = [];
    if ($cache = $this->cacheData->get($cid)) {
      $data = $cache->data;
    }
    else {
      try {
        $response = $this->httpClient->request(
              'GET',
              self::API_URL,
              [
                'query' =>
              [
                'q' => $city,
                'appid' => $this->config->get('weather_info.settings')->get('api_key'),
                'units' => 'metric',
              ],
              ]
          );
        $data = Json::decode($response->getBody()->getContents());
        $this->cacheData->set($cid, $data, 3600);
      }
      catch (GuzzleException $e) {
        if ($e->hasResponse()) {
          $response = Json::decode($e->getResponse()->getBody()->getContents());
        }
        $data['error'] = $response['message'] ?: $this->t('Something went wrong try again later.');
      }
    }
    return $data;
  }

}
