<?php

declare(strict_types = 1);

namespace Drupal\weather_info\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\weather_info\WeatherInfoService;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a weather info block.
 *
 * @Block(
 *   id = "weather_info_block",
 *   admin_label = @Translation("Weather info block"),
 *   category = @Translation("Custom"),
 * )
 */
final class WeatherInfoBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * Constructs the plugin instance.
   */
  public function __construct(
        array $configuration,
        $plugin_id,
        $plugin_definition,
        private readonly WeatherInfoService $weatherInfoService,
    ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition): self {
    return new self(
          $configuration,
          $plugin_id,
          $plugin_definition,
          $container->get('weather_info.service'),
      );
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state): array {
    $form['city'] = [
      '#type' => 'textfield',
      '#title' => $this->t('City'),
      '#required' => TRUE,
      '#default_value' => $this->configuration['city'] ?? '',
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state): void {
    $this->configuration['city'] = $form_state->getValue('city');
  }

  /**
   * {@inheritdoc}
   */
  public function build(): array {
    $data = $this->weatherInfoService->getWeatherInfo($this->configuration['city']);
    $build['content'] = [
      '#theme' => 'weather_info',
      '#data' => $data,
    ];
    return $build;
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheMaxAge(): int {
    return 0;
  }

}
