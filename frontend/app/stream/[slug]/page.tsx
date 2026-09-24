"use client";

import Link from "next/link";
import Hls from "hls.js";
import { FormEvent, useEffect, useRef, useState } from "react";

type Stream = { id: number; title: string; thumbnail_url?: string; playback_url?: string; viewer_count: number; started_at?: string; following: boolean; channel: { id: number; name: string; slug: string; description?: string; category?: string; username: string; profile?: { display_name?: string; avatar_url?: string } } };
type ChatMessage = { id: number; message: string; created: string; user: { username: string } };

function viewerSessionId(streamId: number) {
  const key = `eaststream_viewer_${streamId}`;
  let id = sessionStorage.getItem(key);
  if (!id) { id = crypto.randomUUID(); sessionStorage.setItem(key, id); }
  return id;
}

export default function StreamPage({ params }: { params: Promise<{ slug: string }> }) {
  const [slug, setSlug] = useState<string>();
  const [stream, setStream] = useState<Stream | null>(null);
  const [messages, setMessages] = useState<ChatMessage[]>([]);
  const [error, setError] = useState("");
  const [sending, setSending] = useState(false);
  const [streamEnded, setStreamEnded] = useState(false);
  const playerRef = useRef<HTMLVideoElement>(null);
  const streamId = stream?.id;

  useEffect(() => { void params.then((value) => setSlug(value.slug)); }, [params]);
  useEffect(() => {
    if (!slug) return;
    const token = localStorage.getItem("eaststream_token");
    fetch(`/api/streams/${encodeURIComponent(slug)}`, { headers: token ? { Authorization: `Bearer ${token}` } : {} })
      .then(async (response) => { const data = await response.json(); if (!response.ok) throw new Error(data.message); return data; })
      .then((data) => { setStream(data.stream); return fetch(`/api/streams/${data.stream.id}/chat`); })
      .then(async (response) => { const data = await response.json(); setMessages(data.messages ?? []); })
      .catch((reason: Error) => setError(reason.message || "Unable to load this stream."));
  }, [slug]);

  useEffect(() => {
    const player = playerRef.current;
    if (!player || !stream?.playback_url) return;
    const seekToLiveEdge = () => {
      const ranges = player.seekable;
      if (ranges.length) player.currentTime = Math.max(ranges.start(ranges.length - 1), ranges.end(ranges.length - 1) - 0.5);
    };
    if (player.canPlayType("application/vnd.apple.mpegurl")) {
      player.src = stream.playback_url;
      player.addEventListener("loadedmetadata", seekToLiveEdge, { once: true });
      return () => player.removeEventListener("loadedmetadata", seekToLiveEdge);
    }
    if (!Hls.isSupported()) return;
    const hls = new Hls({ startPosition: -1, liveSyncDurationCount: 2, liveMaxLatencyDurationCount: 5 });
    hls.loadSource(stream.playback_url);
    hls.attachMedia(player);
    hls.on(Hls.Events.MANIFEST_PARSED, seekToLiveEdge);
    hls.on(Hls.Events.ERROR, (_, data) => { if (data.fatal) setError("Unable to play this live stream."); });
    return () => hls.destroy();
  }, [stream?.playback_url]);

  useEffect(() => {
    if (!streamId) return;
    const sessionId = viewerSessionId(streamId);
    let active = true;
    async function heartbeat() {
      const response = await fetch(`/api/streams/${streamId}/presence`, { method: "POST", headers: { "Content-Type": "application/json" }, body: JSON.stringify({ session_id: sessionId }) }).catch(() => null);
      if (!response?.ok || !active) return;
      const data = await response.json().catch(() => null);
      if (typeof data?.viewer_count === "number") setStream((current) => current ? { ...current, viewer_count: data.viewer_count } : current);
    }
    void heartbeat();
    const heartbeatTimer = window.setInterval(() => void heartbeat(), 15_000);
    const events = new EventSource(`/api/streams/${streamId}/events`);
    events.addEventListener("snapshot", (event) => {
      const data = JSON.parse((event as MessageEvent<string>).data) as { status: string; viewer_count: number; messages: ChatMessage[] };
      if (!active) return;
      setMessages(data.messages);
      setStream((current) => current ? { ...current, viewer_count: data.viewer_count } : current);
      if (data.status !== "live") { setStreamEnded(true); events.close(); }
    });
    return () => { active = false; window.clearInterval(heartbeatTimer); events.close(); };
  }, [streamId]);

  async function toggleFollow() {
    if (!stream) return;
    const token = localStorage.getItem("eaststream_token");
    if (!token) { setError("Sign in to follow this channel."); return; }
    const response = await fetch(`/api/channels/${stream.channel.id}/follow`, { method: stream.following ? "DELETE" : "POST", headers: { Authorization: `Bearer ${token}` } });
    const data = await response.json();
    if (!response.ok) { setError(data.message || "Unable to update follow."); return; }
    setStream({ ...stream, following: data.following });
  }

  async function sendMessage(event: FormEvent<HTMLFormElement>) {
    event.preventDefault(); if (!stream) return;
    const formElement = event.currentTarget;
    const form = new FormData(formElement); const message = String(form.get("message") ?? "").trim();
    if (!message) return;
    const token = localStorage.getItem("eaststream_token");
    if (!token) { setError("Sign in to join the chat."); return; }
    setSending(true);
    const response = await fetch(`/api/streams/${stream.id}/chat`, { method: "POST", headers: { "Content-Type": "application/json", Authorization: `Bearer ${token}` }, body: JSON.stringify({ message }) });
    const data = await response.json();
    if (response.ok) { setMessages(data.messages ?? []); formElement.reset(); } else setError(data.message || "Unable to send message.");
    setSending(false);
  }

  if (error && !stream) return <main className="watch-error"><Link href="/">← Back to Eaststream</Link><h1>Stream unavailable</h1><p>{error}</p></main>;
  if (!stream) return <main className="watch-loading">Loading stream…</main>;
  if (streamEnded) return <main className="watch-error"><Link href="/">← Back to Eaststream</Link><h1>This stream has ended</h1><p>{stream.channel.name} is no longer live. Thanks for watching together.</p></main>;
  const creator = stream.channel.profile?.display_name || stream.channel.name;
  const initial = creator.charAt(0).toUpperCase();
  return <main className="watch-page"><header className="watch-topbar"><Link className="brand" href="/"><span className="brand-mark">e</span><span>eaststream</span></Link><div className="watch-breadcrumb">Browse <span>/</span> {stream.channel.category || "Live"}</div><Link className="watch-exit" href="/">Exit stream ↗</Link></header><div className="watch-layout"><section className="watch-main"><div className="player-shell" style={stream.thumbnail_url ? { backgroundImage: `linear-gradient(120deg,#14252ddd,#3d3074aa),url(${stream.thumbnail_url})` } : undefined}>{stream.playback_url ? <video className="mediamtx-player" ref={playerRef} controls playsInline /> : <div className="player-ready"><strong>Preparing the live player</strong><span>The creator&apos;s playback URL is not ready yet.</span></div>}<span className="live-pill">● LIVE</span><span className="player-viewers">◉ {new Intl.NumberFormat("en", { notation: "compact" }).format(stream.viewer_count)} watching</span></div><div className="stream-details"><div className="creator-avatar">{initial}</div><div className="stream-copy"><p className="stream-category">{stream.channel.category || "Live now"}</p><h1>{stream.title}</h1><div className="creator-row"><Link href={`/creator/${encodeURIComponent(stream.channel.username)}`}><strong>{creator}</strong><span>@{stream.channel.username}</span></Link><button className={stream.following ? "following-button" : "follow-button"} onClick={toggleFollow}>{stream.following ? "✓ Following" : "+ Follow"}</button><button className="share-button">↗ Share</button></div></div></div><div className="about-stream"><h2>About {creator}</h2><p>{stream.channel.description || "Welcome to this live Eaststream channel."}</p><div><span>◉ {new Intl.NumberFormat("en", { notation: "compact" }).format(stream.viewer_count)} viewers</span><span>◌ Live now</span></div></div></section><aside className="chat-panel"><div className="chat-heading"><div><h2>Stream chat</h2><span>{messages.length} messages</span></div><button>•••</button></div><div className="chat-messages">{messages.length ? messages.map((item) => <div className="chat-message" key={item.id}><strong>{item.user.username}</strong><p>{item.message}</p></div>) : <div className="chat-empty"><span>✦</span><h3>Be the first to say hello</h3><p>Join the conversation with {creator}&apos;s community.</p></div>}</div>{error && <p className="chat-error">{error}</p>}<form className="chat-form" onSubmit={sendMessage}><input name="message" maxLength={500} placeholder="Say something kind…" /><button disabled={sending}>{sending ? "…" : "↑"}</button></form></aside></div></main>;
}
