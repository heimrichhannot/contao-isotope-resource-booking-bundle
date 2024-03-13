<?php

use Contao\CoreBundle\DataContainer\PaletteManipulator;

$dca = &$GLOBALS['TL_DCA']['tl_iso_producttype'];

PaletteManipulator::create()
    ->addField('addResourceBooking', 'expert_legend', PaletteManipulator::POSITION_APPEND)
    ->applyToPalette('standard', 'tl_iso_producttype');

$dca['fields']['addResourceBooking'] = [
    'exclude'   => true,
    'inputType' => 'checkbox',
    'eval'      => ['tl_class' => 'clr', 'submitOnChange' => true],
    'sql'       => "char(1) NOT NULL default ''",
];