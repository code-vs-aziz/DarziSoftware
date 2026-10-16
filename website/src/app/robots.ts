import type { MetadataRoute } from "next";

export default function robots(): MetadataRoute.Robots {
  const site = process.env.NEXT_PUBLIC_SITE_URL || "http://localhost:3000";
  return {
    rules: [
      {
        userAgent: "*",
        allow: ["/", "/shop", "/tutorials", "/contact"],
        disallow: ["/measurements", "/cart", "/api"],
      },
    ],
    sitemap: `${site}/sitemap.xml`,
  };
}
