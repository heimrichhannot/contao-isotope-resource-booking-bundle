<?php

use Contao\CoreBundle\DataContainer\PaletteManipulator;

$dca = &$GLOBALS['TL_DCA']['tl_iso_producttype'];

PaletteManipulator::create()
    ->addLegend('booking_legend', 'description_legend', PaletteManipulator::POSITION_AFTER, true)
    ->addField('addResourceBooking', 'booking_legend', PaletteManipulator::POSITION_APPEND)
    ->applyToPalette('standard', 'tl_iso_producttype');

$dca['palettes']['__selector__'][] = 'addResourceBooking';
$dca['subpalettes']['addResourceBooking'] = 'allowBlockingTime';

$dca['fields']['addResourceBooking'] = [
    'exclude'   => true,
    'filter'    => true,
    'inputType' => 'checkbox',
    'eval'      => ['tl_class' => 'clr w50', 'submitOnChange' => true],
    'sql'       => "char(1) NOT NULL default ''",
];

$dca['fields']['allowBlockingTime'] = [
    'exclude'   => true,
    'inputType' => 'checkbox',
    'eval'      => ['tl_class' => 'w50'],
    'sql'       => "char(1) NOT NULL default ''",
];