package com.jackelow.jackelo.classes;

/**
 * Created by David on 2/24/2015.
 */
import org.apache.http.HttpResponse;
import org.apache.http.client.ClientProtocolException;
import org.apache.http.client.HttpClient;
import org.apache.http.client.methods.HttpGet;
import org.apache.http.impl.client.DefaultHttpClient;
import org.json.JSONObject;

import java.io.IOException;
import java.lang.Object;
import java.net.URI;
import java.net.URISyntaxException;
import java.lang.String;
import java.io.*;
import com.jackelow.jackelo.classes.getter;

public class apiCaller {

    HttpClient client;
    HttpGet request;

    //Override
    public apiCaller(){
        client = new DefaultHttpClient();
        request = new HttpGet();

    }

    //@Override
    public String getURL(JSONObject params) {

        String api;
        String url = "http://jackelow.gjye.com/api/";
        try {

            api = params.getString("api");
            url+=api;

            if(params.getString("id") != null){
                if(params.getString("id").equals("all")) {
                    // Do nothing
                }
                else{
                    url+=params.getString("id"); // Tag the id along
                }

            }
        }
        catch (Exception e){

        }

        return url;
    }

    //@Override
    public JSONObject apiGet(String params) {


        ByteArrayOutputStream out = new ByteArrayOutputStream();


        HttpResponse response = null;
        try {

            request.setURI(new URI(params));
            getter myGetter = new getter(client);
            //response = client.execute(request);
            String result = (myGetter).execute(request).get();
            return new JSONObject(result);

        } catch (URISyntaxException e) {
            e.printStackTrace();
        } catch (Exception e) {
            e.printStackTrace();
        }

        return null;
    }

    //@Override
    public JSONObject apiGet(JSONObject params) {

        return apiGet( getURL(params));
    }
}


