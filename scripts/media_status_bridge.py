"""Mirror MediaMTX path readiness to Eaststream without shell hooks.

The official MediaMTX image is intentionally distroless, so runOnOnline cannot
execute curl. This sidecar reads its private control API and emits transitions.
"""
import json
import os
import time
import urllib.error
import urllib.parse
import urllib.request

API_URL = os.environ["MEDIAMTX_API_URL"]
WEBHOOK_URL = os.environ["EASTSTREAM_MEDIA_WEBHOOK_URL"]
SECRET = os.environ["EASTSTREAM_MEDIA_WEBHOOK_SECRET"]
online = set()


def notify(path, state):
    query = urllib.parse.urlencode({"path": path, "state": state})
    request = urllib.request.Request(
        f"{WEBHOOK_URL}?{query}", method="POST",
        headers={"X-Eaststream-Media-Signature": SECRET}, data=b"",
    )
    try:
        with urllib.request.urlopen(request, timeout=3) as response:
            response.read()
        print(f"reported {state}: {path}", flush=True)
    except (urllib.error.URLError, TimeoutError) as error:
        print(f"webhook {state} for {path} failed: {error}", flush=True)


while True:
    try:
        with urllib.request.urlopen(API_URL, timeout=3) as response:
            payload = json.load(response)
        items = payload.get("items", [])
        # `online` is true only while MediaMTX has an active publisher.
        current = {item["name"] for item in items if item.get("online")}
        for path in current - online:
            notify(path, "online")
        for path in online - current:
            notify(path, "offline")
        online = current
    except (urllib.error.URLError, TimeoutError, json.JSONDecodeError, KeyError) as error:
        print(f"MediaMTX API unavailable: {error}", flush=True)
    time.sleep(2)
