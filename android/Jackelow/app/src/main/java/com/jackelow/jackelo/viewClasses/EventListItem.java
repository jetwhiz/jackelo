package com.jackelow.jackelo.viewClasses;

import android.graphics.Bitmap;

import com.jackelow.jackelo.classes.apiCaller;
import com.jackelow.jackelo.classes.imageGetter;

import org.json.JSONArray;
import org.json.JSONObject;

import java.net.URL;

class EventListItem {

    String name;
    String location;
    String description;
    Bitmap eventImage;
    int id;
    JSONObject rawJSON;


    EventListItem(){
        // Default constructor used when EventList is first constructing the buffer
    }

    EventListItem(int id){
        this.id = id;
    }

    EventListItem(String name, String location, String description, Bitmap eventImage, int ID, JSONObject rawJSON) {
        this.name = name;
        this.location = location;
        this.description = description;
        this.eventImage = eventImage;
    }

    public void load(apiCaller myCaller, int i){

        id = i;
        rawJSON = myCaller.getEvent(id);
        String eventImageURL = "";
        location = "";


        try {
            name = (rawJSON.getString("name") == null || rawJSON.getString("name").equals("")) ?
                    "(No title)" : rawJSON.getString("name");
            description = (rawJSON.getString("name") == null || rawJSON.getString("name").equals("")) ?
                    "(No description)" : rawJSON.getString("name");


            JSONArray curDestinations = rawJSON.getJSONArray("destinations");

            for (int j = 0; j < curDestinations.length(); j++){
                JSONObject curDestination = curDestinations.getJSONObject(j);
                location += (curDestination.getString("address"));

                if(i < (curDestinations.length() - 1)){
                    location += " - ";
                }

                if(eventImageURL.equals("")) {
                    eventImageURL = curDestination.getString("thumb");//.replaceFirst("s", "");
                }
            }

            if(!eventImageURL.equals("")){

                imageGetter myImGetter = new imageGetter();
                URL url = new URL(eventImageURL);
                eventImage = (myImGetter).execute(url).get();

            }
        }catch (Exception e) {

            e.printStackTrace();
        }


    }

    public void clear(){
        name = null;
        location = null;
        description = null;
        eventImage = null;
    }
}