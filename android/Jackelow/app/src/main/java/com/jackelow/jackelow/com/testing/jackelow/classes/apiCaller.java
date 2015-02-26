package com.jackelow.jackelow.com.testing.jackelow.classes;

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
import java.lang.String

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
        try {

            params.getString("api");
        }
        catch (Exception e){

        }

        if api.
        
        return true;
    }

    //@Override
    public JSONObject apiGet(String params) {

        HttpResponse response = null;
        try {

            request.setURI(new URI(params));
            response = client.execute(request);
        } catch (URISyntaxException e) {
            e.printStackTrace();
        } catch (ClientProtocolException e) {
            // TODO Auto-generated catch block
            e.printStackTrace();
        } catch (IOException e) {
            // TODO Auto-generated catch block
            e.printStackTrace();
        }
        return new JSONObject(response);
    }

}


