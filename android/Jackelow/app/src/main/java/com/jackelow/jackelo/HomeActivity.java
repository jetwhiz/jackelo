package com.jackelow.jackelo;

import android.app.ProgressDialog;
import android.content.Intent;
import android.graphics.Bitmap;
import android.net.Uri;
import android.os.AsyncTask;
import android.support.v4.app.Fragment;
import android.support.v7.app.ActionBarActivity;
import android.os.Bundle;
import android.support.v7.widget.LinearLayoutManager;
import android.support.v7.widget.RecyclerView;
import android.util.Log;
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
import android.widget.RelativeLayout;
import android.widget.TextView;

import com.jackelow.jackelo.classes.apiCaller;
import com.jackelow.jackelo.classes.imageGetter;
import com.jackelow.jackelo.viewClasses.MySimpleArrayAdapter;
import com.jackelow.jackelo.viewClasses.SplashActivity;

import org.json.JSONArray;
import org.json.JSONObject;

import java.net.URL;
import java.util.ArrayList;
import java.util.List;
import com.jackelow.jackelo.viewClasses.RVAdapter;



public class HomeActivity extends ActionBarActivity implements AdapterView.OnItemClickListener{

    List<String> li;
    ArrayList<JSONObject> myEvents;
    apiCaller myCaller;
    JSONArray myEventIds;

    @Override
    protected void onCreate(Bundle savedInstanceState) {

        // Show loading screen until list of events finally appears
        super.onCreate(savedInstanceState);
        LoadViewTask lvt = new LoadViewTask();
        lvt.execute();


        myCaller = new apiCaller(getApplicationContext()); // Instantiate a new api caller

        final ListView list = new ListView(this);
        list.setOnItemClickListener(this);
        li = new ArrayList<String>();
        myEvents = new ArrayList<JSONObject>();


        JSONObject myJSON = new JSONObject(); // JSON Object used for api calls
        myEventIds = null;

        try {

            myJSON.put("api", "event/");
            JSONObject ret = myCaller.apiGet(myJSON); // Collect all events
            myEventIds = ret.getJSONArray("results");
            lvt.cancel(true);
            generateListView(list);

        } catch (Exception e) {

            e.printStackTrace();
        }

        int curId = 0;
        JSONObject curRet = null;
        lvt.cancel(true);
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
        // Log.i("TAG", "You clicked item " + id + " at position " + position);
        // Here you start the intent to show the contact details
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
    protected void onNewIntent(Intent intent) {
        Uri data = intent.getData();
        if (data != null) {
            String accessToken = data.getQueryParameter("access_token");
            // Use the accessToken.
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

    private class LoadViewTask extends AsyncTask<Void, Integer, Void> {
        protected void onPreExecute()
        {
            setContentView(R.layout.activity_splash); // Set simple splash screen
        }

        //The code to be executed in a background thread.
        @Override
        protected Void doInBackground(Void... params)
        {
            setContentView(R.layout.activity_splash); // Set simple splash screen
            return null;
        }

        //Update the progress
        @Override
        protected void onProgressUpdate(Integer... values)
        {
        }

        //after executing the code in the thread
        @Override
        protected void onPostExecute(Void result)
        {
        }

    }

    // Use JSON results from /events and collect data for each one.
    public void generateListView(ListView list){
        final JSONObject myJSON = new JSONObject();

        setContentView(R.layout.activity_home); // Show the home screen

        RecyclerView rv = (RecyclerView) findViewById(R.id.rv);

        LinearLayoutManager llm = new LinearLayoutManager(this);
        rv.setLayoutManager(llm);
        rv.setHasFixedSize(true);

        getEventsById(myEventIds);
        RVAdapter adapter = new RVAdapter(myEvents);
        rv.setAdapter(adapter);


        //adp.setDropDownViewResource(android.R.layout.simple_dropdown_item_1line);

        //list.setAdapter(adp);
//        list.setLayoutParams(params);


        //rv.addView(list);
        //getEventsById(myEventIds);


    }



}
