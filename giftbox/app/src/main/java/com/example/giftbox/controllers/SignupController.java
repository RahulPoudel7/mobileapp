package com.example.giftbox.controllers;

import android.text.TextUtils;
import android.util.Patterns;

public class SignupController {

    public enum SignupError {
        NONE,
        EMPTY_NAME,
        EMPTY_PHONE,
        INVALID_PHONE,
        EMPTY_EMAIL,
        INVALID_EMAIL,
        EMPTY_PASSWORD,
        SHORT_PASSWORD,
        EMPTY_CONFIRM_PASSWORD,
        PASSWORD_MISMATCH
    }

    public SignupError validateSignup(String name,
                                      String phone,
                                      String email,
                                      String password,
                                      String confirmPassword) {

        if (TextUtils.isEmpty(name)) {
            return SignupError.EMPTY_NAME;
        }

        if (TextUtils.isEmpty(phone)) {
            return SignupError.EMPTY_PHONE;
        }

        if (phone.length() < 7) {
            return SignupError.INVALID_PHONE;
        }

        if (TextUtils.isEmpty(email)) {
            return SignupError.EMPTY_EMAIL;
        }

        if (!Patterns.EMAIL_ADDRESS.matcher(email).matches()) {
            return SignupError.INVALID_EMAIL;
        }

        if (TextUtils.isEmpty(password)) {
            return SignupError.EMPTY_PASSWORD;
        }

        if (password.length() < 6) {
            return SignupError.SHORT_PASSWORD;
        }

        if (TextUtils.isEmpty(confirmPassword)) {
            return SignupError.EMPTY_CONFIRM_PASSWORD;
        }

        if (!password.equals(confirmPassword)) {
            return SignupError.PASSWORD_MISMATCH;
        }

        return SignupError.NONE;
    }
}

