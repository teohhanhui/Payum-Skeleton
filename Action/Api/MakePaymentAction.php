<?php

namespace CoopTilleuls\Payum\BamboraNorthAmerica\Action\Api;

use CoopTilleuls\Payum\BamboraNorthAmerica\Api;
use CoopTilleuls\Payum\BamboraNorthAmerica\Request\Api\MakePayment;
use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Core\ApiAwareTrait;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\Http\HttpExceptionInterface;
use Payum\Core\Exception\LogicException;
use Payum\Core\Exception\RequestNotSupportedException;

class MakePaymentAction implements ActionInterface, ApiAwareInterface
{
    use ApiAwareTrait;

    public function __construct()
    {
        $this->apiClass = Api::class;
    }

    /**
     * {@inheritDoc}
     *
     * @param MakePayment $request
     */
    public function execute($request): void
    {
        RequestNotSupportedException::assertSupports($this, $request);

        $model = ArrayObject::ensureArrayObject($request->getModel());

        if (!isset($model['token'])) {
            throw new LogicException('The token has to be set.');
        }

        $fields = new ArrayObject($model);

        $fields->validateNotEmpty([
            'amount',
            'payment_method',
            'token',
        ]);
        if (empty($fields['token']['code'])) {
            throw new LogicException('The token.code field is required.');
        }
        if (empty($fields['token']['name'])) {
            throw new LogicException('The token.name field is required.');
        }

        try {
            $response = $this->api->makePayment((array) $fields);

            $model->replace($response);
        } catch (HttpExceptionInterface $e) {
            $model->replace(json_decode((string) $e->getResponse()->getBody(), true));
        }
    }

    /**
     * {@inheritDoc}
     */
    public function supports($request): bool
    {
        return
            $request instanceof MakePayment &&
            $request->getModel() instanceof \ArrayAccess
        ;
    }
}
