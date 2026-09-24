"use client";

import Link from "next/link";
import { FormEvent, useEffect, useState } from "react";

type Channel = { is_live: boolean; slug: string };

export default function ObsLivePage() {
  const [channel, setChannel] = useState<Channel | null>(null);
  const [role, setRole] = useState("");
  const [streamKey, setStreamKey] = useState("");
  const [ingestUrl, setIngestUrl] = useState("");
  const [notice, setNotice] = useState("");
  const [ready, setReady] = useState(false);
  const token = typeof window === "undefined" ? "" : localStorage.getItem("eaststream_token") ?? "";

  useEffect(() => { const timer = window.setTimeout(() => { void (async () => { try { const headers = { Authorization: `Bearer ${localStorage.getItem("eaststream_token")}` }; const profile = await fetch("/api/profiles/me", { headers }).then((response) => response.json()); setRole(profile.user?.role ?? ""); const data = await fetch("/api/channels/me", { headers }).then((response) => response.json()); setChannel(data.channel); } catch { setNotice("Unable to load OBS setup."); } finally { setReady(true); } })(); }, 0); return () => window.clearTimeout(timer); }, []);

  async function prepareStream(event: FormEvent<HTMLFormElement>) { event.preventDefault(); const form = new FormData(event.currentTarget); const response = await fetch("/api/streams/me", { method: "POST", headers: { "Content-Type": "application/json", Authorization: `Bearer ${token}` }, body: JSON.stringify({ title: form.get("title"), thumbnail_url: form.get("thumbnail_url") }) }); const data = await response.json(); if (!response.ok) { setNotice(data.message || "Unable to prepare stream."); return; } setStreamKey(data.stream_key); setIngestUrl(data.ingest_url); setNotice("Broadcast prepared. Your stream will appear live automatically when OBS starts sending media."); }

  if (!ready) return <main className="role-loading">Opening OBS setup…</main>;
  if (!token || !["streamer", "admin"].includes(role)) return <main className="access-denied"><span className="brand-mark">e</span><p className="eyebrow purple"><span /> OBS SETUP</p><h1>OBS setup unavailable</h1><p>Create a channel from your member home first.</p><Link className="primary-button" href="/studio">Back to Creator Studio <span>→</span></Link></main>;
  return <main className="role-shell"><header className="role-header"><Link className="brand" href="/"><span className="brand-mark">e</span><span>eaststream</span></Link><span>OBS setup</span><Link href="/studio">Creator Studio ↗</Link></header><section className="obs-page"><Link className="page-back" href="/studio">← Back to Creator Studio</Link><p className="eyebrow purple"><span /> STREAMING SOFTWARE</p><h1>Stream with OBS.</h1><p className="role-intro">Create your broadcast, then connect OBS with a private stream key.</p>{channel?.is_live && <div className="obs-live-note"><span className="status-dot live-dot" /> <span>Your channel is live. <Link href={`/stream/${encodeURIComponent(channel.slug)}`}>View live →</Link></span></div>}<section className="role-card obs-setup-card"><h2>1. Create your broadcast</h2><form className="dashboard-form" onSubmit={prepareStream}><label>Stream title<input name="title" required placeholder="What are you streaming today?" /></label><label>Thumbnail URL <small>(optional)</small><input name="thumbnail_url" type="url" placeholder="https://…" /></label><button className="primary-button">Create broadcast</button></form>{streamKey && <div className="key-box"><strong>2. Add these in OBS → Settings → Stream</strong><label>Server URL<code>{ingestUrl}</code></label><label>Stream key<code>{streamKey}</code></label><small>Keep your stream key private. Eaststream will mark your channel live once MediaMTX receives video from OBS.</small></div>}</section>{notice && <p className="dashboard-notice">{notice}</p>}</section></main>;
}
