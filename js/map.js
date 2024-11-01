// jshint esversion: 10

// Keep this out of global scope
jQuery(document).ready(function () {

    /**
     * Since singleton classes don't really work in JS, we're just using a variable
     * with properties and functions. This acts like a Singleton.
     *
     * This class's purpose is to handle user interaction with a map
     * of property listings on the page. There should be a div on the page
     * with ID vsta-listing-map, which the map will be displayed inside.
     * @author Nate Lanza
     * @type {Object}
     */
    var MapManager = {

        /**
         * Stores the Leaflet map object
         * @type {L.Map}
         */
        map: null,

        /**
         * All listings displayed on the map, as Listing objects
         * @type {array}
         */
        listings: null,

        /**
         * Whether this object's init function has been called. If false, no other functions can run
         * @type {Boolean}
         */
        initialized: false,

        /**
         * @throws Error if init has not been called
         * @return {void}
         */
        checkInit: function () {
            if (!this.initialized)
                throw new Error("Must call init first");
        },

        /**
         * Gets listings to be displayed on the map
         * and stores them in the listings field
         * Currently retrieves listings from a custom element with id 'vsta-listing-data'
         */
        getListings: function () {
            this.checkInit();
            this.listings = JSON.parse(document.getElementById("vsta-listing-data").dataset.listings);
        },

        /**
         * Retrieves a listing by ID
         * @param  {int} ID ID of the listing to retrieve
         * @return {Object || bool}    Listing object, or false if not found
         */
        getListing: function (ID) {
            this.checkInit();
            for (var listing of this.listings)
                if (listing.listingid == ID)
                    return listing;

            return false;
        },

        /**
         * Fires when a map marker is clicked
         * Because JS is weird, 'this' refers to the marker object when markerClicked is called from populateMap
         * @param Event e The mouseclick event
         * @return {void}
         */
        markerClicked: function (e) {
            // Get listing
            var listing = MapManager.getListing(this.ID);
            if (!listing)
                throw new Error("Listing " + this.ID + " not found");

            // Populate display spans
            jQuery('.vsta-map-field').each(function (i, obj) {
                var fieldName = jQuery(this).attr('id').slice(14).toLowerCase();
                if (fieldName in listing)
                    jQuery(this).html(listing[fieldName]);
                else
                    jQuery(this).html("Field not found");
            });
            // Make sure the div is shown
            jQuery('#vsta-listing-map-info').show();
        },

        /**
         * Places a marker on the map for each listing to be displayed
         * Fields map and listings should both be initialized
         * @return {void}
         */
        populateMap: function () {
            this.checkInit();
            if (this.listings == null)
                throw new Error("getListings must be called before populateMap");

            // Add each listing to map
            for (var listing of this.listings) {
                // Create marker
                var marker = new L.Marker(
                    new L.LatLng(listing.lat, listing.lng)
                );
                marker.ID = listing.listingid;
                // Add to map & add onclick action
                marker.addTo(this.map).on('click', this.markerClicked);
            }

            // Center map on first listing
            this.map.setView([this.listings[0].lat, this.listings[0].lng], 10);
        },

        /**
         * Initializes the Map field, retrieves listings from a global variable,
         * fills the listings field, and populates the map with listing markers.
         * Must be executed before any other functions in this 'class',
         * otherwise an error will be thrown
         * @return {void}
         */
        init: function () {
            // Check init
            if (this.initialized)
                return;

            // Init map
            this.map = L.map('vsta-listing-map', {
                center: [43.61031, -116.20153],
                zoom: 8,
                // Canvas doesn't work on IE,
                // but the other option, SVG, doesn't work on Android
                preferCanvas: true,
                attributionControl: true,
            });

            // Create map background
            L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {
                attribution: '&copy; <a href="http://openstreetmap.org">OpenStreetMap</a> contributors',
                maxZoom: 18,
            }).addTo(this.map);

            // Center map
            this.map.setView([43.61031, -116.20153], 8);

            this.initialized = true;

            // Init listings
            this.getListings();
            // Populate map
            this.populateMap();
        }
    };

    // Code to run when page is loaded
    MapManager.init();

});
