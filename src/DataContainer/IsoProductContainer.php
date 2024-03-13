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
use HeimrichHannot\IsotopeResourceBookingBundle\Controller\ResourceBookingPlanController;
use Isotope\Model\Product;
use Symfony\Component\HttpFoundation\RequestStack;

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
}
