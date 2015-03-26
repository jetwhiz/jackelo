package com.jackelow.jackelo.viewClasses;

import android.content.Intent;
import android.graphics.Bitmap;
import android.support.v7.widget.CardView;
import android.support.v7.widget.RecyclerView;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.ImageView;
import android.widget.TextView;

import com.jackelow.jackelo.R;
import com.jackelow.jackelo.classes.imageGetter;

import org.json.JSONArray;
import org.json.JSONObject;

import java.net.URL;
import java.util.List;

public class RVAdapter extends RecyclerView.Adapter<RVAdapter.EventViewHolder> {

    public static class EventViewHolder extends RecyclerView.ViewHolder /*implements View.OnClickListener, View.OnLongClickListener*/ {

        CardView cv;
        TextView name;
        TextView location;
        TextView description;
        ImageView eventImage;

        /*private ClickListener clickListener;*/

        EventViewHolder(View itemView) {
            super(itemView);
            cv = (CardView)itemView.findViewById(R.id.cv);
            name = (TextView)itemView.findViewById(R.id.name);
            location = (TextView)itemView.findViewById(R.id.location);
            description = (TextView)itemView.findViewById(R.id.description);
            eventImage = (ImageView)itemView.findViewById(R.id.eventImage);
        }






   /* Interface for handling clicks - both normal and long ones. */
   /*
        public interface ClickListener {

            *//**
             * Called when the view is clicked.
             *
             * @param v view that is clicked
             * @param position of the clicked item
             * @param isLongClick true if long click, false otherwise
             *//*
            public void onClick(View v, int position, boolean isLongClick);

        }

        *//* Setter for listener. *//*
        public void setClickListener(ClickListener clickListener) {
            this.clickListener = clickListener;
        }

        @Override
        public void onClick(View v) {

            // If not long clicked, pass last variable as false.
            clickListener.onClick(v, getPosition(), false);
            JSONObject curRet = myEvents.get((int) id);

            try {

                Intent eventViewScreen = new Intent(getApplicationContext(), EventView.class);
                eventViewScreen.putExtra("json", curRet.toString());
                eventViewScreen.putExtra("id", String.valueOf(myEventIds.getInt(position)));
                startActivity(eventViewScreen);

            } catch (Exception e) {

                e.printStackTrace();
            }
        }

        @Override
        public boolean onLongClick(View v) {

            // If long clicked, passed last variable as true.
            clickListener.onClick(v, getPosition(), true);
            return true;
        }


        */

    }

    List<JSONObject> JSONevents;

    public RVAdapter(List<JSONObject> JSONevents){
        this.JSONevents = JSONevents;
    }

    @Override
    public void onAttachedToRecyclerView(RecyclerView recyclerView) {
        super.onAttachedToRecyclerView(recyclerView);
    }

    @Override
    public EventViewHolder onCreateViewHolder(ViewGroup viewGroup, int i) {
        View v = LayoutInflater.from(viewGroup.getContext()).inflate(R.layout.cardview_activity, viewGroup, false);
        EventViewHolder pvh = new EventViewHolder(v);
        return pvh;
    }

    @Override
    public void onBindViewHolder(EventViewHolder eventViewHolder, int i) {

        JSONObject curJSON = JSONevents.get(i);

        String eventName = "";
        String eventDesc = "";
        String eventDestination = "";
        String eventImageURL = "";

        JSONArray curResults;
        JSONObject curResult;
        JSONArray curDestinations;
        JSONObject curDestination;

        // Parse data from api return
        try {
            curResults = curJSON.getJSONArray("results");
            curResult = curResults.getJSONObject(0);
            eventName = curResult.getString("name");
            curDestinations = curResult.getJSONArray("destinations");
            eventDesc = curResult.getString("description");

            if (curDestinations.length() > 0){
                curDestination = curDestinations.getJSONObject(0);
                eventDestination = curDestination.getString("address");
                eventImageURL = curDestination.getString("thumb");//.replaceFirst("s", "");
            }


        } catch  (Exception e) {
            e.printStackTrace();
        }

        // Display thumb if present for destinations
        try{
            if(!eventImageURL.equals("")){

                imageGetter myImGetter = new imageGetter();
                URL url = new URL(eventImageURL);
                Bitmap bmp = (myImGetter).execute(url).get();
                eventViewHolder.eventImage.setImageBitmap(bmp);
            }

            // Take subtrings from fields if necessary and display
            eventName = truncText(eventName, 30);
            eventDestination = truncText(eventDestination, 30);
            eventDesc = truncText(eventDesc, 80);


        } catch  (Exception e) {
            e.printStackTrace();
        }



        eventViewHolder.name.setText(eventName);
        eventViewHolder.location.setText(eventDestination);
        eventViewHolder.description.setText(eventDesc);
        //eventViewHolder.eventImage.setImageResource(JSONevents.get(i).eventImage);
    }

    public String truncText(String name, int len){
        if(name.length() > len){
            return name.substring(0,len-3)+"...";
        }
        else{
            return name;
        }

    }

    @Override
    public int getItemCount() {
        return JSONevents.size();
    }
}
