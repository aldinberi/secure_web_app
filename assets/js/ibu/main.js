var Main = {
	widgetID_login: null,

	widgetID_register: null,

	setWidgetIDLogin: function (widgetID_login) {
		this.widgetID_login = widgetID_login;
	},

	setWidgetIDRegister: function (widgetID_register) {
		this.widgetID_register = widgetID_register;
	},

	getWidgetIDLogin: function () {
		return this.widgetID_login;
	},

	getWidgetIDRegister: function () {
		return this.widgetID_register;
	},

	init: function () {
		if (!localStorage.getItem("userToken")) {
			var token = window.location.href.split("?jwt=")[1];
			if (token) {
				localStorage.setItem("userToken", token);
				Utils.user_login_check("index.html");
			}
		}

		$(function () {
			function rescaleCaptcha() {
				var width = $("#register_captcha").parent().width();
				var scale = 1;
				if (width < 302) {
					scale = width / 302;
				} else {
					scale = 1.0;
				}

				if (scale != 0) {
					$("#register_captcha").css("transform", "scale(" + scale + ")");
					$("#register_captcha").css("-webkit-transform", "scale(" + scale + ")");
					$("#register_captcha").css("transform-origin", "0 0");
					$("#register_captcha").css("-webkit-transform-origin", "0 0");
				}
			}

			rescaleCaptcha();
			$(window).resize(function () {
				rescaleCaptcha();
			});
		});

		$(function () {
			function rescaleCaptcha() {
				var width = $("#login_captcha").parent().width();
				var scale;
				if (width < 302) {
					scale = width / 302;
				} else {
					scale = 1.0;
				}

				if (scale != 0) {
					$("#login_captcha").css("transform", "scale(" + scale + ")");
					$("#login_captcha").css("-webkit-transform", "scale(" + scale + ")");
					$("#login_captcha").css("transform-origin", "0 0");
					$("#login_captcha").css("-webkit-transform-origin", "0 0");
				}
			}

			rescaleCaptcha();
			$(window).resize(function () {
				rescaleCaptcha();
			});
		});

		User.login("#user_login");
		User.signup("#user_signup");
		User.frogot_password();
		Utils.user_login_check("index.html");
	},

	recovery_init: function () {
		var token_jwt = window.location.href.split("?jwt=")[1];
		if (token_jwt) {
			localStorage.setItem("userToken", token_jwt);
		}

		User.recover_password();
	},
};
