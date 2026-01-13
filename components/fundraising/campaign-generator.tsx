'use client'

import { useState } from 'react'
import { Button } from "@/components/ui/button"
import { Input } from "@/components/ui/input"
import { Textarea } from "@/components/ui/textarea"
import { Label } from "@/components/ui/label"
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from "@/components/ui/card"
import { Tabs, TabsContent, TabsList, TabsTrigger } from "@/components/ui/tabs"
import { Loader2, Sparkles, LayoutTemplate, PenTool } from 'lucide-react'
import ReactMarkdown from 'react-markdown'
import { generateCampaign } from "@/app/fundraising/actions"

export function CampaignGenerator() {
    const [loading, setLoading] = useState(false)
    const [result, setResult] = useState<{ copy: string, layout: string } | null>(null)

    async function handleSubmit(formData: FormData) {
        setLoading(true)
        setResult(null)
        try {
            const response = await generateCampaign(formData)
            if (response.copy && response.layout) {
                setResult(response)
            }
        } catch (e) {
            console.error(e)
        } finally {
            setLoading(false)
        }
    }

    return (
        <div className="grid gap-6 lg:grid-cols-2">
            <Card>
                <CardHeader>
                    <CardTitle>Dettagli Campagna</CardTitle>
                    <CardDescription>Descrivi l'iniziativa per generare contenuti.</CardDescription>
                </CardHeader>
                <CardContent>
                    <form action={handleSubmit} className="space-y-4">
                        <div className="space-y-2">
                            <Label htmlFor="title">Titolo Iniziativa</Label>
                            <Input name="title" placeholder="Es. Uovo di Pasqua Solidale" required />
                        </div>
                        <div className="space-y-2">
                            <Label htmlFor="goal">Obiettivo Fundraising</Label>
                            <Input name="goal" placeholder="Es. Acquistare una nuova ambulanza" required />
                        </div>
                        <div className="space-y-2">
                            <Label htmlFor="target">Target Donatori</Label>
                            <Input name="target" placeholder="Es. Aziende locali, Famiglie, Sostenitori storici" required />
                        </div>
                        <div className="space-y-2">
                            <Label htmlFor="tone">Tono di Voce</Label>
                            <Input name="tone" placeholder="Es. Emozionale, Urgente, Istituzionale" />
                        </div>
                        <div className="space-y-2">
                            <Label htmlFor="details">Dettagli Aggiuntivi</Label>
                            <Textarea name="details" placeholder="Date, scadenze, vantaggi fiscali..." />
                        </div>

                        <Button type="submit" className="w-full" disabled={loading}>
                            {loading ? (
                                <>
                                    <Loader2 className="mr-2 h-4 w-4 animate-spin" />
                                    Erika sta scrivendo...
                                </>
                            ) : (
                                <>
                                    <Sparkles className="mr-2 h-4 w-4" />
                                    Genera Campagna
                                </>
                            )}
                        </Button>
                    </form>
                </CardContent>
            </Card>

            {result && (
                <Card className="h-full flex flex-col">
                    <CardHeader>
                        <CardTitle>Risultato Generato</CardTitle>
                        <CardDescription>Usa questi contenuti per la tua comunicazione.</CardDescription>
                    </CardHeader>
                    <CardContent className="flex-1 overflow-auto">
                        <Tabs defaultValue="copy" className="w-full">
                            <TabsList className="grid w-full grid-cols-2">
                                <TabsTrigger value="copy">
                                    <PenTool className="mr-2 h-4 w-4" />
                                    Testi & Copy
                                </TabsTrigger>
                                <TabsTrigger value="layout">
                                    <LayoutTemplate className="mr-2 h-4 w-4" />
                                    Struttura Elementor
                                </TabsTrigger>
                            </TabsList>
                            <TabsContent value="copy" className="prose dark:prose-invert max-w-none mt-4 p-4 border rounded-md bg-muted/20">
                                <ReactMarkdown>{result.copy}</ReactMarkdown>
                            </TabsContent>
                            <TabsContent value="layout" className="mt-4 space-y-4">
                                <div className="p-4 border rounded-md bg-muted/20">
                                    <p className="text-sm text-muted-foreground mb-4">
                                        Il template Elementor è stato generato e personalizzato per la tua campagna.
                                        Scarica il file JSON e importalo direttamente in Elementor come "Pagina" o "Template".
                                    </p>
                                    <Button
                                        variant="outline"
                                        className="w-full gap-2"
                                        onClick={() => {
                                            if (!result?.layout) return;
                                            const blob = new Blob([result.layout], { type: "application/json" });
                                            const url = URL.createObjectURL(blob);
                                            const a = document.createElement("a");
                                            a.href = url;
                                            a.download = "campagna-elementor.json";
                                            document.body.appendChild(a);
                                            a.click();
                                            document.body.removeChild(a);
                                            URL.revokeObjectURL(url);
                                        }}
                                    >
                                        <LayoutTemplate className="h-4 w-4" />
                                        Scarica Template Elementor (.json)
                                    </Button>
                                </div>
                            </TabsContent>
                        </Tabs>
                    </CardContent>
                </Card>
            )}
        </div>
    )
}
