package com.example.giftbox.view;

import android.Manifest;
import android.annotation.SuppressLint;
import android.content.Intent;
import android.content.pm.PackageManager;
import android.location.Address;
import android.location.Geocoder;
import android.os.Bundle;
import android.util.Log;
import android.widget.Button;
import android.widget.CheckBox;
import android.widget.ImageView;
import android.widget.Toast;

import androidx.activity.result.ActivityResultLauncher;
import androidx.activity.result.contract.ActivityResultContracts;
import androidx.annotation.Nullable;
import androidx.appcompat.app.AppCompatActivity;
import androidx.core.content.ContextCompat;

import com.example.giftbox.R;
import com.example.giftbox.api.ApiClient;
import com.example.giftbox.api.ApiService;
import com.example.giftbox.utils.SessionManager;
import com.google.android.gms.location.FusedLocationProviderClient;
import com.google.android.gms.location.LocationServices;
import com.google.android.material.textfield.TextInputEditText;
import com.google.android.material.textfield.TextInputLayout;
import com.google.gson.JsonObject;

import java.io.IOException;
import java.util.List;
import java.util.Locale;

import retrofit2.Call;
import retrofit2.Callback;
import retrofit2.Response;

public class AddAddressActivity extends AppCompatActivity {

    private static final String TAG = "AddAddressActivity";
    private static final int REQUEST_CHECK_SETTINGS = 1001;

    private FusedLocationProviderClient fusedLocationClient;
    private double currentLat = 0.0;
    private double currentLng = 0.0;
    private ApiService apiService;
    private SessionManager sessionManager;
    private CheckBox cbSaveAsDefault;

    private final ActivityResultLauncher<Intent> mapLauncher = registerForActivityResult(
            new ActivityResultContracts.StartActivityForResult(),
            result -> {
                if (result.getResultCode() == RESULT_OK && result.getData() != null) {
                    String fullAddress = result.getData().getStringExtra(MapPickerActivity.EXTRA_SELECTED_ADDRESS);
                    if (fullAddress != null) {
                        TextInputEditText etLocation = findViewById(R.id.etLocation);
                        TextInputEditText etAddressTitle = findViewById(R.id.etAddressTitle);

                        etLocation.setText(fullAddress);
                        String suggestedTitle = fullAddress.split(",")[0].trim();
                        etAddressTitle.setText(suggestedTitle);
                        etAddressTitle.setSelection(etAddressTitle.getText().length());

                        getCoordinatesFromAddress(fullAddress);
                    }
                }
            }
    );

    private final ActivityResultLauncher<String> requestPermissionLauncher =
            registerForActivityResult(new ActivityResultContracts.RequestPermission(), isGranted -> {
                if (isGranted) {
                    checkGPSSettings();
                } else {
                    Toast.makeText(this, "Permission denied.", Toast.LENGTH_SHORT).show();
                }
            });

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_add_address);
        if (getSupportActionBar() != null) getSupportActionBar().hide();

        fusedLocationClient = LocationServices.getFusedLocationProviderClient(this);
        apiService = ApiClient.getApiService();
        sessionManager = new SessionManager(this);

        // View Initialization
        ImageView ivBackAddAddress = findViewById(R.id.ivBackAddAddress);
        Button btnSelectOnMap = findViewById(R.id.btnSelectOnMap);
        Button btnUseCurrentLocation = findViewById(R.id.btnUseCurrentLocation);
        Button btnSaveAddress = findViewById(R.id.btnSaveAddress);
        cbSaveAsDefault = findViewById(R.id.cbSaveAsDefault);

        ivBackAddAddress.setOnClickListener(v -> finish());
        btnSelectOnMap.setOnClickListener(v -> {
            Intent intent = new Intent(AddAddressActivity.this, MapPickerActivity.class);
            mapLauncher.launch(intent);
        });
        btnUseCurrentLocation.setOnClickListener(v -> checkPermissionAndGetCurrentLocation());

        btnSaveAddress.setOnClickListener(v -> {
            TextInputEditText etAddressTitle = findViewById(R.id.etAddressTitle);
            TextInputEditText etLocation = findViewById(R.id.etLocation);
            TextInputLayout tilAddressTitle = findViewById(R.id.tilAddressTitle);
            TextInputLayout tilLocation = findViewById(R.id.tilLocation);

            clearErrors(tilAddressTitle, tilLocation);

            if (validateAddress(etAddressTitle, etLocation, tilAddressTitle, tilLocation)) {
                // If save as default is checked, save to backend
                if (cbSaveAsDefault.isChecked()) {
                    saveDefaultAddressToBackend(etAddressTitle.getText().toString(), etLocation.getText().toString());
                } else {
                    // Return address to previous activity
                    returnAddressResult(etAddressTitle.getText().toString(), etLocation.getText().toString());
                }
            }
        });
    }

    // --- Handling the GPS Settings Popup Result ---
    @Override
    protected void onActivityResult(int requestCode, int resultCode, @Nullable Intent data) {
        super.onActivityResult(requestCode, resultCode, data);
        if (requestCode == REQUEST_CHECK_SETTINGS) {
            if (resultCode == RESULT_OK) {
                getCurrentLocation();
            } else {
                Toast.makeText(this, "Please enable GPS to use this feature", Toast.LENGTH_SHORT).show();
            }
        }
    }

    private void checkPermissionAndGetCurrentLocation() {
        if (ContextCompat.checkSelfPermission(this, Manifest.permission.ACCESS_FINE_LOCATION) == PackageManager.PERMISSION_GRANTED) {
            checkGPSSettings();
        } else {
            requestPermissionLauncher.launch(Manifest.permission.ACCESS_FINE_LOCATION);
        }
    }

    private void checkGPSSettings() {
        com.google.android.gms.location.LocationRequest locationRequest = com.google.android.gms.location.LocationRequest.create()
                .setPriority(com.google.android.gms.location.LocationRequest.PRIORITY_HIGH_ACCURACY);

        com.google.android.gms.location.LocationSettingsRequest.Builder builder = new com.google.android.gms.location.LocationSettingsRequest.Builder()
                .addLocationRequest(locationRequest);

        LocationServices.getSettingsClient(this).checkLocationSettings(builder.build())
                .addOnCompleteListener(task -> {
                    try {
                        task.getResult(com.google.android.gms.common.api.ApiException.class);
                        getCurrentLocation();
                    } catch (com.google.android.gms.common.api.ApiException exception) {
                        if (exception.getStatusCode() == com.google.android.gms.location.LocationSettingsStatusCodes.RESOLUTION_REQUIRED) {
                            try {
                                com.google.android.gms.common.api.ResolvableApiException resolvable = (com.google.android.gms.common.api.ResolvableApiException) exception;
                                resolvable.startResolutionForResult(AddAddressActivity.this, REQUEST_CHECK_SETTINGS);
                            } catch (Exception e) {
                                Log.e(TAG, "Error starting resolution", e);
                            }
                        }
                    }
                });
    }


    @SuppressLint("MissingPermission")
    private void getCurrentLocation() {
        // Show a small toast so the user knows the app is working
        Toast.makeText(this, "Locating...", Toast.LENGTH_SHORT).show();

        fusedLocationClient.getLastLocation().addOnSuccessListener(this, location -> {
            if (location != null) {
                currentLat = location.getLatitude();
                currentLng = location.getLongitude();

                // Find your UI fields
                TextInputEditText etLocation = findViewById(R.id.etLocation);
                TextInputEditText etAddressTitle = findViewById(R.id.etAddressTitle);

                // Attempt to get a human-readable address (Requires Internet)
                Address addressObject = getAddressObjectFromLatLng(currentLat, currentLng);

                if (addressObject != null) {
                    // --- ONLINE MODE ---
                    String fullAddress = addressObject.getAddressLine(0);
                    etLocation.setText(fullAddress);

                    // Auto-fill Title: Use place name if available, otherwise first part of address
                    if (addressObject.getFeatureName() != null && !addressObject.getFeatureName().equals(fullAddress)) {
                        etAddressTitle.setText(addressObject.getFeatureName());
                    } else {
                        etAddressTitle.setText(fullAddress.split(",")[0].trim());
                    }
                    Toast.makeText(this, "Location detected!", Toast.LENGTH_SHORT).show();
                } else {
                    // --- OFFLINE MODE ---
                    // If Geocoder fails (no internet), we still fill the field with coordinates
                    String coords = String.format(Locale.getDefault(), "%.5f, %.5f", currentLat, currentLng);
                    etLocation.setText(coords);
                    etAddressTitle.setText("GPS Location"); // Default title for offline

                    Toast.makeText(this, "Offline: Using GPS coordinates", Toast.LENGTH_LONG).show();
                }

                // Move cursor to end of title
                if (etAddressTitle.getText() != null) {
                    etAddressTitle.setSelection(etAddressTitle.getText().length());
                }

            } else {
                // This happens if GPS was just turned on and hasn't fixed a position yet
                // We request a fresh one-time location update
                requestNewLocationData();
            }
        });
    }

    /**
     * If lastKnownLocation is null, this forces the device to find the current position.
     */
    @SuppressLint("MissingPermission")
    private void requestNewLocationData() {
        com.google.android.gms.location.LocationRequest lr = com.google.android.gms.location.LocationRequest.create()
                .setPriority(com.google.android.gms.location.LocationRequest.PRIORITY_HIGH_ACCURACY)
                .setInterval(0)
                .setFastestInterval(0)
                .setNumUpdates(1);

        fusedLocationClient.requestLocationUpdates(lr, new com.google.android.gms.location.LocationCallback() {
            @Override
            public void onLocationResult(com.google.android.gms.location.LocationResult locationResult) {
                getCurrentLocation(); // Retry once we have a fresh fix
            }
        }, android.os.Looper.myLooper());
    }
    private void getCoordinatesFromAddress(String addressString) {
        Geocoder geocoder = new Geocoder(this, Locale.getDefault());
        try {
            List<Address> addresses = geocoder.getFromLocationName(addressString, 1);
            if (addresses != null && !addresses.isEmpty()) {
                currentLat = addresses.get(0).getLatitude();
                currentLng = addresses.get(0).getLongitude();
            }
        } catch (IOException e) {
            Log.e(TAG, "Geocoder failed", e);
        }
    }

    private Address getAddressObjectFromLatLng(double latitude, double longitude) {
        Geocoder geocoder = new Geocoder(this, Locale.getDefault());
        try {
            List<Address> addresses = geocoder.getFromLocation(latitude, longitude, 1);
            if (addresses != null && !addresses.isEmpty()) return addresses.get(0);
        } catch (IOException e) {
            Log.e(TAG, "Geocoder service failed", e);
        }
        return null;
    }

    private boolean validateAddress(TextInputEditText title, TextInputEditText location, TextInputLayout tilTitle, TextInputLayout tilLoc) {
        boolean valid = true;
        if (title.getText().toString().trim().isEmpty()) {
            tilTitle.setError("Title required");
            valid = false;
        }
        if (location.getText().toString().trim().isEmpty()) {
            tilLoc.setError("Location required");
            valid = false;
        }
        return valid;
    }

    private void clearErrors(TextInputLayout... tils) {
        for (TextInputLayout til : tils) til.setError(null);
    }

    private void returnAddressResult(String title, String address) {
        Intent resultIntent = new Intent();
        resultIntent.putExtra("addressTitle", title);
        resultIntent.putExtra("detailAddress", address);
        resultIntent.putExtra("latitude", currentLat);
        resultIntent.putExtra("longitude", currentLng);
        resultIntent.putExtra("customerName", "");
        resultIntent.putExtra("phone", "");

        setResult(RESULT_OK, resultIntent);
        finish();
    }

    private void saveDefaultAddressToBackend(String title, String address) {
        String token = sessionManager.getAuthToken();
        String bearerToken = "Bearer " + token;

        JsonObject addressBody = new JsonObject();
        addressBody.addProperty("default_delivery_address", address);
        addressBody.addProperty("default_delivery_lat", currentLat);
        addressBody.addProperty("default_delivery_lng", currentLng);

        apiService.updateDefaultAddress(bearerToken, addressBody).enqueue(new Callback<JsonObject>() {
            @Override
            public void onResponse(Call<JsonObject> call, Response<JsonObject> response) {
                if (response.isSuccessful()) {
                    Toast.makeText(AddAddressActivity.this, "Default address saved!", Toast.LENGTH_SHORT).show();
                    // Return address to previous activity as well
                    returnAddressResult(title, address);
                } else {
                    Toast.makeText(AddAddressActivity.this, "Failed to save default address", Toast.LENGTH_SHORT).show();
                    // Still return the address
                    returnAddressResult(title, address);
                }
            }

            @Override
            public void onFailure(Call<JsonObject> call, Throwable t) {
                Toast.makeText(AddAddressActivity.this, "Error: " + t.getMessage(), Toast.LENGTH_SHORT).show();
                // Still return the address
                returnAddressResult(title, address);
            }
        });
    }
}