<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

use HeimrichHannot\IsotopeResourceBookingBundle\EventListener\Isotope\CalculatePriceListener;
use HeimrichHannot\IsotopeResourceBookingBundle\EventListener\Isotope\PostAddProductToCollectionListener;
use HeimrichHannot\IsotopeResourceBookingBundle\EventListener\Isotope\PreCheckoutListener;

/*
 * Isotope Hooks
 */
$GLOBALS['ISO_HOOKS']['postAddProductToCollection']['huh_isotope_resource_booking_bundle'] = [PostAddProductToCollectionListener::class, '__invoke'];
$GLOBALS['ISO_HOOKS']['preCheckout']['huh_isotope_resource_booking_bundle'] = [PreCheckoutListener::class, '__invoke'];
$GLOBALS['ISO_HOOKS']['calculatePrice']['huh_isotope_resource_booking_bundle'] = [CalculatePriceListener::class, '__invoke'];
