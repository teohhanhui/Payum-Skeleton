<?php

namespace CoopTilleuls\Payum\BamboraNorthAmerica\Action;

use Payum\Core\Action\ActionInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Request\GetStatusInterface;

class StatusAction implements ActionInterface
{
    /**
     * {@inheritDoc}
     *
     * @param GetStatusInterface $request
     */
    public function execute($request): void
    {
        RequestNotSupportedException::assertSupports($this, $request);

        $model = ArrayObject::ensureArrayObject($request->getModel());

        if (!isset($model['approved']) && isset($model['code'])) {
            $request->markFailed();

            return;
        }

        if (!isset($model['approved']) && !isset($model['code']) && !isset($model['token'])) {
            $request->markNew();

            return;
        }

        if (!isset($model['approved']) && !isset($model['code']) && isset($model['token'])) {
            $request->markPending();

            return;
        }

        if (1 !== $model['approved'] && '1' !== $model['approved']) {
            $request->markFailed();

            return;
        }

        if (false !== ($model['token']['complete'] ?? false)) {
            $request->markCaptured();

            return;
        }

        if (false === ($model['token']['complete'] ?? false)) {
            $request->markAuthorized();

            return;
        }

        $request->markUnknown();
    }

    /**
     * {@inheritDoc}
     */
    public function supports($request): bool
    {
        return
            $request instanceof GetStatusInterface &&
            $request->getModel() instanceof \ArrayAccess
        ;
    }
}
