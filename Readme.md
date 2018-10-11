## Kerberos.io Dashboard


#### Screenshot:

Show multiple Kerberos.io surveillance camera streams next to each other in a browser. Each stream links to the separate Kerberos.io dashboard. Recent camera events show as a timestamp overlay ontop of each stream.

![screenshot](https://raw.githubusercontent.com/dtbaker/kerberos-dashboard/master/cameracreenshot.jpg)

#### Getting started

1. We assume you are already running multiple Kerberos.io camera instances. If not, then you need to start there first. I like to match port numbers with camera numbers and leave port 80 free for the webcam dashboard (see end of doc).

    ```
    #!/bin/bash

    # Script to boot multiple Kerberos surveillance instances.

    # Build a local camera docker image (takes a while!)
    docker build -t cameras -d kerberos/kerberos
    # Start camera 1
    docker run --restart always -d -p 81:80 -p 91:8889 --name camera1 camera
    docker run --restart always -d -p 82:80 -p 92:8889 --name camera2 camera
    docker run --restart always -d -p 83:80 -p 93:8889 --name camera3 camera
    docker run --restart always -d -p 84:80 -p 94:8889 --name camera4 camera
    ```
1. Checkout this repo: `git clone git@github.com:dtbaker/kerberos-dashboard.git`
1. Adjust the camera configuration in `app/config.json` (these are the cameras that will show up on your dashboard, the username and password is required to get access to the recent camera events to show on the screen)
    ```
    {
      "servers": [
        {
          "name": "Cam 1",
          "ip": "192.168.0.142",
          "port": "81",
          "streamport": "91",
          "username": "USERNAMEHERE",
          "password": "PASSWORDHERE"
        },
        {
          "name": "Cam 2",
          "ip": "192.168.0.142",
          "port": "82",
          "streamport": "92",
          "username": "USERNAMEHERE",
          "password": "PASSWORDHERE"
        },
        {
          "name": "Cam 3",
          "ip": "192.168.0.142",
          "port": "83",
          "streamport": "93",
          "username": "USERNAMEHERE",
          "password": "PASSWORDHERE"
        },
        {
          "name": "Cam 4",
          "ip": "192.168.0.142",
          "port": "84",
          "streamport": "94",
          "username": "USERNAMEHERE",
          "password": "PASSWORDHERE"
        }
      ]
    }
    ```
1. Build a Dashboard docker image:
    ```
    docker build -t cameradashboard -f Dockerfile  .
    ```
1. Start the dashboard:
    ```
    docker run --restart always -d -p 80:80 --name dashboard cameradashboard
    ```

#### Todo:

The "recent history" overlay needs some work. Sometimes it works and sometimes it doesn't. I need to make some changes to the kerberos `web` PHP code to make the API a bit easier to get this information. Once those changes have been made I'll update this repo with instructions.

