var Utils = {
	user_login_check: function (page) {
		try {
			var user = Utils.parseJwt(localStorage.getItem("userToken"));
			if (user.account_verified) {
				if (page != "main.html#edit_profile") {
					window.location = "main.html";
				} else {
					$("body").show();
				}
			} else {
				localStorage.setItem("userToken", "");
				window.location = "index.html";
				$("body").show();
			}
			//$("body").show();
		} catch (error) {
			$("body").show();
		}
	},

	parseJwt: function (token) {
		var base64Url = token.split(".")[1];
		var base64 = decodeURIComponent(
			atob(base64Url)
				.split("")
				.map(function (c) {
					return "%" + ("00" + c.charCodeAt(0).toString(16)).slice(-2);
				})
				.join("")
		);

		return JSON.parse(base64);
	},

	objectifyForm: function (form) {
		var fields = {};

		form.find("input, textarea, select").each(function () {
			fields[this.name] = $(this).val();
		});

		return fields;
	},

	http_get: function (url, success_callback, error_callback) {
		$.ajax({
			url: url,
			type: "GET",
			dataType: "json",
			headers: {
				Authorization: localStorage.getItem("userToken"),
			},
			contentType: "application/json; charset=utf-8",
			success: function (data) {
				if (success_callback) {
					success_callback(data);
				}
			},
			error: function (error) {
				if (error_callback) {
					error_callback(error);
				} else {
					error_text = JSON.parse(error.responseText);
					toastr.error(error_text.msg);
				}
			},
		});
	},

	http_post: function (url, form, success_callback, passed_data, error_callback) {
		if (!passed_data) {
			var passed_data = Utils.objectifyForm($(form));
		}
		$.ajax({
			url: url,
			type: "POST",
			headers: {
				Authorization: localStorage.getItem("userToken"),
			},
			contentType: "application/json; charset=utf-8",
			data: JSON.stringify(passed_data),
			success: function (data) {
				if (success_callback) {
					success_callback(data);
				}
			},
			error: function (error) {
				if (error_callback) {
					error_callback(error);
				} else {
					error_text = JSON.parse(error.responseText);
					toastr.error(error_text.msg);
				}
			},
		});
	},

	http_put: function (url, form, success_callback, data, error_callback) {
		if (!data) {
			var data = Utils.objectifyForm($(form));
		}
		$.ajax({
			url: url,
			type: "PUT",
			headers: {
				Authorization: localStorage.getItem("userToken"),
			},
			contentType: "application/json; charset=utf-8",
			data: JSON.stringify(data),
			success: function (data) {
				if (success_callback) {
					success_callback(data);
				}
			},
			error: function (error) {
				if (error_callback) {
					error_callback(error);
				} else {
					error_text = JSON.parse(error.responseText);
					toastr.error(error_text.msg);
				}
			},
		});
	},

	http_delete: function (url, success_callback, error_callback) {
		$.ajax({
			url: url,
			type: "DELETE",
			headers: {
				Authorization: localStorage.getItem("userToken"),
			},
			contentType: "application/json; charset=utf-8",
			success: function (data) {
				if (success_callback) {
					success_callback(data);
				}
			},
			error: function (error) {
				if (error_callback) {
					error_callback(error);
				} else {
					error_text = JSON.parse(error.responseText);
					toastr.error(error_text.msg);
				}
			},
		});
	},

	block: function (target, options) {
		var el = $(target);

		options = $.extend(
			true,
			{
				opacity: 0.03,
				overlayColor: "#000000",
				state: "brand",
				type: "loader",
				size: "lg",
				centerX: true,
				centerY: true,
				message: "",
				shadow: true,
				width: "auto",
			},
			options
		);

		var skin;
		var state;
		var loading;

		if (options.type == "spinner") {
			skin = options.skin ? "m-spinner--skin-" + options.skin : "";
			state = options.state ? "m-spinner--" + options.state : "";
			loading = '<div class="m-spinner ' + skin + " " + state + '"></div';
		} else {
			skin = options.skin ? "m-loader--skin-" + options.skin : "";
			state = options.state ? "m-loader--" + options.state : "";
			size = options.size ? "m-loader--" + options.size : "";
			loading = '<div class="m-loader ' + skin + " " + state + " " + size + '"></div';
		}

		if (options.message && options.message.length > 0) {
			var classes = "m-blockui " + (options.shadow === false ? "m-blockui-no-shadow" : "");

			html = '<div class="' + classes + '"><span>' + options.message + "</span><span>" + loading + "</span></div>";

			var el = document.createElement("div");
			mUtil.get("body").prepend(el);
			mUtil.addClass(el, classes);
			el.innerHTML = "<span>" + options.message + "</span><span>" + loading + "</span>";
			options.width = mUtil.actualWidth(el) + 10;
			mUtil.remove(el);

			if (target == "body") {
				html =
					'<div class="' +
					classes +
					'" style="margin-left:-' +
					options.width / 2 +
					'px;"><span>' +
					options.message +
					"</span><span>" +
					loading +
					"</span></div>";
			}
		} else {
			html = loading;
		}

		console.log(html);

		var params = {
			message: html,
			centerY: options.centerY,
			centerX: options.centerX,
			css: {
				top: "30%",
				left: "50%",
				border: "0",
				padding: "0",
				backgroundColor: "none",
				width: options.width,
			},
			overlayCSS: {
				backgroundColor: options.overlayColor,
				opacity: options.opacity,
				cursor: "wait",
				zIndex: "10",
			},
			onUnblock: function () {
				if (el && el[0]) {
					mUtil.css(el[0], "position", "");
					mUtil.css(el[0], "zoom", "");
				}
			},
		};

		if (target == "body") {
			params.css.top = "50%";
			$.blockUI(params);
		} else {
			var el = $(target);
			el.block(params);
		}
	},

	unblock: function (target) {
		if (target && target != "body") {
			$(target).unblock();
		} else {
			$.unblockUI();
		}
	},
};
