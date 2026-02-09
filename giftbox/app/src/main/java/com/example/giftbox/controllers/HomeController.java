package com.example.giftbox.controllers;

public class HomeController {

    // Format greeting text based on username
    public String getGreetingText(String username) {
        if (username != null && !username.trim().isEmpty()) {
            return "Hi, " + username.trim() + "!";
        } else {
            return "Hi there!";
        }
    }
}
