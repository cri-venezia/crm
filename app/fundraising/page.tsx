import { CampaignGenerator } from "@/components/fundraising/campaign-generator"
import { PiggyBank } from "lucide-react"

export default function FundraisingPage() {
    return (
        <main className="flex min-h-screen flex-col p-8 bg-gray-50 dark:bg-gray-900">
            <div className="flex flex-col gap-8 max-w-7xl mx-auto w-full">
                <div className="flex items-center gap-4">
                    <div className="p-3 bg-purple-100 rounded-full dark:bg-purple-900/20">
                        <PiggyBank className="w-8 h-8 text-purple-600 dark:text-purple-500" />
                    </div>
                    <div>
                        <h1 className="text-3xl font-bold tracking-tight">Fundraising</h1>
                        <p className="text-muted-foreground">
                            Genera campagne di raccolta fondi e landing page con il supporto di Erika.
                        </p>
                    </div>
                </div>

                <CampaignGenerator />
            </div>
        </main>
    )
}
