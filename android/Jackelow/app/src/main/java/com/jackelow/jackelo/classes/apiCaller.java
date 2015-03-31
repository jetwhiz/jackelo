package com.jackelow.jackelo.classes;

/**
 * Created by David on 2/24/2015.
 */
import android.content.Context;

import org.apache.http.HttpResponse;
import org.apache.http.HttpVersion;
import org.apache.http.client.ClientProtocolException;
import org.apache.http.client.HttpClient;
import org.apache.http.client.methods.HttpGet;
import org.apache.http.client.protocol.ClientContext;
import org.apache.http.conn.ClientConnectionManager;
import org.apache.http.conn.scheme.PlainSocketFactory;
import org.apache.http.conn.scheme.Scheme;
import org.apache.http.conn.scheme.SchemeRegistry;
import org.apache.http.conn.ssl.SSLSocketFactory;
import org.apache.http.impl.client.DefaultHttpClient;
import org.apache.http.impl.conn.tsccm.ThreadSafeClientConnManager;
import org.apache.http.params.BasicHttpParams;
import org.apache.http.params.HttpParams;
import org.apache.http.params.HttpProtocolParams;
import org.apache.http.protocol.BasicHttpContext;
import org.apache.http.protocol.HTTP;
import org.apache.http.protocol.HttpContext;
import org.json.JSONObject;

import java.io.IOException;
import java.lang.Object;
import java.net.URI;
import java.net.URISyntaxException;
import java.lang.String;
import java.io.*;
import java.security.KeyStore;
import java.util.Iterator;

import com.jackelow.jackelo.classes.getter;
import com.jackelow.jackelo.net.MySSLSocketFactory;
import com.jackelow.jackelo.net.PersistentCookieStore;

public class apiCaller {

    HttpClient client;
    HttpGet request;
    Context appContext;
    PersistentCookieStore myCookieStore;
    HttpContext localContext;

    //Override
    public apiCaller(Context inContext){

        request = new HttpGet();
        appContext = inContext;
        client = getNewHttpClient();

        myCookieStore = new PersistentCookieStore(appContext);
        localContext = new BasicHttpContext();

        localContext.setAttribute(ClientContext.COOKIE_STORE, myCookieStore);

    }

    //@Override
    public String getURL(JSONObject params) {

        String api;
        String url = "https://jackelow.gjye.com/api/";

        try {


            JSONObject jCopy = new JSONObject(params.toString());

            // The api being used should be appended first to the url.
            api = jCopy.getString("api");
            url += (api+"/");
            jCopy.remove("api");


            Iterator<String> keys = jCopy.keys();
            String curKey; // Current key being looked at
            // Iterate through all keys
            while(keys.hasNext()){

                String key = keys.next();

                if(key.equals("id") ){
                    if(jCopy.getString("id").equals("all")) {
                        // Do nothing
                    }
                    else{
                        url+=jCopy.getString("id")+"/"; // Tag the id along
                    }

                }

                // Default api builder: Append key/value to url
                else{
                    url+=(key+"/"+jCopy.getString(key)+"/");
                }
            }


        }
        catch (Exception e){
            e.printStackTrace();
        }

        return url;
    }

    // Quickly pull JSON for an event
    public JSONObject getEvent(int id){
        String url = "https://jackelow.gjye.com/api/event/"+id+"/";
        JSONObject myRet =  apiGet(url);
        JSONObject justEvent;

        try {
            justEvent =  myRet.getJSONArray("results").getJSONObject(0);
            return justEvent;
        }
        catch (Exception e){

            e.printStackTrace();
            justEvent = null;
        }

        return justEvent;
    }

    //@Override
    public JSONObject apiGet(String params) {

        ByteArrayOutputStream out = new ByteArrayOutputStream();

        HttpResponse response = null;
        try { // Use new getter for each call to do asynchronous tasks

            request.setURI(new URI(params));
            getter myGetter = new getter(client, localContext);
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

    //Get a new HTTP client for HTTPS calls
    public HttpClient getNewHttpClient() {
        try {
            KeyStore trustStore = KeyStore.getInstance(KeyStore.getDefaultType());

            trustStore.load(null, null);

            SSLSocketFactory sf = new MySSLSocketFactory(trustStore);
            sf.setHostnameVerifier(SSLSocketFactory.ALLOW_ALL_HOSTNAME_VERIFIER);

            HttpParams params = new BasicHttpParams();
            HttpProtocolParams.setVersion(params, HttpVersion.HTTP_1_1);
            HttpProtocolParams.setContentCharset(params, HTTP.UTF_8);

            SchemeRegistry registry = new SchemeRegistry();
            registry.register(new Scheme("http", PlainSocketFactory.getSocketFactory(), 80));
            registry.register(new Scheme("https", sf, 443));

            ClientConnectionManager ccm = new ThreadSafeClientConnManager(params, registry);

            return new DefaultHttpClient(ccm, params);
        } catch (Exception e) {
            return new DefaultHttpClient();
        }
    }
}


