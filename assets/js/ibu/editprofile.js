let EditProfile = {
	user: Utils.parseJwt(localStorage.getItem("userToken")),

	init: function () {
		Utils.block("#user_info", {
			overlayColor: "#000000",
			type: "loader",
			state: "info",
			size: "lg",
		});

		$('#update_user_form input[name="name"]').val(EditProfile.user.name);
		$('#update_user_form input[name="email"]').val(EditProfile.user.email);
		$('#update_user_form input[name="phone"]').val(EditProfile.user.phone);

		if (EditProfile.user.sms_verify) {
			$("input[name=verification][value=sms_verify]").prop("checked", true);
		} else if (EditProfile.user.otp_verify) {
			$("input[name=verification][value=otp_verify]").prop("checked", true);
		} else {
			$("input[name=verification][value=none]").prop("checked", true);
		}

		if (localStorage.getItem("rememberToken")) {
			rememberArray = Utils.parseJwt(localStorage.getItem("rememberToken"));
			if (rememberArray.includes(EditProfile.user.id.toString())) {
				$("input[name=remember]").prop("checked", true);
			}
		}

		if (!localStorage.getItem("userToken")) {
			window.location("/");
		}

		Utils.unblock("#user_info");
		EditProfile.update_user();
		Utils.user_login_check("main.html#edit_profile");
		$("body").on("click", '[data-toggle="modal"]', function () {
			$($(this).data("target") + " .modal-body").load($(this).data("remote"));
		});
	},

	update_user: function () {
		$("#update_user_form").validate({
			rules: {
				name: {
					required: true,
					minlength: 5,
					maxlength: 50,
				},

				phone: {
					required: true,
					minlength: 9,
					maxlength: 15,
				},

				email: {
					required: true,
					email: true,
				},
			},

			messages: {
				name: {
					minlength: "The full name must be longer than 5",
					maxlength: "The full name must be shorter than 30",
				},
				phone: {
					minlength: "The phone must be longer or equal to 9",
					maxlength: "The phone must be shorter than 15",
				},
			},

			submitHandler: function (form, event) {
				event.preventDefault();

				let form_user = Utils.objectifyForm($("#update_user_form"));
				let verification = $('input[name="verification"]:checked').val();
				if ($("#remember").is(":checked")) {
					form_user["remember_me"] = 1;
				} else {
					form_user["remember_me"] = 0;
				}
				form_user["remember_token"] = localStorage.getItem("rememberToken");

				if (verification == "sms_verify") {
					form_user.sms_verify = 1;
					form_user.otp_verify = 0;
				} else if (verification == "otp_verify") {
					form_user.sms_verify = 0;
					form_user.otp_verify = 1;
				} else {
					form_user.sms_verify = 0;
					form_user.otp_verify = 0;
				}

				Utils.block("#user_info", {
					overlayColor: "#000000",
					type: "loader",
					state: "info",
					size: "lg",
				});
				Utils.http_put(
					"rest/users/" + EditProfile.user.id,
					null,
					function (data) {
						toastr.success("Successfully updated user info");
						Utils.unblock("#user_info");
						if (data.remember_token) {
							localStorage.setItem("rememberToken", data.remember_token);
						}
					},
					form_user,
					function (error) {
						error_text = JSON.parse(error.responseText);
						toastr.error(error_text.msg);
						Utils.unblock("#user_info");
					}
				);
			},
		});
	},

	update_password: function () {
		$("#change_password_form").validate({
			rules: {
				old_password: {
					required: true,
				},
				new_password: {
					required: true,
					minlength: 4,
					maxlength: 20,
				},

				confirm_new_password: {
					required: true,
					equalTo: "#new_password",
				},
			},

			messages: {
				new_password: {
					minlength: "The password must be longer than 4",
					maxlength: "The password must be shorter than 20",
				},

				confirm_new_password: {
					equalTo: "Enter Confirm Password Same as Password",
				},
			},
		});

		if ($("#change_password_form").valid()) {
			Utils.block("#password_modal .modal-content", {
				overlayColor: "#000000",
				type: "loader",
				state: "info",
				size: "lg",
			});

			Utils.http_put(
				"rest/users/password/" + EditProfile.user.id,
				"#change_password_form",
				function () {
					toastr.success("Successfully updated password");
					Utils.unblock("#password_modal .modal-content");
					$("#password_modal").modal("toggle");
				},
				null,
				function (error) {
					error_text = JSON.parse(error.responseText);
					toastr.error(error_text.msg);
					Utils.unblock("#password_modal .modal-content");
				}
			);
		}
	},
};
