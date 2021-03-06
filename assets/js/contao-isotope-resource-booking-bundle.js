import flatpickr from "flatpickr";
import "flatpickr/dist/flatpickr.css"
import $ from "jquery";
import moment from 'moment';

class HeimrichHannotIsotopeResourceBookingBundle
{
    init() {
        this.initBookingPlan();
        this.registerEvents();
    }

    registerEvents() {
        $(document).on('change', '.quantity_container input', function() {
            HeimrichHannotIsotopeResourceBookingBundle.updateBookingPlan($(this));
        });
    };

    initBookingPlan() {
        let input = $(document).find('#bookingPlan'),
            blocked = input.data('blocked');

        HeimrichHannotIsotopeResourceBookingBundle.initFlatpickr(blocked);
    };

    static initFlatpickr(blocked) {
        let lang = document.querySelector('html').getAttribute('lang');

        import(/* webpackChunkName: "flatpickr-[request]" */ 'flatpickr/dist/l10n/' + lang + '.js').then((locale) =>
        {
            // flatpickr.localize(locale.default[locale]);
            flatpickr('#bookingPlan', {
                dateFormat: 'd.m.Y',
                minDate: 'today',
                locale: lang,
                mode: 'range',
                inline: true,
                onDayCreate: function(dObj, dStr, fp, dayElem) {
                    var date = dayElem.dateObj;

                    var dateString = HeimrichHannotIsotopeResourceBookingBundle.getComparableDate(date.getTime());

                    $.each(blocked, function(key, value) {
                        // need to convert to date string since tstamps could be in different timezone format
                        if (moment.unix(value).format('DD.MM.YYYY') == moment.unix(dateString).format('DD.MM.YYYY')) {
                            dayElem.className += ' flatpickr-disabled blocked';
                        }
                    });
                },
            });
        });
    };

    updateBookingPlan(elem) {
        let url = $(document).find('.bookingPlan_container').data('update'),
            productId = $(document).find('.bookingPlan_container').data('productId'),
            qantity = elem.val();

        $.ajax({
            url: url,
            dataType: 'JSON',
            method: 'POST',
            data: {'productId': productId, 'quantity': qantity},
            success: function(data) {
                if (undefined !== data.result.data.blocked) {
                    HeimrichHannotIsotopeResourceBookingBundle.initFlatpickr(data.result.data.blocked);
                } else {
                    alert('Ein Fehler ist aufgetreten!');
                }
            },
        });
    };

    static getComparableDate(date) {
        date = date.toString().substring(0, 10);
        date = parseInt(date);
        return date + 7200;
    };
}

let instance = new HeimrichHannotIsotopeResourceBookingBundle();
instance.init();