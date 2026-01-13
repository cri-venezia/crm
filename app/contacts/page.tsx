import { Suspense } from "react";
import { Users, Search } from "lucide-react";
import { Input } from "@/components/ui/input";
import { ContactsList } from "@/components/contacts/contacts-list";
import { ContactFormWrapper } from "@/app/contacts/wrapper";
import { getContacts } from "@/app/contacts/actions";



export const dynamic = 'force-dynamic'

export default async function ContactsPage({
    searchParams,
}: {
    searchParams?: {
        query?: string
        type?: string
    }
}) {
    const query = searchParams?.query || ""
    const type = searchParams?.type || "all"

    const contacts = await getContacts(query, type)

    return (
        <main className="flex min-h-screen flex-col p-8 bg-gray-50 dark:bg-gray-900">
            <div className="flex flex-col gap-8 max-w-7xl mx-auto w-full">
                <div className="flex items-end justify-between">
                    <div className="flex items-center gap-4">
                        <Users className="w-12 h-12 text-red-600 dark:text-red-500" />
                        <div>
                            <h1 className="text-3xl font-bold tracking-tight">Anagrafica</h1>
                            <p className="text-muted-foreground">
                                Gestisci volontari, donatori e sostenitori.
                            </p>
                        </div>
                    </div>
                    <ContactFormWrapper />
                </div>

                <div className="flex items-center gap-4">
                    <div className="relative flex-1 max-w-sm">
                        <Search className="absolute left-2.5 top-2.5 h-4 w-4 text-muted-foreground" />
                        <Input
                            type="search"
                            placeholder="Cerca per nome, email..."
                            className="pl-9 bg-white dark:bg-slate-950"
                        // Note: In a real app we'd use client side router push for search
                        // For now, simple server render is fine
                        />
                    </div>
                    {/* Add simple filter links/buttons here if needed */}
                </div>

                <div className="bg-white dark:bg-slate-950 rounded-lg shadow border p-1">
                    <ContactsList contacts={contacts} />
                </div>
            </div>
        </main>
    )
}
