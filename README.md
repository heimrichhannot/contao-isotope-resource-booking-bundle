# Contao Isotope Resource Booking Bundle

[![](https://img.shields.io/packagist/v/heimrichhannot/contao-isotope-resource-booking-bundle.svg)](https://packagist.org/packages/heimrichhannot/contao-isotope-resource-booking-bundle)
[![](https://img.shields.io/packagist/dt/heimrichhannot/contao-isotope-resource-booking-bundle.svg)](https://packagist.org/packages/heimrichhannot/contao-isotope-resource-booking-bundle)

This bundle adds a booking functionality for products to [Isotope](https://isotopeecommerce.org).

![](docs/img/booking_frontend.png)

## Limitations

Works currently only with [Encore Bundle](https://github.com/heimrichhannot/contao-encore-bundle).

## Setup

Install with composer or contao manager and update database afterwards.

    composer require heimrichhannot/contao-isotope-resource-booking-bundle

## Usage

Booking functionality has to be activated in product type setting.

![backend_activate_booking.png](docs%2Fimg%2Fbackend_activate_booking.png)

Additionally, you can activate blocking times. This features allows to add a time frame around the booking date to block it for other bookings. This can be useful for shipping, printing, etc.

Afterwards you'll find a new operation in product list for products of these categories. This operation allows to manage bookings for the product. You'll see a list of all bookings and can add custom booking/blocked timeframges from backend.

![backend_operation.png](docs%2Fimg%2Fbackend_operation.png)

If you activated blocking times in product type settings, you'll see a new field in product settings to manage blocked timeframes.

To output the datepicker in the frontend, you need to activate the edit_booking_plan action in the frontend module settings.