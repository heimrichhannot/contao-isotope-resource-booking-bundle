<?php

/*
 * Copyright (c) 2023 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\IsotopeResourceBookingBundle\DataContainer;

use Contao\Backend;
use Contao\CoreBundle\DataContainer\PaletteManipulator;
use Contao\CoreBundle\ServiceAnnotation\Callback;
use Contao\DataContainer;
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
     * @Callback(table="tl_iso_product", target="config.onload", priority=-1)
     */
    public function onLoadCallback(?DataContainer $dc = null): void
    {
        if (!$dc || !$dc->id || !($product = Product::findById($dc->id))) {
            return;
        }

        if (!$this->bookingAttribute->isBlockingTimeActive($product)) {
            return;
        }

        $pm = PaletteManipulator::create()
            ->addLegend('booking_legend', 'general_legend')
            ->addField('bookingBlock', 'booking_legend', PaletteManipulator::POSITION_APPEND);

        foreach (array_keys($GLOBALS['TL_DCA']['tl_iso_product']['palettes'] ?? []) as $name) {
            if (in_array($name, ['__selector__'])) {
                continue;
            }
            $pm->applyToPalette((string)$name, 'tl_iso_product');
        }
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
