# Local MediaMTX testing

Set one shared webhook secret, then start the media server from the repository root:

```bash
export EASTSTREAM_MEDIA_WEBHOOK_SECRET="$(openssl rand -hex 32)"
docker compose up -d
```

Set that exact `EASTSTREAM_MEDIA_WEBHOOK_SECRET` in the backend environment too
(or in `backend/config/app_local.php`). The included status-bridge service polls
MediaMTX's private control API and sends signed online/offline transitions, so
creating an OBS broadcast leaves it in **Preparing** until OBS starts sending video.

Run the backend and frontend as usual. In Creator Studio, choose **Create broadcast**. Then set OBS to:

- Service: **Custom**
- Server: the displayed `rtmp://localhost:1935`
- Stream key: the displayed private key

Start streaming in OBS. After MediaMTX receives the stream, Eaststream marks it
live and its watch page plays the HLS manifest at `http://localhost:8888/<stream-key>/index.m3u8`.

The watch page maintains a short-lived browser viewer session (45-second expiry,
15-second heartbeat) and receives chat, state, and viewer-count updates over SSE.

## Camera live

After pulling the latest configuration, restart MediaMTX so the browser WebRTC port is exposed:

```bash
docker compose up -d --force-recreate
```

In Eaststream, open **Creator Studio → Camera live**, enter a title, and select **Enable camera & go live**. Allow browser camera/microphone permission when prompted. This works on `http://localhost:3000`; production browser camera streaming requires HTTPS and a publicly reachable MediaMTX WebRTC/ICE configuration.

This local configuration deliberately accepts any RTMP path. Do not expose it to the internet. A production setup needs HTTPS, RTMPS, a public hostname in `MEDIAMTX_INGEST_URL` and `MEDIAMTX_PLAYBACK_BASE_URL`, authentication, and a bandwidth/CDN plan.
