package com.jackelow.jackelo.viewClasses;

/**
 * Created by dgaspard on 3/13/2015.
 */

import android.content.Context;
import android.graphics.Bitmap;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.ArrayAdapter;
import android.widget.ImageView;
import android.widget.TextView;

import com.jackelow.jackelo.R;
import com.jackelow.jackelo.classes.imageGetter;

import org.json.JSONArray;
import org.json.JSONObject;

import java.net.URL;
import java.util.ArrayList;

public class MySimpleArrayAdapter extends ArrayAdapter<JSONObject> {
    private final Context context;
    ArrayList<JSONObject> values;

    public MySimpleArrayAdapter(Context context, ArrayList<JSONObject> values) {

        super(context, R.layout.event_list_item, values);
        this.context = context;
        this.values = values;
    }

    @Override
    public View getView(int position, View convertView, ViewGroup parent) {

        JSONObject curJSON = values.get(position);

        LayoutInflater inflater = (LayoutInflater) context
                .getSystemService(Context.LAYOUT_INFLATER_SERVICE);
        View rowView = inflater.inflate(R.layout.event_list_item, parent, false);

        TextView name = (TextView) rowView.findViewById(R.id.name);
        TextView location = (TextView) rowView.findViewById(R.id.location);
        TextView description = (TextView) rowView.findViewById(R.id.description);
        ImageView eventImage = (ImageView) rowView.findViewById(R.id.eventImage);

        String eventName = "";
        String eventDesc = "";
        String eventDestination = "";
        String eventImageURL = "";

        JSONArray curResults;
        JSONObject curResult;
        JSONArray curDestinations;
        JSONObject curDestination;

        //circleBuff myBuffer = circleBuff(15);

        //if
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
                //eventImage.setImageBitmap(bmp);
            }

            // Take subtrings from fields if necessary and display
            eventName = truncText(eventName, 30);
            eventDestination = truncText(eventDestination, 30);
            eventDesc = truncText(eventDesc, 80);


        } catch  (Exception e) {
            e.printStackTrace();
        }

        // Add data to view fields
        name.setText(eventName);
        location.setText(eventDestination);
        description.setText(eventDesc);


        return rowView;
    }

    // Object representing  a slot in the circular buffer
    private class circleBuffObj{

        int    pos;
        String evtName;
        String loc;
        String desc;
        Bitmap bmp;

        // Constructor for circular buffer
        void circleBuff(){

            evtName = null;
            loc = null;
            desc = null;
            bmp = null;
        }

        // Add an item to the buffer
        void addToBuffer(int pos, String tit){

        }
    }

    private class circleBuff{

        circleBuffObj[] buffer;
        int topP;
        int bottomP;
        int len;

        // Constructor
        private void circleBuff(int inLen){

            len = inLen;
            buffer = new circleBuffObj[inLen];
            bottomP = 0;
            topP = 0;

        }

        // If the requested item is in the buffer pull it
        // else return null
        circleBuffObj pullFromBuffer(int pos){

            if(buffer[pos % len].pos == pos){
                return buffer[pos % len];
            }

            return null;
        }

        // Add info to a buffer slot
        private void addBuffer(int pos, String title, String location, String desc, Bitmap evtImg ){

            int ind = pos % len;

            buffer[ind].pos = pos;
            buffer[ind].evtName = title;
            buffer[ind].loc = location;
            buffer[ind].desc = desc;
            buffer[ind].bmp = evtImg;

        }

    }

    public String truncText(String name, int len){
        if(name.length() > len){
            return name.substring(0,len-3)+"...";
        }
        else{
            return name;
        }

    }
}


