package com.example.giftbox.models;

import com.google.gson.annotations.SerializedName;

public class VerifyOtpResponse {
    @SerializedName("success")
    private boolean success;

    @SerializedName("message")
    private String message;

    @SerializedName("data")
    private OtpData data;

    public boolean isSuccess() {
        return success;
    }

    public void setSuccess(boolean success) {
        this.success = success;
    }

    public String getMessage() {
        return message;
    }

    public void setMessage(String message) {
        this.message = message;
    }

    public OtpData getData() {
        return data;
    }

    public void setData(OtpData data) {
        this.data = data;
    }

    public static class OtpData {
        @SerializedName("user_id")
        private int userId;

        @SerializedName("email")
        private String email;

        @SerializedName("token")
        private String token;

        public int getUserId() {
            return userId;
        }

        public void setUserId(int userId) {
            this.userId = userId;
        }

        public String getEmail() {
            return email;
        }

        public void setEmail(String email) {
            this.email = email;
        }

        public String getToken() {
            return token;
        }

        public void setToken(String token) {
            this.token = token;
        }
    }
}
