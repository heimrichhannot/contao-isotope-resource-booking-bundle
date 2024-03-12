<?php

namespace HeimrichHannot\IsotopeResourceBookingBundle\Migration;

use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\CoreBundle\Migration\MigrationInterface;
use Contao\CoreBundle\Migration\MigrationResult;
use HeimrichHannot\IsotopeResourceBookingBundle\Model\ProductBookingModel;
use Isotope\Model\ProductCollection;
use Isotope\Model\ProductCollectionItem;

class BookingPlanTableMigration implements MigrationInterface
{
    private ContaoFramework $framework;

    public function __construct(ContaoFramework $framework)
    {
        $this->framework = $framework;
    }

    public function getName(): string
    {
        return "Isotope Resource Booking Table migration";
    }

    public function shouldRun(): bool
    {
        $this->framework->initialize();

        $items = ProductCollectionItem::findBy(['product_id =?'], [105]);
        if (!$items) {
            return false;
        }

        $run = false;
        foreach ($items as $item) {
            if ($item->bookingStart && $item->bookingStop) {
                $run = true;
                break;
            }
        }

        return $run;
    }

    public function run(): MigrationResult
    {
        /** @var ProductCollectionItem[] $items */
        $items = ProductCollectionItem::findBy(['product_id =?'], [105]);
        if (!$items) {
            return new MigrationResult(false, $this->getName() . " already ran");
        }

        foreach ($items as $item) {
            if (!$item->bookingStart || !$item->bookingStop) {
                continue;
            }

            /** @var ProductCollection $order */
            $order = $item->getRelated('pid');

            $bookingModel = new ProductBookingModel();
            $bookingModel->pid = $item->product_id;
            $bookingModel->tstamp = time();
            $bookingModel->start = (int)$item->bookingStart;
            $bookingModel->stop = (int)$item->bookingStop;
            $bookingModel->count = $item->quantity;
            $bookingModel->comment = $item->bookingComment;
            $bookingModel->document_number = $order->getDocumentNumber();
            $bookingModel->product_collection_id = $item->id;
            $bookingModel->save();

            $item->bookingStart = '';
            $item->bookingStop = '';
            $item->save();
        }

        return new MigrationResult(true, $this->getName() . " was successful");
    }
}