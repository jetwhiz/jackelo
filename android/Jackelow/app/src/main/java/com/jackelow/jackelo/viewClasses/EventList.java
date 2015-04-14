package com.jackelow.jackelo.viewClasses;

import com.jackelow.jackelo.classes.apiCaller;

import org.json.JSONArray;
import org.json.JSONObject;
import java.lang.Math;
import java.util.ArrayList;
import java.util.EmptyStackException;
import java.util.List;

/**
 * Created by David on 3/26/2015.
 */
public class EventList {

    int bufferSize;
    JSONArray ids;
    int idsLen;
    int numTotalIDs;
    ArrayList<EventListItem> buffer;
    int curTop;
    int curBottom;
    apiCaller myApiCaller;

    // Constructor
    public EventList(int inLen, apiCaller caller){

        this.ids = new JSONArray();
        idsLen = ids.length();
        curTop = -1;
        curBottom = -1;
        myApiCaller = caller;
        bufferSize = inLen;

        spawnBuffer();
        getNumTotalIDs(); // Get the number of IDs on the server
    }

    private void spawnBuffer() {
        buffer  = new ArrayList<EventListItem>();
    }

    // Place newly obtained Ids one by one into the buffer
    private void addToIds(JSONArray newEventIds) {
        try{
            for(int i = 0 ; i < newEventIds.length(); i++){

                ids.put(newEventIds.getInt(i));
            }
        }catch (Exception e) {

            e.printStackTrace();
        }
    }

    // Clear the list
    public void clear() {

        for (int i = 0; i < bufferSize; i++) {
            buffer.clear();
        }
    }

    // Get an item
    public EventListItem get(int pos){

        try {
            if (pos > curBottom) {
                idsLen += loadIds(pos);
                loadMore(pos);

                curTop = 0;
                curBottom = curTop + buffer.size() - 1;
            }

            return buffer.get(pos);
        }
        catch (Exception e) {

            e.printStackTrace();
        }

        return null;

    }

    // Load more events
    private void loadMore(int pos) {

        int numToLoad = Math.min(bufferSize, numTotalIDs - pos);

        for(int i = pos; i < numToLoad; i++){
            try{
                EventListItem newItem = new EventListItem();
                newItem.load(myApiCaller, ids.getInt(i));
                buffer.add(newItem);
            }catch (Exception e) {

                e.printStackTrace();
            }
        }

    }

    // Load ids into id array
    private int loadIds(int pos) {
        try{

            JSONObject myJSON = new JSONObject();

            myJSON.put("api", "event");
            myJSON.put("start", pos);
            myJSON.put("limit", bufferSize);

            JSONObject ret = myApiCaller.apiGet(myJSON); // Collect all events
            JSONArray newEventIds = ret.getJSONArray("results");

            addToIds(newEventIds);
            return newEventIds.length();

        }catch (Exception e) {

            e.printStackTrace();
        }

        return Integer.parseInt(null);
    }


    // Get the number of events total from the server
    public void getNumTotalIDs(){

        try {
            JSONObject myJSON = new JSONObject();

            myJSON.put("api", "event");
            myJSON.put("id", "count");

            JSONObject ret = myApiCaller.apiGet(myJSON); // Collect all events
            JSONArray results = ret.getJSONArray("results");
            JSONObject firstResult = results.getJSONObject(0);
            numTotalIDs = firstResult.getInt("noninfo");// + firstResult.getInt("info");

        }catch (Exception e) {

            e.printStackTrace();
        }
    }

    // Return the id of buffer position 3
    public int getEventId(int pos) {
        return buffer.get(pos).id;
    }
}
