package com.jackelow.jackelo;

import android.content.Context;
import android.content.Intent;
import android.graphics.Bitmap;
import android.graphics.BitmapFactory;
import android.net.Uri;
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
import com.jackelow.jackelo.net.PersistentCookieStore;
import com.jackelow.jackelo.viewClasses.MySimpleArrayAdapter;
import android.content.SharedPreferences;

import java.io.*;
import java.net.URL;
import java.util.ArrayList;
import java.util.Date;
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

import org.apache.http.client.CookieStore;
import org.apache.http.cookie.Cookie;
import org.apache.http.impl.client.BasicCookieStore;
import org.apache.http.impl.cookie.BasicClientCookie;
import org.json.JSONArray;
import org.json.JSONObject;


public class MainActivity extends ActionBarActivity {

    List<String> li;
    ArrayList<JSONObject> myEvents;
    apiCaller myCaller;
    JSONArray myEventIds;
    PersistentCookieStore myCookieStore;

    @Override
    protected void onCreate(Bundle savedInstanceState) {


        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_main); // Show the home screen

        // File cookieText = new File(getApplicationContext().getFilesDir(), "jackelow.session.cookie");

        myCookieStore = new PersistentCookieStore(getApplicationContext());
        myCookieStore.clear();
        List<Cookie> cookieList = myCookieStore.getCookies();

        // No cookies, got to login screen
        if(cookieList.size() == 0){
            goToLoginScreen();
        }
        else{

            // Go to the home screen
            goToHomeScreen();

        }


    }

    // Go to the home activity
    private void goToLoginScreen() {
        try {

            Intent loginScreen = new Intent(getApplicationContext(), LoginActivity.class);
            startActivity(loginScreen);

        } catch (Exception e) {

            e.printStackTrace();
        }
    }

    private void goToHomeScreen() {
        try {

            Intent homeScreen = new Intent(getApplicationContext(), HomeActivity.class);
            startActivity(homeScreen);

        } catch (Exception e) {
            e.printStackTrace();
        }
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

        rel.addView(list);


    }

}
