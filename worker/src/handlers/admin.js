import { createClient } from '@supabase/supabase-js';

export class AdminHandler {
    constructor(env) {
        this.env = env;
        // MUST use Service Role Key for Admin operations (creating/deleting users)
        if (!env.SUPABASE_URL || !env.SUPABASE_SERVICE_ROLE_KEY) {
            throw new Error("Missing SUPABASE_URL or SUPABASE_SERVICE_ROLE_KEY");
        }
        this.supabaseAdmin = createClient(env.SUPABASE_URL, env.SUPABASE_SERVICE_ROLE_KEY);
    }

    async handle(request) {
        const url = new URL(request.url);
        const path = url.pathname.replace('/api/admin/users', '');

        // CORS headers
        const headers = {
            'Content-Type': 'application/json',
            'Access-Control-Allow-Origin': '*',
            'Access-Control-Allow-Methods': 'GET, POST, DELETE, OPTIONS',
            'Access-Control-Allow-Headers': 'Content-Type'
        };

        if (request.method === 'OPTIONS') {
            return new Response(null, { headers });
        }

        try {
            // GET /: List Users
            if (request.method === 'GET') {
                // 1. Fetch Auth Users
                const { data: { users }, error: authError } = await this.supabaseAdmin.auth.admin.listUsers();
                if (authError) throw authError;

                // 2. Fetch Profiles with Group & Location
                const { data: profiles, error: profileError } = await this.supabaseAdmin
                    .from('profiles')
                    .select('id, role, full_name, group_id, location_id, user_groups(name, accessible_modules), locations(name)');

                if (profileError) throw profileError;

                // 3. Merge Data
                const mergedUsers = users.map(u => {
                    const profile = profiles.find(p => p.id === u.id);
                    return {
                        id: u.id,
                        email: u.email,
                        created_at: u.created_at,
                        role: profile?.role || 'volontario',
                        full_name: profile?.full_name || '',
                        group_id: profile?.group_id,
                        location_id: profile?.location_id,
                        group_name: profile?.user_groups?.name || 'N/A',
                        location_name: profile?.locations?.name || 'N/A',
                        modules: profile?.user_groups?.accessible_modules || []
                    };
                });

                return new Response(JSON.stringify(mergedUsers), { headers });
            }

            // POST /: Invite/Create User
            if (request.method === 'POST') {
                let { email, password, role, full_name, group_id, location_id } = await request.json();

                // Sanitize legacy 'user' role to 'volontario'
                if (role === 'user') role = 'volontario';

                if (!email || !password) throw new Error("Email and Password required");

                // 1. Create Auth User
                const { data: { user }, error: createError } = await this.supabaseAdmin.auth.admin.createUser({
                    email,
                    password,
                    email_confirm: true,
                    user_metadata: { full_name }
                });

                if (createError) throw createError;

                // 2. Create Profile
                const { error: profileError } = await this.supabaseAdmin
                    .from('profiles')
                    .insert({
                        id: user.id,
                        role: role || 'volontario',
                        full_name: full_name || '',
                        group_id: group_id || null,
                        location_id: location_id || null
                    });

                if (profileError) {
                    await this.supabaseAdmin.auth.admin.deleteUser(user.id);
                    throw profileError;
                }

                return new Response(JSON.stringify({ success: true, user }), { headers });
            }

            // PUT /: Update User
            if (request.method === 'PUT') {
                const id = url.searchParams.get('id');
                if (!id) throw new Error("Missing ID");

                const { role, full_name, group_id, location_id } = await request.json();

                const updates = {};
                if (role) updates.role = role;
                if (full_name) updates.full_name = full_name;
                if (group_id) updates.group_id = group_id;
                if (location_id) updates.location_id = location_id;

                const { error } = await this.supabaseAdmin
                    .from('profiles')
                    .update(updates)
                    .eq('id', id);

                if (error) throw error;

                return new Response(JSON.stringify({ success: true }), { headers });
            }


            // DELETE /:id : Delete User
            if (request.method === 'DELETE') {
                // Extract ID from URL query or last segment? 
                // Let's assume passed as query param ?id=... for simplicity or last segment
                // A better router would parse params. For now, let's use query param ?id=
                const id = url.searchParams.get('id');

                if (!id) throw new Error("Missing ID");

                const { error } = await this.supabaseAdmin.auth.admin.deleteUser(id);
                if (error) throw error;

                // Profile cascades on delete? Usually yes if FK set to cascade. 
                // If not, we should delete profile too. Let's try delete profile explicitly just in case.
                await this.supabaseAdmin.from('profiles').delete().eq('id', id);

                return new Response(JSON.stringify({ success: true }), { headers });
            }

            return new Response(JSON.stringify({ error: "Method not allowed" }), { status: 405, headers });

        } catch (error) {
            return new Response(JSON.stringify({ error: error.message }), { status: 500, headers });
        }
    }
}
