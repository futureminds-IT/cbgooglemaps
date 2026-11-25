addEventListener("DOMContentLoaded", () => {
    /*
     * Das Formelement liefert den Mapbox-Token als data-accestoken.
     * Aeltere Staende (GeoCodingButtonElement) schrieben data-token - beides
     * auslesen, sonst bekommt Mapbox "undefined" als Token und rendert nichts.
     */
    const mapboxTokenOf = (element) => element.dataset.accestoken || element.dataset.token || '';
  
    // Button Map Preview
    document.getElementById("mappreview").addEventListener('click', function() { 
        const parameters = document.querySelector("#mappreview");       
        cbGooglemaps.displayPreview(parameters.dataset.vanillauid,parameters.dataset.zoom,parameters.dataset.maptype,parameters.dataset.navcontrol,parameters.dataset.mapprovider,mapboxTokenOf(parameters));        
    }, false);
    
    // Button Display Location
    document.getElementById("dodisplaylocation").addEventListener('click', function() { 
        const parameters = document.querySelector("#dodisplaylocation");       
        cbGooglemaps.displayLocation(parameters.dataset.vanillauid,parameters.dataset.mapprovider,mapboxTokenOf(parameters));        
    }, false);
    
    // Fetch Address
    document.getElementById("dogeocoding").addEventListener('click', function() { 
        const parameters = document.querySelector("#dogeocoding");       
        cbGooglemaps.doGeocoding(parameters.dataset.vanillauid,parameters.dataset.mapprovider,mapboxTokenOf(parameters));        
    }, false);
    
});




