/** @type {import('next').NextConfig} */
const nextConfig = {
  images: {
    remotePatterns: [
      { protocol: "http", hostname: "localhost" },
      { protocol: "http", hostname: "127.0.0.1" },
      { protocol: "https", hostname: "**" },
    ],
  },
  async rewrites() {
    const erp =
      (process.env.ERP_ORIGIN || "http://127.0.0.1:8080").replace(/\/$/, "");
    return [
      {
        source: "/api/:path*",
        destination: `${erp}/index.php/api/:path*`,
      },
    ];
  },
};

export default nextConfig;
