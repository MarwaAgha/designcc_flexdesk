<?php
    // Default timezone aufsetzen
    date_default_timezone_set('Europe/Berlin');

    // An DB verbinden
    $mysqli = new mysqli("localhost", "designcc_flexdesk", "LmLGn6hdJHdf", "designcc_flexdesk");