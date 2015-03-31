package com.jackelow.jackelo.classes;

import android.content.Context;
import android.os.AsyncTask;

import com.jackelow.jackelo.net.PersistentCookieStore;

import org.apache.http.HttpResponse;
import org.apache.http.client.HttpClient;
import org.apache.http.client.methods.HttpGet;
import org.apache.http.impl.client.DefaultHttpClient;
import org.apache.http.protocol.HttpContext;

import java.io.ByteArrayOutputStream;
import java.net.URL;

// import com.jackelow.jackelo.net;

/**
 * Created by David on 3/2/2015.
 */
public class getter extends AsyncTask<HttpGet, Integer, String> {

    HttpClient client;// = getNewHttpClient();
    HttpContext netContext;

    //Override
    public getter(HttpClient inClient, HttpContext inContext){

        super();
        client = inClient;
        netContext = inContext;

    }

    protected String doInBackground(HttpGet... request) {
        HttpResponse response = null;

        try {
            ByteArrayOutputStream out = new ByteArrayOutputStream();
            response = client.execute(request[0], netContext);
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