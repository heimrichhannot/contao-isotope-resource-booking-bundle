<?php

/*
 * Copyright (c) 2023 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

$dca = &$GLOBALS['TL_DCA']['tl_iso_product_collection_item'];

$dca['fields']['bookingStart'] = [
    'inputType' => 'text',
    'eval' => ['tl_class' => 'w50', 'rgxp' => 'date'],
    'sql' => "varchar(16) NOT NULL default ''",
];

$dca['fields']['bookingStop'] = [
    'inputType' => 'text',
    'eval' => ['tl_class' => 'w50', 'rgxp' => 'date'],
    'sql' => "varchar(16) NOT NULL default ''",
];
