<?php

/*
 * Copyright (c) 2023 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\IsotopeResourceBookingBundle\DataContainer;

use Contao\Backend;
use Contao\CoreBundle\ServiceAnnotation\Callback;
use Contao\Image;
use Contao\Input;
use Contao\StringUtil;
use HeimrichHannot\IsotopeResourceBookingBundle\Attribute\BookingAttribute;
use Isotope\Model\Product;

class ProductContainer
{
    private BookingAttribute $bookingAttribute;

    public function __construct(BookingAttribute $bookingAttribute)
    {
        $this->bookingAttribute = $bookingAttribute;
    }

    /**
     * @Callback(table="tl_iso_product", target="list.operations.bookingPlan.button")
     */
    public function onBookingPlanButtonCallback(array $row, ?string $href, string $label, string $title, ?string $icon, string $attributes): string
    {
        $product = Product::findById($row['id']);
        if (!$product) {
            return '';
        }

        if (!$this->bookingAttribute->isActive($product)) {
            return '';
        }

        $href = Backend::addToUrl($href . '&amp;id=' . $row['id'] . (Input::get('nb') ? '&amp;nc=1' : ''));
        return '<a href="' . $href . '" title="' . StringUtil::specialchars($title) . '"' . $attributes . '>'
            . Image::getHtml($icon, $label, 'width="16" height="16"')
            . '</a> ';
    }
}
