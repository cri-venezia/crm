import { createClient } from '@supabase/supabase-js';

export class MetadataHandler {
    constructor(env) {
        this.env = env;
        this.supabase = createClient(env.SUPABASE_URL, env.SUPABASE_ANON_KEY);
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

        if (request.method === 'GET') {
            try {
                // Fetch Groups
                const { data: groups, error: groupsError } = await this.supabase
                    .from('user_groups')
                    .select('*')
                    .order('name');

                if (groupsError) throw groupsError;

                // Fetch Locations
                const { data: locations, error: locationsError } = await this.supabase
                    .from('locations')
                    .select('*')
                    .order('name');

                if (locationsError) throw locationsError;

                return new Response(JSON.stringify({ groups, locations }), { headers });

            } catch (error) {
                return new Response(JSON.stringify({ error: error.message }), { status: 500, headers });
            }
        }

        return new Response('Method not allowed', { status: 405, headers });
    }
}
