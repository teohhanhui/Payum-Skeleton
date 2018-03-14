<?php

namespace CoopTilleuls\Payum\BamboraNorthAmerica;

use CoopTilleuls\Payum\BamboraNorthAmerica\Action\Api\MakePaymentAction;
use CoopTilleuls\Payum\BamboraNorthAmerica\Action\Api\ObtainTokenAction;
use CoopTilleuls\Payum\BamboraNorthAmerica\Action\AuthorizeAction;
use CoopTilleuls\Payum\BamboraNorthAmerica\Action\CancelAction;
use CoopTilleuls\Payum\BamboraNorthAmerica\Action\CaptureAction;
use CoopTilleuls\Payum\BamboraNorthAmerica\Action\ConvertPaymentAction;
use CoopTilleuls\Payum\BamboraNorthAmerica\Action\NotifyAction;
use CoopTilleuls\Payum\BamboraNorthAmerica\Action\RefundAction;
use CoopTilleuls\Payum\BamboraNorthAmerica\Action\StatusAction;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\GatewayFactory;

class BamboraCustomCheckoutGatewayFactory extends GatewayFactory
{
    /**
     * {@inheritDoc}
     */
    protected function populateConfig(ArrayObject $config): void
    {
        $config->defaults([
            'payum.factory_name' => 'bambora_na_custom_checkout',
            'payum.factory_title' => 'Bambora (North America) Custom Checkout',

            'payum.action.status' => new StatusAction(),
            'payum.action.convert_payment' => new ConvertPaymentAction(),
            'payum.action.capture' => new CaptureAction(),

            'payum.action.obtain_token' => function (ArrayObject $config) {
                return new ObtainTokenAction($config['payum.template.obtain_token']);
            },
            'payum.action.make_payment' => new MakePaymentAction(),

            'payum.template.obtain_token' => '@PayumBambora/Action/obtain_token.html.twig',

            // 'payum.action.authorize' => new AuthorizeAction(),
            // 'payum.action.refund' => new RefundAction(),
            // 'payum.action.cancel' => new CancelAction(),
            // 'payum.action.notify' => new NotifyAction(),
        ]);

        if (empty($config['payum.api'])) {
            $config['payum.default_options'] = [
                'merchant_id',
                'api_access_passcode',
            ];
            $config->defaults($config['payum.default_options']);
            $config['payum.required_options'] = [
                'merchant_id',
                'api_access_passcode',
            ];

            $config['payum.api'] = function (ArrayObject $config) {
                $config->validateNotEmpty($config['payum.required_options']);

                return new Api((array) $config, $config['payum.http_client'], $config['httplug.message_factory']);
            };
        }
    }
}
