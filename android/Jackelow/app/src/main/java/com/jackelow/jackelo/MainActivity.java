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


public class MainActivity extends  Activity{

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
        myCookieStore.clear();
        List<Cookie> cookieList = myCookieStore.getCookies();

        // No cookies, got to login screen
        if(cookieList.size() != 1){
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


}
