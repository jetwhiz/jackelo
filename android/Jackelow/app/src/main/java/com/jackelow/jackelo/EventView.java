package com.jackelow.jackelo;

import android.content.Intent;
import android.graphics.Bitmap;
import android.support.v7.app.ActionBarActivity;
import android.os.Bundle;
import android.view.KeyEvent;
import android.view.Menu;
import android.view.MenuItem;
import android.widget.ImageView;
import android.widget.TextView;

import com.jackelow.jackelo.classes.apiCaller;
import com.jackelow.jackelo.classes.imageGetter;
import com.jackelow.jackelo.viewClasses.EventViewItem;

import org.json.JSONArray;
import org.json.JSONObject;

import java.net.URL;


public class EventView extends ActionBarActivity {

    @Override
    protected void onCreate(Bundle savedInstanceState) {

        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_event_view);

        ImageView image = (ImageView) findViewById(R.id.imageView);
        TextView name = (TextView) findViewById(R.id.textView1);
        TextView location = (TextView) findViewById(R.id.textView2);
        TextView desc = (TextView) findViewById(R.id.textView3);

        // Create a new eventview item
        EventViewItem evItem = new EventViewItem();
        Intent i = getIntent();

        int evId = 0;
        try{
            evId = i.getIntExtra("id", 0);
        } catch (Exception e) {
            e.printStackTrace();
            finish();
        }

        // Load the data for the event
        evItem.load(new apiCaller(getApplicationContext()), evId);

        String eventName = "";
        String eventDesc = "";
        String eventDestination = "";
        String eventImageURL = "";

        JSONArray curResults;
        JSONObject curResult;
        JSONArray curDestinations;
        JSONObject curDestination;


        // Populate view FILL THIS IN for new template
        try{

            if(!evItem.location.equals("") && !(evItem.location == null)) {
                image.setImageBitmap(evItem.eventImage);
            }

            name.setText(eventName);
            location.setText(eventDestination);
            desc.setText(eventDesc);

        }catch (Exception e){
            e.printStackTrace();
        }
    }


    @Override
    public boolean onCreateOptionsMenu(Menu menu) {
        // Inflate the menu; this adds items to the action bar if it is present.
        getMenuInflater().inflate(R.menu.menu_event_view, menu);
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

    public boolean onKeyDown(int keyCode, KeyEvent event)  {
        if (keyCode == KeyEvent.KEYCODE_BACK && event.getRepeatCount() == 0) {
            // do something on back.
            finish();
            return true;
        }

        return super.onKeyDown(keyCode, event);
    }
}
