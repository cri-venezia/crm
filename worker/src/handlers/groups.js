import { createClient } from '@supabase/supabase-js';

export class GroupsHandler {
    constructor(env) {
        this.env = env;
        this.supabase = createClient(env.SUPABASE_URL, env.SUPABASE_SERVICE_ROLE_KEY);
    }

    async handle(request) {
        // Headers
        const headers = {
            'Content-Type': 'application/json',
            'Access-Control-Allow-Origin': '*',
            'Access-Control-Allow-Methods': 'GET, POST, PUT, DELETE, OPTIONS',
            'Access-Control-Allow-Headers': 'Content-Type, Authorization, apikey, x-client-info'
        };

        if (request.method === 'OPTIONS') {
            return new Response(null, { headers });
        }

        try {
            const url = new URL(request.url);

            // GET /: List Groups
            if (request.method === 'GET') {
                const { data, error } = await this.supabase
                    .from('user_groups')
                    .select('*')
                    .order('name');

                if (error) throw error;
                return new Response(JSON.stringify(data), { headers });
            }

            // POST /: Create Group
            if (request.method === 'POST') {
                const { name, accessible_modules } = await request.json();

                if (!name) throw new Error("Name is required");

                const { data, error } = await this.supabase
                    .from('user_groups')
                    .insert({ name, accessible_modules: accessible_modules || [] })
                    .select()
                    .single();

                if (error) throw error;
                return new Response(JSON.stringify(data), { headers });
            }

            // PUT /: Update Group
            if (request.method === 'PUT') {
                const id = url.searchParams.get('id');
                if (!id) throw new Error("Missing Group ID");

                const { name, accessible_modules } = await request.json();
                const updates = {};
                if (name) updates.name = name;
                if (accessible_modules) updates.accessible_modules = accessible_modules;

                const { error } = await this.supabase
                    .from('user_groups')
                    .update(updates)
                    .eq('id', id);

                if (error) throw error;
                return new Response(JSON.stringify({ success: true }), { headers });
            }

            // DELETE /: Delete Group
            if (request.method === 'DELETE') {
                const id = url.searchParams.get('id');
                if (!id) throw new Error("Missing Group ID");

                // Check usage first? 
                // For now, let DB constraints fail if used, or cascade if configured. 
                // Usually better to check manually to give better error.

                const { error } = await this.supabase
                    .from('user_groups')
                    .delete()
                    .eq('id', id);

                if (error) throw error;
                return new Response(JSON.stringify({ success: true }), { headers });
            }

            return new Response('Method not allowed', { status: 405, headers });

        } catch (error) {
            return new Response(JSON.stringify({ error: error.message }), { status: 500, headers });
        }
    }
}
