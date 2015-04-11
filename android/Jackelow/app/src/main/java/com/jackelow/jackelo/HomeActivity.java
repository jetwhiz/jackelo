package com.jackelow.jackelo;

import android.app.ProgressDialog;
import android.content.Context;
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
import com.jackelow.jackelo.viewClasses.EventList;
import com.jackelow.jackelo.viewClasses.MySimpleArrayAdapter;
import com.jackelow.jackelo.viewClasses.SplashActivity;

import org.json.JSONArray;
import org.json.JSONObject;

import java.net.URL;
import java.util.ArrayList;
import java.util.List;
import com.jackelow.jackelo.viewClasses.RVAdapter;



public class HomeActivity extends ActionBarActivity {

    List<String> li;
    EventList myEvents;
    apiCaller myCaller;
    int numIDsPerLoad = 30;

    @Override
    protected void onCreate(Bundle savedInstanceState) {

        // Show loading screen until list of events finally appears
        super.onCreate(savedInstanceState);
        LoadViewTask lvt = new LoadViewTask();
        lvt.execute();


        myCaller = new apiCaller(getApplicationContext()); // Instantiate a new api caller

        li = new ArrayList<String>();
        JSONObject myJSON = new JSONObject();


        int curId = 0;
        JSONObject curRet = null;
        lvt.cancel(true);
        generateListView();

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

    // Use JSON results from /events and collect data for each one.
    public void getEventsById (JSONArray myIds)
    {

    }

    private class LoadViewTask extends AsyncTask<Void, Integer, Void> {
        protected void onPreExecute()
        {
            // setContentView(R.layout.activity_splash); // Set simple splash screen
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
    public void generateListView(){

        myEvents = new EventList(numIDsPerLoad, myCaller);
        // myEvents.loadAll(0,1); // Load ids starting at 0, load forward
        setContentView(R.layout.activity_home); // Show the home screen

        RecyclerView rv = (RecyclerView) findViewById(R.id.rv);

        LinearLayoutManager llm = new LinearLayoutManager(this);
        rv.setLayoutManager(llm);
        rv.setHasFixedSize(true);

        RVAdapter adapter = new RVAdapter(myEvents, this);
        rv.setAdapter(adapter);



    }

    // Go to the events EventView screen
    public void goToEvent(int pos){

        Intent eventViewScreen = new Intent(getApplicationContext(), EventView.class);
        int id = myEvents.getEventId(pos);
        JSONObject myEvent = myCaller.getEvent(id);
        eventViewScreen.putExtra("id", id);
        // eventViewScreen.putExtra("json", myEvent.toString());

        startActivity(eventViewScreen);
    }

}
