async function huhIsotopeRessourceBookingBackend() {
    document.querySelectorAll("#huh_isotope_bookingoverview_prev, #huh_isotope_bookingoverview_next").forEach((element) => {
        element.addEventListener('click', async (event) => {
            event.preventDefault();
            const response = await fetch(event.currentTarget.href);
            let string = await response.text();
            doc = (new DOMParser()).parseFromString(string, 'text/html');

            document.querySelector('#huh_isotope_backend_product_booking_overview').innerHTML = doc.querySelector('#huh_isotope_backend_product_booking_overview').innerHTML;
            huhIsotopeRessourceBookingBackend();
        });
    });
}

document.addEventListener('DOMContentLoaded', huhIsotopeRessourceBookingBackend);