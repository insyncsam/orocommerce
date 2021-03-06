<?php

namespace Oro\Bundle\RFPBundle\ComponentProcessor;

use Oro\Bundle\ProductBundle\ComponentProcessor\DataStorageAwareComponentProcessor;
use Oro\Bundle\ProductBundle\Storage\ProductDataStorage;
use Oro\Bundle\RFPBundle\Form\Extension\RequestDataStorageExtension;
use Oro\Bundle\SecurityBundle\Authentication\TokenAccessorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Translation\TranslatorInterface;

class DataStorageComponentProcessor extends DataStorageAwareComponentProcessor
{
    /** @var RequestDataStorageExtension */
    protected $requestDataStorageExtension;

    /**
     * Processor constructor.
     * @param UrlGeneratorInterface $router
     * @param ProductDataStorage $storage
     * @param AuthorizationCheckerInterface $authorizationChecker
     * @param TokenAccessorInterface $tokenAccessor
     * @param Session $session
     * @param TranslatorInterface $translator
     * @param RequestDataStorageExtension $requestDataStorageExtension
     */
    public function __construct(
        UrlGeneratorInterface $router,
        ProductDataStorage $storage,
        AuthorizationCheckerInterface $authorizationChecker,
        TokenAccessorInterface $tokenAccessor,
        Session $session,
        TranslatorInterface $translator,
        RequestDataStorageExtension $requestDataStorageExtension
    ) {
        $this->requestDataStorageExtension = $requestDataStorageExtension;

        parent::__construct($router, $storage, $authorizationChecker, $tokenAccessor, $session, $translator);
    }

    /**
     * {@inheritdoc}
     */
    public function process(array $data, Request $request)
    {
        $isAllowedRFP = $this->requestDataStorageExtension
            ->isAllowedRFP($data[ProductDataStorage::ENTITY_ITEMS_DATA_KEY])
        ;

        if (!$isAllowedRFP) {
            $this->session->getFlashBag()->add(
                'warning',
                $this->translator->trans('oro.frontend.rfp.data_storage.no_products_be_added_to_rfq')
            );

            return null;
        }

        return parent::process($data, $request);
    }
}
