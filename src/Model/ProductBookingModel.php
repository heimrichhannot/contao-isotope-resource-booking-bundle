<?php

namespace HeimrichHannot\IsotopeResourceBookingBundle\Model;

use Contao\Model;

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
 */
class ProductBookingModel extends Model
{
    protected static $strTable = 'tl_iso_product_booking';
}