import { createClient } from '@supabase/supabase-js';
import fs from 'fs';
import path from 'path';
import 'dotenv/config';

// Load env vars
const SUPABASE_URL = process.env.VITE_SUPABASE_URL;
// Use Service Role Key for full access (bypass RLS)
// We'll read it from env or ask user if missing. Assuming it's in .env as SUPABASE_SERVICE_ROLE_KEY
// If not, we fall back to the one effectively used in worker secret put earlier.
const SUPABASE_KEY = process.env.SUPABASE_SERVICE_ROLE_KEY || process.env.VITE_SUPABASE_ANON_KEY;

if (!SUPABASE_URL || !SUPABASE_KEY) {
    console.error("Missing SUPABASE_URL or SUPABASE_SERVICE_ROLE_KEY in .env");
    process.exit(1);
}

const supabase = createClient(SUPABASE_URL, SUPABASE_KEY);

async function exportLogs() {
    console.log("Fetching ai_chat_logs...");

    const allRows = [];
    let page = 0;
    const pageSize = 1000;
    let hasMore = true;

    while (hasMore) {
        const { data, error } = await supabase
            .from('ai_chat_logs')
            .select('*')
            .range(page * pageSize, (page + 1) * pageSize - 1);

        if (error) {
            console.error("Error fetching logs:", error);
            process.exit(1);
        }

        if (data.length > 0) {
            allRows.push(...data);
            console.log(`Fetched ${data.length} rows (Total: ${allRows.length})`);
            page++;
        } else {
            hasMore = false;
        }
    }

    const backupDir = path.resolve('backup');
    if (!fs.existsSync(backupDir)) {
        fs.mkdirSync(backupDir, { recursive: true });
    }

    const filePath = path.join(backupDir, 'chat_logs.json');
    fs.writeFileSync(filePath, JSON.stringify(allRows, null, 2));
    console.log(`✅ Successfully exported ${allRows.length} logs to ${filePath}`);
}

exportLogs();
