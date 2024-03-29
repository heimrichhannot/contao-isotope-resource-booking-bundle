# Changelog

All notable changes to this project will be documented in this file.

## [0.2.3] - 2024-03-14
- Fixed: migration issues

## [0.2.2] - 2024-03-14
- Fixed: create booking table on migration if not exists

## [0.2.1] - 2024-03-14
- Fixed: timezone issue js
- Fixed: blocking time evaluation

## [0.2.0] - 2024-03-14
This release introduces a own table to booking data. So it is very important to run the migration after updating to this version.

- Changed: booking data now stored in own table 
- Changed: a lot of refactoring and cleanup
- Changed: reserved dates (products in cart) now visible in picker with own css class
- Changed: removed fieldpalette dependency
- Changed: removed moments.js dependency

## [0.1.9] - 2024-01-29
- Changed: added encore contracts support

## [0.1.8] - 2024-01-29
- Fixed: checkout not working
- Fixed: backend booking widget not showing correct time
- Fixed: missing alt texts on backend widget
- Fixed: missing translations for backend widget
- Fixed: broken navigation in backend widget
- Fixed: missing dependency for fieldpalette

## [0.1.7] - 2023-03-29
- Fixed: backend widget not working ([#7], [#8])
- Fixed: palette issues in backend ([#7], [#8])
- Fixed: missing translation keys  ([#7], [#8])

## [0.1.6] - 2023-03-22
- Fixed: unknown column exception ([#6])
- Fixed: routes not correctly registered

## [0.1.5] - 2023-03-17
- Fixed: wrong class used ([#5])

## [0.1.4] - 2023-03-16 
- Fixed: internal error([#4])

## [0.1.3] - 2023-03-13
- Fixed: undefined method call exception ([#3])

## [0.1.2] - 2022-09-06
- Fixed: symfony 5 compatibility ([#2])

## [0.1.1] - 2021-10-04
- Changed: tl_iso_product.bookingBlock is not an integer field
- Changed: the display of blocked dates in the picker now also calculates the blocked timeframe of new dates
- Fixed: added products to cart if booking dates not available was possible
- Fixed: missing translations for backend

## [0.1.0] - 2021-09-24
Migrated from Isotope Bundle


[#8]: https://github.com/heimrichhannot/contao-isotope-resource-booking-bundle/pull/8
[#7]: https://github.com/heimrichhannot/contao-isotope-resource-booking-bundle/issues/7
[#6]: https://github.com/heimrichhannot/contao-isotope-resource-booking-bundle/issues/6
[#5]: https://github.com/heimrichhannot/contao-isotope-resource-booking-bundle/issues/5
[#4]: https://github.com/heimrichhannot/contao-isotope-resource-booking-bundle/issues/4
[#3]: https://github.com/heimrichhannot/contao-isotope-resource-booking-bundle/issues/3
[#2]: https://github.com/heimrichhannot/contao-isotope-resource-booking-bundle/issues/2