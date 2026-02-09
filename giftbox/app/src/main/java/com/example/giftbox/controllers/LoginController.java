package com.example.giftbox.controllers;

import android.text.TextUtils;
import android.util.Patterns;

public class LoginController {

    public enum LoginError {
        NONE,
        EMPTY_EMAIL,
        INVALID_EMAIL,
        EMPTY_PASSWORD,
        SHORT_PASSWORD
    }

    // Pure logic: no Context, no Toast, no Views
    public LoginError validateCredentials(String email, String password) {
        if (TextUtils.isEmpty(email)) {
            return LoginError.EMPTY_EMAIL;
        }
        if (!Patterns.EMAIL_ADDRESS.matcher(email).matches()) {
            return LoginError.INVALID_EMAIL;
        }
        if (TextUtils.isEmpty(password)) {
            return LoginError.EMPTY_PASSWORD;
        }
        if (password.length() < 6) {
            return LoginError.SHORT_PASSWORD;
        }
        return LoginError.NONE;
    }
}
