<?php

/**
 * @file
 * Contains \Drupal\payment\Plugin\Payment\Method\BasicDeriver.
 */

namespace Drupal\payment_datatrans\Plugin\Payment\Method;

use Drupal\Component\Plugin\Derivative\DeriverBase;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Plugin\Discovery\ContainerDeriverInterface;
use Drupal\payment\Plugin\Payment\MethodConfiguration\PaymentMethodConfigurationManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Derives payment method plugin definitions based on configuration entities.
 *
 * @see \Drupal\payment\Plugin\Payment\Method\Basic
 */
class DatatransDeriver extends DeriverBase implements ContainerDeriverInterface {

  /**
   * The payment method configuration manager.
   *
   * @var \Drupal\payment\Plugin\Payment\MethodConfiguration\PaymentMethodConfigurationManagerInterface
   */
  protected $paymentMethodConfigurationManager;

  /**
   * The payment method configuration storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $paymentMethodConfigurationStorage;

  /**
   * Constructs a new class instance.
   */
  public function __construct(EntityStorageInterface $payment_method_configuration_storage, PaymentMethodConfigurationManagerInterface $payment_method_configuration_manager) {
    $this->paymentMethodConfigurationStorage = $payment_method_configuration_storage;
    $this->paymentMethodConfigurationManager = $payment_method_configuration_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, $base_plugin_id) {
    /** @var \Drupal\Core\Entity\EntityManagerInterface $entity_manager */
    $entity_manager = $container->get('entity.manager');

    return new static($entity_manager->getStorage('payment_method_configuration'), $container->get('plugin.manager.payment.method_configuration'));
  }

  /**
   * {@inheritdoc}
   */
  public function getDerivativeDefinitions($base_plugin_definition) {
    /** @var \Drupal\payment\Entity\PaymentMethodConfigurationInterface[] $payment_methods */
    $payment_methods = $this->paymentMethodConfigurationStorage->loadMultiple();
    foreach ($payment_methods as $payment_method) {
      if ($payment_method->getPluginId() == 'payment_datatrans') {
        /** @var \Drupal\payment_datatrans\Plugin\Payment\MethodConfiguration\DatatransConfiguration $configuration_plugin */
        $configuration_plugin = $this->paymentMethodConfigurationManager->createInstance($payment_method->getPluginId(), $payment_method->getPluginConfiguration());
        $this->derivatives[$payment_method->id()] = array(
            //'active' => $payment_method->status(),
            'id' => 'payment_datatrans:' . $payment_method->id(),
            'merchant_id' => $configuration_plugin->getMerchantId(),
            'message_text' => '',
            'message_text_format' => '',
            'up_start_url' => $configuration_plugin->getUpStartUrl(),
            'req_type' => $configuration_plugin->getReqType(),
            'security' => array(
              'security_level' => $configuration_plugin->getSecurityLevel(),
              'merchant_control_constant' => $configuration_plugin->getMerchantControlConstant(),
              'hmac_key' => $configuration_plugin->getHmacKey(),
              'hmac_key_2' => $configuration_plugin->getHmacKeyTwo(),
              'use_hmac_2' => $configuration_plugin->getUseHmacTwo(),
            ),
          ) + $base_plugin_definition;
      }
    }

    return $this->derivatives;
  }

}
