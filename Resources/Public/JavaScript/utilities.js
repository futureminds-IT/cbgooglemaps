addEventListener("DOMContentLoaded", () => {  
    // Button Map Preview
    document.getElementById("mappreview").addEventListener('click', function() { 
        const parameters = document.querySelector("#mappreview");       
        cbGooglemaps.displayPreview(parameters.dataset.vanillauid,parameters.dataset.zoom,parameters.dataset.maptype,parameters.dataset.navcontrol,parameters.dataset.mapprovider,parameters.dataset.accestoken);        
    }, false);
    
    // Button Display Location
    document.getElementById("dodisplaylocation").addEventListener('click', function() { 
        const parameters = document.querySelector("#dodisplaylocation");       
        cbGooglemaps.displayLocation(parameters.dataset.vanillauid,parameters.dataset.mapprovider,parameters.dataset.accestoken);        
    }, false);
    
    // Fetch Address
    document.getElementById("dogeocoding").addEventListener('click', function() { 
        const parameters = document.querySelector("#dogeocoding");       
        cbGooglemaps.doGeocoding(parameters.dataset.vanillauid,parameters.dataset.mapprovider,parameters.dataset.accestoken);        
    }, false);
    
});




