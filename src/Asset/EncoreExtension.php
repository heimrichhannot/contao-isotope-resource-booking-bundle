<?php

namespace HeimrichHannot\IsotopeResourceBookingBundle\Asset;

use HeimrichHannot\EncoreContracts\EncoreEntry;
use HeimrichHannot\EncoreContracts\EncoreExtensionInterface;
use HeimrichHannot\IsotopeResourceBookingBundle\HeimrichHannotIsotopeResourceBookingBundle;

class EncoreExtension implements EncoreExtensionInterface
{

    public function getBundle(): string
    {
        return HeimrichHannotIsotopeResourceBookingBundle::class;
    }

    public function getEntries(): array
    {
        return [
            EncoreEntry::create("contao-isotope-resource-booking-bundle", 'assets/js/contao-isotope-resource-booking-bundle.js')
                ->setRequiresCss(true),
        ];
    }
}