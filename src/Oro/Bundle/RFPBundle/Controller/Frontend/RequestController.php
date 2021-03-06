<?php

namespace Oro\Bundle\RFPBundle\Controller\Frontend;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

use Oro\Bundle\LayoutBundle\Annotation\Layout;
use Oro\Bundle\SecurityBundle\Annotation\Acl;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Oro\Bundle\RFPBundle\Entity\Request as RFPRequest;
use Oro\Bundle\RFPBundle\Form\Handler\RequestUpdateHandler;
use Oro\Bundle\WebsiteBundle\Manager\WebsiteManager;

class RequestController extends Controller
{
    /**
     * @Route("/view/{id}", name="oro_rfp_frontend_request_view", requirements={"id"="\d+"})
     * @Layout
     * @Acl(
     *      id="oro_rfp_frontend_request_view",
     *      type="entity",
     *      class="OroRFPBundle:Request",
     *      permission="VIEW",
     *      group_name="commerce"
     * )
     *
     * @param RFPRequest $request
     * @return array
     */
    public function viewAction(RFPRequest $request)
    {
        $this->assertValidInternalStatus($request);

        return [
            'data' => [
                'entity' => $request
            ]
        ];
    }

    /**
     * @AclAncestor("oro_rfp_frontend_request_view")
     * @Route("/", name="oro_rfp_frontend_request_index")
     * @Layout(vars={"entity_class"})
     * @return array
     */
    public function indexAction()
    {
        $entityClass = $this->container->getParameter('oro_rfp.entity.request.class');
        $viewPermission = 'VIEW;entity:' . $entityClass;
        if (!$this->isGranted($viewPermission)) {
            return $this->redirect(
                $this->generateUrl('oro_rfp_frontend_request_create')
            );
        }

        return [
            'entity_class' => $entityClass,
        ];
    }

    /**
     * @Acl(
     *      id="oro_rfp_frontend_request_create",
     *      type="entity",
     *      class="OroRFPBundle:Request",
     *      permission="CREATE",
     *      group_name="commerce"
     * )
     * @Route("/create", name="oro_rfp_frontend_request_create")
     * @Layout
     *
     * @param Request $request
     * @return array
     */
    public function createAction(Request $request)
    {
        $rfpRequest = $this->get('oro_rfp.request.manager')->create();
        $this->addProductItemsToRfpRequest($rfpRequest, $request);

        $response = $this->update($rfpRequest);

        if ($response instanceof Response) {
            return $response;
        }

        return ['data' => $response];
    }

    /**
     * @Route("/update/{id}", name="oro_rfp_frontend_request_update", requirements={"id"="\d+"})
     * @Layout
     * @Acl(
     *      id="oro_rfp_frontend_request_update",
     *      type="entity",
     *      class="OroRFPBundle:Request",
     *      permission="EDIT",
     *      group_name="commerce"
     * )
     *
     * @param RFPRequest $rfpRequest
     * @return array|RedirectResponse
     */
    public function updateAction(RFPRequest $rfpRequest)
    {
        $this->assertValidInternalStatus($rfpRequest);

        $response = $this->update($rfpRequest);

        if ($response instanceof Response) {
            return $response;
        }

        return ['data' => $response];
    }

    /**
     * @param RFPRequest $rfpRequest
     * @return array|RedirectResponse
     */
    protected function update(RFPRequest $rfpRequest)
    {
        /* @var $handler RequestUpdateHandler */
        $handler = $this->get('oro_rfp.service.request_update_handler');

        return $handler->handleUpdate(
            $rfpRequest,
            $this->get('oro_rfp.layout.data_provider.request_form')->getRequestForm($rfpRequest),
            function (RFPRequest $rfpRequest) {
                if ($this->isGranted('VIEW', $rfpRequest)) {
                    $route = $this->isGranted('EDIT', $rfpRequest)
                        ? 'oro_rfp_frontend_request_update'
                        : 'oro_rfp_frontend_request_view';

                    return [
                        'route' => $route,
                        'parameters' => ['id' => $rfpRequest->getId()],
                    ];
                }

                return [
                    'route' => 'oro_rfp_frontend_request_create',
                    'parameters' => [],
                ];
            },
            function (RFPRequest $rfpRequest) {
                if ($this->isGranted('VIEW', $rfpRequest)) {
                    return [
                        'route' => 'oro_rfp_frontend_request_view',
                        'parameters' => ['id' => $rfpRequest->getId()],
                    ];
                }

                return [
                    'route' => 'oro_rfp_frontend_request_create',
                    'parameters' => [],
                ];
            },
            $this->get('translator')->trans('oro.rfp.controller.request.saved.message'),
            null,
            function (RFPRequest $rfpRequest, FormInterface $form, Request $request) {
                $url = $request->getUri();
                if ($request->headers->get('referer')) {
                    $url = $request->headers->get('referer');
                }

                return [
                    'backToUrl' => $url,
                    'form' => $form->createView()
                ];
            }
        );
    }

    /**
     * Creates HTMLPurifier
     *
     * @return \HTMLPurifier
     */
    protected function getPurifier()
    {
        $purifierConfig = \HTMLPurifier_Config::createDefault();
        $purifierConfig->set('HTML.Allowed', '');

        return new \HTMLPurifier($purifierConfig);
    }

    /**
     * @return WebsiteManager
     */
    protected function getWebsiteManager()
    {
        return $this->get('oro_website.manager');
    }

    /**
     * @param RFPRequest $rfpRequest
     * @param Request $request
     */
    protected function addProductItemsToRfpRequest(RFPRequest $rfpRequest, Request $request)
    {
        $productLineItems = (array)$request->query->get('product_items', []);
        $filteredProducts = [];
        foreach ($productLineItems as $productId => $items) {
            $productId = (int)$productId;
            if ($productId <= 0) {
                continue;
            }
            $filteredItems = [];
            foreach ($items as $item) {
                if (!is_array($item) || array_diff(['unit', 'quantity'], array_keys($item))) {
                    continue;
                }
                $filteredItems[] = $item;
            }
            if (count($filteredItems) > 0) {
                $filteredProducts[$productId] = $filteredItems;
            }
        }
        if (count($productLineItems) === 0) {
            return;
        }
        $this->get('oro_rfp.request.manager')
            ->addProductLineItemsToRequest($rfpRequest, $filteredProducts);
    }

    /**
     * @param RFPRequest $request
     * @throws NotFoundHttpException
     */
    private function assertValidInternalStatus(RFPRequest $request)
    {
        $status = $request->getInternalStatus();
        if ($status && $status->getId() === RFPRequest::INTERNAL_STATUS_DELETED) {
            throw $this->createNotFoundException();
        }
    }
}
