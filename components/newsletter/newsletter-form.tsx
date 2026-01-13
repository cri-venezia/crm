'use client'

import { useState } from 'react'
import { sendNewsletter } from '@/app/newsletter/actions'
import { Button } from "@/components/ui/button"
import { Input } from "@/components/ui/input"
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "@/components/ui/select"
import { Label } from "@/components/ui/label"
import { Card, CardContent, CardDescription, CardFooter, CardHeader, CardTitle } from "@/components/ui/card"
import { useToast } from "@/components/ui/use-toast"
import { Loader2, Send } from 'lucide-react'

export function NewsletterForm() {
    const [loading, setLoading] = useState(false)
    const { toast } = useToast()

    async function handleSubmit(formData: FormData) {
        setLoading(true)
        try {
            const result = await sendNewsletter(formData)

            if (result.error) {
                toast({
                    variant: "destructive",
                    title: "Errore Invio",
                    description: result.error
                })
            } else {
                toast({
                    title: "Invio Completato",
                    description: result.message
                })
                // Reset form manually or use ref if strict needed. 
                // For now, simpler to not reset to allow re-sending corrections? 
                // No, better to clear for safety.
                const form = document.getElementById("newsletter-form") as HTMLFormElement
                form?.reset()
            }
        } catch (e) {
            toast({
                variant: "destructive",
                title: "Errore Critico",
                description: "Impossibile contattare il server."
            })
        } finally {
            setLoading(false)
        }
    }

    return (
        <Card className="w-full max-w-2xl mx-auto">
            <CardHeader>
                <CardTitle>Nuova Campagna</CardTitle>
                <CardDescription>Invia email a gruppi di contatti.</CardDescription>
            </CardHeader>
            <CardContent>
                <form id="newsletter-form" action={handleSubmit} className="space-y-6">
                    <div className="space-y-2">
                        <Label htmlFor="recipientType">Destinatari</Label>
                        <Select name="recipientType" defaultValue="all">
                            <SelectTrigger>
                                <SelectValue placeholder="Seleziona gruppo" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="all">Tutti i Contatti</SelectItem>
                                <SelectItem value="volontario">Volontari</SelectItem>
                                <SelectItem value="donatore">Donatori</SelectItem>
                                <SelectItem value="sostenitore">Sostenitori</SelectItem>
                            </SelectContent>
                        </Select>
                    </div>

                    <div className="space-y-2">
                        <Label htmlFor="templateId">ID Template Brevo</Label>
                        <Input name="templateId" type="number" placeholder="Es. 1" required />
                        <p className="text-xs text-muted-foreground">Inserisci l'ID numerico del template creato su Brevo.</p>
                    </div>

                    <Button type="submit" className="w-full" disabled={loading}>
                        {loading ? (
                            <>
                                <Loader2 className="mr-2 h-4 w-4 animate-spin" />
                                Invio in corso...
                            </>
                        ) : (
                            <>
                                <Send className="mr-2 h-4 w-4" />
                                Invia tramite Template
                            </>
                        )}
                    </Button>
                </form>
            </CardContent>
        </Card>
    )
}
