package com.jackelow.jackelo.classes;

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
public class getter extends AsyncTask<HttpGet, Integer, String> {

    final HttpClient client = new DefaultHttpClient();

    //Override
    public getter(HttpClient client){
        super();
    }

    protected String doInBackground(HttpGet... request) {
        HttpResponse response = null;

        try {
            ByteArrayOutputStream out = new ByteArrayOutputStream();
            response = client.execute(request[0]);
            response.getEntity().writeTo(out);
            return out.toString();

        }  catch (Exception e) {
            e.printStackTrace();
        }

        return "";
    }

    protected void onProgressUpdate(Integer... progress) {
        //setProgressPercent(progress[0]);
    }

    protected void onPostExecute(Long result) {
        // showDialog("Downloaded " + result + " bytes");
    }
}