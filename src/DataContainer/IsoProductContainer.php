<?php

/*
 * Copyright (c) 2023 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\IsotopeResourceBookingBundle\DataContainer;

use Contao\Backend;
use Contao\CoreBundle\ServiceAnnotation\Callback;
use Contao\DataContainer;
use Contao\Image;
use Contao\Input;
use Contao\StringUtil;
use HeimrichHannot\IsotopeResourceBookingBundle\Attribute\BookingAttribute;
use HeimrichHannot\IsotopeResourceBookingBundle\Controller\ResourceBookingPlanController;
use Isotope\Model\Product;
use Symfony\Component\HttpFoundation\RequestStack;
use Twig\Environment;

class IsoProductContainer
{
    private RequestStack       $requestStack;
    private ResourceBookingPlanController $resourceBookingPlanController;

    public function __construct(RequestStack $requestStack, ResourceBookingPlanController $resourceBookingPlanController)
    {
        $this->requestStack = $requestStack;
        $this->resourceBookingPlanController = $resourceBookingPlanController;
    }

    /**
     * @Callback(table="tl_iso_product", target="list.operations.bookingPlan.button")
     */
    public function onBookingPlanButtonCallback(
        array $row,
        ?string $href,
        string $label,
        string $title,
        ?string $icon,
        string $attributes,
        string $table,
        ?array $rootRecordIds,
        ?array $childRecordIds,
        bool $circularReference,
        ?string $previous,
        ?string $next,
        DataContainer $dc
    ): string
    {
        $product = Product::findById($row['id']);
        if (!$product) {
            return '';
        }

        if (!in_array('bookingOverview', $product->getType()->getAttributes())) {
            return '';
        }

        $href = Backend::addToUrl($href . '&amp;id=' . $row['id'] . (Input::get('nb') ? '&amp;nc=1' : ''));
        return '<a href="' . $href . '" title="' . StringUtil::specialchars($title) . '"' . $attributes . '>'
            . Image::getHtml($icon, $label, 'width="16" height="16"')
            . '</a> ';
    }

    /**
     * Generates an overview of bookings for product.
     *
     * @return string An Html-Code
     */
    public function onBookingOverviewTextCallback(array $attributes)
    {
        $product = Product::findById($attributes['dataContainer']->id);
        $request = $this->requestStack->getCurrentRequest();

        $year = is_numeric($request->get('year')) ? (int) $request->get('year') : date('Y');
        $month = is_numeric($request->get('month')) ? (int) $request->get('month') : date('n');

        if ($request->request->has('huh_isotope_resource_booking_'.$product->id)) {
            $value = $request->request->get('huh_isotope_resource_booking_'.$product->id);
            [$month, $year] = explode('_', $value);
        }

        return $this->resourceBookingPlanController->renderBookingOverview($product, $month, $year);
    }
}
