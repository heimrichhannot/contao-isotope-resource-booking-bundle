<?php

namespace HeimrichHannot\IsotopeResourceBookingBundle\EventListener\Isotope;

use HeimrichHannot\IsotopeResourceBookingBundle\Model\ProductBookingModel;
use Isotope\Model\ProductCollection\Order;
use Isotope\ServiceAnnotation\IsotopeHook;

/**
 * @IsotopeHook("postCheckout")
 */
class PostCheckoutListener
{
    public function __invoke(Order $order, array $tokens): void
    {
        foreach ($order->getItems() as $item) {
            if (!$item->getProduct()->getType()->addResourceBooking) {
                continue;
            }

            if (!$item->bookingStart || !$item->bookingStop) {
                continue;
            }

            $booking = new ProductBookingModel();
            $booking->tstamp = time();
            $booking->pid = $item->getProduct()->id;
            $booking->start = (int)$item->bookingStart;
            $booking->stop = (int)$item->bookingStop;
            $booking->count = $item->quantity;
            $booking->product_collection_id = $item->pid;
            $booking->document_number = $order->getDocumentNumber();
            $booking->save();

            $item->bookingStart = '';
            $item->bookingStop = '';
            $item->save();
        }
    }
}