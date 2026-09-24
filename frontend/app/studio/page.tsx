"use client";

import Link from "next/link";
import { useEffect, useState } from "react";

type Channel = { name: string; slug: string; is_live: boolean };
type LiveSummary = { live_viewers: number; peak_viewers: number; live_duration_seconds: number };

function formatDuration(seconds: number) { const hours = Math.floor(seconds / 3600); const minutes = Math.floor((seconds % 3600) / 60); const remainingSeconds = seconds % 60; if (hours) return `${hours}h ${minutes}m`; if (minutes) return `${minutes}m`; return `${remainingSeconds}s`; }

export default function StudioPage() {
  const [channel, setChannel] = useState<Channel | null>(null);
  const [role, setRole] = useState("");
  const [notice, setNotice] = useState("");
  const [liveSummary, setLiveSummary] = useState<LiveSummary | null>(null);
  const [ready, setReady] = useState(false);
  const token = typeof window === "undefined" ? "" : localStorage.getItem("eaststream_token") ?? "";
  async function loadLiveSummary() { const response = await fetch("/api/creator/analytics", { headers: { Authorization: `Bearer ${localStorage.getItem("eaststream_token")}` } }); if (!response.ok) return; const data = await response.json(); setLiveSummary(data.metrics); }
  useEffect(() => { const timer = window.setTimeout(() => { void (async () => { try { const headers = { Authorization: `Bearer ${localStorage.getItem("eaststream_token")}` }; const profile = await fetch("/api/profiles/me", { headers }).then((r) => r.json()); setRole(profile.user?.role ?? ""); const data = await fetch("/api/channels/me", { headers }).then((r) => r.json()); setChannel(data.channel); if (data.channel?.is_live) await loadLiveSummary(); } catch { setNotice("Unable to load Creator Studio."); } finally { setReady(true); } })(); }, 0); return () => window.clearTimeout(timer); }, []);
  useEffect(() => { if (!channel?.is_live) return; const interval = window.setInterval(() => { void loadLiveSummary(); }, 30_000); return () => window.clearInterval(interval); }, [channel?.is_live]);
  async function endStream() { const response = await fetch("/api/streams/me", { method: "PATCH", headers: { "Content-Type": "application/json", Authorization: `Bearer ${token}` }, body: JSON.stringify({ status: "offline" }) }); const data = await response.json(); if (response.ok) { setChannel((current) => current ? { ...current, is_live: false } : current); setLiveSummary(null); setNotice("Your stream is now offline."); } else setNotice(data.message || "Unable to end stream."); }
  if (!ready) return <main className="role-loading">Opening Creator Studio…</main>;
  if (!token || !["streamer", "admin"].includes(role)) return <AccessDenied title="Creator Studio" detail="Create a channel from your member home first to unlock streaming tools." />;
  return <main className="role-shell"><DashboardHeader label="Creator Studio" /><section className="studio-page"><p className="eyebrow purple"><span /> CREATOR STUDIO</p><h1>{channel?.is_live ? "You’re live." : "Choose how to go live."}</h1><p className="role-intro">{channel?.is_live ? channel?.name : "Start with your camera or streaming software."}</p><div className="studio-status"><span className={channel?.is_live ? "status-dot live-dot" : "status-dot"} /><div><strong>{channel?.is_live ? "Broadcasting live" : "Currently offline"}</strong><small>{channel?.is_live ? "Your audience can now find your stream." : "Choose a broadcast method below."}</small></div>{channel?.is_live && <div className="studio-live-actions"><Link className="secondary-button" href={`/stream/${encodeURIComponent(channel.slug)}`}>View live</Link><button className="danger-button" onClick={endStream}>End stream</button></div>}</div>{channel?.is_live && <section className="studio-live-summary" aria-label="Live broadcast summary"><div><strong>{liveSummary?.live_viewers ?? 0}</strong><span>Watching now</span></div><div><strong>{liveSummary?.peak_viewers ?? 0}</strong><span>Peak viewers</span></div><div><strong>{formatDuration(liveSummary?.live_duration_seconds ?? 0)}</strong><span>Time live</span></div></section>}<section className="broadcast-choices" aria-label="Choose a broadcast method"><Link className="broadcast-choice camera-choice" href="/studio/camera"><span className="broadcast-choice-icon">◉</span><div><p>QUICK START</p><h2>Use your camera</h2><span>Go live directly from this browser with your camera and microphone.</span></div><b>Go with camera →</b></Link><Link className="broadcast-choice obs-choice" href="/studio/obs"><span className="broadcast-choice-icon">▣</span><div><p>STREAMING SOFTWARE</p><h2>Use OBS</h2><span>Connect OBS or another encoder using a private stream key.</span></div><b>Set up OBS →</b></Link></section>{notice && <p className="dashboard-notice">{notice}</p>}</section></main>;
}

function DashboardHeader({ label }: { label: string }) { return <header className="role-header"><Link className="brand" href="/"><span className="brand-mark">e</span><span>eaststream</span></Link><span>{label}</span><Link href="/">Member home ↗</Link></header>; }
function AccessDenied({ title, detail }: { title: string; detail: string }) { return <main className="access-denied"><span className="brand-mark">e</span><p className="eyebrow purple"><span /> ACCESS RESTRICTED</p><h1>{title}</h1><p>{detail}</p><Link className="primary-button" href="/">Back to home <span>→</span></Link></main>; }
