package com.jackelow.jackelo.viewClasses;

import android.content.Context;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.ArrayAdapter;
import android.widget.TextView;

import com.jackelow.jackelo.EventView;
import com.jackelow.jackelo.R;

import java.util.List;

/**
 * Created by bjoffe on 4/12/2015.
 */
public class LocAdapter extends ArrayAdapter<EventViewItem.Location> {

   List<EventViewItem.Location> locations;
//    EventView activity;

    public LocAdapter(Context context, int textViewResourceId) {
        super(context, textViewResourceId);
    }

    public LocAdapter(Context context, int resource, List<EventViewItem.Location> items) {
        super(context, resource, items);
        this.locations = items;
    }

    @Override
    public View getView(int position, View convertView, ViewGroup parent) {

        View v = convertView;
        if (v == null) {

            LayoutInflater vi;
            vi = LayoutInflater.from(getContext());
            v = vi.inflate(R.layout.locations_dates, null);

        }

        EventViewItem.Location p = getItem(position);

        if (p != null) {

        TextView address = (TextView) v.findViewById(R.id.address2);
        TextView cityName = (TextView) v.findViewById(R.id.location2);
        TextView startF = (TextView) v.findViewById(R.id.start_date);
        TextView endF = (TextView) v.findViewById(R.id.end_date);

        if (address != null) {
            address.setText(p.address);
        }

        if (cityName != null) {
            cityName.setText(p.cityName);
        }
        if (startF != null) {

            startF.setText(p.startF);
        }
        if (endF != null) {

            endF.setText(p.endF);
        }
    }
        return v;
}
}

