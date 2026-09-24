"use client";

import Link from "next/link";
import { useEffect, useState } from "react";

type Creator = {
  user: { id: number; username: string; role: string };
  profile?: { display_name?: string; bio?: string; avatar_url?: string; banner_url?: string } | null;
  channel: { id: number; name: string; slug: string; description?: string; category?: string; is_live: boolean; followers: number; following: boolean; live_stream?: { title: string; viewer_count: number } | null } | null;
  recent_streams: { id: number; title: string; status: string; viewer_count: number; started_at?: string }[];
};

function initial(name: string) { return (name.match(/\p{L}/u)?.[0] ?? "E").toUpperCase(); }
function compact(number: number) { return new Intl.NumberFormat("en", { notation: "compact" }).format(number); }

export default function CreatorProfilePage({ params }: { params: Promise<{ username: string }> }) {
  const [creator, setCreator] = useState<Creator | null>(null);
  const [error, setError] = useState("");
  const [updatingFollow, setUpdatingFollow] = useState(false);

  useEffect(() => {
    void params.then(async ({ username }) => {
      const token = localStorage.getItem("eaststream_token");
      const response = await fetch(`/api/profiles/${encodeURIComponent(username)}`, { headers: token ? { Authorization: `Bearer ${token}` } : {} });
      const data = await response.json().catch(() => null);
      if (!response.ok) { setError(data?.message || "Unable to load this creator."); return; }
      setCreator(data);
    }).catch(() => setError("Unable to load this creator."));
  }, [params]);

  async function toggleFollow() {
    if (!creator?.channel) return;
    const token = localStorage.getItem("eaststream_token");
    if (!token) { setError("Sign in to follow this creator."); return; }
    setUpdatingFollow(true);
    const response = await fetch(`/api/channels/${creator.channel.id}/follow`, { method: creator.channel.following ? "DELETE" : "POST", headers: { Authorization: `Bearer ${token}` } });
    const data = await response.json().catch(() => null);
    if (!response.ok) setError(data?.message || "Unable to update follow.");
    else setCreator({ ...creator, channel: { ...creator.channel, following: data.following, followers: creator.channel.followers + (data.following ? 1 : -1) } });
    setUpdatingFollow(false);
  }

  if (error && !creator) return <main className="creator-state"><Link href="/">← Back to Eaststream</Link><h1>Creator unavailable</h1><p>{error}</p></main>;
  if (!creator) return <main className="creator-state">Loading creator…</main>;
  const name = creator.profile?.display_name || creator.channel?.name || creator.user.username;
  const isOwnProfile = typeof window !== "undefined" && creator.user.username === localStorage.getItem("eaststream_username");
  return <main className="creator-page"><header className="watch-topbar"><Link className="brand" href="/"><span className="brand-mark">e</span><span>eaststream</span></Link><Link className="watch-exit" href="/">Browse live ↗</Link></header><section className="creator-hero"><div className="creator-banner" style={creator.profile?.banner_url ? { backgroundImage: `linear-gradient(110deg, #1b2b5acc, #3a4f9688), url(${creator.profile.banner_url})` } : undefined} /><div className="creator-summary"><div className="creator-profile-avatar">{creator.profile?.avatar_url ? <span className="creator-avatar-image" style={{ backgroundImage: `url(${creator.profile.avatar_url})` }} /> : initial(name)}</div><div className="creator-title"><p className="eyebrow purple"><span /> CREATOR</p><h1>{name}</h1><p>@{creator.user.username}{creator.channel?.category ? ` · ${creator.channel.category}` : ""}</p></div>{creator.channel && !isOwnProfile && <button className={creator.channel.following ? "following-button" : "follow-button"} disabled={updatingFollow} onClick={toggleFollow}>{updatingFollow ? "…" : creator.channel.following ? "✓ Following" : "+ Follow"}</button>}</div></section><section className="creator-content"><div className="creator-main"><section className="creator-about"><h2>About {name}</h2><p>{creator.profile?.bio || creator.channel?.description || "This creator has not added a bio yet."}</p><div className="creator-stats"><span><b>{compact(creator.channel?.followers ?? 0)}</b> followers</span><span><b>{creator.recent_streams.length}</b> broadcasts</span></div></section>{creator.channel?.is_live && creator.channel.live_stream && <section className="creator-live"><span className="live-pill">● LIVE</span><div><p>Live now with {compact(creator.channel.live_stream.viewer_count)} viewers</p><h2>{creator.channel.live_stream.title}</h2></div><Link className="primary-button" href={`/stream/${encodeURIComponent(creator.channel.slug)}`}>Watch live <span>→</span></Link></section>}<section className="creator-broadcasts"><div className="section-heading"><div><p className="eyebrow"><span /> RECENT</p><h2>Recent broadcasts</h2></div></div>{creator.recent_streams.length ? <div className="creator-stream-list">{creator.recent_streams.map((stream) => <article key={stream.id}><span className={stream.status === "live" ? "broadcast-dot live-dot" : "broadcast-dot"} /><div><strong>{stream.title}</strong><small>{stream.started_at ? new Date(stream.started_at).toLocaleDateString() : "Not started"}</small></div><span className="broadcast-status">{stream.status}</span><b>◉ {compact(stream.viewer_count)}</b></article>)}</div> : <p className="muted-note">No broadcasts yet.</p>}</section></div><aside className="creator-side"><div className="creator-side-card"><strong>{creator.channel?.is_live ? "Live right now" : "Currently offline"}</strong><p>{creator.channel?.is_live ? "Join the community in chat." : "Follow to know when this creator goes live."}</p>{creator.channel?.is_live && <Link className="primary-button small" href={`/stream/${encodeURIComponent(creator.channel.slug)}`}>Watch now <span>→</span></Link>}</div></aside></section>{error && <p className="creator-error">{error}</p>}</main>;
}
