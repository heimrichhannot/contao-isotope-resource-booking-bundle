<?php

/*
 * Copyright (c) 2023 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\IsotopeResourceBookingBundle\EventListener\Contao;

use Contao\CoreBundle\Routing\ScopeMatcher;
use Contao\CoreBundle\ServiceAnnotation\Hook;
use HeimrichHannot\IsotopeResourceBookingBundle\Action\BookingPlanAction;
use Isotope\Frontend\ProductAction\Registry;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * @Hook("initializeSystem")
 */
class InitializeSystemListener
{
    protected BookingPlanAction $bookingPlanAction;

    public function __construct(BookingPlanAction $bookingPlanAction)
    {
        $this->bookingPlanAction = $bookingPlanAction;
    }

    public function __invoke(): void
    {
        Registry::add($this->bookingPlanAction);
    }
}
