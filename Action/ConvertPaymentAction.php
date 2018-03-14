<?php

namespace CoopTilleuls\Payum\BamboraNorthAmerica\Action;

use Payum\Core\Action\ActionInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayAwareTrait;
use Payum\Core\Model\PaymentInterface;
use Payum\Core\Request\Convert;
use Payum\Core\Request\GetCurrency;
use Payum\Core\Security\SensitiveValue;

class ConvertPaymentAction implements ActionInterface, GatewayAwareInterface
{
    use GatewayAwareTrait;

    /**
     * {@inheritDoc}
     *
     * @param Convert $request
     */
    public function execute($request): void
    {
        RequestNotSupportedException::assertSupports($this, $request);

        /** @var PaymentInterface $payment */
        $payment = $request->getSource();

        $this->gateway->execute($currency = new GetCurrency($payment->getCurrencyCode()));
        $divisor = pow(10, $currency->exp);

        $details = ArrayObject::ensureArrayObject($payment->getDetails());
        $details['order_number'] = $payment->getNumber();
        $details['amount'] = $payment->getTotalAmount() / $divisor;
        $details['comments'] = $payment->getDescription();

        if ($card = $payment->getCreditCard()) {
            if ($card->getToken()) {
                $details['payment_method'] = 'token';
                $details['token'] = [
                    'complete' => true,
                    'code' => $card->getToken(),
                    'name' => $card->getHolder(),
                ];
            } else {
                $details['payment_method'] = 'card';
                $details['card'] = SensitiveValue::ensureSensitive([
                    'number' => $card->getNumber(),
                    'name' => $card->getHolder(),
                    'expiry_month' => $card->getExpireAt()->format('m'),
                    'expiry_year' => $card->getExpireAt()->format('y'),
                    'cvd' => $card->getSecurityCode(),
                ]);
            }
        }

        $request->setResult((array) $details);
    }

    /**
     * {@inheritDoc}
     */
    public function supports($request): bool
    {
        return
            $request instanceof Convert &&
            $request->getSource() instanceof PaymentInterface &&
            'array' === $request->getTo()
        ;
    }
}
