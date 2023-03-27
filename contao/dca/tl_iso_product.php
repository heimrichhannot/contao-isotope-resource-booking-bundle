<?php

/*
 * Copyright (c) 2023 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

use HeimrichHannot\IsotopeResourceBookingBundle\DataContainer\IsoProductContainer;

$dca = &$GLOBALS['TL_DCA']['tl_iso_product'];

$fields = [
    'bookingStart' => [
        'label' => &$GLOBALS['TL_LANG']['tl_iso_product']['bookingStart'],
        'inputType' => 'text',
        'eval' => ['tl_class' => 'w50', 'rgxp' => 'date', 'datepicker' => true],
        'attributes' => ['legend' => 'inventory_legend'],
        'sql' => "varchar(16) NOT NULL default ''",
    ],
    'bookingStop' => [
        'label' => &$GLOBALS['TL_LANG']['tl_iso_product']['bookingStop'],
        'inputType' => 'text',
        'eval' => ['tl_class' => 'w50', 'rgxp' => 'date', 'datepicker' => true],
        'attributes' => ['legend' => 'inventory_legend'],
        'sql' => "varchar(16) NOT NULL default ''",
    ],
    'bookingBlock' => [
        'label' => &$GLOBALS['TL_LANG']['tl_iso_product']['bookingBlock'],
        'inputType' => 'text',
        'eval' => [
            'tl_class' => 'w50',
            'rgxp' => 'natural',
        ],
        'attributes' => ['legend' => 'inventory_legend'],
        'sql' => "int(10) unsigned NOT NULL default '0'",
    ],
    'bookingReservedDates' => [
        'inputType' => 'fieldpalette',
        'foreignKey' => 'tl_fieldpalette.id',
        'relation' => ['type' => 'hasMany', 'load' => 'eager'],
        'eval' => ['tl_class' => 'clr'],
        'attributes' => ['legend' => 'inventory_legend'],
        'sql' => 'blob NULL',
        'fieldpalette' => [
            'config' => [
                'hidePublished' => true,
            ],
            'list' => [
                'label' => [
                    'fields' => ['start', 'stop'],
                    'format' => '%s - %s',
                ],
            ],
            'palettes' => [
                '__selector__' => ['useCount'],
                'default' => '{block_legend},start,stop,useCount;',
            ],
            'subpalettes' => [
                'useCount' => 'count',
            ],
            'fields' => [
                'start' => [
                    'label' => &$GLOBALS['TL_LANG']['tl_iso_product']['start'],
                    'exclude' => true,
                    'inputType' => 'text',
                    'eval' => ['rgxp' => 'date', 'datepicker' => true, 'tl_class' => 'w50 wizard', 'mandatory' => true],
                    'sql' => "varchar(10) NOT NULL default ''",
                ],
                'stop' => [
                    'label' => &$GLOBALS['TL_LANG']['tl_iso_product']['stop'],
                    'exclude' => true,
                    'inputType' => 'text',
                    'eval' => ['rgxp' => 'date', 'datepicker' => true, 'tl_class' => 'w50 wizard', 'mandatory' => true],
                    'sql' => "varchar(10) NOT NULL default ''",
                ],
                'useCount' => [
                    'label' => &$GLOBALS['TL_LANG']['tl_iso_product']['useCount'],
                    'exclude' => true,
                    'inputType' => 'checkbox',
                    'eval' => ['tl_class' => 'clr w50', 'submitOnChange' => true],
                    'sql' => "char(1) NOT NULL default ''",
                ],
                'count' => [
                    'label' => &$GLOBALS['TL_LANG']['tl_iso_product']['count'],
                    'exclude' => true,
                    'inputType' => 'text',
                    'eval' => ['tl_class' => 'w50'],
                    'sql' => "varchar(10) NOT NULL default '1'",
                ],
            ],
        ],
    ],
    'bookingOverview' => [
        'inputType' => 'huh_be_explanation',
        'eval' => [
            'text_callback' => [IsoProductContainer::class, 'onBookingOverviewTextCallback'],
            'tl_class' => 'clr',
        ],
        'attributes' => ['legend' => 'inventory_legend'],
    ],
];

$dca['fields'] = array_merge($dca['fields'], $fields);
