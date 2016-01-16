require.config({
    baseUrl : (( typeof window.JSConst === 'object') ? window.JSConst.absRefPrefix : '/') + 'src/js'
});
require(['config'], function() {
    require(['jquery'], function($) {
        
        var currentHighlights = new Array();
        var currentHighlightHomeCountry = "";

        function openMap() {
            var url = "map.php";
            var optionen = "toolbar=no,location=no,directories=no,status=no,menubar=no,";
            optionen += "scrollbars=yes,resizable=yes,left=200,top=100";
            open(url, "",optionen);
        }

        function openFlashMap() {
            var url = "./flashmap/AmfPlay.html";
            var optionen = "toolbar=no,location=no,directories=no,status=no,menubar=no,";
            optionen += "scrollbars=yes,resizable=yes,left=200,top=100";
            open(url, "",optionen);
        }

        function HighlightSingleCountry(country) {
            var found = false,
                infobox;
            
            for (var x = 0; x < currentHighlights.length; x++) {
                if (currentHighlights[x] == country) {
                    found = true;
                    break;
                }
            }
            
            if (!found) {
                document.getElementById(country).style.opacity = "1";
                infobox = "infobox_" + country;
                document.getElementById(infobox).style.display = "block";
            } else {
                infobox = "infobox_" + country;
                document.getElementById(infobox).style.display = "block";
            }
        }

        function ClearHighlightSingleCountry(country) {
            var found = false,
                infobox;
            
            for (var x = 0; x < currentHighlights.length; x++) {
                if (currentHighlights[x] == country) {
                    found = true;
                    break;
                }
            }
            
            if (!found) {		
                document.getElementById(country).style.opacity = "";
                infobox = "infobox_" + country;
                document.getElementById(infobox).style.display = "";
            } else {
                infobox = "infobox_" + country;
                document.getElementById(infobox).style.display = "";
            }
        }

        function HighlightMultipleCountries(homeCountry, countries) {
            ClearHighlightMultipleCountries();
            
            if (homeCountry != currentHighlightHomeCountry) {		
                currentHighlightHomeCountry = homeCountry;
                for (var x = 0; x < countries.length; x++) {
                    document.getElementById(countries[x]).style.opacity = "1";
                    currentHighlights.push(countries[x]);
                }
                var infobox_float = "infobox_float_" + homeCountry;
                document.getElementById(infobox_float).style.display = "block";
            } else {
                currentHighlightHomeCountry = "";
            }
        }

        function ClearHighlightMultipleCountries() {
            if (currentHighlights.length != 0) {
                for (var x = 0; x < currentHighlights.length; x++) {
                    document.getElementById(currentHighlights[x]).style.opacity = "";
                }
                currentHighlights = new Array();
                var infobox_float = "infobox_float_" + currentHighlightHomeCountry;
                document.getElementById(infobox_float).style.display = "none";
            }
        }

        function toggleResources(maxcountry) {
            clearDisplay(maxcountry);
            for (var x = 1; x <= maxcountry; x++) {
                var resourcediv = "resource_" + x;
                if (document.getElementById(resourcediv)) {
                    document.getElementById(resourcediv).style.display = "block";
                }
            }
        }

        function toggleTank(maxcountry) {
            clearDisplay(maxcountry);
            for (var x = 1; x <= maxcountry; x++) {
                var tankdiv = "tank_" + x;
                if (document.getElementById(tankdiv)) {
                    document.getElementById(tankdiv).style.display = "block";
                }
            }
        }

        function toggleTraderoutes(maxcountry) {
            clearDisplay(maxcountry);
            for (var x = 1; x <= maxcountry; x++) {
                var traderoutediv = "traderoute_" + x;
                if (document.getElementById(traderoutediv)) {
                    document.getElementById(traderoutediv).style.display = "block";
                }
            }
        }

        function toggleLandUnits(maxcountry) {
            clearDisplay(maxcountry);
            for (var x = 1; x <= maxcountry; x++) {
                var landunitsdiv = "landunit_" + x;
                if (document.getElementById(landunitsdiv)) {
                    document.getElementById(landunitsdiv).style.display = "block";
                }
            }
        }

        function toggleSeaUnits(maxcountry) {
            clearDisplay(maxcountry);
            for (var x = 1; x <= maxcountry; x++) {
                var seaunitsdiv = "seaunit_" + x;
                if (document.getElementById(seaunitsdiv)) {
                    document.getElementById(seaunitsdiv).style.display = "block";
                }
            }
        }

        function clearDisplay(maxcountry) {
            var seaunitsdiv;
            for(var x = 1; x <= maxcountry; x++) {
                seaunitsdiv = "seaunit_" + x;
                if (document.getElementById(seaunitsdiv)) {
                    document.getElementById(seaunitsdiv).style.display = "none";
                }
                seaunitsdiv = "traderoute_" + x;
                if (document.getElementById(seaunitsdiv)) {
                    document.getElementById(seaunitsdiv).style.display = "none";
                }
                var landunitsdiv = "landunit_" + x;
                if (document.getElementById(landunitsdiv)) {
                    document.getElementById(landunitsdiv).style.display = "none";
                }
                var resourcediv = "resource_" + x;
                if (document.getElementById(resourcediv)) {
                    document.getElementById(resourcediv).style.display = "none";
                }
                var tankdiv = "tank_" + x;
                if (document.getElementById(tankdiv)) {
                    document.getElementById(tankdiv).style.display = "none";
                }
            }
        }

        // Das Objekt, das gerade bewegt wird.
        var dragobjekt = null;

        // Position, an der das Objekt angeklickt wurde.
        var dragx = 0;
        var dragy = 0;

        // Mausposition
        var posx = 0;
        var posy = 0;

        function draginit() {
            // Initialisierung der überwachung der Events
            document.onmousemove = drag;
            document.onmouseup = dragstop;
        }

        function dragstart(element) {
            // Wird aufgerufen, wenn ein Objekt bewegt werden soll.
            dragobjekt = element;
            dragx = posx - dragobjekt.offsetLeft;
            dragy = posy - dragobjekt.offsetTop;
        }

        function dragstop() {
            // Wird aufgerufen, wenn ein Objekt nicht mehr bewegt werden soll.
            dragobjekt = null;
        }

        function drag(ereignis) {
            // Wird aufgerufen, wenn die Maus bewegt wird und bewegt bei Bedarf das Objekt.
            posx = document.all ? window.event.clientX : ereignis.pageX;
            posy = document.all ? window.event.clientY : ereignis.pageY;
            if (dragobjekt != null) {
                dragobjekt.style.left = (posx - dragx) + "px";
                dragobjekt.style.top = (posy - dragy) + "px";
            }
        }

    });
});
