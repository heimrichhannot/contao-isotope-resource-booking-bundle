<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

use HeimrichHannot\IsotopeBundle\Action\BookingPlanAction;
use Isotope\Frontend\ProductAction\Registry;

/*
 * Product actions
 */
Registry::add(new BookingPlanAction());
