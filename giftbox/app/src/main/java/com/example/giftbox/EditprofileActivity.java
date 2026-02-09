package com.example.giftbox;

import android.annotation.SuppressLint;
import android.content.Intent;
import android.graphics.Bitmap;
import android.net.Uri;
import android.os.Bundle;
import android.provider.MediaStore;
import android.widget.Button;
import android.widget.EditText;
import android.widget.ImageView;
import android.widget.ProgressBar;
import android.widget.Toast;

import androidx.activity.EdgeToEdge;
import androidx.annotation.NonNull;
import androidx.annotation.Nullable;
import androidx.appcompat.app.AlertDialog;
import androidx.appcompat.app.AppCompatActivity;
import androidx.core.graphics.Insets;
import androidx.core.view.ViewCompat;
import androidx.core.view.WindowInsetsCompat;

import com.example.giftbox.api.ApiClient;
import com.example.giftbox.api.ApiService;
import com.example.giftbox.utils.SessionManager;
import com.example.giftbox.view.ProfileActivity;
import com.google.gson.JsonObject;

import retrofit2.Call;
import retrofit2.Callback;
import retrofit2.Response;

public class EditprofileActivity extends AppCompatActivity {

    private static final int REQ_CAMERA = 1;
    private static final int REQ_GALLERY = 2;

    ImageView ivProfilePhoto, ivChangePhoto;
    EditText etName, etPhone;
    Button btnSave;
    ProgressBar progressBar;
    
    private SessionManager sessionManager;
    private ApiService apiService;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        EdgeToEdge.enable(this);
        setContentView(R.layout.activity_editprofile);   // set layout FIRST

        if (getSupportActionBar() != null) {
            getSupportActionBar().hide();
        }

        // Initialize SessionManager and ApiService
        sessionManager = new SessionManager(this);
        apiService = ApiClient.getApiService();

        // find views AFTER setContentView
        ivProfilePhoto = findViewById(R.id.ivProfilePhoto);
        ivChangePhoto  = findViewById(R.id.ivChangePhoto);
        ImageView ivBack = findViewById(R.id.ivBack);
        etName = findViewById(R.id.etName);
        etPhone = findViewById(R.id.etPhone);
        btnSave = findViewById(R.id.btnSave);
        
        // Load current user data
        loadUserData();

        // back button
        ivBack.setOnClickListener(v -> {
            Intent intent = new Intent(EditprofileActivity.this, ProfileActivity.class);
            startActivity(intent);
            finish();
        });

        // change photo click
        ivChangePhoto.setOnClickListener(v -> showImagePickerDialog());
        
        // save button click
        btnSave.setOnClickListener(v -> saveChanges());

        // insets
        ViewCompat.setOnApplyWindowInsetsListener(findViewById(R.id.main), (v, insets) -> {
            Insets systemBars = insets.getInsets(WindowInsetsCompat.Type.systemBars());
            v.setPadding(systemBars.left, systemBars.top, systemBars.right, systemBars.bottom);
            return insets;
        });
    }


    private void loadUserData() {
        String token = "Bearer " + sessionManager.getAuthToken();
        
        Call<JsonObject> call = apiService.getProfile(token);
        call.enqueue(new Callback<JsonObject>() {
            @Override
            public void onResponse(@NonNull Call<JsonObject> call, @NonNull Response<JsonObject> response) {
                if (response.isSuccessful() && response.body() != null) {
                    JsonObject profileData = response.body();
                    try {
                        JsonObject user = profileData.has("user") ? profileData.getAsJsonObject("user") : profileData;
                        
                        String name = user.has("name") ? user.get("name").getAsString() : "";
                        String phone = user.has("phone") && !user.get("phone").isJsonNull() 
                                ? user.get("phone").getAsString() 
                                : "";
                        
                        etName.setText(name);
                        etPhone.setText(phone);
                    } catch (Exception e) {
                        e.printStackTrace();
                    }
                }
            }

            @Override
            public void onFailure(@NonNull Call<JsonObject> call, @NonNull Throwable t) {
                Toast.makeText(EditprofileActivity.this, "Failed to load profile", Toast.LENGTH_SHORT).show();
            }
        });
    }

    private void saveChanges() {
        String name = etName.getText().toString().trim();
        String phone = etPhone.getText().toString().trim();
        
        // Validate input
        if (name.isEmpty()) {
            Toast.makeText(this, "Please enter your name", Toast.LENGTH_SHORT).show();
            return;
        }
        
        if (phone.isEmpty()) {
            Toast.makeText(this, "Please enter your phone number", Toast.LENGTH_SHORT).show();
            return;
        }
        
        // Show progress
        btnSave.setEnabled(false);
        
        // Create request body
        JsonObject profileBody = new JsonObject();
        profileBody.addProperty("name", name);
        profileBody.addProperty("phone", phone);
        
        String token = "Bearer " + sessionManager.getAuthToken();
        
        Call<JsonObject> call = apiService.updateProfile(token, profileBody);
        call.enqueue(new Callback<JsonObject>() {
            @Override
            public void onResponse(@NonNull Call<JsonObject> call, @NonNull Response<JsonObject> response) {
                btnSave.setEnabled(true);
                
                if (response.isSuccessful()) {
                    Toast.makeText(EditprofileActivity.this, "Profile updated successfully", Toast.LENGTH_SHORT).show();
                    
                    // Go back to profile activity
                    Intent intent = new Intent(EditprofileActivity.this, ProfileActivity.class);
                    startActivity(intent);
                    finish();
                } else {
                    try {
                        String errorBody = response.errorBody() != null ? response.errorBody().string() : "Unknown error";
                        Toast.makeText(EditprofileActivity.this, "Error: " + errorBody, Toast.LENGTH_LONG).show();
                    } catch (Exception e) {
                        Toast.makeText(EditprofileActivity.this, "Failed to update profile (Code: " + response.code() + ")", Toast.LENGTH_SHORT).show();
                    }
                }
            }

            @Override
            public void onFailure(@NonNull Call<JsonObject> call, @NonNull Throwable t) {
                btnSave.setEnabled(true);
                Toast.makeText(EditprofileActivity.this, "Error: " + t.getMessage(), Toast.LENGTH_SHORT).show();
            }
        });
    }

    private void showImagePickerDialog() {
        String[] options = {"Take Photo", "Choose from Gallery"};

        AlertDialog.Builder builder = new AlertDialog.Builder(this);
        builder.setTitle("Select Photo");
        builder.setItems(options, (dialog, which) -> {
            if (which == 0) {
                openCamera();
            } else if (which == 1) {
                openGallery();
            }
        });
        builder.show();
    }

    private void openCamera() {
        Intent intent = new Intent(MediaStore.ACTION_IMAGE_CAPTURE);
        if (intent.resolveActivity(getPackageManager()) != null) {
            startActivityForResult(intent, REQ_CAMERA);
        }
    }

    private void openGallery() {
        Intent intent = new Intent(Intent.ACTION_PICK,
                MediaStore.Images.Media.EXTERNAL_CONTENT_URI);
        intent.setType("image/*");
        startActivityForResult(intent, REQ_GALLERY);
    }



    @Override
    protected void onActivityResult(int requestCode, int resultCode, @Nullable Intent data) {
        super.onActivityResult(requestCode, resultCode, data);
        if (resultCode != RESULT_OK || data == null) return;

        if (requestCode == REQ_CAMERA) {
            Bundle extras = data.getExtras();
            if (extras != null) {
                Bitmap imageBitmap = (Bitmap) extras.get("data");
                ivProfilePhoto.setImageBitmap(imageBitmap);
            }
        } else if (requestCode == REQ_GALLERY) {
            Uri selectedImage = data.getData();
            if (selectedImage != null) {
                ivProfilePhoto.setImageURI(selectedImage);
            }
        }
    }
}
