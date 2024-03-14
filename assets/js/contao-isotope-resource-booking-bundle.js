import flatpickr from "flatpickr";
import "flatpickr/dist/flatpickr.css"
import $ from "jquery";

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
        let input = $(document).find('#bookingPlan');
        let blocked = input.data('blocked');
        let reserved = input.data('reserved');

        HeimrichHannotIsotopeResourceBookingBundle.initFlatpickr(blocked, reserved);
    };

    static initFlatpickr(blocked, reserved) {
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
                    /**
                     * @var Date date
                     */
                    let date = dayElem.dateObj;
                    let adjustedDate = new Date(date.getTime() - (date.getTimezoneOffset() * 60 * 1000));
                    let dateString = adjustedDate.toISOString().split('T')[0];

                    if (blocked.includes(dateString)) {
                        dayElem.className += ' flatpickr-disabled blocked';
                    } else if (reserved.includes(dateString)) {
                        dayElem.className += ' flatpickr-disabled reserved';
                    }
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
                    HeimrichHannotIsotopeResourceBookingBundle.initFlatpickr(data.result.data.blocked, data.result.data.reserved);
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