<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

$lang = &$GLOBALS['TL_LANG']['tl_iso_product'];

$lang['bookingStart'] = ['Buchungszeitraum-Start', 'Wählen Sie hier den Beginn der Buchung aus.'];
$lang['bookingStop'] = ['Buchungszeitraum-Ende', 'Wählen Sie hier das Ende der Buchung aus.'];
$lang['bookingBlock'] = [
    'Artikel vor/nach Bestellung blockieren',
    'Tragen Sie hier die Anzahl der Tage ein die ein Artikel vor und nach seiner Buchung gesperrt sein soll. Dies kann bspw. benötigt werden wenn ein Artikel für einen Buchungszeitraum gebucht wird und nach dem Buchungszeitraum aus logistischen Gründen für eine Zeit gesperrt ist.',
];
$lang['bookingReservedDates'] = [
    'Produkt-Reservierungen',
    'Sie können hier Zeiträume hinterlegen, für die das Produkt reserviert sein soll. Die angegebenen Daten werden in die Berechnung der gesperrten Tage des Produktes aufgenommen.',
];

$lang['useCount'] = ['Anzahl festlegen', 'Wählen Sie diese Option, wenn Sie die Anzahl der reservierten Produkte angeben wollen. Wenn Sie diese Option nicht wählen, werden alle auf Lager befindlichen Produkte reserviert.'];
$lang['count'] = ['Anzahl', 'Wählen Sie hier die Anzahl der zu reservierenden Produkte aus.'];
