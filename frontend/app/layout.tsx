import type { Metadata } from "next";
import { Bricolage_Grotesque, Inter } from "next/font/google";
import "./globals.css";

const display = Bricolage_Grotesque({
  subsets: ["latin"],
  variable: "--font-display",
  weight: ["500", "600", "800"],
});

const body = Inter({
  subsets: ["latin"],
  variable: "--font-body",
  weight: ["400", "500", "600"],
});

export const metadata: Metadata = {
  title: "Eaststream — Find your people",
  description: "A home for live stories, games, sounds, and conversations.",
};

export default function RootLayout({ children }: LayoutProps<"/">) {
  return (
    <html
      lang="en"
      className={`h-full antialiased ${display.variable} ${body.variable}`}
    >
      <body className="min-h-full flex flex-col bg-signal text-ink font-body">
        <div className="signal-field" aria-hidden="true" />
        {children}
      </body>
    </html>
  );
}