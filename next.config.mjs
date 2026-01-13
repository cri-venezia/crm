/** @type {import('next').NextConfig} */
const nextConfig = {
    // Optimizations for Cloudflare Pages
    eslint: {
        ignoreDuringBuilds: true,
    },
    typescript: {
        ignoreBuildErrors: true,
    }
};

export default nextConfig;
