-- Enable necessary extensions
create extension if not exists "vector" with schema extensions;

-- PROFILES (Users)
DO $$
BEGIN
    IF NOT EXISTS (SELECT 1 FROM pg_type WHERE typname = 'user_role') THEN
        CREATE TYPE user_role AS ENUM ('admin', 'fundraising', 'newsletter', 'volontario');
    END IF;
END$$;

create table if not exists public.profiles (
  id uuid references auth.users on delete cascade primary key,
  full_name text,
  role user_role default 'volontario',
  created_at timestamp with time zone default timezone('utc'::text, now()) not null
);

alter table public.profiles enable row level security;

-- Policies (drop first to allow re-run)
drop policy if exists "Public profiles are viewable by everyone." on public.profiles;
create policy "Public profiles are viewable by everyone." on public.profiles
  for select using (true);

drop policy if exists "Users can insert their own profile." on public.profiles;
create policy "Users can insert their own profile." on public.profiles
  for insert with check (auth.uid() = id);

drop policy if exists "Users can update own profile." on public.profiles;
create policy "Users can update own profile." on public.profiles
  for update using (auth.uid() = id);

-- Trigger to create profile on signup
create or replace function public.handle_new_user()
returns trigger as $$
begin
  insert into public.profiles (id, full_name, role)
  values (new.id, new.raw_user_meta_data->>'full_name', 'volontario')
  on conflict (id) do nothing;
  return new;
end;
$$ language plpgsql security definer;

-- Drop trigger first
drop trigger if exists on_auth_user_created on auth.users;
create trigger on_auth_user_created
  after insert on auth.users
  for each row execute procedure public.handle_new_user();


-- CONTACTS (Anagrafica)
DO $$
BEGIN
    IF NOT EXISTS (SELECT 1 FROM pg_type WHERE typname = 'contact_type') THEN
        CREATE TYPE contact_type AS ENUM ('volontario', 'sostenitore', 'donatore');
    END IF;
END$$;

create table if not exists public.contacts (
  id uuid default gen_random_uuid() primary key,
  first_name text not null,
  last_name text not null,
  email text,
  phone text,
  type contact_type default 'sostenitore',
  tags text[] default '{}',
  notes text,
  created_at timestamp with time zone default timezone('utc'::text, now()) not null,
  updated_at timestamp with time zone default timezone('utc'::text, now()) not null
);

alter table public.contacts enable row level security;

drop policy if exists "Authenticated users can manage contacts" on public.contacts;
create policy "Authenticated users can manage contacts" on public.contacts
  for all using (auth.role() = 'authenticated');


-- AI CHAT LOGS
create table if not exists public.ai_chat_logs (
  id uuid default gen_random_uuid() primary key,
  user_id uuid references auth.users on delete set null,
  message_input text not null,
  message_output text not null,
  created_at timestamp with time zone default timezone('utc'::text, now()) not null
);

alter table public.ai_chat_logs enable row level security;

drop policy if exists "Users can view own logs" on public.ai_chat_logs;
create policy "Users can view own logs" on public.ai_chat_logs
  for select using (auth.uid() = user_id);

drop policy if exists "Service role can insert logs" on public.ai_chat_logs;
create policy "Service role can insert logs" on public.ai_chat_logs
  for insert with check (true);
