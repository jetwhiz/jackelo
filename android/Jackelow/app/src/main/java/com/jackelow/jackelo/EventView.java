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

import com.jackelow.jackelo.classes.imageGetter;

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

        Intent i = getIntent();
        // Receiving the Data from activity creation
        JSONObject curRet = null;
        try{
            curRet = new JSONObject(i.getStringExtra("json"));
        } catch (Exception e) {
            e.printStackTrace();
            finish();
        }


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
            curResult = curRet;
            eventName = curResult.getString("name");

            curDestinations = curResult.getJSONArray("destinations");

            if(curDestinations.length() > 0) {
                curDestination = curDestinations.getJSONObject(0);
                eventDestination = curDestination.getString("address");
                eventImageURL = curDestination.getString("thumb");//.replaceFirst("s","");
            }

            eventDesc = curResult.getString("description");
        }
        catch (Exception e){}

        // Populate view
        try{

            if(!eventDestination.equals("")) {
                URL url = new URL(eventImageURL);
                imageGetter myImGetter = new imageGetter();

                Bitmap bmp = (myImGetter).execute(url).get();
                image.setImageBitmap(bmp);
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
