<?php
/**
 * @file
 * Contains DatatransPaymentMethodController.
 */

/**
 * Class DatatransPaymentMethodController
 *
 * The Datatrans payment method controller.
 */
class DatatransPaymentMethodController extends PaymentMethodController {

  /**
   * {@inheritdoc}
   */
  public function execute(Payment $payment) {
    entity_save('payment', $payment);
    $_SESSION['ogone_pid'] = $payment->pid;
    drupal_goto('payment_datatrans/redirect/' . $payment->pid);
  }

}
