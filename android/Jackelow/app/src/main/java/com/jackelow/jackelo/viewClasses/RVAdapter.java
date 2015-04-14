package com.jackelow.jackelo.viewClasses;

import android.app.Activity;
import android.content.Context;
import android.content.Intent;
import android.graphics.Bitmap;
import android.support.v7.widget.CardView;
import android.support.v7.widget.RecyclerView;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.ImageView;
import android.widget.TextView;
import android.widget.Toast;

import com.jackelow.jackelo.EventView;
import com.jackelow.jackelo.HomeActivity;
import com.jackelow.jackelo.R;
import com.jackelow.jackelo.classes.imageGetter;

import org.json.JSONArray;
import org.json.JSONObject;

import java.net.URL;
import java.text.SimpleDateFormat;
import java.util.Date;
import java.util.List;

public class RVAdapter extends RecyclerView.Adapter<RVAdapter.EventViewHolder> {

    public static class EventViewHolder extends RecyclerView.ViewHolder {

        CardView cv;
        TextView name;
        TextView owner;
        TextView location;
        TextView date;
        TextView description;
        ImageView eventImage;
        View view;
        HomeActivity myAct;

        /*private ClickListener clickListener;*/

        EventViewHolder(View itemView, final HomeActivity myAct, final int id) {

            super(itemView);
            cv = (CardView) itemView.findViewById(R.id.cv);
            name = (TextView) itemView.findViewById(R.id.name);
            owner = (TextView) itemView.findViewById(R.id.owner);
            location = (TextView) itemView.findViewById(R.id.location);
            date = (TextView) itemView.findViewById(R.id.date);
            description = (TextView) itemView.findViewById(R.id.description);
            eventImage = (ImageView) itemView.findViewById(R.id.eventImage);
            this.view = itemView;
            this.myAct = myAct;
        }

        // Set the onClick listener for the view
        public void setClick(int i){

            final int me = i;
            view.setOnClickListener(new View.OnClickListener(){
                @Override
                public void onClick(final View view) {
                    try {

                        myAct.goToEvent(me);

                    } catch (Exception e) {

                        e.printStackTrace();
                    }
                }

            });
        }



    }

    EventList myEvents;
    HomeActivity homeActivity;

    public RVAdapter(EventList myEvents, HomeActivity homeActivity){

        this.myEvents = myEvents;
        this.homeActivity = homeActivity;
    }

    @Override
    public void onAttachedToRecyclerView(RecyclerView recyclerView) {
        super.onAttachedToRecyclerView(recyclerView);
    }

    @Override
    public EventViewHolder onCreateViewHolder(ViewGroup viewGroup, int i) {
            View v = LayoutInflater.from(viewGroup.getContext()).inflate(R.layout.cardview_activity, viewGroup, false);
            EventViewHolder pvh = new EventViewHolder(v, homeActivity, i);
            return pvh;
    }

    @Override
    public void onBindViewHolder(EventViewHolder eventViewHolder, int i) {

            eventViewHolder.setClick(i);

            EventListItem curEvent = myEvents.get(i);

            String eventName = curEvent.name;
            String eventOwner = curEvent.owner;
            String eventDesc = curEvent.description;
            String location = curEvent.location;
            String eventDate = curEvent.date;
            String eventDestination = curEvent.location;
            Bitmap eventImage = curEvent.eventImage;

            JSONArray curResults;
            JSONObject curResult;
            JSONArray curDestinations;
            JSONObject curDestination;


            // Take substrings from fields if necessary and display
//            eventName = truncText(eventName, 30);
//            eventDestination = truncText(eventDestination, 30);
//            eventDesc = truncText(eventDesc, 80);
        try {
        if(eventDate!= null) {
            SimpleDateFormat formatter = new SimpleDateFormat("yyyy-mm-dd hh:mm:ss");
            Date fStartDate = formatter.parse(eventDate);
            SimpleDateFormat outFormatter = new SimpleDateFormat("MMM d, yyyy, hh:mm:ss");
            eventDate = outFormatter.format(fStartDate);
        }

            eventViewHolder.name.setText(eventName);
            eventViewHolder.owner.setText(eventOwner);
            eventViewHolder.location.setText(eventDestination);
            eventViewHolder.description.setText(eventDesc);
            eventViewHolder.date.setText(eventDate);
            if(eventImage != null){
                eventViewHolder.eventImage.setImageBitmap(eventImage);
            }

        }
        catch(Exception e){
            // Throw
            throw new RuntimeException("Date object throwing runtime exception upon construction");
        }
    }

    public String truncText(String name, int len){
        try {
            if (name.length() > len) {
                return name.substring(0, len - 3) + "...";
            } else {
                return name;
            }
        } catch (Exception e) {

            e.printStackTrace();
        }

        return null;

    }



    @Override
    public int getItemCount() {
        return myEvents.numTotalIDs;
    }
}
