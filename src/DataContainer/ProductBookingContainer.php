<?php

namespace HeimrichHannot\IsotopeResourceBookingBundle\DataContainer;

use Contao\CoreBundle\ServiceAnnotation\Callback;
use Contao\Date;
use Symfony\Contracts\Translation\TranslatorInterface;

class ProductBookingContainer
{
    private TranslatorInterface $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }


    /**
     * @Callback(table="tl_iso_product_booking", target="list.sorting.child_record")
     */
    public function onListSortingChildRecordCallback(array $row): string
    {
        return '<b>'.Date::parse(Date::getNumericDateFormat(), $row['start']) . ' - '
            . Date::parse(Date::getNumericDateFormat(), $row['stop']).'</b>'
            . (($row['document_number'] || $row['comment']) ? '<span style="color:#999;"> [' : '')
            . (($row['document_number']) ?
                $this->translator->trans('tl_iso_product_collection.document_number.0', [], 'contao_tl_iso_product_collection'). ': '.$row['document_number']: '')
            . (($row['comment']) ?: '')
            . (($row['document_number'] || $row['comment']) ? ']</span>' : '');


    }
}