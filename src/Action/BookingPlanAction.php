<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\IsotopeResourceBookingBundle\Action;

use Contao\Controller;
use Contao\Input;
use Contao\System;
use HeimrichHannot\IsotopeResourceBookingBundle\Attribute\BookingAttribute;
use Isotope\Frontend\ProductAction\CartAction;
use Isotope\Interfaces\IsotopeProduct;
use Isotope\Isotope;
use Isotope\Message;

class BookingPlanAction extends CartAction
{
    public function getName()
    {
        return 'edit_booking_plan';
    }

    public function getLabel(IsotopeProduct $product = null)
    {
        return $GLOBALS['TL_LANG']['MSC']['buttonLabel']['edit_booking_plan'];
    }

    public function getBlockedDates($product)
    {
        return System::getContainer()->get(BookingAttribute::class)->getBlockedDates($product);
    }

    public function generate(IsotopeProduct $product, array $config = [])
    {
        if (System::getContainer()->has('huh.encore.asset.frontend')) {
            System::getContainer()->get('huh.encore.asset.frontend')->addActiveEntrypoint('contao-isotope-resource-booking-bundle');
        }

        $url = System::getContainer()->get('contao.routing.url_generator')->generate('huh_isotope_resource_booking_blocked_dates');

        return sprintf(
                    '<div class="bookingPlan_container" data-update="%s" data-product-id="%s">
                        <label for="%s">%s</label>
                    <input type="text" name="%s" id="bookingPlan" class="submit %s %s"  data-blocked="%s" required></div>',
            $url,
            $product->id,
            $this->getName(),
            $this->getLabel(),
            $this->getName(),
            $this->getName(),
            $this->getClasses($product),
            json_encode($this->getBlockedDates($product))
        ).'<input type="submit" name="submit" class="submit btn btn-primary" value="zum Warenkorb hinzufügen">';
    }

    /**
     * {@inheritdoc}
     */
    public function handleSubmit(IsotopeProduct $product, array $config = [])
    {
        if (empty($_POST[$this->getName()])) {
            Message::addError(System::getContainer()->get('translator')->trans('huh.isotope.collection.booking.error.emptySelection'));

            return false;
        }

        // do not update cart item that is already in cart but add a new one with the set booking dates
        $success = $this->handleAddToCart($product, $config);

        if ($success) {
            if (!$config['module']->iso_addProductJumpTo) {
                Controller::reload();
            }

            if (null === ($jumpToPage = System::getContainer()->get('huh.utils.url')->getJumpToPageObject($config['module']->iso_addProductJumpTo))) {
                Controller::reload();
            }

            System::getContainer()->get('huh.utils.url')->redirect($jumpToPage->alias);
        } else {
            return false;
        }
    }

    /**
     * @return bool
     */
    private function handleAddToCart(IsotopeProduct $product, array $config = [])
    {
        $module = $config['module'];
        $quantity = 1;

        if ($module->iso_use_quantity && Input::post('quantity_requested') > 0) {
            $quantity = (int) Input::post('quantity_requested');
        }

        // Do not add parent of variant product to the cart
        if (($product->hasVariants() && !$product->isVariant())
            || !$item = Isotope::getCart()->addProduct($product, $quantity, $config)
        ) {
            return false;
        }

        if ($item->hasErrors()) {
            Message::addError($item->getErrors()[0]);

            return false;
        }

        Message::addConfirmation($GLOBALS['TL_LANG']['MSC']['addedToCart']);

        return true;
    }
}
