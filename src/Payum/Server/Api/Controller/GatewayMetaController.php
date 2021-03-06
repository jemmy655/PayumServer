<?php
namespace Payum\Server\Api\Controller;

use Payum\Core\Bridge\Symfony\Form\Type\GatewayConfigType;
use Payum\Core\Registry\GatewayFactoryRegistryInterface;
use Payum\Server\Api\View\FormToJsonConverter;
use Payum\Server\Controller\ForwardExtensionTrait;
use Payum\Server\Model\GatewayConfig;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class GatewayMetaController
{
    use ForwardExtensionTrait;

    /**
     * @var FormFactoryInterface
     */
    private $formFactory;

    /**
     * @var FormToJsonConverter
     */
    private $formToJsonConverter;

    /**
     * @var GatewayFactoryRegistryInterface
     */
    private $registry;

    /**
     * @param FormFactoryInterface $formFactory
     * @param FormToJsonConverter $formToJsonConverter
     * @param GatewayFactoryRegistryInterface $registry
     */
    public function __construct(
        FormFactoryInterface $formFactory,
        FormToJsonConverter $formToJsonConverter,
        GatewayFactoryRegistryInterface $registry
    ) {
        $this->formFactory = $formFactory;
        $this->formToJsonConverter = $formToJsonConverter;
        $this->registry = $registry;
    }

    /**
     * @return JsonResponse
     */
    public function getAllAction()
    {
        $normalizedFactories = [];

        foreach ($this->registry->getGatewayFactories() as $name => $factory) {
            $gatewayConfig = new GatewayConfig();
            $gatewayConfig->setFactoryName($name);

            $form = $this->formFactory->create(GatewayConfigType::class, $gatewayConfig, [
                'csrf_protection' => false,
                'data_class' => GatewayConfig::class,
            ]);
            $normalizedFactories[$name]['config'] = $this->formToJsonConverter->convertMeta($form->get('config'));
        }

        $form = $this->formFactory->create(GatewayConfigType::class, null, [
            'csrf_protection' => false,
            'data_class' => GatewayConfig::class,
        ]);

        return new JsonResponse(array(
            'generic' => $this->formToJsonConverter->convertMeta($form),
            'meta' => $normalizedFactories,
        ));
    }
}
