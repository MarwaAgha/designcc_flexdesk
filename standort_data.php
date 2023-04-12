<?php
    // DB Verbindung
    include('connect.php');

    // GET Variablen herausfinden
    $standort = $_GET['standort'];
    $datum = $_GET['datum'];

    // Array einrichten
    $standorte_arr = [];

    // Die Standorte aus DB abrufen
    $standorte_result = $mysqli->query("SELECT * FROM standorte");
    while($standorte_rows = $standorte_result->fetch_assoc())
    {
        // Die Karte vom Standort 
        $standorte_arr[$standorte_rows['standort']]['map'] = $standorte_rows['map'];

        // Die Schreibtische im Standort anlegen
        $s = 0;
        $schreibtische_result = $mysqli->query("SELECT * FROM schreibtische WHERE standort_id ='" . $standorte_rows['id'] . "'");
        while($schreibtische_rows = $schreibtische_result->fetch_assoc())
        {
            $standorte_arr[$standorte_rows['standort']]['schreibtische'][$s]['id'] = $schreibtische_rows['id'];
            $standorte_arr[$standorte_rows['standort']]['schreibtische'][$s]['nummer'] = $schreibtische_rows['nummer'];
            $standorte_arr[$standorte_rows['standort']]['schreibtische'][$s]['inventare'] = $schreibtische_rows['inventare'];
            $standorte_arr[$standorte_rows['standort']]['schreibtische'][$s]['position'] = [$schreibtische_rows['positionX'], $schreibtische_rows['positionY']];
            $standorte_arr[$standorte_rows['standort']]['schreibtische'][$s]['verfugbar'] = true;

            // Die Verfügbarkeit prüfen
            $schreibtische_datums_result = $mysqli->query("SELECT * FROM schreibtische_datums WHERE schreibtisch_id ='" . $schreibtische_rows['id'] . "' AND datum = '" . strtotime($datum) . "'");
            if($schreibtische_datums_result->num_rows == 1)
            {
                $standorte_arr[$standorte_rows['standort']]['schreibtische'][$s]['verfugbar'] = false;
            }

            $s++;
        }
    }

    // Senden Result als JSON
    echo json_encode($standorte_arr);