'use client'

import { useState } from 'react'
import { Button } from "@/components/ui/button"
import { Input } from "@/components/ui/input"
import { Label } from "@/components/ui/label"
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from "@/components/ui/card"
import { Tabs, TabsContent, TabsList, TabsTrigger } from "@/components/ui/tabs"
import { useToast } from "@/components/ui/use-toast"
import { updateProfile, updatePassword } from "@/app/profile/actions"
import { User } from '@supabase/supabase-js'

interface ProfileFormProps {
    user: User
}

export function ProfileForm({ user }: ProfileFormProps) {
    const { toast } = useToast()
    const [loading, setLoading] = useState(false)

    // Derived initial state
    const meta = user.user_metadata || {}
    const initialFirst = meta.first_name || meta.full_name?.split(' ')[0] || ''
    const initialLast = meta.last_name || meta.full_name?.split(' ').slice(1).join(' ') || ''

    async function handleProfileUpdate(formData: FormData) {
        setLoading(true)
        try {
            const res = await updateProfile(formData)
            if (res.error) {
                toast({ variant: "destructive", title: "Errore", description: res.error })
            } else {
                toast({ title: "Successo", description: res.message })
            }
        } catch (e) {
            toast({ variant: "destructive", title: "Errore", description: "Si è verificato un problema." })
        } finally {
            setLoading(false)
        }
    }

    async function handlePasswordUpdate(formData: FormData) {
        setLoading(true)
        try {
            const res = await updatePassword(formData)
            if (res.error) {
                toast({ variant: "destructive", title: "Errore", description: res.error })
            } else {
                toast({ title: "Successo", description: res.message })
            }
        } catch (e) {
            toast({ variant: "destructive", title: "Errore", description: "Si è verificato un problema." })
        } finally {
            setLoading(false)
        }
    }

    return (
        <Tabs defaultValue="general" className="w-full">
            <TabsList className="grid w-full grid-cols-2">
                <TabsTrigger value="general">Dati Personali</TabsTrigger>
                <TabsTrigger value="security">Sicurezza</TabsTrigger>
            </TabsList>

            <TabsContent value="general">
                <Card>
                    <CardHeader>
                        <CardTitle>Dati Personali</CardTitle>
                        <CardDescription>Gestisci le tue informazioni pubbliche.</CardDescription>
                    </CardHeader>
                    <CardContent>
                        <form action={handleProfileUpdate} className="space-y-4">
                            <div className="grid grid-cols-2 gap-4">
                                <div className="space-y-2">
                                    <Label htmlFor="firstName">Nome</Label>
                                    <Input name="firstName" defaultValue={initialFirst} placeholder="Nome" required />
                                </div>
                                <div className="space-y-2">
                                    <Label htmlFor="lastName">Cognome</Label>
                                    <Input name="lastName" defaultValue={initialLast} placeholder="Cognome" required />
                                </div>
                            </div>
                            <div className="space-y-2">
                                <Label>Email</Label>
                                <Input value={user.email} disabled className="bg-muted" />
                                <p className="text-xs text-muted-foreground">L'email non può essere modificata.</p>
                            </div>
                            <Button type="submit" disabled={loading}>
                                {loading ? "Salvataggio..." : "Salva Modifiche"}
                            </Button>
                        </form>
                    </CardContent>
                </Card>
            </TabsContent>

            <TabsContent value="security">
                <Card>
                    <CardHeader>
                        <CardTitle>Password</CardTitle>
                        <CardDescription>Aggiorna la tua password di accesso.</CardDescription>
                    </CardHeader>
                    <CardContent>
                        <form action={handlePasswordUpdate} className="space-y-4">
                            <div className="space-y-2">
                                <Label htmlFor="password">Nuova Password</Label>
                                <Input name="password" type="password" required minLength={6} placeholder="Min. 6 caratteri" />
                            </div>
                            <div className="space-y-2">
                                <Label htmlFor="confirmPassword">Conferma Password</Label>
                                <Input name="confirmPassword" type="password" required minLength={6} placeholder="Ripeti password" />
                            </div>
                            <Button type="submit" disabled={loading}>
                                {loading ? "Aggiornamento..." : "Aggiorna Password"}
                            </Button>
                        </form>
                    </CardContent>
                </Card>
            </TabsContent>
        </Tabs>
    )
}
