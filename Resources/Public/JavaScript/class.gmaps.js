/*
 * Provides functionality to geocode, localize positions by using
 * google maps api v3 and display given geocoded postions in
 * google maps views.
 *
 * @package				class.gmaps
 * @version				1.0: gmaps,  03-2011
 * @copyright 			(c)2011 Christian Brinkert
 * @author 				Christian Brinkert <christian.brinkert@googlemail.com>
 */

function Gmaps(){
	// set self
	var self = this;

	// set private properties
	var street = null;
	var zip = null;
	var city = null;
	var country = null;

	// initialize defauls
	var lat = null;
	var lng = null;
	
	// current uid
	var uid = null;

	// current map provider
	var mapProvider = null;



	// helper methods
	function trim(givenString){
		return givenString.replace (/^\s+/, '').replace (/\s+$/, '');
	}



	// getter & setter
	this.setStreet = function(streetString){
		if (typeof(streetString) === 'string' && '' !== streetString)
			self.street = streetString;
	};
	this.getStreet = function(){
		return self.street;
	};


	this.setZip = function(zipCode){
		if (typeof(zipCode) === 'string' && '' !== zipCode)
			self.zip = zipCode;
	};
	this.getZip = function(){
		return self.zip;
	};


	this.setCity = function(cityString){
		if (typeof(cityString) === 'string' && '' !== cityString)
			self.city = cityString;
	};
	this.getCity = function(){
		return self.city;
	};


	this.setCountry = function(countryString){
		if (typeof(countryString) === 'string' && '' !== countryString)
			self.country = countryString;
	};
	this.getCountry = function(){
		return self.country;
	};


	this.setLatitude = function(latitude){
		if (typeof(latitude) === 'number')
			self.lat = latitude;
	};
	this.getLatitude = function(){
		return self.lat;
	};


	this.setLongitude = function(longitude){
		if (typeof(longitude) === 'number')
			self.lng = longitude;
	};
	this.getLongitude = function(){
		return self.lng;
	};


	this.setUid = function(uid){
		self.uid = uid;
	};
	this.getUid = function(){
		return self.uid;
	};


	this.setMapProvider = function(mapProvider){
		self.mapProvider = mapProvider;
	};
	this.getMapProvider = function(){
		return self.mapProvider;
	};


    /**
	 * Fetch coordinates from mapProvider
	 * @param callback object
	 * @param uid int
	 * @param mapProvider string
	 * @param mapboxAccesstoken string
     */
	this.fetchCoordinatesByAddress = function(callback, mapProvider, mapboxAccesstoken){
        // set mapProvider
		self.setMapProvider(mapProvider);

		// if google is current map provider, ask google for localization
		if ('Google' === self.getMapProvider()) {
            var address = this.getAddressAsString();
            if ('' === trim(address)) {
                alert('Bitte zuerst eine Adresse angeben (Straße, PLZ/Ort oder Land).');
                return;
            }

            if (!window.google || !google.maps || typeof google.maps.importLibrary !== 'function') {
                alert('Google Maps ist nicht geladen - API-Key/Bibliotheken pruefen oder als Kartenanbieter OpenStreetMap waehlen.');
                return;
            }

            // Aktuelle Google-Maps-JS-API: Geocoding-Bibliothek dynamisch laden.
            // Der Status ist ein String ("OK") - google.maps.GeocoderStatus gibt es nicht mehr.
            google.maps.importLibrary('geocoding').then(function (geocodingLibrary) {
                var geocoder = new geocodingLibrary.Geocoder();

                geocoder.geocode({address: address}, function (results, status) {
                    if (status === 'OK' && results && results.length > 0) {
                        self.setLatitude(results[0].geometry.location.lat());
                        self.setLongitude(results[0].geometry.location.lng());
                        // return values to callback method
                        callback(self);
                        return;
                    }

                    alert('Die angegebene Adresse konnte nicht gefunden werden (Google: ' + status + ').');
                });
            }).catch(function () {
                alert('Die Google-Geocoding-Bibliothek konnte nicht geladen werden.');
            });

        } else if ('MapBox' === self.getMapProvider()) {
            //https://api.mapbox.com/geocoding/v5/mapbox.places/Los%20Angeles.json?access_token=your-access-token
			// if mapbox is current map provider
			var xhr = new XMLHttpRequest();
			var searchUri = 'https://api.mapbox.com/geocoding/v5/mapbox.places/'
						  +	encodeURI(this.getAddressAsString()) +'.json?limit=1&language=de&access_token='+ mapboxAccesstoken;

            xhr.open('GET', searchUri );
            xhr.onload = function () {
                // parse result string to json object
                results = JSON.parse(xhr.response);

                if (results.features && 0 < results.features.length){
                    self.setLatitude(parseFloat(results.features[0].geometry.coordinates[1]));
                    self.setLongitude(parseFloat(results.features[0].geometry.coordinates[0]));
                    // return values to callback method
                    callback(self);
                } else {
                    alert('Die angegebene Adresse konnte nicht gefunden werden.');
                }
            };
            xhr.onerror = function () {
                alert('Der Geocoding-Dienst ist nicht erreichbar.');
            };
            xhr.send();

		} else {
			// OpenStreetMap: Nominatim (kostenlos, kein API-Key noetig)
            var xhr = new XMLHttpRequest();
            var address = this.getAddressAsString();

            if ('' === trim(address)) {
                alert('Bitte zuerst eine Adresse angeben (Straße, PLZ/Ort oder Land).');
                return;
            }

            xhr.open('GET', 'https://nominatim.openstreetmap.org/search'
                + '?format=json&limit=1&accept-language=de&q=' + encodeURI(address));
            xhr.onload = function () {
                if (xhr.status < 200 || xhr.status >= 300) {
                    alert('Der Geocoding-Dienst (Nominatim/OpenStreetMap) antwortet nicht (HTTP ' + xhr.status + ').');
                    return;
                }
            	// parse result string to json object
                var results = JSON.parse(xhr.response);

            	if (0 < results.length){
                    self.setLatitude(parseFloat(results[0].lat));
                    self.setLongitude(parseFloat(results[0].lon));
                    // return values to callback method
                    callback(self);
				} else {
            		alert('Die angegebene Adresse konnte nicht gefunden werden:\n"' + address + '"');
				}
            };
            xhr.onerror = function () {
                alert('Der Geocoding-Dienst (Nominatim/OpenStreetMap) ist nicht erreichbar.');
            };
            xhr.send();
		}
	};


	// set location by one method
	this.setAddress = function(street, zip, city, country){
		self.setStreet(street);
		self.setZip(zip);
		self.setCity(city);
		self.setCountry(country);
	};

	// return complete location as comma separated string
	this.getAddressAsString = function(){
		var address = [];
		if(null != self.getStreet())
			address.push(self.getStreet());
		if(null != self.getZip() && null != self.getCity()){
			address.push(trim(self.getZip() +" "+ self.getCity()));
		}else if(null != self.getZip()){
			address.push(self.getZip());
		}else if(null != self.getCity()){
			address.push(self.getCity());
		}
		if(null != self.getCountry())
			address.push(self.getCountry());

		// return array as string
		return address.join(", ");
	};

	// set coordinates by one call
	this.setCoordinates = function(lat, lng){
		self.lat = latitude;
		self.lng = longitude;
	};

}