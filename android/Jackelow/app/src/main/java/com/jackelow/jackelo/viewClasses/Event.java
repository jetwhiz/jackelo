package com.jackelow.jackelo.viewClasses;

class Event {
    String name;
    String location;
    String description;
    int eventImage;

    Event(String name, String location, String description, int eventImage) {
        this.name = name;
        this.location = location;
        this.description = description;
        this.eventImage = eventImage;
    }
}