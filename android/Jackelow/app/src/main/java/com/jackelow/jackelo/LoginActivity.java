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

import com.jackelow.jackelo.R;
import com.jackelow.jackelo.net.PersistentCookieStore;

public class LoginActivity extends ActionBarActivity {

    PersistentCookieStore myCookieStore;


    @Override
    protected void onCreate(Bundle savedInstanceState) {


        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_login);
        myCookieStore = new PersistentCookieStore(getApplicationContext());
        myCookieStore.clear();

        if(true){
            String _url =  "https://jackelow.gjye.com/login/mobile/";
            Intent i = new Intent(Intent.ACTION_VIEW);
            i.setData( Uri.parse(_url) );
            startActivity(i);
        }
    }

    // Collect the cookie
    @Override
    protected void onNewIntent(Intent intent) {

        Uri data = intent.getData();

        if (data != null) {
            String accessToken = data.getQueryParameter("sessionID");
            // Use the accessToken.
            BasicClientCookie loginCookie = new BasicClientCookie("sessionID",accessToken);
            loginCookie.setDomain("jackelow.gjye.com");
            myCookieStore.addCookie(loginCookie); // Add cookie to persistent cookie store
            goToHomeScreen();

        }

        finish();
    }


    @Override
    public boolean onCreateOptionsMenu(Menu menu) {
        // Inflate the menu; this adds items to the action bar if it is present.
        getMenuInflater().inflate(R.menu.menu_login, menu);
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



    private void goToHomeScreen() {
        try {

            Intent homeScreen = new Intent(getApplicationContext(), HomeActivity.class);
            startActivity(homeScreen);

        } catch (Exception e) {
            e.printStackTrace();
        }
    }
}
