<?php
    // DB Verbindung
    include('connect.php');

    // GET Variablen herausfinden
    $name = $_GET['name'];
    $datum = $_GET['datum'];
    $schreibtisch_id = $_GET['schreibtisch_id'];

    // Variablen prüfen
    if ($name !== '' && $datum !== '' && $schreibtisch_id !== '') {
        $schreibtische_datums_result = $mysqli->query("SELECT * FROM schreibtische_datums WHERE schreibtisch_id ='" . $schreibtisch_id . "' AND datum = '" . strtotime($datum) . "'");

        // Die Verfügbar prüfen
        if($schreibtische_datums_result->num_rows == 0) {
            $mysqli->query("INSERT INTO schreibtische_datums (schreibtisch_id, name, datum) VALUES ('" . $schreibtisch_id . "', '" . $name . "', '" . strtotime($datum) . "')");

            $result['success'] = 'Dieser Platz ist erfolgereich für dich reserviert';
        } else {
            $result['error'] = 'Dieser Platz ist nicht verfügbar';
        }
    } else {
        $result['error'] = 'Bitte alle Felder eintragen';
    }

    // Senden Result als JSON
    echo json_encode($result);
    
    