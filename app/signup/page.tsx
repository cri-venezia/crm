'use client'

import { useState } from 'react'
import { signup } from '@/app/login/actions'
import { Button } from "@/components/ui/button"
import { Input } from "@/components/ui/input"
import { useToast } from "@/components/ui/use-toast"
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from "@/components/ui/card"
import { Label } from "@/components/ui/label"
import { useRouter } from 'next/navigation'

export default function SignupPage() {
    const [loading, setLoading] = useState(false)
    const { toast } = useToast()
    const router = useRouter()

    const handleAction = async (formData: FormData) => {
        setLoading(true)

        try {
            const result = await signup(formData)
            if (result && 'error' in result && result.error) {
                toast({
                    variant: "destructive",
                    title: "Errore",
                    description: result.error,
                })
            } else if (result && 'message' in result && result.message) {
                toast({
                    title: "Successo",
                    description: result.message,
                })
                // Optional: Redirect to login or show success state
                router.push('/login')
            }
        } catch (error) {
            toast({
                variant: "destructive",
                title: "Critico",
                description: "Qualcosa è andato storto. Riprova.",
            })
        } finally {
            setLoading(false)
        }
    }

    return (
        <div className="flex items-center justify-center min-h-screen bg-gray-50 dark:bg-gray-900 px-4">
            <Card className="w-full max-w-md">
                <CardHeader className="space-y-1">
                    <CardTitle className="text-2xl font-bold text-center text-red-600">Registrazione</CardTitle>
                    <CardDescription className="text-center">
                        Crea un account per CRI Venezia
                    </CardDescription>
                </CardHeader>
                <CardContent className="space-y-4">

                    <form action={handleAction} className="space-y-4">
                        <div className="grid grid-cols-2 gap-4">
                            <div className="space-y-2">
                                <Label htmlFor="firstName">Nome</Label>
                                <Input id="firstName" name="firstName" type="text" placeholder="Mario" required />
                            </div>
                            <div className="space-y-2">
                                <Label htmlFor="lastName">Cognome</Label>
                                <Input id="lastName" name="lastName" type="text" placeholder="Rossi" required />
                            </div>
                        </div>

                        <div className="space-y-2">
                            <Label htmlFor="email">Email</Label>
                            <Input id="email" name="email" type="email" placeholder="m@example.com" required />
                        </div>
                        <div className="space-y-2">
                            <Label htmlFor="password">Password</Label>
                            <Input id="password" name="password" type="password" required />
                        </div>

                        <div className="flex flex-col gap-2 pt-4">
                            <Button
                                type="submit"
                                className="w-full bg-red-600 hover:bg-red-700"
                                disabled={loading}
                            >
                                {loading ? "Caricamento..." : "Crea Account"}
                            </Button>
                        </div>
                        <div className="text-center text-sm mt-4">
                            <a href="/login" className="text-muted-foreground hover:text-red-600 underline">
                                Hai già un account? Accedi
                            </a>
                        </div>
                    </form>
                </CardContent>
            </Card>
        </div>
    )
}
