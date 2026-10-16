import type { MetadataRoute } from "next";

export default function sitemap(): MetadataRoute.Sitemap {
  const site = process.env.NEXT_PUBLIC_SITE_URL || "http://localhost:3000";
  const now = new Date();
  return [
    { url: `${site}/`, lastModified: now, changeFrequency: "weekly", priority: 1 },
    { url: `${site}/tutorials`, lastModified: now, changeFrequency: "monthly", priority: 0.8 },
    { url: `${site}/shop`, lastModified: now, changeFrequency: "daily", priority: 0.9 },
    { url: `${site}/contact`, lastModified: now, changeFrequency: "monthly", priority: 0.5 },
  ];
}
