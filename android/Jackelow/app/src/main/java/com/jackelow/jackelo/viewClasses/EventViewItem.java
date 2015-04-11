package com.jackelow.jackelo.viewClasses;

import android.graphics.Bitmap;
import android.location.Location;

import com.jackelow.jackelo.classes.apiCaller;

import org.json.JSONArray;
import org.json.JSONObject;

import java.text.DateFormat;
import java.text.SimpleDateFormat;
import java.util.ArrayList;
import java.util.Date;

/**
 * Created by David on 4/7/2015.
 */
public class EventViewItem extends EventListItem {

    ArrayList<Location> locations = new ArrayList<Location>();
    ArrayList<Comment> comments = new ArrayList<Comment>();
    String error = "OK";


    @Override
    public void parseRest(apiCaller caller) {

        getLocations();
        getComments(caller);
        getCategories();

    }

    public void getLocations() {

        JSONArray locsList;

        try {
            locsList = rawJSON.getJSONArray("destinations");
            if(locsList.length() == 0) {
                return;
            }
        }
        catch(Exception e){
            // No locations in the list. Do nada
            return;
        }

        // Iterate through locations and parse
        Location curLoc;
        try {
            for (int i = 0; i < locsList.length(); i++) {
                curLoc = new Location(locsList.getJSONObject(i));
                locations.add(curLoc);
            }
        }
        catch(Exception e){
            // Random exception
            error = "Failure to parse locations in getLocations():"+e.toString();
        }

    }

    // Collect and parse comments for an event
    public void getComments(apiCaller caller) {

        JSONObject apiCall = new JSONObject();

        try{

            JSONArray comIds = caller.getCommentIds(id);

            int curComId;
            for (int i = 0; i < comIds.length(); i++) {

                curComId = comIds.getInt(i);
                JSONObject retFromCall = caller.getComment(this.id, curComId);
                Comment curCom = new Comment(curComId, retFromCall);
                comments.add(curCom);
            }

        }
        catch(Exception e){
            // Throw
            throw new RuntimeException("Runtime Exception thrown when attempting to collect comments");
        }

    }


    public class Location{

        String address;
        String start;
        String end;
        String startF;
        String endF;
        long unixStart;
        long unixEnd;
        int id;
        String cityName;
        String countryName;
        Bitmap thumb;

        public Location(JSONObject myJSON){

            try {
                address = myJSON.getString("address");
                start = myJSON.getString("datetimeStart");
                end = myJSON.getString("datetimeEnd");
                id = myJSON.getInt("cityID");
                cityName = myJSON.getString("cityName");
                countryName = myJSON.getString("countryName");
                String thumbUrl = myJSON.getString("thumb");

                // parse the Dates
                if(start!= null) {
                    SimpleDateFormat formatter = new SimpleDateFormat("yyyy-mm-dd hh:mm:ss");
                    Date fStartDate = formatter.parse(start);
                    Date fEndDate = formatter.parse(end);
                    unixStart = fStartDate.getTime();
                    unixEnd = fEndDate.getTime();

                    SimpleDateFormat outFormatter = new SimpleDateFormat("M d, yyyy");
                    startF = formatter.format(fStartDate);
                    startF = formatter.format(fEndDate);
                }


            }
            catch(Exception e){
                // Throw
                throw new RuntimeException("Location object throwing runtime exception upon construction");
            }

            parseNull();

        }

        // No null Strings
        private void parseNull() {
            if(address == null){
                address = defaultStringValue;
            }
            if(start == null){
                start = defaultStringValue;
            }
            if(end == null){
                end = defaultStringValue;
            }
            if(startF == null){
                startF = defaultStringValue;
            }
            if(endF == null){
                endF = defaultStringValue;
            }
            if(cityName == null){
                cityName = defaultStringValue;
            }
            if(countryName == null){
                countryName = defaultStringValue;
            }
        }

    }

    public class Comment{

        String username;
        String date;
        String dateF;
        long unixDate;
        String message;
        String networkAbbr;
        String network;
        int evtId;
        int comId;

        public Comment(int comId, JSONObject myJSON){

            try{
                username = myJSON.getString("username");
                date = myJSON.getString("datetime");
                message = myJSON.getString("message");
                networkAbbr = myJSON.getString("networkAbbr");
                network = myJSON.getString("network");
                networkAbbr = myJSON.getString("networkAbbr");


                evtId = myJSON.getInt("eventID");

                // parse the Dates
                if(date!= null) {
                    SimpleDateFormat formatter = new SimpleDateFormat("yyyy-mm-dd hh:mm:ss");
                    Date fStartDate = formatter.parse(date);
                    unixDate = fStartDate.getTime();

                    SimpleDateFormat outFormatter = new SimpleDateFormat("M d, yyyy");
                    dateF = formatter.format(fStartDate);
                }

            } catch(Exception e){
                // Throw
                throw new RuntimeException("Comment object throwing runtime exception upon construction");
            }

            parseNull();

        }

        // No null Strings
        private void parseNull() {
            if(username == null){
                username = defaultStringValue;
            }
            if(date == null){
                date = defaultStringValue;
            }
            if(message == null){
                message = defaultStringValue;
            }
            if(networkAbbr == null){
                networkAbbr = defaultStringValue;
            }
            if(network == null){
                network = defaultStringValue;
            }
        }
    }
}
