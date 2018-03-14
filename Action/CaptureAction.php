<?php

namespace CoopTilleuls\Payum\BamboraNorthAmerica\Action;

use CoopTilleuls\Payum\BamboraNorthAmerica\Request\Api\MakePayment;
use CoopTilleuls\Payum\BamboraNorthAmerica\Request\Api\ObtainToken;
use Payum\Core\Action\ActionInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayAwareTrait;
use Payum\Core\Request\Capture;

class CaptureAction implements ActionInterface, GatewayAwareInterface
{
    use GatewayAwareTrait;

    /**
     * {@inheritDoc}
     *
     * @param Capture $request
     */
    public function execute($request): void
    {
        RequestNotSupportedException::assertSupports($this, $request);

        $model = ArrayObject::ensureArrayObject($request->getModel());

        if (isset($model['approved']) || isset($model['code'])) {
            return;
        }

        if (!isset($model['token'])) {
            $obtainToken = new ObtainToken($request->getToken());
            $obtainToken->setModel($model);

            $this->gateway->execute($obtainToken);
        }

        $this->gateway->execute(new MakePayment($model));
    }

    /**
     * {@inheritDoc}
     */
    public function supports($request): bool
    {
        return
            $request instanceof Capture &&
            $request->getModel() instanceof \ArrayAccess
        ;
    }
}
