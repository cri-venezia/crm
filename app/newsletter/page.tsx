import { NewsletterForm } from "@/components/newsletter/newsletter-form"
import { Mail } from "lucide-react"

export default function NewsletterPage() {
    return (
        <main className="flex min-h-screen flex-col p-8 bg-gray-50 dark:bg-gray-900">
            <div className="flex flex-col gap-8 max-w-7xl mx-auto w-full">
                <div className="flex items-center gap-4">
                    <div className="p-3 bg-blue-100 rounded-full dark:bg-blue-900/20">
                        <Mail className="w-8 h-8 text-blue-600 dark:text-blue-500" />
                    </div>
                    <div>
                        <h1 className="text-3xl font-bold tracking-tight">Newsletter</h1>
                        <p className="text-muted-foreground">
                            Gestione campagne email e comunicazioni.
                        </p>
                    </div>
                </div>

                <div className="grid gap-6">
                    <NewsletterForm />
                </div>
            </div>
        </main>
    )
}
