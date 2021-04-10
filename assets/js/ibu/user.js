var User = {
	login: function (selector) {
		$(selector).validate({
			submitHandler: function (form, event) {
				let form_user = Utils.objectifyForm($(selector));
				form_user["remember_token"] = localStorage.getItem("rememberToken");
				event.preventDefault();
				$("#singinButton").attr("class", "fa fa-spinner fa-spin");
				Utils.http_post(
					"rest/login",
					null,
					function (data) {
						localStorage.setItem("userToken", data.token);
						let user = Utils.parseJwt(data.token);
						if (!user.id) {
							user.id = 0;
						}
						rememberArray = [];
						if (localStorage.getItem("rememberToken")) {
							rememberArray = Utils.parseJwt(localStorage.getItem("rememberToken"));
						}

						if (rememberArray.includes(user.id.toString())) {
							Utils.user_login_check("index.html");
						} else {
							if (user.sms_verify || user.otp_verify || !user.account_verified) {
								$("#singinButton").attr("class", "");
								$("#verify").removeAttr("hidden");
								$("#m_login").removeClass("m-login--signin"),
									$("#m_login").removeClass("m-login--forget-password"),
									$("#m_login").removeClass("m-login--signup"),
									$("#m_login").addClass("m-login--verify"),
									mUtil.animateClass($("#m_login").find(".m-login__verify")[0], "flipInX animated");
								if (!user.account_verified) {
									$("#verify_button").attr("onclick", "User.verify_register();");
								} else {
									$("#verify_button").attr("onclick", "User.verify_login();");
								}
							} else {
								Utils.user_login_check("index.html");
							}
						}
					},
					form_user,
					function (error) {
						$("#singinButton").attr("class", "");
						error_response = JSON.parse(error.responseText);
						toastr.error(error_response.msg);
						console.log(error_response.login_attempt);
						if (error_response.login_attempt >= 3) {
							$("#login_captcha").removeAttr("hidden");
							hcaptcha.reset(Main.getWidgetIDLogin());
						}
					}
				);
			},
		});
	},
	verify_login: function () {
		var code = $("#code").val();
		$("#verifyButton").attr("class", "fa fa-spinner fa-spin");
		Utils.http_post(
			"rest/login/verify",
			null,
			function (data) {
				localStorage.setItem("userToken", data.token);
				Utils.user_login_check("index.html");
			},
			{ code: code },
			function (error) {
				$("#verifyButton").attr("class", "");
				error_response = JSON.parse(error.responseText);
				toastr.error(error_response.msg);
			}
		);
	},
	verify_register: function () {
		var user = Utils.objectifyForm($("#user_signup"));
		user["code"] = $("#code").val();
		console.log(user);
		$("#verifyButton").attr("class", "fa fa-spinner fa-spin");
		Utils.http_post(
			"rest/register/verify",
			null,
			function (data) {
				localStorage.setItem("userToken", data.token);
				Utils.user_login_check("index.html");
			},
			user,
			function (error) {
				$("#verifyButton").attr("class", "");
				console.log(error);
				error_response = JSON.parse(error.responseText);
				toastr.error(error_response.msg);
			}
		);
	},
	signup: function (selector) {
		$(selector).validate({
			rules: {
				name: {
					required: true,
					minlength: 5,
					maxlength: 50,
				},

				email: {
					required: true,
				},

				password: {
					required: true,
					minlength: 4,
					maxlength: 20,
				},

				rpassword: {
					required: true,
					equalTo: "#password",
				},
			},

			messages: {
				fullname: {
					minlength: "Your name must be at least 5 characters long",
					maxlength: "Your name must be less than 50 characters long",
				},

				password: {
					minlength: "The password must be longer than 4",
					maxlength: "The password must be shorter than 10",
				},

				rpassword: {
					equalTo: "Enter Confirm Password Same as Password",
				},
			},

			submitHandler: function (form, event) {
				event.preventDefault();
				$("#singupButton").attr("class", "fa fa-spinner fa-spin");

				Utils.http_post(
					"rest/register",
					selector,
					function (data) {
						$("#singinButton").attr("class", "");
						$("#verify").removeAttr("hidden");
						$("#m_login").removeClass("m-login--signin"),
							$("#m_login").removeClass("m-login--forget-password"),
							$("#m_login").removeClass("m-login--signup"),
							$("#m_login").addClass("m-login--verify"),
							mUtil.animateClass($("#m_login").find(".m-login__verify")[0], "flipInX animated");

						localStorage.setItem("userToken", data.token);
						$("#verify_button").attr("onclick", "User.verify_register();");
					},
					null,
					function (error) {
						$("#singupButton").attr("class", "");
						error_response = JSON.parse(error.responseText);
						toastr.error(error_response.msg);
						hcaptcha.reset(Main.getWidgetIDRegister());
					}
				);
			},
		});
	},

	recover_password: function () {
		$("#update_password").validate({
			rules: {
				password: {
					required: true,
					minlength: 4,
					maxlength: 20,
				},

				rpassword: {
					required: true,
					equalTo: "#password",
				},
			},

			messages: {
				password: {
					minlength: "The password must be longer than 4",
					maxlength: "The password must be shorter than 10",
				},

				rpassword: {
					equalTo: "Enter Confirm Password Same as Password",
				},
			},

			submitHandler: function (form, event) {
				event.preventDefault();
				$("#recoverButton").attr("class", "fa fa-spinner fa-spin");
				user = Utils.parseJwt(localStorage.getItem("userToken"));
				Utils.http_put(
					"rest/users/password-recover/" + user.id,
					"#update_password",
					function () {
						localStorage.setItem("userToken", "");
						toastr.success("Successfully updated password");
						window.location = "/";
					},
					null,
					function (error) {
						$("#recoverButton").attr("class", "");
						error_response = JSON.parse(error.responseText);
						toastr.error(error_response.msg);
					}
				);
			},
		});
	},

	frogot_password: function () {
		$("#frogot_password_form").validate({
			rules: {
				email: {
					required: true,
				},
			},

			submitHandler: function (form, event) {
				event.preventDefault();
				$("#requestButton").attr("class", "fa fa-spinner fa-spin");
				Utils.http_post(
					"rest/users/recovery-email",
					"#frogot_password_form",
					function () {
						$("#requestButton").attr("class", "");
						toastr.success("Recovery mail has been send to the provided email");
					},
					null,
					function (error) {
						$("#requestButton").attr("class", "");
						error_response = JSON.parse(error.responseText);
						toastr.error(error_response.msg);
					}
				);
			},
		});
	},
};
