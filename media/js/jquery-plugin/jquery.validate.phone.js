/*
 * Kalkun - open source web based SMS manager
 *
 * Copyright (C) 2022 Fab Stz <fabstz-it@yahoo.fr>
 *
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the Free
 * Software Foundation, either version 3 of the License, or (at your option)
 * any later version.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or
 * FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for
 * more details.
 *
 * You should have received a copy of the GNU General Public License along with
 * this program. If not, see <https://spdx.org/licenses/GPL-3.0-or-later.html>.
 *
 */

$(document).ready(function() {
	// Use libphonenumber to check if number is a possible mobile phone number
	// We by purpose don't check for Validity but for Possibility
	jQuery.validator.addMethod("phone", function(phone_number, element, country) {
		try {
			const phoneNumber = libphonenumber.parsePhoneNumber(phone_number, country);
			var is_mobile = (phoneNumber.getType() === 'MOBILE' || phoneNumber.getType() === 'FIXED_LINE_OR_MOBILE');
// 			console.log(
// 				"Phone number: " + phoneNumber.formatInternational() +
// 				" - Type:" + phoneNumber.getType() +
// 				" - Possible?" + phoneNumber.isPossible());
			return (phoneNumber.isPossible() && is_mobile === true);
		} catch (error) {
			console.error("Error while checking phone number: " + error.message);
			return false;
		}
	}, "Please specify a valid mobile phone number");
});
