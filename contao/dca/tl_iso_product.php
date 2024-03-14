<?php

/*
 * Copyright (c) 2023 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

use HeimrichHannot\IsotopeResourceBookingBundle\DataContainer\ProductContainer;
use HeimrichHannot\IsotopeResourceBookingBundle\Model\ProductBookingModel;

$dca = &$GLOBALS['TL_DCA']['tl_iso_product'];

$dca['config']['ctable'][] = ProductBookingModel::getTable();

$dca['list']['operations']['bookingPlan'] = [
    'href' => 'table=tl_iso_product_booking',
    'icon' => 'web/bundles/heimrichhannotisotoperesourcebooking/backend/img/calendar.svg',
];

$fields = [
    'bookingBlock' => [
        'inputType' => 'text',
        'eval' => [
            'tl_class' => 'w50 clr',
            'rgxp' => 'natural',
        ],
        'attributes' => ['legend' => 'inventory_legend'],
        'sql' => "int(10) unsigned NOT NULL default '0'",
    ],
//    'bookingOverview' => [
//        'inputType' => 'huh_be_explanation',
//        'eval' => [
//            'text_callback' => [IsoProductContainer::class, 'onBookingOverviewTextCallback'],
//            'tl_class' => 'clr',
//        ],
//        'attributes' => ['legend' => 'inventory_legend'],
//    ],
];

$dca['fields'] = array_merge($dca['fields'], $fields);
