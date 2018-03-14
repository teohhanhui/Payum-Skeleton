<?php

namespace CoopTilleuls\Payum\BamboraNorthAmerica\Action\Api;

use CoopTilleuls\Payum\BamboraNorthAmerica\Request\Api\ObtainToken;
use Payum\Core\Action\ActionInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\LogicException;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayAwareTrait;
use Payum\Core\Reply\HttpResponse;
use Payum\Core\Request\GetHttpRequest;
use Payum\Core\Request\RenderTemplate;

class ObtainTokenAction implements ActionInterface, GatewayAwareInterface
{
    use GatewayAwareTrait;

    protected $templateName;

    public function __construct(string $templateName)
    {
        $this->templateName = $templateName;
    }

    /**
     * {@inheritDoc}
     *
     * @param ObtainToken $request
     */
    public function execute($request): void
    {
        RequestNotSupportedException::assertSupports($this, $request);

        $model = ArrayObject::ensureArrayObject($request->getModel());

        if (isset($model['token'])) {
            throw new LogicException('The token has already been set.');
        }

        $getHttpRequest = new GetHttpRequest();
        $this->gateway->execute($getHttpRequest);
        if ('POST' === $getHttpRequest->method && isset($getHttpRequest->request['bamboraToken'])) {
            $model['payment_method'] = 'token';
            $model['token'] = [
                'complete' => true,
                'code' => $getHttpRequest->request['bamboraToken'],
                'name' => $getHttpRequest->request['cardholderName'],
            ];

            return;
        }

        $this->gateway->execute($renderTemplate = new RenderTemplate($this->templateName, [
            'model' => $model,
            'actionUrl' => $request->getToken() ? $request->getToken()->getTargetUrl() : null,
        ]));

        throw new HttpResponse($renderTemplate->getResult());
    }

    /**
     * {@inheritDoc}
     */
    public function supports($request): bool
    {
        return
            $request instanceof ObtainToken &&
            $request->getModel() instanceof \ArrayAccess
        ;
    }
}
