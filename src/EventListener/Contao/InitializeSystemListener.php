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
    private RequestStack        $requestStack;
    private ScopeMatcher        $scopeMatcher;

    public function __construct(BookingPlanAction $bookingPlanAction, RequestStack $requestStack, ScopeMatcher $scopeMatcher)
    {
        $this->bookingPlanAction = $bookingPlanAction;
        $this->requestStack = $requestStack;
        $this->scopeMatcher = $scopeMatcher;
    }

    public function __invoke(): void
    {
        Registry::add($this->bookingPlanAction);

        if (($request = $this->requestStack->getCurrentRequest()) && $this->scopeMatcher->isBackendRequest($request)) {
            $GLOBALS['TL_CSS']['huh_isotope_resource_booking'] = 'bundles/heimrichhannotisotoperesourcebooking/backend/css/huh_isotope_resource_booking_backend.css|static';
            $GLOBALS['TL_JAVASCRIPT']['huh_isotope_resource_booking'] = 'bundles/heimrichhannotisotoperesourcebooking/backend/js/huh_isotope_resource_booking_backend.js|static';
        }
    }
}
