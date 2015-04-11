package com.jackelow.jackelo.viewClasses;

import android.graphics.Bitmap;

import com.jackelow.jackelo.classes.apiCaller;
import com.jackelow.jackelo.classes.imageGetter;

import org.json.JSONArray;
import org.json.JSONObject;

import java.net.URL;
import java.util.ArrayList;

public class EventListItem {

    public String name;
    public String location;
    public String description;
    public Bitmap eventImage;
    public int id;
    public JSONObject rawJSON;
    public ArrayList<Category> categories = new ArrayList<Category>();
    public String defaultStringValue = "";


    EventListItem(){
        // Default constructor used when EventList is first constructing the buffer
    }

    EventListItem(int id){
        this.id = id;
    }

//    EventListItem(String name, String location, String description, Bitmap eventImage, int ID, JSONObject rawJSON) {
//        this.name = name;
//        this.location = location;
//        this.description = description;
//        this.eventImage = eventImage;
//    }

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

    parseRest(myCaller);


    }

    // Load information specific to a subclass of EventListItem
    public void parseRest(apiCaller caller){

    }



    // Parse out the categories and place them in individual structures
    public void getCategories() {
        try{

            JSONArray cats = rawJSON.getJSONArray("categories");
            for (int i = 0; i < cats.length(); i++) {

                JSONObject curCatJSON = cats.getJSONObject(i);
                Category curCat = new Category(curCatJSON);
                categories.add(curCat);
            }

        }
        catch(Exception e){
            // Throw
            throw new RuntimeException("Runtime Exception thrown when attempting to collect categories getCategories()");
        }
    }


    public class Category {

        int id;
        String name;

        // Simple constructor
        public Category(JSONObject myJSON){
            try{

                id = myJSON.getInt("categoryID");
                name = myJSON.getString("name");

                parseNull();
                parseNull();

            } catch(Exception e){
                // Throw
                throw new RuntimeException("Category object throwing runtime exception upon construction");
            }
        }

        // No null Strings
        private void parseNull() {
            if (name == null) {
                name = defaultStringValue;
            }
        }
    }

    public void clear(){
        name = null;
        location = null;
        description = null;
        eventImage = null;
    }
}