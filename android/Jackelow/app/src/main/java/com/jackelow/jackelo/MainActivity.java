package com.jackelow.jackelo;

import android.content.Context;
import android.content.Intent;
import android.graphics.Bitmap;
import android.graphics.BitmapFactory;
import android.support.v7.app.ActionBarActivity;
import android.support.v7.app.ActionBar;
import android.support.v4.app.Fragment;
import android.support.v4.app.FragmentManager;
import android.support.v4.app.FragmentTransaction;
import android.support.v4.app.FragmentPagerAdapter;
import android.os.Bundle;
import android.support.v4.view.ViewPager;
import android.util.Log;
import android.view.Gravity;
import android.view.LayoutInflater;
import android.view.Menu;
import android.view.MenuItem;
import android.view.View;
import android.view.ViewGroup;
import android.widget.AdapterView;
import android.widget.ArrayAdapter;
import android.widget.Button;
import android.widget.ImageView;
import android.widget.ListView;
import android.widget.RadioGroup;
import android.widget.RelativeLayout;
import android.widget.TextView;
import com.jackelow.jackelo.classes.apiCaller;
import com.jackelow.jackelo.classes.imageGetter;
import com.jackelow.jackelo.viewClasses.MySimpleArrayAdapter;

import java.io.*;
import java.net.URL;
import java.util.ArrayList;
import java.util.HashMap;
import java.util.List;

import java.util.ArrayList;
import java.util.List;
import android.os.Bundle;
import android.app.Activity;
import android.view.Menu;
import android.view.View;
import android.widget.ArrayAdapter;
import android.widget.Button;
import android.widget.ListView;
import android.widget.RelativeLayout;
import android.widget.RelativeLayout.LayoutParams;

import org.json.JSONArray;
import org.json.JSONObject;


public class MainActivity extends ActionBarActivity implements AdapterView.OnItemClickListener {

    List<String> li;
    ArrayList<JSONObject> myEvents;
    apiCaller myCaller;

    @Override
    protected void onCreate(Bundle savedInstanceState) {


        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_main); // Show the home screen

        myCaller = new apiCaller(); // Instantiate a new api caller

        final Button show = (Button) findViewById(R.id.button1);
        final RelativeLayout rel = (RelativeLayout) findViewById(R.id.rel);

        final ListView list = new ListView(this);
        list.setOnItemClickListener(this);
        li = new ArrayList<String>();
        myEvents = new ArrayList<JSONObject>();


        // TODO: Take out button and load events automatically
        show.setOnClickListener(new View.OnClickListener() {
            public void onClick(View v) {
                // Perform action on click

                JSONObject myJSON = new JSONObject(); // JSON Object used for api calls

                try {
                    myJSON.put("api", "event/");
                    JSONObject ret = myCaller.apiGet(myJSON); // Collect all events
                    JSONArray myResults = null;
                    myResults = ret.getJSONArray("results");
                    getEventsById(myResults);
                    generateListView(list, rel);

                } catch (Exception e) {
                    e.printStackTrace();
                }

                int curId = 0;
                JSONObject curRet = null;


            }
        });
    }




    @Override
    public boolean onCreateOptionsMenu(Menu menu) {
        // Inflate the menu; this adds items to the action bar if it is present.
        getMenuInflater().inflate(R.menu.menu_main, menu);
        return true;
    }

    @Override
    public boolean onOptionsItemSelected(MenuItem item) {
        // Handle action bar item clicks here. The action bar will
        // automatically handle clicks on the Home/Up button, so long
        // as you specify a parent activity in AndroidManifest.xml.
        int id = item.getItemId();

        //noinspection SimplifiableIfStatement
        if (id == R.id.action_settings) {
            return true;
        }

        return super.onOptionsItemSelected(item);
    }

    /**
     * A placeholder fragment containing a simple view.
     */
    public static class PlaceholderFragment extends Fragment {

        public PlaceholderFragment() {
        }

        @Override
        public View onCreateView(LayoutInflater inflater, ViewGroup container,
                                 Bundle savedInstanceState) {
            View rootView = inflater.inflate(R.layout.fragment_main, container, false);
            return rootView;
        }
    }

    public void onItemClick(AdapterView<?> l, View v, int position, long id) {
        Log.i("TAG", "You clicked item " + id + " at position " + position);
        setContentView(R.layout.eventview);
        // Here you start the intent to show the contact details
        JSONObject curRet = myEvents.get((int) id);

        ImageView image = (ImageView) findViewById(R.id.imageView);
        TextView name = (TextView) findViewById(R.id.textView1);
        TextView location = (TextView) findViewById(R.id.textView2);
        TextView desc = (TextView) findViewById(R.id.textView3);


        String eventName = "";
        String eventDesc = "";
        String eventDestination = "";
        String eventImageURL = "";

        JSONArray curResults;
        JSONObject curResult;
        JSONArray curDestinations;
        JSONObject curDestination;

        // Get data to poulate view
        try{
            curResults = curRet.getJSONArray("results");
            curResult = curResults.getJSONObject(0);
            eventName = curResult.getString("name");

            curDestinations = curResult.getJSONArray("destinations");
            curDestination = curDestinations.getJSONObject(0);
            eventDestination = curDestination.getString("address");
            eventImageURL = curDestination.getString("thumb");//.replaceFirst("s","");

            eventDesc = curResult.getString("description");
        }
        catch (Exception e){}

        // Populate view
        try{
            URL url = new URL(eventImageURL);
            imageGetter myImGetter = new imageGetter();

            Bitmap bmp = (myImGetter).execute(url).get();
            image.setImageBitmap(bmp);
            name.setText(eventName);
            location.setText(eventDestination);
            desc.setText(eventDesc);

        }catch (Exception e){
            e.printStackTrace();
        }
    }

    // Use JSON results from /events and collect data for each one.
    public void getEventsById (JSONArray myIds)
    {
        JSONObject myJSON = new JSONObject();
        JSONObject curRet = new JSONObject();
        JSONObject[] myRets;

        JSONArray curResults;
        JSONObject curResult;
        JSONArray curDestinations;
        JSONObject curDestination;

        int curId;
        String eventName;
        String eventDestination;
        String eventDesc;
        myEvents.clear();

        for(int i = myIds.length()-1; i>=0 ;i--){

            try {
                myJSON.put("api", "event/");
                curId = myIds.getInt(i);
                myJSON.put("id", curId);
                curRet = myCaller.apiGet(myJSON);
                myEvents.add(curRet);

                curResults = curRet.getJSONArray("results");
                curResult = curResults.getJSONObject(0);
                eventName = curResult.getString("name");

                curDestinations = curResult.getJSONArray("destinations");
                curDestination = curDestinations.getJSONObject(0);
                eventDestination = curDestination.getString("address");

                eventDesc = curResult.getString("description");

                li.add("Event: "+eventName+"\nLocation: "+eventDestination+"\nDescription: "+eventDesc);
            }catch (Exception e){}

        }
    }

    // Use JSON results from /events and collect data for each one.
    public void generateListView(ListView list, RelativeLayout rel){
        final JSONObject myJSON = new JSONObject();
//        final RelativeLayout.LayoutParams params=new RelativeLayout.LayoutParams
//                ((int) RadioGroup.LayoutParams.WRAP_CONTENT,(int) RadioGroup.LayoutParams.WRAP_CONTENT);
//
//        params.leftMargin = 10;
//        params.topMargin = 150;
//        params.height = 500;

        String[] trash = {};
        ArrayAdapter<JSONObject> adp = new MySimpleArrayAdapter(getBaseContext(), myEvents);
        adp.setDropDownViewResource(android.R.layout.simple_dropdown_item_1line);

        list.setAdapter(adp);
//        list.setLayoutParams(params);

        rel.addView(list);


    }

}
