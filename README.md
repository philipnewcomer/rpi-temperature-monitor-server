# Raspberry Pi Temperature Monitor Server

*Monitor the temperature at a remote location using a Raspberry Pi*

This repo contains the server counterpart to the [Temperature Monitor Daemon](https://github.com/philipnewcomer/rpi-temperature-monitor-daemon).

The server is a Laravel app which records temperature readings received from the remote Raspberry Pi device running the [daemon](https://github.com/philipnewcomer/rpi-temperature-monitor-daemon). The server also provides a web interface to view the recorded readings.

## Setup

 1. Install the app in the standard Laravel way.
 2. Configure `REMOTE_SECRET_KEY` in the app's `.env` file.
