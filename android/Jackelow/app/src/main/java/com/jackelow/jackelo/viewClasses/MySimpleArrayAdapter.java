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

        try {
            curResults = curJSON.getJSONArray("results");
            curResult = curResults.getJSONObject(0);
            eventName = curResult.getString("name");

            curDestinations = curResult.getJSONArray("destinations");
            curDestination = curDestinations.getJSONObject(0);
            eventDestination = curDestination.getString("address");
            eventImageURL = curDestination.getString("thumb");//.replaceFirst("s", "");

            eventDesc = curResult.getString("description");


        } catch  (Exception e) {
            e.printStackTrace();
        }

        try{
            URL url = new URL(eventImageURL);
            imageGetter myImGetter = new imageGetter();

            Bitmap bmp = (myImGetter).execute(url).get();
            eventImage.setImageBitmap(bmp);
            name.setText(eventName);
            location.setText(eventDestination);
            description.setText(eventDesc);

        } catch  (Exception e) {
            e.printStackTrace();
        }

        return rowView;
    }
}


