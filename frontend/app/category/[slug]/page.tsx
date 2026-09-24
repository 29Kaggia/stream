"use client";

import Link from "next/link";
import { useEffect, useState } from "react";

type Channel = { id: number; name: string; slug: string; username: string; category?: string; is_live: boolean; followers: number; profile?: { display_name?: string } };
type Stream = { id: number; title: string; viewer_count: number; channel: Channel };
type Category = { id: number; name: string; slug: string; description?: string };
type Data = { live: Stream[]; recent: Stream[]; recommended: Channel[]; categories: Category[] };
function initial(name: string) { return (name.match(/\p{L}/u)?.[0] || "E").toUpperCase(); }

export default function CategoryPage({ params }: { params: Promise<{ slug: string }> }) {
  const [slug, setSlug] = useState("");
  const [data, setData] = useState<Data | null>(null);
  const [error, setError] = useState("");
  useEffect(() => { void params.then(({ slug: nextSlug }) => { setSlug(nextSlug); return fetch(`/api/discover?category=${encodeURIComponent(nextSlug)}`).then(async (response) => { if (!response.ok) throw new Error("This category could not be loaded."); return response.json() as Promise<Data>; }).then(setData).catch((reason: Error) => { setData({ live: [], recent: [], recommended: [], categories: [] }); setError(reason.message); }); }); }, [params]);
  const category = data?.categories.find((item) => item.slug === slug);
  const title = category?.name || slug.replaceAll("-", " ");
  return <main className="discover-page"><header className="watch-topbar"><Link className="brand" href="/"><span className="brand-mark">e</span><span>eaststream</span></Link><Link className="watch-exit" href="/discover">Discover ↗</Link></header><section className="discover-hero"><p className="eyebrow purple"><span /> CATEGORY</p><h1>{title}</h1><p>{category?.description || "Live conversations and creators in this community."}</p></section><section className="discover-content"><section className="discover-section"><h2>Live in {title}</h2>{data?.live.length ? <div className="channel-grid">{data.live.map((stream) => <Link className="channel-card" href={`/stream/${encodeURIComponent(stream.channel.slug)}`} key={stream.id}><div className="channel-visual"><span className="live-pill">● LIVE</span><div className="visual-symbol">{initial(stream.channel.name)}</div></div><div className="channel-info"><div className="channel-avatar">{initial(stream.channel.name)}</div><div><h3>{stream.channel.profile?.display_name || stream.channel.name}</h3><p>{stream.title}</p><span>◉ {stream.viewer_count} watching</span></div></div></Link>)}</div> : <p className="muted-note">No streams are live in this category right now.</p>}</section><section className="discover-section"><h2>Creators in {title}</h2>{data?.recommended.length ? <div className="creator-discovery-grid">{data.recommended.map((channel) => <Link href={`/creator/${encodeURIComponent(channel.username)}`} className="creator-discovery-card" key={channel.id}><span>{initial(channel.profile?.display_name || channel.name)}</span><div><strong>{channel.profile?.display_name || channel.name}</strong><small>@{channel.username}</small><p>{channel.followers} followers {channel.is_live ? "· Live now" : ""}</p></div></Link>)}</div> : <p className="muted-note">No creators have joined this category yet.</p>}</section>{error && <p className="muted-note" role="alert">{error}</p>}</section></main>;
}
