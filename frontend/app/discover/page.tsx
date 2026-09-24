"use client";

import Link from "next/link";
import { useRouter, useSearchParams } from "next/navigation";
import { FormEvent, Suspense, useEffect, useState } from "react";
import { Search } from "lucide-react";

type Category = { id: number; name: string; slug: string; description?: string };
type Channel = { id: number; name: string; slug: string; username: string; category?: string; category_slug?: string; is_live: boolean; followers: number; profile?: { display_name?: string } };
type Stream = { id: number; title: string; viewer_count: number; ended_at?: string; channel: Channel };
type Data = { live: Stream[]; recent: Stream[]; recommended: Channel[]; categories: Category[] };

function initial(name: string) { return (name.match(/\p{L}/u)?.[0] || "E").toUpperCase(); }
function compact(value: number) { return new Intl.NumberFormat("en", { notation: "compact" }).format(value); }

export default function DiscoverPage() {
  return <Suspense fallback={<main className="creator-state">Loading discovery…</main>}><DiscoverContent /></Suspense>;
}

function DiscoverContent() {
  const router = useRouter();
  const searchParams = useSearchParams();
  const query = searchParams.get("q") ?? "";
  const [data, setData] = useState<Data | null>(null);
  const following = searchParams.get("following") === "1";
  const token = typeof window === "undefined" ? "" : localStorage.getItem("eaststream_token") ?? "";
  const [error, setError] = useState("");

  useEffect(() => {
    const params = new URLSearchParams();
    if (query) params.set("q", query);
    if (following) params.set("following", "1");
    void fetch(`/api/discover?${params.toString()}`, { headers: token ? { Authorization: `Bearer ${token}` } : {} })
      .then(async (response) => {
        if (!response.ok) throw new Error("Discovery is unavailable right now.");
        return response.json() as Promise<Data>;
      })
      .then((nextData) => { setData(nextData); setError(""); })
      .catch((reason: Error) => { setData({ live: [], recent: [], recommended: [], categories: [] }); setError(reason.message); });
  }, [query, following, token]);

  function updateSearch(nextQuery: string, nextFollowing = following) {
    const params = new URLSearchParams();
    if (nextQuery) params.set("q", nextQuery);
    if (nextFollowing) params.set("following", "1");
    router.replace(`/discover${params.size ? `?${params}` : ""}`);
  }
  function submit(event: FormEvent<HTMLFormElement>) {
    event.preventDefault();
    updateSearch(String(new FormData(event.currentTarget).get("search") ?? "").trim());
  }

  const searching = Boolean(query);
  return <main className="discover-page">
    <header className="watch-topbar"><Link className="brand" href="/"><span className="brand-mark">e</span><span>eaststream</span></Link><Link className="watch-exit" href="/">Home ↗</Link></header>
    <section className="discover-hero"><p className="eyebrow purple"><span /> DISCOVER</p><h1>{searching ? <>Results for<br /><em>“{query}”</em></> : <>Find your next<br /><em>live moment.</em></>}</h1><form onSubmit={submit}><label className="sr-only" htmlFor="discovery-search">Search creators, streams, or categories</label><Search size={18} aria-hidden="true" /><input id="discovery-search" name="search" key={query} defaultValue={query} placeholder="Search creators, streams, or categories" /><button className="primary-button">Search <span>→</span></button></form>{token && <button className={following ? "following-button" : "secondary-button"} onClick={() => updateSearch(query, !following)}>{following ? "✓ Following feed" : "Show following"}</button>}</section>
    <section className="discover-content">
      <CategorySection categories={data?.categories ?? []} title={searching ? "Matching categories" : "Browse categories"} />
      <StreamSection title={following ? "Live from channels you follow" : searching ? "Live stream results" : "Live now"} streams={data?.live ?? []} live empty={following ? "None of the channels you follow are live right now." : undefined} />
      <ChannelSection channels={data?.recommended ?? []} title={searching ? "Creator results" : "Recommended creators"} />
      <StreamSection title={searching ? "Recent broadcast results" : "Recently live"} streams={data?.recent ?? []} empty={searching ? "No recent broadcasts match that search." : undefined} />
      {error && <p className="muted-note" role="alert">{error}</p>}
    </section>
  </main>;
}

function CategorySection({ categories, title }: { categories: Category[]; title: string }) { return <section className="discover-section"><div className="section-heading"><div><h2>{title}</h2><p>Find communities around the things you care about.</p></div></div>{categories.length ? <div className="category-discovery-grid">{categories.map((category) => <Link href={`/category/${encodeURIComponent(category.slug)}`} className="category-discovery-card" key={category.id}><span>Explore</span><strong>{category.name}</strong><p>{category.description || "See creators and live conversations."}</p><b>View category →</b></Link>)}</div> : <p className="muted-note">No categories match that search.</p>}</section>; }
function StreamSection({ title, streams, live, empty }: { title: string; streams: Stream[]; live?: boolean; empty?: string }) { return <section className="discover-section"><h2>{title}</h2>{streams.length ? <div className="channel-grid">{streams.map((stream) => <Link className="channel-card" href={live ? `/stream/${encodeURIComponent(stream.channel.slug)}` : `/creator/${encodeURIComponent(stream.channel.username)}`} key={stream.id}><div className="channel-visual"><span className={live ? "live-pill" : "viewer-pill"}>{live ? "● LIVE" : "RECENT"}</span><div className="visual-symbol">{initial(stream.channel.name)}</div></div><div className="channel-info"><div className="channel-avatar">{initial(stream.channel.name)}</div><div><h3>{stream.channel.profile?.display_name || stream.channel.name}</h3><p>{stream.title}</p><span>{live ? `◉ ${compact(stream.viewer_count)} watching` : stream.channel.category || "Broadcast"}</span></div></div></Link>)}</div> : <p className="muted-note">{empty || "Nothing here yet—try another search or check back soon."}</p>}</section>; }
function ChannelSection({ channels, title }: { channels: Channel[]; title: string }) { return <section className="discover-section"><h2>{title}</h2>{channels.length ? <div className="creator-discovery-grid">{channels.map((channel) => <Link href={`/creator/${encodeURIComponent(channel.username)}`} className="creator-discovery-card" key={channel.id}><span>{initial(channel.profile?.display_name || channel.name)}</span><div><strong>{channel.profile?.display_name || channel.name}</strong><small>@{channel.username}{channel.category ? ` · ${channel.category}` : ""}</small><p>{compact(channel.followers)} followers {channel.is_live ? "· Live now" : ""}</p></div></Link>)}</div> : <p className="muted-note">No creators match that search.</p>}</section>; }
