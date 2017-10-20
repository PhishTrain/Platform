"use strict";
(function (Q, $) {

	var Users = Q.plugins.Users;

	Q.onReady.add(function () {
		if (Q.info.isCordova && (window.FCMPlugin || window.PushNotification)) {
			var appId = location.search.queryField('Q.Users.appId');
			localStorage.setItem("Q\tUsers.Device.appId", appId);
		}
		Users.Device.init(function () {
			console.log('Device adapter init: ' + Users.Device.adapter.adapterName);
		});
	}, 'Users.Device');

	/**
	 * Some methods related to Users.Device
	 * @class Users.Device
	 */
	Users.Device = {
		/**
		 * Subscribe to listen for push notifications
		 * if the current environment supports it.
		 * (Web Push, Cordova, etc.)
		 * @method subscribe
		 * @static
		 * @param {Function} callback
		 * @param {Object} options
		 * @param {Boolean} options.userVisibleOnly whether the returned push subscription
		 *   will only be used for messages whose effect is made visible to the user
		 * @param {String} options.applicationServerKey A public key your push server
		 *   will use to send messages to client apps via a push server. This value is
		 *   part of a signing key pair generated by your application server, and usable
		 *   with elliptic curve digital signature (ECDSA), over the P-256 curve.
		 */
		subscribe: function (callback, options) {
			this.getAdapter(function(adapter, err) {
				if (err) {
					callback(null, err);
				} else {
					adapter.subscribe(function(subscribed, err){
						callback(subscribed, err);
					}, options);
				}
			});
		},

		/**
		 * Unsubscribe to stop handling push notifications
		 * if we were previously subscribed
		 * @method unsubscribe
		 * @static
		 * @param {Function} callback
		 */
		unsubscribe: function (callback) {
			this.getAdapter(function(adapter, err) {
				if (err) {
					callback(null, err);
				} else {
					adapter.unsubscribe(function(subscribed, err){
						callback(subscribed, err);
					});
				}
			});
		},

		/**
		 * Checks whether the user already has a subscription.
		 * @method subscribed
		 * @static
		 * @param {Boolean} callback Whether the user already has a subscription
		 */
		subscribed: function (callback) {
			this.getAdapter(function(adapter, err) {
				if (err) {
					callback(null, err);
				} else {
					adapter.subscribed(function(subscribed, err){
						callback(subscribed, err);
					});
				}
			})
		},

		/**
		 * Event occurs when a notification comes in to be processed by the app.
		 * The handlers you add are supposed to process it.
		 * The notification might have brought the app back from the background,
		 * or not. Please see the documentation here:
		 * https://github.com/katzer/cordova-plugin-local-notifications
		 * @event onNotification
		 */
		onNotification: new Q.Event(),

		init: function (callback) {
			if (Q.info.isCordova && window.FCMPlugin) {
				// FCM adapter
				this.adapter = adapterFCM;
			} else if (Q.info.isCordova && window.PushNotification) {
				// PushNotification adapter
				this.adapter = adapterPushNotification;
			} else if ((Q.info.browser.name === 'chrome') || (Q.info.browser.name === 'firefox')) {
				// Chrome and Firefox
				this.adapter = adapterWeb;
			} else if (Q.info.browser.name === 'safari') {
				// TODO implement adapter for Safari Browser
			}
			if (this.adapter) {
				this.adapter.init(callback);
			}
		},

		getAdapter: function(callback) {
			if (!this.adapter) {
				callback(null, new Error('There is no suitable adapter for this type of device'));
				return;
			}
			callback(this.adapter);
		},

		adapter: null

	};

	// Adapter for Chrome and Firefox
	var adapterWeb = {

		adapterName: 'Web',

		init: function () {
			this.appConfig = Q.getObject('Q.Users.browserApps.' + Q.info.browser.name + '.' + Q.info.app);
		},

		subscribe: function (callback, options) {
			var self = this;
			this.getServiceWorkerRegistration(function(sw, err) {
				if (err)
					callback(null, err);
				else {
					var userVisibleOnly = true;
					if (options && !options.userVisibleOnly) {
						userVisibleOnly = false;
					}
					sw.pushManager.subscribe({
						userVisibleOnly: userVisibleOnly,
						applicationServerKey: _urlB64ToUint8Array(self.appConfig.publicKey)
					}).then(function (subscription) {
						_saveSubscription(subscription, self.appConfig);
						if (callback) {
							callback(subscription);
						}
					}).catch(function (err) {
						if (Notification.permission === 'denied') {
							console.warn('Permission for Notifications was denied');
						} else {
							console.warn('Unable to subscribe to push.', err);
						}
						if (callback) {
							callback(null, err);
						}
					});
				}
			});
		},

		unsubscribe: function (callback) {
			this.getServiceWorkerRegistration(function(sw, err) {
				if (err)
					callback(null, err);
				else {
					sw.pushManager.getSubscription()
						.then(function (subscription) {
							if (subscription) {
								_deleteSubscription(subscription.endpoint);
								return subscription.unsubscribe();
							}
						})
						.catch(function (error) {
							console.log('Error while unsubscribing', error);
							if (callback) {
								callback(false, error);
							}
						})
						.then(function () {
							console.log('User is unsubscribed.');
							if (callback) {
								callback(true);
							}
						});
				}
			});
		},

		subscribed: function (callback) {
			this.getServiceWorkerRegistration(function(sw, err) {
				if (err)
					callback(null, err);
				else {
					sw.pushManager.getSubscription()
						.then(function (subscription) {
							callback(!(subscription === null));
						}).catch(function(err) {
							callback(null, err);
						});
				}
			});
		},

		getServiceWorkerRegistration: function(callback) {
			var self = this;
			if (this.serviceWorkerRegistration) {
				return callback(this.serviceWorkerRegistration);
			}
			_registerServiceWorker.bind(this)(function(sw, err) {
				if (err)
					return callback(null, err);
				else {
					self.serviceWorkerRegistration = sw;
					return callback(sw);
				}
			});
		},

		serviceWorkerRegistration: null,

		appConfig: null

	};

	// Adapter for FCM
	var adapterFCM = {

		adapterName: 'FCM',

		init: function (callback) {
			this.push = _FCMInit();
			if (callback)
				callback();
		},

		subscribe: function (callback) {
			this.push = _FCMInit(true);
			if (callback)
				callback();
		},

		unsubscribe: function (callback) {
			var deviceId = localStorage.getItem("Q\tUsers.Device.deviceId");
			localStorage.removeItem("Q\tUsers.Device.deviceId");
			_deleteSubscription(deviceId);
			if (callback)
				callback();
		},

		subscribed: function (callback) {
			var storedDeviceId = localStorage.getItem("Q\tUsers.Device.deviceId");
			if (storedDeviceId) {
				callback(true);
			} else {
				callback(false);
			}
		},

		serviceWorkerRegistration: null,

		appConfig: null
	};

	// Adapter for PushNotification
	var adapterPushNotification = {

		adapterName: 'PushNotification',

		init: function (callback) {
			_PushNotificationInit();
			if (callback)
				callback();
		},

		subscribe: function (callback) {
			this.push = _PushNotificationInit(true);
			if (callback)
				callback();
		},

		unsubscribe: function (callback) {
			var deviceId = localStorage.getItem("Q\tUsers.Device.deviceId");
			localStorage.removeItem("Q\tUsers.Device.deviceId");
			_deleteSubscription(deviceId);
			if (callback)
				callback();
		},

		subscribed: function (callback) {
			var storedDeviceId = localStorage.getItem("Q\tUsers.Device.deviceId");
			if (storedDeviceId) {
				callback(true);
			} else {
				callback(false);
			}
		},

		serviceWorkerRegistration: null,

		appConfig: null
	};

	function _FCMInit(register) {

		FCMPlugin.onTokenRefresh(function (token) {
			_registerDevice(token);
		});

		if (register) {
			FCMPlugin.getToken(function (token) {
				_registerDevice(token);
			});
		}

		FCMPlugin.onNotification(function (data) {
			// data.wasTapped is true: Notification was received on device tray and tapped by the user.
			// data.wasTapped is false: Notification was received in foreground. Maybe the user needs to be notified.
			Users.Device.onNotification.handle(data);
		});

	}

	function _PushNotificationInit(register) {
		var push = PushNotification.init({
			android: {},
			browser: {
				pushServiceURL: 'http://push.api.phonegap.com/v1/push'
			},
			ios: {
				alert: true,
				badge: true,
				sound: true
			},
			windows: {}
		});

		if (register) {
			push.on('registration', function (data) {
				var deviceId = data.registrationId;
				localStorage.setItem("Q\tUsers.Device.deviceId", deviceId);
				var appId = location.search.queryField('Q.Users.appId');
				if (appId) {
					localStorage.setItem("Q\tUsers.Device.appId", appId);
				}
				if (Q.Users.loggedInUser) {
					_registerDevice();
				}
				if (callback)
					callback();
			});
		}

		push.on('notification', function (data) {
			Users.Device.onNotification.handle(data);
		});

		push.on('error', function (e) {
			console.log("ERROR", e);
		});

		Users.logout.options.onSuccess.set(function () {
			PushNotification.setApplicationBadgeNumber(0);
		}, 'Users.PushNotifications');

	}

	function _registerServiceWorker(callback) {
		if (!'serviceWorker' in navigator && 'PushManager' in window) {
			if (callback)
				callback(null, new Error("Push messaging is not supported"));
			return;
		}
		navigator.serviceWorker.register('/Q/plugins/Users/js/sw.js')
			.then(function (swReg) {
				navigator.serviceWorker.addEventListener('message', function(event){
					Users.Device.onNotification.handle(event.data);
				});
				console.log('Service Worker is registered.');
				if (callback)
					callback(swReg);
			})
			.catch(function (error) {
				callback(null, error);
				console.error('Service Worker Error', error);
			});
	}

	function _registerDevice(deviceId) {
		if (!deviceId || !Q.Users.loggedInUser) {
			throw(new Error('Error while registering device. User must be logged in and deviceId must be set.'));
		}
		var appId = localStorage.getItem("Q\tUsers.Device.appId");
		// todo remove next line
		appId = 'com.qbix.cordova';
		if (!appId) {
			throw(new Error('Error while registering device. AppId must be must be set.'));
		}
		var storedDeviceId = localStorage.getItem("Q\tUsers.Device.deviceId");
		if (storedDeviceId === deviceId) {
			return;
		}
		localStorage.setItem("Q\tUsers.Device.deviceId", deviceId);
		Q.req('Users/device', function (err, response) {
			if (!err) {
				Q.handle(Users.onDevice, [response.data]);
			}
		}, {
			method: 'post',
			fields: {
				appId: appId,
				deviceId: deviceId
			}
		});
	}

	function _urlB64ToUint8Array(base64String) {
		var padding = '='.repeat((4 - base64String.length % 4) % 4);
		var base64 = (base64String + padding).replace(/\-/g, '+').replace(/_/g, '/');
		var rawData = window.atob(base64);
		var outputArray = new Uint8Array(rawData.length);
		for (var i = 0; i < rawData.length; ++i) {
			outputArray[i] = rawData.charCodeAt(i);
		}
		return outputArray;
	}

	function _saveSubscription(subscription, appConfig) {
		if (!subscription) {
			return;
		}
		subscription = JSON.parse(JSON.stringify(subscription));
		Q.req('Users/device', function (err, response) {
			if (!err) {
				Q.handle(Users.onDevice, [response.data]);
			}
		}, {
			method: 'post',
			fields: {
				deviceId: subscription.endpoint,
				auth: subscription.keys.auth,
				p256dh: subscription.keys.p256dh,
				appId: appConfig.appId
			}
		});
	}

	function _deleteSubscription(deviceId) {
		if (!deviceId) {
			return;
		}
		Q.req('Users/device', function (err, response) {
		}, {
			method: 'delete',
			fields: {
				deviceId: deviceId
			}
		});
	}

})(Q, jQuery);
