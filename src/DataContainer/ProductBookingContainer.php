<?php

namespace HeimrichHannot\IsotopeResourceBookingBundle\DataContainer;

use Contao\Controller;
use Contao\CoreBundle\ServiceAnnotation\Callback;
use Contao\DataContainer;
use Contao\Date;
use Contao\Image;
use Contao\StringUtil;
use HeimrichHannot\IsotopeResourceBookingBundle\Model\ProductBookingModel;
use HeimrichHannot\UtilsBundle\Util\Utils;
use Isotope\Model\ProductCollection\Order;
use Symfony\Contracts\Translation\TranslatorInterface;

class ProductBookingContainer
{
    private TranslatorInterface $translator;
    private Utils $utils;

    public function __construct(TranslatorInterface $translator, Utils $utils)
    {
        $this->translator = $translator;
        $this->utils = $utils;
    }

    /**
     * @Callback(table="tl_iso_product_booking", target="config.onload")
     */
    public function onLoadCallback(DataContainer $dc = null): void
    {
        if (!$dc || !$dc->id || !($bookingModel = ProductBookingModel::findByPk($dc->id))) {
            return;
        }

        if ($bookingModel->product_collection_id) {
            $dca = &$GLOBALS['TL_DCA']['tl_iso_product_booking'];
            $dca['fields']['start']['eval']['readonly'] = true;
            $dca['fields']['stop']['eval']['readonly'] = true;
            $dca['fields']['count']['eval']['readonly'] = true;
        }
    }

    /**
     * @Callback(table="tl_iso_product_booking", target="list.sorting.child_record")
     */
    public function onListSortingChildRecordCallback(array $row): string
    {
        $order = null;
        if ($row['product_collection_id']) {
            $order = Order::findByPk($row['product_collection_id']);
            $order = sprintf('<a href="%s" style="color:#999;">'.$this->translator->trans('tl_iso_product_collection.document_number.0', [], 'contao_tl_iso_product_collection').' '.$order->document_number.'</a>',
                $this->utils->routing()->generateBackendRoute(['do' => 'iso_orders', 'act' => 'edit', 'id' => $row['product_collection_id']])
            );
        }

        $suffix = implode(', ', array_filter([$order, $row['comment']]));
        return '<b>'.Date::parse(Date::getNumericDateFormat(), $row['start']) . ' - '
            . Date::parse(Date::getNumericDateFormat(), $row['stop']).'</b>'
            . ($suffix ? '<span style="color:#999;padding-left: 3px;">['.$suffix.']</span>' : '');
    }

    /**
     * @Callback(table="tl_iso_product_booking", target="fields.document_number.wizard")
     */
    public function onDocumentNumberWizardCallback(DataContainer $dc): string
    {
        if ($dc->id < 1) {
            return '';
        }

        $booking = ProductBookingModel::findByPk($dc->id);
        if (!$booking) {
            return '';
        }

        $order = Order::findByPk($booking->product_collection_id);
        if (!$order) {
            return '';
        }

        Controller::loadLanguageFile('tl_iso_order');
        $url = $this->utils->routing()->generateBackendRoute(['do' => 'iso_orders', 'act' => 'edit', 'id' => $booking->product_collection_id]);

        return sprintf(
            ' <a href="%s" title="%s" style="padding-left:3px" onclick="Backend.openModalIframe({\'width\':768,\'title\':\'%s\',\'url\':this.href});return false">%s</a>',
            $url,
            sprintf(StringUtil::specialchars($GLOBALS['TL_LANG']['tl_iso_order']['edit'][1]), $dc->value),
            StringUtil::specialchars(str_replace("'", "\\'", sprintf($GLOBALS['TL_LANG']['tl_iso_order']['edit'][1], $dc->value))),
            Image::getHtml('alias.svg', $GLOBALS['TL_LANG']['tl_iso_producttype']['edit'][0])
        );
    }
}