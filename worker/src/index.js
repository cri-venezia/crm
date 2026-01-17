
import { handleChat } from "./handlers/chat.js";
import { handleFundraising } from "./handlers/fundraising.js";
import { handleNewsletter } from "./handlers/newsletter.js";
import { AdminHandler } from "./handlers/admin.js";

export default {
    async fetch(request, env, ctx) {
        const url = new URL(request.url);
        const path = url.pathname;

        // CORS Headers
        const corsHeaders = {
            "Access-Control-Allow-Origin": "*",
            "Access-Control-Allow-Methods": "GET, POST, PUT, DELETE, OPTIONS",
            "Access-Control-Allow-Headers": "Content-Type, Authorization, apikey, x-client-info",
        };

        // Handle OPTIONS (Preflight)
        if (request.method === "OPTIONS") {
            return new Response(null, { headers: corsHeaders });
        }

        try {
            // Routing
            if (path === "/api/erika/chat") {
                return handleChat(request, env, corsHeaders);
            }

            if (path === "/api/fundraising/generate") {
                return handleFundraising(request, env, corsHeaders);
            }

            if (path.startsWith("/api/newsletter/send")) {
                return handleNewsletter(request, env, corsHeaders);
            }

            if (path.startsWith("/api/admin/users")) {
                const admin = new AdminHandler(env);
                return await admin.handle(request);
            }

            if (path === "/api/admin/metadata") {
                const { MetadataHandler } = await import("./handlers/metadata.js");
                const metadata = new MetadataHandler(env);
                return await metadata.handle(request);
            }

            if (path.startsWith("/api/admin/groups")) {
                const { GroupsHandler } = await import("./handlers/groups.js");
                const groupsHandler = new GroupsHandler(env);
                return await groupsHandler.handle(request);
            }

            // 404 for unknown paths
            return new Response("Not Found", { status: 404, headers: corsHeaders });

        } catch (error) {
            return new Response(JSON.stringify({ error: error.message }), {
                status: 500,
                headers: { ...corsHeaders, "Content-Type": "application/json" }
            });
        }
    }
};
