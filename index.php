<?php
    include('connect.php');
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Office Map</title>
    <!-- Leaflet-Bibliothek einbinden -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css" />
    <!-- Bootstrap -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
    <style>
        #karte {
            height: 500px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1 class="mt-5">Office Map</h1>

        <!-- Formular Beginn -->
        <form>
            <div class="form-group">
                <label for="standort">Standort:</label>
                <select name="standort" id="standort" class="form-control">
                    <option value="">Standort auswählen</option>
                    <?php
                        // Standorte aus der DB
                        $standorte_result = $mysqli->query("SELECT * FROM standorte");
                        while($standorte_rows = $standorte_result->fetch_assoc())
                        {
                            ?>
                            <option value="<?=$standorte_rows['standort']?>"><?=$standorte_rows['standort']?></option>
                            <?php
                        }
                    ?>
                </select>
            </div>

            <div class="form-group">
                <label for="datum">Datum:</label>
                <input type="date" id="datum" name="datum" class="form-control">
            </div>

            <div class="form-group">
                <label for="name">Name:</label>
                <input type="text" id="name" name="name" class="form-control" autocomplete="off">
            </div>

            <div id="karteDiv" class="form-group mt-20">
                <label for="karte">Office Karte:</label>
                <div id="karte"></div>
            </div>
        </form>
        <!-- Formular Ende -->
    </div>

    <!-- JS Library -->
    <script src="https://code.jquery.com/jquery-3.3.1.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>
    <script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js"></script>
    <script>
        // Icons der Karte einrichten
        var greenIcon = L.icon({
            iconUrl: 'https://cdn.rawgit.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-green.png',
            iconSize: [25, 41],
            iconAnchor: [12, 41],
            popupAnchor: [1, -34],
            shadowSize: [41, 41],
            shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/0.7.7/images/marker-shadow.png',
            color: 'green' // Farbwert für das Symbol
        });

        var redIcon = L.icon({
            iconUrl: 'https://cdn.rawgit.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-red.png',
            iconSize: [25, 41],
            iconAnchor: [12, 41],
            popupAnchor: [1, -34],
            shadowSize: [41, 41],
            shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/0.7.7/images/marker-shadow.png',
            color: 'red' // Farbwert für das Symbol
        });

        // Leaflet-Karte initialisieren
        let karte = L.map('karte', {
            crs: L.CRS.Simple
        });
        let bounds = [[0,0], [500,500]];

        var markerLayer = L.layerGroup();
        var markers = []; // Array für die Marker-Objekte


        // Beim Laden muss die Karte versteckt werden
        $('#karteDiv').hide();

        // Beim Aktualisierung den Standort oder das Datum die Funktion getPlaces aufrufen
        $('#standort, #datum').change(function() {
            getPlaces();
        });

        /* 
        * Die Funktion getPlaces kümmert sich um Daten aus der DB abzurufen
        */
        function getPlaces() {
            let standort = $('#standort').val();
            let datum = $('#datum').val();

            // Den Standort und das Datum prüfen
            if (standort !== '' && datum !== '') {
                // Die Karte anzeigen
                $('#karteDiv').show();
                
                // Die alte Marker entfernen
                markerLayer.clearLayers();

                // AJAX aufrufen
                $.ajax({
                    url: "standort_data.php",
                    type: "GET",
                    data: {
                        standort: standort,
                        datum: datum
                    },
                    success: function(standorte){
                        // Die Daten an JSON Format konvertieren
                        standorte = JSON.parse(standorte);

                        // Koordinaten und Schreibtischdaten für verschiedene Standorte
                        let schreibtische = standorte[standort]['schreibtische'];
                        let image = standorte[standort]['map'];

                        karte.fitBounds(bounds);

                        // Hinzufügen der Bilddatei zur Karte
                        L.imageOverlay(image, bounds).addTo(karte);

                        for (let i = 0; i < schreibtische.length; i++) {
                            // Die Daten vom Schreibtisch
                            let schreibtisch = schreibtische[i];
                            let id = schreibtisch['id'];
                            let nummer = schreibtisch['nummer'];
                            let inventare = schreibtisch['inventare'];
                            let position = schreibtisch['position'];
                            let verfugbar = schreibtisch['verfugbar'];

                            // PopUp Text vorbereiten
                            let TextPopup = '<b>' + nummer + '</b>:<br>' + inventare + '<br><br>' + (verfugbar ? '<input type="button" value="Reservieren" onClick="reservieren(\'' + id + '\')">' : '<b>Dieser Platz ist nicht verfügbar</b>');

                            // Markierung erstellen und zur Marker-Gruppe hinzufügen
                            let marker = L.marker(position, {
                                icon: verfugbar ? greenIcon : redIcon
                            }).bindPopup(TextPopup);
                            markers.push(marker);
                            markerLayer.addLayer(marker);
                        }

                        // Marker-Gruppe zur Karte hinzufügen
                        karte.addLayer(markerLayer);
                    }
                });
            }
        }

        /* 
        * Die Funktion der Reservierung
        * id vom Schreibtisch
        */
        function reservieren(id) {
            let standort = $('#standort').val();
            let name = $('#name').val();
            let datum = $('#datum').val();
            let schreibtisch_id = id;

            // Die Daten vom Formular prüfen
            if (standort == '' || datum == '' || name == '') {
                alert('Bitte alle Felder eintragen');
            } else {
                // AJAX aufrufen
                $.ajax({
                    url: "schreibtisch_reservieren.php",
                    type: "GET",
                    data: {
                        name: name,
                        datum: datum,
                        schreibtisch_id: schreibtisch_id
                    },
                    success: function(data){
                        // Die Daten an JSON Format konvertieren
                        data = JSON.parse(data);

                        // Wenn einen Fehler
                        if(data.error) {
                            alert(data.error);
                        } else {
                            // Wenn keinen Fehler
                            // Die Karte neue laden
                            getPlaces(); 
                        }
                    },
                    error: function (request, status, error) {
                        console.log(request.responseText);
                    }
                });
            }
        }
    </script>
</body>
</html>