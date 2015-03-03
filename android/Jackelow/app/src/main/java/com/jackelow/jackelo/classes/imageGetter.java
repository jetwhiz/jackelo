package com.jackelow.jackelo.classes;

import android.graphics.Bitmap;
import android.graphics.BitmapFactory;
import android.os.AsyncTask;

import org.apache.http.HttpResponse;
import org.apache.http.client.HttpClient;
import org.apache.http.client.methods.HttpGet;
import org.apache.http.impl.client.DefaultHttpClient;

import java.io.ByteArrayOutputStream;
import java.net.URL;

/**
 * Created by David on 3/2/2015.
 */
public class imageGetter extends AsyncTask<URL, Integer, Bitmap> {

    final HttpClient client = new DefaultHttpClient();

    protected Bitmap doInBackground(URL... urls) {
        HttpResponse response = null;

        try {
            return BitmapFactory.decodeStream(urls[0].openConnection().getInputStream());

        }  catch (Exception e) {
            e.printStackTrace();
        }

        return null;
    }

    protected void onProgressUpdate(Integer... progress) {
        //setProgressPercent(progress[0]);
    }

    protected void onPostExecute(Long result) {
        // showDialog("Downloaded " + result + " bytes");
    }
}