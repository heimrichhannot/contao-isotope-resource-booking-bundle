<?php

namespace HeimrichHannot\IsotopeResourceBookingBundle\Migration;

use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\CoreBundle\Migration\MigrationInterface;
use Contao\CoreBundle\Migration\MigrationResult;
use Contao\Model\Collection;
use Contao\StringUtil;
use HeimrichHannot\FieldpaletteBundle\Model\FieldPaletteModel;
use HeimrichHannot\IsotopeResourceBookingBundle\Model\ProductBookingModel;
use HeimrichHannot\UtilsBundle\Util\Utils;
use Isotope\Model\Product;
use Isotope\Model\ProductCollection;
use Isotope\Model\ProductCollectionItem;
use Isotope\Model\ProductType;

class IsotopeBooking000200Migration implements MigrationInterface
{
    private ContaoFramework $framework;
    private Utils $utils;

    public function __construct(ContaoFramework $framework, Utils $utils)
    {
        $this->framework = $framework;
        $this->utils = $utils;
    }

    public function getName(): string
    {
        return "Isotope Resource Booking 0.2.0 migration";
    }

    public function shouldRun(): bool
    {
        $this->framework->initialize();

        return
            $this->migrateReservations()
         || $this->migrateBookings()
         || $this->migrateActivation();
    }

    private function migrateActivation(bool $run = false): bool
    {
        /** @var ProductType[]|Collection|null $productTypes */
        $productTypes = ProductType::findAll();
        foreach ($productTypes as $productType) {
            $attributes = StringUtil::deserialize($productType->attributes, true);
            if (empty($attributes['bookingOverview']) && empty($attributes['bookingReservedDates'])) {
                continue;
            }

            if (!($attributes['bookingOverview']['enabled'] ?? false) && !($attributes['bookingReservedDates']['enabled'] ?? false)) {
                continue;
            }

            if (!$run) {
                return true;
            }

            $productType->addResourceBooking = '1';

            if ($attributes['bookingReservedDates']['enabled'] ?? false) {
                $productType->allowBlockingTime = '1';
            }

            $productType->save();

            unset($attributes['bookingOverview']);
            unset($attributes['bookingReservedDates']);
            $productType->attributes = serialize($attributes);
            $productType->save();
        }

        return false;
    }

    private function migrateReservations(bool $run = false): bool
    {
        $product = Product::findByPk(32);
        if (!$product) {
            return $run;
        }

        if (!$product->bookingReservedDates) {
            return $run;
        }

        if (!$run) {
            return true;
        }

        $ids = StringUtil::deserialize($product->bookingReservedDates, true);
        foreach ($ids as $id) {
            $reservation = FieldPaletteModel::findByPk($id);
            if (!$reservation) {
                continue;
            }

            $bookingModel = new ProductBookingModel();
            $bookingModel->pid = $product->id;
            $bookingModel->tstamp = time();
            $bookingModel->start = $reservation->start;
            $bookingModel->stop = $reservation->stop;
            $bookingModel->count = $reservation->count;
            $bookingModel->comment = "Reservierung vom ".date("d.m.Y", $reservation->tstamp);
            $bookingModel->save();

            $reservation->delete();
        }

        $product->bookingReservedDates = null;
        $product->save();

        return true;
    }

    private function migrateBookings(bool $run = false): bool
    {
//        tl_iso_product_collection.type = 'order';

        $items = ProductCollectionItem::findBy(
            ['product_id =?', 'bookingStart!=?', 'bookingStop!=?'],
            [32, '', '']
        );
        if (!$items) {
            return $run;
        }

        if (!$run) {
            return true;
        }

        foreach ($items as $item) {
            $collection = $item->getRelated('pid');

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

        return true;
    }

    public function run(): MigrationResult
    {
        if (!$this->migrateActivation(true)) {
            return new MigrationResult(false, $this->getName() . " migrateActivation failed");
        }
        if (!$this->migrateReservations(true)) {
            return new MigrationResult(false, $this->getName() . " migrateReservations failed");
        }
        if (!$this->migrateBookings(true)) {
            return new MigrationResult(false, $this->getName() . " migrateBookings failed");
        }

        return new MigrationResult(true, $this->getName() . " was successful");
    }
}