import { Users, Bot, HeartHandshake, Venus, PiggyBank } from "lucide-react";
import { DashboardCard } from "@/components/dashboard/dashboard-card";

export default function Home() {
    return (
        <main className="container mx-auto p-8 pt-10 min-h-screen">
            <div className="mb-8 space-y-2">
                <h1 className="text-3xl font-bold tracking-tight">Dashboard</h1>
                <p className="text-muted-foreground">
                    Benvenuto nel CRM di CRI Venezia. Seleziona un modulo per iniziare.
                </p>
            </div>

            <div className="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                <DashboardCard
                    title="Anagrafica"
                    description="Gestione Volontari, Donatori e Sostenitori. Visualizza e modifica contatti."
                    icon={Users}
                    href="/contacts"
                    variant="red"
                />
                <DashboardCard
                    title="Erika"
                    description="Assistente Virtuale per supporto operativo, fundraising e scrittura contenuti."
                    icon={Venus}
                    href="/chat"
                    variant="pink"
                />
                <DashboardCard
                    title="Newsletter"
                    description="Integrazione Brevo per invio campagne email."
                    icon={HeartHandshake}
                    href="/newsletter"
                    variant="blue"
                />
                <DashboardCard
                    title="Fundraising"
                    description="Generatore campagne e guida Elementor."
                    icon={PiggyBank}
                    href="/fundraising"
                    variant="green"
                />
            </div>
        </main>
    );
}
