(function (Q, $, window, document, undefined) {

	/**
	 * Places Tools
	 * @module Places-tools
	 */

	var Users = Q.Users;
	var Streams = Q.Streams;
	var Places = Q.Places;

	Q.setObject("Q.text.Places.Location", {
		myCurrentLocation: "My current location",
		enterAddress: "Enter an address",
		create: {
			action: "Add Location",
			title: "New Location",
			nameYourLocation: "Name your location"
		},
		confirm: {
			title: "Save this location?",
			message: "Do you plan to use this location again in the future?",
			ok: 'Yes',
			cancel: 'No'
		},
		add: {
			title: "Name this location",
			placeholder: "Home, Work etc.",
			ok: "Save"
		}
	});
	Q.setObject("Q.text_en.Places.Location", Q.text.Places.Location);

	/**
	 * Allows the logged-in user to add his own locations and select among these locations
	 * @class Places location
	 * @constructor
	 * @param {Object} [options] used to pass options
	 * @param {Object} [options.geocode] Default google location object, if available
	 * @param {Boolean} [options.useRelatedLocations] Whether to show related locations
	 * @param {Object} [options.location] Currently selected location
	 * @param {Q.Event} [options.onChoose] this event occurs when user selected some valid location
	 */

	Q.Tool.define("Places/location", function (options) {
			var tool = this;
			var state = this.state;
			var $te = $(tool.element);

			Q.addStylesheet('plugins/Places/css/location.css');

			// change location event
			$te.on(Q.Pointer.click, "[data-location], .Places_location_preview_tool", function () {
				var $this = $(this);

				if ($this.hasClass('Q_selected')) {
					return false;
				}

				// set onChoose status to null
				Q.handle(state.onChoose, tool, [null]);

				// toggle Q_selected class
				$te.find(".Q_selected").removeClass("Q_selected");
				$this.addClass('Q_selected');
				
				var $olt = $this.find(tool.otherLocationTool.element);
				if ($olt.length) {
					$olt.plugin('Q/placeholders', function () {
						tool.otherLocationTool.filter.setText('');
						tool.otherLocationTool.filter.$input.plugin('Q/clickfocus');
					});
				}

				var selector = $this.attr("data-location");
				if (selector == 'current') {
					tool.getCurrentPosition(function (pos) {
						var crd = pos.coords;

						// something wrong
						if (!crd) {
							Q.alert("Places/location tool: could not obtain location", pos);
							return false;
						}

						// get valid google object and fire onChoose event
						tool.geocode({
							latitude: crd.latitude,
							longitude: crd.longitude
						}, function (geocode) {
							Q.handle(state.onChoose, tool, [geocode]);
						});
					}, function (err) {
						Q.alert("Places/location tool: ERROR(" + err.code + "): " + err.message);
						return false;
					});

					return;
				} else if (selector == 'other') {
					// if "other location" selected just repeat onChoose event of places/address tool
					Q.handle(
						tool.otherLocationTool.state.onChoose, 
						tool.otherLocationTool, 
						[tool.otherLocationTool.place]
					);
					return;
				}

				// related location selected
				var locationPreviewTool = Q.Tool.from($this, "Streams/preview");
				var ls = locationPreviewTool.state;
				Streams.get(ls.publisherId, ls.streamName, function () {
					// get valid google object and fire onChoose event
					tool.geocode(this, function (geocode) {
						Q.handle(state.onChoose, tool, [geocode]);
					});
				});
			});

			tool.refresh();
		},

		{ // default options here
			geocode: null,
			onChoose: new Q.Event(function (geocode) {
				this.state.location = geocode;
			}, 'Places/location'),
			location: null, // currently selected location
			useRelatedLocations: true
		},

		{ // methods go here
			/**
			 * Refresh the display
			 * @method refresh
			 */
			refresh: function () {
				var tool = this;
				var state = tool.state;
				var $te = $(tool.element);
				var userId = Users.loggedInUser.id;

				// location already set in state - just type it
				if (state.geocode) {
					if (!state.geocode.formatted_address) {
						Q.alert("Places/location tool: wrong geocode", state.geocode);
						return false;
					}

					$te.html(state.geocode.formatted_address).addClass("Q_selected");
					Q.handle(state.onChoose, tool, [state.geocode.geometry.location]);

					return;
				}

				Q.Template.render('Places/location/select', {
					text: Q.text.Places.Location
				}, function (err, html) {
					$te.html(html).activate(function () {

						// set otherLocation address tool
						tool.$(".Places_location_otherLocation")
						.tool('Places/address', {
							onChoose: _onChoose
						}, 'otherLocation', tool.prefix)
						.activate(function () {
							tool.otherLocationTool = this;
						});

						// set related locations if state.
						if (state.useRelatedLocations && userId) {
							tool.$(".Places_location_related")
							.tool('Streams/related', {
								publisherId: userId,
								streamName: 'Places/user/locations',
								relationType: 'Places/locations',
								isCategory: true
							}, tool.prefix + 'relatedLocations')
							.activate(function () {
								tool.relatedTool = this;
							});
						}
						
						function _onChoose(place) {
							if (!place || !place.id) {
								return Q.handle(state.onChoose, tool, [null]);
							}
							// get valid google object and fire onChoose event
							tool.geocode({placeId: place.id}, function (l) {
								var c = Q.text.Places.Location.confirm;
								Q.confirm(c.message, function (shouldSave) {
									if (!shouldSave) {
										return;
									}
									var a = Q.text.Places.Location.add;
									Q.prompt(a.prompt, function (title) {
										if (!title) {
											return;
										}
										Streams.create({
											type: 'Places/location',
											title: title,
											attributes: {
												latitude: l.lat(),
												longitude: l.lng()
											},
											readLevel: 0,
											writeLevel: 0,
											adminLevel: 0
										}, function (err) {
											if (!err) {
												tool.relatedTool.refresh();
											}
										}, {
											publisherId: userId,
											streamName: 'Places/user/locations',
											type: 'Places/locations'
										});
									}, {
										title: a.title,
										placeholder: a.placeholder,
										ok: a.ok
									});
								}, {
									title: c.title,
									ok: c.ok,
									cancel: c.cancel
								});
								Q.handle(state.onChoose, tool, [l]);
							});
						}
					});
				}, {tool: tool});
			},
			/**
			 * Get current geolocation
			 * @method getCurrentPosition
			 * @param {function} [success] Callback for success
			 * @param {function} [fail] Callback for fail
			 */
			getCurrentPosition: function (success, fail) {
				var tool = this;

				navigator.geolocation.getCurrentPosition(function (pos) {
					Q.handle(success, tool, [pos]);
				}, function (err) {
					Q.handle(fail, tool, [err]);
					console.warn("Places.location.getCurrentPosition: ERROR(" + err.code + "): " + err.message);
				}, {
					enableHighAccuracy: true, // need to set true to make it work consistently, it doesn't seem to make it any more accurate
					timeout: 5000,
					maximumAge: 0
				});
			},
			/**
			 * Obtain geocoding definition from a geocoding service
			 * @method geocode
			 * @static
			 * @param {Object} loc Provide a Places/location stream, or an object with either a "placeId" property, a pair of "latitude","longitude" properties, an "address" property for reverse geocoding, or a pair of "userId" and optional "streamName" (which otherwise defaults to "Places/user/location")
			 * @param {Function} callback gets (array of results of the geolocation, and status code)
			 */
			geocode: function (loc, callback) {
				var tool = this;

				if (loc.latitude || loc.longitude) { // if known latitude, longitude - calculate google location local
					if (!loc.latitude) {
						throw new Q.Error(p + "missing latitude");
					}
					if (!loc.longitude) {
						throw new Q.Error(p + "missing longitude");
					}

					// localy calculate if known lat and lng, to avoid requests to google api (danger of OVER_QUERY_LIMIT !!!)
					var latlng = new google.maps.LatLng(
						parseFloat(loc.latitude),
						parseFloat(loc.longitude)
					);
					Q.handle(callback, Places.Location, [latlng, 'OK']);
					return;
				} else if (typeof loc.lat == 'function' && typeof loc.lng == 'function') { // loc - already google location object
					Q.handle(callback, Places.Location, [loc, 'OK']);
					return;
				}else if(loc.geometry && loc.geometry.location){ // we have standard google location object - return just location
					Q.handle(callback, Places.Location, [loc.geometry.location, 'OK']);
					return;
				}

				// for other loc - call Places plugin
				Places.Location.geocode(loc, function(geocode){
					if(geocode.geometry && geocode.geometry.location){ geocode = geocode.geometry.location; }
					Q.handle(callback, tool, [geocode]);
				});
			}
		}
	);

	Q.Template.set('Places/location/select',
		'<div data-location="current">{{text.myCurrentLocation}}</div>' +
		'<div class="Places_location_related"></div>' +
		'<div data-location="other"><label>{{text.enterAddress}}</label><div class="Places_location_otherLocation"></div></div>'
	);

	Q.Template.set("Places/location/new",
		'<div class="Places_location_new">' +
		'	<div class="Places_location_new_title"><input placeholder="{{text.nameYourLocation}}" name="title"></div>' +
		'	<div class="Places_location_new_select"></div>' +
		'	<div class="Places_location_new_actions"><button class="Q_button" name="submit">{{text.action}}</button></div>' +
		'</div>'
	);

})(Q, jQuery, window, document);