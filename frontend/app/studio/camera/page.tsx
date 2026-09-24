"use client";

import Link from "next/link";
import { FormEvent, useEffect, useRef, useState } from "react";

type PreparedStream = { stream_key: string; whip_url: string };

export default function CameraLivePage() {
  const previewRef = useRef<HTMLVideoElement>(null);
  const mediaRef = useRef<MediaStream | null>(null);
  const peerRef = useRef<RTCPeerConnection | null>(null);
  const sessionUrlRef = useRef<string | null>(null);
  const [title, setTitle] = useState("");
  const [ready, setReady] = useState(false);
  const [live, setLive] = useState(false);
  const [starting, setStarting] = useState(false);
  const [notice, setNotice] = useState("");
  const [error, setError] = useState("");
  const [accessError, setAccessError] = useState("");

  useEffect(() => {
    const timer = window.setTimeout(() => {
      const token = localStorage.getItem("eaststream_token");
      if (!token) { setAccessError("Sign in to use Camera live."); setReady(true); return; }
      void fetch("/api/profiles/me", { headers: { Authorization: `Bearer ${token}` } })
        .then((response) => response.json())
        .then((data) => { if (!["streamer", "admin"].includes(data.user?.role)) setAccessError("Create a channel from your member home first."); })
        .catch(() => setAccessError("Unable to verify your account."))
        .finally(() => setReady(true));
    }, 0);
    return () => { window.clearTimeout(timer); stopMedia(); };
  }, []);

  function stopMedia() {
    peerRef.current?.close(); peerRef.current = null;
    mediaRef.current?.getTracks().forEach((track) => track.stop()); mediaRef.current = null;
    if (previewRef.current) previewRef.current.srcObject = null;
  }

  async function startLive(event: FormEvent<HTMLFormElement>) {
    event.preventDefault(); setError(""); setStarting(true); setNotice("Requesting camera and microphone access…");
    let prepared: PreparedStream | null = null;
    try {
      const stream = await navigator.mediaDevices.getUserMedia({ video: true, audio: true });
      mediaRef.current = stream;
      if (previewRef.current) previewRef.current.srcObject = stream;
      const token = localStorage.getItem("eaststream_token");
      const response = await fetch("/api/streams/me", { method: "POST", headers: { "Content-Type": "application/json", Authorization: `Bearer ${token}` }, body: JSON.stringify({ title }) });
      prepared = await response.json().catch(() => null) as PreparedStream | null;
      if (!response.ok || !prepared) throw new Error((prepared as unknown as { message?: string } | null)?.message || "Unable to prepare your live stream.");
      const peer = new RTCPeerConnection(); peerRef.current = peer;
      stream.getTracks().forEach((track) => peer.addTrack(track, stream));
      const offer = await peer.createOffer(); await peer.setLocalDescription(offer);
      await waitForIceGathering(peer);
      const whipResponse = await fetch(prepared.whip_url, { method: "POST", headers: { "Content-Type": "application/sdp" }, body: peer.localDescription?.sdp });
      if (!whipResponse.ok) throw new Error("Media server rejected the camera stream. Make sure Docker MediaMTX is running.");
      const answer = await whipResponse.text(); await peer.setRemoteDescription({ type: "answer", sdp: answer });
      const location = whipResponse.headers.get("location");
      sessionUrlRef.current = location ? new URL(location, prepared.whip_url).toString() : null;
      setLive(true); setNotice("You are live from your camera.");
    } catch (reason) {
      if (prepared) {
        const token = localStorage.getItem("eaststream_token");
        await fetch("/api/streams/me", { method: "PATCH", headers: { "Content-Type": "application/json", Authorization: `Bearer ${token}` }, body: JSON.stringify({ status: "offline" }) }).catch(() => undefined);
      }
      stopMedia(); setNotice("");
      if (reason instanceof DOMException && reason.name === "NotReadableError") setError("Your camera is busy. Close OBS, Zoom, Teams, or any other app using it, then try again.");
      else if (reason instanceof DOMException && reason.name === "NotAllowedError") setError("Camera or microphone permission was blocked. Allow it in your browser site settings, then try again.");
      else setError(reason instanceof Error ? reason.message : "Unable to start camera live.");
    } finally {
      setStarting(false);
    }
  }

  async function endLive() {
    if (sessionUrlRef.current) await fetch(sessionUrlRef.current, { method: "DELETE" }).catch(() => undefined);
    const token = localStorage.getItem("eaststream_token");
    await fetch("/api/streams/me", { method: "PATCH", headers: { "Content-Type": "application/json", Authorization: `Bearer ${token}` }, body: JSON.stringify({ status: "offline" }) }).catch(() => undefined);
    stopMedia(); sessionUrlRef.current = null; setLive(false); setNotice("Your camera stream has ended.");
  }

  if (!ready) return <main className="role-loading">Opening Camera live…</main>;
  if (accessError) return <main className="access-denied"><span className="brand-mark">e</span><p className="eyebrow purple"><span /> CAMERA LIVE</p><h1>Camera live unavailable</h1><p>{accessError}</p><Link className="primary-button" href="/studio">Back to Creator Studio <span>→</span></Link></main>;
  return <main className="role-shell"><header className="role-header"><Link className="brand" href="/"><span className="brand-mark">e</span><span>eaststream</span></Link><span>Camera live</span><Link href="/studio">Creator Studio ↗</Link></header><section className="camera-live"><Link className="page-back" href="/studio">← Back to Creator Studio</Link><p className="eyebrow purple"><span /> CAMERA LIVE</p><h1>{live ? "You’re live." : "Go live from your camera."}</h1><p className="role-intro">Your camera and microphone stream directly to your Eaststream channel. OBS remains available in Creator Studio.</p><div className="camera-preview"><video ref={previewRef} autoPlay muted playsInline />{!live && <span>Camera preview appears after you start.</span>}{live && <b className="live-pill">● LIVE</b>}</div>{!live ? <form className="camera-form" onSubmit={startLive}><label>Stream title<input value={title} onChange={(event) => setTitle(event.target.value)} required placeholder="What are you sharing today?" /></label><button className="primary-button" disabled={starting}>{starting ? "Starting camera…" : "Enable camera & go live"} <span>→</span></button></form> : <button className="danger-button camera-end" onClick={endLive}>End camera stream</button>}{notice && <p className="dashboard-notice">{notice}</p>}{error && <p className="form-notice">{error}</p>}</section></main>;
}

function waitForIceGathering(peer: RTCPeerConnection) {
  if (peer.iceGatheringState === "complete") return Promise.resolve();
  return new Promise<void>((resolve) => peer.addEventListener("icegatheringstatechange", () => { if (peer.iceGatheringState === "complete") resolve(); }, { once: false }));
}
