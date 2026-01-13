'use client'

import { useState } from "react"
import { useForm } from "react-hook-form"
import { zodResolver } from "@hookform/resolvers/zod"
import { Contact, createContact, updateContact } from "@/app/contacts/actions"
import { contactFormSchema, ContactFormValues } from "./schema"
import { Button } from "@/components/ui/button"
import {
    Form,
    FormControl,
    FormDescription,
    FormField,
    FormItem,
    FormLabel,
    FormMessage,
} from "@/components/ui/form"
import { Input } from "@/components/ui/input"
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from "@/components/ui/select"
import { Textarea } from "@/components/ui/textarea"
import { Dialog, DialogContent, DialogDescription, DialogHeader, DialogTitle } from "@/components/ui/dialog"
import { CriIcon } from "@/components/ui/cri-icon"

interface ContactFormProps {
    open: boolean
    onOpenChange: (open: boolean) => void
    contact?: Contact
}

export function ContactForm({ open, onOpenChange, contact }: ContactFormProps) {
    const [loading, setLoading] = useState(false)
    const isEditing = !!contact

    const form = useForm<ContactFormValues>({
        resolver: zodResolver(contactFormSchema),
        defaultValues: {
            first_name: contact?.first_name || "",
            last_name: contact?.last_name || "",
            email: contact?.email || "",
            phone: contact?.phone || "",
            type: contact?.type || "sostenitore",
            tags: contact?.tags?.join(", ") || "",
            notes: contact?.notes || "",
        },
    })

    async function onSubmit(data: ContactFormValues) {
        setLoading(true)
        try {
            const payload = {
                ...data,
                tags: data.tags ? data.tags.split(",").map(t => t.trim()).filter(Boolean) : [],
                email: data.email || null, // Ensure empty string becomes null if backend expects it, or keep as string
                phone: data.phone || null,
                notes: data.notes || null,
            }

            let result;
            if (isEditing && contact) {
                result = await updateContact(contact.id, payload)
            } else {
                result = await createContact(payload)
            }

            if (result.error) {
                console.error(result.error)
                // You could show a toast here
                return
            }

            onOpenChange(false)
            form.reset()
        } catch (error) {
            console.error(error)
        } finally {
            setLoading(false)
        }
    }

    return (
        <Dialog open={open} onOpenChange={onOpenChange}>
            <DialogContent className="sm:max-w-[550px]">
                <DialogHeader>
                    <DialogTitle>{isEditing ? "Modifica Contatto" : "Nuovo Contatto"}</DialogTitle>
                    <DialogDescription>
                        {isEditing ? "Modifica le informazioni del contatto esistente." : "Aggiungi un nuovo volontario, donatore o sostenitore."}
                    </DialogDescription>
                </DialogHeader>
                <Form {...form}>
                    <form onSubmit={form.handleSubmit(onSubmit)} className="space-y-4">
                        <div className="grid grid-cols-2 gap-4">
                            <FormField
                                control={form.control}
                                name="first_name"
                                render={({ field }) => (
                                    <FormItem>
                                        <FormLabel>Nome</FormLabel>
                                        <FormControl>
                                            <Input placeholder="Mario" {...field} />
                                        </FormControl>
                                        <FormMessage />
                                    </FormItem>
                                )}
                            />
                            <FormField
                                control={form.control}
                                name="last_name"
                                render={({ field }) => (
                                    <FormItem>
                                        <FormLabel>Cognome</FormLabel>
                                        <FormControl>
                                            <Input placeholder="Rossi" {...field} />
                                        </FormControl>
                                        <FormMessage />
                                    </FormItem>
                                )}
                            />
                        </div>

                        <div className="grid grid-cols-2 gap-4">
                            <FormField
                                control={form.control}
                                name="email"
                                render={({ field }) => (
                                    <FormItem>
                                        <FormLabel>Email</FormLabel>
                                        <FormControl>
                                            <Input placeholder="m.rossi@example.com" {...field} />
                                        </FormControl>
                                        <FormMessage />
                                    </FormItem>
                                )}
                            />
                            <FormField
                                control={form.control}
                                name="phone"
                                render={({ field }) => (
                                    <FormItem>
                                        <FormLabel>Telefono</FormLabel>
                                        <FormControl>
                                            <Input placeholder="+39 333 1234567" {...field} />
                                        </FormControl>
                                        <FormMessage />
                                    </FormItem>
                                )}
                            />
                        </div>

                        <FormField
                            control={form.control}
                            name="type"
                            render={({ field }) => (
                                <FormItem>
                                    <FormLabel>Tipo</FormLabel>
                                    <Select onValueChange={field.onChange} defaultValue={field.value}>
                                        <FormControl>
                                            <SelectTrigger>
                                                <SelectValue placeholder="Seleziona tipo" />
                                            </SelectTrigger>
                                        </FormControl>
                                        <SelectContent>
                                            <SelectItem value="volontario">
                                                <div className="flex items-center gap-2">
                                                    <CriIcon name="user-nurse" size={16} /> Volontario
                                                </div>
                                            </SelectItem>
                                            <SelectItem value="sostenitore">
                                                <div className="flex items-center gap-2">
                                                    <CriIcon name="star-of-life" size={16} /> Sostenitore
                                                </div>
                                            </SelectItem>
                                            <SelectItem value="donatore">
                                                <div className="flex items-center gap-2">
                                                    <CriIcon name="heart-circle-plus" size={16} /> Donatore
                                                </div>
                                            </SelectItem>
                                        </SelectContent>
                                    </Select>
                                    <FormMessage />
                                </FormItem>
                            )}
                        />

                        <FormField
                            control={form.control}
                            name="tags"
                            render={({ field }) => (
                                <FormItem>
                                    <FormLabel>Tags (separati da virgola)</FormLabel>
                                    <FormControl>
                                        <Input placeholder="socio, autista, corso-base" {...field} />
                                    </FormControl>
                                    <FormDescription>Es: autista, logistica, fundraising</FormDescription>
                                    <FormMessage />
                                </FormItem>
                            )}
                        />

                        <FormField
                            control={form.control}
                            name="notes"
                            render={({ field }) => (
                                <FormItem>
                                    <FormLabel>Note</FormLabel>
                                    <FormControl>
                                        <Textarea placeholder="Note aggiuntive..." {...field} />
                                    </FormControl>
                                    <FormMessage />
                                </FormItem>
                            )}
                        />

                        <div className="flex justify-end gap-2 pt-4">
                            <Button variant="outline" type="button" onClick={() => onOpenChange(false)}>Annulla</Button>
                            <Button type="submit" disabled={loading} className="bg-red-600 hover:bg-red-700">
                                {loading ? "Salvataggio..." : (isEditing ? "Aggiorna" : "Crea")}
                            </Button>
                        </div>
                    </form>
                </Form>
            </DialogContent>
        </Dialog>
    )
}
