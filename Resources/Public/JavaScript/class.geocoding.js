/*
 * Handles geocoding and displays google maps with given locations
 *
 * @package				class.geocoding
 * @version				2.0: geocoding,  03-2018
 * @copyright 			(c)2018 Christian Brinkert
 * @author 				Christian Brinkert <christian.brinkert@googlemail.com>
 */



// Google Maps: AdvancedMarkerElement benoetigt eine Map-ID, sonst rendert der
// Marker nicht zuverlaessig; Googles Demo-ID genuegt fuer die Backend-Vorschau.
var CBM_GOOGLE_MAP_ID = 'DEMO_MAP_ID';

function Geocoding() {

    var self = this;
    var gmap = null;
    var osmmap = null;
    var osmmapPreview = null;
    var mapboxmap = null;
    var mapboxAccessToken = null;
    var mapboxMarker = null;
    var mapboxPopup = null;


    /**
     * Try to find coordinates by given address
     * @param uid int
     * @param mapProvider string
     * @param mapboxAccesstoken string
     * @return void
     */
    this.doGeocoding = function (uid, mapProvider, mapboxAccesstoken) {
        // verify existing gmap instance
        if (!this.gmap) {
            this.gmap = new Gmaps();
        }
        // set uid to the current gmap instance
        this.gmap.setUid(uid);

        // set address to gmap object
        this.getAddressFromBackend(this.gmap);

        // try to fetch coordinates by address, set callback method
        this.gmap.fetchCoordinatesByAddress(this.resultsHandling, mapProvider, mapboxAccesstoken);

    };



    /**
     * Result handle to check if gecoding was succesfull and display results in backend form
     * @param gmap Gmaps
     * @return void
     */
    this.resultsHandling = function (gmap) {
        // Koordinaten direkt in die Backend-Formularfelder schreiben - ohne Meldung
        if (gmap && typeof(gmap.getLatitude()) === 'number' && typeof(gmap.getLongitude()) === 'number') {
            if (!self.setCoordinatesToBackend(gmap)) {
                // Zielfelder nicht gefunden: nur Konsole, kein Alert
                if (window.console && console.warn) {
                    console.warn('cbgooglemaps: Formularfelder fuer Breite/Laenge nicht gefunden.');
                }
            }
            return;
        }

        alert("Die angegebene Adresse konnte nicht lokalisiert werden.");
    };



    /*
     * Display results in a new windows with coordinates and google maps preview
     * @param uid int
     * @param mapProvider string
     * @param mapboxAccesstoken string
     * @return void
     */
    this.displayLocation = function (uid, mapProvider, mapboxAccesstoken) {

        // verify existing gmap instance
        if (!this.gmap) {
            this.gmap = new Gmaps();
        }
        // set uid to the current gmap instance
        this.gmap.setUid(uid);


        // set address to gmap object
        this.getAddressFromBackend(this.gmap);
        this.getCoordinatesFromBackend(this.gmap);

        // check if coordinates are given
        if (typeof this.gmap.getLatitude() === 'number' && typeof this.gmap.getLongitude() === 'number') {

            // set attributes to the map
            document.getElementById('cbgm_previewLocation').setAttribute("style", "width:530px; height:300px; border:1px solid #8E8E8E; ", false);

            if ('Google' === mapProvider) {

                // Aktuelle Google-Maps-JS-API: Bibliotheken dynamisch laden und
                // AdvancedMarkerElement verwenden (google.maps.Marker ist abgekuendigt).
                google.maps.importLibrary('maps').then(function (mapsLibrary) {
                    return google.maps.importLibrary('marker').then(function (markerLibrary) {
                        var position = {lat: self.gmap.getLatitude(), lng: self.gmap.getLongitude()};
                        var map = new mapsLibrary.Map(document.getElementById('cbgm_previewLocation'), {
                            zoom: 15,
                            center: position,
                            mapId: CBM_GOOGLE_MAP_ID
                        });

                        var marker = new markerLibrary.AdvancedMarkerElement({
                            map: map,
                            position: position,
                            gmpDraggable: true,
                            title: ''
                        });

                        marker.addListener('dragend', function () {
                            // set new coordinates from marker to gmap instance
                            self.gmap.setLatitude(marker.position.lat());
                            self.gmap.setLongitude(marker.position.lng());

                            // update coordinates to the backend fields
                            self.setCoordinatesToBackend(self.gmap);
                        });
                    });
                }).catch(function () {
                    alert('Google Maps konnte nicht geladen werden - API-Key und Bibliotheken (maps/marker) pruefen.');
                });

            } else if ('MapBox' === mapProvider) {
                // display map by mapbox gl
                mapboxgl.accessToken = mapboxAccesstoken;

                this.mapboxmap = new mapboxgl.Map({
                    container: 'cbgm_previewLocation',
                    center: [this.gmap.getLongitude(),this.gmap.getLatitude()],    // longitude and latitude switched!
                    zoom: 15,
                    style: 'mapbox://styles/mapbox/streets-v12'
                }).addControl(new mapboxgl.NavigationControl());

                this.mapboxMarker = new mapboxgl.Marker({draggable: true})
                    .setLngLat([this.gmap.getLongitude(),this.gmap.getLatitude()]) // longitude and latitude switched!
                    .addTo(this.mapboxmap)
                    .on('dragend', function(e) {

                        // set new coordinates from marker to gmap instance
                        self.gmap.setLatitude( e.target._lngLat.lat );
                        self.gmap.setLongitude( e.target._lngLat.lng );

                        // update coordinates to the backend fields
                        self.setCoordinatesToBackend( self.gmap );
                    });

            } else {
                // display map by openstreetmap
                if (this.osmmap) {
                    // if map already exists, clear it
                    this.osmmap.off();
                    this.osmmap.remove();
                }

                // show openstreetmap map
                this.osmmap = L.map('cbgm_previewLocation', {
                    center: [this.gmap.getLatitude(), this.gmap.getLongitude()],
                    zoom: 15,
                    scrollWheelZoom: true
                });

                // add osm layer
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    attribution: '&copy; <a href="http://osm.org/copyright">OpenStreetMap</a> contributors, '
                    + '<a href="http://creativecommons.org/licenses/by-sa/2.0/">CC-BY-SA</a>'
                }).addTo(this.osmmap);

                // add marker
                L.marker([this.gmap.getLatitude(), this.gmap.getLongitude()],{draggable:true})
                    .addTo(this.osmmap)
                    .on('dragend', function(e) {

                        // set new coordinates from marker to gmap instance
                        self.gmap.setLatitude( e.target._latlng.lat );
                        self.gmap.setLongitude( e.target._latlng.lng );

                        // update coordinates to the backend fields
                        self.setCoordinatesToBackend( self.gmap );
                    });
            }
        } else {
            alert("Es sind noch keine Koordinaten hinterlegt - bitte zuerst über \"Ermittle Koordinaten\" die Adresse auflösen lassen.");
        }
    };



    /*
     * Display results in a new windows with coordinates and google maps preview
     * @param uid int
     * @param defaultZoom int
     * @param defaultMapType string
     * @param mapProvider string
     * @param mapboxAccesstoken string
     */
    this.displayPreview = function(uid, defaultZoom, defaultMapType, defaultMapControl, mapProvider, mapboxAccesstoken) {

        // verify existing gmap instance
        if (!this.gmap) {
            this.gmap = new Gmaps();
        }
        // set uid to the current gmap instance
        this.gmap.setUid(uid);
        // get coordinates
        this.getCoordinatesFromBackend(this.gmap);

        var infoText = '';
        var mapZoom = 10;
        var mapType = '';
        var mapControl = '';

        // fetch current inputs
        var fieldPrefix = "data[tt_content][" + this.gmap.getUid() + "][pi_flexform][data][s_displayproperties][lDEF]";

        infoText = document.getElementsByName(fieldPrefix + "[settings.cbgmDescription][vDEF]")[0].value;
        mapZoom = document.getElementsByName(fieldPrefix + "[settings.cbgmScaleLevel][vDEF]")[0].value;
        mapType = document.getElementsByName(fieldPrefix + "[settings.cbgmMapType][vDEF]")[0].value;
        mapControl = document.getElementsByName(fieldPrefix + "[settings.cbgmNavigationControl][vDEF]")[0].value;


        if (isNaN(mapZoom)) mapZoom = parseInt(defaultZoom);
        if ('' === mapType) mapType = defaultMapType;
        if ('' === mapControl) mapControl = defaultMapControl;

        // split infoText into rows
        var rowsInfoText = infoText.split("\n");

        // check if coordinates are given
        if (typeof(this.gmap.getLatitude()) === 'number' && typeof(this.gmap.getLongitude()) === 'number') {

            // set attributes to the map
            document.getElementById('cbgm_previewMap').setAttribute("style", "width:530px; height:300px; border:1px solid #8E8E8E; ", false);

            if ('Google' === mapProvider){
                // Aktuelle Google-Maps-JS-API: Bibliotheken dynamisch laden,
                // AdvancedMarkerElement statt des abgekuendigten google.maps.Marker.
                google.maps.importLibrary('maps').then(function (mapsLibrary) {
                    return google.maps.importLibrary('marker').then(function (markerLibrary) {
                        var position = {lat: self.gmap.getLatitude(), lng: self.gmap.getLongitude()};
                        var myOptions = {
                            zoom: mapZoom,
                            center: position,
                            mapId: CBM_GOOGLE_MAP_ID
                        };

                        // specify map type
                        switch (mapType) {
                            case 'ROADMAP':
                                myOptions.mapTypeId = mapsLibrary.MapTypeId.ROADMAP;
                                break;
                            case 'TERRAIN':
                                myOptions.mapTypeId = mapsLibrary.MapTypeId.TERRAIN;
                                break;
                            case 'SATELLITE':
                                myOptions.mapTypeId = mapsLibrary.MapTypeId.SATELLITE;
                                break;
                            default:
                                myOptions.mapTypeId = mapsLibrary.MapTypeId.HYBRID;
                        }

                        // Hinweis: eigene Navigations-Control-Styles gibt es in der
                        // aktuellen API nicht mehr - die Karte bringt ihre Controls selbst mit.

                        var map = new mapsLibrary.Map(document.getElementById('cbgm_previewMap'), myOptions);
                        var marker = new markerLibrary.AdvancedMarkerElement({
                            map: map,
                            position: position,
                            title: rowsInfoText[0] || ''
                        });

                        // set info window
                        if ('' !== infoText) {
                            infoText = infoText.replace(/\n/g, '<br>');
                            var infowindow = new mapsLibrary.InfoWindow({content: infoText});
                            marker.addListener('click', function () {
                                infowindow.open({map: map, anchor: marker});
                            });
                        }
                    });
                }).catch(function () {
                    alert('Google Maps konnte nicht geladen werden - API-Key und Bibliotheken (maps/marker) pruefen.');
                });

            } else if ('MapBox' === mapProvider) {
                // get map styling (aktuelle Mapbox-Styles, v3)
                switch (mapType) {
                    case "MapBox-BASIC":
                        mapType = 'mapbox://styles/mapbox/standard';
                        break;
                    case "MapBox-BRIGHT":
                        mapType = 'mapbox://styles/mapbox/streets-v12';
                        break;
                    case "MapBox-LIGHT":
                        mapType = 'mapbox://styles/mapbox/light-v11';
                        break;
                    case "MapBox-DARK":
                        mapType = 'mapbox://styles/mapbox/dark-v11';
                        break;
                    case "MapBox-SATELLITE":
                        mapType = 'mapbox://styles/mapbox/satellite-v9';
                        break;
                    default:
                        mapType = 'mapbox://styles/mapbox/streets-v12';
                }

                // create map by mapboy
                mapboxgl.accessToken = mapboxAccesstoken;

                this.mapboxmap = new mapboxgl.Map({
                    container: 'cbgm_previewMap',
                    center: [this.gmap.getLongitude(), this.gmap.getLatitude()],       // longitude and latitude switched!
                    zoom: mapZoom,
                    style: mapType
                }).addControl(new mapboxgl.NavigationControl());

                // add infotext popup if given
                if ('' !== infoText) {
                    this.mapboxPopup = new mapboxgl.Popup({closeOnClick: true, closeButton: false})
                        .setLngLat([this.gmap.getLongitude(), this.gmap.getLatitude()])
                        .setHTML(infoText.replace(/\+/g, ' ').replace(/\n/g, '<br/>'));
                }

                // add marker to the position
                this.mapboxMarker = new mapboxgl.Marker()
                    .setLngLat([this.gmap.getLongitude(), this.gmap.getLatitude()])    // longitude and latitude switched!
                    .addTo(this.mapboxmap);

                if (this.mapboxPopup) {
                    this.mapboxMarker.setPopup(this.mapboxPopup);
                }

            } else {
                // create map by openstreetmap
                if (this.osmmapPreview) {
                    this.osmmapPreview.off();
                    this.osmmapPreview.remove();
                }

                // show openstreetmap map
                this.osmmapPreview = L.map('cbgm_previewMap', {
                    center: [this.gmap.getLatitude(), this.gmap.getLongitude()],
                    zoom: mapZoom,
                    scrollWheelZoom: true
                });

                // add osm layer
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    attribution: '&copy; <a href="http://osm.org/copyright">OpenStreetMap</a> contributors, '
                    + '<a href="http://creativecommons.org/licenses/by-sa/2.0/">CC-BY-SA</a>'
                }).addTo(this.osmmapPreview);

                // add marker
                osmMarker = L.marker([this.gmap.getLatitude(), this.gmap.getLongitude()]).addTo(this.osmmapPreview);

                // add infotext
                if (infoText){
                    osmMarker.bindPopup( infoText.replace(/\+/g, ' ').replace(/\n/g, '<br/>') );
                }

            }

        } else {
            alert("Es sind noch keine Koordinaten hinterlegt - bitte zuerst über \"Ermittle Koordinaten\" die Adresse auflösen lassen.");
        }
    };



    /**
     * Get coordinates from TYPO3 backend fields and store them to local properties
     * @param  gmap Gmaps
     * @return void
     */
    this.getAddressFromBackend = function (gmap){

        // fetch current inputs
        var fieldPrefix = "data[tt_content][" + this.gmap.getUid() + "][pi_flexform][data][sDEF][lDEF]";

            gmap.setAddress(
                document.getElementsByName(fieldPrefix + "[settings.cbgmStreet][vDEF]")[0].value,
                document.getElementsByName(fieldPrefix + "[settings.cbgmZip][vDEF]")[0].value,
                document.getElementsByName(fieldPrefix + "[settings.cbgmCity][vDEF]")[0].value,
                document.getElementsByName(fieldPrefix + "[settings.cbgmCountry][vDEF]")[0].value);

        };



    /**
     * Set given coordinates to the backend fields
     *
     * TYPO3 14: jQuery wurde aus dem Backend entfernt - die alten
     * jQuery/TYPO3.jQuery-Zweige liefen ins Leere (bzw. warfen
     * "jQuery is not defined"), dadurch wurden die Koordinaten nie
     * in das Formular geschrieben.
     *
     * @param gmap Gmaps
     */
    this.setCoordinatesToBackend = function (gmap) {

        var fieldPrefix = "data[tt_content][" + gmap.getUid() + "][pi_flexform][data][sDEF][lDEF]";

        var latitudeStored = this.setFormEngineFieldValue(fieldPrefix + "[settings.cbgmLatitude][vDEF]", String(gmap.getLatitude()));
        var longitudeStored = this.setFormEngineFieldValue(fieldPrefix + "[settings.cbgmLongitude][vDEF]", String(gmap.getLongitude()));

        return latitudeStored && longitudeStored;
    };

    /**
     * Wert in ein FormEngine-Feld schreiben (sichtbares Feld + Hidden-Feld)
     * und die Events ausloesen, damit TYPO3 den Wert uebernimmt.
     *
     * @param fieldName string kompletter Feldname wie im Backend-Formular
     * @param value string
     * @return bool true, wenn mindestens ein Feld gefunden wurde
     */
    this.setFormEngineFieldValue = function (fieldName, value) {
        var fields = document.querySelectorAll(
            '[data-formengine-input-name="' + fieldName + '"], [name="' + fieldName + '"]'
        );

        for (var i = 0; i < fields.length; i++) {
            fields[i].value = value;
            fields[i].dispatchEvent(new Event('input', {bubbles: true}));
            fields[i].dispatchEvent(new Event('change', {bubbles: true}));
        }

        return fields.length > 0;
    };



    /**
     * Set coordinates from backend to the gmap instance
     * @param gmap Gmaps
     * @return void
     */
    this.getCoordinatesFromBackend = function(gmap){

        var fieldPrefix = "data[tt_content][" + gmap.getUid() + "][pi_flexform][data][sDEF][lDEF]";

            gmap.setLatitude( parseFloat(document.getElementsByName(fieldPrefix + "[settings.cbgmLatitude][vDEF]")[0].value) );
            gmap.setLongitude( parseFloat(document.getElementsByName(fieldPrefix + "[settings.cbgmLongitude][vDEF]")[0].value) );

    };

}


var cbGooglemaps = new Geocoding();

