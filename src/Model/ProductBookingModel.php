<?php

namespace HeimrichHannot\IsotopeResourceBookingBundle\Model;

use Contao\Model;
use Contao\Model\Collection;
use Isotope\Interfaces\IsotopeProduct;

/**
 * @property int $id
 * @property int $pid
 * @property int $tstamp
 * @property int $start
 * @property int $stop
 * @property int $count
 * @property string $comment
 * @property string $document_number
 * @property int $product_collection_id
 * @property int $product_collection_item_id
 */
class ProductBookingModel extends Model
{
    protected static $strTable = 'tl_iso_product_booking';

    /**
     * @param IsotopeProduct|int $product
     * @param array $options
     * @return Collection|null
     */
    public static function findByProduct($product, array $options = []): ?Collection
    {
        if ($product instanceof IsotopeProduct) {
            $product = $product->id;
        }
        if (!is_int($product)) {
            return null;
        }

        return static::findBy(['pid=?'], [$product], $options);
    }
}