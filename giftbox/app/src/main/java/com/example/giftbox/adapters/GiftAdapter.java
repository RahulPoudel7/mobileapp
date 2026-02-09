package com.example.giftbox.adapters;

import android.content.Context;
import android.util.Log;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.ImageView;
import android.widget.TextView;
import android.widget.Toast;

import androidx.annotation.NonNull;
import androidx.recyclerview.widget.RecyclerView;

import com.bumptech.glide.Glide;
import com.example.giftbox.R;
import com.example.giftbox.models.GiftListResponse;
import com.google.android.material.button.MaterialButton;

import java.util.List;

public class GiftAdapter extends RecyclerView.Adapter<GiftAdapter.GiftViewHolder> {

    private static final String TAG = "GiftAdapter";
    private List<GiftListResponse.Gift> gifts;
    private Context context;
    private OnGiftClickListener onGiftClickListener;
    private OnAddToCartListener onAddToCartListener;

    public interface OnGiftClickListener {
        void onGiftClick(GiftListResponse.Gift gift);
    }

    public interface OnAddToCartListener {
        void onAddToCart(GiftListResponse.Gift gift);
    }

    public GiftAdapter(List<GiftListResponse.Gift> gifts, Context context, OnGiftClickListener listener, OnAddToCartListener addToCartListener) {
        this.gifts = gifts;
        this.context = context;
        this.onGiftClickListener = listener;
        this.onAddToCartListener = addToCartListener;
    }

    @NonNull
    @Override
    public GiftViewHolder onCreateViewHolder(@NonNull ViewGroup parent, int viewType) {
        View view = LayoutInflater.from(context).inflate(R.layout.item_gift_card, parent, false);
        return new GiftViewHolder(view);
    }

    @Override
    public void onBindViewHolder(@NonNull GiftViewHolder holder, int position) {
        GiftListResponse.Gift gift = gifts.get(position);
        holder.tvGiftName.setText(gift.getName());
        holder.tvGiftPrice.setText("Rs. " + gift.getPrice());
        holder.tvGiftDescription.setText(gift.getDescription());

        // Load image using Glide
        if (gift.getImage() != null && !gift.getImage().isEmpty()) {
            Log.d(TAG, "Loading image: " + gift.getImage());
            Glide.with(context)
                    .load(gift.getImage())
                    .placeholder(R.drawable.gift_placeholder)
                    .error(R.drawable.gift_placeholder)
                    .into(holder.ivGiftImage);
        } else {
            Log.d(TAG, "No image URL for gift: " + gift.getName());
            holder.ivGiftImage.setImageResource(R.drawable.gift_placeholder);
        }

        holder.itemView.setOnClickListener(v -> {
            if (onGiftClickListener != null) {
                onGiftClickListener.onGiftClick(gift);
            }
        });

        holder.btnAddToCart.setOnClickListener(v -> {
            if (onAddToCartListener != null) {
                onAddToCartListener.onAddToCart(gift);
            } else {
                Toast.makeText(context, gift.getName() + " added to cart", Toast.LENGTH_SHORT).show();
            }
        });
    }

    @Override
    public int getItemCount() {
        return gifts != null ? gifts.size() : 0;
    }

    public void updateGifts(List<GiftListResponse.Gift> newGifts) {
        this.gifts = newGifts;
        notifyDataSetChanged();
    }

    static class GiftViewHolder extends RecyclerView.ViewHolder {
        ImageView ivGiftImage;
        TextView tvGiftName;
        TextView tvGiftPrice;
        TextView tvGiftDescription;
        MaterialButton btnAddToCart;

        public GiftViewHolder(@NonNull View itemView) {
            super(itemView);
            ivGiftImage = itemView.findViewById(R.id.iv_gift_image);
            tvGiftName = itemView.findViewById(R.id.tv_gift_name);
            tvGiftPrice = itemView.findViewById(R.id.tv_gift_price);
            tvGiftDescription = itemView.findViewById(R.id.tv_gift_description);
            btnAddToCart = itemView.findViewById(R.id.btn_add_to_cart);
        }
    }
}
